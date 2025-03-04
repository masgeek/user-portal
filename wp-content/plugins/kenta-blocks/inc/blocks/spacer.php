<?php
/**
 * Spacer block config
 *
 * @package Kenta Blocks
 */

$attributes = array(
	'blockID' => array(
		'type' => 'string',
	),
	'height'  => array(
		'type'    => 'object',
		'default' => '100px',
	)
);

$metadata = array(
	'title'      => __( 'Spacer (KB)', 'kenta-blocks' ),
	'keywords'   => array( 'spacer', 'spacing' ),
	'supports'   => array(
		'anchor' => true,
	),
	'attributes' => $attributes,
);

return array(
	'metadata' => $metadata,
	'css'      => function ( $block, $css ) {
		$id = kb_get_block_attr( $block, 'blockID' );

		$css[".kb-spacer-$id"] = array(
			'height' => kb_get_block_attr( $block, 'height' )
		);

		return $css;
	}
);
