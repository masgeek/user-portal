<?php
/**
 * Heading block config
 *
 * @package Kenta Blocks
 */

$attributes = array_merge(
	array(
		'blockID' => array(
			'type' => 'string',
		),
		// general
		'content' => array(
			'type'     => 'string',
			'source'   => 'html',
			'selector' => 'h1,h2,h3,h4,h5,h6',
			'default'  => ''
		),
		'markup'  => array(
			'type'    => 'string',
			'default' => 'h2'
		),
	),
	kenta_blocks_paragraph_attrs(),
	kenta_blocks_box_attrs(),
	kenta_blocks_advanced_attrs()
);

$metadata = array(
	'title'      => __( 'Heading (KB)', 'kenta-blocks' ),
	'keywords'   => array( 'heading', 'title' ),
	'supports'   => array(
		'align'                  => array( 'wide', 'full' ),
		'anchor'                 => true,
		'__experimentalSelector' => 'h1,h2,h3,h4,h5,h6',
	),
	'attributes' => $attributes,
);

return array(
	'metadata' => $metadata,
	'css'      => function ( $block, $css ) {
		$markup = kb_esc_heading_markup( kb_get_block_attr( $block, 'markup' ) );

		return kenta_blocks_paragraph_css( $markup, 'kb-heading', $block, $css );
	}
);
