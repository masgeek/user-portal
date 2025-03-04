<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'kenta_blocks_advanced_attrs' ) ) {
	/**
	 * Attrs for advanced
	 *
	 * @param array $defaults
	 *
	 * @return array[]
	 */
	function kenta_blocks_advanced_attrs( $defaults = array() ) {
		$defaults = wp_parse_args( $defaults, array(
			'zIndex'   => \KentaBlocks\Css::INITIAL_VALUE,
			'padding'  => array(
				'linked' => true,
				'top'    => '',
				'right'  => '',
				'bottom' => '',
				'left'   => '',
			),
			'margin'   => array(
				'linked' => true,
				'top'    => '',
				'right'  => 'auto',
				'bottom' => '',
				'left'   => 'auto',
			),
			'overflow' => '',
		) );

		return array(
			'zIndex'   => array(
				'type'    => 'object',
				'default' => $defaults['zIndex']
			),
			'padding'  => array(
				'type'    => 'object',
				'default' => $defaults['padding']
			),
			'margin'   => array(
				'type'    => 'object',
				'default' => $defaults['margin']
			),
			'overflow' => array(
				'type'    => 'object',
				'default' => $defaults['overflow']
			),
		);
	}
}

if ( ! function_exists( 'kenta_blocks_advanced_css' ) ) {
	/**
	 * @param $block
	 * @param array $exclude
	 *
	 * @return array
	 */
	function kenta_blocks_advanced_css( $block, $exclude = array() ) {
		$result = array();
		if ( ! in_array( 'z-index', $exclude ) ) {
			$result['z-index'] = kb_get_block_attr( $block, 'zIndex' );
		}

		if ( ! in_array( 'overflow', $exclude ) ) {
			$result['overflow'] = kb_get_block_attr( $block, 'overflow' );
		}

		if ( ! in_array( 'margin', $exclude ) ) {
			$result = array_merge( $result, kenta_blocks_css()->dimensions( kb_get_block_attr( $block, 'margin' ) ) );
		}

		if ( ! in_array( 'padding', $exclude ) ) {
			$result = array_merge( $result, kenta_blocks_css()->dimensions( kb_get_block_attr( $block, 'padding' ), 'padding' ) );
		}

		return $result;
	}
}
