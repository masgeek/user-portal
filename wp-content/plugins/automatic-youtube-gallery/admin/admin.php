<?php

/**
 * The admin-specific functionality of the plugin.
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
 * AYG_Admin class.
 *
 * @since 1.0.0
 */
class AYG_Admin {

	/**
	 * Insert missing plugin options.
	 *
	 * @since 1.6.4
	 */
	public function insert_missing_options() {		
		if ( AYG_VERSION !== get_option( 'ayg_version' ) ) {	
			$defaults = ayg_get_default_settings();				

			// Update the player settings
			$player_settings = get_option( 'ayg_player_settings' );

			if ( ! array_key_exists( 'player_type', $player_settings ) ) {
				$player_settings['player_type']  = $defaults['ayg_player_settings']['player_type'];
				$player_settings['player_color'] = $defaults['ayg_player_settings']['player_color'];

				update_option( 'ayg_player_settings', $player_settings );
			}

			// Insert the livestream settings			
			if ( false == get_option( 'ayg_livestream_settings' ) ) {
				add_option( 'ayg_livestream_settings', $defaults['ayg_livestream_settings'] );
			}

			// Insert the privacy settings			
			if ( false == get_option( 'ayg_privacy_settings' ) ) {
				add_option( 'ayg_privacy_settings', $defaults['ayg_privacy_settings'] );
			}

			// Create a custom database table "{$wpdb->prefix}ayg_videos" 
			if ( version_compare( AYG_VERSION, '2.1.0', '<=' ) ) {
				ayg_db_create_videos_table();
			}

			// Delete the plugin cache
			ayg_delete_cache();
			
			// Update the plugin version		
			update_option( 'ayg_version', AYG_VERSION );
		}
	}

	/**
	 * Enqueue styles for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_style( 
			AYG_SLUG . '-magnific-popup', 
			AYG_URL . 'vendor/magnific-popup/magnific-popup.css', 
			array(), 
			'1.2.0', 
			'all' 
		);

		wp_enqueue_style( 
			AYG_SLUG . '-admin', 
			AYG_URL . 'admin/assets/css/admin.min.css', 
			array(), 
			AYG_VERSION, 
			'all' 
		);
	}

	/**
	 * Enqueue scripts for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_script( 
			AYG_SLUG . '-magnific-popup', 
			AYG_URL . 'vendor/magnific-popup/magnific-popup.min.js', 
			array( 'jquery' ), 
			'1.2.0', 
			array( 'strategy' => 'defer' )  
		);

		wp_enqueue_script( 
			AYG_SLUG . '-admin', 
			AYG_URL . 'admin/assets/js/admin.min.js', 
			array( 'jquery' ), 
			AYG_VERSION, 
			false 
		);

		wp_localize_script( 
			AYG_SLUG . '-admin', 
			'ayg_admin', 
			array(
				'ajax_nonce' => wp_create_nonce( 'ayg_ajax_nonce' ),
				'i18n'       => array(					
					'invalid_api_key' => __( 'Invalid API Key', 'automatic-youtube-gallery' ),
					'cleared'         => __( 'Cleared', 'automatic-youtube-gallery' )
				)					
			)
		);		
	}	

	/**
	 * Add dashboard page link on the plugins menu.
	 *
	 * @since  1.0.0
	 * @param  array  $links An array of plugin action links.
	 * @return string $links Array of filtered plugin action links.
	 */
	public function plugin_action_links( $links ) {
		$dashboard_link = sprintf( 
			'<a href="%s">%s</a>', 
			admin_url( 'admin.php?page=automatic-youtube-gallery' ), 
			__( 'Build Gallery', 'automatic-youtube-gallery' ) 
		);
		
        array_unshift( $links, $dashboard_link );
		
    	return $links;
	}

	/**
	 * Add "Dashboard" menu.
	 *
	 * @since 1.3.0
	 */
	public function admin_menu() {	
		add_menu_page( 
			__( 'Automatic YouTube Gallery', 'automatic-youtube-gallery' ), 
			__( 'YouTube Gallery', 'automatic-youtube-gallery' ),
			'manage_options', 
			'automatic-youtube-gallery', 
			array( $this, 'display_dashboard_content' ),
			'dashicons-video-alt3', 
			10 
		);

		add_submenu_page(
			'automatic-youtube-gallery',
			__( 'Dashboard', 'automatic-youtube-gallery' ),
			__( 'Dashboard', 'automatic-youtube-gallery' ),
			'manage_options',
			'automatic-youtube-gallery',
			array( $this, 'display_dashboard_content' )
		);
	}

	/**
	 * Display dashboard content.
	 *
	 * @since 1.3.0
	 */
	public function display_dashboard_content() {
		$general_settings = get_option( 'ayg_general_settings' );

		$tabs = array(
			'dashboard' => __( 'Build Gallery', 'automatic-youtube-gallery' )
		);
		
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'dashboard';

		require_once AYG_DIR . 'admin/templates/dashboard.php';				
	}

	/**
	 * Prints admin screen notices.
	 *
	 * @since 2.0.0
	 */
	public function admin_notices() {
		$general_settings = get_option( 'ayg_general_settings' );

		if ( isset( $general_settings['development_mode'] ) && ! empty( $general_settings['development_mode'] ) ) {
			?>
			<div class="notice notice-info">
                <p>
					<?php 
					printf(
						__( '<strong>Automatic YouTube Gallery:</strong> You have <a href="%s">development mode</a> enabled. We do not cache API results in this mode. While this is ok when you are testing the plugin, we strongly recommend disabling this option when your site goes live.', 'automatic-youtube-gallery' ),
						esc_url( admin_url( 'admin.php?page=automatic-youtube-gallery-settings' ) )
					); 
					?>
				</p>
            </div>
			<?php
		}
	}

	/**
	 * Save API Key.
	 *
	 * @since 1.3.0
	 */
	public function ajax_callback_save_api_key() {	
		check_ajax_referer( 'ayg_ajax_nonce', 'security' );
		
		if ( current_user_can( 'manage_options' ) ) {
			$general_settings = get_option( 'ayg_general_settings' );
			$general_settings['api_key'] = sanitize_text_field( $_POST['api_key'] );

			update_option( 'ayg_general_settings', $general_settings );
		}

		wp_die();	
	}

}
