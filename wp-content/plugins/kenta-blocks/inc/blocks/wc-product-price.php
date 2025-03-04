<?php
/**
 * WooCommerce product price block
 *
 * @package Kenta Blocks
 */

$attributes = array_merge(
	kenta_blocks_paragraph_attrs(),
	array(
		'blockID' => array(
			'type'    => 'string',
			'default' => '',
		),
		// override paragraph color
		'color'   => array(
			'type'    => 'object',
			'default' => array(
				'current'  => \KentaBlocks\Css::INITIAL_VALUE,
				'previous' => \KentaBlocks\Css::INITIAL_VALUE,
			),
		),
	),
	kenta_blocks_advanced_attrs()
);

$metadata = array(
	'title'           => __( 'Product Price (KB)', 'kenta-blocks' ),
	'description'     => __( 'Display the price of a product.', 'kenta-blocks' ),
	'keywords'        => array( 'woocommerce', 'product', 'price' ),
	'ancestor'        => array( 'kenta-blocks/query' ),
	'attributes'      => $attributes,
	'category'        => 'kenta-wc-blocks',
	'editor_script'   => 'kenta-blocks-wc-script',
	'uses_context'    => array(
		'postType',
		'postId',
		'queryId',
	),
	'render_callback' => function ( $attrs, $content, $block ) {

		if ( ! empty( $content ) ) {
			return $content;
		}

		if ( isset( $block->context['postId'] ) ) {
			$post_ID = $block->context['postId'];
			$product = wc_get_product( $post_ID );

			if ( $product ) {

				$wrapper_attributes = get_block_wrapper_attributes( array(
					'class' => esc_attr( kb_clsx( [
						'kb-product-price',
						'kb-product-price-' . $attrs['blockID']
					] ) ),
				) );

				return sprintf(
					'<div %1$s>%2$s</div>',
					$wrapper_attributes,
					wp_kses_post( $product->get_price_html() )
				);
			}
		}

		return '';
	}
);

return array(
	'metadata' => $metadata,
	'css'      => function ( $block, $css ) {
		$id       = kb_get_block_attr( $block, 'blockID' );
		$selector = ".kb-product-price.kb-product-price-{$id}";

		$css       = kenta_blocks_paragraph_css( 'span', 'kb-product-price', $block, $css );
		$color_css = kenta_blocks_css()->colors( kb_get_block_attr( $block, 'color' ), array(
			'previous' => '--kb-product-previous-color',
			'current'  => 'color',
		) );

		$css[ $selector ] = isset( $css[ $selector ] ) ? array_merge( $css[ $selector ], $color_css ) : $color_css;

		return $css;
	}
);
