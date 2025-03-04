<?php

/**
 * Helper Functions.
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
 * Build gallery HTML output.
 *
 * @since  1.0.0
 * @param  array $args An array of gallery options.
 * @return mixed
 */
function ayg_build_gallery( $args ) {
	$general_settings = get_option( 'ayg_general_settings' );

	global $post;

	// Vars
	$fields   = ayg_get_editor_fields();
	$defaults = array();

	foreach ( $fields as $key => $value ) {
		foreach ( $value['fields'] as $field ) {
			$defaults[ $field['name'] ] = $field['value'];
		}
	}

	$attributes = shortcode_atts( $defaults, $args );

	$attributes['post_id'] = 0;
	if ( isset( $post->ID ) ) {
		$attributes['post_id'] = (int) $post->ID;
	}

	$attributes['columns'] = min( 12, (int) $attributes['columns'] );
	if ( empty( $attributes['columns'] ) ) {
		$attributes['columns'] = 3;
	}

	$attributes['limit'] = min( 500, (int) $attributes['limit'] );
	if ( empty( $attributes['limit'] ) ) {
		$attributes['limit'] = 500;
	}
	
	$attributes['per_page'] = min( 50, (int) $attributes['per_page'] );
	if ( empty( $attributes['per_page'] ) ) {
		$attributes['per_page'] = 50;
	}

	$source_type = sanitize_text_field( $attributes['type'] );
	if ( 'livestream' == $source_type ) {
		$attributes['livestream'] = $attributes['channel'];
	}

	if ( isset( $args['uid'] ) && ! empty( $args['uid'] ) ) {
		$attributes['uid'] = $args['uid'];
	} else {
		$attributes['uid'] = md5( $source_type . sanitize_text_field( $attributes[ $source_type ] ) . sanitize_text_field( $attributes['theme'] ) );
	}

	$attributes['lazyload'] = 0;
	if ( isset( $general_settings['lazyload'] ) && ! empty( $general_settings['lazyload'] ) ) {
		$attributes['lazyload'] = 1;
	}

	// Get Videos
	$api_params = array(
		'type'       => $source_type,
		'src'        => sanitize_text_field( $attributes[ $source_type ] ),
		'order'      => sanitize_text_field( $attributes['order'] ), // applicable only when type=search
		'maxResults' => $attributes['per_page'],
		'cache'      => (int) $attributes['cache']
	);

	$api_params = apply_filters( 'ayg_youtube_api_request_params', $api_params, $args );

	$youtube_api = new AYG_YouTube_API();
	$response = $youtube_api->query( $api_params );

	// Process output
	if ( ! isset( $response->error ) ) {
		// Store Gallery ID
		if ( $attributes['post_id'] > 0 && isset( $attributes['deeplinking'] ) && 1 == $attributes['deeplinking'] ) {
			$pages = get_option( 'ayg_gallery_page_ids', array() );
			$page_id = $attributes['post_id'];

			if ( ! in_array( $page_id, $pages ) ) {
				$pages[] = $page_id;
				update_option( 'ayg_gallery_page_ids', $pages );
			}		
		}
		
		// Gallery
		$videos = array();		

		$gallery_id_from_url = get_query_var( 'ayg_gallery_id' );	
		$video_id_from_url = get_query_var( 'ayg_video_id' );	

		if ( $attributes['uid'] == $gallery_id_from_url ) {
			$video = ayg_db_get_video( $video_id_from_url );		

			if ( $video ) {
				$videos[] = $video;
			} else {
				$video_id_from_url = '';
			}
		}
		
		if ( isset( $response->videos ) ) {
			if ( ! empty( $video_id_from_url ) ) {
				foreach ( $response->videos as $video ) {
					if ( $video->id != $video_id_from_url ) {
						$videos[] = $video;
					}
				}
			} else {
				$videos = $response->videos;
			}
		}

		// Pagination
		if ( isset( $response->page_info ) ) {
			$attributes = array_merge( $attributes, $api_params, $response->page_info );
		}

		// Theme
		$theme = 'classic';

		if ( 'video' == $source_type ) {
			$theme = 'single';
		} elseif ( 'livestream' == $source_type ) {
			$theme = 'livestream';
		} else {
			if ( 1 == count( $videos ) ) {
				$theme = 'single';
			}
		}

		// Enqueue dependencies
		wp_enqueue_style( AYG_SLUG . '-public' );

		wp_enqueue_script( AYG_SLUG . '-public' );		
		if ( $attributes['theme'] == $theme && 'classic' == $attributes['theme'] ) {
			wp_enqueue_script( AYG_SLUG . '-theme-classic' );
		}

		// Output
		ob_start();
		include ayg_get_template( AYG_DIR . "public/templates/theme-{$theme}.php", $attributes['theme'] );
		return ob_get_clean();
	} else {
		return sprintf(	'<div class="ayg ayg-error">%s</div>', wp_kses_post( $response->error_message )	);
	}
}

/**
 * Combine video attributes as a string.
 * 
 * @since 2.5.0
 * @param array  $atts Array of video attributes.
 * @param string       Combined attributes string.
 */
