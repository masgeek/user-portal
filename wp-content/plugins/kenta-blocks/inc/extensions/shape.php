<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !function_exists( 'kenta_blocks_shape_attrs' ) ) {
    /**
     * Shape divider attrs
     *
     * @return array
     */
    function kenta_blocks_shape_attrs(  $defaults = array()  ) {
        $defaults = wp_parse_args( $defaults, array(
            'shape'         => 'none',
            'shapeSvg'      => '',
            'flipShape'     => 'no',
            'invertShape'   => 'no',
            'shapeWidth'    => '100%',
            'shapeHeight'   => '50px',
            'shapeColor'    => 'var(--kb-base-100)',
            'shapePosition' => 'bottom',
            'zIndex'        => 2,
        ) );
        return array(
            'shape'         => array(
                'type'    => 'string',
                'default' => $defaults['shape'],
            ),
            'shapeSvg'      => array(
                'type'     => 'string',
                'source'   => 'html',
                'selector' => '.kb-shape-divider',
                'default'  => $defaults['shapeSvg'],
            ),
            'flipShape'     => array(
                'type'    => 'string',
                'default' => $defaults['flipShape'],
            ),
            'invertShape'   => array(
                'type'    => 'string',
                'default' => $defaults['invertShape'],
            ),
            'shapeWidth'    => array(
                'type'    => 'object',
                'default' => $defaults['shapeWidth'],
            ),
            'shapeHeight'   => array(
                'type'    => 'object',
                'default' => $defaults['shapeHeight'],
            ),
            'shapeColor'    => array(
                'type'    => 'string',
                'default' => $defaults['shapeColor'],
            ),
            'shapePosition' => array(
                'type'    => 'string',
                'default' => $defaults['shapePosition'],
            ),
            'shapeZIndex'   => array(
                'type'    => 'object',
                'default' => $defaults['zIndex'],
            ),
        );
    }

}
if ( !function_exists( 'kenta_blocks_get_shapes' ) ) {
    /**
     * Get all shapes
     *
     * @param null $id
     *
     * @return array|array[]|mixed|null
     */
    function kenta_blocks_get_shapes(  $id = null  ) {
        $shapes = array(
            'none'     => array(
                'title'   => _x( 'None', 'Shapes', 'kenta-blocks' ),
                'options' => array(),
            ),
            'tilt'     => array(
                'title'   => _x( 'Tilt', 'Shapes', 'kenta-blocks' ),
                'options' => array('shape_flip'),
            ),
            'triangle' => array(
                'title'   => _x( 'Triangle (Pro)', 'Shapes', 'kenta-blocks' ),
                'options' => array('shape_invert'),
            ),
        );
        foreach ( $shapes as $shape => $shape_data ) {
            $folder = 'assets/images/shapes/';
            $files = array();
            $filepath = KENTA_BLOCKS_PLUGIN_PATH . ($folder . $shape . '.svg');
            if ( is_file( $filepath ) && is_readable( $filepath ) ) {
                $files['svg'] = kb_wp_filesystem()->get_contents( $filepath );
            }
            if ( in_array( 'shape_invert', $shape_data['options'] ) ) {
                $negative_filepath = KENTA_BLOCKS_PLUGIN_PATH . ($folder . $shape . '-negative' . '.svg');
                if ( is_file( $negative_filepath ) && is_readable( $negative_filepath ) ) {
                    $files['negative'] = kb_wp_filesystem()->get_contents( $negative_filepath );
                }
            }
            $shapes[$shape]['files'] = $files;
        }
        if ( $id !== null ) {
            return $shapes[$id] ?? null;
        }
        return $shapes;
    }

}
if ( !function_exists( 'kenta_blocks_shape_css' ) ) {
    /**
     * Generate shape divider css
     *
     * @param $selector
     * @param $block
     *
     * @return array|array[]
     */
    function kenta_blocks_shape_css(  $selector, $block  ) {
        $value = kb_get_block_attr( $block, 'shape' );
        $zIndex = kb_get_block_attr( $block, 'shapeZIndex' );
        $flip = kb_get_block_attr( $block, 'flipShape' );
        $width = kb_get_block_attr( $block, 'shapeWidth' );
        $height = kb_get_block_attr( $block, 'shapeHeight' );
        $color = kb_get_block_attr( $block, 'shapeColor' );
        $shapeData = kenta_blocks_get_shapes( $value );
        if ( !$shapeData || empty( $shapeData['files'] ) ) {
            return array(
                $selector => array(
                    'display' => 'none',
                ),
            );
        }
        $flip = $flip === 'yes' && in_array( 'shape_flip', $shapeData['options'] );
        return array(
            $selector         => array(
                'z-index'         => $zIndex,
                '--kb-shape-fill' => $color,
            ),
            "{$selector} svg" => array(
                'width'     => $width,
                'height'    => $height,
                'transform' => ( $flip ? 'translateX(-50%) rotateY(180deg)' : 'translateX(-50%)' ),
            ),
        );
    }

}