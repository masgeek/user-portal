<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !function_exists( 'kenta_blocks_box_attrs' ) ) {
    /**
     * Attrs for box
     *
     * @param array $defaults
     *
     * @return array[]
     */
    function kenta_blocks_box_attrs(  $defaults = array()  ) {
        $defaults = wp_parse_args( $defaults, array(
            'border'          => array(
                'width' => 1,
                'style' => 'none',
                'color' => 'var(--kb-base-300)',
            ),
            'shadow'          => array(
                'enable'     => 'no',
                'horizontal' => '0px',
                'vertical'   => '0px',
                'blur'       => '10px',
                'spread'     => '0px',
                'color'      => 'rgba(0, 0, 0, 0.15)',
            ),
            'background'      => \KentaBlocks\Css::INITIAL_VALUE,
            'radius'          => array(
                'linked' => true,
                'top'    => '',
                'right'  => '',
                'bottom' => '',
                'left'   => '',
            ),
            'borderHover'     => array(
                'inherit' => true,
                'width'   => 1,
                'style'   => 'none',
                'color'   => 'var(--kb-base-300)',
            ),
            'shadowHover'     => \KentaBlocks\Css::INITIAL_VALUE,
            'backgroundHover' => \KentaBlocks\Css::INITIAL_VALUE,
        ) );
        $attrs = array(
            'border'     => array(
                'type'    => 'object',
                'default' => $defaults['border'],
            ),
            'shadow'     => array(
                'type'    => 'object',
                'default' => $defaults['shadow'],
            ),
            'background' => array(
                'type'    => 'object',
                'default' => $defaults['background'],
            ),
            'radius'     => array(
                'type'    => 'object',
                'default' => $defaults['radius'],
            ),
        );
        return $attrs;
    }

}