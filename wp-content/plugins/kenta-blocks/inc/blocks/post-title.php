<?php
/**
 * Post title block config, a variant of heading block
 *
 * @package Kenta Blocks
 */

$attributes = array_merge(
	array(
		'isLink'     => array(
			'type'    => 'string',
			'default' => 'yes',
		),
		'color'      => array(
			'type'    => 'object',
			'default' => array(
				'initial'      => 'var(--kb-accent-color)',
				'link-initial' => 'var(--kb-accent-color)',
				'link-hover'   => 'var(--kb-primary-color)',
			),
		),
		'typography' => array(
			'type'    => 'object',
			'default' => array(
				'family'     => 'inherit',
				'fontSize'   => array( 'desktop' => '1.25rem', 'tablet' => '1rem', 'mobile' => '1rem' ),
				'variant'    => '700',
				'lineHeight' => '1.5'
			)
		),
	),
	kenta_blocks_advanced_attrs( array(
		'margin' => array(
			'linked' => true,
			'top'    => '0px',
			'right'  => 'auto',
			'bottom' => '12px',
			'left'   => 'auto',
		),
	) )
);

$metadata = array(
	'title'           => __( 'Post Title (KB)', 'kenta-blocks' ),
	'description'     => __( "Displays the title of a post, page, or any other content-type.", 'kenta-blocks' ),
	'keywords'        => array( 'post', 'title', 'heading' ),
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

		$post_ID = $block->context['postId'];
		$title   = get_the_title();

		if ( ! $title ) {
			return '';
		}

		$tag_name = kb_esc_heading_markup( $attrs['markup'] );

		if ( isset( $attrs['isLink'] ) && $attrs['isLink'] === 'yes' ) {
			$title = sprintf(
				'<a href="%1$s" target="%2$s">%3$s</a>',
				esc_url( get_the_permalink( $post_ID ) ),
				'_self',
				esc_html( $title )
			);
		}

		$wrapper_attributes = get_block_wrapper_attributes( array(
			'class' => esc_attr( kb_clsx( [
				'kb-post-title'                      => true,
				'kb-post-title-' . $tag_name         => true,
				'kb-post-title-' . $attrs['blockID'] => true,
				'kb-background-clip'                 => $attrs['backgroundClip'] === 'yes' && $attrs['displayAsBlock'] === 'yes',
			] ) )
		) );

		if ( $attrs['displayAsBlock'] === 'yes' ) {
			return sprintf(
				'<%1$s %2$s>%3$s</%1$s>',
				$tag_name,
				$wrapper_attributes,
				$title
			);
		}

		if ( $attrs['backgroundClip'] === 'yes' ) {
			return sprintf( '<div %1$s><%2$s class="kb-background-clip">%3$s</%2$s></div>', $wrapper_attributes, $tag_name, $title );
		}

		return sprintf( '<div %1$s><%2$s>%3$s</%2$s></div>', $wrapper_attributes, $tag_name, $title );
	}
);

return array(
	'metadata' => $metadata,
	'css'      => function ( $block, $css ) {
		$id     = kb_get_block_attr( $block, 'blockID' );
		$markup = kb_get_block_attr( $block, 'markup' );

		$selectors = array(
			"{$markup}.kb-post-title-{$id}",
			".kb-post-title.kb-post-title-{$id} {$markup}"
		);

		$css[ implode( ' a,', $selectors ) . ' a' ] =
			kenta_blocks_css()->typography( kb_get_block_attr( $block, 'typography' ) );

		return kenta_blocks_paragraph_css( $markup, 'kb-post-title', $block, $css );
	}
);
