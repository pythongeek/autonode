<?php
namespace AutoNode;

defined( 'ABSPATH' ) || exit;

final class Installer {

    public static function activate(): void {
        self::create_tables();
        update_option( 'autonode_db_version', AUTONODE_DB_VERSION );
        add_option( 'autonode_settings', [
            'rate_limit'         => 120,
            'rate_window_sec'    => 60,
            'log_retention_days' => 90,
            'require_https'      => false,
            'enable_webhooks'    => true,
            'webhook_timeout_ms' => 5000,
            'max_retry_attempts' => 3,
            'brute_force_limit'  => 10,
            'brute_force_window' => 300,
            'min_capability'     => 'manage_options',
            'max_sideload_size'  => 20, /* MB */
        ] );
        flush_rewrite_rules();
    }

    public static function deactivate(): void {
        /* Clear every scheduled hook this plugin uses */
        $hooks = [
            'autonode_prune_logs',
            'autonode_prune_rate_limits',
            'autonode_fire_webhook',
            'autonode_retry_webhook',
            'autonode_cron_health_ping',
            'autonode_prune_brute_force',
        ];
        foreach ( $hooks as $h ) {
            wp_clear_scheduled_hook( $h );
        }
        flush_rewrite_rules();
    }

    public static function uninstall(): void {
        if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
            return;
        }
        global $wpdb;
        $tables = [
            'autonode_keys', 'autonode_log', 'autonode_rate', 'autonode_webhooks',
            'autonode_analytics', 'autonode_webhook_log', 'autonode_brute_force',
        ];
        foreach ( $tables as $t ) {
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$t}" ); // phpcs:ignore
        }
        delete_option( 'autonode_db_version' );
        delete_option( 'autonode_settings' );
        delete_option( 'autonode_cron_last_ping' );
        delete_option( 'autonode_apppass_dismissed' );
    }

    public static function create_tables(): void {
        global $wpdb;
        $c = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        /* â”€â”€ API Keys â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}autonode_keys (
            id            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            user_id       BIGINT UNSIGNED  NOT NULL,
            key_hash      CHAR(64)         NOT NULL,
            key_prefix    CHAR(12)         NOT NULL DEFAULT '',
            label         VARCHAR(191)     NOT NULL DEFAULT '',
            description   TEXT             DEFAULT NULL,
            environment   VARCHAR(20)      NOT NULL DEFAULT 'production',
            scopes        TEXT             NOT NULL DEFAULT '[]',
            ip_whitelist  TEXT             DEFAULT NULL,
            last_used_at  DATETIME         DEFAULT NULL,
            last_used_ip  VARCHAR(45)      DEFAULT NULL,
            expires_at    DATETIME         DEFAULT NULL,
            total_requests BIGINT UNSIGNED NOT NULL DEFAULT 0,
            created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
            revoked       TINYINT(1)       NOT NULL DEFAULT 0,
            revoked_at    DATETIME         DEFAULT NULL,
            PRIMARY KEY   (id),
            UNIQUE KEY    uq_hash (key_hash),
            KEY           idx_uid (user_id)
        ) $c;" );

        /* â”€â”€ Activity Log â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}autonode_log (
            id           BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            key_id       BIGINT UNSIGNED  DEFAULT NULL,
            user_id      BIGINT UNSIGNED  DEFAULT NULL,
            ip_address   VARCHAR(45)      NOT NULL DEFAULT '',
            method       VARCHAR(10)      NOT NULL DEFAULT '',
            endpoint     VARCHAR(512)     NOT NULL DEFAULT '',
            action       VARCHAR(128)     NOT NULL DEFAULT '',
            object_type  VARCHAR(64)      DEFAULT NULL,
            object_id    BIGINT UNSIGNED  DEFAULT NULL,
            http_status  SMALLINT         DEFAULT NULL,
            response_msg TEXT             DEFAULT NULL,
            duration_ms  SMALLINT UNSIGNED DEFAULT NULL,
            created_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY idx_key  (key_id),
            KEY idx_date (created_at)
        ) $c;" );

        /* â”€â”€ Rate Limit â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}autonode_rate (
            bucket       VARCHAR(128)      NOT NULL,
            hits         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            window_open  DATETIME          NOT NULL,
            PRIMARY KEY  (bucket)
        ) $c;" );

        /* â”€â”€ Webhooks â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}autonode_webhooks (
            id            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            key_id        BIGINT UNSIGNED  DEFAULT NULL,
            label         VARCHAR(191)     NOT NULL DEFAULT '',
            target_url    TEXT             NOT NULL,
            secret        VARCHAR(255)     DEFAULT NULL,
            events        TEXT             NOT NULL DEFAULT '[]',
            post_types    TEXT             NOT NULL DEFAULT '[\"post\",\"page\"]',
            active        TINYINT(1)       NOT NULL DEFAULT 1,
            last_fired_at DATETIME         DEFAULT NULL,
            last_status   SMALLINT         DEFAULT NULL,
            fire_count    BIGINT UNSIGNED  NOT NULL DEFAULT 0,
            fail_count    BIGINT UNSIGNED  NOT NULL DEFAULT 0,
            created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY   (id)
        ) $c;" );

        /* â”€â”€ Webhook Delivery Log â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}autonode_webhook_log (
            id            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
            webhook_id    BIGINT UNSIGNED  NOT NULL,
            event         VARCHAR(64)      NOT NULL DEFAULT '',
            object_id     BIGINT UNSIGNED  DEFAULT NULL,
            attempt       TINYINT UNSIGNED NOT NULL DEFAULT 1,
            http_status   SMALLINT         DEFAULT NULL,
            duration_ms   SMALLINT UNSIGNED DEFAULT NULL,
            response_body TEXT             DEFAULT NULL,
            error_message TEXT             DEFAULT NULL,
            created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY   (id),
            KEY idx_wh  (webhook_id),
            KEY idx_date (created_at)
        ) $c;" );

        /* â”€â”€ Analytics â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}autonode_analytics (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            bucket_hour DATETIME        NOT NULL,
            key_id      BIGINT UNSIGNED DEFAULT NULL,
            endpoint    VARCHAR(128)    NOT NULL DEFAULT '',
            method      VARCHAR(10)     NOT NULL DEFAULT '',
            http_status SMALLINT        NOT NULL DEFAULT 200,
            hits        INT UNSIGNED    NOT NULL DEFAULT 1,
            total_ms    INT UNSIGNED    NOT NULL DEFAULT 0,
            PRIMARY KEY (id),
            UNIQUE KEY  uq_bucket (bucket_hour, key_id, endpoint, method, http_status),
            KEY idx_hour (bucket_hour)
        ) $c;" );

        /* â”€â”€ Brute Force Tracking â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
        dbDelta( "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}autonode_brute_force (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            ip_address  VARCHAR(45)     NOT NULL,
            event_type  VARCHAR(32)     NOT NULL DEFAULT 'failed_auth',
            hits        SMALLINT UNSIGNED NOT NULL DEFAULT 1,
            window_open DATETIME        NOT NULL,
            blocked_until DATETIME      DEFAULT NULL,
            created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY  uq_ip_type (ip_address, event_type),
            KEY idx_ip  (ip_address)
        ) $c;" );
    }
}
