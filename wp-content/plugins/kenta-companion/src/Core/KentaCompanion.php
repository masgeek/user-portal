<?php

namespace KentaCompanion\Core;

use KentaCompanion\Container\Container;

/**
 * Container instance for our plugin
 *
 * @package Kenta Companion
 */
class KentaCompanion extends Container {

	public function __construct() {
		self::setInstance( $this );

		// register modules
		add_action( 'kcmp/after_bootstrap', [ $this, 'after_boostrap' ] );

		// admin init
		add_action( 'rest_api_init', [ Route::class, 'api_v1' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
		add_action( 'kcmp/show_admin_setup_page', [ $this, 'show_about_kenta_theme_page' ] );
		add_action( 'admin_action_kcmp_deactivate_classic_editor', 'kcmp_deactivate_classic_editor' );
		add_filter( 'kenta_admin_page_url', [ $this, 'admin_page_url' ], 10, 2 );
		add_filter( 'kenta_admin_page_tabs', [ $this, 'admin_tabs' ] );
		add_filter( 'kenta_admin_page_customizer_items', [ $this, 'admin_page_customizer_items' ] );

		// customizer settings
		kcmp_add_action( 'customizer_default_settings_saved', [ $this, 'update_customizer_settings_version' ] );
		kcmp_add_action( 'should_reload_customizer_settings', [ $this, 'should_customizer_settings_reload' ] );

		// dynamic css
		kcmp_add_action( 'dynamic_css_cached', [ $this, 'update_cached_css_version' ] );
		kcmp_add_filter( 'should_dynamic_css_re_cached', [ $this, 'should_dynamic_css_re_cached' ] );

		// starter sites
		add_action( 'kcmp/template_imported', [ $this, 'update_imported_template' ], 10, 2 );

		if ( is_plugin_active( 'classic-editor/classic-editor.php' ) ) {
			add_action( 'admin_notices', [ $this, 'show_disable_classic_editor_notice' ] );
		}

		// opt-in notice
		add_action( 'current_screen', [ $this, 'remove_optin_notice' ] );
		if ( ! kenta_fs()->is_registered() && current_user_can( 'manage_options' ) ) {
			kcmp_notices()->add_notice(
				sprintf(
					__( 'We made a few tweaks to the Kenta Companion, %s', 'kenta-companion' ),
					sprintf( '<b><a href="%s">%s</a></b>',
						add_query_arg( [ 'page' => 'kenta-companion-optin' ], admin_url( 'admin.php' ) ),
						__( 'Opt in to make Kenta Companion better!', 'kenta-companion' )
					)
				),
				'connect_account',
				'Kenta Companion'
			);
		}
	}

	/**
	 * Remove opt-in notice in opt-in screen
	 */
	public function remove_optin_notice() {
		$screen = get_current_screen();
		if ( 'kenta-blocks_page_kenta-blocks-optin' === $screen->id ||
		     'kenta_page_kenta-companion-optin' === $screen->id ) {
			kcmp_notices()->remove_notice( 'connect_account' );
		}
	}

	/**
	 * Boostrap theme extenions
	 *
	 * @return void
	 */
	public function after_boostrap() {
		if ( ! kcmp_is_valid_theme() ) {
			return;
		}

		// register extensions
		$extensions = kcmp( 'extensions' );
		$extensions
			->register( 'builder', [
				'class' => \KentaCompanion\Extensions\Builder::class,
			] )
			->register( 'archive', [
				'class' => \KentaCompanion\Extensions\Archive::class,
			] )
			->register( 'article', [
				'class' => \KentaCompanion\Extensions\Article::class,
			] )
			->register( 'button', [
				'class' => \KentaCompanion\Extensions\Button::class,
			] )
			->register( 'icon-button', [
				'class' => \KentaCompanion\Extensions\IconButton::class,
			] )
			->register( 'copyright', [
				'class' => \KentaCompanion\Extensions\Copyright::class,
			] )
			->register( 'breadcrumbs', [
				'class' => \KentaCompanion\Extensions\Breadcrumbs::class,
			] )
			->register( 'widgets', [
				'class' => \KentaCompanion\Extensions\Widgets::class,
			] )
			->register( 'menu-element', [
				'class' => \KentaCompanion\Extensions\MenuElement::class,
			] )
			->register( 'socials', [
				'class' => \KentaCompanion\Extensions\Socials::class,
			] )
			->register( 'scroll-top', [
				'class' => \KentaCompanion\Extensions\ScrollTop::class,
			] )
			->register( 'cookies-consent', [
				'class' => \KentaCompanion\Extensions\CookiesConsent::class,
			] )
			->register( 'card', [
				'class' => \KentaCompanion\Extensions\Card::class,
			] )
			->register( 'pagination', [
				'class' => \KentaCompanion\Extensions\Pagination::class,
			] )
			->register( 'lightbox', [
				'class' => \KentaCompanion\Extensions\Lightbox::class,
			] )
			->register( 'scroll-reveal', [
				'class' => \KentaCompanion\Extensions\ScrollReveal::class,
			] )
			->register( 'reset', [
				'class' => \KentaCompanion\Extensions\Reset::class,
			] )
			->register( 'custom-fonts', [
				'class' => \KentaCompanion\Extensions\CustomFonts::class,
			] )
			->register( 'particles', [
				'class' => \KentaCompanion\Extensions\Particles::class,
			] )
			->bootstrap();
	}

	/**
	 * Register admin menu
	 */
	public function admin_menu() {
		$setup   = kcmp_plugin_setup_page();
		$starter = kcmp_plugin_starter_page();
		$optin   = kcmp_plugin_optin_page();

		add_menu_page(
			$setup['page_title'],
			$setup['menu_title'],
			$setup['capability'],
			$setup['menu_slug'],
			[ $this, 'show_admin_setup_page' ],
			KCMP_ASSETS_URL . 'images/kenta-logo.svg',
			'58.6'
		);

		if ( kcmp_is_valid_theme() ) {
			add_submenu_page(
				$starter['parent_slug'],
				$starter['page_title'],
				$starter['menu_title'],
				$starter['capability'],
				$starter['menu_slug'],
				[ $this, 'show_starter_sites_page' ]
			);
		}

		if ( ! kenta_fs()->is_registered() ) {
			add_submenu_page(
				$optin['parent_slug'],
				$optin['page_title'],
				$optin['menu_title'],
				$optin['capability'],
				$optin['menu_slug'],
				[ kenta_fs(), '_connect_page_render' ]
			);
		}
	}

	public function admin_tabs( $tabs ) {
		return array_merge( $tabs, [
			'starter-sites' => [
				'label' => __( 'Starter Sites', 'kenta-companion' ),
				'url'   => add_query_arg( [ 'page' => 'kenta-starter-sites' ], admin_url( 'admin.php' ) ),
			],
			'opt-in'        => [
				'label' => __( 'Opt In', 'kenta-companion' ),
				'url'   => add_query_arg( [ 'page' => 'kenta-companion-optin' ], admin_url( 'admin.php' ) ),
				'skip'  => kenta_fs()->is_registered(),
			],
		] );
	}

	public function admin_page_url( $url, $args ) {

		return add_query_arg( array_merge( $args, [
			'page' => 'kenta-companion'
		] ), admin_url( 'admin.php' ) );
	}

	public function admin_page_customizer_items( $items ) {
		return array_merge( $items, [
			[
				'label'    => __( 'Cookies Consent Settings', 'kenta-companion' ),
				'icon'     => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M257.5 27.6c-.8-5.4-4.9-9.8-10.3-10.6c-22.1-3.1-44.6 .9-64.4 11.4l-74 39.5C89.1 78.4 73.2 94.9 63.4 115L26.7 190.6c-9.8 20.1-13 42.9-9.1 64.9l14.5 82.8c3.9 22.1 14.6 42.3 30.7 57.9l60.3 58.4c16.1 15.6 36.6 25.6 58.7 28.7l83 11.7c22.1 3.1 44.6-.9 64.4-11.4l74-39.5c19.7-10.5 35.6-27 45.4-47.2l36.7-75.5c9.8-20.1 13-42.9 9.1-64.9c-.9-5.3-5.3-9.3-10.6-10.1c-51.5-8.2-92.8-47.1-104.5-97.4c-1.8-7.6-8-13.4-15.7-14.6c-54.6-8.7-97.7-52-106.2-106.8zM208 208c-17.7 0-32-14.3-32-32s14.3-32 32-32s32 14.3 32 32s-14.3 32-32 32zm0 128c0 17.7-14.3 32-32 32s-32-14.3-32-32s14.3-32 32-32s32 14.3 32 32zm160 0c-17.7 0-32-14.3-32-32s14.3-32 32-32s32 14.3 32 32s-14.3 32-32 32z"/></svg>',
				'location' => 'kenta_global:kenta_global_cookies_consent',
			],
		] );
	}

	public function show_disable_classic_editor_notice() {
		kcmp_get_template_part( 'classic-editor-notice' );
	}

	public function show_about_kenta_theme_page() {
		if ( kcmp_is_valid_theme() ) {
			return;
		}

		kcmp_get_template_part( 'about-kenta' );
	}

	/**
	 * Show setup page
	 */
	public function show_admin_setup_page() {
		do_action( 'kcmp/show_admin_setup_page' );
	}

	/**
	 * Show starter sites
	 */
	public function show_starter_sites_page() {
		kcmp_get_template_part( 'starter-sites' );
	}

	/**
	 * Save current template in database
	 *
	 * @param $slug
	 * @param $types
	 */
	public function update_imported_template( $slug, $types = [] ) {
		update_option( 'kenta_active_template', $slug );
	}

	/**
	 * Update cached css version
	 */
	public function update_cached_css_version() {
		update_option( 'kcmp_dynamic_css_cached_version', esc_html( $this->get_code_verion() ) );
	}

	/**
	 * Check if dynamic css should be re cached
	 *
	 * If the companion plugin version changed, bust the cache.
	 *
	 * @param $bool
	 *
	 * @return mixed
	 *
	 * @since 1.1.4
	 */
	public function should_dynamic_css_re_cached( $bool ) {

		$cached_version = get_option( 'kcmp_dynamic_css_cached_version', '' );
		if ( $this->get_code_verion() !== $cached_version ) {
			return true;
		}

		return $bool;
	}

	/**
	 *  Update customizer settings css version
	 *
	 * @return void
	 * @since v1.2.2
	 */
	public function update_customizer_settings_version() {
		update_option( 'kcmp_customizer_default_settings_version', esc_html( $this->get_code_verion() ) );
	}

	/**
	 * Check if customizer settings should be reloaded
	 *
	 * If the companion plugin version changed, bust the cache.
	 *
	 * @param $bool
	 *
	 * @return true
	 * @since v1.2.2
	 */
	public function should_customizer_settings_reload( $bool ) {
		$cached_version = get_option( 'kcmp_customizer_default_settings_version', '' );
		if ( $this->get_code_verion() !== $cached_version ) {
			return true;
		}

		return $bool;
	}

	/**
	 * Get code version
	 *
	 * @return string
	 */
	private function get_code_verion() {
		return kenta_fs()->can_use_premium_code() ? 'premium' : 'free' . '-' . KCMP_VERSION;
	}
}