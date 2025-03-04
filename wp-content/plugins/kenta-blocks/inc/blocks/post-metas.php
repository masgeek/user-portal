<?php

/**
 * Post metas block config
 *
 * @package Kenta Blocks
 */
$attributes = array_merge( array(
    'blockID'             => array(
        'type' => 'string',
    ),
    'structure'           => array(
        'type'    => 'array',
        'default' => array(array(
            'id'      => 'byline',
            'visible' => true,
        ), array(
            'id'      => 'published',
            'visible' => true,
        ), array(
            'id'      => 'comments',
            'visible' => true,
        )),
    ),
    'bylineMetaIcon'      => array(
        'type'    => 'string',
        'default' => 'fas fa-feather',
    ),
    'publishedMetaIcon'   => array(
        'type'    => 'string',
        'default' => 'far fa-calendar',
    ),
    'publishedMetaFormat' => array(
        'type'    => 'string',
        'default' => 'M j, Y',
    ),
    'commentsMetaIcon'    => array(
        'type'    => 'string',
        'default' => 'far fa-comments',
    ),
    'metaItemsStyle'      => array(
        'type'    => 'string',
        'default' => 'icon',
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
    'title'           => __( 'Post Metas (KB)', 'kenta-blocks' ),
    'description'     => __( "Displays the author, date, comments information of a post or page.", 'kenta-blocks' ),
    'keywords'        => array(
        'post',
        'meta',
        'author',
        'comments',
        'date'
    ),
    'attributes'      => $attributes,
    'uses_context'    => array('postType', 'postId', 'queryId'),
    'render_callback' => function ( $attrs, $content, $block ) {
        if ( !isset( $block->context['postId'] ) ) {
            return '';
        }
        $items = kb_layers( $attrs['structure'] );
        ob_start();
        kb_show_post_metas( $attrs, $items, [
            'style' => 'kb-post-meta-link',
        ] );
        $content = ob_get_clean();
        if ( $content === '' ) {
            return '';
        }
        $wrapper_attributes = get_block_wrapper_attributes( array(
            'class' => esc_attr( kb_clsx( ['kb-post-metas', 'kb-post-metas-' . $attrs['blockID']] ) ),
        ) );
        return sprintf( '<div %1$s>%2$s</div>', $wrapper_attributes, wp_kses_post( $content ) );
    },
);
return array(
    'metadata' => $metadata,
    'css'      => function ( $block, $css ) {
        $id = kb_get_block_attr( $block, 'blockID' );
        $selector = ".kb-post-metas.kb-post-metas-{$id}, .kb-post-metas.kb-post-metas-{$id} a";
        $css[$selector] = array(
            '--kb-meta-link-initial-color' => 'var(--kb-accent-active)',
            '--kb-meta-link-hover-color'   => 'var(--kb-primary-color)',
            'font-family'                  => 'inherit',
            'font-size'                    => '0.65rem',
            'font-weight'                  => '400',
            'line-height'                  => '1.5',
            'text-transform'               => 'capitalize',
        );
        $css[".kb-post-metas.kb-post-metas-{$id}"] = kenta_blocks_advanced_css( $block );
        return $css;
    },
);