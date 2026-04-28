<?php
namespace AutoNode\REST;

defined( 'ABSPATH' ) || exit;

use AutoNode\Rankmath_Handler;

final class SEO_Controller extends Base_Controller {

    public static function register(): void {
        foreach ( [ 'posts', 'pages' ] as $type ) {
            register_rest_route( self::NS, "/{$type}/(?P<id>\\d+)/seo", [
                [ 'methods' => \WP_REST_Server::READABLE, 'callback' => [ self::class, 'show' ],   'permission_callback' => fn( $r ) => self::guard( $r, 'seo:read' ) ],
                [ 'methods' => \WP_REST_Server::EDITABLE, 'callback' => [ self::class, 'update' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'seo:write' ) ],
            ] );
        }
    }

    public static function show( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id = (int) $req->get_param( 'id' );
        if ( ! get_post( $id ) ) return self::fail( $req, new \WP_Error( 'amp_not_found', __( 'Post not found.', 'autonode' ), [ 'status' => 404 ] ), 'get_seo' );
        return self::ok( $req, Rankmath_Handler::read( $id ), 200, 'get_seo', $id, 'post' );
    }

    public static function update( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id   = (int) $req->get_param( 'id' );
        $body = $req->get_json_params() ?: [];
        if ( ! get_post( $id ) ) return self::fail( $req, new \WP_Error( 'amp_not_found', __( 'Post not found.', 'autonode' ), [ 'status' => 404 ] ), 'update_seo' );
        $r = Rankmath_Handler::update( $id, $body );
        return self::ok( $req, array_merge( $r, [ 'post_id' => $id, 'seo' => Rankmath_Handler::read( $id ) ] ), 200, 'update_seo', $id, 'post' );
    }
}