function ayg_combine_video_attributes( $atts ) {
	$attributes = array();
	
	foreach ( $atts as $key => $value ) {
		if ( '' === $value ) {
			$attributes[] = $key;
		} else {
			$attributes[] = sprintf( '%s="%s"', $key, $value );
		}
	}
	
	return implode( ' ', $attributes );
}

/**
 * Create a custom database table "{$wpdb->prefix}ayg_videos" 
 *
 * @since 2.1.0
 */
function ayg_db_create_videos_table() {
	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();	

	$sql = "CREATE TABLE `{$wpdb->prefix}ayg_videos` (
		NUM bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		id varchar(100) NOT NULL,
		title text NOT NULL,
		description text NOT NULL,
		thumbnails text NOT NULL,		
		duration varchar(25) NOT NULL,
		status varchar(100) NOT NULL,
		published_at varchar(100) NOT NULL,
		PRIMARY KEY  (NUM)
	) $charset_collate;";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

/**
 * Store videos in our custom database table "{$wpdb->prefix}ayg_videos" 
 *
 * @since 2.1.0
 * @param object $data YouTube API response object.
 */
function ayg_db_store_videos( $data ) {
	if ( AYG_VERSION !== get_option( 'ayg_version' ) ) {
		return false;
	}

	global $wpdb, $post;

	$table_name = $wpdb->prefix . 'ayg_videos';

	if ( isset( $data->kind ) && 'youtube#channelListResponse' == $data->kind ) {
		return false;				
	}

	if ( ! isset( $data->items ) ) {
		return false;
	}

	$items = $data->items;
	if ( ! is_array( $items ) || 0 == count( $items ) ) {
		return false;
	}	

	// Store Videos
	foreach ( $items as $item ) {	
		$row = array();

		// Video ID
		$row['id'] = '';	

		if ( isset( $item->snippet->resourceId ) && isset( $item->snippet->resourceId->videoId ) ) {
			$row['id'] = $item->snippet->resourceId->videoId;
		} elseif ( isset( $item->contentDetails ) && isset( $item->contentDetails->videoId ) ) {
			$row['id'] = $item->contentDetails->videoId;
		} elseif ( isset( $item->id ) && isset( $item->id->videoId ) ) {
			$row['id'] = $item->id->videoId;
		} elseif ( isset( $item->id ) ) {
			$row['id'] = $item->id;
		}	

		if ( empty( $row['id'] ) ) {
			continue;
		}

		// Video title
		$row['title'] = $item->snippet->title;

		// Video description
		$row['description'] = $item->snippet->description;

		// Video thumbnails
		$row['thumbnails'] = '';
		if ( isset( $item->snippet->thumbnails ) ) {
			$row['thumbnails'] = serialize( $item->snippet->thumbnails );
		}		

		// Video duration
		$row['duration'] = '';
		if ( isset( $item->contentDetails ) && isset( $item->contentDetails->duration ) ) {
			$row['duration'] = $item->contentDetails->duration;
		}		

		// Video status
		$row['status'] = 'private';
		
		if ( isset( $item->status ) && ( 'public' == $item->status->privacyStatus || 'unlisted' == $item->status->privacyStatus ) ) {
			$row['status'] = 'public';				
		}

		if ( isset( $item->snippet->status ) && ( 'public' == $item->snippet->status->privacyStatus || 'unlisted' == $item->snippet->status->privacyStatus ) ) {
			$row['status'] = 'public';				
		}

		if ( 'youtube#searchResult' == $item->kind ) {
			$row['status'] = 'public';				
		}

		// Video publish date
		$row['published_at'] = $item->snippet->publishedAt;

		// Store
		$query = $wpdb->prepare( "SELECT NUM FROM $table_name WHERE id = %s", $row['id'] );
		$insert_id = $wpdb->get_var( $query );

		if ( empty( $insert_id ) ) {
			$wpdb->insert( 
				$table_name, 
				$row, 
				array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
			);
		} else {
			$wpdb->update( 
				$table_name, 
				$row, 
				array( 'NUM' => $insert_id ), 
				array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' ),
				array( '%d' ) 
			);
		}
	}
}

/**
 * Get a single video record from our custom database table "{$wpdb->prefix}ayg_videos" 
 *
 * @since  2.1.0
 * @param  string $video_id YouTube Video ID.
 * @return mixed
 */
function ayg_db_get_video( $video_id ) {
	global $wpdb;

	$cache_key = 'ayg_'. $video_id;
	$row = wp_cache_get( $cache_key );

	if ( false === $row ) {
		$query = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}ayg_videos WHERE id = %s", $video_id );
		$row = $wpdb->get_row( $query );

		if ( $row ) {
			if ( ! empty( $row->thumbnails ) ) {
				$row->thumbnails = unserialize( $row->thumbnails );
			}

			wp_cache_set( $cache_key, $row );
		}		
	}

	return $row;
}

/**
 * Dump our plugin transients.
 *
 * @since 2.1.0
 */
function ayg_delete_cache() {
	delete_option( 'ayg_gallery_page_ids' );
	
	// Get the current list of transients
	$transient_keys = get_option( 'ayg_transient_keys', array() );

	// For each key, delete that transient
	foreach ( $transient_keys as $key ) {
		delete_transient( $key );
	}
	
	// Reset our DB value
	update_option( 'ayg_transient_keys', array() );
}

