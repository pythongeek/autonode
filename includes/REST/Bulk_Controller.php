<?php
namespace AutoNode\REST;

defined( 'ABSPATH' ) || exit;

use AutoNode\Rankmath_Handler;

final class Bulk_Controller extends Base_Controller {

    public static function register(): void {
        register_rest_route( self::NS, '/bulk', [
            [ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => [ self::class, 'handle' ], 'permission_callback' => fn( $r ) => self::guard( $r, 'bulk:write' ) ],
        ] );
    }

    public static function handle( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $body      = $req->get_json_params() ?: [];
        $operation = sanitize_key( $body['operation'] ?? '' );
        $items     = (array) ( $body['items'] ?? [] );

        if ( ! $operation || empty( $items ) ) {
            return self::fail( $req, new \WP_Error( 'amp_invalid', 'operation and items required.', [ 'status' => 400 ] ), 'bulk' );
        }
        if ( count( $items ) > 50 ) {
            return self::fail( $req, new \WP_Error( 'amp_too_many', 'Max 50 items per request.', [ 'status' => 400 ] ), 'bulk' );
        }

        $results = []; $success = 0; $errors = 0;

        foreach ( $items as $item ) {
            $id = (int) ( $item['id'] ?? 0 );
            try {
                $r = match ( $operation ) {
                    'publish'     => self::set_status( $id, 'publish' ),
                    'draft'       => self::set_status( $id, 'draft' ),
                    'trash'       => self::set_status( $id, 'trash' ),
                    'delete'      => self::delete( $id ),
                    'update_seo'  => self::update_seo( $id, $item['seo'] ?? [] ),
                    'update_meta' => self::update_meta( $id, $item['meta'] ?? [] ),
                    default       => [ 'error' => 'Unknown operation: ' . $operation ],
                };
                if ( isset( $r['error'] ) ) { $errors++; $results[] = [ 'id' => $id, 'status' => 'error', 'message' => $r['error'] ]; }
                else                        { $success++; $results[] = array_merge( [ 'id' => $id, 'status' => 'success' ], $r ); }
            } catch ( \Throwable $e ) {
                $errors++;
                $results[] = [ 'id' => $id, 'status' => 'error', 'message' => $e->getMessage() ];
            }
        }

        return self::ok( $req, [ 'operation' => $operation, 'total' => count( $items ), 'success' => $success, 'errors' => $errors, 'results' => $results ], 200, 'bulk_' . $operation );
    }

    private static function set_status( int $id, string $s ): array {
        $r = wp_update_post( [ 'ID' => $id, 'post_status' => $s ], true );
        return is_wp_error( $r ) ? [ 'error' => $r->get_error_message() ] : [ 'new_status' => $s ];
    }

    private static function delete( int $id ): array {
        $r = wp_delete_post( $id, true );
        return $r ? [ 'deleted' => true ] : [ 'error' => 'Delete failed' ];
    }

    private static function update_seo( int $id, array $seo ): array {
        if ( ! $seo ) return [ 'error' => 'No SEO data provided' ];
        return Rankmath_Handler::update( $id, $seo );
    }

    private static function update_meta( int $id, array $meta ): array {
        if ( ! $meta ) return [ 'error' => 'No meta data provided' ];
        foreach ( $meta as $k => $v ) update_post_meta( $id, sanitize_key( $k ), $v );
        return [ 'updated' => count( $meta ) ];
    }
}
