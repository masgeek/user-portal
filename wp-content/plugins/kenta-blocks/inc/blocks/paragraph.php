<?php
/**
 * Paragraph block config
 *
 * @package Kenta Blocks
 */

$attributes = array_merge(
	array(
		'blockID' => array(
			'type' => 'string',
		),
		'content' => array(
			'type'     => 'string',
			'source'   => 'html',
			'selector' => 'p',
			'default'  => ''
		),
	),
	kenta_blocks_paragraph_attrs(),
	kenta_blocks_box_attrs(),
	kenta_blocks_advanced_attrs()
);

$metadata = array(
	'title'      => __( 'Paragraph (KB)', 'kenta-blocks' ),
	'keywords'   => array( 'paragraph', 'text' ),
	'supports'   => array(
		'align'                  => array( 'wide', 'full' ),
		'anchor'                 => true,
		'__experimentalSelector' => 'p',
	),
	'attributes' => $attributes,
);

return array(
	'metadata' => $metadata,
	'css'      => function ( $block, $css ) {
		return kenta_blocks_paragraph_css( 'p', 'kb-paragraph', $block, $css );
	}
);
