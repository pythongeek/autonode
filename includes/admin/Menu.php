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
        add_action( 'wp_ajax_autonode_dismiss_apppass_notice', [ __CLASS__, 'dismiss_apppass' ] );

        foreach ( [ 'create_key', 'revoke_key', 'rotate_key', 'save_settings', 'get_chart_data', 'create_webhook', 'delete_webhook', 'test_webhook', 'toggle_webhook', 'unblock_ip', 'clear_logs' ] as $a ) {
            add_action( "wp_ajax_autonode_{$a}", [ Ajax_Handler::class, $a ] );
        }
    }

    public static function register_menus(): void {
        $cap = self::get_cap();
        add_menu_page( 'AutoNode WP', 'AutoNode WP', $cap, 'amp-cm', [ __CLASS__, 'page_dashboard' ], self::svg_icon(), 80 );
        add_submenu_page( 'amp-cm', esc_html__( 'Dashboard', 'autonode' ),     esc_html__( 'Dashboard', 'autonode' ),     $cap, 'amp-cm',           [ __CLASS__, 'page_dashboard' ] );
        add_submenu_page( 'amp-cm', esc_html__( 'API Keys', 'autonode' ),      esc_html__( 'API Keys', 'autonode' ),      $cap, 'autonode-keys',      [ __CLASS__, 'page_keys' ] );
        add_submenu_page( 'amp-cm', esc_html__( 'Webhooks', 'autonode' ),      esc_html__( 'Webhooks', 'autonode' ),      $cap, 'autonode-webhooks',  [ __CLASS__, 'page_webhooks' ] );
        add_submenu_page( 'amp-cm', esc_html__( 'n8n Templates', 'autonode' ), esc_html__( 'n8n Templates', 'autonode' ), $cap, 'autonode-templates', [ __CLASS__, 'page_templates' ] );
        add_submenu_page( 'amp-cm', esc_html__( 'Activity Log', 'autonode' ),  esc_html__( 'Activity Log', 'autonode' ),  $cap, 'autonode-logs',      [ __CLASS__, 'page_logs' ] );
        add_submenu_page( 'amp-cm', esc_html__( 'Settings', 'autonode' ),      esc_html__( 'Settings', 'autonode' ),      $cap, 'autonode-settings',  [ __CLASS__, 'page_settings' ] );
        add_submenu_page( 'amp-cm', esc_html__( 'API Docs', 'autonode' ),      esc_html__( 'API Docs', 'autonode' ),      $cap, 'autonode-docs',      [ __CLASS__, 'page_docs' ] );
        add_submenu_page( 'amp-cm', esc_html__( 'Compatibility', 'autonode' ), esc_html__( 'Compatibility', 'autonode' ), $cap, 'autonode-compat',    [ __CLASS__, 'page_compat' ] );
    }

    public static function enqueue( string $hook ): void {
        if ( ! str_contains( $hook, 'amp-cm' ) && ! str_contains( $hook, 'autonode' ) ) {
            return;
        }

        wp_enqueue_style(  'amp-cm',       AUTONODE_URL . 'assets/css/admin.css', array(), AUTONODE_VERSION );
        wp_enqueue_script( 'autonode-chart', AUTONODE_URL . 'assets/js/chart.umd.min.js', array(), '4.4.3', true );
        wp_enqueue_script( 'autonode-admin', AUTONODE_URL . 'assets/js/admin.js', array( 'jquery', 'autonode-chart' ), AUTONODE_VERSION, true );

        wp_localize_script( 'autonode-admin', 'ampCM', [
            'nonce'          => wp_create_nonce( 'autonode_admin' ),
            'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
            'apiBase'        => get_site_url() . '/wp-json/' . AUTONODE_NS,
            'scopes'         => \AutoNode\Api_Auth::SCOPES,
            'presets'        => \AutoNode\Api_Auth::PRESET_SCOPES,
            'events'         => \AutoNode\Webhook_Manager::get_events(),
            'seoPlugin'      => \AutoNode\Rankmath_Handler::active_plugin(),
            'version'        => AUTONODE_VERSION,
            'isAgenticTheme' => \AutoNode\Compatibility::is_agentic_theme(),
        ] );
    }

    /* â”€â”€ Page renderers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public static function page_dashboard(): void {
        self::cap();
        $summary     = \AutoNode\Activity_Logger::summary();
        $hourly      = \AutoNode\Activity_Logger::hourly_stats( 24 );
        $top_ep      = \AutoNode\Activity_Logger::top_endpoints( 7 );
        $keys        = \AutoNode\Api_Auth::list_keys();
        $active_keys = count( array_filter( $keys, fn( $k ) => ! $k['revoked'] ) );
        $compat      = \AutoNode\Compatibility::status();
        require AUTONODE_DIR . 'includes/admin/views/dashboard.php';
    }

    public static function page_keys(): void {
        self::cap();
        $keys = \AutoNode\Api_Auth::list_keys( 0, true );
        require AUTONODE_DIR . 'includes/admin/views/keys.php';
    }

    public static function page_webhooks(): void {
        self::cap();
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table not cacheable via WP API.
        $webhooks = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}autonode_webhooks ORDER BY created_at DESC", ARRAY_A ) ?: array();
        require AUTONODE_DIR . 'includes/admin/views/webhooks.php';
    }

    public static function page_logs(): void {
        self::cap();
        $offset = isset( $_GET['offset'] ) ? absint( $_GET['offset'] ) : 0; // phpcs:ignore
        $logs   = \AutoNode\Activity_Logger::query( [ 'limit' => 100, 'offset' => $offset ] );
        require AUTONODE_DIR . 'includes/admin/views/logs.php';
    }

    public static function page_settings(): void {
        self::cap();
        $settings = get_option( 'autonode_settings', [] );
        $compat   = \AutoNode\Compatibility::status();
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
        $compat = \AutoNode\Compatibility::status();
        require AUTONODE_DIR . 'includes/admin/views/compat.php';
    }

    public static function dismiss_apppass(): void {
        check_ajax_referer( 'autonode_admin', 'nonce' );
        if ( current_user_can( self::get_cap() ) ) update_option( 'autonode_apppass_dismissed', true );
        wp_send_json_success();
    }

    private static function cap(): void {
        if ( ! current_user_can( self::get_cap() ) ) wp_die( esc_html__( 'Insufficient permissions.', 'autonode' ) );
    }

    public static function get_cap(): string {
        $s = get_option( 'autonode_settings', [] );
        return (string) ( $s['min_capability'] ?? 'manage_options' );
    }

    private static function svg_icon(): string {
        return 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">' .
            '<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="#28CCCD" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>' .
            '</svg>'
        );
    }
}