/** 
 * Get current address bar URL.
 *
 * @since  2.1.0
 * @return string Current Page URL.
 */
function ayg_get_current_url() {
    global $wp;
	$current_url = home_url( add_query_arg( array(), $wp->request ) );
	
    return $current_url;	
}

/**
 * Get default plugin settings.
 *
 * @since  1.6.4
 * @return array $defaults Array of plugin settings.
 */
function ayg_get_default_settings() {
	$defaults = array(
		'ayg_general_settings' => array(
			'api_key'          => '',
			'development_mode' => 1,
			'lazyload'         => 0
		),
		'ayg_gallery_settings' => array(
			'theme'                 => 'classic',
			'columns'               => 3,
			'per_page'              => 12,
			'thumb_ratio'           => 56.25,
			'thumb_title'           => 1,
			'thumb_title_length'    => 0,
			'thumb_excerpt'         => 1,
			'thumb_excerpt_length'  => 75,
			'pagination'            => 1,
			'pagination_type'       => 'more',
			'more_button_label'     => __( 'Load More', 'automatic-youtube-gallery' ),
			'previous_button_label' => __( 'Previous', 'automatic-youtube-gallery' ),
			'next_button_label'     => __( 'Next', 'automatic-youtube-gallery' ),
			'scroll_top_offset'     => 10
		),
		'ayg_player_settings' => array(
			'player_type'           => 'youtube',
			'player_color'          => '#00b3ff',
			'player_width'          => '',
			'player_ratio'          => 56.25,
			'player_title'          => 1,
			'player_description'    => 1,
			'autoplay'              => 0,
			'autoadvance'           => 1,
			'loop'                  => 0,
			'muted'                 => 0,
			'controls'              => 1,
			'modestbranding'        => 1,
			'cc_load_policy'        => 0,
			'iv_load_policy'        => 0,
			'hl'                    => '',
			'cc_lang_pref'          => '',
			'privacy_enhanced_mode' => 0,
			'origin'                => 0
		),
		'ayg_livestream_settings' => array(
			'fallback_message' => __( 'Sorry, but the channel is not currently streaming live content. Please check back later.', 'automatic-youtube-gallery' )
		),
		'ayg_privacy_settings' => array(			
			'cookie_consent'  => 0,
			'consent_message' => __( 'Please accept YouTube cookies to play this video. By accepting you will be accessing content from YouTube, a service provided by an external third party.', 'automatic-youtube-gallery' ),
			'button_label'    => __( 'Accept', 'automatic-youtube-gallery' )
		)
	);

	return $defaults;
}

/**
 * Get editor fields.
 *
 * @since  1.0.0
 * @return array Array of fields.
 */
