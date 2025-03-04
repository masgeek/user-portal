<?php

/**
 * Post featured image block config, a variant of image block
 *
 * @package Kenta Blocks
 */
$attributes = array(
    'isLink'         => array(
        'type'    => 'string',
        'default' => 'yes',
    ),
    'postLinkTarget' => array(
        'type'    => 'string',
        'default' => '_self',
    ),
    'postLinkRel'    => array(
        'type'    => 'string',
        'default' => '',
    ),
    'sizeSlug'       => array(
        'type'    => 'string',
        'default' => 'medium',
    ),
);
$metadata = array(
    'title'           => __( 'Post Featured Image (KB)', 'kenta-blocks' ),
    'description'     => __( "Display a post's featured image.", 'kenta-blocks' ),
    'keywords'        => array('post', 'featured image', 'thumbnail'),
    'attributes'      => $attributes,
    'uses_context'    => array('postType', 'postId', 'queryId'),
    'render_callback' => function ( $attrs, $content, $block ) {
        if ( !isset( $block->context['postId'] ) ) {
            return '';
        }
        $post_ID = $block->context['postId'];
        $featured_image = get_the_post_thumbnail( $post_ID, $attrs['sizeSlug'] );
        if ( !$featured_image ) {
            return '';
        }
        $wrapper_attributes = get_block_wrapper_attributes( array(
            'class' => esc_attr( kb_clsx( ['kb-post-featured-image', 'kb-post-featured-image-' . $attrs['blockID']] ) ),
        ) );
        if ( $attrs['isLink'] === 'yes' ) {
            $link_target = $attrs['postLinkTarget'];
            $rel = ( !empty( $attrs['postLinkRel'] ) ? 'rel="' . esc_attr( $attrs['postLinkRel'] ) . '"' : '' );
            $featured_image = sprintf(
                '<a href="%1$s" target="%2$s" %3$s>%4$s</a>',
                esc_url( get_the_permalink( $post_ID ) ),
                esc_attr( $link_target ),
                $rel,
                $featured_image
            );
        }
        return "<figure {$wrapper_attributes}>{$featured_image}</figure>";
    },
);
return array(
    'metadata' => $metadata,
    'css'      => function ( $block, $css ) {
        $id = kb_get_block_attr( $block, 'blockID' );
        $css[".kb-post-featured-image.kb-post-featured-image-{$id}"] = array_merge( array(
            'text-align' => kb_get_block_attr( $block, 'alignment' ),
        ), kenta_blocks_css()->background( kb_get_block_attr( $block, 'background' ) ), kenta_blocks_advanced_css( $block ) );
        $css[".kb-post-featured-image.kb-post-featured-image-{$id} img, .kb-post-featured-image.kb-post-featured-image-{$id} a"] = array(
            'width'     => kb_get_block_attr( $block, 'width' ),
            'max-width' => kb_get_block_attr( $block, 'maxWidth' ),
            'height'    => kb_get_block_attr( $block, 'height' ),
        );
        $css[".kb-post-featured-image.kb-post-featured-image-{$id} img"] = array_merge(
            array(
                'opacity'    => kb_get_block_attr( $block, 'opacity' ),
                'object-fit' => kb_get_block_attr( $block, 'objectFit' ),
            ),
            kenta_blocks_css()->filter( kb_get_block_attr( $block, 'cssFilter' ) ),
            kenta_blocks_css()->border( kb_get_block_attr( $block, 'border' ) ),
            kenta_blocks_css()->shadow( kb_get_block_attr( $block, 'shadow' ) ),
            kenta_blocks_css()->dimensions( kb_get_block_attr( $block, 'radius' ), 'border-radius' )
        );
        return $css;
    },
);