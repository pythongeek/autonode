<?php
namespace AutoNode\Licensing;

/**
 * AutoNode Pro — CodeCanyon License Manager
 *
 * Validates purchase codes via Envato API.
 * Supports local license caching, offline grace period, and admin notices.
 *
 * @since 4.2.0
 */
final class License_Manager {

	private const OPTION_KEY        = 'autonode_license';
	private const CACHE_KEY         = 'autonode_license_status';
	private const CACHE_DURATION    = DAY_IN_SECONDS * 7; /* Re-verify weekly */
	private const GRACE_DAYS        = 14; /* Offline grace period */
	private const ENVATO_API        = 'https://api.envato.com/v3/market/author/sale';

	/** Check if license is valid (cached or fresh) */
	public static function is_valid(): bool {
		$status = self::get_cached_status();
		if ( $status === 'valid' ) {
			return true;
		}
		if ( $status === 'unknown' || $status === 'error' ) {
			/* Check grace period */
			return self::within_grace_period();
		}
		return false;
	}

	/** Get license data array */
	public static function get_license(): array {
		return get_option( self::OPTION_KEY, [] );
	}

	/** Save license data */
	public static function save_license( array $data ): void {
		update_option( self::OPTION_KEY, $data );
		delete_transient( self::CACHE_KEY );
	}

	/** Activate a purchase code */
	public static function activate( string $purchase_code, string $buyer_email = '' ): array|\WP_Error {
		$purchase_code = sanitize_text_field( $purchase_code );
		$buyer_email   = sanitize_email( $buyer_email );

		if ( ! self::is_valid_purchase_code( $purchase_code ) ) {
			return new \WP_Error( 'amp_invalid_code', __( 'Invalid purchase code format.', 'autonode-pro'), [ 'status' => 400 ] );
		}

		$result = self::verify_with_envato( $purchase_code );

		if ( is_wp_error( $result ) ) {
			/* If API is unreachable, allow activation with warning */
			if ( $result->get_error_code() === 'amp_api_unreachable' ) {
				self::save_license( [
					'purchase_code'  => $purchase_code,
					'buyer_email'    => $buyer_email,
					'activated_at'   => time(),
					'last_verified'  => time(),
					'status'         => 'pending',
					'item_id'        => '',
					'buyer'          => '',
					'sold_at'        => '',
					'supported_until' => '',
				] );
				set_transient( self::CACHE_KEY, 'valid', self::CACHE_DURATION );
				return [
					'success' => true,
					'warning' => __( 'License saved. Will verify when connection is available.', 'autonode-pro'),
				];
			}
			return $result;
		}

		/* Validate it's the correct item */
		$item_id = self::get_expected_item_id();
		if ( $item_id && isset( $result['item']['id'] ) && (int) $result['item']['id'] !== $item_id ) {
			return new \WP_Error( 'amp_wrong_item', __( 'This purchase code is for a different item.', 'autonode-pro'), [ 'status' => 400 ] );
		}

		self::save_license( [
			'purchase_code'   => $purchase_code,
			'buyer_email'     => $buyer_email,
			'activated_at'    => time(),
			'last_verified'   => time(),
			'status'          => 'valid',
			'item_id'         => $result['item']['id'] ?? '',
			'item_name'       => $result['item']['name'] ?? '',
			'buyer'           => $result['buyer'] ?? '',
			'sold_at'         => $result['sold_at'] ?? '',
			'supported_until' => $result['supported_until'] ?? '',
		] );

		set_transient( self::CACHE_KEY, 'valid', self::CACHE_DURATION );

		return [
			'success'         => true,
			'item_name'       => $result['item']['name'] ?? '',
			'buyer'           => $result['buyer'] ?? '',
			'supported_until' => $result['supported_until'] ?? '',
		];
	}

	/** Deactivate license */
	public static function deactivate(): void {
		delete_option( self::OPTION_KEY );
		delete_transient( self::CACHE_KEY );
	}

	/** Re-verify license with Envato API */
	public static function reverify(): array|\WP_Error {
		$license = self::get_license();
		if ( empty( $license['purchase_code'] ) ) {
			return new \WP_Error( 'amp_no_license', __( 'No license found.', 'autonode-pro'), [ 'status' => 400 ] );
		}

		$result = self::verify_with_envato( $license['purchase_code'] );

		if ( is_wp_error( $result ) ) {
			/* Keep existing status on API error */
			$license['last_verified'] = time();
			self::save_license( $license );
			return new \WP_Error( 'amp_api_error', __( 'Could not reach Envato API. Using cached status.', 'autonode-pro'), [ 'status' => 503 ] );
		}

		$license['last_verified'] = time();
		$license['status']        = 'valid';
		$license['supported_until'] = $result['supported_until'] ?? '';
		self::save_license( $license );
		set_transient( self::CACHE_KEY, 'valid', self::CACHE_DURATION );

		return [ 'success' => true, 'status' => 'valid' ];
	}

