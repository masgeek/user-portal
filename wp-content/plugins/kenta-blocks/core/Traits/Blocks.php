<?php
/**
 * Blocks hooks
 *
 * @package Kenta Blocks
 */

namespace KentaBlocks\Traits;

trait Blocks {
	/**
	 * Register custom block category.
	 *
	 * @param array $categories All categories.
	 *
	 * @link https://wordpress.org/gutenberg/handbook/extensibility/extending-blocks/#managing-block-categories
	 */
	public function blocks_categories( $categories ) {
		return array_merge(
			array(
				array(
					'slug'  => 'kenta-blocks',
					'title' => __( 'Kenta Blocks', 'kenta-blocks' ),
				),
				array(
					'slug'  => 'kenta-wc-blocks',
					'title' => __( 'Kenta WooCommerce Blocks', 'kenta-blocks' ),
				),
			),
			$categories
		);
	}

	/**
	 * Registers all blocks using the metadata loaded from the `block.json` file.
	 */
	public function blocks_init() {

		$blocks = kenta_blocks_all( 'metadata' );

		$global_args = array(
			'api_version'   => 2,
			'textdomain'    => 'kenta-blocks',
			'category'      => 'kenta-blocks',
			'style'         => 'kenta-blocks-style',
			'editor_script' => 'kenta-blocks-editor-script',
			'editor_style'  => 'kenta-blocks-editor-style',
		);

		foreach ( $blocks as $id => $args ) {
			register_block_type( $id, array_merge( $global_args, $args ) );
		}
	}
}
