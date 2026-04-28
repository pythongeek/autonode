<?php
namespace AutoNode;

defined( 'ABSPATH' ) || exit;

final class Rate_Limiter {

    public static function init(): void {
        if ( ! wp_next_scheduled( 'autonode_prune_rate_limits' ) ) {
            wp_schedule_event( time(), 'hourly', 'autonode_prune_rate_limits' );
        }
        add_action( 'autonode_prune_rate_limits', [ __CLASS__, 'prune' ] );
    }

    /** @return array{allowed:bool,remaining:int,reset_at:int,limit:int} */
    public static function check( int $key_id ): array {
        global $wpdb;
        $s      = get_option( 'autonode_settings', [] );
        $limit  = (int) ( $s['rate_limit']      ?? 120 );
        $window = (int) ( $s['rate_window_sec'] ?? 60 );
        $bucket = 'k' . $key_id;
        $table  = "{$wpdb->prefix}autonode_rate";
        $now_ts = time();
        $now    = current_time( 'mysql', true );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table.
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT hits, window_open FROM $table WHERE bucket = %s", $bucket ), ARRAY_A );

        if ( ! $row ) {
            $wpdb->insert( $table, [ 'bucket' => $bucket, 'hits' => 1, 'window_open' => $now ], [ '%s', '%d', '%s' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            return [ 'allowed' => true, 'remaining' => $limit - 1, 'reset_at' => $now_ts + $window, 'limit' => $limit ];
        }

        $elapsed = $now_ts - (int) strtotime( $row['window_open'] );
        if ( $elapsed >= $window ) {
            $wpdb->update( $table, [ 'hits' => 1, 'window_open' => $now ], [ 'bucket' => $bucket ], [ '%d', '%s' ], [ '%s' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            return [ 'allowed' => true, 'remaining' => $limit - 1, 'reset_at' => $now_ts + $window, 'limit' => $limit ];
        }

        $hits     = (int) $row['hits'];
        $reset_at = (int) strtotime( $row['window_open'] ) + $window;
        if ( $hits >= $limit ) {
            return [ 'allowed' => false, 'remaining' => 0, 'reset_at' => $reset_at, 'limit' => $limit ];
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table.
        $wpdb->query( $wpdb->prepare( "UPDATE $table SET hits = hits + 1 WHERE bucket = %s", $bucket ) );
        return [ 'allowed' => true, 'remaining' => $limit - $hits - 1, 'reset_at' => $reset_at, 'limit' => $limit ];
    }

    public static function prune(): void {
        global $wpdb;
        $t = "{$wpdb->prefix}autonode_rate";
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- custom table bulk delete.
        $wpdb->query( "DELETE FROM $t WHERE window_open < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 2 HOUR)" );
    }
}
