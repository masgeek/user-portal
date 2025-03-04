<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'kenta_blocks_position_attrs' ) ) {


	function kenta_blocks_position_attrs( $defaults = array() ) {

		$defaults = wp_parse_args( $defaults, array(
			'position' => '',
			'top'      => '',
			'right'    => '',
			'bottom'   => '',
			'left'     => '',
		) );

		return array(
			'position' => array(
				'type'    => 'object',
				'default' => $defaults['position'],
			),
			'top'      => array(
				'type'    => 'object',
				'default' => $defaults['top'],
			),
			'right'    => array(
				'type'    => 'object',
				'default' => $defaults['right'],
			),
			'bottom'   => array(
				'type'    => 'object',
				'default' => $defaults['bottom'],
			),
			'left'     => array(
				'type'    => 'object',
				'default' => $defaults['left'],
			),
		);
	}
}

if ( ! function_exists( 'kenta_blocks_position_css' ) ) {
	/**
	 * @param $block
	 *
	 * @return array
	 */
	function kenta_blocks_position_css( $block ) {
		return array(
			'position' => kb_get_block_attr( $block, 'position' ),
			'top'      => kb_get_block_attr( $block, 'top' ),
			'right'    => kb_get_block_attr( $block, 'right' ),
			'bottom'   => kb_get_block_attr( $block, 'bottom' ),
			'left'     => kb_get_block_attr( $block, 'left' ),
		);
	}
}