function ayg_get_editor_fields() {	
	$fields = array(
		'source' => array(
			'label'  => __( 'General', 'automatic-youtube-gallery' ),
			'fields' => array(
				array(
					'name'              => 'type',
					'label'             => __( 'Source Type', 'automatic-youtube-gallery' ),
					'description'       => '',
					'type'              => 'select',
					'options'           => array(
						'playlist'   => __( 'Playlist', 'automatic-youtube-gallery' ),
						'channel'    => __( 'Channel', 'automatic-youtube-gallery' ),
						'username'   => __( 'Username', 'automatic-youtube-gallery' ),
						'search'     => __( 'Search Keywords', 'automatic-youtube-gallery' ),
						'video'      => __( 'Single Video', 'automatic-youtube-gallery' ),
						'livestream' => __( 'Livestream', 'automatic-youtube-gallery' ),						
						'videos'     => __( 'Custom Videos List', 'automatic-youtube-gallery' )
					),
					'value'             => 'playlist',
					'sanitize_callback' => 'sanitize_key'
				),
				array(
					'name'              => 'playlist',
					'label'             => __( 'YouTube Playlist ID (or) URL', 'automatic-youtube-gallery' ),					
					'description'       => sprintf( '%s: https://www.youtube.com/playlist?list=XXXXXXXXXX', __( 'Example', 'automatic-youtube-gallery' ) ),
					'type'              => 'url',
					'value'             => '',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
					'name'              => 'channel',
					'label'             => __( 'YouTube Channel ID (or) a YouTube Video URL from the Channel', 'automatic-youtube-gallery' ),
					'description'       => sprintf( '%s: https://www.youtube.com/channel/XXXXXXXXXX', __( 'Example', 'automatic-youtube-gallery' ) ),
					'type'              => 'url',
					'value'             => '',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
					'name'              => 'username',
					'label'             => __( 'YouTube Account Username', 'automatic-youtube-gallery' ),
					'description'       => sprintf( '%s: SanRosh', __( 'Example', 'automatic-youtube-gallery' ) ),
					'type'              => 'text',
					'value'             => '',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
					'name'              => 'search',
					'label'             => __( 'Search Keywords', 'automatic-youtube-gallery' ),
					'description'       => sprintf( '%s: Cartoon (space:AND , -:NOT , |:OR)', __( 'Example', 'automatic-youtube-gallery' ) ),
					'type'              => 'text',
					'value'             => '',
					'sanitize_callback' => 'sanitize_text_field'
				),
				array(
					'name'              => 'video',
					'label'             => __( 'YouTube Video ID (or) URL', 'automatic-youtube-gallery' ),
					'description'       => sprintf( '%s: https://www.youtube.com/watch?v=XXXXXXXXXX', __( 'Example', 'automatic-youtube-gallery' ) ),
					'type'              => 'url',
					'value'             => '',
					'sanitize_callback' => 'sanitize_text_field'
				),				
				array(
					'name'              => 'videos',
					'label'             => __( 'YouTube Video IDs (or) URLs', 'automatic-youtube-gallery' ),
					'description'       => sprintf( '%s: https://www.youtube.com/watch?v=XXXXXXXXXX', __( 'Example', 'automatic-youtube-gallery' ) ),
					'type'              => 'textarea',					
					'placeholder'       => __( 'Enter one video per line', 'automatic-youtube-gallery' ),
					'value'             => '',
					'sanitize_callback' => 'sanitize_text_field'
				),				
				array(
					'name'              => 'order',
					'label'             => __( 'Order Videos by', 'automatic-youtube-gallery' ),
					'description'       => '',
					'type'              => 'select',
					'options' => array(
						'date'      => __( 'Date', 'automatic-youtube-gallery' ),
						'rating'    => __( 'Rating', 'automatic-youtube-gallery' ),
						'relevance' => __( 'Relevance', 'automatic-youtube-gallery' ),
						'title'     => __( 'Title', 'automatic-youtube-gallery' ),
						'viewCount' => __( 'View Count', 'automatic-youtube-gallery' )
					),
					'value'             => 'relevance',
					'sanitize_callback' => 'sanitize_key'
				),
				array(
					'name'              => 'limit',
					'label'             => __( 'Number of Videos', 'automatic-youtube-gallery' ),					
					'description'       => __( 'Specifies the maximum number of videos that will appear in this gallery. Set to 0 for the maximum amount (500).', 'automatic-youtube-gallery' ),
					'type'              => 'number',					
					'min'               => 0,
					'max'               => 500,
					'value'             => 0,
					'sanitize_callback' => 'intval'
				),
				array(
					'name'              => 'cache',
					'label'             => __( 'Cache Duration', 'automatic-youtube-gallery' ),
					'description'       => __( 'Specifies how frequently we should check your YouTube source for new videos/updates.', 'automatic-youtube-gallery' ),
					'type'              => 'select',
					'options' => array(
						'900'     => __( '15 Minutes', 'automatic-youtube-gallery' ),
						'1800'    => __( '30 Minutes', 'automatic-youtube-gallery' ),
						'3600'    => __( '1 Hour', 'automatic-youtube-gallery' ),
						'86400'   => __( '1 Day', 'automatic-youtube-gallery' ),
						'604800'  => __( '1 Week', 'automatic-youtube-gallery' ),
						'2419200' => __( '1 Month', 'automatic-youtube-gallery' )
					),
					'value'             => 86400,
					'sanitize_callback' => 'intval'
				)
			)			
		),
		'gallery' => array(
			'label'  => __( 'Gallery (optional)', 'automatic-youtube-gallery' ),
			'fields' => ayg_get_gallery_settings_fields()
		),
		'player' => array(
			'label'  => __( 'Player (optional)', 'automatic-youtube-gallery' ),
			'fields' => ayg_get_player_settings_fields()
		)
	);

	return apply_filters( 'ayg_editor_fields', $fields );
}

/**
 * Get gallery settings fields.
 *
 * @since  1.0.0
 * @return array $fields Array of fields.
 */