	/** Get license status for display */
	public static function get_status(): array {
		$license = self::get_license();
		if ( empty( $license ) ) {
			return [
				'status'          => 'inactive',
				'message'         => __( 'No license activated.', 'autonode-pro'),
				'is_valid'        => false,
				'grace_remaining' => self::GRACE_DAYS,
			];
		}

		$is_valid = self::is_valid();
		$grace    = self::grace_remaining_days();

		return [
			'status'          => $license['status'] ?? 'unknown',
			'item_name'       => $license['item_name'] ?? '',
			'buyer'           => $license['buyer'] ?? '',
			'activated_at'    => $license['activated_at'] ?? 0,
			'last_verified'   => $license['last_verified'] ?? 0,
			'supported_until' => $license['supported_until'] ?? '',
			'is_valid'        => $is_valid,
			'grace_remaining' => $grace,
			'purchase_code'   => self::mask_code( $license['purchase_code'] ?? '' ),
		];
	}

	/* ── Private helpers ─────────────────────────────────────────────── */

	private static function get_cached_status(): string {
		$cache = get_transient( self::CACHE_KEY );
		if ( $cache !== false ) {
			return $cache;
		}

		$license = self::get_license();
		if ( empty( $license ) ) {
			return 'inactive';
		}
		if ( ( $license['status'] ?? '' ) === 'valid' ) {
			set_transient( self::CACHE_KEY, 'valid', self::CACHE_DURATION );
			return 'valid';
		}
		if ( ( $license['status'] ?? '' ) === 'pending' ) {
			return 'unknown';
		}
		return 'invalid';
	}

	private static function verify_with_envato( string $code ): array|\WP_Error {
		$token = self::get_envato_token();
		if ( ! $token ) {
			/* No token configured — accept any valid-format code (self-hosted validation) */
			return [
				'item' => [ 'id' => self::get_expected_item_id(), 'name' => 'AutoNode WP Pro' ],
				'buyer' => '',
				'sold_at' => gmdate( 'c' ),
				'supported_until' => gmdate( 'c', strtotime( '+6 months' ) ),
			];
		}

		$response = wp_remote_get( self::ENVATO_API . '?code=' . rawurlencode( $code ), [
			'timeout' => 15,
			'headers' => [ 'Authorization' => 'Bearer ' . $token ],
		] );

		if ( is_wp_error( $response ) ) {
			return new \WP_Error( 'amp_api_unreachable', __( 'Could not reach Envato API.', 'autonode-pro'), [ 'status' => 503 ] );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code === 404 ) {
			return new \WP_Error( 'amp_invalid_code', __( 'Purchase code not found or already used.', 'autonode-pro'), [ 'status' => 401 ] );
		}
		if ( $code !== 200 || ! is_array( $body ) ) {
			return new \WP_Error( 'amp_api_error', __( 'Envato API error. Please try again later.', 'autonode-pro'), [ 'status' => 503 ] );
		}

		return $body;
	}

	private static function is_valid_purchase_code( string $code ): bool {
		return (bool) preg_match( '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $code );
	}

	private static function get_envato_token(): string {
		/* Envato API token can be set via constant or filter */
		if ( defined( 'AUTONODE_ENVATO_TOKEN' ) ) {
			return AUTONODE_ENVATO_TOKEN;
		}
		return apply_filters( 'autonode_envato_token', '' );
	}

	private static function get_expected_item_id(): int {
		/* Item ID can be set via constant or filter */
		if ( defined( 'AUTONODE_ITEM_ID' ) ) {
			return (int) AUTONODE_ITEM_ID;
		}
		return (int) apply_filters( 'autonode_item_id', 0 );
	}

	private static function within_grace_period(): bool {
		$license = self::get_license();
		if ( empty( $license['activated_at'] ) ) {
			return false;
		}
		$days_since = ( time() - (int) $license['activated_at'] ) / DAY_IN_SECONDS;
		return $days_since <= self::GRACE_DAYS;
	}

	private static function grace_remaining_days(): int {
		$license = self::get_license();
		if ( empty( $license['activated_at'] ) ) {
			return self::GRACE_DAYS;
		}
		$days_since = (int) floor( ( time() - (int) $license['activated_at'] ) / DAY_IN_SECONDS );
		return max( 0, self::GRACE_DAYS - $days_since );
	}

	private static function mask_code( string $code ): string {
		if ( strlen( $code ) < 12 ) {
			return $code;
		}
		return substr( $code, 0, 4 ) . '****' . substr( $code, -4 );
	}
}
