<?php

add_action('wp_ajax_bravepop_ajax_popup_viewed', 'bravepop_ajax_popup_viewed', 0);
add_action('wp_ajax_nopriv_bravepop_ajax_popup_viewed', 'bravepop_ajax_popup_viewed');

function bravepop_ajax_popup_viewed(){
   if(!isset($_POST['popupID']) || !isset($_POST['security'])){ wp_die(); }
   check_ajax_referer('brave-ajax-nonce', 'security');

   $goalIsFirstView = isset($_POST['goalIsFirstView']) && sanitize_text_field($_POST['goalIsFirstView']) == 'true' ? true : false;
   $pageURL = isset($_POST['pageURL']) ? esc_url_raw(wp_unslash($_POST['pageURL'])) : get_site_url();
   $goalUTCTime = isset($_POST['goalUTCTime']) ? sanitize_text_field(wp_unslash($_POST['goalUTCTime'])) : time();

   $settings = get_option('_bravepopup_settings');
   $excludedIPs = isset($settings['analytics']->excludedIps) && !empty($settings['analytics']->excludedIps)  ? explode(",",$settings['analytics']->excludedIps) : false;
   $excludedIPs = $excludedIPs ? array_filter(array_map('trim', $excludedIPs)) : false;
   $user_ip_address = bravepop_getVisitorIP();

   if($excludedIPs && in_array($user_ip_address, $excludedIPs) ){
      return;
      wp_die();
   }


   //Update the View count in popup Meta
   $popupID = sanitize_text_field(wp_unslash($_POST['popupID']));
   $currentViews  = get_post_meta( $popupID, 'popup_views', true );
   $currentViews = $currentViews ? (int)$currentViews  : 0;
   update_post_meta( $popupID, 'popup_views', $currentViews+1 );
   
   //if the popup goal is set to view first step, Update the goal count in popup Meta 
   if($goalIsFirstView){
      $currentConversion  = get_post_meta( $popupID, 'popup_conversion', true );
      $currentConversion = $currentConversion ? (int)$currentConversion  : 0;
      update_post_meta( $popupID, 'popup_conversion', $currentConversion+1 );
      $userData = array('ip'=> bravepop_getVisitorIP(), 'country'=> bravepopup_getvisitorCountry(), 'ID' => get_current_user_id());
      do_action( 'bravepop_user_completed_goal', $popupID, $pageURL, $userData);
      //Send Goal Notification
      if (function_exists('bravepop_send_goal_notification')) {
         bravepop_send_goal_notification($popupID, $pageURL, 'view', '', '', $goalUTCTime);
      }
   }

   //Finally, update the brave stats db
   $settings = get_option('_bravepopup_settings');
   $disableTracking = isset($settings['analytics']->disableTracking) && $settings['analytics']->disableTracking === true ? true : false;

   if(!$disableTracking && class_exists('BravePop_Geolocation')){
      $date = isset($_POST['date']) ? $_POST['date'] : date('Y').'-'.date('m').'-'.date('d');
      $braveStats = new BravePop_Analytics();
      $braveStats->updatePopupStat( intval($popupID), 'views', $date, $goalIsFirstView);
   }

   return $currentViews;

   wp_die();
}

add_action('wp_ajax_bravepop_ajax_popup_complete_goal', 'bravepop_ajax_popup_complete_goal', 0);
add_action('wp_ajax_nopriv_bravepop_ajax_popup_complete_goal', 'bravepop_ajax_popup_complete_goal');

