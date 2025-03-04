<?php

function bravepop_enqueue_front_scripts() {
	if ( !is_admin() ) {
      wp_register_script( 'bravepop_front_js', BRAVEPOP_PLUGIN_PATH . 'assets/frontend/brave.js' ,'','',true);
      global $bravepop_settings;
      $customFonts = isset($bravepop_settings['fonts']) ? $bravepop_settings['fonts'] : array();
      $appSettings = isset($bravepop_settings['app_settings']) ? $bravepop_settings['app_settings'] : new stdClass();
      $disableGoogleFonts = isset($appSettings->disableFonts) ? wp_json_encode($appSettings->disableFonts) : 'false'; 

      $verbs = array(
         'loggedin' => is_user_logged_in() ? 'true' : 'false',
         'isadmin' => current_user_can('activate_plugins') ? 'true' : 'false',
         'referer' => wp_get_referer(),
         'security' => wp_create_nonce('brave-ajax-nonce'),
         'goalSecurity' => wp_create_nonce('brave-ajax-goal-nonce'),
         'couponSecurity' => wp_create_nonce('apply-coupon'),
         'cartURL' => function_exists('wc_get_cart_url') ? wc_get_cart_url() : '',
         'checkoutURL' => function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '',
         'ajaxURL' => esc_url(admin_url( 'admin-ajax.php' )),
         'field_required' => __( 'Required', 'bravepop' ),
         'no_html_allowed' => __( 'No Html Allowed', 'bravepop' ),
         'invalid_number' => __( 'Invalid Number', 'bravepop' ),
         'invalid_email' => __( 'Invalid Email', 'bravepop' ),
         'invalid_url' => __( 'Invalid URL', 'bravepop' ),
         'invalid_date' => __( 'Invalid Date', 'bravepop' ),
         'fname_required' => __( 'First Name is Required.', 'bravepop' ),
         'fname_required' => __( 'First Name is Required.', 'bravepop' ),
         'lname_required' => __( 'Last Name is Required.', 'bravepop' ),
         'username_required' => __( 'Username is Required.', 'bravepop' ),
         'email_required' => __( 'Email is Required.', 'bravepop' ),
         'email_invalid' => __( 'Invalid Email addresss.', 'bravepop' ),
         'pass_required' => __( 'Password is Required.', 'bravepop' ),
         'pass_short' => __( 'Password is too Short.', 'bravepop' ),
         'yes' => __( 'Yes', 'bravepop' ),
         'no' => __( 'No', 'bravepop' ),
         'login_error' => __( 'Something Went Wrong. Please contact the Site administrator.', 'bravepop' ),
         'pass_reset_success' => __( 'Please check your Email for the Password reset link.', 'bravepop' ),
         'customFonts'=>  $customFonts,
         'disableGoogleFonts' => $disableGoogleFonts,
      );
      wp_localize_script( 'bravepop_front_js', 'bravepop_global', $verbs );
      wp_enqueue_script('bravepop_front_js');

      //ENQEUE STYLE 
		wp_enqueue_style('bravepop_front_css',  BRAVEPOP_PLUGIN_PATH . 'assets/css/frontend.min.css');
	}
}
add_action('wp_footer', 'bravepop_enqueue_front_scripts');


add_action('wp_footer', 'bravepop_enqueue_tooltip');
function bravepop_enqueue_tooltip() { 
   print_r('<div id="bravepop_element_tooltip"></div><div id="bravepop_element_lightbox"><div id="bravepop_element_lightbox_close" onclick="brave_lightbox_close()"></div><div id="bravepop_element_lightbox_content"></div></div>');
}

add_action( 'wp_head', 'bravepop_popupjs_vars', 3 );
function bravepop_popupjs_vars() { 
   global $bravepop_settings;
   $emailValidator = isset($bravepop_settings['emailvalidator']->active) && $bravepop_settings['emailvalidator']->active !== 'disabled' ? true : false;
   print_r('<style type="text/css">.brave_popup{display:none}</style>');
   print_r('<script data-no-optimize="1"> var brave_popup_data = {}; var bravepop_emailValidation='.wp_json_encode($emailValidator).'; var brave_popup_videos = {};  var brave_popup_formData = {};var brave_popup_adminUser = '.wp_json_encode(is_user_logged_in() &&current_user_can('administrator') ? true : false).'; var brave_popup_pageInfo = '.( function_exists('bravepop_get_current_pageInfo') ? wp_json_encode(bravepop_get_current_pageInfo()) :'{}').';  var bravepop_emailSuggestions={};</script>');
}

add_action( 'wp_head', 'bravepop_popup_adblock_detecet_js', 10 );
function bravepop_popup_adblock_detecet_js() { 
   global $bravepop_global;
   if(!empty($bravepop_global['adblock_detect'])){
      wp_enqueue_script( 'bravepop_adblock_js', BRAVEPOP_PLUGIN_PATH . 'assets/frontend/prebid-ads-banner.js');
   }
}