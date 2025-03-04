<?php

/**
 * The public-facing functionality of the plugin.
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
 * AYG_Public class.
 *
 * @since 1.0.0
 */
class AYG_Public {

	/**
	 * Get things started.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_shortcode( 'automatic_youtube_gallery', array( $this, 'shortcode_automatic_youtube_gallery' ) );
	}	

	/**
	 * Enqueue styles for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function register_styles() {
		$player_settings = get_option( 'ayg_player_settings' );

		$deps = array();
		if ( isset( $player_settings['player_type'] ) && 'custom' == $player_settings['player_type'] ) {
			$deps[] = AYG_SLUG . '-plyr';
		}

		wp_register_style( 
			AYG_SLUG . '-plyr', 
			AYG_URL . 'vendor/plyr/plyr.css', 
			array(), 
			'3.7.8', 
			'all' 
		);

		wp_register_style( 
			AYG_SLUG . '-public', 
			AYG_URL . 'public/assets/css/public.min.css', 
			$deps, 
			AYG_VERSION, 
			'all' 
		);
	}

	/**
	 * Enqueue scripts for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function register_scripts() {
		$gallery_settings = get_option( 'ayg_gallery_settings' );
		$player_settings  = get_option( 'ayg_player_settings' );
		$privacy_settings = get_option( 'ayg_privacy_settings' );

		$deps = array( 'jquery' );
		if ( isset( $player_settings['player_type'] ) && 'custom' == $player_settings['player_type'] ) {
			$deps[] = AYG_SLUG . '-plyr';
		}

		wp_register_script( 
			AYG_SLUG . '-plyr', 
			AYG_URL . 'vendor/plyr/plyr.polyfilled.js', 
			array(), 
			'3.7.8', 
			array( 'strategy' => 'defer' )  
		);

		wp_register_script( 
			AYG_SLUG . '-public', 
			AYG_URL . 'public/assets/js/public.min.js', 
			$deps, 
			AYG_VERSION, 
			array( 'strategy' => 'defer' )  
		);

		$scroll_top_offset = ( isset( $gallery_settings['scroll_top_offset'] ) && ! empty( $gallery_settings['scroll_top_offset'] ) ) ? (int) $gallery_settings['scroll_top_offset'] : 10;
		$scroll_top_offset = apply_filters( 'ayg_gallery_scrolltop_offset', $scroll_top_offset ); // Backward compatibility to 2.4.3
		$scroll_top_offset = apply_filters( 'ayg_gallery_scroll_top_offset', $scroll_top_offset );

		$script_args = array(
			'ajax_url'              => admin_url( 'admin-ajax.php' ),
			'ajax_nonce'            => wp_create_nonce( 'ayg_ajax_nonce' ),	
			'current_page_url'      => get_permalink(),
			'current_gallery_id'    => get_query_var( 'ayg_gallery_id' ),					
			'player_type'           => isset( $player_settings['player_type'] ) ? sanitize_text_field( $player_settings['player_type'] ) : 'youtube',
			'player_color'          => isset( $player_settings['player_color'] ) ? sanitize_text_field( $player_settings['player_color'] ) : '#00b3ff',	
			'privacy_enhanced_mode' => isset( $player_settings['privacy_enhanced_mode'] ) ? (int) $player_settings['privacy_enhanced_mode'] : 0,
			'origin'                => '',
			'cookieconsent'         => 0,
			'top_offset'            => $scroll_top_offset,
			'i18n'                  => array(
				'show_more' => '[+] ' . __( 'Show More', 'automatic-youtube-gallery' ),
				'show_less' => '[-] ' . __( 'Show Less', 'automatic-youtube-gallery' )
			)
		);

		if ( isset( $player_settings['origin'] ) && ! empty( $player_settings['origin'] ) ) {
			$url_parts = parse_url( site_url() );
			$script_args['origin'] = $url_parts['scheme'] . '://' . $url_parts['host'];
		}

		if ( ! isset( $_COOKIE['ayg_gdpr_consent'] ) ) {
			if ( ! empty( $privacy_settings['cookie_consent'] ) && ! empty( $privacy_settings['consent_message'] ) && ! empty( $privacy_settings['button_label'] ) ) {
				$script_args['cookieconsent'] = 1;
				$script_args['cookieconsent_message'] = wp_kses_post( trim( $privacy_settings['consent_message'] ) );
				$script_args['cookieconsent_button_label'] = esc_html( $privacy_settings['button_label'] );
			}
		}

		wp_localize_script( 
			AYG_SLUG . '-public', 
			'ayg_config', 
			$script_args
		);

		wp_register_script( 
			AYG_SLUG . '-theme-classic', 
			AYG_URL . 'public/assets/js/theme-classic.min.js', 
			array( 'jquery' ), 
			AYG_VERSION, 
			array( 'strategy' => 'defer' )  
		);
	}

	/**
	 * Enqueue Gutenberg block assets for backend editor.
	 *
	 * @since 1.6.1
	 */
	public function enqueue_block_editor_assets() {
		// Styles
		$this->register_styles();
		wp_enqueue_style( AYG_SLUG . '-public' );

		// Scripts
		$this->register_scripts();

		wp_enqueue_script( AYG_SLUG . '-public' );
		wp_enqueue_script( AYG_SLUG . '-theme-classic' );
	}

