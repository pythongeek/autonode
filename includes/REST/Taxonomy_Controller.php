<?php
namespace AutoNode\REST;

defined( 'ABSPATH' ) || exit;

use AutoNode\Taxonomy_Manager;

final class Taxonomy_Controller extends Base_Controller {

    public static function register(): void {
        register_rest_route( self::NS, '/categories', [
            [ 'methods' => \WP_REST_Server::READABLE,  'callback' => [ self::class, 'categories' ],  'permission_callback' => fn( $r ) => self::guard( $r, 'taxonomy:read' ) ],
            [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ self::class, 'create_cat' ],  'permission_callback' => fn( $r ) => self::guard( $r, 'taxonomy:write' ) ],
        ] );
        register_rest_route( self::NS, '/tags', [
            [ 'methods' => \WP_REST_Server::READABLE,  'callback' => [ self::class, 'tags' ],        'permission_callback' => fn( $r ) => self::guard( $r, 'taxonomy:read' ) ],
            [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ self::class, 'create_tag' ],  'permission_callback' => fn( $r ) => self::guard( $r, 'taxonomy:write' ) ],
        ] );
    }

    public static function categories( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $terms = get_terms( [ 'taxonomy' => 'category', 'hide_empty' => false ] );
        $data  = is_wp_error( $terms ) ? [] : array_map( fn( $t ) => [ 'id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug, 'count' => $t->count, 'parent' => $t->parent ], $terms );
        return self::ok( $req, [ 'categories' => $data, 'total' => count( $data ) ], 200, 'list_categories' );
    }

    public static function create_cat( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $body = $req->get_json_params() ?: [];
        $name = sanitize_text_field( $body['name'] ?? '' );
        if ( ! $name ) return self::fail( $req, new \WP_Error( 'amp_invalid', 'Name required.', [ 'status' => 400 ] ), 'create_category' );
        $id = Taxonomy_Manager::get_or_create_category( $name, $body['slug'] ?? '' );
        if ( is_wp_error( $id ) ) return self::fail( $req, $id, 'create_category' );
        $t = get_term( $id, 'category' );
        return self::ok( $req, [ 'category' => [ 'id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug ] ], 201, 'create_category' );
    }

    public static function tags( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $terms = get_terms( [ 'taxonomy' => 'post_tag', 'hide_empty' => false ] );
        $data  = is_wp_error( $terms ) ? [] : array_map( fn( $t ) => [ 'id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug, 'count' => $t->count ], $terms );
        return self::ok( $req, [ 'tags' => $data, 'total' => count( $data ) ], 200, 'list_tags' );
    }

    public static function create_tag( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $body = $req->get_json_params() ?: [];
        $name = sanitize_text_field( $body['name'] ?? '' );
        if ( ! $name ) return self::fail( $req, new \WP_Error( 'amp_invalid', 'Name required.', [ 'status' => 400 ] ), 'create_tag' );
        $id = Taxonomy_Manager::get_or_create_tag( $name );
        if ( is_wp_error( $id ) ) return self::fail( $req, $id, 'create_tag' );
        $t = get_term( $id, 'post_tag' );
        return self::ok( $req, [ 'tag' => [ 'id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug ] ], 201, 'create_tag' );
    }
}
