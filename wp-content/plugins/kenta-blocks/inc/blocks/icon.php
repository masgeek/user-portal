<?php

/**
 * Icon block config
 *
 * @package Kenta Blocks
 */
$attributes = array_merge( array(
    'blockID'    => array(
        'type' => 'string',
    ),
    'anchor'     => array(
        'type'      => 'string',
        'source'    => 'attribute',
        'attribute' => 'id',
        'selector'  => 'a',
    ),
    'icon'       => array(
        'type'      => 'string',
        'source'    => 'attribute',
        'selector'  => 'i',
        'attribute' => 'class',
        'default'   => 'fas fa-star',
    ),
    'iconSize'   => array(
        'type'    => 'object',
        'default' => '60px',
    ),
    'url'        => array(
        'type'      => 'string',
        'source'    => 'attribute',
        'selector'  => 'a',
        'attribute' => 'href',
        'default'   => '',
    ),
    'text'       => array(
        'type'     => 'string',
        'source'   => 'html',
        'selector' => 'a span',
    ),
    'linkTarget' => array(
        'type'      => 'string',
        'source'    => 'attribute',
        'selector'  => 'a',
        'attribute' => 'target',
    ),
    'rel'        => array(
        'type'      => 'string',
        'source'    => 'attribute',
        'selector'  => 'a',
        'attribute' => 'rel',
    ),
    'textAlign'  => array(
        'type' => 'object',
    ),
    'iconColor'  => array(
        'type'    => 'object',
        'default' => array(
            'initial' => \KentaBlocks\Css::INITIAL_VALUE,
            'hover'   => \KentaBlocks\Css::INITIAL_VALUE,
        ),
    ),
), kenta_blocks_position_attrs(), kenta_blocks_advanced_attrs() );
$metadata = array(
    'title'      => __( 'Icon (KB)', 'kenta-blocks' ),
    'keywords'   => array(
        'icon',
        'ico',
        'link',
        'button'
    ),
    'supports'   => array(
        'anchor' => true,
    ),
    'attributes' => $attributes,
);
return array(
    'metadata' => $metadata,
    'css'      => function ( $block, $css ) {
        $id = kb_get_block_attr( $block, 'blockID' );
        $css[".kb-icon-{$id}"] = array_merge(
            array(
                'text-align'     => kb_get_block_attr( $block, 'textAlign' ),
                '--kb-icon-size' => kb_get_block_attr( $block, 'iconSize' ),
            ),
            kenta_blocks_css()->colors( kb_get_block_attr( $block, 'iconColor' ), array(
                'initial' => '--kb-icon-initial-color',
                'hover'   => '--kb-icon-hover-color',
            ) ),
            kenta_blocks_position_css( $block ),
            kenta_blocks_advanced_css( $block )
        );
        return $css;
    },
);