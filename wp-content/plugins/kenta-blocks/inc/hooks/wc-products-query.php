<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'kb_wc_products_query_args' ) ) {
	/**
	 * WooCommerce product order
	 *
	 * @param $args
	 * @param $attrs
	 *
	 * @return mixed
	 */
	function kb_wc_products_query_args( $args, $attrs ) {
		if ( $args['post_type'] === 'product' ) {
			$order    = "{$attrs['orderBy']}/{$attrs['order']}";
			$meta_key = array();
			if ( isset( $args['meta_key'] ) ) {
				$meta_key = is_array( $args['meta_key'] ) ? $args['meta_key'] : array( $args['meta_key'] );
			}

//			$args['post_type']  = array( 'product', 'product_variation' );
//			$args['meta_query'] = array(
//				array(
//					'key'   => '_stock_status',
//					'value' => array( 'instock' )
//				)
//			);

			switch ( $order ) {
				case 'date/desc':
				{
					$args['orderby'] = 'date ID';
					$args['order']   = 'desc';
					break;
				}
				case 'date/asc':
				{
					$args['orderby'] = 'date ID';
					$args['order']   = 'asc';
					break;
				}
				case 'popularity/desc':
				{
					$args['orderby'] = 'meta_value_num';
					$args['order']   = 'desc';
					$meta_key[]      = 'total_sales';
					break;
				}
				case 'rating/desc':
				{
					$args['orderby'] = 'meta_value_num';
					$args['order']   = 'desc';
					$meta_key[]      = '_wc_average_rating';
					break;
				}
				case 'price/desc':
				{
					$args['orderby'] = 'meta_value_num';
					$args['order']   = 'desc';
					$meta_key[]      = '_price';
					break;
				}
				case 'price/asc':
				{
					$args['orderby'] = 'meta_value_num';
					$args['order']   = 'asc';
					$meta_key[]      = '_price';
					break;
				}
			}

			if ( ! empty( $meta_key ) ) {
				$args['meta_key'] = $meta_key;
			}
		}

		return $args;
	}
}
add_filter( 'kb/posts_query_args', 'kb_wc_products_query_args', 10, 2 );

//add_action( 'woocommerce_product_query', function ( $q ) {
//	echo '<pre>';
//	var_dump( $q->get( 'meta_key' ) );
//	echo '</pre>';
//}, PHP_INT_MAX - 10 );
