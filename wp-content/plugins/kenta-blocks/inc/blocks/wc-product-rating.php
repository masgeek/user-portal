<?php
/**
 * WooCommerce product rating block
 *
 * @package Kenta Blocks
 */

$attributes = array_merge(
	array(
		'blockID'                => array(
			'type'    => 'string',
			'default' => ''
		),
		'size'                   => array(
			'type'    => 'object',
			'default' => '1rem'
		),
		'color'                  => array(
			'type'    => 'object',
			'default' => array(
				'default' => '#ffdc00',
			)
		),
		'letterSpacing'          => array(
			'type'    => 'object',
			'default' => '2px'
		),
		'alignment'              => array(
			'type'    => 'object',
			'default' => 'left'
		),
		'showNoRatingOnFrontend' => array(
			'type'    => 'string',
			'default' => 'yes'
		),
	),
	kenta_blocks_advanced_attrs()
);

$metadata = array(
	'title'           => __( 'Product Rating (KB)', 'kenta-blocks' ),
	'description'     => __( 'Display the average rating of a product.', 'kenta-blocks' ),
	'keywords'        => array( 'woocommerce', 'product', 'rating' ),
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

		$product_reviews_count = $product->get_review_count();
		$product_rating        = $product->get_average_rating();
		$show_no_rating        = $attrs['showNoRatingOnFrontend'] === 'yes';

		/**
		 * Filter the output from wc_get_rating_html.
		 *
		 * @param string $html Star rating markup. Default empty string.
		 * @param float $rating Rating being shown.
		 * @param int $count Total number of ratings.
		 *
		 * @return string
		 */
		$filter_rating_html = function ( $html, $rating, $count ) use ( $post_ID, $product_rating, $product_reviews_count, $show_no_rating ) {
			$product_permalink = get_permalink( $post_ID );
			$reviews_count     = $count;
			$average_rating    = $rating;

			if ( $product_rating ) {
				$average_rating = $product_rating;
			}

			if ( $product_reviews_count ) {
				$reviews_count = $product_reviews_count;
			}
			/* translators: %s: rating */
			$label = sprintf( __( 'Rated %s out of 5', 'kenta-blocks' ), $average_rating );

			if ( $show_no_rating || 0 < $average_rating || false === $product_permalink ) {
				$html = sprintf(
					'<div class="kb-product-rating-stars" role="img" aria-label="%1$s">
								%2$s
							</div>',
					esc_attr( $label ),
					wc_get_star_rating_html( $average_rating, $reviews_count )
				);
			} else {
				$html = '';
			}

			return $html;
		};

		add_filter(
			'woocommerce_product_get_rating_html',
			$filter_rating_html,
			10,
			3
		);

		$rating_html = wc_get_rating_html( $product->get_average_rating() );

		remove_filter(
			'woocommerce_product_get_rating_html',
			$filter_rating_html,
			10
		);

		$wrapper_attributes = get_block_wrapper_attributes( array(
			'class' => esc_attr( kb_clsx(
				'kb-product-rating',
				'kb-product-rating-' . $attrs['blockID']
			) )
		) );

		return sprintf(
			'<div %s>%s</div>',
			$wrapper_attributes,
			wp_kses_post( $rating_html )
		);
	}
);

return array(
	'metadata' => $metadata,
	'css'      => function ( $block, $css ) {

		$id = kb_get_block_attr( $block, 'blockID' );

		$css[".kb-product-rating.kb-product-rating-{$id}"] = array_merge(
			array(
				'font-size'      => kb_get_block_attr( $block, 'size' ),
				'text-align'     => kb_get_block_attr( $block, 'alignment' ),
				'letter-spacing' => kb_get_block_attr( $block, 'letterSpacing' ),
			),
			kenta_blocks_css()->colors( kb_get_block_attr( $block, 'color' ), [
				'default' => '--kb-product-rating-color',
			] ),
			kenta_blocks_advanced_css( $block )
		);

		return $css;
	}
);
