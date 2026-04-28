<?php
namespace AutoNode;

defined( 'ABSPATH' ) || exit;

final class Api_Auth {

    public const PREFIX = 'ampcm_';

    public const SCOPES = [
        'posts:read', 'posts:write', 'posts:delete', 'posts:publish',
        'pages:read', 'pages:write', 'pages:delete',
        'seo:read', 'seo:write',
        'media:read', 'media:write', 'media:delete',
        'taxonomy:read', 'taxonomy:write',
        'bulk:write',
        'webhooks:read', 'webhooks:write',
        'analytics:read',
        'keys:read', 'keys:rotate',
        'system:read',
    ];

    public const PRESET_SCOPES = [
        'readonly' => [
            'posts:read', 'pages:read', 'seo:read', 'media:read',
            'taxonomy:read', 'analytics:read', 'system:read',
        ],
        'writer' => [
            'posts:read', 'posts:write', 'posts:publish',
            'pages:read', 'pages:write',
            'seo:read', 'seo:write',
            'media:read', 'media:write',
            'taxonomy:read', 'taxonomy:write',
            'bulk:write', 'system:read',
        ],
        'full_access' => [], // empty = all scopes granted
    ];

    public static function init(): void {}

    /* â”€â”€ Key Generation â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public static function generate_raw(): string {
        return self::PREFIX . bin2hex( random_bytes( 32 ) );
    }

    public static function hash_key( string $raw ): string {
        return hash( 'sha256', $raw );
    }

    /* â”€â”€ Create â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public static function create( array $args ): array {
        global $wpdb;
        $raw    = self::generate_raw();
        $scopes = array_values( array_intersect( $args['scopes'] ?? [], self::SCOPES ) );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- custom table insert.
        $wpdb->insert( "{$wpdb->prefix}autonode_keys", array(
            'user_id'      => (int) ( $args['user_id'] ?? get_current_user_id() ),
            'key_hash'     => self::hash_key( $raw ),
            'key_prefix'   => substr( $raw, 0, 12 ),
            'label'        => sanitize_text_field( $args['label'] ?? '' ),
            'description'  => sanitize_textarea_field( $args['description'] ?? '' ),
            'environment'  => in_array( $args['environment'] ?? '', [ 'production', 'staging', 'development' ], true )
                              ? $args['environment'] : 'production',
            'scopes'       => wp_json_encode( $scopes ),
            'ip_whitelist' => ! empty( $args['ip_whitelist'] ) ? wp_json_encode( array_values( $args['ip_whitelist'] ) ) : null,
            'expires_at'   => $args['expires_at'] ?? null,
            'created_at'   => current_time( 'mysql', true ),
        ) );

        return [
            'raw_key' => $raw,
            'id'      => (int) $wpdb->insert_id,
            'prefix'  => substr( $raw, 0, 12 ),
            'label'   => $args['label'] ?? '',
            'scopes'  => $scopes,
        ];
    }

    /* â”€â”€ Rotate (generate new key, keep same id / scopes / settings) */

