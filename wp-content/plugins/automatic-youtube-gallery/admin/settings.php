<?php

/**
 * Plugin Settings.
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
 * AYG_Admin_Settings class.
 *
 * @since 1.0.0
 */
class AYG_Admin_Settings {

	/**
     * Settings tabs array.
     *
	 * @since  1.0.0
	 * @access protected
     * @var    array
     */
	protected $tabs = array();
	
	/**
     * Settings sections array.
     *
	 * @since  1.0.0
	 * @access protected
     * @var    array
     */
	protected $sections = array();
	
	/**
     * Settings fields array
     *
	 * @since  1.0.0
	 * @access protected
     * @var    array
     */
	protected $fields = array();
	
	/**
	 * Add "Settings" menu.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {	
        add_submenu_page(
			'automatic-youtube-gallery',
			__( 'Settings', 'automatic-youtube-gallery' ),
			__( 'Settings', 'automatic-youtube-gallery' ),
			'manage_options',
			'automatic-youtube-gallery-settings',
			array( $this, 'display_settings_form' )
		);
	}

	/**
	 * Display settings form.
	 *
	 * @since 1.0.0
	 */
	public function display_settings_form() {
        $gallery_settings = get_option( 'ayg_gallery_settings' );
        $player_settings  = get_option( 'ayg_player_settings' );
	
        $active_tab      = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $this->tabs ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
        $active_theme    = $gallery_settings['theme'];
        $pagination_type = $gallery_settings['pagination_type'];
        $player_type     = isset( $player_settings['player_type'] ) ? $player_settings['player_type'] : 'youtube';

		require_once AYG_DIR . 'admin/templates/settings.php';		
	}

	/**
	 * Initiate settings.
	 *
	 * @since 1.0.0
	 */
	public function admin_init() {
		$this->tabs     = $this->get_tabs();
        $this->sections = $this->get_sections();
        $this->fields   = $this->get_fields();
		
        // Initialize settings
		$this->initialize_settings();		
	}

	/**
     * Get settings tabs.
     *
	 * @since  1.0.0
     * @return array $tabs Setting tabs array.
     */
    public function get_tabs() {	
		$tabs = array(
            'general'    => __( 'General', 'automatic-youtube-gallery' ),
            'gallery'    => __( 'Gallery', 'automatic-youtube-gallery' ),
            'player'     => __( 'Player', 'automatic-youtube-gallery' ),
            'livestream' => __( 'Livestream', 'automatic-youtube-gallery' ),
            'privacy'    => __( 'GDPR Compliance', 'automatic-youtube-gallery' )
		);
		
		return apply_filters( 'ayg_settings_tabs', $tabs );	
	}

	/**
     * Get settings sections.
     *
	 * @since  1.0.0
     * @return array $sections Setting sections array.
     */
    public function get_sections() {		
		$sections = array(
			array(
                'id'          => 'ayg_general_settings',
                'title'       => __( 'General Settings', 'automatic-youtube-gallery' ),
                'description' => '',
                'tab'         => 'general'
            ),
            array(
                'id'          => 'ayg_gallery_settings',
                'title'       => __( 'Gallery Settings', 'automatic-youtube-gallery' ),
                'description' => '',
				'tab'         => 'gallery'
            ),
            array(
                'id'          => 'ayg_player_settings',
                'title'       => __( 'Player Settings', 'automatic-youtube-gallery' ),
                'description' => '',
				'tab'         => 'player'
            ),
            array(
                'id'          => 'ayg_livestream_settings',
                'title'       => __( 'Livestream Settings', 'automatic-youtube-gallery' ),
                'description' => '',
				'tab'         => 'livestream'
            ),
            array(
                'id'          => 'ayg_privacy_settings',
                'title'       => __( 'GDPR Compliance', 'automatic-youtube-gallery' ),
                'description' => __( 'These options will help with privacy restrictions such as GDPR and the EU Cookie Law.', 'automatic-youtube-gallery' ),
                'tab'         => 'privacy'
            ),				
        );
		
		return apply_filters( 'ayg_settings_sections', $sections );		
	}

