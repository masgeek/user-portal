<?php
/**
 * Separator block config
 *
 * @package Kenta Blocks
 */

$attributes = array_merge(
	array(
		'blockID' => array(
			'type' => 'string',
		),
		'height'  => array(
			'type'    => 'object',
			'default' => '2px',
		),
		'width'   => array(
			'type' => 'object',
		),
		'color'   => array(
			'type'    => 'object',
			'default' => array(
				'default' => 'var(--kb-base-300)',
			),
		),
		'style'   => array(
			'type'    => 'string',
			'default' => 'solid',
		),
		'radius'  => array(
			'type'    => 'object',
			'default' => array(
				'top'    => '',
				'right'  => '',
				'bottom' => '',
				'left'   => '',
			)
		),
		'shadow'  => array(
			'type'    => 'object',
			'default' => array(
				'enable'     => 'no',
				'horizontal' => '0px',
				'vertical'   => '0px',
				'blur'       => '10px',
				'spread'     => '0px',
				'color'      => 'rgba(0, 0, 0, 0.15)',
			),
		)
	),
	kenta_blocks_advanced_attrs( array(
		'margin' => array(
			'top'    => '12px',
			'right'  => 'auto',
			'bottom' => '12px',
			'left'   => 'auto',
		)
	) )
);

$metadata = array(
	'title'      => __( 'Separator (KB)', 'kenta-blocks' ),
	'keywords'   => array( 'separator', 'divider' ),
	'supports'   => array(
		'anchor' => true,
		'align'  => array( 'wide', 'full' ),
	),
	'attributes' => $attributes,
);

return array(
	'metadata' => $metadata,
	'css'      => function ( $block, $css ) {
		$id = kb_get_block_attr( $block, 'blockID' );

		$css[".kb-separator.kb-separator-$id, hr.kb-separator.kb-separator-$id"] = array_merge(
			array(
				'--kb-separator-height' => kb_get_block_attr( $block, 'height' ),
				'width'                 => kb_get_block_attr( $block, 'width' ),
				'border-top-style'      => kb_get_block_attr( $block, 'style' ),
			),
			kenta_blocks_advanced_css( $block, array( 'padding' ) ),
			kenta_blocks_css()->dimensions( kb_get_block_attr( $block, 'margin' ), '--kb-separator-margin' ),
			kenta_blocks_css()->dimensions( kb_get_block_attr( $block, 'radius' ), 'border-radius' ),
			kenta_blocks_css()->shadow( kb_get_block_attr( $block, 'shadow' ) ),
			kenta_blocks_css()->colors( kb_get_block_attr( $block, 'color' ), array(
				'default' => '--kb-separator-color',
			) )
		);

		return $css;
	}
);
