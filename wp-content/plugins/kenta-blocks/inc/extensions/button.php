<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'kenta_blocks_button_attrs' ) ) {
	/**
	 * General properties of button elements
	 *
	 * @param string $prefix
	 * @param array $defaults
	 *
	 * @return array|int[][][]|string[][]|string[][][]
	 */
	function kenta_blocks_button_attrs( $defaults = array() ) {
		$defaults = wp_parse_args( $defaults, array(
			'prefix'       => '',
			'text'         => array(
				'type'     => 'string',
				'source'   => 'html',
				'selector' => '.kb-button span'
			),
			'hasIcon'      => false,
			'icon'         => array(
				'type'      => 'string',
				'source'    => 'attribute',
				'selector'  => 'i',
				'attribute' => 'class',
				'default'   => 'fas fa-star',
			),
			'iconPosition' => 'left',
			'justify'      => 'center',
			'preset'       => 'primary',
			'width'        => array(
				'desktop' => '',
				'tablet'  => '',
				'mobile'  => '',
			),
			'radius'       => array(
				'top'    => '2px',
				'right'  => '2px',
				'bottom' => '2px',
				'left'   => '2px',
				'linked' => true,
			),
			'padding'      => array(
				'top'    => '12px',
				'right'  => '16px',
				'bottom' => '12px',
				'left'   => '16px',
			),
			'typography'   => array(
				'family'     => 'inherit',
				'fontSize'   => '0.875rem',
				'variant'    => '500',
				'lineHeight' => '1',
			),
		) );

		$prefix = $defaults['prefix'];

		return array(
			$prefix . 'text'         => $defaults['text'],
			// icon
			$prefix . 'hasIcon'      => array(
				'type'    => 'string',
				'default' => $defaults['hasIcon'] ? 'yes' : 'no',
			),
			$prefix . 'icon'         => $defaults['icon'],
			$prefix . 'iconPosition' => array(
				'enum'    => array( 'left', 'right' ),
				'default' => $defaults['iconPosition']
			),
			// style
			$prefix . 'justify'      => array(
				'type'    => 'object',
				'default' => $defaults['justify']
			),
			$prefix . 'preset'       => array(
				'type'    => 'string',
				'default' => 'primary',
			),
			$prefix . 'width'        => array(
				'type'    => 'object',
				'default' => $defaults['width'],
			),
			$prefix . 'radius'       => array(
				'type'    => 'object',
				'default' => $defaults['radius'],
			),
			$prefix . 'padding'      => array(
				'type'    => 'object',
				'default' => $defaults['padding'],
			),
			$prefix . 'typography'   => array(
				'type'    => 'object',
				'default' => $defaults['typography']
			),
			$prefix . 'textColor'    => array(
				'type'    => 'object',
				'default' => array(
					'initial' => 'var(--kb-base-color)',
					'hover'   => 'var(--kb-base-color)',
				),
			),
			$prefix . 'buttonColor'  => array(
				'type'    => 'object',
				'default' => array(
					'initial' => 'var(--kb-primary-color)',
					'hover'   => 'var(--kb-accent-color)',
				),
			),
			$prefix . 'border'       => array(
				'type'    => 'object',
				'default' => array(
					'width' => 2,
					'style' => 'solid',
					'color' => 'var(--kb-primary-color)',
					'hover' => 'var(--kb-accent-color)',
				)
			),
			$prefix . 'shadow'       => array(
				'type'    => 'object',
				'default' => array(
					'enable'     => 'no',
					'horizontal' => '0px',
					'vertical'   => '0px',
					'blur'       => '10px',
					'spread'     => '0px',
					'color'      => 'rgba(0, 0, 0, 0.15)',
				)
			),
			$prefix . 'shadowActive' => array(
				'type'    => 'object',
				'default' => array(
					'enable'     => 'no',
					'horizontal' => '0px',
					'vertical'   => '0px',
					'blur'       => '10px',
					'spread'     => '0px',
					'color'      => 'rgba(0, 0, 0, 0.15)',
				)
			)
		);
	}
}