    public static function rotate( int $id ): array|\WP_Error {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table lookup.
        $row = $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}autonode_keys WHERE id = %d AND revoked = 0", $id ),
            ARRAY_A
        );
        if ( ! $row ) {
            return new \WP_Error( 'amp_not_found', 'Key not found.', [ 'status' => 404 ] );
        }
        $raw = self::generate_raw();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table update.
        $wpdb->update( "{$wpdb->prefix}autonode_keys", [
            'key_hash'   => self::hash_key( $raw ),
            'key_prefix' => substr( $raw, 0, 12 ),
        ], [ 'id' => $id ], [ '%s', '%s' ], [ '%d' ] );

        return [
            'raw_key' => $raw,
            'id'      => $id,
            'prefix'  => substr( $raw, 0, 12 ),
            'label'   => $row['label'],
        ];
    }

    /* â”€â”€ Authenticate â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public static function authenticate( \WP_REST_Request $req ): array|\WP_Error {
        $ip = self::client_ip();

        /* Brute-force check first */
        $bf = Brute_Force::check_ip( $ip );
        if ( is_wp_error( $bf ) ) {
            return $bf;
        }

        /* HTTPS check */
        $settings = get_option( 'autonode_settings', [] );
        if ( ! empty( $settings['require_https'] ) && ! is_ssl() && ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
            return new \WP_Error( 'amp_ssl_required', __( 'HTTPS required.', 'autonode' ), [ 'status' => 403 ] );
        }

        /* Extract bearer token */
        $auth = $req->get_header( 'authorization' );
        if ( empty( $auth ) ) {
            $xkey = $req->get_header( 'x-api-key' );
            if ( ! empty( $xkey ) ) {
                $auth = 'Bearer ' . trim( $xkey );
            }
        }
        if ( empty( $auth ) || ! str_starts_with( $auth, 'Bearer ' ) ) {
            return new \WP_Error( 'amp_no_auth', __( 'Missing Authorization header.', 'autonode' ), [ 'status' => 401 ] );
        }

        $raw = trim( substr( $auth, 7 ) );
        if ( ! str_starts_with( $raw, self::PREFIX ) ) {
            Brute_Force::record_failure( $ip );
            return new \WP_Error( 'amp_bad_token', __( 'Malformed token.', 'autonode' ), [ 'status' => 401 ] );
        }

        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table auth lookup.
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}autonode_keys
                 WHERE key_hash = %s AND revoked = 0
                 AND (expires_at IS NULL OR expires_at > UTC_TIMESTAMP())",
                self::hash_key( $raw )
            ),
            ARRAY_A
        );

        if ( ! $row ) {
            Brute_Force::record_failure( $ip );
            return new \WP_Error( 'amp_invalid_key', 'Invalid or expired API key.', [ 'status' => 401 ] );
        }

        /* Successful auth â€” clear any brute-force record */
        Brute_Force::clear( $ip );

        /* IP whitelist */
        if ( ! empty( $row['ip_whitelist'] ) ) {
            $list = json_decode( $row['ip_whitelist'], true ) ?: [];
            if ( $list && ! self::ip_allowed( $ip, $list ) ) {
                return new \WP_Error( 'amp_ip_blocked', 'Client IP not permitted.', [ 'status' => 403 ] );
            }
        }

        /* Update last used (lightweight) */
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table update.
        $wpdb->update(
            "{$wpdb->prefix}autonode_keys",
            [ 'last_used_at' => current_time( 'mysql', true ), 'last_used_ip' => $ip, 'total_requests' => (int) $row['total_requests'] + 1 ],
            [ 'id' => (int) $row['id'] ],
            [ '%s', '%s', '%d' ], [ '%d' ]
        );

        $row['scopes_array'] = json_decode( $row['scopes'], true ) ?: [];
        $row['is_full']      = empty( $row['scopes_array'] );
        return $row;
    }

    public static function has_scope( array $key, string $scope ): bool {
        return ( $key['is_full'] ?? false ) || in_array( $scope, $key['scopes_array'] ?? [], true );
    }

    public static function revoke( int $id ): bool {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- custom table update.
        return (bool) $wpdb->update(
            "{$wpdb->prefix}autonode_keys",
            [ 'revoked' => 1, 'revoked_at' => current_time( 'mysql', true ) ],
            [ 'id' => $id ], [ '%d', '%s' ], [ '%d' ]
        );
    }

    public static function list_keys( int $uid = 0, bool $include_revoked = false ): array {
        global $wpdb;
        $w = [ '1=1' ];
        if ( ! $include_revoked ) $w[] = 'revoked = 0';
        if ( $uid > 0 )           $w[] = $wpdb->prepare( 'user_id = %d', $uid );
        $where = implode( ' AND ', $w );
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
        return $wpdb->get_results(
            "SELECT id,user_id,key_prefix,label,description,environment,scopes,ip_whitelist,last_used_at,last_used_ip,expires_at,total_requests,created_at,revoked,revoked_at FROM {$wpdb->prefix}autonode_keys WHERE $where ORDER BY created_at DESC",
            ARRAY_A
        ) ?: [];
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
    }

    /* â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public static function client_ip(): string {
        foreach ( [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' ] as $h ) {
            if ( ! empty( $_SERVER[ $h ] ) ) {
                $ip = trim( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $h ] ) ) )[0] );
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    public static function ip_allowed( string $ip, array $list ): bool {
        foreach ( $list as $cidr ) {
            if ( self::cidr_match( $ip, trim( $cidr ) ) ) {
                return true;
            }
        }
        return false;
    }

    private static function cidr_match( string $ip, string $cidr ): bool {
        if ( ! str_contains( $cidr, '/' ) ) {
            return $ip === $cidr;
        }
        [ $subnet, $bits ] = explode( '/', $cidr, 2 );
        $a    = ip2long( $ip );
        $b    = ip2long( $subnet );
        $bits = (int) $bits;
        if ( false === $a || false === $b ) {
            return false;
        }
        $mask = $bits === 0 ? 0 : ~0 << ( 32 - $bits );
        return ( $a & $mask ) === ( $b & $mask );
    }
}
