<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'kenta_blocks_overlay_attrs' ) ) {
	/**
	 * Overlay attrs
	 *
	 * @return array
	 */
	function kenta_blocks_overlay_attrs( $defaults = array() ) {
		$defaults = wp_parse_args( $defaults, array(
			'overlay'    => 'no',
			'background' => array(
				'type'  => 'color',
				'color' => 'rgba(0,0,0,0.25)'
			),
		) );

		return array(
			'overlay'           => array(
				'type'    => 'string',
				'default' => $defaults['overlay']
			),
			'overlayZIndex'     => array(
				'type'    => 'object',
				'default' => \KentaBlocks\Css::INITIAL_VALUE
			),
			'overlayOpacity'    => array(
				'type'    => 'object',
				'default' => \KentaBlocks\Css::INITIAL_VALUE
			),
			'overlayFilter'     => array(
				'type' => 'object'
			),
			'overlayBlendMode'  => array(
				'type'    => 'string',
				'default' => ''
			),
			'overlayBackground' => array(
				'type'    => 'object',
				'default' => $defaults['background']
			),
		);
	}
}

if ( ! function_exists( 'kenta_blocks_overlay_css' ) ) {
	/**
	 * @param $block
	 *
	 * @return array|null[]
	 */
	function kenta_blocks_overlay_css( $block ) {
		if ( kb_get_block_attr( $block, 'overlay' ) !== 'yes' ) {
			return array();
		}

		return array_merge(
			array(
				'z-index'        => kb_get_block_attr( $block, 'overlayZIndex' ),
				'opacity'        => kb_get_block_attr( $block, 'overlayOpacity' ),
				'mix-blend-mode' => kb_get_block_attr( $block, 'overlayBlendMode' )
			),
			kenta_blocks_css()->filter(
				kb_get_block_attr( $block, 'overlayFilter' )
			),
			kenta_blocks_css()->background(
				kb_get_block_attr( $block, 'overlayBackground' )
			)
		);
	}
}
