<?php
namespace AutoNode;

defined( 'ABSPATH' ) || exit;

final class Plugin {

    private static bool $booted = false;

    public static function boot(): void {
        if ( self::$booted ) {
            return;
        }
        self::$booted = true;

        /* i18n */
        // Translations are loaded automatically by WordPress since 4.6 — no manual call needed.

        /* DB schema upgrade */
        if ( (int) get_option( 'autonode_db_version', 0 ) < AUTONODE_DB_VERSION ) {
            Installer::create_tables();
            update_option( 'autonode_db_version', AUTONODE_DB_VERSION );
        }

        /* Services — order matters (Brute_Force before Api_Auth) */
        Compatibility::init();
        Brute_Force::init();
        Api_Auth::init();
        Rate_Limiter::init();
        Activity_Logger::init();
        Webhook_Manager::init();
        Cron_Health::init();

        /* Licensing */
        Licensing\License_Admin::init();

        /* REST API */
        add_action( 'rest_api_init', [ __CLASS__, 'register_rest_routes' ] );
        add_filter( 'rest_post_dispatch', [ __CLASS__, 'security_headers' ], 10, 2 );

        /* Admin UI */
        if ( is_admin() ) {
            Admin\Menu::init();
        }

        /* Multisite: handle new site creation */
        if ( is_multisite() ) {
            add_action( 'wp_initialize_site', [ __CLASS__, 'init_new_site' ] );
        }
    }

    public static function init_new_site( \WP_Site $site ): void {
        if ( is_plugin_active_for_network( plugin_basename( AUTONODE_FILE ) ) ) {
            switch_to_blog( $site->blog_id );
            Installer::activate_blog();
            restore_current_blog();
        }
    }

    public static function register_rest_routes(): void {
        REST\Posts_Controller::register();
        REST\Pages_Controller::register();
        REST\SEO_Controller::register();
        REST\Meta_Controller::register();
        REST\Media_Controller::register();
        REST\Taxonomy_Controller::register();
        REST\Bulk_Controller::register();
        REST\Oneshot_Controller::register();
        REST\Webhook_Controller::register();
        REST\Analytics_Controller::register();
        REST\Openapi_Controller::register();
        REST\Keys_Controller::register();
        REST\System_Controller::register();
        REST\Discovery_Controller::register();
    }

    /**
     * Add security headers to all REST API responses.
     */
    public static function security_headers( \WP_REST_Response $resp, $server_or_req ): \WP_REST_Response {
        $resp->header( 'X-Content-Type-Options', 'nosniff' );
        $resp->header( 'X-REST-Version', AUTONODE_VERSION );
        return $resp;
    }
}
