<?php
namespace AutoNode;

defined( 'ABSPATH' ) || exit;

/**
 * Webhook Manager â€” v2
 *
 * Fixes:
 *  - HMAC secret stored as raw (not hashed) so n8n can verify signatures correctly
 *  - Delivery log written on every attempt
 *  - Exponential backoff retry via separate cron event
 *  - Retry count tracked per delivery log row
 *  - Attachment events respect post_types filter
 */
final class Webhook_Manager {

    public const EVENTS = [
        'post.created', 'post.updated', 'post.published', 'post.deleted',
        'page.created', 'page.updated', 'page.published', 'page.deleted',
        'media.uploaded', 'test',
    ];

    private const MAX_RETRIES = 3;

    /* Retry delays in seconds: attempt 2 â†’ 60s, attempt 3 â†’ 300s, attempt 4 â†’ 900s */
    private const RETRY_DELAYS = [ 0, 60, 300, 900 ];

    public static function init(): void {
        $s = get_option( 'autonode_settings', [] );
        if ( empty( $s['enable_webhooks'] ) ) {
            return;
        }

        add_action( 'wp_insert_post',     [ __CLASS__, 'on_post_save' ],   99, 3 );
        add_action( 'before_delete_post', [ __CLASS__, 'on_post_delete' ], 10, 2 );
        add_action( 'add_attachment',     static fn( $id ) => self::schedule_fire( 'media.uploaded', $id, 'attachment' ) );

        /* Primary delivery worker */
        add_action( 'autonode_fire_webhook',  [ __CLASS__, 'fire' ],  10, 3 );
        /* Retry worker */
        add_action( 'autonode_retry_webhook', [ __CLASS__, 'retry' ], 10, 2 );
    }

