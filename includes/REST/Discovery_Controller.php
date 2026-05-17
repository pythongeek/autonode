<?php
namespace AutoNode\REST;

defined( 'ABSPATH' ) || exit;

/**
 * n8n Dynamic Discovery Controller.
 * Provides metadata about post types, taxonomies, and custom fields (ACF/Meta).
 */
final class Discovery_Controller extends Base_Controller {

    public static function register(): void {
        register_rest_route( self::NS, '/discovery/fields', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ self::class, 'get_fields' ],
                'permission_callback' => fn( $r ) => self::guard( $r, 'system:read' ),
            ]
        ] );

        register_rest_route( self::NS, '/discovery/post-types', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [ self::class, 'get_post_types' ],
                'permission_callback' => fn( $r ) => self::guard( $r, 'system:read' ),
            ]
        ] );
    }

    /**
     * Get a list of available fields for a specific post type.
     * Includes standard fields, Rank Math fields, and ACF fields.
     */
    public static function get_fields( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $type = sanitize_key( $req->get_param( 'type' ) ?: 'post' );
        
        $fields = [
            'standard' => [
                [ 'name' => 'title', 'label' => 'Post Title', 'type' => 'string' ],
                [ 'name' => 'content', 'label' => 'Content', 'type' => 'string' ],
                [ 'name' => 'excerpt', 'label' => 'Excerpt', 'type' => 'string' ],
                [ 'name' => 'status', 'label' => 'Status', 'type' => 'string', 'options' => ['publish', 'draft', 'pending', 'private'] ],
                [ 'name' => 'featured_image_url', 'label' => 'Featured Image URL (Sideload)', 'type' => 'string' ],
                [ 'name' => 'categories', 'label' => 'Category IDs', 'type' => 'array' ],
                [ 'name' => 'tags', 'label' => 'Tag IDs', 'type' => 'array' ],
            ],
            'seo' => [
                [ 'name' => 'seo[focus_keyword]', 'label' => 'SEO Focus Keyword', 'type' => 'string' ],
                [ 'name' => 'seo[title]', 'label' => 'SEO Title', 'type' => 'string' ],
                [ 'name' => 'seo[description]', 'label' => 'SEO Meta Description', 'type' => 'string' ],
                [ 'name' => 'seo[canonical]', 'label' => 'SEO Canonical URL', 'type' => 'string' ],
            ],
            'acf' => []
        ];

        /* Discovery of ACF Fields */
        if ( function_exists( 'acf_get_field_groups' ) ) {
            $groups = acf_get_field_groups( [ 'post_type' => $type ] );
            foreach ( $groups as $group ) {
                $acf_fields = acf_get_fields( $group['key'] );
                if ( $acf_fields ) {
                    foreach ( $acf_fields as $f ) {
                        $fields['acf'][] = [
                            'name'  => $f['name'],
                            'label' => $f['label'],
                            'type'  => $f['type'],
                            'key'   => $f['key']
                        ];
                    }
                }
            }
        }

        return self::ok( $req, [ 'post_type' => $type, 'fields' => $fields ], 200, 'discover_fields' );
    }

    /**
     * Get all public post types.
     */
    public static function get_post_types( \WP_REST_Request $req ): \WP_REST_Response|\WP_Error {
        $pts = get_post_types( [ 'public' => true ], 'objects' );
        $res = [];
        foreach ( $pts as $pt ) {
            $res[] = [
                'name'  => $pt->name,
                'label' => $pt->label,
                'hierarchical' => $pt->hierarchical
            ];
        }
        return self::ok( $req, [ 'post_types' => $res ], 200, 'discover_post_types' );
    }
}