function ayg_get_gallery_settings_fields() {
	$gallery_settings = get_option( 'ayg_gallery_settings' );

	$fields = array(
		array(
			'name'              => 'theme',
			'label'             => __( 'Select Theme (Layout)', 'automatic-youtube-gallery' ),
			'description'       => ( ayg_fs()->is_not_paying() ? sprintf( __( '<a href="%s">Upgrade Pro</a> for more themes (Popup, Inline, Slider, Playlist).', 'automatic-youtube-gallery' ), esc_url( ayg_fs()->get_upgrade_url() ) ) : '' ),
			'type'              => 'select',
			'options'           => array( 
				'classic' => __( 'Classic', 'automatic-youtube-gallery' )
			),
			'value'             => $gallery_settings['theme'],
			'sanitize_callback' => 'sanitize_key'
		),		
		array(
			'name'              => 'columns',
			'label'             => __( 'Columns', 'automatic-youtube-gallery' ),
			'description'       => __( 'Enter the number of columns you like to have in the gallery. Maximum of 12.', 'automatic-youtube-gallery' ),			
			'type'              => 'number',
			'min'               => 0,
			'max'               => 12,
			'value'             => $gallery_settings['columns'],
			'sanitize_callback' => 'intval'
		),
		array(
			'name'              => 'per_page',
			'label'             => __( 'Videos per Page', 'automatic-youtube-gallery' ),
			'description'       => __( 'Enter the number of videos to show per page. Maximum of 50.', 'automatic-youtube-gallery' ),			
			'type'              => 'number',
			'min'               => 0,
			'max'               => 50,
			'value'             => $gallery_settings['per_page'],
			'sanitize_callback' => 'intval'
		),
		array(
			'name'              => 'thumb_ratio',
			'label'             => __( 'Image Height (Ratio)', 'automatic-youtube-gallery' ),
			'description'       => __( 'Select the ratio value used to calculate the image height in the gallery thumbnails.', 'automatic-youtube-gallery' ),			
			'type'              => 'radio',
			'options'           => array(
				'56.25' => '16:9',
				'75'    => '4:3'				
			),
			'value'             => $gallery_settings['thumb_ratio'],
			'sanitize_callback' => 'floatval'
		),
		array(
			'name'              => 'thumb_title',
			'label'             => __( 'Show Video Title', 'automatic-youtube-gallery' ),			
			'description'       => __( 'Check this option to show the video title in each gallery item.', 'automatic-youtube-gallery' ),
			'type'              => 'checkbox',
			'value'             => $gallery_settings['thumb_title'],
			'sanitize_callback' => 'intval'
		),
		array(
			'name'              => 'thumb_title_length',
			'label'             => __( 'Video Title Length', 'automatic-youtube-gallery' ),			
			'description'       => __( 'Enter the number of characters you like to show in the title. Set 0 to show the whole title.', 'automatic-youtube-gallery' ),
			'type'              => 'number',
			'min'               => 0,
			'max'               => 500,
			'value'             => $gallery_settings['thumb_title_length'],
			'sanitize_callback' => 'intval'
		),
		array(
			'name'              => 'thumb_excerpt',
			'label'             => __( 'Show Video Excerpt (Short Description)', 'automatic-youtube-gallery' ),			
			'description'       => __( 'Check this option to show the short description of a video in each gallery item.', 'automatic-youtube-gallery' ),
			'type'              => 'checkbox',
			'value'             => $gallery_settings['thumb_excerpt'],
			'sanitize_callback' => 'intval'
		),
		array(
			'name'              => 'thumb_excerpt_length',
			'label'             => __( 'Video Excerpt Length', 'automatic-youtube-gallery' ),			
			'description'       => __( 'Enter the number of characters you like to have in the video excerpt. Set 0 to show the whole description.', 'automatic-youtube-gallery' ),
			'type'              => 'number',
			'min'               => 0,
			'max'               => 500,
			'value'             => $gallery_settings['thumb_excerpt_length'],
			'sanitize_callback' => 'intval'
		),
		array(
			'name'              => 'pagination',
			'label'             => __( 'Pagination', 'automatic-youtube-gallery' ),			
			'description'       => __( 'Check this option to show the pagination.', 'automatic-youtube-gallery' ),
			'type'              => 'checkbox',
			'value'             => $gallery_settings['pagination'],
			'sanitize_callback' => 'intval'
		),
		array(
			'name'              => 'pagination_type',
			'label'             => __( 'Pagination Type', 'automatic-youtube-gallery' ),			
			'type'              => 'select',
			'options'           => array(
				'more'  => __( 'More Button', 'automatic-youtube-gallery' ),
				'pager' => __( 'Pager', 'automatic-youtube-gallery' )
						
			),
			'value'             => $gallery_settings['pagination_type'],
			'sanitize_callback' => 'sanitize_key'
		),
		array(
			'name'              => 'more_button_label',
			'label'             => __( 'More Button Label', 'automatic-youtube-gallery' ),
			'description'       => __( 'Enter the more button label text.', 'automatic-youtube-gallery' ),
			'type'              => 'text',
			'value'             => isset( $gallery_settings['more_button_label'] ) ? $gallery_settings['more_button_label'] : __( 'Load More', 'automatic-youtube-gallery' ),
			'sanitize_callback' => 'sanitize_text_field'
		),
		array(
			'name'              => 'previous_button_label',
			'label'             => __( 'Previous Button Label', 'automatic-youtube-gallery' ),
			'description'       => __( 'Enter the previous button label text.', 'automatic-youtube-gallery' ),
			'type'              => 'text',
			'value'             => isset( $gallery_settings['previous_button_label'] ) ? $gallery_settings['previous_button_label'] : __( 'Previous', 'automatic-youtube-gallery' ),
			'sanitize_callback' => 'sanitize_text_field'
		),
		array(
			'name'              => 'next_button_label',
			'label'             => __( 'Next Button Label', 'automatic-youtube-gallery' ),
			'description'       => __( 'Enter the next button label text.', 'automatic-youtube-gallery' ),
			'type'              => 'text',
			'value'             => isset( $gallery_settings['next_button_label'] ) ? $gallery_settings['next_button_label'] : __( 'Next', 'automatic-youtube-gallery' ),
			'sanitize_callback' => 'sanitize_text_field'
		)
	);

	return apply_filters( 'ayg_gallery_settings_fields', $fields );
}

/**
 * Get video description to show on top of the player.
 *
 * @since  1.0.0
 * @param  stdClass $video       YouTube video object.
 * @param  int      $words_count Number of words to show by default.
 * @return string                Video description.
 */
