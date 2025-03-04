<?php
/**
 * Posts Magazine block config
 *
 * @package Kenta Blocks
 */

$attributes = array_merge(
	array(
		'blockID'         => array(
			'type'    => 'string',
			'default' => '',
		),
		'layout'          => array(
			'type'    => 'string',
			'default' => 'style1',
		),
		'containerHeight' => array(
			'type'    => 'object',
			'default' => array(
				'desktop' => '520px',
				'tablet'  => '520px',
				'mobile'  => '520px',
			),
		),
		'hGutter'         => array(
			'type'    => 'object',
			'default' => array(
				'desktop' => '12px',
				'tablet'  => '12px',
				'mobile'  => '12px',
			),
		),
		'vGutter'         => array(
			'type'    => 'object',
			'default' => array(
				'desktop' => '12px',
				'tablet'  => '12px',
				'mobile'  => '12px',
			),
		),
		'hAlignment'      => array(
			'type'    => 'object',
			'default' => array(
				'desktop' => 'left',
				'tablet'  => 'left',
				'mobile'  => 'left',
			),
		),
		'vAlignment'      => array(
			'type'    => 'object',
			'default' => array(
				'desktop' => 'flex-end',
				'tablet'  => 'flex-end',
				'mobile'  => 'flex-end',
			),
		),
	),
	kb_posts_card_attrs( array(
		'cardStyle' => 'dynamic'
	) ),
	kenta_blocks_container_global_style( array(
		'overrideColors' => 'yes',
		'accentColor'    => array(
			'default' => 'rgba(255, 255, 255, 0.8)',
			'active'  => 'rgba(255, 255, 255, 0.8)',
		),
	) ),
	kenta_blocks_overlay_attrs( array(
		'overlay'    => 'yes',
		'background' => array(
			'type'     => 'gradient',
			'gradient' => 'linear-gradient(180deg, rgba(255, 255, 255, 0) 0%, #24262b 100%)',
		),
	) )
);

$metadata = array(
	'title'           => __( 'Posts Magazine (KB)', 'kenta-blocks' ),
	'description'     => __( "Use a magazine layout that contains the block elements used to render a post, like the title, date, featured image, content or excerpt, and more.", 'kenta-blocks' ),
	'keywords'        => array( 'posts', 'post', 'query', 'grid', 'loop', 'magazine' ),
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

		// Modify the perPage parameter of the query context
		// with the number of posts we need in the magazine layout
		$count                              = \KentaBlocks\Blocks\Magazine::all( $attrs['layout'] )['count'] ?? 4;
		$block->context['query']['perPage'] = absint( $count );

		// Generate WP_Query object
		$query_args = kb_get_posts_query_args( $page, $block->context['query'] );
		$query      = new \WP_Query( $query_args );

		if ( ! $query->have_posts() ) {
			return '';
		}

		$wrapper_attributes = get_block_wrapper_attributes( [
			'class' => esc_attr( kb_clsx( [
				'kb-posts-magazine',
				'not-prose',
				'kb-posts-magazine-' . $attrs['blockID']
			] ) )
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
				'kb-magazine-item'         => true,
				'kb-magazine-dynamic-item' => $attrs['cardStyle'] === 'dynamic',
			] ) ) );

			$inner_classes = kb_clsx( array(
				'kb-post-item-inner'             => true,
				'kb-post-item-inner-has-overlay' => $attrs['overlay'] === 'yes'
			) );

			$post_thumb = '';

			if ( $attrs['cardStyle'] === 'dynamic' ) {
				$post_thumb = '<div class="kb-post-item-thumb" ' . 'style="background-image: url(' . esc_url(
						get_the_post_thumbnail_url( get_the_ID() )
					) . ')"' . '></div>';
			}

			$content .=
				'<article class="' . esc_attr( $post_classes ) . '">'
				. '<div class="' . esc_attr( $inner_classes ) . '">'
				. $post_thumb
				. '<div class="kb-post-item-content">'
				. $block_content
				. '</div>'
				. '</div>'
				. '</article>';
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
		// Note the order here, reversing will be overwritten later.
		$scope = ".kb-posts-magazine-{$id}.kb-posts-magazine";

		// Global container style
		$css[ $scope ] = array_merge(
			array(
				'--kb-magazine-container-height' => kb_get_block_attr( $block, 'containerHeight' ),
				'--kb-magazine-v-gutter'         => kb_get_block_attr( $block, 'vGutter' ),
				'--kb-magazine-h-gutter'         => kb_get_block_attr( $block, 'hGutter' ),
			),
			kenta_blocks_container_global_css( $block )
		);

		// Grid layout
		$style = kb_get_block_attr( $block, 'layout' );
		$css   = call_user_func( array(
			\KentaBlocks\Blocks\Magazine::class,
			$style
		), "kb-posts-magazine-{$id}", $css );
		// Alignment
		$css["{$scope} .kb-magazine-item .kb-post-item-content"] = array(
			'text-align'      => kb_get_block_attr( $block, 'hAlignment' ),
			'justify-content' => kb_get_block_attr( $block, 'vAlignment' ),
		);
		// Card style
		$css["{$scope} .kb-magazine-item"] = kb_card_preset_style( $block );
		// Overlay style
		$css["{$scope} .kb-magazine-item .kb-post-item-inner-has-overlay::after"] = kenta_blocks_overlay_css( $block );

		return $css;
	}
);
