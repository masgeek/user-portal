<?php
/**
 * Must be required before plugin run
 *
 * @package Kenta Companion
 */

if ( ! function_exists( 'kcmp_kenta_need_upgrade_notice' ) ) {
	function kcmp_kenta_need_upgrade_notice() {
		if ( ! current_user_can( 'install_themes' ) ) {
			return;
		}

		$path = KCMP_PLUGIN_PATH . 'templates/kenta-upgrade-notice.php';
		if ( file_exists( $path ) ) {
			require $path;
		}
	}
}

if ( ! function_exists( 'kcmp_enqueue_admin_scripts' ) ) {
	function kcmp_enqueue_admin_scripts() {
		$suffix = defined( 'WP_DEBUG' ) && WP_DEBUG ? '' : '.min';

		wp_enqueue_style(
			'kenta-cmp-admin-style',
			KCMP_ASSETS_URL . 'css/kenta-admin' . $suffix . '.css',
			[],
			KCMP_VERSION
		);

		$screen = get_current_screen();

		if ( ! in_array( $screen->base, [ 'toplevel_page_kenta-companion', 'kenta_page_kenta-starter-sites' ] ) ) {
			return;
		}

		wp_enqueue_script(
			'kenta-cmp-admin-script',
			KCMP_ASSETS_URL . 'js/kenta-admin' . $suffix . '.js',
			[
				'jquery',
				'wp-api-fetch',
				'wp-element',
				'wp-components'
			],
			KCMP_VERSION
		);

		$localize = [
			'general' => [
				'premium_kb' => function_exists( 'kcmp_is_premium_kb_installed' ) && kcmp_is_premium_kb_installed(),
			],
			'starter' => [
				'plan' => kenta_fs()->can_use_premium_code() ? 'premium' : 'free',
				'api'  => kcmp( 'demos' )->api( kcmp_current_template() . '/' ),
			],
		];

		wp_localize_script(
			'kenta-cmp-admin-script',
			'KentaCompanion',
			apply_filters( 'kcmp/admin_js_localize', $localize )
		);
	}
}

if ( ! function_exists( 'kcmp_bust_cache' ) ) {
	/**
	 * Perform actions when plugin activated
	 *
	 * @return void
	 */
	function kcmp_bust_cache() {
		update_option( 'kcmp_dynamic_css_cached_version', '' );
		update_option( 'kcmp_customizer_default_settings_version', '' );
	}
}

if ( ! function_exists( 'kcmp_get_stylesheet_tag' ) ) {
	/**
	 * Get tag with stylesheet prefix
	 *
	 * @param $tag
	 *
	 * @return string
	 * @since v1.2.2
	 */
	function kcmp_get_stylesheet_tag( $tag ) {
		return get_stylesheet() . '_' . $tag;
	}
}

if ( ! function_exists( 'kcmp_get_option' ) ) {
	/**
	 * @param $option
	 * @param $default_value
	 *
	 * @return false|mixed|null
	 * @since v1.2.2
	 */
	function kcmp_get_option( $option, $default_value = false ) {
		return get_option( kcmp_get_stylesheet_tag( $option ), $default_value );
	}
}

if ( ! function_exists( 'kcmp_update_option' ) ) {
	/**
	 * @param $option
	 * @param $value
	 * @param null $autoload
	 *
	 * @return false|mixed|null
	 * @since v1.2.2
	 */
	function kcmp_update_option( $option, $value, $autoload = null ) {
		return update_option( kcmp_get_stylesheet_tag( $option ), $value, $autoload = null );
	}
}

if ( ! function_exists( 'kcmp_do_action' ) ) {
	/**
	 * @param $hook_name
	 * @param ...$arg
	 *
	 * @return void
	 * @since v1.2.2
	 */
	function kcmp_do_action( $hook_name, ...$arg ) {
		do_action( kcmp_get_stylesheet_tag( $hook_name ), ...$arg );
	}
}

if ( ! function_exists( 'kcmp_add_action' ) ) {
	/**
	 * @param $hook_name
	 * @param $callback
	 * @param $priority
	 * @param $accepted_args
	 *
	 * @return true|null
	 * @since v1.2.2
	 */
	function kcmp_add_action( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
		return add_action( kcmp_get_stylesheet_tag( $hook_name ), $callback, $priority, $accepted_args );
	}
}

if ( ! function_exists( 'kcmp_apply_filters' ) ) {
	/**
	 * @param $hook_name
	 * @param $value
	 * @param ...$args
	 *
	 * @return mixed|null
	 * @since v1.2.2
	 */
	function kcmp_apply_filters( $hook_name, $value, ...$args ) {
		return apply_filters( kcmp_get_stylesheet_tag( $hook_name ), $value, ...$args );
	}
}

if ( ! function_exists( 'kcmp_add_filter' ) ) {
	/**
	 * @param $hook_name
	 * @param $callback
	 * @param $priority
	 * @param $accepted_args
	 *
	 * @return true|null
	 * @since v1.2.2
	 */
	function kcmp_add_filter( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
		return add_filter( kcmp_get_stylesheet_tag( $hook_name ), $callback, $priority, $accepted_args );
	}
}