function bravepop_ajax_popup_complete_goal(){
   if(!isset($_POST['popupID']) || !isset($_POST['security'])){ wp_die(); }
   // error_log(wp_json_encode($_REQUEST));
   check_ajax_referer('brave-ajax-nonce', 'security');

   $popupID = sanitize_text_field(wp_unslash($_POST['popupID']));
   $goalType = isset($_POST['goalType']) ? sanitize_text_field(wp_unslash($_POST['goalType'])) : '';
   $views = isset($_POST['views']) ? sanitize_text_field(wp_unslash($_POST['views'])) : 1;
   $goalTime = isset($_POST['goalTime']) ? sanitize_text_field(wp_unslash($_POST['goalTime'])) : time();
   $goalDate = isset($_POST['goalDate']) ? sanitize_text_field(wp_unslash($_POST['goalDate'])) : date('Y').'-'.date('m').'-'.date('d');
   $goalUTCTime = isset($_POST['goalUTCTime']) ? sanitize_text_field(wp_unslash($_POST['goalUTCTime'])) : time();
   $device = isset($_POST['device']) ? sanitize_text_field(wp_unslash($_POST['device'])) : 'undefined';
   $pageURL = isset($_POST['pageURL']) ? esc_url_raw(wp_unslash($_POST['pageURL'])) :'';

   bravepop_popup_complete_goal($popupID, $goalType, $views, $goalTime, $goalDate, $goalUTCTime, $device, $pageURL);

   echo '';

   wp_die();
}


function bravepop_popup_complete_goal($popupID, $goalType, $views, $goalTime, $goalDate, $goalUTCTime, $device='', $pageURL=''){
   $currentConversion  = get_post_meta( $popupID, 'popup_conversion', true );
   $currentConversion = $currentConversion ? (int)$currentConversion  : 0;
   $user_ip_address = ''; $country = ''; $city =''; $country_code ='';
   if (class_exists('BravePop_Geolocation')) {
      $geolocation_instance = new BravePop_Geolocation();
      $user_ip_address = $geolocation_instance->get_ip_address();
      $user_geolocation = $geolocation_instance->geolocate_ip( $user_ip_address );
      $country = $user_geolocation && $user_geolocation['country'] ? $user_geolocation['country'] : '';
      $country_code = $user_geolocation && $user_geolocation['iso_code'] ? $user_geolocation['iso_code'] : '';
   }

   update_post_meta( $popupID, 'popup_conversion', $currentConversion+1 );

   $userData = array('ip'=> $user_ip_address, 'country'=> $country, 'ID' => get_current_user_id());
   do_action( 'bravepop_user_completed_goal', $popupID, $pageURL, $userData);
   
   if(class_exists('BravePop_Analytics')) {
         $settings = get_option('_bravepopup_settings');
         $saveIp = isset($settings['analytics']->ipaddress) && $settings['analytics']->ipaddress === true ? true : false;
         $saveCountry = isset($settings['analytics']->country) && $settings['analytics']->country === false ? false : true;
         $disableTracking = isset($settings['analytics']->disableTracking) && $settings['analytics']->disableTracking === true ? true : false;
         $excludedIPs = isset($settings['analytics']->excludedIps) && !empty($settings['analytics']->excludedIps)  ? explode(",",$settings['analytics']->excludedIps) : false;
         $excludedIPs = $excludedIPs ? array_filter(array_map('trim', $excludedIPs)) : false;

         if($excludedIPs && in_array($user_ip_address, $excludedIPs)){
            $disableTracking = true;
         }

         $goalStat = array(
            'goal_time' => $goalTime ? date("Y-m-d H:i:s", round ( $goalTime / 1000)) : current_time( 'mysql' ),
            'popup' => intval($popupID),
            'country' => $saveCountry ? $country_code : '',
            'ip' => $saveIp ? $user_ip_address : '',
            'device' => $device,
            'goaltype' => $goalType,
            'actiontype' => 'click',
            'url' =>  str_replace( get_site_url(), '', esc_url($pageURL)),
            'user' => get_current_user_id(),
            'viewed' => intval($views)
         );
         if(!$disableTracking && class_exists('BravePop_Geolocation')){
            $braveStats = new BravePop_Analytics();
            $braveStats->updatePopupStat( intval($popupID), 'goals', $goalDate);
            $braveStats->insertGoal( $goalStat );
         }
   }

   //Send Goal Notification
   if (function_exists('bravepop_send_goal_notification')) {
      bravepop_send_goal_notification($popupID, $pageURL, $goalType, $country, $city, $goalUTCTime);
   }
}