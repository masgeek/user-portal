<?php
/**
 * All blocks config file
 *
 * @package Kenta Blocks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'kb_get_block_attr' ) ) {
	/**
	 * Get block attr or default value
	 *
	 * @param $block
	 * @param $attr
	 *
	 * @return mixed|null
	 */
	function kb_get_block_attr( $block, $attr ) {
		$block_name = $block['blockName'] ?? '';
		if ( '' === $block_name ) {
			return null;
		}

		$block_attrs = $block['attrs'] ?? array();
		if ( isset( $block_attrs[ $attr ] ) ) {
			return $block_attrs[ $attr ];
		}

		$kb_blocks = kenta_blocks_all();
		if ( isset( $kb_blocks[ $block_name ] ) ) {

			$metadata = $kb_blocks[ $block_name ]['metadata'];

			if ( isset( $metadata['attributes'] ) && isset( $metadata['attributes'][ $attr ] ) ) {
				return $metadata['attributes'][ $attr ]['default'] ?? null;
			}
		}

		return null;
	}
}

if ( ! function_exists( 'kb_register_block' ) ) {
	/**
	 * Register kenta block
	 *
	 * @param $id
	 * @param null $args
	 *
	 * @return false|mixed|null
	 */
	function kb_register_block( $id, $args = null ) {

		$kb_blocks = \KentaBlocks\Store::get( 'kenta-blocks', array() );

		$kb_blocks["kenta-blocks/{$id}"] = $args ?: require KENTA_BLOCKS_PLUGIN_PATH . "inc/blocks/{$id}.php";

		\KentaBlocks\Store::keep( 'kenta-blocks', $kb_blocks );

		return $kb_blocks;
	}
}

if ( ! function_exists( 'kb_register_block_variation' ) ) {
	/**
	 * Register kenta block variation
	 *
	 * @param $base
	 * @param $id
	 * @param $args
	 *
	 * @return false|mixed|null
	 */
	function kb_register_block_variation( $base, $id, $args = null ) {
		$kb_blocks = \KentaBlocks\Store::get( 'kenta-blocks', array() );
		if ( isset( $kb_blocks["kenta-blocks/{$base}"] ) ) {
			$kb_blocks["kenta-blocks/{$id}"] = array_replace_recursive(
				$kb_blocks["kenta-blocks/{$base}"],
				$args ?: require KENTA_BLOCKS_PLUGIN_PATH . "inc/blocks/{$id}.php"
			);

			\KentaBlocks\Store::keep( 'kenta-blocks', $kb_blocks );
		}

		return $kb_blocks;
	}
}

/**
 * All extensions
 */
require KENTA_BLOCKS_PLUGIN_PATH . 'inc/extensions/button.php';
require KENTA_BLOCKS_PLUGIN_PATH . 'inc/extensions/paragraph.php';
require KENTA_BLOCKS_PLUGIN_PATH . 'inc/extensions/advanced.php';
require KENTA_BLOCKS_PLUGIN_PATH . 'inc/extensions/linkable.php';
require KENTA_BLOCKS_PLUGIN_PATH . 'inc/extensions/box.php';
require KENTA_BLOCKS_PLUGIN_PATH . 'inc/extensions/container.php';
require KENTA_BLOCKS_PLUGIN_PATH . 'inc/extensions/overlay.php';
require KENTA_BLOCKS_PLUGIN_PATH . 'inc/extensions/shape.php';
require KENTA_BLOCKS_PLUGIN_PATH . 'inc/extensions/transform.php';
require KENTA_BLOCKS_PLUGIN_PATH . 'inc/extensions/posts-query.php';
require KENTA_BLOCKS_PLUGIN_PATH . 'inc/extensions/posts-card.php';
require KENTA_BLOCKS_PLUGIN_PATH . 'inc/extensions/posts-structure.php';
require KENTA_BLOCKS_PLUGIN_PATH . 'inc/extensions/position.php';
require KENTA_BLOCKS_PLUGIN_PATH . 'inc/extensions/particles.php';

/**
 * All hooks
 */
if ( KENTA_BLOCKS_WOOCOMMERCE_ACTIVE ) {
	require KENTA_BLOCKS_PLUGIN_PATH . 'inc/hooks/wc-products-query.php';
}

/**
 * Register blocks
 */
kb_register_block( 'templates' );
kb_register_block( 'section' );
kb_register_block( 'column' );
kb_register_block( 'button' );
kb_register_block( 'icon-button' );
kb_register_block( 'buttons' );
kb_register_block( 'icon' );
kb_register_block( 'cover' );
kb_register_block( 'spacer' );
kb_register_block( 'heading' );
kb_register_block( 'paragraph' );
kb_register_block( 'image' );
kb_register_block( 'separator' );
kb_register_block( 'group' );
kb_register_block( 'row' );
kb_register_block( 'stack' );
kb_register_block( 'slide-item' );
kb_register_block( 'slides' );
kb_register_block( 'query' );
kb_register_block( 'query-pagination' );
kb_register_block( 'posts-grid' );
kb_register_block( 'posts-magazine' );
kb_register_block( 'post-metas' );
kb_register_block( 'post-taxonomy' );

kb_register_block_variation( 'heading', 'post-title' );
kb_register_block_variation( 'paragraph', 'post-excerpt' );
kb_register_block_variation( 'image', 'post-featured-image' );

/**
 * WooCommerce blocks
 */
if ( KENTA_BLOCKS_WOOCOMMERCE_ACTIVE ) {
	kb_register_block( 'wc-product-price' );
	kb_register_block( 'wc-product-rating' );
	kb_register_block( 'wc-add-to-cart' );
}

return \KentaBlocks\Store::get( 'kenta-blocks', array() );
