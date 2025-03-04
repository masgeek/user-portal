<?php

function bravepop_getVisitorIP()
{
   foreach (array('CF-Connecting-IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key){
      if (array_key_exists($key, $_SERVER) === true){
         foreach (explode(',', wp_strip_all_tags($_SERVER[$key])) as $ip){
               $ip = trim($ip); // just to be safe

               if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false){
                  return $ip;
               }
         }
      }
   }
}

function bravepop_getCurrentUser(){
   $current_user = wp_get_current_user();
   $userData = [];
   if (( $current_user instanceof WP_User ) ) {
      if(empty($userData['ID']) && $current_user->ID){
         $userData['ID'] = $current_user->ID;
      }
      if(empty($userData['name']) && $current_user->display_name){
         $userData['name'] = $current_user->display_name;
      }
      if(empty($userData['firstname']) && $current_user->user_firstname){
         $userData['firstname'] = $current_user->user_firstname;
      }
      if(empty($userData['lastname']) && $current_user->user_lastname){
         $userData['lastname'] = $current_user->user_lastname;
      }
      if(empty($userData['name']) && $current_user->user_firstname && $current_user->user_lastname){
         $userData['name'] = $current_user->user_firstname.' '.$current_user->user_lastname;
      }
      if(empty($userData['email']) && $current_user->user_email){
         $userData['email'] = $current_user->user_email;
      }
      if(empty($userData['username']) && $current_user->user_login){
         $userData['username'] = $current_user->user_login;
      }
   }
   return $userData;
}

function bravepopup_getvisitorCountry(){
   $country = '';
   if (class_exists('BravePop_Geolocation')) {
      $geolocation_instance = new BravePop_Geolocation();
      $user_ip_address = $geolocation_instance->get_ip_address();
      $user_geolocation = $geolocation_instance->geolocate_ip( $user_ip_address );
      $country = $user_geolocation && $user_geolocation['country'] ? $user_geolocation['country'] : '';
   }
   return $country;
}

function bravepopup_get_country_fields($type, $selectedCountry=false) {
   ob_start();
   include __DIR__ . '/data/countries.json';
   $contents = ob_get_clean();

   $countryData =json_decode($contents);
   $theOptionFields = '';

   if(isset($countryData->countries) && $type ==='country'){
      foreach ($countryData->countries as $key => $value) {
         $theOptionFields .= '<option value="'.$countryData->countries[$key]->name.'">'.$countryData->countries[$key]->name.'</option>';
      }
   }
   if($selectedCountry && $countryData->cities && $countryData->cities->$selectedCountry && $type ==='city'){
      foreach ($countryData->cities->$selectedCountry as $key => $val) {
         $theOptionFields .= '<option value="'.$val.'">'.$val.'</option>';
      }
   }
   if($selectedCountry && $countryData->states && $countryData->states->$selectedCountry && $type ==='state'){
      foreach ($countryData->states->$selectedCountry as $key => $val) {
         $theOptionFields .= '<option value="'.$val.'">'.$val.'</option>';
      }
   }

   return $theOptionFields;
}

add_filter('upload_mimes', 'bravepop_allow_font_mime_types');
function bravepop_allow_font_mime_types($mimes) {
   $mimes['woff'] = 'application/x-font-woff';
   $mimes['woff2'] = 'application/x-font-woff2';
   return $mimes;
 }

 function bravepopup_url_to_domain($url) {
    if($url){
      $sitedomain = wp_parse_url( esc_url($url), PHP_URL_HOST ); 
      if ( 'www.' === substr( $sitedomain, 0, 4 ) ) { $sitedomain = substr( $sitedomain, 4 ); }
      return $sitedomain;

    }else{
      return '';
    }
}

//Prepare REST POST data for Cloudflare 
function bravepopup_prepare_CF_data($popData) {
   $popupData = str_replace('_BRAVE_CF__STLE_','style', $popData);
   $popupData = str_replace('_BRAVE_CF_SC_','src', $popupData); 
   $popupData = str_replace('_BRAVE_CF_EQUAL_','=', $popupData);
   return $popupData;
}

function bravepopup_rocket_exclude_inline_js( $inline_js ) {
   $inline_js[] = 'bravepop_front_js';
   $inline_js[] = 'bravepop_front_js-js-extra';
   return $inline_js;
}
add_filter( 'rocket_excluded_inline_js_content', 'bravepopup_rocket_exclude_inline_js' );

function bravepop_get_curent_lang() {
   if(function_exists('weglot_get_current_language')){
      return weglot_get_current_language();
   }elseif(class_exists( 'SitePress' )){
      return apply_filters( 'wpml_current_language', NULL );
   }else{
      return substr( get_bloginfo ( 'language' ), 0, 2 );
   }
}

add_action('init', 'bravepop_menu_roles', 11);
function bravepop_menu_roles(){
   $allowedRole = apply_filters( 'bravepop_allowed_backend_cap', 'manage_options' );
   global $wp_roles; 
   $wp_roles->add_cap( 'administrator', 'access_brave_menus' ); 
}

function bravepop_updated_menu_roles ($previousRoles=array(), $allowedRoles=array()){
   global $wp_roles; 
   //First Disable the previously allowed roles
   foreach ($previousRoles as $key => $value) {
      $wp_roles->remove_cap( $value, 'access_brave_menus' );
   }
   // // Then allow the new roles
   foreach ($allowedRoles as $key => $value) {
      $wp_roles->add_cap( $value, 'access_brave_menus' );
   }
}

function bravepop_should_lazyload(){
   global $bravepop_settings;
   $appSettings = isset($bravepop_settings['app_settings']) ? $bravepop_settings['app_settings'] : new stdClass();
   $disableLazy = isset($appSettings->disableLazyLoading) ? wp_json_encode($appSettings->disableLazyLoading) : 'false'; 
   return $disableLazy === 'true' ? false : true;
}


include __DIR__ . '/login.php';
include __DIR__ . '/notifications.php';
include __DIR__ . '/stats.php';
include __DIR__ . '/icons.php';
include __DIR__ . '/forms.php';
include __DIR__ . '/metabox.php';
include __DIR__ . '/animation.php';
include __DIR__ . '/getSocialIcon.php';
include __DIR__ . '/generateStyleProps.php';