function ayg_get_player_description( $video, $words_count = 30 ) {
	$description = $video->description;

	$words_array = explode( ' ', strip_tags( $description ) );	
	if ( count( $words_array ) > $words_count ) {
		$words_array[ $words_count ] = '<span class="ayg-player-description-dots">...</span></span><span class="ayg-player-description-more">' . $words_array[ $words_count ];

		$description  = '<span class="ayg-player-description-less">' . implode( ' ', $words_array ) . '</span>';
		$description .= '<a href="#" class="ayg-player-description-toggle-btn">[+] ' . __( 'Show More', 'automatic-youtube-gallery' ) . '</a>';
	}

	$description = nl2br( $description );
	$description = make_clickable( $description );

	return apply_filters( 'ayg_player_description', $description, $video, $words_count );	
}

/**
 * Get player settings fields.
 *
 * @since  1.0.0
 * @return array $fields Array of fields.
 */
function ayg_get_player_settings_fields() {
	$player_settings = get_option( 'ayg_player_settings' );

	$fields = array(
		array(
			'name'              => 'player_width',
			'label'             => __( 'Player Width', 'automatic-youtube-gallery' ),
			'description'       => __( 'In pixels. Maximum width of the player. Leave this field empty to scale 100% of its enclosing container/html element.', 'automatic-youtube-gallery' ),
			'type'              => 'text',
			'value'             => isset( $player_settings['player_width'] ) ? $player_settings['player_width'] : '',
			'sanitize_callback' => 'ayg_sanitize_int'
		),
		array(
			'name'              => 'player_ratio',
			'label'             => __( 'Player Height (Ratio)', 'automatic-youtube-gallery' ),	
			'description'       => __( 'Select the ratio value used to calculate the player height.', 'automatic-youtube-gallery' ),		
			'type'              => 'radio',
			'options'           => array(
				'56.25' => '16:9',
				'75'    => '4:3'				
			),
			'value'             => $player_settings['player_ratio'],
			'sanitize_callback' => 'floatval'
		),
		array(
			'name'              => 'player_title',
			'label'             => __( 'Show Video Title', 'automatic-youtube-gallery' ),			
			'description'       => __( 'Check this option to show the current playing video title on the bottom of the player.', 'automatic-youtube-gallery' ),
			'type'              => 'checkbox',
			'value'             => $player_settings['player_title'],
			'sanitize_callback' => 'intval'
		),
		array(
			'name'              => 'player_description',
			'label'             => __( 'Show Video Description', 'automatic-youtube-gallery' ),			
			'description'       => __( 'Check this option to show the current playing video description on the bottom of the player.', 'automatic-youtube-gallery' ),
			'type'              => 'checkbox',
			'value'             => $player_settings['player_description'],
			'sanitize_callback' => 'intval'
		),
		array(
			'name'              => 'autoplay',
			'label'             => __( 'Autoplay', 'automatic-youtube-gallery' ),			
			'description'       => __( 'Specifies whether the initial video will automatically start to play when the player loads.', 'automatic-youtube-gallery' ),
			'type'              => 'checkbox',
			'value'             => $player_settings['autoplay'],
			'sanitize_callback' => 'intval'
		),
		array(
			'name'              => 'autoadvance',
			'label'             => __( 'Autoplay Next Video', 'automatic-youtube-gallery' ),			
			'description'       => __( 'Specifies whether to play the next video in the list automatically after previous one end.', 'automatic-youtube-gallery' ),
			'type'              => 'checkbox',
			'value'             => $player_settings['autoadvance'],
			'sanitize_callback' => 'intval'
		),
		array(
			'name'              => 'loop',
			'label'             => __( 'Loop', 'automatic-youtube-gallery' ),			
			'description'       => __( 'In the case of a single video player, plays the initial video again and again. In the case of a gallery, plays the entire list in the gallery and then starts again at the first video.', 'automatic-youtube-gallery' ),
			'type'              => 'checkbox',
			'value'             => $player_settings['loop'],
			'sanitize_callback' => 'intval'
		),	
		array(
			'name'              => 'muted',
			'label'             => __( 'Muted', 'automatic-youtube-gallery' ),			
			'description'       => __( 'Check this option to turn OFF the audio output of the video by default.', 'automatic-youtube-gallery' ),
			'type'              => 'checkbox',
			'value'             => isset( $player_settings['muted'] ) ? $player_settings['muted'] : 0,
			'sanitize_callback' => 'intval'
		),	
		array(
			'name'              => 'controls',
			'label'             => __( 'Show Player Controls', 'automatic-youtube-gallery' ),			
			'description'       => __( 'Uncheck this option to hide the video player controls.', 'automatic-youtube-gallery' ),
			'type'              => 'checkbox',
			'value'             => $player_settings['controls'],
			'sanitize_callback' => 'intval'
		),
		array(
			'name'              => 'modestbranding',
			'label'             => __( 'Hide YouTube Logo', 'automatic-youtube-gallery' ),			
			'description'       => __( "Lets you prevent the YouTube logo from displaying in the control bar. Note that a small YouTube text label will still display in the upper-right corner of a paused video when the user's mouse pointer hovers over the player.", 'automatic-youtube-gallery' ),
			'type'              => 'checkbox',
			'value'             => $player_settings['modestbranding'],
			'sanitize_callback' => 'intval'
		),
		array(
			'name'              => 'cc_load_policy',
			'label'             => __( 'Force Closed Captions', 'automatic-youtube-gallery' ),			
			'description'       => __( 'Show captions by default, even if the user has turned captions off. The default behavior is based on user preference.', 'automatic-youtube-gallery' ),
			'type'              => 'checkbox',
			'value'             => $player_settings['cc_load_policy'],
			'sanitize_callback' => 'intval'
		),		
		array(
			'name'              => 'iv_load_policy',
			'label'             => __( 'Show Annotations', 'automatic-youtube-gallery' ),			
			'description'       => __( 'Choose whether to show annotations or not.', 'automatic-youtube-gallery' ),
			'type'              => 'checkbox',
			'value'             => $player_settings['iv_load_policy'],
			'sanitize_callback' => 'intval'
		),
		array(
			'name'              => 'hl',
			'label'             => __( 'Player Language', 'automatic-youtube-gallery' ),			
			'description'       => sprintf( 
				__( 'Specifies the player\'s interface language. Set the field\'s value to an <a href="%s" target="_blank">ISO 639-1 two-letter language code.</a>', 'automatic-youtube-gallery' ),
				'http://www.loc.gov/standards/iso639-2/php/code_list.php'
			),
			'type'              => 'text',
			'value'             => $player_settings['hl'],
			'sanitize_callback' => 'sanitize_text_field'
		),
		array(
			'name'              => 'cc_lang_pref',
			'label'             => __( 'Default Captions Language', 'automatic-youtube-gallery' ),			
			'description'       => sprintf( 
				__( 'Specifies the default language that the player will use to display captions. Set the field\'s value to an <a href="%s" target="_blank">ISO 639-1 two-letter language code.</a>', 'automatic-youtube-gallery' ),
				'http://www.loc.gov/standards/iso639-2/php/code_list.php'
			),
			'type'              => 'text',
			'value'             => $player_settings['cc_lang_pref'],
			'sanitize_callback' => 'sanitize_text_field'
		)
	);

	return $fields;
}


