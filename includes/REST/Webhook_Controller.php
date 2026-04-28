<?php
namespace AutoNode\REST;

defined( 'ABSPATH' ) || exit;

use AutoNode\Api_Auth;
use AutoNode\Activity_Logger;
use AutoNode\Webhook_Manager;
use AutoNode\Brute_Force;
use AutoNode\Cron_Health;

/* â”€â”€â”€ Webhook Controller â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
final class Webhook_Controller extends Base_Controller {

    public static function register(): void {
        register_rest_route( self::NS, '/webhooks', [
            [ 'methods' => \WP_REST_Server::READABLE,  'callback' => [ self::class, 'index' ],  'permission_callback' => fn( $r ) => self::guard( $r, 'webhooks:read' ) ],
            [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ self::class, 'create' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'webhooks:write' ) ],
        ] );
        register_rest_route( self::NS, '/webhooks/(?P<id>\d+)', [
            [ 'methods' => \WP_REST_Server::READABLE,  'callback' => [ self::class, 'show' ],   'permission_callback' => fn( $r ) => self::guard( $r, 'webhooks:read' ) ],
            [ 'methods' => \WP_REST_Server::EDITABLE,  'callback' => [ self::class, 'update' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'webhooks:write' ) ],
            [ 'methods' => \WP_REST_Server::DELETABLE, 'callback' => [ self::class, 'destroy' ],'permission_callback' => fn( $r ) => self::guard( $r, 'webhooks:write' ) ],
        ] );
        register_rest_route( self::NS, '/webhooks/(?P<id>\d+)/test', [
            [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ self::class, 'test' ],   'permission_callback' => fn( $r ) => self::guard( $r, 'webhooks:write' ) ],
        ] );
        register_rest_route( self::NS, '/webhooks/(?P<id>\d+)/deliveries', [
            [ 'methods' => \WP_REST_Server::READABLE,  'callback' => [ self::class, 'deliveries' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'webhooks:read' ) ],
        ] );
    }

    public static function index( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table.
        $rows = $wpdb->get_results( "SELECT id,key_id,label,target_url,events,post_types,active,last_fired_at,last_status,fire_count,fail_count,created_at FROM {$wpdb->prefix}autonode_webhooks ORDER BY created_at DESC", ARRAY_A ) ?: [];
        return self::ok( $req, [ 'webhooks' => $rows, 'total' => count( $rows ) ], 200, 'list_webhooks' );
    }

    public static function create( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        global $wpdb;
        $body = $req->get_json_params() ?: [];
        $url  = esc_url_raw( $body['target_url'] ?? '' );
        if ( ! $url || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            return self::fail( $req, new \WP_Error( 'amp_invalid', 'Valid target_url required.', [ 'status' => 400 ] ), 'create_webhook' );
        }
        $key = $req->get_param( '_amp_key' );
        /* Store the raw secret â€” HMAC signing uses this directly */
        $secret = sanitize_text_field( $body['secret'] ?? '' );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table insert.
        $wpdb->insert( "{$wpdb->prefix}autonode_webhooks", [
            'key_id'      => $key['id'] ?? null,
            'label'       => sanitize_text_field( $body['label'] ?? '' ),
            'target_url'  => $url,
            'secret'      => $secret ?: null,
            'events'      => wp_json_encode( (array) ( $body['events'] ?? [ 'post.published' ] ) ),
            'post_types'  => wp_json_encode( (array) ( $body['post_types'] ?? [ 'post', 'page' ] ) ),
            'active'      => 1,
            'created_at'  => current_time( 'mysql', true ),
        ] );
        $id  = (int) $wpdb->insert_id;
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT id,label,target_url,events,post_types,active,created_at FROM {$wpdb->prefix}autonode_webhooks WHERE id=%d", $id ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return self::ok( $req, [ 'webhook' => $row ], 201, 'create_webhook', $id, 'webhook' );
    }

    public static function show( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        global $wpdb;
        $id  = (int) $req->get_param( 'id' );
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT id,key_id,label,target_url,events,post_types,active,last_fired_at,last_status,fire_count,fail_count,created_at FROM {$wpdb->prefix}autonode_webhooks WHERE id=%d", $id ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        if ( ! $row ) return self::fail( $req, new \WP_Error( 'amp_not_found', 'Webhook not found.', [ 'status' => 404 ] ), 'get_webhook' );
        return self::ok( $req, [ 'webhook' => $row ], 200, 'get_webhook', $id, 'webhook' );
    }

    public static function update( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        global $wpdb;
        $id   = (int) $req->get_param( 'id' );
        $body = $req->get_json_params() ?: [];
        if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}autonode_webhooks WHERE id=%d", $id ) ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            return self::fail( $req, new \WP_Error( 'amp_not_found', 'Webhook not found.', [ 'status' => 404 ] ), 'update_webhook' );
        }
        $upd = [];
        if ( isset( $body['label'] )      ) $upd['label']      = sanitize_text_field( $body['label'] );
        if ( isset( $body['target_url'] ) ) $upd['target_url'] = esc_url_raw( $body['target_url'] );
        if ( isset( $body['events'] )     ) $upd['events']     = wp_json_encode( (array) $body['events'] );
        if ( isset( $body['post_types'] ) ) $upd['post_types'] = wp_json_encode( (array) $body['post_types'] );
        if ( isset( $body['active'] )     ) $upd['active']     = (int) (bool) $body['active'];
        if ( array_key_exists( 'secret', $body ) ) $upd['secret'] = $body['secret'] ? sanitize_text_field( $body['secret'] ) : null;
        if ( $upd ) $wpdb->update( "{$wpdb->prefix}autonode_webhooks", $upd, [ 'id' => $id ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT id,label,target_url,events,post_types,active,created_at FROM {$wpdb->prefix}autonode_webhooks WHERE id=%d", $id ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return self::ok( $req, [ 'webhook' => $row ], 200, 'update_webhook', $id, 'webhook' );
    }

    public static function destroy( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        global $wpdb;
        $id = (int) $req->get_param( 'id' );
        $r  = $wpdb->delete( "{$wpdb->prefix}autonode_webhooks", [ 'id' => $id ], [ '%d' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return self::ok( $req, [ 'deleted' => (bool) $r, 'id' => $id ], 200, 'delete_webhook', $id, 'webhook' );
    }

    public static function test( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        global $wpdb;
        $id  = (int) $req->get_param( 'id' );
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}autonode_webhooks WHERE id=%d", $id ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        if ( ! $row ) return self::fail( $req, new \WP_Error( 'amp_not_found', 'Webhook not found.', [ 'status' => 404 ] ), 'test_webhook' );
        return self::ok( $req, [ 'test_result' => Webhook_Manager::fire_test( $row ) ], 200, 'test_webhook', $id, 'webhook' );
    }

    public static function deliveries( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id   = (int) $req->get_param( 'id' );
        $rows = Webhook_Manager::delivery_log( $id, 50 );
        return self::ok( $req, [ 'deliveries' => $rows, 'total' => count( $rows ) ], 200, 'list_deliveries' );
    }
}

/* â”€â”€â”€ Analytics Controller â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
final class Analytics_Controller extends Base_Controller {

    public static function register(): void {
        register_rest_route( self::NS, '/analytics/summary',   [ [ 'methods' => \WP_REST_Server::READABLE, 'callback' => [ self::class, 'summary' ],   'permission_callback' => fn( $r ) => self::guard( $r, 'analytics:read' ) ] ] );
        register_rest_route( self::NS, '/analytics/hourly',    [ [ 'methods' => \WP_REST_Server::READABLE, 'callback' => [ self::class, 'hourly' ],    'permission_callback' => fn( $r ) => self::guard( $r, 'analytics:read' ), 'args' => [ 'hours' => [ 'type' => 'integer', 'default' => 24, 'minimum' => 1, 'maximum' => 168 ] ] ] ] );
        register_rest_route( self::NS, '/analytics/endpoints', [ [ 'methods' => \WP_REST_Server::READABLE, 'callback' => [ self::class, 'endpoints' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'analytics:read' ) ] ] );
        register_rest_route( self::NS, '/analytics/keys',      [ [ 'methods' => \WP_REST_Server::READABLE, 'callback' => [ self::class, 'keys' ],      'permission_callback' => fn( $r ) => self::guard( $r, 'analytics:read' ) ] ] );
    }

    public static function summary( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        return self::ok( $req, Activity_Logger::summary(), 200, 'analytics_summary' );
    }
    public static function hourly( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        return self::ok( $req, Activity_Logger::hourly_stats( (int) $req->get_param( 'hours' ) ), 200, 'analytics_hourly' );
    }
    public static function endpoints( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        return self::ok( $req, Activity_Logger::top_endpoints(), 200, 'analytics_endpoints' );
    }
    public static function keys( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        global $wpdb;
        $t    = "{$wpdb->prefix}autonode_analytics";
        $k    = "{$wpdb->prefix}autonode_keys";
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results(
            "SELECT a.key_id, k.label, k.key_prefix, k.environment,
                    SUM(a.hits) as total_requests, AVG(a.total_ms/a.hits) as avg_ms,
                    SUM(CASE WHEN a.http_status >= 400 THEN a.hits ELSE 0 END) as errors
             FROM $t a LEFT JOIN $k k ON a.key_id = k.id
             WHERE a.bucket_hour >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 7 DAY)
             GROUP BY a.key_id ORDER BY total_requests DESC LIMIT 50",
            ARRAY_A
        ) ?: [];
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return self::ok( $req, [ 'keys' => $rows, 'period' => '7d' ], 200, 'analytics_keys' );
    }
}

/* â”€â”€â”€ Keys Controller â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
final class Keys_Controller extends Base_Controller {

    public static function register(): void {
        register_rest_route( self::NS, '/keys', [ [ 'methods' => \WP_REST_Server::READABLE, 'callback' => [ self::class, 'index' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'keys:read' ) ] ] );
        register_rest_route( self::NS, '/keys/(?P<id>\d+)/revoke', [ [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ self::class, 'revoke' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'keys:read' ) ] ] );
        register_rest_route( self::NS, '/keys/(?P<id>\d+)/rotate', [ [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ self::class, 'rotate' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'keys:rotate' ) ] ] );
    }

    public static function index( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $key  = $req->get_param( '_amp_key' );
        $rows = Api_Auth::list_keys( (int) $key['user_id'] );
        return self::ok( $req, [ 'keys' => $rows, 'total' => count( $rows ) ], 200, 'list_keys' );
    }

    public static function revoke( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id  = (int) $req->get_param( 'id' );
        $key = $req->get_param( '_amp_key' );
        global $wpdb;
        $owner = (int) $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}autonode_keys WHERE id=%d", $id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        if ( $owner !== (int) $key['user_id'] && ! user_can( (int) $key['user_id'], 'manage_options' ) ) {
            return self::fail( $req, new \WP_Error( 'amp_forbidden', 'Cannot revoke this key.', [ 'status' => 403 ] ), 'revoke_key' );
        }
        return self::ok( $req, [ 'revoked' => Api_Auth::revoke( $id ), 'id' => $id ], 200, 'revoke_key', $id, 'key' );
    }

    public static function rotate( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id     = (int) $req->get_param( 'id' );
        $result = Api_Auth::rotate( $id );
        if ( is_wp_error( $result ) ) {
            return self::fail( $req, $result, 'rotate_key' );
        }
        return self::ok( $req, $result, 200, 'rotate_key', $id, 'key' );
    }
}

/* â”€â”€â”€ System Controller â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
final class System_Controller extends Base_Controller {

    public static function register(): void {
        register_rest_route( self::NS, '/status',       [ [ 'methods' => \WP_REST_Server::READABLE, 'callback' => [ self::class, 'status' ],       'permission_callback' => fn( $r ) => self::guard( $r, 'system:read' ) ] ] );
        register_rest_route( self::NS, '/ping',         [ [ 'methods' => \WP_REST_Server::READABLE, 'callback' => [ self::class, 'ping' ],         'permission_callback' => '__return_true' ] ] );
        register_rest_route( self::NS, '/cron-health',  [ [ 'methods' => \WP_REST_Server::READABLE, 'callback' => [ self::class, 'cron_health' ],  'permission_callback' => fn( $r ) => self::guard( $r, 'system:read' ) ] ] );
        register_rest_route( self::NS, '/blocked-ips',  [ [ 'methods' => \WP_REST_Server::READABLE, 'callback' => [ self::class, 'blocked_ips' ],  'permission_callback' => fn( $r ) => self::guard( $r, 'system:read' ) ] ] );
    }

    public static function status( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $key = $req->get_param( '_amp_key' );
        return self::ok( $req, [
            'status'      => 'ok',
            'plugin'      => AUTONODE_VERSION,
            'wordpress'   => get_bloginfo( 'version' ),
            'php'         => PHP_VERSION,
            'site_url'    => get_site_url(),
            'api_base'    => get_site_url() . '/wp-json/' . AUTONODE_NS,
            'seo_plugin'  => \AutoNode\Rankmath_Handler::active_plugin(),
            'key_id'      => $key['id']          ?? null,
            'key_label'   => $key['label']        ?? '',
            'key_prefix'  => $key['key_prefix']   ?? '',
            'key_scopes'  => $key['scopes_array'] ?? [],
            'cron'        => Cron_Health::status()['status'],
            'server_time' => current_time( 'c', true ),
        ], 200, 'system_status' );
    }

    public static function ping( \WP_REST_Request $req ): \WP_REST_Response {
        return new \WP_REST_Response( [ 'pong' => true, 'time' => current_time( 'c', true ) ], 200 );
    }

    public static function cron_health( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        return self::ok( $req, Cron_Health::status(), 200, 'cron_health' );
    }

    public static function blocked_ips( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        return self::ok( $req, [ 'blocks' => Brute_Force::list_blocks() ], 200, 'blocked_ips' );
    }
}