	/**
     * Get settings fields.
     *
	 * @since     1.0.0
     * @return    array    $fields    Setting fields array.
     */
    public function get_fields() {
        // General Settings
        $fields['ayg_general_settings']	= array(
            array(
                'name'              => 'api_key',
                'label'             => __( 'Youtube API Key', 'automatic-youtube-gallery' ),
                'description'       => sprintf( 
                    __( 'Follow <a href="%s" target="_blank">this guide</a> to get your own API key.', 'automatic-youtube-gallery' ),  
                    'https://plugins360.com/automatic-youtube-gallery/how-to-get-youtube-api-key/' 
                ),
                'type'              => 'text',
                'sanitize_callback' => 'sanitize_text_field'
            ),
            array(
                'name'              => 'development_mode',
                'label'             => __( 'Development Mode', 'automatic-youtube-gallery' ),
                'description'       => __( 'Does not cache API results when checked. We strongly recommend disabling this option when your site is live.', 'automatic-youtube-gallery' ),
                'type'              => 'checkbox',
                'sanitize_callback' => 'intval'
            ),
            array(
                'name'              => 'lazyload',
                'label'             => __( 'Lazyload Images / Videos', 'automatic-youtube-gallery' ),
                'description'       => __( 'Enable this option to lazy load images and videos added by the plugin to enhance page load speed and performance. If you experience any issues with content display, try disabling this option.', 'automatic-youtube-gallery' ),
                'type'              => 'checkbox',
                'sanitize_callback' => 'intval'
            )
        );

        // Gallery Settings
        $gallery_settings = ayg_get_gallery_settings_fields();

        $gallery_settings[] = array(
			'name'              => 'scroll_top_offset',
			'label'             => __( 'Page Scroll Top Offset', 'automatic-youtube-gallery' ),
			'description'       => __( 'Set the top offset in pixels for scrolling to the video player after a thumbnail is clicked.', 'automatic-youtube-gallery' ),
			'type'              => 'text',
			'sanitize_callback' => 'ayg_sanitize_int'
		);

        $fields['ayg_gallery_settings'] = $gallery_settings;

        // Player Settings
        $player_settings = array(
            array(
                'name'              => 'player_type',
                'label'             => __( 'Player Type', 'automatic-youtube-gallery' ),	
                'description'       => '',		
                'type'              => 'radio',
                'options'           => array(
                    'youtube' => __( 'Native YouTube Embed', 'automatic-youtube-gallery' ),
                    'custom'  => __( 'Custom Video Player', 'automatic-youtube-gallery' )			
                ),
                'sanitize_callback' => 'sanitize_text_field'
            ),
            array(
                'name'              => 'player_color',
                'label'             => __( 'Player Color', 'automatic-youtube-gallery' ),	
                'description'       => __( 'Set the theme color for your video player.', 'automatic-youtube-gallery' ),		
                'type'              => 'color',
                'sanitize_callback' => 'sanitize_text_field'
            )
        );

        $player_settings = array_merge( $player_settings, ayg_get_player_settings_fields() );

        $player_settings[] = array(
			'name'              => 'privacy_enhanced_mode',
			'label'             => __( 'Privacy Enhanced Mode', 'automatic-youtube-gallery' ),
			'description'       => __( "Prevent YouTube from leaving tracking cookies on your visitor's browsers unless they actually play the videos. Please uncheck this option if you see errors while testing your playlist embeds or watching your videos on mobile.", 'automatic-youtube-gallery' ),
			'type'              => 'checkbox',
			'sanitize_callback' => 'intval'
		);

        $player_settings[] = array(
			'name'              => 'origin',
			'label'             => __( 'Extra Player Security', 'automatic-youtube-gallery' ),
			'description'       => __( 'Add site origin information with each embed code as an extra security measure. In YouTube\'s own words, checking this option "protects against malicious third-party JavaScript being injected into your page and hijacking control of your YouTube player."', 'automatic-youtube-gallery' ),
			'type'              => 'checkbox',
			'sanitize_callback' => 'intval'
		);

        $fields['ayg_player_settings'] = $player_settings;

        // Livestream Settings
        $fields['ayg_livestream_settings'] = array(
            array(
                'name'              => 'fallback_message',
                'label'             => __( 'Fallback Message', 'automatic-youtube-gallery' ),
                'description'       => __( 'Enter your Custom HTML message that should be displayed when there is no video streaming live.', 'automatic-youtube-gallery' ),
                'type'              => 'wysiwyg',
                'sanitize_callback' => 'wp_kses_post'
            )
        );

        // Privacy Settings
        $fields['ayg_privacy_settings'] = array(                
            array(
                'name'              => 'cookie_consent',
                'label'             => __( 'Cookie Consent', 'automatic-youtube-gallery' ),
                'description'       => __( 'Ask for viewer consent to store YouTube cookies before showing videos.', 'automatic-youtube-gallery' ),
                'type'              => 'checkbox',
                'sanitize_callback' => 'intval'
            ),
            array(
                'name'              => 'consent_message',
                'label'             => __( 'Consent Message', 'automatic-youtube-gallery' ),
                'description'       => '',
                'type'              => 'wysiwyg',
                'sanitize_callback' => 'wp_kses_post'
            ),
            array(
                'name'              => 'button_label',
                'label'             => __( 'Button Label', 'automatic-youtube-gallery' ),
                'description'       => '',
                'type'              => 'text',
                'sanitize_callback' => 'sanitize_text_field'
            )
		);
		
		return apply_filters( 'ayg_settings_fields', $fields );		
	}

