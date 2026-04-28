<?php
namespace AutoNode;

defined( 'ABSPATH' ) || exit;

final class Activity_Logger {

    public static function init(): void {
        if ( ! wp_next_scheduled( 'autonode_prune_logs' ) ) {
            wp_schedule_event( time(), 'daily', 'autonode_prune_logs' );
        }
        add_action( 'autonode_prune_logs', array( __CLASS__, 'prune' ) );
    }

    public static function write( array $d ): void {
        global $wpdb;

        $key_id  = isset( $d['key_id'] )    ? (int) $d['key_id']    : null;
        $user_id = isset( $d['user_id'] )   ? (int) $d['user_id']   : null;
        $obj_id  = isset( $d['object_id'] ) ? (int) $d['object_id'] : null;
        $status  = isset( $d['http_status'] ) ? (int) $d['http_status'] : 200;
        $ms      = isset( $d['duration_ms'] ) ? (int) $d['duration_ms'] : null;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table, not cacheable.
        $wpdb->insert( "{$wpdb->prefix}autonode_log", array(
            'key_id'       => $key_id,
            'user_id'      => $user_id,
            'ip_address'   => Api_Auth::client_ip(),
            'method'       => strtoupper( sanitize_key( $d['method'] ?? 'GET' ) ),
            'endpoint'     => sanitize_text_field( $d['endpoint'] ?? '' ),
            'action'       => sanitize_key( $d['action'] ?? '' ),
            'object_type'  => isset( $d['object_type'] ) ? sanitize_key( $d['object_type'] ) : null,
            'object_id'    => $obj_id,
            'http_status'  => $status,
            'response_msg' => isset( $d['response_msg'] ) ? substr( sanitize_text_field( $d['response_msg'] ), 0, 500 ) : null,
            'duration_ms'  => $ms,
            'created_at'   => current_time( 'mysql', true ),
        ), array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d', '%s' ) );

        /* Analytics bucket */
        if ( null !== $key_id ) {
            $bucket = gmdate( 'Y-m-d H:00:00' );
            $ep     = substr( sanitize_key( str_replace( '/', '_', $d['endpoint'] ?? '' ) ), 0, 128 );
            $ana    = "{$wpdb->prefix}autonode_analytics";
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table, ON DUPLICATE KEY not supportable via WP API.
            $wpdb->query( $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}autonode_analytics (bucket_hour,key_id,endpoint,method,http_status,hits,total_ms) VALUES(%s,%d,%s,%s,%d,1,%d) ON DUPLICATE KEY UPDATE hits=hits+1, total_ms=total_ms+%d",
                $bucket, $key_id, $ep, strtoupper( sanitize_key( $d['method'] ?? 'GET' ) ), $status, $ms ?? 0, $ms ?? 0
            ) );
        }
    }

    public static function query( array $args = array() ): array {
        global $wpdb;
        $t = "{$wpdb->prefix}autonode_log";
        $w = array( '1=1' );
        $v = array();
        if ( ! empty( $args['key_id'] ) )     { $w[] = 'key_id = %d';      $v[] = (int) $args['key_id']; }
        if ( ! empty( $args['action'] ) )      { $w[] = 'action = %s';      $v[] = $args['action']; }
        if ( ! empty( $args['object_type'] ) ) { $w[] = 'object_type = %s'; $v[] = $args['object_type']; }
        if ( ! empty( $args['http_status'] ) ) { $w[] = 'http_status = %d'; $v[] = (int) $args['http_status']; }
        if ( ! empty( $args['date_from'] ) )   { $w[] = 'created_at >= %s'; $v[] = $args['date_from']; }

        $limit  = min( (int) ( $args['limit'] ?? 50 ), 500 );
        $offset = (int) ( $args['offset'] ?? 0 );
        $clause = implode( ' AND ', $w );
        $v[]    = $limit;
        $v[]    = $offset;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber, PluginCheck.Security.DirectDB.UnescapedDBParameter -- dynamic WHERE built from allow-listed columns above; custom table.
        return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}autonode_log WHERE $clause ORDER BY created_at DESC LIMIT %d OFFSET %d", ...$v ), ARRAY_A ) ?: array();
    }

    public static function summary(): array {
        global $wpdb;
        $log = "{$wpdb->prefix}autonode_log";
        $ana = "{$wpdb->prefix}autonode_analytics";
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        return array(
            'requests_today'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}autonode_log WHERE created_at >= CURDATE()" ),
            'requests_week'   => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}autonode_log WHERE created_at >= DATE_SUB(UTC_TIMESTAMP(),INTERVAL 7 DAY)" ),
            'errors_24h'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}autonode_log WHERE http_status >= 400 AND created_at >= DATE_SUB(UTC_TIMESTAMP(),INTERVAL 24 HOUR)" ),
            'avg_response_ms' => (float) round( (float) $wpdb->get_var( "SELECT AVG(total_ms/hits) FROM {$wpdb->prefix}autonode_analytics WHERE bucket_hour >= DATE_SUB(UTC_TIMESTAMP(),INTERVAL 24 HOUR)" ), 1 ),
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    }

    public static function hourly_stats( int $hours = 24, ?int $key_id = null ): array {
        global $wpdb;
        $cond = $key_id ? $wpdb->prepare( 'AND key_id = %d', $key_id ) : '';
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT bucket_hour, SUM(hits) as hits, SUM(total_ms) as total_ms, SUM(CASE WHEN http_status >= 400 THEN hits ELSE 0 END) as errors FROM {$wpdb->prefix}autonode_analytics WHERE bucket_hour >= DATE_SUB(UTC_TIMESTAMP(),INTERVAL %d HOUR) $cond GROUP BY bucket_hour ORDER BY bucket_hour ASC",
            $hours
        ), ARRAY_A ) ?: array();
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
    }

    public static function top_endpoints( int $days = 7 ): array {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table.
        return $wpdb->get_results( $wpdb->prepare(
            "SELECT endpoint, method, SUM(hits) as hits, AVG(total_ms/hits) as avg_ms FROM {$wpdb->prefix}autonode_analytics WHERE bucket_hour >= DATE_SUB(UTC_TIMESTAMP(),INTERVAL %d DAY) GROUP BY endpoint,method ORDER BY hits DESC LIMIT 10",
            $days
        ), ARRAY_A ) ?: array();
    }

    public static function prune(): void {
        global $wpdb;
        $days = (int) get_option( 'autonode_log_retention_days', 90 );

        /* Prune standard logs */
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- bulk delete on custom table.
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}autonode_log WHERE created_at < DATE_SUB(UTC_TIMESTAMP(),INTERVAL %d DAY)", $days ) );

        /* Prune analytics (uses bucket_hour instead of created_at) */
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- bulk delete on custom table.
        $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}autonode_analytics WHERE bucket_hour < DATE_SUB(UTC_TIMESTAMP(),INTERVAL %d DAY)", $days ) );
    }
}