    /* â”€â”€ WP hooks â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public static function on_post_save( int $id, \WP_Post $post, bool $update ): void {
        if ( wp_is_post_revision( $id ) || wp_is_post_autosave( $id ) ) {
            return;
        }
        if ( ! in_array( $post->post_type, [ 'post', 'page' ], true ) ) {
            return;
        }
        $type = $post->post_type;
        self::schedule_fire( $update ? "{$type}.updated" : "{$type}.created", $id, $type );
        if ( $post->post_status === 'publish' ) {
            self::schedule_fire( "{$type}.published", $id, $type );
        }
    }

    public static function on_post_delete( int $id, \WP_Post $post ): void {
        if ( in_array( $post->post_type, [ 'post', 'page' ], true ) ) {
            self::schedule_fire( "{$post->post_type}.deleted", $id, $post->post_type );
        }
    }

    private static function schedule_fire( string $event, int $id, string $type ): void {
        wp_schedule_single_event( time(), 'autonode_fire_webhook', [ $event, $id, $type ] );
        /* Spawn a cron request immediately so WP-Cron doesn't wait for next page load */
        if ( ! defined( 'DOING_CRON' ) ) {
            wp_remote_post( site_url( 'wp-cron.php' ), [
                'timeout'   => 0.01,
                'blocking'  => false,
                'sslverify' => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WP core filter.
            ] );
        }
    }

    /* â”€â”€ Delivery â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public static function fire( string $event, int $object_id, string $object_type ): void {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table.
        $hooks = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}autonode_webhooks WHERE active = 1",
            ARRAY_A
        ) ?: [];

        $payload = self::build_payload( $event, $object_id, $object_type );

        foreach ( $hooks as $hook ) {
            $events = json_decode( $hook['events'],     true ) ?: [];
            $types  = json_decode( $hook['post_types'], true ) ?: [];
            if ( ! in_array( $event, $events, true ) ) {
                continue;
            }
            if ( $object_type !== 'attachment' && ! in_array( $object_type, $types, true ) ) {
                continue;
            }
            self::deliver( $hook, $payload, 1 );
        }
    }

    /** Called by the retry cron with the log_id of the failed attempt */
    public static function retry( int $log_id, int $attempt ): void {
        global $wpdb;
        $log = $wpdb->get_row( // phpcs:ignore
            $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}autonode_webhook_log WHERE id = %d", $log_id ),
            ARRAY_A
        );
        if ( ! $log ) {
            return;
        }
        $hook = $wpdb->get_row( // phpcs:ignore
            $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}autonode_webhooks WHERE id = %d AND active = 1", $log['webhook_id'] ),
            ARRAY_A
        );
        if ( ! $hook ) {
            return;
        }
        $payload = self::build_payload( $log['event'], (int) $log['object_id'], '' );
        self::deliver( $hook, $payload, $attempt, $log_id );
    }

    /** Core delivery â€” sends, logs, schedules retry on failure */
    private static function deliver( array $hook, array $payload, int $attempt, ?int $parent_log_id = null ): void {
        global $wpdb;
        $t_log  = "{$wpdb->prefix}autonode_webhook_log";
        $t_hook = "{$wpdb->prefix}autonode_webhooks";

        $started = microtime( true );
        $result  = self::send( $hook, $payload );
        $ms      = (int) round( ( microtime( true ) - $started ) * 1000 );
        $success = $result['success'];

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table insert.
        $wpdb->insert( $t_log, [
            'webhook_id'    => (int) $hook['id'],
            'event'         => $payload['event'] ?? '',
            'object_id'     => $payload['object_id'] ?? null,
            'attempt'       => $attempt,
            'http_status'   => $result['status'] ?: null,
            'duration_ms'   => $ms,
            'response_body' => isset( $result['body'] ) ? substr( $result['body'], 0, 1000 ) : null,
            'error_message' => $result['error'] ?? null,
            'created_at'    => current_time( 'mysql', true ),
        ], [ '%d', '%s', '%d', '%d', '%d', '%d', '%s', '%s', '%s' ] );

        $log_id = (int) $wpdb->insert_id;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table update.
        $wpdb->update( $t_hook, [
            'last_fired_at' => current_time( 'mysql', true ),
            'last_status'   => $result['status'],
            'fire_count'    => (int) $hook['fire_count'] + 1,
            'fail_count'    => $success ? (int) $hook['fail_count'] : (int) $hook['fail_count'] + 1,
        ], [ 'id' => (int) $hook['id'] ], [ '%s', '%d', '%d', '%d' ], [ '%d' ] );

        /* Schedule retry if failed and attempts remain */
        $max    = (int) ( get_option( 'autonode_settings', [] )['max_retry_attempts'] ?? self::MAX_RETRIES );
        if ( ! $success && $attempt <= $max ) {
            $delay = self::RETRY_DELAYS[ min( $attempt, count( self::RETRY_DELAYS ) - 1 ) ];
            wp_schedule_single_event( time() + $delay, 'autonode_retry_webhook', [ $log_id, $attempt + 1 ] );
        }
    }

    /* â”€â”€ HTTP Send â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    private static function send( array $hook, array $payload ): array {
        $body    = wp_json_encode( $payload );
        $headers = [
            'Content-Type'     => 'application/json',
            'X-autonode-Event'   => $payload['event'] ?? '',
            'X-autonode-Version' => AUTONODE_VERSION,
            'X-autonode-Hook-ID' => (string) $hook['id'],
        ];

        /*
         * HMAC signature â€” uses the RAW secret (not a hash of it).
         * The secret column now stores the plaintext secret.
         * n8n verifies: HMAC-SHA256(secret, raw_body) == signature_value
         */
        if ( ! empty( $hook['secret'] ) ) {
            $headers['X-autonode-Signature'] = 'sha256=' . hash_hmac( 'sha256', $body, $hook['secret'] );
        }

        $s       = get_option( 'autonode_settings', [] );
        $timeout = max( 1, (int) ( $s['webhook_timeout_ms'] ?? 5000 ) / 1000 );

        $resp = wp_remote_post( $hook['target_url'], [
            'body'        => $body,
            'headers'     => $headers,
            'timeout'     => $timeout,
            'blocking'    => true,
            'data_format' => 'body',
            'sslverify'   => true,
        ] );

        if ( is_wp_error( $resp ) ) {
            return [ 'status' => 0, 'success' => false, 'error' => $resp->get_error_message() ];
        }

        $code = (int) wp_remote_retrieve_response_code( $resp );
        return [
            'status'  => $code,
            'success' => $code >= 200 && $code < 300,
            'body'    => substr( wp_remote_retrieve_body( $resp ), 0, 500 ),
        ];
    }

    /* â”€â”€ Payload Builder â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    private static function build_payload( string $event, int $id, string $type ): array {
        $payload = [
            'event'       => $event,
            'object_id'   => $id,
            'object_type' => $type,
            'timestamp'   => current_time( 'c', true ),
            'site_url'    => get_site_url(),
        ];
        if ( in_array( $type, [ 'post', 'page' ], true ) ) {
            $p = get_post( $id );
            if ( $p ) {
                $post_data = Post_Manager::format( $p );
                $seo_data  = Rankmath_Handler::read( $id );
                
                $s = get_option( 'autonode_settings', [] );
                if ( ! empty( $s['flatten_n8n_webhooks'] ) ) {
                    // Flatten structure for n8n
                    foreach( $post_data as $k => $v ) {
                        $payload[ 'post_' . $k ] = is_array( $v ) ? wp_json_encode( $v ) : $v;
                    }
                    foreach( $seo_data as $k => $v ) {
                        $payload[ 'seo_' . $k ] = is_array( $v ) ? wp_json_encode( $v ) : $v;
                    }
                } else {
                    $payload['post'] = $post_data;
                    $payload['seo']  = $seo_data;
                }
            }
        }
        return $payload;
    }

    /* â”€â”€ Test â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public static function fire_test( array $hook ): array {
        $payload = [
            'event'      => 'test',
            'test'       => true,
            'webhook_id' => (int) $hook['id'],
            'timestamp'  => current_time( 'c', true ),
            'site_url'   => get_site_url(),
        ];
        $started = microtime( true );
        $result  = self::send( $hook, $payload );
        $result['duration_ms'] = (int) round( ( microtime( true ) - $started ) * 1000 );
        return $result;
    }

    /* â”€â”€ Delivery Log Queries â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public static function delivery_log( int $webhook_id, int $limit = 50 ): array {
        global $wpdb;
        return $wpdb->get_results( // phpcs:ignore
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}autonode_webhook_log WHERE webhook_id = %d ORDER BY created_at DESC LIMIT %d",
                $webhook_id,
                $limit
            ),
            ARRAY_A
        ) ?: [];
    }

    public static function recent_deliveries( int $limit = 100 ): array {
        global $wpdb;
        return $wpdb->get_results( // phpcs:ignore
            $wpdb->prepare(
                "SELECT l.*, w.label, w.target_url FROM {$wpdb->prefix}autonode_webhook_log l
                 LEFT JOIN {$wpdb->prefix}autonode_webhooks w ON l.webhook_id = w.id
                 ORDER BY l.created_at DESC LIMIT %d",
                $limit
            ),
            ARRAY_A
        ) ?: [];
    }

    public static function get_events(): array {
        return self::EVENTS;
    }
}