if ( ! function_exists( 'kb_button_preset' ) ) {
	/**
	 * Get button preset style
	 *
	 * @param $style
	 *
	 * @return array
	 */
	function kb_button_preset( $style ) {
		$presets = array(
			'ghost'   => array(
				'textColor'   => array(
					'initial' => 'currentColor',
					'hover'   => 'currentColor',
				),
				'buttonColor' => array(
					'initial' => 'var(--kb-transparent)',
					'hover'   => 'var(--kb-transparent)',
				),
				'border'      => array(
					'style' => 'none',
					'width' => 2,
					'color' => 'var(--kb-primary-color)',
					'hover' => 'var(--kb-primary-active)',
				),
			),
			'solid'   => array(
				'textColor'   => array(
					'initial' => 'var(--kb-base-color)',
					'hover'   => 'var(--kb-base-color)',
				),
				'buttonColor' => array(
					'initial' => 'var(--kb-primary-color)',
					'hover'   => 'var(--kb-accent-color)',
				),
				'border'      => array(
					'style' => 'solid',
					'width' => 2,
					'color' => 'var(--kb-primary-color)',
					'hover' => 'var(--kb-accent-color)',
				),
			),
			'outline' => array(
				'textColor'   => array(
					'initial' => 'var(--kb-primary-color)',
					'hover'   => 'var(--kb-base-color)',
				),
				'buttonColor' => array(
					'initial' => 'var(--kb-transparent)',
					'hover'   => 'var(--kb-primary-color)',
				),
				'border'      => array(
					'style' => 'solid',
					'width' => 2,
					'color' => 'var(--kb-primary-color)',
					'hover' => 'var(--kb-primary-color)',
				),
			),
			'invert'  => array(
				'textColor'   => array(
					'initial' => 'var(--kb-base-color)',
					'hover'   => 'var(--kb-base-color)',
				),
				'buttonColor' => array(
					'initial' => 'var(--kb-accent-color)',
					'hover'   => 'var(--kb-primary-color)',
				),
				'border'      => array(
					'style' => 'solid',
					'width' => 2,
					'color' => 'var(--kb-accent-color)',
					'hover' => 'var(--kb-primary-color)',
				),
			),
			'primary' => array(
				'textColor'   => array(
					'initial' => 'var(--kb-base-color)',
					'hover'   => 'var(--kb-base-color)',
				),
				'buttonColor' => array(
					'initial' => 'var(--kb-primary-color)',
					'hover'   => 'var(--kb-primary-active)',
				),
				'border'      => array(
					'style' => 'solid',
					'width' => 2,
					'color' => 'var(--kb-primary-color)',
					'hover' => 'var(--kb-primary-active)',
				),
			),
			'accent'  => array(
				'textColor'   => array(
					'initial' => 'var(--kb-base-color)',
					'hover'   => 'var(--kb-base-color)',
				),
				'buttonColor' => array(
					'initial' => 'var(--kb-accent-color)',
					'hover'   => 'var(--kb-accent-active)',
				),
				'border'      => array(
					'style' => 'solid',
					'width' => 2,
					'color' => 'var(--kb-accent-color)',
					'hover' => 'var(--kb-accent-active)',
				),
			)
		);

		return $presets[ $style ] ?? array();
	}
}

if ( ! function_exists( 'kb_button_css' ) ) {
	/**
	 * Generate button style
	 *
	 * @param string $prefix
	 * @param array $attrs
	 * @param array $metadata
	 * @param array $button_css
	 *
	 * @return array|mixed
	 */
	function kb_button_css( $prefix, $block, $button_css = array() ) {
		$preset      = kb_get_block_attr( $block, $prefix . 'preset' );
		$presetStyle = kb_button_preset( $preset );

		// custom button style
		if ( $preset === 'custom' ) {
			$button_css = array_merge(
				$button_css,
				kenta_blocks_css()->colors( kb_get_block_attr( $block, $prefix . 'textColor' ), array(
					'initial' => '--kb-button-text-initial-color',
					'hover'   => '--kb-button-text-hover-color',
				) ),
				kenta_blocks_css()->colors( kb_get_block_attr( $block, $prefix . 'buttonColor' ), array(
					'initial' => '--kb-button-initial-color',
					'hover'   => '--kb-button-hover-color',
				) ),
				kenta_blocks_css()->border( kb_get_block_attr( $block, $prefix . 'border' ), '--kb-button-border' ),
				kenta_blocks_css()->shadow( kb_get_block_attr( $block, $prefix . 'shadow' ), '--kb-button-shadow' ),
				kenta_blocks_css()->shadow( kb_get_block_attr( $block, $prefix . 'shadowActive' ), '--kb-button-shadow-active' )
			);
		}

		if ( isset( $presetStyle['textColor'] ) ) {
			$button_css = kenta_blocks_css()->colors( $presetStyle['textColor'], array(
				'initial' => '--kb-button-text-initial-color',
				'hover'   => '--kb-button-text-hover-color',
			), $button_css );
		}

		if ( isset( $presetStyle['buttonColor'] ) ) {
			$button_css = kenta_blocks_css()->colors( $presetStyle['buttonColor'], array(
				'initial' => '--kb-button-initial-color',
				'hover'   => '--kb-button-hover-color',
			), $button_css );
		}

		if ( isset( $presetStyle['border'] ) ) {
			$button_css = array_merge( $button_css, kenta_blocks_css()->border( $presetStyle['border'], '--kb-button-border' ) );
		}

		// padding
		return array_merge( $button_css,
			array(
				'justify-content' => kb_get_block_attr( $block, $prefix . 'justify' ),
			),
			kenta_blocks_css()->typography( kb_get_block_attr( $block, $prefix . 'typography' ) ),
			kenta_blocks_css()->dimensions(
				kb_get_block_attr( $block, $prefix . 'radius' ), '--kb-button-radius'
			),
			kenta_blocks_css()->dimensions(
				kb_get_block_attr( $block, $prefix . 'padding' ), '--kb-button-padding'
			)
		);
	}
}
