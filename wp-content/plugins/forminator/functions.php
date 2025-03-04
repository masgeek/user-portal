<?php
/**
 * The plugin functions.
 *
 * @package    Forminator
 * @subpackage Functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( ! function_exists( 'forminator' ) ) {
	/**
	 * Forminator instance.
	 */
	function forminator() {
		return Forminator::get_instance();
	}

	/**
	 * Init the plugin and load the plugin instance
	 *
	 * @since 1.0
	 * Priority is set to 4 to support Gutenberg blocks.
	 * Priority is set to 0 to support Forminator widget.
	 */
	add_action( 'init', 'forminator', 0 );
}

if ( ! function_exists( 'forminator_plugin_url' ) ) {
	/**
	 * Return plugin URL
	 *
	 * @since 1.0
	 * @return string
	 */
	function forminator_plugin_url() {
		return trailingslashit( plugin_dir_url( __FILE__ ) );
	}
}

if ( ! function_exists( 'forminator_plugin_dir' ) ) {
	/**
	 * Return plugin path
	 *
	 * @since 1.0
	 * @return string
	 */
	function forminator_plugin_dir() {
		return trailingslashit( plugin_dir_path( __FILE__ ) );
	}
}

if ( ! function_exists( 'forminator_addons_dir' ) ) {
	/**
	 * Return plugin path
	 *
	 * @since 1.0.5
	 * @return string
	 */
	function forminator_addons_dir() {
		return trailingslashit( forminator_plugin_dir() . 'addons' );
	}
}

/**
 * Check if payments functionality are disabled
 *
 * @return bool
 */
function forminator_payments_disabled(): bool {
	return apply_filters( 'forminator_payments_disabled', false );
}
