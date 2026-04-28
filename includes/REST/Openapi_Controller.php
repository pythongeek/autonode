<?php
namespace AutoNode\REST;

defined( 'ABSPATH' ) || exit;

use AutoNode\Api_Auth;

final class Openapi_Controller extends Base_Controller {

    public static function register(): void {
        register_rest_route( self::NS, '/openapi.json', [
            [ 'methods' => \WP_REST_Server::READABLE, 'callback' => [ self::class, 'handle' ], 'permission_callback' => '__return_true' ],
        ] );
    }

    public static function handle( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        // Expose a standard OpenAPI 3.0 configuration suitable for n8n.
        $site_url = site_url();
        $api_url = rest_url( self::NS );

        $schema = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'AutoNode WP',
                'description' => __( 'Enterprise REST API bridge for n8n. Manage WordPress Posts, Rank Math SEO, Media, and Webhooks.', 'autonode' ),
                'version' => AUTONODE_VERSION
            ],
            'servers' => [
                [ 'url' => $api_url, 'description' => __( 'Current WordPress Site', 'autonode' ) ]
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'API Key'
                    ]
                ]
            ],
            'security' => [
                [ 'bearerAuth' => [] ]
            ],
            'paths' => [
                '/bulk/oneshot' => [
                    'post' => [
                        'summary' => __( 'One-Shot Publish', 'autonode' ),
                        'description' => __( 'Create a post, assign categories, sideload a featured image, and set Rank Math SEO in a single HTTP request.', 'autonode' ),
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'title' => [ 'type' => 'string', 'example' => 'Automated Post Title' ],
                                            'content' => [ 'type' => 'string', 'example' => 'Post body content here.' ],
                                            'status' => [ 'type' => 'string', 'example' => 'publish' ],
                                            'categories' => [ 'type' => 'array', 'items' => [ 'type' => 'integer' ], 'example' => [1] ],
                                            'featured_image_url' => [ 'type' => 'string', 'example' => 'https://example.com/cover.jpg' ],
                                            'seo' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'focus_keyword' => [ 'type' => 'string', 'example' => 'automation' ],
                                                    'title' => [ 'type' => 'string', 'example' => 'Automation Guide - WP Pro' ]
                                                ]
                                            ]
                                        ],
                                        'required' => ['title']
                                    ]
                                ]
                            ]
                        ],
                        'responses' => [
                            '201' => [ 'description' => __( 'Successfully created and mapped everything.', 'autonode' ) ]
                        ]
                    ]
                ],
                '/posts' => [
                    'get' => [ 'summary' => __( 'List Posts', 'autonode' ) ],
                    'post' => [ 'summary' => __( 'Create Post', 'autonode' ) ]
                ],
                '/media/sideload' => [
                    'post' => [
                        'summary' => __( 'Sideload Media from URL', 'autonode' ),
                        'requestBody' => [
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'url' => [ 'type' => 'string' ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return new \WP_REST_Response( $schema, 200 );
    }
}
