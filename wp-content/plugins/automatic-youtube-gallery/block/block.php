<?php

/**
 * Automatic YouTube Gallery Gutenberg Block.
 *
 * @link    https://plugins360.com
 * @since   1.0.0
 *
 * @package Automatic_YouTube_Gallery
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AYG_Block class.
 *
 * @since 1.0.0
 */
class AYG_Block {

	/**
	 * Register our custom block category.
	 *
	 * @since  1.0.0
	 * @param  array $categories Default block categories.
	 * @return array             Modified block categories.
	 */
	public function block_categories( $categories ) {		
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'automatic-youtube-gallery',
					'title' => __( 'Automatic YouTube Gallery', 'automatic-youtube-gallery' ),
				),
			)
		);		
	}

	/**
	 * Enqueue block assets for backend editor.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_block_editor_assets() {
		$fields = ayg_get_editor_fields();
			
		foreach ( $fields as $key => $section ) {
			foreach ( $section['fields'] as $_key => $field ) {
				if ( isset( $field['description'] ) ) {
					$fields[ $key ]['fields'][ $_key ]['description'] = strip_tags( $field['description'] );
				}
			}
		}

		wp_localize_script( 
			'wp-block-editor', 
			'ayg_block', 
			array(
				'options' => $fields,
				'i18n'    => array(
					'selected_color' => __( 'Selected Color', 'automatic-youtube-gallery' ),
					'is_loading'     => __( 'Loading...', 'automatic-youtube-gallery' )
				)
			)
		);	
	}

	/**
	 * Register our custom block.
	 * 
	 * @since 1.0.0
	 */
	public function register_block_type() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return false;
		}
	
		$attributes = array(
			'is_admin' => array(
				'type' => 'boolean'
			),
			'uid' => array(
				'type' => 'string'
			)
		);

		$fields = ayg_get_editor_fields();

		foreach ( $fields as $key => $section ) {
			foreach ( $section['fields'] as $field ) {
				$type = 'string';

				if ( 'number' == $field['type'] ) {
					$type = 'number';
				} elseif ( 'checkbox' == $field['type'] ) {
					$type = 'boolean';
				}

				$attributes[ $field['name'] ] = array(
					'type' => $type
				);
			}
		}

		register_block_type( __DIR__ . '/build', array(
			'attributes'      => $attributes,
			'render_callback' => array( $this, 'render_block' ),
		) );
	}

	/**
	 * Render the block frontend.
	 *
	 * @since  1.0.0
	 * @param  array  $atts An associative array of attributes.
	 * @return string       HTML output.
	 */
	public function render_block( $atts ) {
		if ( ! empty( $atts['is_admin'] ) ) {			
			$atts['autoplay'] = false;
		}

		if ( ! empty( $atts['uid'] ) ) {			
			$atts['uid'] = md5( $atts['uid'] );
		}

		// Output
		$output  = '<div ' . get_block_wrapper_attributes() . '>';
		$output .= ayg_build_gallery( $this->clean_attributes( $atts ) );
		$output .= '</div>';

		return $output;
	}

	/**
	 * Clean attributes array.
	 * 
	 * @since  1.0.0
	 * @access private
	 * @param  array   $atts Array of attributes.
	 * @return array         Cleaned attributes array.
	 */
	private function clean_attributes( $atts ) {
		$attributes = array();
		
		foreach ( $atts as $key => $value ) {
			if ( is_null( $value ) ) {
				continue;
			}

			if ( is_bool( $value ) ) {
				$value = ( true === $value ) ? 1 : 0;
			}

			$attributes[ $key ] = $value;
		}
		
		return $attributes;
	}

}
