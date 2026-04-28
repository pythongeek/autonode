<?php
namespace AutoNode;

defined( 'ABSPATH' ) || exit;

/**
 * Brute-force protection.
 * Tracks failed auth attempts per IP with a sliding window.
 * After limit is exceeded the IP is blocked for escalating durations.
 */
final class Brute_Force {

    public static function init(): void {
        if ( ! wp_next_scheduled( 'autonode_prune_brute_force' ) ) {
            wp_schedule_event( time(), 'hourly', 'autonode_prune_brute_force' );
        }
        add_action( 'autonode_prune_brute_force', [ __CLASS__, 'prune' ] );
    }

    /**
     * Check if this IP is currently blocked.
     * Returns WP_Error if blocked, true if allowed.
     */
    public static function check_ip( string $ip ): bool|\WP_Error {
        global $wpdb;
        $t   = "{$wpdb->prefix}autonode_brute_force";
        $row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table.
            $wpdb->prepare( "SELECT * FROM $t WHERE ip_address = %s AND event_type = 'failed_auth'", $ip ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            ARRAY_A
        );
        if ( ! $row ) {
            return true;
        }
        if ( ! empty( $row['blocked_until'] ) && strtotime( $row['blocked_until'] ) > time() ) {
            $secs = strtotime( $row['blocked_until'] ) - time();
            return new \WP_Error(
                'amp_ip_blocked',
                sprintf( 'Too many failed attempts. Retry in %d seconds.', $secs ),
                [ 'status' => 429 ]
            );
        }
        return true;
    }

    /**
     * Record a failed authentication attempt.
     * Blocks the IP if limit is exceeded (escalating durations).
     */
    public static function record_failure( string $ip ): void {
        global $wpdb;
        $s      = get_option( 'autonode_settings', [] );
        $limit  = (int) ( $s['brute_force_limit']  ?? 10 );
        $window = (int) ( $s['brute_force_window'] ?? 300 ); // 5 min default
        $t      = "{$wpdb->prefix}autonode_brute_force";
        $now    = current_time( 'mysql', true );

        $row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table.
            $wpdb->prepare( "SELECT * FROM $t WHERE ip_address = %s AND event_type = 'failed_auth'", $ip ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            ARRAY_A
        );

        if ( ! $row ) {
            $wpdb->insert( $t, [ // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
                'ip_address'  => $ip,
                'event_type'  => 'failed_auth',
                'hits'        => 1,
                'window_open' => $now,
                'created_at'  => $now,
            ], [ '%s', '%s', '%d', '%s', '%s' ] );
            return;
        }

        /* If window has expired, reset counter */
        $elapsed = time() - (int) strtotime( $row['window_open'] );
        if ( $elapsed >= $window ) {
            $wpdb->update( $t, [ // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                'hits'          => 1,
                'window_open'   => $now,
                'blocked_until' => null,
            ], [ 'ip_address' => $ip, 'event_type' => 'failed_auth' ],
            [ '%d', '%s', '%s' ], [ '%s', '%s' ] );
            return;
        }

        $new_hits = (int) $row['hits'] + 1;
        $blocked_until = null;

        if ( $new_hits >= $limit ) {
            /* Escalating block: 5m â†’ 30m â†’ 2h â†’ 24h */
            $block_secs = match ( true ) {
                $new_hits >= $limit * 5 => 86400,  // 24h
                $new_hits >= $limit * 3 => 7200,   // 2h
                $new_hits >= $limit * 2 => 1800,   // 30m
                default                 => 300,    // 5m
            };
            $blocked_until = gmdate( 'Y-m-d H:i:s', time() + $block_secs );
        }

        $wpdb->update( $t, // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            [ 'hits' => $new_hits, 'blocked_until' => $blocked_until ],
            [ 'ip_address' => $ip, 'event_type' => 'failed_auth' ],
            [ '%d', '%s' ], [ '%s', '%s' ]
        );
    }

    /** Clear record after a successful auth (IP is no longer suspect) */
    public static function clear( string $ip ): void {
        global $wpdb;
        $t = "{$wpdb->prefix}autonode_brute_force";
        $wpdb->delete( $t, [ 'ip_address' => $ip, 'event_type' => 'failed_auth' ], [ '%s', '%s' ] ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
    }

    /** Prune old entries not touched in 24h */
    public static function prune(): void {
        global $wpdb;
        $t = "{$wpdb->prefix}autonode_brute_force";
        $wpdb->query( "DELETE FROM $t WHERE window_open < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 24 HOUR) AND (blocked_until IS NULL OR blocked_until < UTC_TIMESTAMP())" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
    }

    /** Returns all current blocks for the admin UI */
    public static function list_blocks(): array {
        global $wpdb;
        $t = "{$wpdb->prefix}autonode_brute_force";
        return $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            "SELECT * FROM $t WHERE blocked_until > UTC_TIMESTAMP() ORDER BY hits DESC LIMIT 100", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            ARRAY_A
        ) ?: [];
    }
}
