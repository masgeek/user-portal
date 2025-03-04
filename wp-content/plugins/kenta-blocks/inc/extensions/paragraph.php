<?php

// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !function_exists( 'kenta_blocks_paragraph_attrs' ) ) {
    function kenta_blocks_paragraph_attrs(  $defaults = array()  ) {
        $defaults = wp_parse_args( $defaults, array(
            'color'          => array(
                'initial'      => \KentaBlocks\Css::INITIAL_VALUE,
                'link-initial' => \KentaBlocks\Css::INITIAL_VALUE,
                'link-hover'   => \KentaBlocks\Css::INITIAL_VALUE,
            ),
            'typography'     => array(
                'family'     => \KentaBlocks\Css::INITIAL_VALUE,
                'fontSize'   => \KentaBlocks\Css::INITIAL_VALUE,
                'variant'    => \KentaBlocks\Css::INITIAL_VALUE,
                'lineHeight' => \KentaBlocks\Css::INITIAL_VALUE,
            ),
            'displayAsBlock' => 'no',
            'backgroundClip' => 'no',
            'textShadow'     => array(
                'enable'  => 'no',
                'hShadow' => '5px',
                'vShadow' => '5px',
                'blur'    => '0px',
                'color'   => 'var(--kb-primary-color)',
            ),
        ) );
        $attrs = array(
            'maxWidth'       => array(
                'type' => 'object',
            ),
            'textAlign'      => array(
                'type' => 'object',
            ),
            'alignSelf'      => array(
                'type' => 'object',
            ),
            'color'          => array(
                'type'    => 'object',
                'default' => $defaults['color'],
            ),
            'typography'     => array(
                'type'    => 'object',
                'default' => $defaults['typography'],
            ),
            'displayAsBlock' => array(
                'type'    => 'string',
                'default' => $defaults['displayAsBlock'],
            ),
            'backgroundClip' => array(
                'type'    => 'string',
                'default' => $defaults['backgroundClip'],
            ),
            'textShadow'     => array(
                'type'    => 'object',
                'default' => $defaults['textShadow'],
            ),
        );
        return $attrs;
    }

}
if ( !function_exists( 'kenta_blocks_paragraph_css' ) ) {
    /**
     * Generate css for paragraph/heading/post-title block
     *
     * @param $markup
     * @param $selector
     * @param $block
     * @param $css
     *
     * @return mixed
     */
    function kenta_blocks_paragraph_css(
        $markup,
        $selector,
        $block,
        $css
    ) {
        $id = kb_get_block_attr( $block, 'blockID' );
        $selectors = array("{$markup}.{$selector}-{$id}", ".{$selector}.{$selector}-{$id} {$markup}");
        $css[".{$selector}.{$selector}-{$id}"] = array_merge( array(
            'text-align' => kb_get_block_attr( $block, 'textAlign' ),
            'align-self' => kb_get_block_attr( $block, 'alignSelf' ),
            'max-width'  => kb_get_block_attr( $block, 'maxWidth' ),
        ), kenta_blocks_advanced_css( $block ) );
        $css[implode( ',', $selectors )] = array_merge(
            kenta_blocks_css()->colors( kb_get_block_attr( $block, 'color' ), array(
                'initial'      => 'color',
                'link-initial' => array('--kenta-link-initial-color', '--kb-link-initial-color'),
                'link-hover'   => array('--kenta-link-hover-color', '--kb-link-hover-color'),
            ) ),
            kenta_blocks_css()->typography( kb_get_block_attr( $block, 'typography' ) ),
            kenta_blocks_css()->border( kb_get_block_attr( $block, 'border' ) ),
            kenta_blocks_css()->shadow( kb_get_block_attr( $block, 'shadow' ) ),
            kenta_blocks_css()->textShadow( kb_get_block_attr( $block, 'textShadow' ) ),
            kenta_blocks_css()->background( kb_get_block_attr( $block, 'background' ) ),
            kenta_blocks_css()->dimensions( kb_get_block_attr( $block, 'radius' ), 'border-radius' )
        );
        return $css;
    }

}