<?php
namespace AutoNode\REST;

defined( 'ABSPATH' ) || exit;

use AutoNode\Post_Manager;
use AutoNode\Rankmath_Handler;
use AutoNode\Taxonomy_Manager;

final class Oneshot_Controller extends Base_Controller {

    public static function register(): void {
        register_rest_route( self::NS, '/bulk/oneshot', [
            [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ self::class, 'handle' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'bulk:write' ) ],
        ] );
    }

    public static function handle( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $body = $req->get_json_params() ?: [];
        
        if ( empty( $body['title'] ) ) {
            return self::fail( $req, new \WP_Error( 'amp_invalid', 'Title is required.', [ 'status' => 400 ] ), 'oneshot_publish' );
        }

        /* 1. Create Post */
        $post_args = $body;
        // Don't pass extra stuff to Post Manager
        unset( $post_args['featured_image_url'], $post_args['seo'] );
        $id = Post_Manager::create( $post_args, $body['post_type'] ?? 'post' );
        if ( is_wp_error( $id ) ) return self::fail( $req, $id, 'oneshot_publish' );

        $response_data = [ 'id' => $id, 'action' => 'created' ];

        /* 2. Sideload Featured Image */
        if ( ! empty( $body['featured_image_url'] ) ) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';

            $url = esc_url_raw( $body['featured_image_url'] );
            $att_id = media_sideload_image( $url, $id, null, 'id' );
            if ( ! is_wp_error( $att_id ) ) {
                set_post_thumbnail( $id, $att_id );
                $response_data['featured_image_id'] = $att_id;
            } else {
                $response_data['image_error'] = $att_id->get_error_message();
            }
        }

        /* 3. Handle Categories / Taxonomy */
        if ( ! empty( $body['categories'] ) ) {
            wp_set_post_categories( $id, (array) $body['categories'] );
        }

        /* 4. Handle SEO Data */
        if ( ! empty( $body['seo'] ) ) {
            $seo_result = Rankmath_Handler::update( $id, $body['seo'] );
            $response_data['seo'] = $seo_result;
        }

        $response_data['post'] = Post_Manager::format( get_post( $id ) );

        return self::ok( $req, $response_data, 201, 'oneshot_publish', $id, 'post' );
    }
}
