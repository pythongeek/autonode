<?php
/**
 * Plugin Name: AutoNode
 * Plugin URI: https://autonode.wikiofautomation.com
 * Description: The ultimate REST API bridge for n8n. Manage posts, Rank Math SEO, media, and webhooks with advanced security and OpenAPI support.
 * Version: 4.2.0
 * Author: BdowneerTech
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires at least: 6.3
 * Requires PHP: 8.1
 * Text Domain: autonode
 */

defined( 'ABSPATH' ) || exit;

/* Temporarily add debug logging for fatal errors */
register_shutdown_function( function() {
    $error = error_get_last();
    if ( $error && in_array( $error['type'], [ E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR ] ) ) {
        $log = gmdate( 'Y-m-d H:i:s' ) . " FATAL: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'] . "\n";
        file_put_contents( __DIR__ . '/plugin-debug.log', $log, FILE_APPEND );
    }
});

define( 'AUTONODE_VERSION',    '4.2.0' );
define( 'AUTONODE_FILE',       __FILE__ );
define( 'AUTONODE_DIR',        plugin_dir_path( __FILE__ ) );
define( 'AUTONODE_URL',        plugin_dir_url( __FILE__ ) );
define( 'AUTONODE_NS',         'autonode/v1' );
define( 'AUTONODE_DB_VERSION', 6 );  /* Bumped: webhook_log + brute_force tables, secret column rename */

/**
 * Explicit file loading Ã¢â‚¬â€ deterministic order, no autoloader, no stubs.
 * Every class has exactly one file. New files added here.
 */
function autonode_load_files(): void {
    $files = [
        /* Infrastructure */
        'includes/Installer.php',
        'includes/Brute_Force.php',       /* NEW: IP-level brute-force protection */
        'includes/Api_Auth.php',
        'includes/Rate_Limiter.php',
        'includes/Activity_Logger.php',
        'includes/Taxonomy_Manager.php',
        'includes/Webhook_Manager.php',   /* FIXED: HMAC bug, retry, delivery log */
        'includes/Post_Manager.php',
        'includes/Rankmath_Handler.php',
        'includes/Compatibility.php',
        'includes/Cron_Health.php',       /* NEW: 5-min ping + stale detection */
        /* REST layer */
        'includes/REST/Base_Controller.php',
        'includes/REST/Posts_Controller.php',
        'includes/REST/Pages_Controller.php',
        'includes/REST/SEO_Controller.php',
        'includes/REST/Meta_Controller.php',       /* NEW: GET/PUT/DELETE /posts/{id}/meta */
        'includes/REST/Media_Controller.php',      /* UPDATED: + /media/sideload from URL */
        'includes/REST/Taxonomy_Controller.php',
        'includes/REST/Bulk_Controller.php',
        'includes/REST/Oneshot_Controller.php',    /* NEW: n8n Bulk Publish */
        'includes/REST/Webhook_Controller.php',    /* UPDATED: delivery log, /analytics/keys, key rotation, cron health, blocked-ips */
        'includes/REST/Analytics_Controller.php',  /* NEW: GET /analytics/hourly */
        'includes/REST/Keys_Controller.php',       /* NEW: POST /keys/rotate */
        'includes/REST/System_Controller.php',     /* NEW: GET /status */
        'includes/REST/Openapi_Controller.php',    /* NEW: OpenAPI Specification for n8n */
        /* Admin layer */
        'includes/admin/Ajax_Handler.php',
        'includes/admin/Menu.php',
        /* Bootstrap last */
        'includes/Plugin.php',
    ];
    foreach ( $files as $f ) {
        require_once AUTONODE_DIR . $f;
    }
}

autonode_load_files();

add_action( 'plugins_loaded',    [ 'AutoNode\\Plugin', 'boot' ] );
register_activation_hook(   AUTONODE_FILE, [ 'AutoNode\\Installer', 'activate' ] );
register_deactivation_hook( AUTONODE_FILE, [ 'AutoNode\\Installer', 'deactivate' ] );
register_uninstall_hook(    AUTONODE_FILE, [ 'AutoNode\\Installer', 'uninstall' ] );
