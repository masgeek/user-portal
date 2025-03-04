<?php

/**
 * Group block config
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
        'justify'        => array(
            'type'    => 'object',
            'default' => 'flex-start',
        ),
    ),
    kenta_blocks_container_global_style(),
    kenta_blocks_box_attrs(),
    kenta_blocks_overlay_attrs(),
    kenta_blocks_linkable_attrs(),
    kenta_blocks_position_attrs(),
    kenta_blocks_advanced_attrs()
);
$metadata = array(
    'title'       => __( 'Group (KB)', 'kenta-blocks' ),
    'description' => __( 'Gather blocks in a container.', 'kenta-blocks' ),
    'keywords'    => array(
        'group',
        'stack',
        'row',
        'container'
    ),
    'supports'    => array(
        'anchor' => true,
        'align'  => array('wide', 'full'),
        'html'   => false,
    ),
    'attributes'  => $attributes,
);
return array(
    'metadata' => $metadata,
    'css'      => function ( $block, $css ) {
        $id = kb_get_block_attr( $block, 'blockID' );
        $isBoxed = kb_get_block_attr( $block, 'boxedContainer' ) === 'yes';
        $css[".kb-group.kb-group-{$id}"] = array_merge(
            kenta_blocks_css()->border( kb_get_block_attr( $block, 'border' ) ),
            kenta_blocks_css()->shadow( kb_get_block_attr( $block, 'shadow' ) ),
            kenta_blocks_css()->background( kb_get_block_attr( $block, 'background' ) ),
            kenta_blocks_css()->dimensions( kb_get_block_attr( $block, 'radius' ), 'border-radius' ),
            kenta_blocks_container_global_css( $block ),
            kenta_blocks_position_css( $block ),
            kenta_blocks_advanced_css( $block, ( $isBoxed ? array('padding') : array() ) )
        );
        if ( $isBoxed ) {
            $css[".kb-group-{$id} .kb-group-boxed-container"] = array_merge( array(
                'max-width'   => kb_get_block_attr( $block, 'contentWidth' ),
                'align-items' => kb_get_block_attr( $block, 'justify' ),
            ), kenta_blocks_css()->dimensions( kb_get_block_attr( $block, 'padding' ), 'padding' ) );
        }
        $css[".kb-group-has-overlay.kb-group-{$id}::after"] = kenta_blocks_overlay_css( $block );
        return $css;
    },
);