/**
 * Get a single video page URL.
 *
 * @since  2.1.0
 * @param  stdClass $video YouTube video object.
 * @param  array    $args  An array of gallery options.
 * @return string          Single video URL.
 */
function ayg_get_single_video_url( $video, $attributes ) {
	return apply_filters( 'ayg_single_video_url', '', $video, $attributes );
}

/**
 * Get filtered php template file path.
 *
 * @since  1.0.0
 * @param  array  $template PHP file path.
 * @param  string $theme    Automatic YouTube Gallery Theme.
 * @return string           Filtered file path.
 */
function ayg_get_template( $template, $theme = '' ) {
	return apply_filters( 'ayg_load_template', $template, $theme );
}

/**
 * Get unique ID.
 *
 * @since  1.0.0
 * @return string Unique ID.
 */
function ayg_get_uniqid() {
	global $ayg_uniqid;

	if ( ! $ayg_uniqid ) {
		$ayg_uniqid = 0;
	}

	return uniqid() . ++$ayg_uniqid;
}

/**
 * Get YouTube domain.
 *
 * @since  2.3.0
 * @return string YouTube embed domain.
 */
function ayg_get_youtube_domain() {
	$player_settings = get_option( 'ayg_player_settings' );

	$domain = 'https://www.youtube.com';
	if ( isset( $player_settings['privacy_enhanced_mode'] ) && ! empty( $player_settings['privacy_enhanced_mode'] ) ) {
		$domain = 'https://www.youtube-nocookie.com';
	}

	return $domain;
}

/**
 * Get the YouTube embed URL.
 *
 * @since  2.5.0
 * @param  string $video_id   YouTube video ID.
 * @param  array  $attributes Array of user attributes.
 * @return string             Player embed URL.
 */
