<?php
namespace AutoNode\Licensing;

defined( 'ABSPATH' ) || exit;

/**
 * License Admin UI — adds license page and notices.
 *
 * @since 4.2.0
 */
final class License_Admin {

	public static function init(): void {
		add_action( 'admin_menu', [ __CLASS__, 'add_license_page' ] );
		add_action( 'admin_notices', [ __CLASS__, 'license_notice' ] );
		add_action( 'wp_ajax_autonode_activate_license', [ __CLASS__, 'ajax_activate' ] );
		add_action( 'wp_ajax_autonode_deactivate_license', [ __CLASS__, 'ajax_deactivate' ] );
	}

	public static function add_license_page(): void {
		add_submenu_page(
			'amp-cm',
			esc_html__( 'License', 'autonode-pro'),
			esc_html__( 'License', 'autonode-pro'),
			'manage_options',
			'autonode-license',
			[ __CLASS__, 'render_page' ]
		);
	}

	public static function license_notice(): void {
		$screen = get_current_screen();
		if ( ! $screen || str_contains( $screen->id, 'autonode-license' ) ) {
			return;
		}

		$status = License_Manager::get_status();
		if ( $status['is_valid'] ) {
			return;
		}

		$msg = '';
		if ( $status['status'] === 'inactive' ) {
			$msg = sprintf(
				/* translators: %s: license page URL */
				__( 'AutoNode Pro: Please <a href="%s">activate your license</a> to unlock all features.', 'autonode-pro'),
				esc_url( admin_url( 'admin.php?page=autonode-license' ) )
			);
		} elseif ( ! empty( $status['grace_remaining'] ) && $status['grace_remaining'] > 0 ) {
			$msg = sprintf(
				/* translators: 1: number of days, 2: license page URL */
				__( 'AutoNode Pro: License verification pending. Grace period expires in %1$d days. <a href="%2$s">Check license status</a>.', 'autonode-pro'),
				$status['grace_remaining'],
				esc_url( admin_url( 'admin.php?page=autonode-license' ) )
			);
		} else {
			$msg = sprintf(
				/* translators: %s: license page URL */
				__( 'AutoNode Pro: License invalid or expired. <a href="%s">Reactivate your license</a> to restore full functionality.', 'autonode-pro'),
				esc_url( admin_url( 'admin.php?page=autonode-license' ) )
			);
		}

		echo '<div class="notice notice-warning is-dismissible"><p>' . wp_kses_post( $msg ) . '</p></div>';
	}

	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'autonode-pro') );
		}
		$status = License_Manager::get_status();
		require AUTONODE_DIR . 'includes/licensing/views/license-page.php';
	}

	public static function ajax_activate(): void {
		check_ajax_referer( 'autonode_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'autonode-pro') ], 403 );
		}

		$code  = sanitize_text_field( wp_unslash( $_POST['purchase_code'] ?? '' ) );
		$email = sanitize_email( wp_unslash( $_POST['buyer_email'] ?? '' ) );

		if ( ! $code ) {
			wp_send_json_error( [ 'message' => __( 'Purchase code is required.', 'autonode-pro') ], 400 );
		}

		$result = License_Manager::activate( $code, $email );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( [ 'message' => $result->get_error_message() ], (int) ( $result->get_error_data()['status'] ?? 400 ) );
		}

		wp_send_json_success( $result );
	}

	public static function ajax_deactivate(): void {
		check_ajax_referer( 'autonode_admin', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Permission denied.', 'autonode-pro') ], 403 );
		}

		License_Manager::deactivate();
		wp_send_json_success( [ 'message' => __( 'License deactivated.', 'autonode-pro') ] );
	}
}
