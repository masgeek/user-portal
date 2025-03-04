<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !function_exists( 'kenta_blocks_transform_attrs' ) ) {
    /**
     * Attrs for transform
     *
     * @param array $defaults
     *
     * @return array
     */
    function kenta_blocks_transform_attrs(  $defaults = array()  ) {
        return array(
            'transform' => array(
                'type'    => 'string',
                'default' => 'no',
            ),
        );
    }

}
if ( !function_exists( 'kenta_blocks_transform_css' ) ) {
    /**
     * @param $block
     *
     * @return array
     */
    function kenta_blocks_transform_css(  $block  ) {
        return array();
    }

}
if ( !function_exists( 'kenta_blocks_transform_hover_css' ) ) {
    /**
     * @param $block
     *
     * @return array
     */
    function kenta_blocks_transform_hover_css(  $block  ) {
        return array();
    }

}