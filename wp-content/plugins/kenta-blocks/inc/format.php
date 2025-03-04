<?php
/**
 * Sanitize and escape functions for our plugin
 *
 * @since v1.4.0
 *
 * @package Kenta Blocks
 */

if ( ! function_exists( 'kb_esc_heading_markup' ) ) {
	/**
	 * Escaping heading markup
	 *
	 * @param $tag
	 * @param $default
	 *
	 * @return mixed|string
	 */
	function kb_esc_heading_markup( $tag, $default = 'h1' ) {
		$tag = strtolower( $tag );

		if ( in_array( $tag, array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'span' ) ) ) {
			return $tag;
		}

		return $default;
	}
}

if ( ! function_exists( 'kb_esc_block_id' ) ) {
	/**
	 * Escaping block id for script/css
	 *
	 * @param $block_id
	 *
	 * @return mixed
	 */
	function kb_esc_block_id( $block_id ) {
		if ( ! preg_match( "/^[a-z0-9]+$/", $block_id ) ) {
			return '';
		}

		return $block_id;
	}
}

if ( ! function_exists( 'kb_sanitize_rgba_color' ) ) {
	/**
	 * RGBA color sanitization callback example.
	 *
	 * @param $color
	 *
	 * @return string|void
	 */
	function kb_sanitize_rgba_color( $color ) {
		// css var
		if ( false !== strpos( $color, 'var' ) ) {
			return $color;
		}

		if ( empty( $color ) || is_array( $color ) ) {
			return 'rgba(0,0,0,0)';
		}

		// If string does not start with 'rgba', then treat as hex
		// sanitize the hex color and finally convert hex to rgba
		if ( false === strpos( $color, 'rgba' ) ) {
			return sanitize_hex_color( $color );
		}

		// By now we know the string is formatted as an rgba color so we need to further sanitize it.
		$color = str_replace( ' ', '', $color );
		sscanf( $color, 'rgba(%d,%d,%d,%f)', $red, $green, $blue, $alpha );

		return 'rgba(' . $red . ',' . $green . ',' . $blue . ',' . $alpha . ')';
	}
}

if ( ! function_exists( 'kb_sanitize_rgba_color_collect' ) ) {
	/**
	 * A collect of RGBA color sanitization callback example.
	 *
	 * @param $colors
	 *
	 * @return mixed
	 */
	function kb_sanitize_rgba_color_collect( $colors ) {
		if ( ! is_array( $colors ) ) {
			return [];
		}

		foreach ( $colors as $key => $value ) {
			$colors[ $key ] = kb_sanitize_rgba_color( $value );
		}

		return $colors;
	}
}

if ( ! function_exists( 'kb_sanitize_checkbox' ) ) {
	/**
	 * Checkbox sanitization callback example.
	 *
	 * Sanitization callback for 'checkbox' type controls. This callback sanitizes `$checked`
	 * as a boolean value, either TRUE or FALSE.
	 *
	 * @param mixed $checked Whether the checkbox is checked.
	 *
	 * @return string Whether the checkbox is checked.
	 */
	function kb_sanitize_checkbox( $checked ) {
		// Boolean check.
		return $checked === 'yes' ? $checked : 'no';
	}
}
