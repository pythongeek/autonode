<?php
namespace AutoNode;

defined( 'ABSPATH' ) || exit;

/**
 * Cron Health Monitor
 *
 * Schedules a frequent "ping" cron event that updates an option timestamp.
 * The admin dashboard then compares last_ping to now â€” if more than 10 minutes
 * old, WP-Cron is broken (no traffic to trigger it) and the admin is warned.
 *
 * Also detects if DISABLE_WP_CRON is set (site using real cron), in which
 * case we skip the warning and show a "Real cron active" badge instead.
 */
final class Cron_Health {

    private const PING_HOOK     = 'autonode_cron_health_ping';
    private const OPTION_KEY    = 'autonode_cron_last_ping';
    private const STALE_SECONDS = 600; /* 10 minutes */

    public static function init(): void {
        /* Register custom interval BEFORE scheduling (WordPress needs to know the interval) */
        add_filter( 'cron_schedules', [ __CLASS__, 'add_5min_interval' ] );
        add_action( self::PING_HOOK, [ __CLASS__, 'record_ping' ] );

        /* Schedule ping every 5 minutes */
        if ( ! wp_next_scheduled( self::PING_HOOK ) ) {
            wp_schedule_event( time(), 'autonode_5min', self::PING_HOOK );
        }
    }

    public static function add_5min_interval( array $schedules ): array {
        if ( ! isset( $schedules['autonode_5min'] ) ) {
            $schedules['autonode_5min'] = [
                'interval' => 300,
                'display'  => 'Every 5 minutes (AMP CM health check)',
            ];
        }
        return $schedules;
    }

    public static function record_ping(): void {
        update_option( self::OPTION_KEY, time(), false );
    }

    /**
     * Returns full health status.
     * @return array{
     *   status: 'ok'|'stale'|'real_cron'|'disabled',
     *   last_ping: int|null,
     *   stale_seconds: int,
     *   message: string,
     *   next_event: string|null,
     *   scheduled_hooks: array
     * }
     */
    public static function status(): array {
        $last_ping = (int) get_option( self::OPTION_KEY, 0 );
        $age       = $last_ping ? time() - $last_ping : PHP_INT_MAX;
        $next      = wp_next_scheduled( self::PING_HOOK );

        /* If real server cron is configured, WP-Cron is bypassed but still works */
        if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
            return [
                'status'          => 'real_cron',
                'last_ping'       => $last_ping ?: null,
                'stale_seconds'   => self::STALE_SECONDS,
                'message'         => __( 'Server cron active (DISABLE_WP_CRON = true). Webhooks and jobs run on schedule.', 'autonode' ),
                'next_event'      => $next ? sprintf( __( '%s from now', 'autonode' ), human_time_diff( $next ) ) : null,
                'scheduled_hooks' => self::list_hooks(),
            ];
        }

        if ( $last_ping === 0 ) {
            $msg = __( 'Cron has never fired. Either the site has no traffic or WP-Cron is disabled.', 'autonode' );
            $st  = 'stale';
        } elseif ( $age > self::STALE_SECONDS ) {
            $mins = (int) ceil( $age / 60 );
            $msg  = sprintf( 
                /* translators: %d: number of minutes */
                __( 'Last cron ping was %d minutes ago — WP-Cron may not be running. Add a real server cron job.', 'autonode' ), 
                $mins 
            );
            $st   = 'stale';
        } else {
            $mins = (int) ceil( $age / 60 );
            $msg  = sprintf( 
                /* translators: %d: number of minutes */
                __( 'Cron is healthy. Last ping %d minute(s) ago.', 'autonode' ), 
                $mins 
            );
            $st   = 'ok';
        }

        return [
            'status'          => $st,
            'last_ping'       => $last_ping ?: null,
            'age_seconds'     => $last_ping ? $age : null,
            'stale_seconds'   => self::STALE_SECONDS,
            'message'         => $msg,
            'next_event'      => $next ? sprintf( __( '%s from now', 'autonode' ), human_time_diff( $next ) ) : __( 'Not scheduled', 'autonode' ),
            'scheduled_hooks' => self::list_hooks(),
        ];
    }

    private static function list_hooks(): array {
        $hooks = [
            'autonode_prune_logs'        => __( 'Prune activity log (daily)', 'autonode' ),
            'autonode_prune_rate_limits' => __( 'Prune rate limit buckets (hourly)', 'autonode' ),
            'autonode_fire_webhook'      => __( 'Webhook delivery (single)', 'autonode' ),
            'autonode_retry_webhook'     => __( 'Webhook retry (single)', 'autonode' ),
            'autonode_cron_health_ping'  => __( 'Health ping (5 min)', 'autonode' ),
            'autonode_prune_brute_force' => __( 'Prune brute-force log (hourly)', 'autonode' ),
        ];
        $result = [];
        foreach ( $hooks as $hook => $label ) {
            $next = wp_next_scheduled( $hook );
            $result[] = [
                'hook'      => $hook,
                'label'     => $label,
                'scheduled' => (bool) $next,
                'next_run'  => $next ? sprintf( __( '%s from now', 'autonode' ), human_time_diff( $next ) ) : '—',
            ];
        }
        return $result;
    }
}
