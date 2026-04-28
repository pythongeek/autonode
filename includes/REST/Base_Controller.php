<?php
namespace AutoNode\REST;

defined( 'ABSPATH' ) || exit;

use AutoNode\Api_Auth;
use AutoNode\Rate_Limiter;
use AutoNode\Activity_Logger;

abstract class Base_Controller {

    protected const NS = AUTONODE_NS;

    /** Authenticate + rate-limit + scope check. Returns true or WP_Error. */
    protected static function guard( \WP_REST_Request $req, string $scope ): bool|\WP_Error {
        $key = Api_Auth::authenticate( $req );
        if ( is_wp_error( $key ) ) return $key;

        $rate = Rate_Limiter::check( (int) $key['id'] );

        add_filter( 'rest_post_dispatch', static function ( \WP_REST_Response $r ) use ( $rate ): \WP_REST_Response {
            $r->header( 'X-RateLimit-Limit',     (string) $rate['limit'] );
            $r->header( 'X-RateLimit-Remaining', (string) $rate['remaining'] );
            $r->header( 'X-RateLimit-Reset',     (string) $rate['reset_at'] );
            return $r;
        } );

        if ( ! $rate['allowed'] ) {
            return new \WP_Error( 'amp_rate_limited', 'Rate limit exceeded. Retry after ' . gmdate( 'c', $rate['reset_at'] ), [ 'status' => 429 ] );
        }

        if ( $scope && ! Api_Auth::has_scope( $key, $scope ) ) {
            return new \WP_Error( 'amp_forbidden', "Scope required: {$scope}", [ 'status' => 403 ] );
        }

        if ( ! empty( $key['user_id'] ) ) {
            wp_set_current_user( (int) $key['user_id'] );
            $user = get_userdata( (int) $key['user_id'] );
            if ( $user ) {
                $settings  = get_option( 'autonode_settings', [] );
                $whitelist = array_filter( array_map( 'trim', explode( "\n", $settings['whitelisted_emails'] ?? '' ) ) );
                if ( in_array( $user->user_email, $whitelist, true ) ) {
                    kses_remove_filters();
                }
            }
        }

        $req->set_param( '_amp_key',   $key );
        $req->set_param( '_amp_start', microtime( true ) );
        $req->set_param( '_amp_rid',   wp_generate_uuid4() );
        return true;
    }

    protected static function ok( \WP_REST_Request $req, mixed $data, int $status = 200, string $action = '', ?int $obj_id = null, string $obj_type = '' ): \WP_REST_Response {
        self::log( $req, $status, 'success', $action, $obj_id, $obj_type );
        $r = new \WP_REST_Response( [ 'success' => true, 'data' => $data, 'request_id' => $req->get_param( '_amp_rid' ) ], $status );
        $r->header( 'X-Request-ID', (string) $req->get_param( '_amp_rid' ) );
        return $r;
    }

    protected static function fail( \WP_REST_Request $req, \WP_Error $err, string $action = '' ): \WP_Error {
        self::log( $req, (int) ( $err->get_error_data()['status'] ?? 400 ), $err->get_error_message(), $action );
        return $err;
    }

    private static function log( \WP_REST_Request $req, int $status, string $msg, string $action, ?int $obj_id = null, string $obj_type = '' ): void {
        $key   = $req->get_param( '_amp_key' );
        $start = (float) ( $req->get_param( '_amp_start' ) ?? microtime( true ) );
        $body  = $req->get_json_params() ?: [];
        unset( $body['content'], $body['post_content'] );
        Activity_Logger::write( [
            'key_id'       => $key['id']      ?? null,
            'user_id'      => $key['user_id'] ?? null,
            'method'       => $req->get_method(),
            'endpoint'     => $req->get_route(),
            'action'       => $action ?: str_replace( '/', '_', trim( $req->get_route(), '/' ) ),
            'object_type'  => $obj_type ?: null,
            'object_id'    => $obj_id,
            'http_status'  => $status,
            'response_msg' => substr( $msg, 0, 200 ),
            'duration_ms'  => (int) round( ( microtime( true ) - $start ) * 1000 ),
        ] );
    }

    protected static function pagination_args(): array {
        return [
            'per_page' => [ 'type' => 'integer', 'default' => 20, 'minimum' => 1, 'maximum' => 100 ],
            'page'     => [ 'type' => 'integer', 'default' => 1,  'minimum' => 1 ],
            'search'   => [ 'type' => 'string' ],
            'orderby'  => [ 'type' => 'string', 'enum' => [ 'date', 'modified', 'title', 'id' ], 'default' => 'date' ],
            'order'    => [ 'type' => 'string', 'enum' => [ 'ASC', 'DESC' ], 'default' => 'DESC' ],
        ];
    }
}
