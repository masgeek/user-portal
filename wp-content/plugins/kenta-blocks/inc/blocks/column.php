<?php

/**
 * Column block config
 *
 * @package Kenta Blocks
 */
$attributes = array_merge(
    array(
        'blockID'           => array(
            'type' => 'string',
        ),
        'verticalAlignment' => array(
            'type' => 'string',
        ),
        'width'             => array(
            'type' => 'object',
        ),
        'order'             => array(
            'type' => 'object',
        ),
    ),
    kenta_blocks_container_global_style(),
    kenta_blocks_box_attrs(),
    kenta_blocks_overlay_attrs(),
    kenta_blocks_shape_attrs(),
    kenta_blocks_linkable_attrs(),
    kenta_blocks_advanced_attrs(),
    kenta_blocks_transform_attrs()
);
$metadata = array(
    'title'      => __( 'Column (KB)', 'kenta-blocks' ),
    'keywords'   => array('column'),
    'parent'     => array('kenta-blocks/section'),
    'supports'   => array(
        'anchor'   => true,
        'reusable' => false,
        'html'     => false,
    ),
    'attributes' => $attributes,
);
return array(
    'metadata' => $metadata,
    'css'      => function ( $block, $css ) {
        $id = kb_get_block_attr( $block, 'blockID' );
        $width = kb_get_block_attr( $block, 'width' );
        $wrapperCss = array(
            '--kb-column-flex-grow' => ( $width ? 0 : 1 ),
            '--kb-column-width'     => $width,
            'order'                 => kb_get_block_attr( $block, 'order' ),
            'z-index'               => kb_get_block_attr( $block, 'zIndex' ),
        );
        $columnCss = array_merge(
            kenta_blocks_css()->border( kb_get_block_attr( $block, 'border' ) ),
            kenta_blocks_css()->shadow( kb_get_block_attr( $block, 'shadow' ) ),
            kenta_blocks_css()->background( kb_get_block_attr( $block, 'background' ) ),
            kenta_blocks_css()->dimensions( kb_get_block_attr( $block, 'radius' ), '--kb-border-radius' ),
            kenta_blocks_advanced_css( $block, array('z-index', 'margin') ),
            kenta_blocks_transform_css( $block )
        );
        $css[".kb-column-wrapper-{$id}"] = array_merge( $wrapperCss, kenta_blocks_container_global_css( $block ) );
        $css[".kb-column-{$id}"] = $columnCss;
        $css[".kb-column-has-overlay.kb-column-{$id}::after"] = kenta_blocks_overlay_css( $block );
        $shape_css = kenta_blocks_shape_css( ".kb-column-{$id} .kb-shape-divider", $block );
        return array_merge( $css, $shape_css );
    },
);