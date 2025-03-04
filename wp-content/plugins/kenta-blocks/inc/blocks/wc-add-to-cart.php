<?php
/**
 * Add to cart button for WooCommerce product
 *
 * @package Kenta Blocks
 */

$attributes = array_merge(
	array(
		'blockID'           => array(
			'type'    => 'string',
			'default' => '',
		),
		'wrapperAlignItems' => array(
			'type'    => 'object',
			'default' => array(
				'desktop' => 'flex-start',
				'tablet'  => 'flex-start',
				'mobile'  => 'flex-start',
			),
		),
	),
	kenta_blocks_position_attrs(),
	kenta_blocks_button_attrs()
);

$metadata = array(
	'title'           => __( 'Add to cart (KB)', 'kenta-blocks' ),
	'description'     => __( 'Display a call to action button which either adds the product to the cart, or links to the product page.', 'kenta-blocks' ),
	'keywords'        => array( 'woocommerce', 'product', 'add to cart', 'cart' ),
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
		if ( ! isset( $block->context['postId'] ) ) {
			return '';
		}

		$post_ID = $block->context['postId'];
		$product = wc_get_product( $post_ID );
		if ( ! $product ) {
			return '';
		}

		$button_attributes = array(
			'aria-label'       => $product->add_to_cart_description(),
			'data-quantity'    => '1',
			'data-product_id'  => $product->get_id(),
			'data-product_sku' => $product->get_sku(),
			'rel'              => 'nofollow',
			'class'            => esc_attr( kb_clsx(
				'kb-button',
				'kb-button-' . $attrs['blockID'],
				'kb-add-to-cart',
				'kb-add-to-cart-' . $attrs['blockID'],
				'add_to_cart_button',
				( function_exists( 'wc_wp_theme_get_element_class_name' ) ? wc_wp_theme_get_element_class_name( 'button' ) : '' ),
				array(
					'ajax_add_to_cart' => (
						$product->supports( 'ajax_add_to_cart' ) &&
						$product->is_purchasable() &&
						( $product->is_in_stock() || $product->backorders_allowed() )
					)
				)
			) ),
		);

		$wrapper_attributes = get_block_wrapper_attributes( array(
			'class' => esc_attr( kb_clsx(
				'kb-button-wrapper',
				'kb-button-wrapper-' . $attrs['blockID'],
				'kb-add-to-cart-wrapper',
				'kb-add-to-cart-wrapper-' . $attrs['blockID']
			) )
		) );

		return sprintf(
			'<div %s><a href="%s" %s>%s</a></div>',
			$wrapper_attributes,
			esc_url( $product->add_to_cart_url() ),
			wc_implode_html_attributes( $button_attributes ),
			esc_html( $product->add_to_cart_text() )
		);
	}
);

return array(
	'metadata' => $metadata,
	'css'      => function ( $block, $css ) {
		$id = kb_get_block_attr( $block, 'blockID' );

		$wrapper_css = array_merge(
			array(
				'align-items' => kb_get_block_attr( $block, 'wrapperAlignItems' )
			),
			kenta_blocks_position_css( $block )
		);
		$button_css  = array_merge(
			array(
				'width' => kb_get_block_attr( $block, 'width' ),
			),
			kb_button_css( '', $block )
		);

		$css[".kb-button-wrapper-$id"]               = $wrapper_css;
		$css[".kb-button-$id, button.kb-button-$id"] = $button_css;

		return $css;
	}
);