	/**
	 * Process the shortcode [automatic_youtube_gallery].
	 *
	 * @since  1.0.0
	 * @param  array  $attributes An associative array of attributes.
	 * @return string             Shortcode HTML output.
	 */
	public function shortcode_automatic_youtube_gallery( $attributes ) {
		return ayg_build_gallery( $attributes );
	}

	/**
	 * Load more videos.
	 *
	 * @since 1.0.0
	 */
	public function ajax_callback_load_more_videos() {
		// Security check
		check_ajax_referer( 'ayg_ajax_nonce', 'security' );	

		// Proceed safe
		$json        = array();
		$attributes  = array_map( 'sanitize_text_field', $_POST );
		$source_type = $attributes['type'];

		$api_params = array(
			'type'       => $source_type,
			'src'        => $attributes['src'],
			'order'      => $attributes['order'], // works only when type=search
			'maxResults' => (int) $attributes['per_page'],
			'cache'      => (int) $attributes['cache'],
			'pageToken'  => $attributes['pageToken']
		);

		$youtube_api = new AYG_YouTube_API();
		$response = $youtube_api->query( $api_params );

		if ( ! isset( $response->error ) ) {
			if ( isset( $response->page_info ) ) {
				$json = $response->page_info;
			}

			if ( isset( $response->videos ) ) {
				$videos = $response->videos;
				$columns = (int) $attributes['columns'];

				ob_start();
				foreach ( $videos as $index => $video ) {
					$classes = array(); 
					$classes[] = 'ayg-video';
					$classes[] = 'ayg-video-' . $video->id;
					$classes[] = 'ayg-col';
					$classes[] = 'ayg-col-' . $columns;
					if ( $columns > 3 ) $classes[] = 'ayg-col-sm-3';
					if ( $columns > 2 ) $classes[] = 'ayg-col-xs-2';

					echo'<div class="' . implode( ' ', $classes ) . '">';
					the_ayg_gallery_thumbnail( $video, $attributes );
					echo '</div>';
				}
				$json['html'] = ob_get_clean();
			}	

			wp_send_json_success( $json );			
		} else {
			$json['message'] =  $response->error_message;
			wp_send_json_error( $json );			
		}		
	}

	/**
	 * Set cookie for accepting the privacy consent.
	 *
	 * @since 2.0.0
	 */
	public function set_gdpr_cookie() {	
		// Security check
		check_ajax_referer( 'ayg_ajax_nonce', 'security' );	

		// Proceed safe
		setcookie( 'ayg_gdpr_consent', 1, time() + ( 30 * 24 * 60 * 60 ), COOKIEPATH, COOKIE_DOMAIN );		
		wp_send_json_success();			
	}

	/**
	 * [SMUSH] Skip YouTube iframes from lazy loading.
	 *
	 * @since  1.5.0
	 * @param  bool   $skip Should skip? Default: false.
	 * @param  string $src  Iframe url.
	 * @return bool
	 */
	public function smush( $skip, $src ) {
		return false !== strpos( $src, 'youtube' );
	}

}
