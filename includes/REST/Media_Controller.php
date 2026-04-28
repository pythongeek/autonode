<?php
namespace AutoNode\REST;

defined( 'ABSPATH' ) || exit;

final class Media_Controller extends Base_Controller {

    public static function register(): void {
        register_rest_route( self::NS, '/media', [
            [ 'methods' => \WP_REST_Server::READABLE,  'callback' => [ self::class, 'index' ],   'permission_callback' => fn( $r ) => self::guard( $r, 'media:read' ),  'args' => self::pagination_args() ],
            [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ self::class, 'upload' ],  'permission_callback' => fn( $r ) => self::guard( $r, 'media:write' ) ],
        ] );
        register_rest_route( self::NS, '/media/sideload', [
            [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ self::class, 'sideload' ],'permission_callback' => fn( $r ) => self::guard( $r, 'media:write' ) ],
        ] );
        register_rest_route( self::NS, '/media/(?P<id>\d+)', [
            [ 'methods' => \WP_REST_Server::READABLE,  'callback' => [ self::class, 'show' ],    'permission_callback' => fn( $r ) => self::guard( $r, 'media:read' ) ],
            [ 'methods' => \WP_REST_Server::EDITABLE,  'callback' => [ self::class, 'update' ],  'permission_callback' => fn( $r ) => self::guard( $r, 'media:write' ) ],
            [ 'methods' => \WP_REST_Server::DELETABLE, 'callback' => [ self::class, 'destroy' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'media:delete' ) ],
        ] );
    }

    public static function index( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $q = new \WP_Query( [
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'posts_per_page' => (int) $req->get_param( 'per_page' ),
            'paged'          => (int) $req->get_param( 'page' ),
            'no_found_rows'  => false,
        ] );
        return self::ok( $req, [
            'media' => array_map( [ self::class, 'format_media' ], $q->posts ),
            'total' => (int) $q->found_posts,
        ], 200, 'list_media' );
    }

    public static function show( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id = (int) $req->get_param( 'id' );
        $a  = get_post( $id );
        if ( ! $a || $a->post_type !== 'attachment' ) {
            return self::fail( $req, new \WP_Error( 'amp_not_found', 'Media not found.', [ 'status' => 404 ] ), 'get_media' );
        }
        return self::ok( $req, [ 'media' => self::format_media( $a ) ], 200, 'get_media', $id, 'media' );
    }

    /** Upload via base64-encoded file data */
    public static function upload( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        self::require_wp_files();
        $body = $req->get_json_params() ?: [];

        if ( empty( $body['file_base64'] ) || empty( $body['filename'] ) ) {
            return self::fail( $req, new \WP_Error( 'amp_invalid', 'file_base64 and filename are required.', [ 'status' => 400 ] ), 'upload_media' );
        }

        $decoded = base64_decode( $body['file_base64'], true ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
        if ( ! $decoded ) {
            return self::fail( $req, new \WP_Error( 'amp_invalid', 'Invalid base64 data.', [ 'status' => 400 ] ), 'upload_media' );
        }

        return self::save_upload( $req, $decoded, $body );
    }

    /** Sideload from a public URL â€” fetches the file then attaches it to the media library */
    public static function sideload( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        self::require_wp_files();
        $body = $req->get_json_params() ?: [];
        $url  = esc_url_raw( $body['url'] ?? '' );

        if ( ! $url || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            return self::fail( $req, new \WP_Error( 'amp_invalid', 'A valid "url" field is required.', [ 'status' => 400 ] ), 'sideload_media' );
        }

        /* Fetch the remote file */
        $tmp = download_url( $url );
        if ( is_wp_error( $tmp ) ) {
            return self::fail( $req, $tmp, 'sideload_media' );
        }

        /* Derive filename from URL if not provided */
        $filename = $body['filename'] ?? basename( wp_parse_url( $url, PHP_URL_PATH ) ) ?: 'media-file';
        $decoded  = file_get_contents( $tmp ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        @unlink( $tmp ); // phpcs:ignore

        if ( ! $decoded ) {
            return self::fail( $req, new \WP_Error( 'amp_fetch_failed', 'Could not read downloaded file.', [ 'status' => 502 ] ), 'sideload_media' );
        }

        return self::save_upload( $req, $decoded, array_merge( $body, [ 'filename' => $filename ] ), 'sideload_media' );
    }

    /** Shared logic: write bytes to media library */
    private static function save_upload( \WP_REST_Request $req, string $bytes, array $meta, string $action = 'upload_media' ): \WP_REST_Response|\WP_Error {
        $filename  = sanitize_file_name( $meta['filename'] ?? 'upload' );
        $mime_type = $meta['mime_type'] ?? wp_check_filetype( $filename )['type'] ?? 'application/octet-stream';
        $tmp       = wp_tempnam( $filename );
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
        file_put_contents( $tmp, $bytes );

        $file_arr = [
            'name'     => $filename,
            'type'     => $mime_type,
            'tmp_name' => $tmp,
            'size'     => strlen( $bytes ),
            'error'    => 0,
        ];
        $uploaded = wp_handle_sideload( $file_arr, [ 'test_form' => false ] );
        @unlink( $tmp ); // phpcs:ignore

        if ( isset( $uploaded['error'] ) ) {
            return self::fail( $req, new \WP_Error( 'amp_upload_fail', $uploaded['error'], [ 'status' => 500 ] ), $action );
        }

        $att_id = wp_insert_attachment( [
            'post_mime_type' => $uploaded['type'],
            'post_title'     => sanitize_text_field( $meta['title'] ?? pathinfo( $filename, PATHINFO_FILENAME ) ),
            'post_excerpt'   => sanitize_text_field( $meta['caption'] ?? '' ),
            'post_status'    => 'inherit',
        ], $uploaded['file'] );

        if ( is_wp_error( $att_id ) ) {
            return self::fail( $req, $att_id, $action );
        }

        wp_update_attachment_metadata( $att_id, wp_generate_attachment_metadata( $att_id, $uploaded['file'] ) );
        if ( ! empty( $meta['alt'] ) ) {
            update_post_meta( $att_id, '_wp_attachment_image_alt', sanitize_text_field( $meta['alt'] ) );
        }
        if ( ! empty( $meta['post_id'] ) ) {
            /* Optionally attach to a post */
            wp_update_post( [ 'ID' => $att_id, 'post_parent' => absint( $meta['post_id'] ) ] );
        }

        return self::ok( $req, [ 'media' => self::format_media( get_post( $att_id ) ) ], 201, $action, $att_id, 'media' );
    }

    public static function update( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id   = (int) $req->get_param( 'id' );
        $body = $req->get_json_params() ?: [];
        $att  = get_post( $id );
        if ( ! $att || $att->post_type !== 'attachment' ) {
            return self::fail( $req, new \WP_Error( 'amp_not_found', 'Media not found.', [ 'status' => 404 ] ), 'update_media' );
        }
        $args = [ 'ID' => $id ];
        if ( isset( $body['title'] )       ) $args['post_title']  = sanitize_text_field( $body['title'] );
        if ( isset( $body['caption'] )     ) $args['post_excerpt'] = sanitize_text_field( $body['caption'] );
        if ( isset( $body['description'] ) ) $args['post_content'] = sanitize_textarea_field( $body['description'] );
        wp_update_post( $args );
        if ( isset( $body['alt'] ) ) {
            update_post_meta( $id, '_wp_attachment_image_alt', sanitize_text_field( $body['alt'] ) );
        }
        return self::ok( $req, [ 'media' => self::format_media( get_post( $id ) ) ], 200, 'update_media', $id, 'media' );
    }

    public static function destroy( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $id = (int) $req->get_param( 'id' );
        if ( ! get_post( $id ) ) {
            return self::fail( $req, new \WP_Error( 'amp_not_found', 'Media not found.', [ 'status' => 404 ] ), 'delete_media' );
        }
        $r = wp_delete_attachment( $id, true );
        return self::ok( $req, [ 'deleted' => (bool) $r, 'id' => $id ], 200, 'delete_media', $id, 'media' );
    }

    public static function format_media( \WP_Post $a ): array {
        $meta = wp_get_attachment_metadata( $a->ID );
        return [
            'id'          => $a->ID,
            'title'       => $a->post_title,
            'alt'         => get_post_meta( $a->ID, '_wp_attachment_image_alt', true ),
            'caption'     => $a->post_excerpt,
            'description' => $a->post_content,
            'mime_type'   => $a->post_mime_type,
            'source_url'  => wp_get_attachment_url( $a->ID ),
            'full'        => wp_get_attachment_image_url( $a->ID, 'full' ),
            'thumbnail'   => wp_get_attachment_image_url( $a->ID, 'thumbnail' ),
            'medium'      => wp_get_attachment_image_url( $a->ID, 'medium' ),
            'width'       => $meta['width']  ?? null,
            'height'      => $meta['height'] ?? null,
            'filesize'    => $meta['filesize'] ?? null,
            'date'        => $a->post_date_gmt,
        ];
    }

    private static function require_wp_files(): void {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
    }
}