function ayg_get_youtube_embed_url( $video_id, $attributes = array() ) {
	$player_settings = get_option( 'ayg_player_settings' );

	$player_website = 'https://www.youtube.com';
	if ( isset( $player_settings['privacy_enhanced_mode'] ) && ! empty( $player_settings['privacy_enhanced_mode'] ) ) {
		$player_website = 'https://www.youtube-nocookie.com';
	}

	if ( empty( $video_id ) ) {
		return '';
	}

	$url = $player_website . '/embed/' . $video_id . '?enablejsapi=1&playsinline=1&rel=0';

	if ( isset( $player_settings['origin'] ) && ! empty( $player_settings['origin'] ) ) {
		$site_url_parts = parse_url( site_url() );
		$origin = $site_url_parts['scheme'] . '://' . $site_url_parts['host'];

		$url = add_query_arg( 'origin', $origin, $url );
	}

	if ( ! is_array( $attributes ) ) {
		$attributes = (array) $attributes;
	}

	$autoplay = isset( $attributes['autoplay'] ) ? (int) $attributes['autoplay'] : 0;
	if ( 1 == $autoplay ) {
		$url = add_query_arg( 'autoplay', 1, $url );
	}

	$loop = isset( $attributes['loop'] ) ? (int) $attributes['loop'] : 0;
	if ( 1 == $loop ) {
		$url = add_query_arg( 'playlist', $video_id, $url );
		$url = add_query_arg( 'loop', 1, $url );
	}

	$muted = isset( $attributes['muted'] ) ? (int) $attributes['muted'] : 0;
	if ( 1 == $muted ) {
		$url = add_query_arg( 'mute', 1, $url );
	}

	$controls = isset( $attributes['controls'] ) ? (int) $attributes['controls'] : 1;
	if ( 0 == $controls ) {
		$url = add_query_arg( 'controls', 0, $url );
	}

	$modestbranding = isset( $attributes['modestbranding'] ) ? (int) $attributes['modestbranding'] : 0;
	if ( 1 == $modestbranding ) {
		$url = add_query_arg( 'modestbranding', 1, $url );
	}

	$cc_load_policy = isset( $attributes['cc_load_policy'] ) ? (int) $attributes['cc_load_policy'] : 0;
	if ( 1 == $cc_load_policy ) {
		$url = add_query_arg( 'cc_load_policy', 1, $url );
	}

	$iv_load_policy = isset( $attributes['iv_load_policy'] ) ? (int) $attributes['iv_load_policy'] : 0;
	if ( 0 == $iv_load_policy ) {
		$url = add_query_arg( 'iv_load_policy', 3, $url );
	}

	if ( isset( $attributes['hl'] ) && ! empty( $attributes['hl'] ) ) {
		$url = add_query_arg( 'hl', sanitize_text_field( $attributes['hl'] ), $url );
	}

	if ( isset( $attributes['cc_lang_pref'] ) && ! empty( $attributes['cc_lang_pref'] ) ) {
		$url = add_query_arg( 'cc_lang_pref', sanitize_text_field( $attributes['cc_lang_pref'] ), $url );
	}

	return apply_filters( 'ayg_youtube_embed_url', $url, $video_id, $attributes );
}

/**
 * Inserts a new associative array after another associative array key.
 *
 * @since  2.1.0
 * @param  string $key       The associative array key to insert after.
 * @param  array  $array     An array to insert in to.
 * @param  array  $new_array An array to insert.
 * @return array             Updated array.
 */
function ayg_insert_array_after( $key, $array, $new_array ) {
	if ( array_key_exists( $key, $array ) ) {
    	$new = array();

    	foreach ( $array as $k => $value ) {
      		$new[ $k ] = $value;

      		if ( $k === $key ) {
				foreach ( $new_array as $new_key => $new_value ) {
        			$new[ $new_key ] = $new_value;
				}
      		}
    	}
		
    	return $new;
  	}
		
  	return $array;  
}

/**
 * Sanitize the integer inputs, accepts empty values.
 *
 * @since  1.0.0
 * @param  string|int $value Input value.
 * @return string|int        Sanitized value.
 */
function ayg_sanitize_int( $value ) {
	$value = intval( $value );
	return ( 0 == $value ) ? '' : $value;	
}

/**
 * Trims text to a certain number of characters.
 *
 * @since  2.0.0
 * @param  string $text           Text to trim.
 * @param  int    $num_characters Number of characters.
 * @param  string $append         String to append to the end of the excerpt.
 * @return string                 Trimmed text.
 */
function ayg_trim_words( $text, $num_characters, $append = '...' ) {
	$num_characters++;

	$original_text = $text;
	$text = ( $num_characters > 1 ) ? wp_strip_all_tags( $original_text, true ) : nl2br( $original_text );

	if ( $num_characters > 1 && mb_strlen( $text ) > $num_characters ) {
		$subex = mb_substr( $text, 0, $num_characters - 5 );
		$exwords = explode( ' ', $subex );
		$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );

		if ( $excut < 0 ) {
			$text = mb_substr( $subex, 0, $excut );
		} else {
			$text = $subex;
		}

		$text .= $append;
	}

	return apply_filters( 'ayg_trim_words', $text, $original_text, $num_characters, $append );	
}

/**
 * Gallery HTML output.
 *
 * @since 1.0.0
 * @param array $video      YouTube video object.
 * @param array $attributes Array of user attributes.
 */
function the_ayg_gallery_thumbnail( $video, $attributes ) {
	include ayg_get_template( AYG_DIR . 'public/templates/thumbnail.php' );
}

/**
 * Pagination HTML output.
 *
 * @since 1.0.0
 * @param array $attributes Array of user attributes.
 */
function the_ayg_pagination( $attributes ) {
	if ( ! empty( $attributes['pagination'] ) ) {
		include ayg_get_template( AYG_DIR . 'public/templates/pagination.php' );
	}	
}

/**
 * Gallery HTML output.
 *
 * @since 2.5.0
 * @param array $video      YouTube video object.
 * @param array $attributes Array of user attributes.
 */
function the_ayg_player( $video, $attributes ) {
	include ayg_get_template( AYG_DIR . 'public/templates/player.php' );
}