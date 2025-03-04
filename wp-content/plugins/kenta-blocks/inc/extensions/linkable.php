<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'kenta_blocks_linkable_attrs' ) ) {
	/**
	 * Linkable block attrs
	 *
	 * @param array $defaults
	 *
	 * @return array
	 */
	function kenta_blocks_linkable_attrs( $defaults = array() ) {
		$defaults = wp_parse_args( $defaults, array(
			'enable' => false,
			'url'    => '',
			'target' => '',
			'rel'    => ''
		) );

		return array(
			'linkable'        => array(
				'type'    => 'string',
				'default' => $defaults['enable'] ? 'yes' : 'no'
			),
			'blockUrl'        => array(
				'type'    => 'string',
				'default' => $defaults['url'],
			),
			'blockLinkTarget' => array(
				'type'    => 'string',
				'default' => $defaults['target'],
			),
			'blockLinkRel'    => array(
				'type'    => 'string',
				'default' => $defaults['rel'],
			)
		);
	}
}
