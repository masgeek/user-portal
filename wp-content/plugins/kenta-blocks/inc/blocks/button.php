<?php
/**
 * Button block config
 *
 * @package Kenta Blocks
 */

$attributes = array_merge(
	array(
		'blockID'    => array(
			'type' => 'string',
		),
		'anchor'     => array(
			'type'      => 'string',
			'source'    => 'attribute',
			'attribute' => 'id',
			'selector'  => 'a',
		),
		// content
		'url'        => array(
			'type'      => 'string',
			'source'    => 'attribute',
			'selector'  => 'a',
			'attribute' => 'href',
			'default'   => ''
		),
		'linkTarget' => array(
			'type'      => 'string',
			'source'    => 'attribute',
			'selector'  => 'a',
			'attribute' => 'target'
		),
		'rel'        => array(
			'type'      => 'string',
			'source'    => 'attribute',
			'selector'  => 'a',
			'attribute' => 'rel'
		),
	),
	kenta_blocks_position_attrs(),
	kenta_blocks_button_attrs()
);

$metadata = array(
	'title'      => __( 'Button (KB)', 'kenta-blocks' ),
	'keywords'   => array( 'link', 'button', 'buttons' ),
	'parent'     => array( 'kenta-blocks/buttons' ),
	'supports'   => array(
		'anchor' => true
	),
	'attributes' => $attributes,
);

return array(
	'metadata' => $metadata,
	'css'      => function ( $block, $css ) {
		$id = kb_get_block_attr( $block, 'blockID' );

		$wrapper_css = array_merge(
			array(
				'width' => kb_get_block_attr( $block, 'width' ),
			),
			kenta_blocks_position_css( $block )
		);

		$button_css = kb_button_css( '', $block );

		$css[".kb-button-wrapper-$id"]               = $wrapper_css;
		$css[".kb-button-$id, button.kb-button-$id"] = $button_css;

		return $css;
	}
);
