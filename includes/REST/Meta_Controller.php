<?php
namespace AutoNode\REST;

defined( 'ABSPATH' ) || exit;

/**
 * Dedicated meta endpoint.
 * GET  /posts/{id}/meta  â€” return all postmeta (filtered)
 * PUT  /posts/{id}/meta  â€” upsert one or more meta keys
 * DEL  /posts/{id}/meta/{key} â€” delete a single meta key
 */
final class Meta_Controller extends Base_Controller {

    /* Keys that must never be exposed or modified via the API */
    private const PROTECTED_KEYS = [
        '_edit_lock', '_edit_last', '_wp_old_slug',
        '_wp_attachment_metadata', '_wp_attached_file',
        '_thumbnail_id',
    ];

    /* Rank Math / Yoast prefixes that belong to the SEO endpoint */
    private const SEO_PREFIXES = [ 'rank_math_', '_yoast_wpseo_', '_aioseo_' ];

    public static function register(): void {
        $types = 'posts|pages';
        register_rest_route( self::NS, "/(?P<type>{$types})/(?P<id>\\d+)/meta", [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ self::class, 'show' ],
                'permission_callback' => fn( $r ) => self::guard( $r, 'posts:read' ),
            ],
            [
                'methods'             => \WP_REST_Server::EDITABLE,
                'callback'            => [ self::class, 'update' ],
                'permission_callback' => fn( $r ) => self::guard( $r, 'posts:write' ),
            ],
        ] );
        register_rest_route( self::NS, "/(?P<type>{$types})/(?P<id>\\d+)/meta/(?P<key>[\\w-]+)", [
            [
                'methods'             => \WP_REST_Server::DELETABLE,
                'callback'            => [ self::class, 'delete_key' ],
                'permission_callback' => fn( $r ) => self::guard( $r, 'posts:write' ),
            ],
        ] );
    }

    public static function show( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id   = (int) $req->get_param( 'id' );
        $post = get_post( $id );
        if ( ! $post ) {
            return self::fail( $req, new \WP_Error( 'amp_not_found', 'Post not found.', [ 'status' => 404 ] ), 'get_meta' );
        }
        $raw    = get_post_meta( $id );
        $result = [];
        foreach ( $raw as $key => $values ) {
            if ( self::is_blocked( $key ) ) {
                continue;
            }
            $result[ $key ] = count( $values ) === 1 ? maybe_unserialize( $values[0] ) : array_map( 'maybe_unserialize', $values );
        }
        return self::ok( $req, [ 'meta' => $result, 'count' => count( $result ) ], 200, 'get_meta', $id, 'post' );
    }

    public static function update( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id   = (int) $req->get_param( 'id' );
        $body = $req->get_json_params() ?: [];
        $post = get_post( $id );
        if ( ! $post ) {
            return self::fail( $req, new \WP_Error( 'amp_not_found', 'Post not found.', [ 'status' => 404 ] ), 'update_meta' );
        }
        $updated = 0; $skipped = 0; $errors = [];
        foreach ( $body as $key => $value ) {
            $key = sanitize_key( (string) $key );
            if ( empty( $key ) || self::is_blocked( $key ) ) {
                $skipped++;
                continue;
            }
            $r = update_post_meta( $id, $key, $value );
            if ( false === $r ) {
                $errors[] = $key;
            } else {
                $updated++;
            }
        }
        return self::ok( $req, [
            'post_id' => $id,
            'updated' => $updated,
            'skipped' => $skipped,
            'errors'  => $errors,
        ], 200, 'update_meta', $id, 'post' );
    }

    public static function delete_key( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id  = (int) $req->get_param( 'id' );
        $key = sanitize_key( $req->get_param( 'key' ) );
        if ( ! get_post( $id ) ) {
            return self::fail( $req, new \WP_Error( 'amp_not_found', __( 'Post not found.', 'autonode' ), [ 'status' => 404 ] ), 'delete_meta' );
        }
        if ( self::is_blocked( $key ) ) {
            return self::fail( $req, new \WP_Error( 'amp_forbidden', __( 'Cannot delete protected meta key.', 'autonode' ), [ 'status' => 403 ] ), 'delete_meta' );
        }
        $r = delete_post_meta( $id, $key );
        return self::ok( $req, [ 'deleted' => $r, 'key' => $key, 'post_id' => $id ], 200, 'delete_meta', $id, 'post' );
    }

    private static function is_blocked( string $key ): bool {
        if ( in_array( $key, self::PROTECTED_KEYS, true ) ) {
            return true;
        }
        foreach ( self::SEO_PREFIXES as $prefix ) {
            if ( str_starts_with( $key, $prefix ) ) {
                return true; /* Use /seo endpoint for these */
            }
        }
        return false;
    }
}
