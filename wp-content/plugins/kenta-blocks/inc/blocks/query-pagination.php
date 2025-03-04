<?php
/**
 * Query pagination block config
 *
 * @package Kenta Blocks
 */

$attributes = array_merge(
	array(
		'blockID'            => array(
			'type'    => 'string',
			'default' => '',
		),
		'layout'             => array(
			'type'    => 'string',
			'default' => 'numbered',
		),
		'alignment'          => array(
			'type'    => 'object',
			'default' => array(
				'desktop' => 'center',
				'tablet'  => \KentaBlocks\Css::INITIAL_VALUE,
				'mobile'  => \KentaBlocks\Css::INITIAL_VALUE,
			),
		),
		'showPrevNextButton' => array(
			'type'    => 'string',
			'default' => 'yes',
		),
		'prevNextStyle'      => array(
			'type'    => 'string',
			'default' => 'icon',
		),
		'prevText'           => array(
			'type'    => 'string',
			'default' => __( 'Prev', 'kenta-blocks' ),
		),
		'nextText'           => array(
			'type'    => 'string',
			'default' => __( 'Next', 'kenta-blocks' ),
		),
		'prevIcon'           => array(
			'type'    => 'string',
			'default' => 'fas fa-arrow-left-long',
		),
		'nextIcon'           => array(
			'type'    => 'string',
			'default' => 'fas fa-arrow-right-long',
		),
		'showDisabledButton' => array(
			'type'    => 'string',
			'default' => 'no'
		),
		// Style
		'typography'         => array(
			'type'    => 'object',
			'default' => array(
				'family'     => 'inherit',
				'fontSize'   => '0.875rem',
				'variant'    => '400',
				'lineHeight' => '1',
			),
		),
		'itemColor'          => array(
			'type'    => 'object',
			'default' => array(
				'initial' => 'var(--kb-accent-color)',
				'active'  => 'var(--kb-base-color)',
				'accent'  => 'var(--kb-primary-color)',
			),
		),
		'itemBorder'         => array(
			'type'    => 'object',
			'default' => array(
				'style'   => 'solid',
				'width'   => 2,
				'color'   => 'var(--kb-base-300)',
				'hover'   => '',
				'inherit' => false,
			),
		),
		'itemRadius'         => array(
			'type'    => 'object',
			'default' => array(
				'desktop' => '2px',
				'tablet'  => \KentaBlocks\Css::INITIAL_VALUE,
				'mobile'  => \KentaBlocks\Css::INITIAL_VALUE,
			),
		)
	),
	kenta_blocks_advanced_attrs( array(
		'margin' => array(
			'linked' => true,
			'top'    => '24px',
			'right'  => 'auto',
			'bottom' => '24px',
			'left'   => 'auto',
		)
	) )
);

