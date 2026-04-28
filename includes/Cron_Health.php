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
                'message'         => 'Server cron active (DISABLE_WP_CRON = true). Webhooks and jobs run on schedule.',
                'next_event'      => $next ? human_time_diff( $next ) . ' from now' : null,
                'scheduled_hooks' => self::list_hooks(),
            ];
        }

        if ( $last_ping === 0 ) {
            $msg = 'Cron has never fired. Either the site has no traffic or WP-Cron is disabled.';
            $st  = 'stale';
        } elseif ( $age > self::STALE_SECONDS ) {
            $mins = (int) ceil( $age / 60 );
            $msg  = "Last cron ping was {$mins} minutes ago â€” WP-Cron may not be running. Add a real server cron job.";
            $st   = 'stale';
        } else {
            $mins = (int) ceil( $age / 60 );
            $msg  = "Cron is healthy. Last ping {$mins} minute(s) ago.";
            $st   = 'ok';
        }

        return [
            'status'          => $st,
            'last_ping'       => $last_ping ?: null,
            'age_seconds'     => $last_ping ? $age : null,
            'stale_seconds'   => self::STALE_SECONDS,
            'message'         => $msg,
            'next_event'      => $next ? human_time_diff( $next ) . ' from now' : 'Not scheduled',
            'scheduled_hooks' => self::list_hooks(),
        ];
    }

    private static function list_hooks(): array {
        $hooks = [
            'autonode_prune_logs'        => 'Prune activity log (daily)',
            'autonode_prune_rate_limits' => 'Prune rate limit buckets (hourly)',
            'autonode_fire_webhook'      => 'Webhook delivery (single)',
            'autonode_retry_webhook'     => 'Webhook retry (single)',
            'autonode_cron_health_ping'  => 'Health ping (5 min)',
            'autonode_prune_brute_force' => 'Prune brute-force log (hourly)',
        ];
        $result = [];
        foreach ( $hooks as $hook => $label ) {
            $next = wp_next_scheduled( $hook );
            $result[] = [
                'hook'      => $hook,
                'label'     => $label,
                'scheduled' => (bool) $next,
                'next_run'  => $next ? human_time_diff( $next ) . ' from now' : 'â€”',
            ];
        }
        return $result;
    }
}
