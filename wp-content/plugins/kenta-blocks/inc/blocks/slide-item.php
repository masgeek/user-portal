<?php

/**
 * Slide item block config
 *
 * @package Kenta Blocks
 */
$attributes = array_merge(
    array(
        'blockID'      => array(
            'type' => 'string',
        ),
        'minHeight'    => array(
            'type'    => 'object',
            'default' => '300px',
        ),
        'contentWidth' => array(
            'type'    => 'object',
            'default' => array(
                'desktop' => '1140px',
                'tablet'  => '768px',
                'mobile'  => '576px',
            ),
        ),
        'alignItems'   => array(
            'type'    => 'object',
            'default' => 'center',
        ),
    ),
    kenta_blocks_container_global_style(),
    kenta_blocks_box_attrs( array(
        'background' => array(
            'type'  => 'color',
            'color' => 'var(--kb-base-300)',
        ),
    ) ),
    kenta_blocks_overlay_attrs(),
    kenta_blocks_advanced_attrs( array(
        'padding' => array(
            'linked' => true,
            'top'    => '24px',
            'right'  => '24px',
            'bottom' => '24px',
            'left'   => '24px',
        ),
    ) )
);
$metadata = array(
    'title'      => __( 'Slide Item (KB)', 'kenta-blocks' ),
    'keywords'   => array(
        'slider',
        'slick',
        'carousel',
        'slides',
        'slide'
    ),
    'parent'     => array('kenta-blocks/slides'),
    'supports'   => array(
        'anchor' => true,
    ),
    'attributes' => $attributes,
);
return array(
    'metadata' => $metadata,
    'css'      => function ( $block, $css ) {
        $id = kb_get_block_attr( $block, 'blockID' );
        $css[".kb-slide-item.kb-slide-item-{$id}"] = array_merge(
            kenta_blocks_css()->border( kb_get_block_attr( $block, 'border' ) ),
            kenta_blocks_css()->shadow( kb_get_block_attr( $block, 'shadow' ) ),
            kenta_blocks_css()->background( kb_get_block_attr( $block, 'background' ) ),
            kenta_blocks_css()->dimensions( kb_get_block_attr( $block, 'radius' ), 'border-radius' ),
            kenta_blocks_container_global_css( $block ),
            kenta_blocks_advanced_css( $block, array('padding') ),
            array(
                'align-items'                     => kb_get_block_attr( $block, 'alignItems' ),
                '--kb-slide-item-inner-max-width' => kb_get_block_attr( $block, 'contentWidth' ),
                '--kb-slide-item-min-height'      => kb_get_block_attr( $block, 'minHeight' ),
            )
        );
        $css[".kb-slide-item-{$id} .kb-slide-item-inner-container"] = kenta_blocks_css()->dimensions( kb_get_block_attr( $block, 'padding' ), 'padding' );
        $css[".kb-slide-item-has-overlay.kb-slide-item-{$id}::after"] = kenta_blocks_overlay_css( $block );
        return $css;
    },
);