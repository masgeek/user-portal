<?php

/**
 * Cover block config
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
    kenta_blocks_box_attrs(),
    kenta_blocks_overlay_attrs( array(
        'overlay' => 'yes',
    ) ),
    kenta_blocks_shape_attrs( array(
        'zIndex' => 4,
    ) ),
    kenta_blocks_advanced_attrs( array(
        'padding' => array(
            'linked' => true,
            'top'    => '24px',
            'right'  => '24px',
            'bottom' => '24px',
            'left'   => '24px',
        ),
    ) ),
    kb_particles_attrs( array(
        'zIndex' => 2,
    ) )
);
$metadata = array(
    'title'      => __( 'Cover (KB)', 'kenta-blocks' ),
    'keywords'   => array('cover', 'hero'),
    'supports'   => array(
        'anchor' => true,
        'align'  => array('wide', 'full'),
    ),
    'attributes' => $attributes,
);
return array(
    'metadata'     => $metadata,
    'dependencies' => function ( $block ) {
        if ( kb_get_block_attr( $block, 'particles' ) === 'yes' ) {
            return [
                // script
                ['particles.js', 'kenta-blocks-frontend-script'],
                // style
                [],
            ];
        }
        return [[], []];
    },
    'css'          => function ( $block, $css ) {
        $id = kb_get_block_attr( $block, 'blockID' );
        $css[".kb-cover.kb-cover-{$id}"] = array_merge(
            kenta_blocks_css()->border( kb_get_block_attr( $block, 'border' ) ),
            kenta_blocks_css()->shadow( kb_get_block_attr( $block, 'shadow' ) ),
            kenta_blocks_css()->background( kb_get_block_attr( $block, 'background' ) ),
            kenta_blocks_css()->dimensions( kb_get_block_attr( $block, 'radius' ), 'border-radius' ),
            kenta_blocks_container_global_css( $block ),
            kenta_blocks_advanced_css( $block, array('padding') ),
            array(
                'align-items'                => kb_get_block_attr( $block, 'alignItems' ),
                '--kb-cover-inner-max-width' => kb_get_block_attr( $block, 'contentWidth' ),
                '--kb-cover-min-height'      => kb_get_block_attr( $block, 'minHeight' ),
            )
        );
        $css[".kb-cover-{$id} .kb-cover-inner-container"] = kenta_blocks_css()->dimensions( kb_get_block_attr( $block, 'padding' ), 'padding' );
        $css[".kb-cover-has-overlay.kb-cover-{$id}::after"] = kenta_blocks_overlay_css( $block );
        $css[".kb-cover-has-particles.kb-cover-{$id} .kb-particles-canvas-{$id}"] = kb_particles_css( $block );
        $shape_css = kenta_blocks_shape_css( ".kb-cover-{$id} .kb-shape-divider", $block );
        return array_merge( $css, $shape_css );
    },
    'script'       => function ( $block ) {
        return kb_particles_script( $block );
    },
);