$metadata = array(
	'title'             => __( 'Pagination (KB)', 'kenta-blocks' ),
	'description'       => __( 'Displays a paginated navigation to next/previous set of posts, when applicable.', 'kenta-blocks' ),
	'keywords'          => array( 'query', 'post', 'pagination' ),
	'parent'            => array( 'kenta-blocks/query' ),
	'supports'          => array(
		'anchor'   => true,
		'reusable' => false,
		'html'     => false,
		'align'    => true,
	),
	'attributes'        => $attributes,
	'uses_context'      => array(
		'queryId',
		'query'
	),
	'skip_inner_blocks' => true,
	'render_callback'   => function ( $attrs, $content, $block ) {
		$page_key        = isset( $block->context['queryId'] ) ? 'kb-query-' . $block->context['queryId'] . '-page' : 'kb-query-page';
		$page            = empty( $_GET[ $page_key ] ) ? 1 : (int) $_GET[ $page_key ];
		$max_page        = isset( $block->context['query']['pages'] ) ? (int) $block->context['query']['pages'] : 0;
		$show_disabled   = isset( $attrs['showDisabledButton'] ) && $attrs['showDisabledButton'] === 'yes';
		$pagination_type = $attrs['layout'] ?? 'numbered';

		global $wp_query;
		$query = new \WP_Query( kb_get_posts_query_args( $page, $block->context['query'] ) );

		//
		// Generate previous and next link
		//
		if ( $pagination_type === 'prev-next'
		     || ( $pagination_type === 'numbered' && $attrs['showPrevNextButton'] === 'yes' )
		) {
			$pn_style = $attrs['prevNextStyle'] ?? 'icon';
			$prev     = '';
			$next     = '';

			// previous button
			$prev_content = $pn_style === 'icon' ? '<i class="' . esc_attr( $attrs['prevIcon'] ) . '"></i>' : esc_html( $attrs['prevText'] );
			if ( 1 !== $page ) {
				$prev = sprintf(
					'<a href="%1$s" class="%2$s">%3$s</a>',
					esc_url( add_query_arg( $page_key, $page - 1 ) ),
					esc_attr( 'kb-pagination-item kb-pagination-prev-item-' . $pn_style ),
					$prev_content
				);
			} else if ( $show_disabled ) {
				$prev = sprintf(
					'<span class="%1$s">%2$s</span>',
					esc_attr( 'kb-pagination-item-disabled kb-pagination-item kb-pagination-prev-item-' . $pn_style ),
					$prev_content
				);
			}

			// next button
			if ( ( ! $max_page || $max_page > $page ) || $show_disabled ) {
				$query_max_pages = (int) $query->max_num_pages;
				$next_content    = $pn_style === 'icon' ? '<i class="' . esc_attr( $attrs['nextIcon'] ) . '"></i>' : esc_html( $attrs['nextText'] );

				if ( $query_max_pages && $query_max_pages !== $page ) {
					$next = sprintf(
						'<a href="%1$s" class="%2$s">%3$s</a>',
						esc_url( add_query_arg( $page_key, $page + 1 ) ),
						esc_attr( 'kb-pagination-item kb-pagination-prev-item-' . $pn_style ),
						$next_content
					);
				} else if ( $show_disabled ) {
					$next = sprintf(
						'<span class="%1$s">%2$s</span>',
						esc_attr( 'kb-pagination-item-disabled kb-pagination-item kb-pagination-prev-item-' . $pn_style ),
						$next_content
					);
				}
			}
		}

		//
		// Generate post pagination numbers link
		// `paginate_links` works with the global $wp_query, so we have to
		// temporarily switch it with our custom query.
		//
		$prev_wp_query = $wp_query;
		$wp_query      = $query;
		$total         = ! $max_page || $max_page > $wp_query->max_num_pages ? $wp_query->max_num_pages : $max_page;
		$paginate_args = array(
			'base'      => '%_%',
			'format'    => "?$page_key=%#%",
			'current'   => max( 1, $page ),
			'total'     => $total,
			'prev_next' => false,
		);
		if ( 1 !== $page ) {
			/**
			 * `paginate_links` doesn't use the provided `format` when the page is `1`.
			 * This is great for the main query as it removes the extra query params
			 * making the URL shorter, but in the case of multiple custom queries is
			 * problematic. It results in returning an empty link which ends up with
			 * a link to the current page.
			 *
			 * A way to address this is to add a `fake` query arg with no value that
			 * is the same for all custom queries. This way the link is not empty and
			 * preserves all the other existent query args.
			 *
			 * @see https://developer.wordpress.org/reference/functions/paginate_links/
			 *
			 * The proper fix of this should be in core. Track Ticket:
			 * @see https://core.trac.wordpress.org/ticket/53868
			 *
			 * TODO: After two WP versions (starting from the WP version the core patch landed),
			 * we should remove this and call `paginate_links` with the proper new arg.
			 */
			$paginate_args['add_args'] = array( 'cst' => '' );
		}
		// We still need to preserve `paged` query param if exists, as is used
		// for Queries that inherit from global context.
		$paged = empty( $_GET['paged'] ) ? null : (int) $_GET['paged'];
		if ( $paged ) {
			$paginate_args['add_args'] = array( 'paged' => $paged );
		}
		$numbers = paginate_links( $paginate_args );

		wp_reset_postdata(); // Restore original Post Data.
		$wp_query = $prev_wp_query; // Restore original global query

		$wrapper_attributes = get_block_wrapper_attributes( [
			'class' => esc_attr( kb_clsx( [
				'kb-query-pagination',
				"kb-query-pagination-{$attrs['blockID']}",
			] ) )
		] );

		return sprintf(
			'<div %1$s>%2$s</div>',
			$wrapper_attributes,
			wp_kses_post( $prev . $numbers . $next )
		);
	}
);

return array(
	'metadata' => $metadata,
	'css'      => function ( $block, $css ) {
		$id = kb_get_block_attr( $block, 'blockID' );

		$pagination_type = kb_get_block_attr( $block, 'layout' );
		$pagination_css  = [];

		if ( $pagination_type === 'numbered' || $pagination_type === 'prev-next' ) {
			$pagination_css = array_merge(
				kenta_blocks_css()->border( kb_get_block_attr( $block, 'itemBorder' ), '--kb-pagination-item-border' ),
				kenta_blocks_css()->colors( kb_get_block_attr( $block, 'itemColor' ), [
					'initial' => '--kb-pagination-initial-color',
					'active'  => '--kb-pagination-active-color',
					'accent'  => '--kb-pagination-accent-color',
				] ),
				[ '--kb-pagination-item-radius' => kb_get_block_attr( $block, 'itemRadius' ) ]
			);
		}

		$css[".kb-query-pagination, .kb-query-pagination-{$id}"] = array_merge(
			$pagination_css,
			kenta_blocks_css()->typography( kb_get_block_attr( $block, 'typography' ) ),
			[ 'justify-content' => kb_get_block_attr( $block, 'alignment' ) ],
			kenta_blocks_advanced_css( $block )
		);

		return $css;
	}
);