	/**
     * Initialize and registers the settings sections and fields to WordPress.
     *
     * @since 1.0.0
     */
    public function initialize_settings() {	
        // Register settings sections & fields
        foreach ( $this->sections as $section ) {
		
			$page_hook = "ayg_{$section['tab']}_settings";
			
			// Sections
            if ( false == get_option( $section['id'] ) ) {
                add_option( $section['id'] );
            }
			
            if ( isset( $section['description'] ) && ! empty( $section['description'] ) ) {
                $callback = array( $this, 'settings_section_callback' );
            } elseif ( isset( $section['callback'] ) ) {
                $callback = $section['callback'];
            } else {
                $callback = null;
            }
			
            add_settings_section( $section['id'], $section['title'], $callback, $page_hook );
			
			// Fields			
			$fields = $this->fields[ $section['id'] ];
			
			foreach ( $fields as $option ) {			
                $name     = $option['name'];
                $type     = isset( $option['type'] ) ? $option['type'] : 'text';
                $label    = isset( $option['label'] ) ? $option['label'] : '';
                $callback = isset( $option['callback'] ) ? $option['callback'] : array( $this, 'callback_' . $type );				
                $args     = array(
                    'id'                => $name,
                    'class'             => isset( $option['class'] ) ? $option['class'] : $name,
                    'label_for'         => "{$section['id']}[{$name}]",
                    'description'       => isset( $option['description'] ) ? $option['description'] : '',
                    'name'              => $label,
                    'section'           => $section['id'],
                    'size'              => isset( $option['size'] ) ? $option['size'] : null,
                    'options'           => isset( $option['options'] ) ? $option['options'] : '',
                    'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
                    'type'              => $type,
                    'placeholder'       => isset( $option['placeholder'] ) ? $option['placeholder'] : '',
                    'min'               => isset( $option['min'] ) ? $option['min'] : '',
                    'max'               => isset( $option['max'] ) ? $option['max'] : '',
                    'step'              => isset( $option['step'] ) ? $option['step'] : ''					
                );
				
                add_settings_field( "{$section['id']}[{$name}]", $label, $callback, $page_hook, $section['id'], $args );
            }
			
			// Creates our settings in the options table
        	register_setting( $page_hook, $section['id'], array( $this, 'sanitize_options' ) );			
        }		
    }
    
    /**
 	 * Displays a section description.
 	 *
	 * @since 1.0.0
	 * @param array $args Settings section args.
 	 */
	public function settings_section_callback( $args ) {
        foreach ( $this->sections as $section ) {
            if ( $section['id'] == $args['id'] ) {
                printf( '<div class="inside">%s</div>', wp_kses_post( $section['description'] ) ); 
                break;
            }
        }
    }
	
