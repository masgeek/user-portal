<?php

/**
 * Post taxonomy block config
 *
 * @package Kenta Blocks
 */
$attributes = array_merge( array(
    'blockID' => array(
        'type' => 'string',
    ),
    'term'    => array(
        'type'    => 'string',
        'default' => 'category',
    ),
    'style'   => array(
        'type'    => 'string',
        'default' => 'default',
    ),
), kenta_blocks_advanced_attrs( array(
    'margin' => array(
        'linked' => true,
        'top'    => '0px',
        'right'  => 'auto',
        'bottom' => '12px',
        'left'   => 'auto',
    ),
) ) );
$metadata = array(
    'title'           => __( 'Post Taxonomy (KB)', 'kenta-blocks' ),
    'description'     => __( "Displays the taxonomy of a post, page, or any other content-type.", 'kenta-blocks' ),
    'keywords'        => array(
        'post',
        'term',
        'taxonomy',
        'tags',
        'categories'
    ),
    'attributes'      => $attributes,
    'uses_context'    => array('postType', 'postId', 'queryId'),
    'render_callback' => function ( $attrs, $content, $block ) {
        if ( !isset( $block->context['postId'] ) || !isset( $attrs['term'] ) ) {
            return '';
        }
        if ( !is_taxonomy_viewable( $attrs['term'] ) ) {
            return '';
        }
        $post_terms = get_the_terms( $block->context['postId'], $attrs['term'] );
        if ( is_wp_error( $post_terms ) || empty( $post_terms ) ) {
            return '';
        }
        $wrapper_attributes = get_block_wrapper_attributes( array(
            'data-tax-type' => esc_attr( $attrs['style'] ),
            'class'         => esc_attr( kb_clsx( ['kb-post-taxonomy', 'kb-post-taxonomy-' . $attrs['term'], 'kb-post-taxonomy-' . $attrs['blockID']] ) ),
        ) );
        $prefix = "<div {$wrapper_attributes}>";
        $suffix = '</div>';
        return get_the_term_list(
            $block->context['postId'],
            $attrs['term'],
            wp_kses_post( $prefix ),
            '',
            wp_kses_post( $suffix )
        );
    },
);
return array(
    'metadata' => $metadata,
    'css'      => function ( $block, $css ) {
        $id = kb_get_block_attr( $block, 'blockID' );
        $selector = ".kb-post-taxonomy.kb-post-taxonomy-{$id}";
        $tax_type = kb_get_block_attr( $block, "style" );
        $tax_css = array(
            'font-family' => 'inherit',
            'font-weight' => '700',
            'font-size'   => '0.75rem',
            'line-height' => '1.5',
        );
        if ( $tax_type === 'default' ) {
            $tax_css = array_merge( $tax_css, [
                '--kb-tax-bg-initial'   => 'var(--kb-transparent)',
                '--kb-tax-bg-hover'     => 'var(--kb-transparent)',
                '--kb-tax-text-initial' => 'var(--kb-primary-color)',
                '--kb-tax-text-hover'   => 'var(--kb-primary-active)',
            ] );
        } else {
            $tax_css = array_merge( $tax_css, [
                '--kb-tax-bg-initial'   => 'var(--kb-primary-color)',
                '--kb-tax-bg-hover'     => 'var(--kb-primary-active)',
                '--kb-tax-text-initial' => 'var(--kb-base-color)',
                '--kb-tax-text-hover'   => 'var(--kb-base-color)',
            ] );
        }
        $css["{$selector}, {$selector} a"] = $tax_css;
        $css[$selector] = kenta_blocks_advanced_css( $block );
        return $css;
    },
);