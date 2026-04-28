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

        /* DB schema upgrade */
        if ( (int) get_option( 'autonode_db_version', 0 ) < AUTONODE_DB_VERSION ) {
            Installer::create_tables();
            update_option( 'autonode_db_version', AUTONODE_DB_VERSION );
        }

        /* Services â€” order matters (Brute_Force before Api_Auth) */
        Compatibility::init();
        Brute_Force::init();
        Api_Auth::init();
        Rate_Limiter::init();
        Activity_Logger::init();
        Webhook_Manager::init();
        Cron_Health::init();

        /* REST API */
        add_action( 'rest_api_init', [ __CLASS__, 'register_rest_routes' ] );

        /* Admin UI */
        if ( is_admin() ) {
            Admin\Menu::init();
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
    }
}
