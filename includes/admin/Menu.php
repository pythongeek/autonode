<?php
namespace AutoNode\Admin;

defined( 'ABSPATH' ) || exit;

final class Menu {

    private static bool $initialized = false;

    public static function init(): void {
        if ( self::$initialized ) return;
        self::$initialized = true;

        add_action( 'admin_menu',            [ __CLASS__, 'register_menus' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue' ] );
        add_action( 'admin_body_class',      [ __CLASS__, 'body_class' ] );
        add_action( 'wp_ajax_autonode_dismiss_apppass_notice', [ __CLASS__, 'dismiss_apppass' ] );

        foreach ( [ 'create_key', 'revoke_key', 'rotate_key', 'save_settings', 'get_chart_data', 'create_webhook', 'delete_webhook', 'test_webhook', 'toggle_webhook', 'unblock_ip', 'clear_logs', 'export_logs', 'export_settings', 'import_settings' ] as $a ) {
            add_action( "wp_ajax_autonode_{$a}", [ Ajax_Handler::class, $a ] );
        }
    }

    public static function register_menus(): void {
        $cap = self::get_cap();
        add_menu_page( 'AutoNode WP', 'AutoNode WP', $cap, 'amp-cm', [ __CLASS__, 'page_dashboard' ], self::svg_icon(), 80 );
        add_submenu_page( 'amp-cm', esc_html__( 'Dashboard', 'autonode-pro'),     esc_html__( 'Dashboard', 'autonode-pro'),     $cap, 'amp-cm',           [ __CLASS__, 'page_dashboard' ] );
        add_submenu_page( 'amp-cm', esc_html__( 'API Keys', 'autonode-pro'),      esc_html__( 'API Keys', 'autonode-pro'),      $cap, 'autonode-keys',      [ __CLASS__, 'page_keys' ] );
        add_submenu_page( 'amp-cm', esc_html__( 'Webhooks', 'autonode-pro'),      esc_html__( 'Webhooks', 'autonode-pro'),      $cap, 'autonode-webhooks',  [ __CLASS__, 'page_webhooks' ] );
        add_submenu_page( 'amp-cm', esc_html__( 'n8n Templates', 'autonode-pro'), esc_html__( 'n8n Templates', 'autonode-pro'), $cap, 'autonode-templates', [ __CLASS__, 'page_templates' ] );
        add_submenu_page( 'amp-cm', esc_html__( 'Activity Log', 'autonode-pro'),  esc_html__( 'Activity Log', 'autonode-pro'),  $cap, 'autonode-logs',      [ __CLASS__, 'page_logs' ] );
        add_submenu_page( 'amp-cm', esc_html__( 'Settings', 'autonode-pro'),      esc_html__( 'Settings', 'autonode-pro'),      $cap, 'autonode-settings',  [ __CLASS__, 'page_settings' ] );
        add_submenu_page( 'amp-cm', esc_html__( 'API Docs', 'autonode-pro'),      esc_html__( 'API Docs', 'autonode-pro'),      $cap, 'autonode-docs',      [ __CLASS__, 'page_docs' ] );
        add_submenu_page( 'amp-cm', esc_html__( 'Compatibility', 'autonode-pro'), esc_html__( 'Compatibility', 'autonode-pro'), $cap, 'autonode-compat',    [ __CLASS__, 'page_compat' ] );
    }

    public static function enqueue( string $hook ): void {
        if ( ! str_contains( $hook, 'amp-cm' ) && ! str_contains( $hook, 'autonode-pro') ) {
            return;
        }

        wp_enqueue_style(  'amp-cm',       AUTONODE_URL . 'assets/css/admin.css', array(), AUTONODE_VERSION );
        wp_enqueue_script( 'autonode-chart', AUTONODE_URL . 'assets/js/chart.umd.min.js', array(), '4.4.3', true );
        wp_enqueue_script( 'autonode-admin', AUTONODE_URL . 'assets/js/admin.js', array( 'jquery', 'autonode-chart' ), AUTONODE_VERSION, true );

        $data = [
            'nonce'          => wp_create_nonce( 'autonode_admin' ),
            'restNonce'      => wp_create_nonce( 'wp_rest' ),
            'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
            'apiBase'        => get_site_url() . '/wp-json/' . AUTONODE_NS,
            'scopes'         => \AutoNode\Api_Auth::SCOPES,
            'presets'        => \AutoNode\Api_Auth::PRESET_SCOPES,
            'events'         => \AutoNode\Webhook_Manager::get_events(),
            'seoPlugin'      => \AutoNode\Rankmath_Handler::active_plugin(),
            'version'        => AUTONODE_VERSION,
            'isAgenticTheme' => \AutoNode\Compatibility::is_agentic_theme(),
        ];

        if ( str_contains( $hook, 'amp-cm' ) ) {
            $hourly = \AutoNode\Activity_Logger::hourly_stats( 24 );
            $data['chart'] = [
                'hourly' => $hourly,
                'hits'   => array_map( fn( $r ) => (int) $r['hits'], $hourly ),
                'errs'   => array_map( fn( $r ) => (int) $r['errors'], $hourly ),
                'ms'     => array_map( fn( $r ) => $r['total_ms'] && $r['hits'] ? round( $r['total_ms'] / $r['hits'], 1 ) : 0, $hourly ),
            ];
        }

        wp_localize_script( 'autonode-admin', 'ampCM', $data );
    }

    public static function body_class( string $classes ): string {
        $hook = get_current_screen()->id;
        if ( ! str_contains( $hook, 'amp-cm' ) && ! str_contains( $hook, 'autonode-pro') ) {
            return $classes;
        }

        $s = get_option( 'autonode_settings', [] );
        $mode = $s['ui_mode'] ?? 'standard'; // Default to native WordPress UI for marketplace compliance.

        if ( 'dark' === $mode ) {
            $classes .= ' amp-mode-dark';
        }

        return $classes;
    }

    /* â”€â”€ Page renderers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public static function page_dashboard(): void {
        self::cap();
        $autonode_summary     = \AutoNode\Activity_Logger::summary();
        $autonode_hourly      = \AutoNode\Activity_Logger::hourly_stats( 24 );
        $autonode_top_ep      = \AutoNode\Activity_Logger::top_endpoints( 7 );
        $autonode_keys        = \AutoNode\Api_Auth::list_keys();
        $autonode_active_keys = count( array_filter( $autonode_keys, fn( $k ) => ! $k['revoked'] ) );
        $autonode_compat      = \AutoNode\Compatibility::status();
        require AUTONODE_DIR . 'includes/admin/views/dashboard.php';
    }

    public static function page_keys(): void {
        self::cap();
        $autonode_keys = \AutoNode\Api_Auth::list_keys( 0, true );
        require AUTONODE_DIR . 'includes/admin/views/keys.php';
    }

    public static function page_webhooks(): void {
        self::cap();
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table not cacheable via WP API.
        $autonode_webhooks = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}autonode_webhooks ORDER BY created_at DESC", ARRAY_A ) ?: array();
        require AUTONODE_DIR . 'includes/admin/views/webhooks.php';
    }

    public static function page_logs(): void {
        self::cap();
        if ( isset( $_GET['_an_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_an_nonce'] ) ), 'autonode_logs_filter' ) ) {
            $autonode_offset = isset( $_GET['offset'] ) ? absint( wp_unslash( $_GET['offset'] ) ) : 0;
        } else {
            $autonode_offset = 0;
        }
        $autonode_logs = \AutoNode\Activity_Logger::query( [ 'limit' => 100, 'offset' => $autonode_offset ] );
        require AUTONODE_DIR . 'includes/admin/views/logs.php';
    }

    public static function page_settings(): void {
        self::cap();
        $autonode_settings = get_option( 'autonode_settings', [] );
        $autonode_compat   = \AutoNode\Compatibility::status();
        require AUTONODE_DIR . 'includes/admin/views/settings.php';
    }

    public static function page_templates(): void {
        self::cap();
        require AUTONODE_DIR . 'includes/admin/views/n8n-templates.php';
    }

    public static function page_docs(): void {
        self::cap();
        $api_base = get_site_url() . '/wp-json/' . AUTONODE_NS;
        require AUTONODE_DIR . 'includes/admin/views/docs.php';
    }

    public static function page_compat(): void {
        self::cap();
        $autonode_compat = \AutoNode\Compatibility::status();
        require AUTONODE_DIR . 'includes/admin/views/compat.php';
    }

    public static function dismiss_apppass(): void {
        check_ajax_referer( 'autonode_admin', 'nonce' );
        if ( current_user_can( self::get_cap() ) ) update_option( 'autonode_apppass_dismissed', true );
        wp_send_json_success();
    }

    private static function cap(): void {
        if ( ! current_user_can( self::get_cap() ) ) wp_die( esc_html__( 'Insufficient permissions.', 'autonode-pro') );
    }

    public static function get_cap(): string {
        $s = get_option( 'autonode_settings', [] );
        return (string) ( $s['min_capability'] ?? 'manage_options' );
    }

    private static function svg_icon(): string {
        $logo_path = AUTONODE_DIR . 'assets/logo.png';
        if ( file_exists( $logo_path ) ) {
            $data = file_get_contents( $logo_path );
            if ( $data !== false ) {
                $type = 'image/png';
                $base = base64_encode( $data );
                return "data:{$type};base64,{$base}";
            }
        }
        return 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128">' .
            '<rect width="128" height="128" rx="16" fill="#1e3a5f"/>' .
            '<text x="64" y="78" font-family="Arial,sans-serif" font-size="52" font-weight="bold" fill="white" text-anchor="middle">AN</text>' .
            '</svg>'
        );
    }
}
