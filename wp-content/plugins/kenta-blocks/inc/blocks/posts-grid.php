<?php
/**
 * Posts List block config
 *
 * @package Kenta Blocks
 */

$attributes = array_merge(
	array(
		'blockID'      => array(
			'type'    => 'string',
			'default' => '',
		),
		/**
		 * Card layout
		 */
		'gridColumns'  => array(
			'type'    => 'object',
			'default' => array(
				'desktop' => 3,
				'tablet'  => 2,
				'mobile'  => 1,
			)
		),
		'gridItemsGap' => array(
			'type'    => 'object',
			'default' => array(
				'desktop' => '24px',
				'tablet'  => '24px',
				'mobile'  => '24px',
			),
		),
	),
	kb_posts_card_attrs(),
	kenta_blocks_container_global_style( array(
		'overrideColors' => 'no',
		'accentColor'    => array(
			'default' => 'rgba(255, 255, 255, 0.8)',
			'active'  => 'rgba(255, 255, 255, 0.8)',
		),
	) ),
	kenta_blocks_overlay_attrs( array(
		'overlay'    => 'no',
		'background' => array(
			'type'     => 'gradient',
			'gradient' => 'linear-gradient(180deg, rgba(255, 255, 255, 0) 0%, #24262b 100%)',
		),
	) )
);

$metadata = array(
	'title'           => __( 'Posts Grid (KB)', 'kenta-blocks' ),
	'description'     => __( "Use a grid layout that contains the block elements used to render a post, like the title, date, featured image, content or excerpt, and more.", 'kenta-blocks' ),
	'keywords'        => array( 'posts', 'post', 'query', 'grid', 'loop' ),
	'parent'          => array( 'kenta-blocks/query' ),
	'supports'        => array(
		'anchor'   => true,
		'reusable' => false,
		'html'     => false,
		'align'    => true,
	),
	'attributes'      => $attributes,
	'uses_context'    => array(
		'queryId',
		'query'
	),
	'render_callback' => function ( $attrs, $content, $block ) {
		$page_key = isset( $block->context['queryId'] ) ? 'kb-query-' . $block->context['queryId'] . '-page' : 'kb-query-page';
		$page     = empty( $_GET[ $page_key ] ) ? 1 : (int) $_GET[ $page_key ];

		$query_args = kb_get_posts_query_args( $page, $block->context['query'] );
		$query      = new \WP_Query( $query_args );

		if ( ! $query->have_posts() ) {
			return '';
		}

		$wrapper_attributes = get_block_wrapper_attributes( [
			'class' => esc_attr( kb_clsx( [ 'kb-posts-grid', 'not-prose', 'kb-posts-grid-' . $attrs['blockID'] ] ) )
		] );

		$content = '';
		while ( $query->have_posts() ) {
			$query->the_post();

			// Get an instance of the current Post Template block.
			$block_instance = $block->parsed_block;

			// Set the block name to one that does not correspond to an existing registered block.
			// This ensures that for the inner instances of the Post Template block, we do not render any block supports.
			$block_instance['blockName'] = 'core/null';

			// Render the inner blocks of the Post Template block with `dynamic` set to `false` to prevent calling
			// `render_callback` and ensure that no wrapper markup is included.
			$block_content = (
			new \WP_Block(
				$block_instance,
				array(
					'postType' => get_post_type(),
					'postId'   => get_the_ID(),
				)
			)
			)->render( array( 'dynamic' => false ) );

			// Wrap the render inner blocks in a `li` element with the appropriate post classes.
			$post_classes = implode( ' ', get_post_class( kb_clsx( [
				'kb-card'             => true,
				'kb-card-has-overlay' => $attrs['overlay'] === 'yes'
			] ) ) );

			$post_thumb = '';

			if ( $attrs['cardStyle'] === 'dynamic' ) {
				$post_thumb = '<div class="kb-card-dynamic-thumb" ' . 'style="background-image: url(' . esc_url(
						get_the_post_thumbnail_url( get_the_ID() )
					) . ')"' . '></div>';
			}

			$content .=
				'<div class="kb-card-wrapper">'
				. '<article data-card-layout="kb-archive-grid" class="' . esc_attr( $post_classes ) . '">'
				. $post_thumb
				. '<div class="kb-card-item-content">'
				. $block_content
				. '</div>'
				. '</article>'
				. '</div>';
		}

		/*
		 * Use this function to restore the context of the template tags
		 * from a secondary query loop back to the main query loop.
		 * Since we use two custom loops, it's safest to always restore.
		*/
		wp_reset_postdata();

		return sprintf(
			'<div %1$s>%2$s</div>',
			$wrapper_attributes,
			wp_kses_post( $content )
		);
	}
);

return array(
	'metadata' => $metadata,
	'css'      => function ( $block, $css ) {
		$id = kb_get_block_attr( $block, 'blockID' );

		$scope = ".kb-posts-grid.kb-posts-grid-{$id}";

		$css[ $scope ] = array_merge(
			array(
				'--card-gap' => kb_get_block_attr( $block, 'gridItemsGap' ),
			),
			kenta_blocks_container_global_css( $block )
		);

		$card_width = [];

		foreach ( kb_get_block_attr( $block, 'gridColumns' ) as $device => $columns ) {
			$card_width[ $device ] = sprintf( "%.2f", substr( sprintf( "%.3f", ( 100 / max( 1, (int) $columns ) ) ), 0, - 1 ) ) . '%';
		}

		$css["{$scope} .kb-card-wrapper"] = [
			'width' => $card_width
		];

		// Card style
		$css["{$scope} .kb-card"] = kb_card_preset_style( $block );
		// Overlay style
		$css["{$scope} .kb-card.kb-card-has-overlay::after"] = kenta_blocks_overlay_css( $block );

		return $css;
	}
);
