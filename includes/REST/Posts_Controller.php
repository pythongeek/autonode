<?php
namespace AutoNode\REST;

defined( 'ABSPATH' ) || exit;

use AutoNode\Post_Manager;
use AutoNode\Rankmath_Handler;
use AutoNode\Taxonomy_Manager;

final class Posts_Controller extends Base_Controller {

    public static function register(): void {
        register_rest_route( self::NS, '/posts', [
            [ 'methods' => \WP_REST_Server::READABLE,  'callback' => [ self::class, 'index' ],  'permission_callback' => fn( $r ) => self::guard( $r, 'posts:read' ),  'args' => array_merge( self::pagination_args(), [ 'status' => [ 'type' => 'string', 'default' => 'any' ], 'category' => [ 'type' => 'integer' ] ] ) ],
            [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ self::class, 'create' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'posts:write' ) ],
        ] );
        register_rest_route( self::NS, '/posts/(?P<id>\d+)', [
            [ 'methods' => \WP_REST_Server::READABLE,  'callback' => [ self::class, 'show' ],    'permission_callback' => fn( $r ) => self::guard( $r, 'posts:read' ) ],
            [ 'methods' => \WP_REST_Server::EDITABLE,  'callback' => [ self::class, 'update' ],  'permission_callback' => fn( $r ) => self::guard( $r, 'posts:write' ) ],
            [ 'methods' => \WP_REST_Server::DELETABLE, 'callback' => [ self::class, 'destroy' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'posts:delete' ), 'args' => [ 'force' => [ 'type' => 'boolean', 'default' => false ] ] ],
        ] );
        register_rest_route( self::NS, '/posts/(?P<id>\d+)/publish', [
            [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ self::class, 'publish' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'posts:publish' ) ],
        ] );
    }

    public static function index( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $per  = (int) $req->get_param( 'per_page' );
        $page = (int) $req->get_param( 'page' );
        $q    = new \WP_Query( array_filter( [
            'post_type'      => 'post',
            'post_status'    => $req->get_param( 'status' ) ?: [ 'publish', 'draft', 'pending', 'private' ],
            'posts_per_page' => $per,
            'paged'          => $page,
            'orderby'        => sanitize_key( $req->get_param( 'orderby' ) ?: 'date' ),
            'order'          => strtoupper( $req->get_param( 'order' ) ?: 'DESC' ) === 'ASC' ? 'ASC' : 'DESC',
            's'              => $req->get_param( 'search' ) ? sanitize_text_field( $req->get_param( 'search' ) ) : null,
            'cat'            => (int) $req->get_param( 'category' ) ?: null,
            'no_found_rows'  => false,
        ] ) );
        return self::ok( $req, [ 'posts' => array_map( [Post_Manager::class,'format'], $q->posts ), 'total' => (int)$q->found_posts, 'total_pages' => (int)$q->max_num_pages ], 200, 'list_posts' );
    }

    public static function create( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $body = $req->get_json_params() ?: [];
        $id   = Post_Manager::create( $body, 'post' );
        if ( is_wp_error( $id ) ) return self::fail( $req, $id, 'create_post' );
        if ( ! empty( $body['seo'] ) ) Rankmath_Handler::update( $id, $body['seo'] );
        $post = Post_Manager::format( get_post( $id ) );
        $post['seo'] = Rankmath_Handler::read( $id );
        return self::ok( $req, [ 'post' => $post ], 201, 'create_post', $id, 'post' );
    }

    public static function show( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id   = (int) $req->get_param( 'id' );
        $post = get_post( $id );
        if ( ! $post || $post->post_type !== 'post' ) return self::fail( $req, new \WP_Error( 'amp_not_found', 'Post not found.', [ 'status' => 404 ] ), 'get_post' );
        $data = Post_Manager::format( $post );
        $data['seo']  = Rankmath_Handler::read( $id );
        $data['meta'] = get_post_meta( $id );
        return self::ok( $req, [ 'post' => $data ], 200, 'get_post', $id, 'post' );
    }

    public static function update( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id   = (int) $req->get_param( 'id' );
        $body = $req->get_json_params() ?: [];
        $post = get_post( $id );
        if ( ! $post || $post->post_type !== 'post' ) return self::fail( $req, new \WP_Error( 'amp_not_found', 'Post not found.', [ 'status' => 404 ] ), 'update_post' );
        $r = Post_Manager::update( $id, $body );
        if ( is_wp_error( $r ) ) return self::fail( $req, $r, 'update_post' );
        if ( ! empty( $body['seo'] ) ) Rankmath_Handler::update( $id, $body['seo'] );
        $updated = Post_Manager::format( get_post( $id ) );
        $updated['seo'] = Rankmath_Handler::read( $id );
        return self::ok( $req, [ 'post' => $updated ], 200, 'update_post', $id, 'post' );
    }

    public static function destroy( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id = (int) $req->get_param( 'id' );
        if ( ! get_post( $id ) ) return self::fail( $req, new \WP_Error( 'amp_not_found', 'Post not found.', [ 'status' => 404 ] ), 'delete_post' );
        $r = wp_delete_post( $id, (bool) $req->get_param( 'force' ) );
        return self::ok( $req, [ 'deleted' => (bool) $r, 'id' => $id ], 200, 'delete_post', $id, 'post' );
    }

    public static function publish( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id = (int) $req->get_param( 'id' );
        $r  = wp_update_post( [ 'ID' => $id, 'post_status' => 'publish' ], true );
        if ( is_wp_error( $r ) ) return self::fail( $req, $r, 'publish_post' );
        return self::ok( $req, [ 'post' => Post_Manager::format( get_post( $id ) ) ], 200, 'publish_post', $id, 'post' );
    }
}
