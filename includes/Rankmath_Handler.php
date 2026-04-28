<?php
namespace AutoNode;

defined( 'ABSPATH' ) || exit;

final class Rankmath_Handler {

    private const RM = [
        'focus_keyword'          => 'rank_math_focus_keyword',
        'title'                  => 'rank_math_title',
        'description'            => 'rank_math_description',
        'robots'                 => 'rank_math_robots',
        'canonical_url'          => 'rank_math_canonical_url',
        'og_title'               => 'rank_math_og_title',
        'og_description'         => 'rank_math_og_description',
        'og_image'               => 'rank_math_og_image',
        'og_image_id'            => 'rank_math_og_image_id',
        'og_object_type'         => 'rank_math_og_object_type',
        'twitter_title'          => 'rank_math_twitter_title',
        'twitter_description'    => 'rank_math_twitter_description',
        'twitter_image'          => 'rank_math_twitter_image',
        'twitter_card_type'      => 'rank_math_twitter_card_type',
        'primary_category'       => 'rank_math_primary_category',
        'schema_type'            => 'rank_math_rich_snippet',
        'breadcrumb_title'       => 'rank_math_breadcrumb_title',
        'pillar_content'         => 'rank_math_pillar_content',
        'advanced_robots'        => 'rank_math_advanced_robots',
        'faq_schema'             => 'rank_math_faq_schema',
    ];

    private const YOAST = [
        'focus_keyword'          => '_yoast_wpseo_focuskw',
        'title'                  => '_yoast_wpseo_title',
        'description'            => '_yoast_wpseo_metadesc',
        'canonical_url'          => '_yoast_wpseo_canonical',
        'og_title'               => '_yoast_wpseo_opengraph-title',
        'og_description'         => '_yoast_wpseo_opengraph-description',
        'og_image'               => '_yoast_wpseo_opengraph-image',
        'og_image_id'            => '_yoast_wpseo_opengraph-image-id',
        'twitter_title'          => '_yoast_wpseo_twitter-title',
        'twitter_description'    => '_yoast_wpseo_twitter-description',
        'twitter_image'          => '_yoast_wpseo_twitter-image',
        'breadcrumb_title'       => '_yoast_wpseo_bctitle',
    ];

    public static function active_plugin(): string {
        if ( defined( 'RANK_MATH_VERSION' ) ) return 'rankmath';
        if ( defined( 'WPSEO_VERSION' ) )     return 'yoast';
        return 'none';
    }

    public static function update( int $post_id, array $data ): array {
        $map     = self::active_plugin() === 'rankmath' ? self::RM : self::YOAST;
        $updated = 0; $skipped = 0;
        foreach ( $data as $field => $value ) {
            if ( ! isset( $map[ $field ] ) ) { $skipped++; continue; }
            update_post_meta( $post_id, $map[ $field ], self::clean( $field, $value ) );
            $updated++;
        }
        if ( self::active_plugin() === 'rankmath' ) {
            do_action( 'rank_math/post/update_seo_meta', $post_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Rank Math plugin hook.
        }
        return [ 'plugin' => self::active_plugin(), 'updated' => $updated, 'skipped' => $skipped ];
    }

    public static function read( int $post_id ): array {
        $map  = self::active_plugin() === 'rankmath' ? self::RM : self::YOAST;
        $data = [ 'plugin' => self::active_plugin() ];
        foreach ( $map as $field => $meta_key ) {
            $data[ $field ] = get_post_meta( $post_id, $meta_key, true );
        }
        if ( self::active_plugin() === 'rankmath' ) {
            $data['seo_score'] = (int) get_post_meta( $post_id, 'rank_math_seo_score', true );
        }
        return $data;
    }

    private static function clean( string $f, mixed $v ): mixed {
        return match ( true ) {
            in_array( $f, [ 'title', 'og_title', 'twitter_title', 'breadcrumb_title', 'og_object_type', 'twitter_card_type', 'focus_keyword', 'schema_type' ], true )
                => sanitize_text_field( (string) $v ),
            in_array( $f, [ 'description', 'og_description', 'twitter_description' ], true )
                => sanitize_textarea_field( (string) $v ),
            in_array( $f, [ 'canonical_url', 'og_image', 'twitter_image' ], true )
                => esc_url_raw( (string) $v ),
            in_array( $f, [ 'og_image_id', 'primary_category' ], true )
                => absint( $v ),
            $f === 'pillar_content'
                => (bool) $v,
            in_array( $f, [ 'robots', 'advanced_robots' ], true ) => (function () use ( $v ): array {
                $allowed = [ 'index','noindex','follow','nofollow','noarchive','nosnippet' ];
                $parts   = is_array( $v ) ? $v : explode( ',', (string) $v );
                return array_values( array_filter( array_map( 'trim', $parts ), fn( $p ) => in_array( strtolower( $p ), $allowed, true ) ) );
            })(),
            in_array( $f, [ 'faq_schema' ], true )
                => is_array( $v ) ? $v : ( json_decode( (string) $v, true ) ?? [] ),
            default => sanitize_text_field( (string) $v ),
        };
    }
}
