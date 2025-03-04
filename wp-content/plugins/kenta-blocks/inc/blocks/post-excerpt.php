<?php
/**
 * Post excerpt block config, a variant of paragraph block
 *
 * @package Kenta Blocks
 */

$attributes = array(
	'moreText' => array(
		'type'    => 'string',
		'default' => '...'
	),
	'moreLink' => array(
		'type'    => 'string',
		'default' => 'no',
	),
	'length'   => array(
		'type'    => 'number',
		'default' => 18,
	),
	'color'    => array(
		'type'    => 'object',
		'default' => array(
			'initial'      => 'var(--kb-accent-active)',
			'link-initial' => 'var(--kb-accent-active)',
			'link-hover'   => 'var(--kb-primary-color)',
		),
	),
);

$metadata = array(
	'title'           => __( 'Post Excerpt (KB)', 'kenta-blocks' ),
	'description'     => __( "Display a post's excerpt.", 'kenta-blocks' ),
	'keywords'        => array( 'post', 'excerpt' ),
	'attributes'      => $attributes,
	'uses_context'    => array(
		'postType',
		'postId',
		'queryId',
	),
	'render_callback' => function ( $attrs, $content, $block ) {
		if ( ! isset( $block->context['postId'] ) ) {
			return '';
		}

		$excerpt_length = absint( $attrs['length'] );
		$more_text      = ! empty( $attrs['moreText'] ) ? wp_kses_post( $attrs['moreText'] ) : '';
		if ( ! empty( $more_text ) && $attrs['moreLink'] === 'yes' ) {
			$more_text = '<a class="kb-post-excerpt-more-link" href="' . esc_url( get_the_permalink( $block->context['postId'] ) ) . '">' . $more_text . '</a>';
		}

		$filter_excerpt_more = function ( $more ) use ( $more_text ) {
			if ( $more_text === null || $more_text === '' ) {
				return $more;
			}

			return $more_text;
		};

		$filter_excerpt_length = function ( $length ) use ( $excerpt_length ) {
			if ( $excerpt_length === null || absint( $excerpt_length ) <= 0 ) {
				return $length;
			}

			return $excerpt_length;
		};

		add_filter( 'excerpt_more', $filter_excerpt_more );
		add_filter( 'excerpt_length', $filter_excerpt_length );

		$excerpt = get_the_excerpt();

		remove_filter( 'excerpt_more', $filter_excerpt_more );
		remove_filter( 'excerpt_length', $filter_excerpt_length );

		if ( ! $excerpt ) {
			return '';
		}

		$wrapper_attributes = get_block_wrapper_attributes( array(
			'class' => esc_attr( kb_clsx( [
				'kb-post-excerpt'                      => true,
				'kb-post-excerpt-' . $attrs['blockID'] => true,
				'kb-background-clip'                   => $attrs['backgroundClip'] === 'yes' && $attrs['displayAsBlock'] === 'yes',
			] ) )
		) );

		if ( $attrs['displayAsBlock'] === 'yes' ) {
			return sprintf( '<p %1$s>%2$s</p>', $wrapper_attributes, $excerpt );
		}

		if ( $attrs['backgroundClip'] === 'yes' ) {
			return sprintf( '<div %1$s><p class="kb-background-clip">%2$s</p></div>', $wrapper_attributes, $excerpt );
		}

		return sprintf( '<div %1$s><p>%2$s</p></div>', $wrapper_attributes, $excerpt );
	}
);

return array(
	'metadata' => $metadata,
	'css'      => function ( $block, $css ) {
		return kenta_blocks_paragraph_css( 'p', 'kb-post-excerpt', $block, $css );
	}
);
