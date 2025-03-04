<?php

/**
 * Icon Button block config
 *
 * @package Kenta Blocks
 */
require dirname( __FILE__ ) . '/button.php';
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
    'url'        => array(
        'type'      => 'string',
        'source'    => 'attribute',
        'selector'  => 'a',
        'attribute' => 'href',
        'default'   => '',
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
    'preset'     => array(
        'type'    => 'string',
        'default' => 'primary',
    ),
    'iconSize'   => array(
        'type'    => 'object',
        'default' => '1.2rem',
    ),
    'buttonSize' => array(
        'type'    => 'object',
        'default' => '2.5em',
    ),
    'radius'     => array(
        'type'    => 'object',
        'default' => array(
            'top'    => '100%',
            'right'  => '100%',
            'bottom' => '100%',
            'left'   => '100%',
            'linked' => true,
        ),
    ),
), kenta_blocks_position_attrs() );
$metadata = array(
    'title'      => __( 'Icon Button (KB)', 'kenta-blocks' ),
    'keywords'   => array(
        'icon',
        'link',
        'button',
        'buttons'
    ),
    'parent'     => array('kenta-blocks/buttons'),
    'supports'   => array(
        'anchor' => true,
    ),
    'attributes' => $attributes,
);
return array(
    'metadata' => $metadata,
    'css'      => function ( $block, $css ) {
        $id = kb_get_block_attr( $block, 'blockID' );
        $button_css = kb_button_css( '', $block, array(
            'font-size'             => kb_get_block_attr( $block, 'iconSize' ),
            '--kb-icon-button-size' => kb_get_block_attr( $block, 'buttonSize' ),
        ) );
        $css[".kb-icon-button-wrapper.kb-icon-button-wrapper-{$id}"] = kenta_blocks_position_css( $block );
        $css[".kb-icon-button.kb-icon-button-{$id}"] = $button_css;
        return $css;
    },
);