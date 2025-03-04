<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
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
 * AYG_Init - The main plugin class.
 *
 * @since 1.0.0
 */
class AYG_Init {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    AYG_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * Get things started.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_block_hooks();
		$this->define_widget_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once AYG_DIR . 'includes/loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once AYG_DIR . 'includes/i18n.php';

		/**
		 * A wrapper class for the Youtube Data API v3.
		 */
		require_once AYG_DIR . 'includes/youtube-api.php';

		/**
		 * The file that holds the general helper functions.
		 */
		require_once AYG_DIR . 'includes/functions.php';

		/**
		 * The classes responsible for defining all actions that occur in the admin area.
		 */
		require_once AYG_DIR . 'admin/admin.php';	
		require_once AYG_DIR . 'admin/settings.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once AYG_DIR . 'public/public.php';
		require_once AYG_DIR . 'public/cron.php';

		/**
		 * The class responsible for defining all actions that occur in the gutenberg block.
		 */
		require_once AYG_DIR. 'block/block.php';
		
		/**
		 * The class responsible for defining all actions that occur in the widget.
		 */
		require_once AYG_DIR . 'widget/widget.php';

		/**
		 * Create an instance of the loader.
		 */
		$this->loader = new AYG_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {
		$i18n = new AYG_i18n();		
		$this->loader->add_action( 'plugins_loaded', $i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {
		// Hooks common to all admin pages
		$admin = new AYG_Admin();

		$this->loader->add_action( 'admin_init', $admin, 'insert_missing_options', 1 );	
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action( 'elementor/editor/after_enqueue_styles', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'elementor/editor/after_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $admin, 'admin_menu' );
		$this->loader->add_action( 'admin_notices', $admin, 'admin_notices' );
		$this->loader->add_action( 'wp_ajax_ayg_save_api_key', $admin, 'ajax_callback_save_api_key' );
		
		$this->loader->add_filter( 'plugin_action_links_' . AYG_FILE_NAME, $admin, 'plugin_action_links' );

		// Hooks specific to the settings page
		$settings = new AYG_Admin_Settings();
		
		$this->loader->add_action( 'admin_menu', $settings, 'admin_menu' );
		$this->loader->add_action( 'admin_init', $settings, 'admin_init' );
		$this->loader->add_action( 'wp_ajax_ayg_delete_cache', $settings, 'ajax_callback_delete_cache' );		
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_public_hooks() {
		// Hooks common to all public pages
		$public = new AYG_Public();
		
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'register_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $public, 'register_scripts' );
		$this->loader->add_action( 'enqueue_block_editor_assets', $public, 'enqueue_block_editor_assets' );
		$this->loader->add_action( 'elementor/editor/after_enqueue_scripts', $public, 'enqueue_block_editor_assets' );
		$this->loader->add_action( 'elementor/preview/enqueue_scripts', $public, 'enqueue_block_editor_assets' );		
		$this->loader->add_action( 'wp_ajax_ayg_load_more_videos', $public, 'ajax_callback_load_more_videos' );
		$this->loader->add_action( 'wp_ajax_nopriv_ayg_load_more_videos', $public, 'ajax_callback_load_more_videos' );
		$this->loader->add_action( 'wp_ajax_ayg_set_cookie', $public, 'set_gdpr_cookie' );
		$this->loader->add_action( 'wp_ajax_nopriv_ayg_set_cookie', $public, 'set_gdpr_cookie' );

		$this->loader->add_filter( 'smush_skip_iframe_from_lazy_load', $public, 'smush', 999, 2 );

		// Hooks specific to the cron jobs
		$cron = new AYG_Public_Cron();

		$this->loader->add_action( 'wp', $cron, 'schedule_events' );
		$this->loader->add_action( 'ayg_schedule_weekly', $cron, 'cron_event' );

		$this->loader->add_filter( 'cron_schedules', $cron, 'cron_schedules' );
	}

	/**
	 * Register all of the hooks related to the gutenberg block.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_block_hooks() {
		if ( is_admin() ) {
			global $pagenow;
			if ( 'widgets.php' === $pagenow ) return;
		}

		global $wp_version;

		$block = new AYG_Block();

		$this->loader->add_action( 'init', $block, 'register_block_type' );
		$this->loader->add_action( 'enqueue_block_editor_assets', $block, 'enqueue_block_editor_assets' );

		if ( version_compare( $wp_version, '5.8', '>=' ) ) {
			$this->loader->add_filter( 'block_categories_all', $block, 'block_categories' );
		} else {
			$this->loader->add_filter( 'block_categories', $block, 'block_categories' );
		}
	}
	
	/**
	 * Register all of the hooks related to the widget.
	 *
	 * @since  2.1.0
	 * @access private
	 */
	private function define_widget_hooks() {
		$this->loader->add_action( 'widgets_init', $this, 'register_widget' );
	}

	/**
	 * Register the widget.
	 *
	 * @since 2.1.0
	 */
	public function register_widget() {		
		register_widget( 'AYG_Widget' );		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

}