	/**
     * Displays a text field for a settings field.
     *
	 * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_text( $args ) {	
        $value       = esc_attr( $this->get_option( $args['id'], $args['section'], '' ) );
        $size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
        $type        = isset( $args['type'] ) ? $args['type'] : 'text';
        $placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
		
        $html        = sprintf( '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder );
        $html       .= $this->get_field_description( $args );
		
        echo $html;		
	}
	
	/**
     * Displays a url field for a settings field.
     *
	 * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_url( $args ) {
        $this->callback_text( $args );
	}
	
	/**
     * Displays a number field for a settings field.
     *
	 * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_number( $args ) {	
        $value       = esc_attr( $this->get_option( $args['id'], $args['section'], 0 ) );
        $size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
        $type        = isset( $args['type'] ) ? $args['type'] : 'number';
        $placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
        $min         = empty( $args['min'] ) ? '' : ' min="' . $args['min'] . '"';
        $max         = empty( $args['max'] ) ? '' : ' max="' . $args['max'] . '"';
        $step        = empty( $args['max'] ) ? '' : ' step="' . $args['step'] . '"';
		
        $html        = sprintf( '<input type="%1$s" class="%2$s-number" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder, $min, $max, $step );
        $html       .= $this->get_field_description( $args );
		
        echo $html;		
	}
	
	/**
     * Displays a checkbox for a settings field.
     *
	 * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_checkbox( $args ) {	
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], 0 ) );
		
        $html  = '<fieldset>';
        $html  .= sprintf( '<label for="%1$s[%2$s]">', $args['section'], $args['id'] );
        $html  .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="0" />', $args['section'], $args['id'] );
        $html  .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="1" %3$s />', $args['section'], $args['id'], checked( $value, 1, false ) );
        $html  .= sprintf( '%1$s</label>', $args['description'] );
        $html  .= '</fieldset>';
		
        echo $html;		
	}
	
	/**
     * Displays a multicheckbox for a settings field.
     *
     * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_multicheck( $args ) {	
        $value = $this->get_option( $args['id'], $args['section'], array() );
		
        $html  = '<fieldset>';
        $html .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id'] );
        foreach ( $args['options'] as $key => $label ) {
            $checked  = in_array( $key, $value ) ? 'checked="checked"' : '';
            $html    .= sprintf( '<label for="%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key );
            $html    .= sprintf( '<input type="checkbox" class="checkbox" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, $checked );
            $html    .= sprintf( '%1$s</label><br>',  $label );
        }
        $html .= $this->get_field_description( $args );
        $html .= '</fieldset>';
		
        echo $html;		
	}
	
	/**
     * Displays a radio button for a settings field.
     *
     * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_radio( $args ) {	
        $value = $this->get_option( $args['id'], $args['section'], '' );
		
        $html  = '<fieldset>';
        foreach ( $args['options'] as $key => $label ) {
            $html .= sprintf( '<label for="%1$s[%2$s][%3$s]">',  $args['section'], $args['id'], $key );
            $html .= sprintf( '<input type="radio" class="radio" id="%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $value, $key, false ) );
            $html .= sprintf( '%1$s</label><br>', $label );
        }
        $html .= $this->get_field_description( $args );
        $html .= '</fieldset>';
		
        echo $html;		
	}
	
	/**
     * Displays a selectbox for a settings field.
     *
     * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_select( $args ) {	
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], '' ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		
        $html  = sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );
        foreach ( $args['options'] as $key => $label ) {
            $html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
        }
        $html .= sprintf( '</select>' );
        $html .= $this->get_field_description( $args );
		
        echo $html;		
	}
	
	/**
     * Displays a textarea for a settings field.
     *
     * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_textarea( $args ) {	
        $value       = esc_textarea( $this->get_option( $args['id'], $args['section'], '' ) );
        $size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
        $placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="'.$args['placeholder'].'"';
		
        $html        = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%4$s>%5$s</textarea>', $size, $args['section'], $args['id'], $placeholder, $value );
        $html       .= $this->get_field_description( $args );
		
        echo $html;		
	}
	
	/**
     * Displays the html for a settings field.
     *
     * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_html( $args ) {
        echo $this->get_field_description( $args );
	}
	
	/**
     * Displays a rich text textarea for a settings field.
     *
     * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_wysiwyg( $args ) {	
        $value = $this->get_option( $args['id'], $args['section'], '' );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : '500px';
		
        echo '<div style="max-width: ' . $size . ';">';
        $editor_settings = array(
            'teeny'         => true,
            'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
            'textarea_rows' => 10
        );
        if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
            $editor_settings = array_merge( $editor_settings, $args['options'] );
        }
        wp_editor( $value, $args['section'] . '-' . $args['id'], $editor_settings );
        echo '</div>';
        echo $this->get_field_description( $args );		
	}
	
	/**
     * Displays a file upload field for a settings field.
     *
     * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_file( $args ) {	
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], '' ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
        $id    = $args['section'] . '[' . $args['id'] . ']';
        $label = isset( $args['options']['button_label'] ) ? $args['options']['button_label'] : __( 'Choose File', 'automatic-youtube-gallery' );
		
        $html  = sprintf( '<input type="text" class="%1$s-text ayg-settings-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
        $html .= '<input type="button" class="button ayg-settings-browse" value="' . $label . '" />';
        $html .= $this->get_field_description( $args );
		
        echo $html;		
	}
	
	/**
     * Displays a password field for a settings field.
     *
     * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_password( $args ) {	
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], '' ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		
        $html  = sprintf( '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
        $html .= $this->get_field_description( $args );
		
        echo $html;		
	}
	
	/**
     * Displays a color picker field for a settings field.
     *
     * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_color( $args ) {	
        $value = esc_attr( $this->get_option( $args['id'], $args['section'], '#ffffff' ) );
        $size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		
        $html  = sprintf( '<input type="text" class="%1$s-text ayg-color-picker" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, '#ffffff' );
        $html .= $this->get_field_description( $args );
		
        echo $html;		
	}
	
	/**
     * Displays a select box for creating the pages select box.
     *
     * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function callback_pages( $args ) {	
        $dropdown_args = array(
			'show_option_none'  => '-- ' . __( 'Select a page', 'automatic-youtube-gallery' ) . ' --',
			'option_none_value' => -1,
            'selected'          => esc_attr($this->get_option($args['id'], $args['section'], -1 ) ),
            'name'              => $args['section'] . '[' . $args['id'] . ']',
            'id'                => $args['section'] . '[' . $args['id'] . ']',
            'echo'              => 0			
        );
		
        $html  = wp_dropdown_pages( $dropdown_args );
		$html .= $this->get_field_description( $args );
		
        echo $html;		
	}
	
	/**
     * Get field description for display.
     *
	 * @since 1.0.0
     * @param array $args Settings field args.
     */
    public function get_field_description( $args ) {	
        if ( ! empty( $args['description'] ) ) {
            $description = sprintf( '<p class="description">%s</p>', $args['description'] );
        } else {
            $description = '';
        }
		
        return $description;		
	}
	
