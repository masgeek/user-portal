<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'kb_show_post_metas' ) ) {
	/**
	 * Show post metas
	 *
	 * @param $attrs
	 * @param string[] $items
	 * @param array $args
	 */
	function kb_show_post_metas( $attrs, $items = [ 'posted_on', 'views', 'comments' ], $args = [] ) {
		$default_args = [
			'before' => '',
			'after'  => '',
			'sep'    => '',
			'style'  => '',
		];

		extract( array_merge( $default_args, $args ) );
		$date_format = $attrs['publishedMetaFormat'];
		$divider     = $attrs['metaItemsDivider'] ?? 'divider-1';
		$icon        = $attrs['metaItemsStyle'] === 'icon';

		echo wp_kses_post( $before );

		foreach ( $items as $item ) {

			if ( $item === 'byline' ) {

				$byline = sprintf(
					'%s',
					'<a class="' . esc_attr( $style ) . '" href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . esc_html( get_the_author() ) . '</a>'
				);

				if ( ! empty( $byline ) ) {
					echo '<span class="byline meta-item"> ' . ( $icon ? '<i class="' . esc_attr( $attrs['bylineMetaIcon'] ) . '"></i>' : '' ) . wp_kses_post( $byline ) . '</span>';
				}
			} elseif ( $item === 'published' ) {

				$time_string = '<time class="published updated" datetime="%1$s">%2$s</time>';
				if ( get_the_time( 'U' ) !== get_the_modified_time( 'U' ) ) {
					$time_string = '<time class="published" datetime="%1$s">%2$s</time><time class="updated hidden" datetime="%3$s">%4$s</time>';
				}

				$time_string = sprintf( $time_string,
					esc_attr( get_the_date( 'c' ) ),
					esc_html( get_the_date( $date_format ) ),
					esc_attr( get_the_modified_date( 'c' ) ),
					esc_html( get_the_modified_date( $date_format ) )
				);

				$posted_on = sprintf(
					'%s',
					'<a class="' . esc_attr( $style ) . '" href="' . esc_url( get_permalink() ) . '" rel="bookmark"><span class="entry-date">' . $time_string . '</span></a>'
				);

				if ( ! empty( $posted_on ) ) {
					echo '<span class="meta-item posted-on">' . ( $icon ? '<i class="' . esc_attr( $attrs['publishedMetaIcon'] ) . '"></i>' : '' ) . wp_kses_post( $posted_on ) . '</span>';
				}
			} elseif ( $item === 'comments' ) {
				if ( ! comments_open( get_the_ID() ) || get_comments_number() <= 0 ) {
					continue;
				}

				echo '<span class="meta-item comments-link">';
				echo $icon ? '<i class="' . esc_attr( $attrs['commentsMetaIcon'] ) . '"></i>' : '';
				comments_popup_link( false, false, false, $style );
				echo '</span>';
			}

			if ( $divider !== 'none' ) {
				echo '<span class="meta-divider">';
				echo wp_kses_post( kb_image( $divider ) );
				echo '</span>';
			} else {
				echo '<span class="meta-empty-divider mr-2"></span>';
			}
		}

		echo wp_kses_post( $after );
	}
}
