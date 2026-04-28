<?php
namespace AutoNode\REST;

defined( 'ABSPATH' ) || exit;

use AutoNode\Post_Manager;
use AutoNode\Rankmath_Handler;

final class Pages_Controller extends Base_Controller {

    public static function register(): void {
        register_rest_route( self::NS, '/pages', [
            [ 'methods' => \WP_REST_Server::READABLE,  'callback' => [ self::class, 'index' ],  'permission_callback' => fn( $r ) => self::guard( $r, 'pages:read' ),  'args' => self::pagination_args() ],
            [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ self::class, 'create' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'pages:write' ) ],
        ] );
        register_rest_route( self::NS, '/pages/(?P<id>\d+)', [
            [ 'methods' => \WP_REST_Server::READABLE,  'callback' => [ self::class, 'show' ],    'permission_callback' => fn( $r ) => self::guard( $r, 'pages:read' ) ],
            [ 'methods' => \WP_REST_Server::EDITABLE,  'callback' => [ self::class, 'update' ],  'permission_callback' => fn( $r ) => self::guard( $r, 'pages:write' ) ],
            [ 'methods' => \WP_REST_Server::DELETABLE, 'callback' => [ self::class, 'destroy' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'pages:delete' ), 'args' => [ 'force' => [ 'type' => 'boolean', 'default' => false ] ] ],
        ] );
    }

    public static function index( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $q = new \WP_Query( [
            'post_type'      => 'page',
            'post_status'    => [ 'publish', 'draft', 'private', 'pending' ],
            'posts_per_page' => (int) $req->get_param( 'per_page' ),
            'paged'          => (int) $req->get_param( 'page' ),
            'orderby'        => sanitize_key( $req->get_param( 'orderby' ) ?: 'date' ),
            'order'          => strtoupper( $req->get_param( 'order' ) ?: 'DESC' ) === 'ASC' ? 'ASC' : 'DESC',
            'no_found_rows'  => false,
        ] );
        return self::ok( $req, [ 'pages' => array_map( [ Post_Manager::class, 'format' ], $q->posts ), 'total' => (int) $q->found_posts, 'total_pages' => (int) $q->max_num_pages ], 200, 'list_pages' );
    }

    public static function create( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $body = $req->get_json_params() ?: [];
        $id   = Post_Manager::create( $body, 'page' );
        if ( is_wp_error( $id ) ) return self::fail( $req, $id, 'create_page' );
        if ( ! empty( $body['seo'] ) ) Rankmath_Handler::update( $id, $body['seo'] );
        $page = Post_Manager::format( get_post( $id ) );
        $page['seo'] = Rankmath_Handler::read( $id );
        return self::ok( $req, [ 'post' => $page ], 201, 'create_page', $id, 'page' );
    }

    public static function show( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id   = (int) $req->get_param( 'id' );
        $post = get_post( $id );
        if ( ! $post || $post->post_type !== 'page' ) return self::fail( $req, new \WP_Error( 'amp_not_found', __( 'Page not found.', 'autonode' ), [ 'status' => 404 ] ), 'get_page' );
        $data = Post_Manager::format( $post );
        $data['seo'] = Rankmath_Handler::read( $id );
        return self::ok( $req, [ 'post' => $data ], 200, 'get_page', $id, 'page' );
    }

    public static function update( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id   = (int) $req->get_param( 'id' );
        $body = $req->get_json_params() ?: [];
        $post = get_post( $id );
        if ( ! $post || $post->post_type !== 'page' ) return self::fail( $req, new \WP_Error( 'amp_not_found', __( 'Page not found.', 'autonode' ), [ 'status' => 404 ] ), 'update_page' );
        $r = Post_Manager::update( $id, $body );
        if ( is_wp_error( $r ) ) return self::fail( $req, $r, 'update_page' );
        if ( ! empty( $body['seo'] ) ) Rankmath_Handler::update( $id, $body['seo'] );
        $updated = Post_Manager::format( get_post( $id ) );
        $updated['seo'] = Rankmath_Handler::read( $id );
        return self::ok( $req, [ 'post' => $updated ], 200, 'update_page', $id, 'page' );
    }

    public static function destroy( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id = (int) $req->get_param( 'id' );
        if ( ! get_post( $id ) ) return self::fail( $req, new \WP_Error( 'amp_not_found', 'Page not found.', [ 'status' => 404 ] ), 'delete_page' );
        $r = wp_delete_post( $id, (bool) $req->get_param( 'force' ) );
        return self::ok( $req, [ 'deleted' => (bool) $r, 'id' => $id ], 200, 'delete_page', $id, 'page' );
    }
}
