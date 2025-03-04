<?php

/**
 * Section block config
 *
 * @package Kenta Blocks
 */
$attributes = array_merge(
    array(
        'blockID'        => array(
            'type' => 'string',
        ),
        'boxedContainer' => array(
            'type'    => 'string',
            'default' => 'no',
        ),
        'contentWidth'   => array(
            'type'    => 'object',
            'default' => array(
                'desktop' => '1140px',
                'tablet'  => '768px',
                'mobile'  => '576px',
            ),
        ),
        'direction'      => array(
            'type'    => 'object',
            'default' => 'row',
        ),
        'alignItems'     => array(
            'type'    => 'object',
            'default' => '',
        ),
        'wrap'           => array(
            'type'    => 'string',
            'default' => 'yes',
        ),
        'gap'            => array(
            'type'    => 'object',
            'default' => '12px',
        ),
    ),
    kenta_blocks_container_global_style(),
    kenta_blocks_box_attrs(),
    kenta_blocks_overlay_attrs(),
    kenta_blocks_shape_attrs(),
    kenta_blocks_advanced_attrs( array(
        'padding' => array(
            'linked' => true,
            'top'    => '12px',
            'right'  => '12px',
            'bottom' => '12px',
            'left'   => '12px',
        ),
    ) )
);
$metadata = array(
    'title'      => __( 'Section (KB)', 'kenta-blocks' ),
    'keywords'   => array(
        'section',
        'column',
        'columns',
        'row'
    ),
    'supports'   => array(
        'anchor' => true,
        'align'  => array('wide', 'full'),
        'html'   => false,
    ),
    'attributes' => $attributes,
);
return array(
    'metadata' => $metadata,
    'css'      => function ( $block, $css ) {
        $id = kb_get_block_attr( $block, 'blockID' );
        $isBoxed = kb_get_block_attr( $block, 'boxedContainer' ) === 'yes';
        $containerStyle = array_merge( array(
            'align-items' => kb_get_block_attr( $block, 'alignItems' ),
            'flex-wrap'   => ( (kb_get_block_attr( $block, 'wrap' ) ?? 'yes') == 'yes' ? 'wrap' : 'nowrap' ),
        ), kenta_blocks_css()->dimensions( kb_get_block_attr( $block, 'padding' ), 'padding' ) );
        if ( $isBoxed ) {
            $css[".kb-section-{$id} .kb-section-container"] = array_merge( $containerStyle, array(
                'max-width' => kb_get_block_attr( $block, 'contentWidth' ),
            ) );
        }
        $css[".kb-section.kb-section-{$id}"] = array_merge(
            ( $isBoxed ? array() : $containerStyle ),
            array(
                '--kb-columns-gap' => kb_get_block_attr( $block, 'gap' ),
            ),
            kenta_blocks_css()->border( kb_get_block_attr( $block, 'border' ) ),
            kenta_blocks_css()->shadow( kb_get_block_attr( $block, 'shadow' ) ),
            kenta_blocks_css()->background( kb_get_block_attr( $block, 'background' ) ),
            kenta_blocks_css()->dimensions( kb_get_block_attr( $block, 'radius' ), '--kb-border-radius' ),
            kenta_blocks_container_global_css( $block ),
            kenta_blocks_advanced_css( $block, array('padding') )
        );
        $css[".kb-section-has-overlay.kb-section-{$id}::after"] = kenta_blocks_overlay_css( $block );
        $shape_css = kenta_blocks_shape_css( ".kb-section-{$id} .kb-shape-divider", $block );
        return array_merge( $css, $shape_css );
    },
);