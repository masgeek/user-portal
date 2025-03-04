<?php

/**
 * Image block config
 *
 * @package Kenta Blocks
 */
$attributes = array_merge(
    array(
        'blockID'    => array(
            'type' => 'string',
        ),
        'id'         => array(
            'type' => 'number',
        ),
        'url'        => array(
            'type'      => 'string',
            'source'    => 'attribute',
            'selector'  => 'img',
            'attribute' => 'src',
        ),
        'alt'        => array(
            'type'      => 'string',
            'source'    => 'attribute',
            'selector'  => 'img',
            'attribute' => 'alt',
        ),
        'caption'    => array(
            'type'     => 'string',
            'source'   => 'html',
            'selector' => 'figcaption',
        ),
        'title'      => array(
            'type'      => 'string',
            'source'    => 'attribute',
            'selector'  => 'img',
            'attribute' => 'title',
        ),
        'alignment'  => array(
            'type' => 'object',
        ),
        'sizeSlug'   => array(
            'type'    => 'string',
            'default' => '',
        ),
        'width'      => array(
            'type' => 'object',
        ),
        'maxWidth'   => array(
            'type' => 'object',
        ),
        'height'     => array(
            'type' => 'object',
        ),
        'objectFit'  => array(
            'type' => 'object',
        ),
        'opacity'    => array(
            'type' => 'object',
        ),
        'href'       => array(
            'type'      => 'string',
            'source'    => 'attribute',
            'selector'  => 'figure > a',
            'attribute' => 'href',
        ),
        'linkTarget' => array(
            'type'      => 'string',
            'source'    => 'attribute',
            'selector'  => 'figure > a',
            'attribute' => 'target',
        ),
        'rel'        => array(
            'type'      => 'string',
            'source'    => 'attribute',
            'selector'  => 'figure > a',
            'attribute' => 'rel',
        ),
        'cssFilter'  => array(
            'type' => 'object',
        ),
    ),
    kenta_blocks_position_attrs(),
    kenta_blocks_box_attrs(),
    kenta_blocks_advanced_attrs()
);
$metadata = array(
    'title'      => __( 'Image (KB)', 'kenta-blocks' ),
    'keywords'   => array('image', 'media'),
    'supports'   => array(
        'anchor' => true,
        'align'  => array('wide', 'full'),
    ),
    'attributes' => $attributes,
);
return array(
    'metadata' => $metadata,
    'css'      => function ( $block, $css ) {
        $id = kb_get_block_attr( $block, 'blockID' );
        $css[".kb-image.kb-image-{$id}"] = array_merge(
            array(
                'text-align' => kb_get_block_attr( $block, 'alignment' ),
            ),
            kenta_blocks_css()->background( kb_get_block_attr( $block, 'background' ) ),
            kenta_blocks_advanced_css( $block ),
            kenta_blocks_position_css( $block )
        );
        // $css[".kb-image.kb-image-$id img, .kb-image.kb-image-$id"] = array(
        // 	'width'     => kb_get_block_attr( $block, 'width' ),
        // 	'max-width' => kb_get_block_attr( $block, 'maxWidth' ),
        // 	'height'    => kb_get_block_attr( $block, 'height' ),
        // );
        $css[".kb-image.kb-image-{$id} img"] = array_merge(
            array(
                'width'      => kb_get_block_attr( $block, 'width' ),
                'max-width'  => kb_get_block_attr( $block, 'maxWidth' ),
                'height'     => kb_get_block_attr( $block, 'height' ),
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