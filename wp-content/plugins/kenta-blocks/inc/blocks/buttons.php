<?php

/**
 * Buttons block config
 *
 * @package Kenta Blocks
 */
$attributes = array_merge( array(
    'blockID'    => array(
        'type' => 'string',
    ),
    'direction'  => array(
        'type'    => 'object',
        'default' => 'row',
    ),
    'justify'    => array(
        'type'    => 'object',
        'default' => 'flex-start',
    ),
    'alignItems' => array(
        'type'    => 'object',
        'default' => 'flex-start',
    ),
    'wrap'       => array(
        'type'    => 'string',
        'default' => 'yes',
    ),
    'gap'        => array(
        'type'    => 'object',
        'default' => '12px',
    ),
), kenta_blocks_position_attrs(), kenta_blocks_advanced_attrs() );
$metadata = array(
    'title'      => __( 'Buttons (KB)', 'kenta-blocks' ),
    'keywords'   => array(
        'link',
        'button',
        'buttons',
        'icon'
    ),
    'supports'   => array(
        'anchor'                                 => true,
        'align'                                  => array('wide', 'full'),
        '__experimentalExposeControlsToChildren' => true,
    ),
    'attributes' => $attributes,
);
return array(
    'metadata' => $metadata,
    'css'      => function ( $block, $css ) {
        $id = kb_get_block_attr( $block, 'blockID' );
        $css[".kb-buttons-{$id}"] = array_merge( array(
            'flex-direction'   => kb_get_block_attr( $block, 'direction' ),
            'justify-content'  => kb_get_block_attr( $block, 'justify' ),
            'align-items'      => kb_get_block_attr( $block, 'alignItems' ),
            'flex-wrap'        => ( kb_get_block_attr( $block, 'wrap' ) == 'yes' ? 'wrap' : 'nowrap' ),
            '--kb-buttons-gap' => kb_get_block_attr( $block, 'gap' ) ?? null,
        ), kenta_blocks_position_css( $block ), kenta_blocks_advanced_css( $block ) );
        return $css;
    },
);