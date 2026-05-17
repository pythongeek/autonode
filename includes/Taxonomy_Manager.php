<?php
namespace AutoNode;

defined( 'ABSPATH' ) || exit;

final class Taxonomy_Manager {

    public static function get_or_create_category( string $name, string $slug = '' ): int|\WP_Error {
        $name = sanitize_text_field( $name );
        $slug = $slug ? sanitize_title( $slug ) : sanitize_title( $name );
        $t    = get_term_by( 'slug', $slug, 'category' );
        if ( $t ) return $t->term_id;
        $r = wp_insert_term( $name, 'category', [ 'slug' => $slug ] );
        if ( is_wp_error( $r ) ) {
            if ( isset( $r->error_data['term_exists'] ) ) return (int) $r->error_data['term_exists'];
            return $r;
        }
        return (int) $r['term_id'];
    }

    public static function get_or_create_tag( string $name ): int|\WP_Error {
        $name = sanitize_text_field( $name );
        $t    = get_term_by( 'slug', sanitize_title( $name ), 'post_tag' );
        if ( $t ) return $t->term_id;
        $r = wp_insert_term( $name, 'post_tag' );
        if ( is_wp_error( $r ) ) {
            if ( isset( $r->error_data['term_exists'] ) ) return (int) $r->error_data['term_exists'];
            return $r;
        }
        return (int) $r['term_id'];
    }

    public static function resolve_categories( array $items ): array {
        $ids = [];
        foreach ( $items as $item ) {
            $id = is_numeric( $item ) ? (int) $item : self::get_or_create_category( (string) $item );
            if ( ! is_wp_error( $id ) && $id > 0 ) $ids[] = $id;
        }
        return array_unique( $ids );
    }

    public static function resolve_tags( array $items ): array {
        $ids = [];
        foreach ( $items as $item ) {
            $id = is_numeric( $item ) ? (int) $item : self::get_or_create_tag( (string) $item );
            if ( ! is_wp_error( $id ) && $id > 0 ) $ids[] = $id;
        }
        return array_unique( $ids );
    }
}
