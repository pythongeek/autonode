<?php
namespace AutoNode;

defined( 'ABSPATH' ) || exit;

final class Post_Manager {

    private const STATUSES = [ 'draft', 'publish', 'pending', 'private', 'future', 'trash' ];

    public static function create( array $data, string $type = 'post' ): int|\WP_Error {
        if ( ! in_array( $data['status'] ?? 'draft', self::STATUSES, true ) ) {
            return new \WP_Error( 'amp_invalid', 'Invalid status.', [ 'status' => 400 ] );
        }
        $args = [
            'post_type'    => $type,
            'post_status'  => $data['status'] ?? 'draft',
            'post_title'   => wp_slash( $data['title']   ?? '' ),
            'post_content' => wp_slash( $data['content']        ?? '' ),
            'post_excerpt' => wp_slash( $data['excerpt'] ?? '' ),
            'post_name'    => sanitize_title( $data['slug'] ?? '' ),
            'post_author'  => get_current_user_id() ?: 1,
        ];
        if ( ! empty( $data['date'] ) ) {
            $ts = strtotime( $data['date'] );
            if ( $ts ) $args['post_date'] = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $ts ) );
        }
        if ( ! empty( $data['parent_id'] ) ) $args['post_parent'] = (int) $data['parent_id'];

        $id = wp_insert_post( $args, true );
        if ( is_wp_error( $id ) ) return $id;

        self::apply_taxonomy( $id, $data );
        if ( isset( $data['featured_media'] ) && (int) $data['featured_media'] > 0 ) {
            set_post_thumbnail( $id, (int) $data['featured_media'] );
        }
        if ( ! empty( $data['meta'] ) && is_array( $data['meta'] ) ) {
            self::apply_meta( $id, $data['meta'] );
        }
        return $id;
    }

    public static function update( int $id, array $data ): int|\WP_Error {
        $args = [ 'ID' => $id ];
        if ( array_key_exists( 'title',   $data ) ) $args['post_title']   = wp_slash( $data['title'] );
        if ( array_key_exists( 'content', $data ) ) $args['post_content'] = wp_slash( $data['content'] );
        if ( array_key_exists( 'excerpt', $data ) ) $args['post_excerpt'] = wp_slash( $data['excerpt'] );
        if ( array_key_exists( 'slug',    $data ) ) $args['post_name']    = sanitize_title( $data['slug'] );
        if ( ! empty( $data['status'] ) && in_array( $data['status'], self::STATUSES, true ) ) {
            $args['post_status'] = $data['status'];
        }
        if ( ! empty( $data['date'] ) ) {
            $ts = strtotime( $data['date'] );
            if ( $ts ) $args['post_date'] = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $ts ) );
        }
        $r = wp_update_post( $args, true );
        if ( is_wp_error( $r ) ) return $r;

        self::apply_taxonomy( $id, $data );
        if ( array_key_exists( 'featured_media', $data ) ) {
            (int) $data['featured_media'] > 0 ? set_post_thumbnail( $id, (int) $data['featured_media'] ) : delete_post_thumbnail( $id );
        }
        if ( ! empty( $data['meta'] ) && is_array( $data['meta'] ) ) {
            self::apply_meta( $id, $data['meta'] );
        }
        return $id;
    }

    public static function format( ?\WP_Post $post, array $fields = [] ): array {
        if ( ! $post ) return [];
        $fid = (int) get_post_thumbnail_id( $post->ID );
        $full = [
            'id'             => $post->ID,
            'type'           => $post->post_type,
            'status'         => $post->post_status,
            'title'          => $post->post_title,
            'slug'           => $post->post_name,
            'link'           => get_permalink( $post->ID ),
            'date_gmt'       => $post->post_date_gmt,
            'modified_gmt'   => $post->post_modified_gmt,
            'excerpt'        => $post->post_excerpt,
            'content'        => $post->post_content,
            'author'         => (int) $post->post_author,
            'parent'         => (int) $post->post_parent,
            'featured_media' => $fid ?: null,
            'featured_url'   => $fid ? wp_get_attachment_image_url( $fid, 'full' ) : null,
            'categories'     => wp_get_post_categories( $post->ID, [ 'fields' => 'ids' ] ),
            'tags'           => wp_get_post_tags( $post->ID, [ 'fields' => 'ids' ] ),
            'word_count'     => str_word_count( wp_strip_all_tags( $post->post_content ) ),
        ];
        return $fields ? array_intersect_key( $full, array_flip( $fields ) ) : $full;
    }

    private static function apply_taxonomy( int $id, array $data ): void {
        if ( ! empty( $data['category_names'] ) ) {
            $data['categories'] = Taxonomy_Manager::resolve_categories( (array) $data['category_names'] );
        }
        if ( ! empty( $data['tag_names'] ) ) {
            $data['tags'] = Taxonomy_Manager::resolve_tags( (array) $data['tag_names'] );
        }
        if ( isset( $data['categories'] ) ) {
            wp_set_post_categories( $id, array_map( 'intval', (array) $data['categories'] ) );
        }
        if ( isset( $data['tags'] ) ) {
            $tags = (array) $data['tags'];
            is_string( $tags[0] ?? null ) ? wp_set_post_tags( $id, $tags ) : wp_set_object_terms( $id, array_map( 'intval', $tags ), 'post_tag' );
        }
    }

    private static function apply_meta( int $id, array $meta ): void {
        $protected = [ '_edit_lock', '_edit_last', '_wp_old_slug', '_wp_attachment_metadata' ];
        foreach ( $meta as $k => $v ) {
            $k = sanitize_key( (string) $k );
            if ( empty( $k ) || in_array( $k, $protected, true ) || str_starts_with( $k, 'autonode_' ) ) continue;
            update_post_meta( $id, $k, $v );
        }
    }
}
