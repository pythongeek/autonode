<?php
/**
 * AutoNode Pro — Uninstall Handler
 *
 * Cleans up ALL plugin data when user deletes the plugin from Plugins page.
 * Does NOT remove posts/pages created by the plugin (user content).
 *
 * @phpcs:disable WordPress.DB.DirectDatabaseQuery.Schema -- Direct table drops on uninstall.
 * @phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup, no caching needed.
 * @phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Known static table names, no user input.
 */

defined('ABSPATH') || exit;

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Multisite support
if ( is_multisite() ) {
    global $wpdb;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- multisite blog enumeration.
    $autonode_blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
    foreach ( $autonode_blog_ids as $autonode_blog_id ) {
        switch_to_blog( $autonode_blog_id );
        autonode_clean_blog_data();
        restore_current_blog();
    }
    delete_site_option( 'autonode_db_version' );
    delete_site_option( 'autonode_settings' );
    delete_site_option( 'autonode_license' );
} else {
    autonode_clean_blog_data();
}

/**
 * Remove all plugin data for a single blog.
 */
function autonode_clean_blog_data(): void {
    global $wpdb;

    $tables = [
        'autonode_keys',
        'autonode_log',
        'autonode_rate',
        'autonode_webhooks',
        'autonode_webhook_log',
        'autonode_analytics',
        'autonode_brute_force',
    ];

    foreach ( $tables as $autonode_table ) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.DirectDatabaseQuery.DirectQuery
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}{$autonode_table}" );
    }

    // Remove all plugin options
    delete_option( 'autonode_settings' );
    delete_option( 'autonode_db_version' );
    delete_option( 'autonode_cron_last_ping' );
    delete_option( 'autonode_apppass_dismissed' );
    delete_option( 'autonode_log_retention_days' );
    delete_option( 'autonode_license' );
    delete_option( 'autonode_installed_time' );
    delete_option( 'autonode_version' );

    // Remove transients
    delete_transient( 'autonode_rate_limit_cache' );
    delete_transient( 'autonode_brute_force_cache' );

    // Clear scheduled cron events
    wp_clear_scheduled_hook( 'autonode_webhook_delivery' );
    wp_clear_scheduled_hook( 'autonode_key_expiry_check' );
    wp_clear_scheduled_hook( 'autonode_log_cleanup' );
    wp_clear_scheduled_hook( 'autonode_rate_limit_cleanup' );
}