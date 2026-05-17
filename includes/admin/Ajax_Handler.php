<?php
namespace AutoNode\Admin;

defined( 'ABSPATH' ) || exit;

final class Ajax_Handler {

    private static function verify(): void {
        check_ajax_referer( 'autonode_admin', 'nonce' );
        if ( ! current_user_can( Menu::get_cap() ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied.' ), 403 );
        }
    }

    public static function clear_logs(): void {
        self::verify();
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->prefix}autonode_log" ); // phpcs:ignore
        $wpdb->query( "DELETE FROM {$wpdb->prefix}autonode_analytics" ); // phpcs:ignore
        $wpdb->query( "DELETE FROM {$wpdb->prefix}autonode_webhook_log" ); // phpcs:ignore
        wp_send_json_success();
    }

    public static function create_key(): void {
        self::verify();

        $label       = sanitize_text_field( wp_unslash( $_POST['label'] ?? 'API Key' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- verified in self::verify().
        $description = sanitize_textarea_field( wp_unslash( $_POST['description'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $environment = sanitize_key( wp_unslash( $_POST['environment'] ?? 'production' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $scopes_raw  = isset( $_POST['scopes'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['scopes'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $ip_raw      = sanitize_textarea_field( wp_unslash( $_POST['ip_whitelist'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $expires     = sanitize_text_field( wp_unslash( $_POST['expires_at'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

        /* Scope preset override */
        $preset = sanitize_key( wp_unslash( $_POST['preset'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( $preset && isset( \AutoNode\Api_Auth::PRESET_SCOPES[ $preset ] ) ) {
            $scopes_raw = \AutoNode\Api_Auth::PRESET_SCOPES[ $preset ];
        }

        $ips           = array_values( array_filter( array_map( 'trim', explode( "\n", $ip_raw ) ) ) );
        $expires_mysql = null;
        if ( $expires ) {
            $ts = strtotime( $expires );
            if ( $ts ) {
                $expires_mysql = gmdate( 'Y-m-d H:i:s', $ts );
            }
        }

        $result = \AutoNode\Api_Auth::create( array(
            'user_id'      => get_current_user_id(),
            'label'        => $label,
            'description'  => $description,
            'environment'  => $environment,
            'scopes'       => $scopes_raw,
            'ip_whitelist' => $ips,
            'expires_at'   => $expires_mysql,
        ) );

        wp_send_json_success( $result );
    }

    public static function revoke_key(): void {
        self::verify();
        $id = absint( wp_unslash( $_POST['key_id'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'Invalid ID.' ) );
        }
        $ok = \AutoNode\Api_Auth::revoke( $id );
        $ok ? wp_send_json_success( array( 'revoked' => true, 'id' => $id ) ) : wp_send_json_error( array( 'message' => 'Revoke failed.' ) );
    }

    public static function save_settings(): void {
        self::verify();
        $s = array(
            'rate_limit'          => absint( wp_unslash( $_POST['rate_limit'] ?? 120 ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
            'rate_window_sec'     => absint( wp_unslash( $_POST['rate_window_sec'] ?? 60 ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
            'log_retention_days'  => absint( wp_unslash( $_POST['log_retention_days'] ?? 90 ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
            'require_https'       => ! empty( $_POST['require_https'] ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
            'enable_webhooks'     => ! empty( $_POST['enable_webhooks'] ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
            'webhook_timeout_ms'  => absint( wp_unslash( $_POST['webhook_timeout_ms'] ?? 5000 ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
            'max_retry_attempts'  => min( 5, absint( wp_unslash( $_POST['max_retry_attempts'] ?? 3 ) ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
            'brute_force_limit'   => absint( wp_unslash( $_POST['brute_force_limit'] ?? 10 ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
            'brute_force_window'  => absint( wp_unslash( $_POST['brute_force_window'] ?? 300 ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
            'flatten_n8n_webhooks' => ! empty( $_POST['flatten_n8n_webhooks'] ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
            'min_capability'       => sanitize_text_field( wp_unslash( $_POST['min_capability'] ?? 'manage_options' ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
            'max_sideload_size'    => absint( wp_unslash( $_POST['max_sideload_size'] ?? 20 ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
            'key_rotation_days'    => absint( wp_unslash( $_POST['key_rotation_days'] ?? 0 ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
            'ui_mode'              => sanitize_text_field( wp_unslash( $_POST['ui_mode'] ?? 'dark' ) ), // phpcs:ignore WordPress.Security.NonceVerification.Missing
        );
        update_option( 'autonode_settings', $s );
        update_option( 'autonode_log_retention_days', $s['log_retention_days'] );
        wp_send_json_success();
    }

    public static function export_logs(): void {
        check_ajax_referer( 'autonode_admin', 'nonce' );
        if ( ! current_user_can( Menu::get_cap() ) ) wp_die( 'Forbidden' );
        $format = sanitize_key( $_GET['format'] ?? 'csv' );
        \AutoNode\Activity_Logger::export( $format );
    }

    public static function get_chart_data(): void {
        self::verify();
        $hours  = absint( wp_unslash( $_POST['hours'] ?? 24 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $key_id = absint( wp_unslash( $_POST['key_id'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        wp_send_json_success( array( 'hourly' => \AutoNode\Activity_Logger::hourly_stats( $hours, $key_id ?: null ) ) );
    }

    public static function create_webhook(): void {
        self::verify();
        global $wpdb;
        $url    = esc_url_raw( wp_unslash( $_POST['target_url'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $label  = sanitize_text_field( wp_unslash( $_POST['label'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $events = isset( $_POST['events'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['events'] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $types  = isset( $_POST['post_types'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['post_types'] ) ) : array( 'post', 'page' ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $secret = sanitize_text_field( wp_unslash( $_POST['secret'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

        if ( ! $url || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            wp_send_json_error( array( 'message' => 'Valid URL required.' ) );
        }

        $wpdb->insert( "{$wpdb->prefix}autonode_webhooks", array( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
            'label'       => $label,
            'target_url'  => $url,
            'secret'      => $secret ?: null,  /* raw secret â€” HMAC uses this directly */
            'events'      => wp_json_encode( $events ),
            'post_types'  => wp_json_encode( $types ),
            'active'      => 1,
            'created_at'  => current_time( 'mysql', true ),
        ) );
        wp_send_json_success( array( 'id' => (int) $wpdb->insert_id ) );
    }

    public static function delete_webhook(): void {
        self::verify();
        global $wpdb;
        $id = absint( wp_unslash( $_POST['id'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $wpdb->delete( "{$wpdb->prefix}autonode_webhooks", array( 'id' => $id ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        wp_send_json_success( array( 'deleted' => true ) );
    }

    public static function test_webhook(): void {
        self::verify();
        global $wpdb;
        $id  = absint( wp_unslash( $_POST['id'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}autonode_webhooks WHERE id=%d", $id ), ARRAY_A ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        if ( ! $row ) {
            wp_send_json_error( array( 'message' => 'Webhook not found.' ) );
        }
        wp_send_json_success( \AutoNode\Webhook_Manager::fire_test( $row ) );
    }

    public static function toggle_webhook(): void {
        self::verify();
        global $wpdb;
        $id     = absint( wp_unslash( $_POST['id'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $active = absint( wp_unslash( $_POST['active'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $wpdb->update( "{$wpdb->prefix}autonode_webhooks", array( 'active' => $active ), array( 'id' => $id ), array( '%d' ), array( '%d' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        wp_send_json_success( array( 'id' => $id, 'active' => $active ) );
    }

    public static function unblock_ip(): void {
        self::verify();
        global $wpdb;
        $ip = sanitize_text_field( wp_unslash( $_POST['ip'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( ! $ip ) {
            wp_send_json_error( array( 'message' => 'IP required.' ) );
        }
        $wpdb->delete( "{$wpdb->prefix}autonode_brute_force", array( 'ip_address' => $ip ), array( '%s' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        wp_send_json_success( array( 'unblocked' => $ip ) );
    }

    public static function rotate_key(): void {
        self::verify();
        $id = absint( wp_unslash( $_POST['key_id'] ?? 0 ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'Invalid ID.' ) );
        }
        $result = \AutoNode\Api_Auth::rotate( $id );
        is_wp_error( $result )
            ? wp_send_json_error( array( 'message' => $result->get_error_message() ) )
            : wp_send_json_success( $result );
    }

    public static function export_settings(): void {
        self::verify();
        $s = get_option( 'autonode_settings', array() );
        unset( $s['ui_mode'] );
        $s['exported_at'] = gmdate( 'c' );
        $s['plugin_version'] = AUTONODE_VERSION;
        $json = wp_json_encode( $s, JSON_PRETTY_PRINT );
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=autonode-settings-' . gmdate( 'Y-m-d' ) . '.json' );
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode produces safe JSON output
        echo $json;
        exit;
    }

    public static function import_settings(): void {
        self::verify();
        if ( empty( $_FILES['settings_file'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            wp_send_json_error( array( 'message' => 'No file uploaded.' ), 400 );
        }
        $file = $_FILES['settings_file']; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.WP.AlternativeFunctions.file_functions_basic
        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            wp_send_json_error( array( 'message' => 'Upload error.' ), 400 );
        }
        /* Enforce 1MB max upload size for settings files */
        if ( $file['size'] > 1048576 ) {
            wp_send_json_error( array( 'message' => 'File too large. Maximum 1MB allowed.' ), 400 );
        }
        $content = file_get_contents( $file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_functions_basic, WordPress.WP.AlternativeFunctions.file_system_operations_readfile
        $data = json_decode( $content, true );
        if ( ! is_array( $data ) ) {
            wp_send_json_error( array( 'message' => 'Invalid JSON file.' ), 400 );
        }
        $allowed_keys = array(
            'rate_limit', 'rate_window_sec', 'log_retention_days', 'require_https',
            'enable_webhooks', 'webhook_timeout_ms', 'max_retry_attempts',
            'brute_force_limit', 'brute_force_window', 'flatten_n8n_webhooks',
            'min_capability', 'max_sideload_size', 'key_rotation_days',
        );
        $imported = array_intersect_key( $data, array_flip( $allowed_keys ) );
        if ( empty( $imported ) ) {
            wp_send_json_error( array( 'message' => 'No valid settings found in file.' ), 400 );
        }
        $current = get_option( 'autonode_settings', array() );
        $merged = array_merge( $current, $imported );
        update_option( 'autonode_settings', $merged );
        update_option( 'autonode_log_retention_days', $merged['log_retention_days'] ?? 90 );
        wp_send_json_success( array( 'imported' => count( $imported ), 'total' => count( $merged ) ) );
    }

}