	/**
     * Sanitize callback for Settings API.
     *
	 * @since  1.0.0
     * @param  array $options The unsanitized collection of options.
     * @return                The collection of sanitized values.
     */
    public function sanitize_options( $options ) {	
        if ( ! $options ) {
            return $options;
        }
		
        foreach ( $options as $option_slug => $option_value ) {		
            $sanitize_callback = $this->get_sanitize_callback( $option_slug );
			
            // If callback is set, call it
            if ( $sanitize_callback ) {
                $options[ $option_slug ] = call_user_func( $sanitize_callback, $option_value );
                continue;
            }			
        }
		
        return $options;		
	}
	
	/**
     * Get sanitization callback for given option slug.
     *
	 * @since  1.0.0
     * @param  string $slug Option slug.
     * @return mixed        String or bool false.
     */
    public function get_sanitize_callback( $slug = '' ) {	
        if ( empty( $slug ) ) {
            return false;
        }
		
        // Iterate over registered fields and see if we can find proper callback
        foreach ( $this->fields as $section => $options ) {
            foreach ( $options as $option ) {
                if ( $option['name'] != $slug ) {
                    continue;
                }
				
                // Return the callback name
                return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
            }
        }
		
        return false;		
    }    

    /**
     * Get the value of a settings field
     *
	 * @since  1.0.0
     * @param  string $option  Settings field name.
     * @param  string $section The section name this field belongs to
     * @param  string $default Default text if it's not found.
     * @return string
     */
    public function get_option( $option, $section, $default = '' ) {	
        $options = get_option( $section );
		
        if ( ! empty( $options[ $option ] ) ) {
            return $options[ $option ];
        }
		
        return $default;		
    }
    
    /**
	 * Dump our plugin transients.
	 *
	 * @since 1.3.0
	 */
	public function ajax_callback_delete_cache() {
        check_ajax_referer( 'ayg_ajax_nonce', 'security' );

        if ( current_user_can( 'manage_options' ) ) {
            ayg_delete_cache();
        }
        
        wp_die();
    }

}
