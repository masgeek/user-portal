<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !function_exists( 'kb_get_posts_query_args' ) ) {
    /**
     * Generate \WP_Query args from query attributes
     *
     * @param $paged
     * @param $attrs
     *
     * @return array
     */
    function kb_get_posts_query_args(  $paged, $attrs  ) {
        $selection = $attrs['selection'] ?? 'dynamic';
        $source = $attrs['source'] ?? 'post';
        $args = [
            'order'   => $attrs['order'],
            'orderby' => $attrs['orderBy'],
        ];
        if ( !empty( $source ) && is_post_type_viewable( $source ) ) {
            $args['post_type'] = $source;
        }
        if ( isset( $attrs['perPage'] ) && is_numeric( $attrs['perPage'] ) ) {
            $perPage = absint( $attrs['perPage'] );
            $offset = 0;
            if ( isset( $attrs['offset'] ) && is_numeric( $attrs['offset'] ) ) {
                $offset = absint( $attrs['offset'] );
            }
            $args['offset'] = $perPage * ($paged - 1) + $offset;
            $args['posts_per_page'] = $perPage;
        }
        // Dynamic selection
        if ( 'dynamic' === $selection ) {
            $authors = $attrs['authors'];
            // taxonomy filters query args
            $tax_query = [];
            foreach ( get_object_taxonomies( $source ) as $tax ) {
                $items = $attrs['taxonomyFilters'][$tax] ?? [];
                if ( !empty( $items ) ) {
                    $tax_query[] = [
                        'taxonomy' => $tax,
                        'field'    => 'id',
                        'terms'    => $items,
                    ];
                }
            }
            if ( !empty( $tax_query ) ) {
                $args['tax_query'] = $tax_query;
            }
            if ( isset( $attrs['excludeItems'] ) && !empty( $attrs['excludeItems'] ) ) {
                $args['post__not_in'] = $attrs['excludeItems'];
            }
            if ( !empty( $authors ) ) {
                $args['author'] = ( is_array( $authors ) ? implode( ',', $authors ) : $authors );
            }
        }
        if ( $attrs['excludeNoImages'] === 'yes' ) {
            $args['meta_key'] = array('_thumbnail_id');
        }
        return apply_filters( 'kb/posts_query_args', $args, $attrs );
    }

}