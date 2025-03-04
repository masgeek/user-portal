<?php
if ( ! class_exists( 'BravePop_Analytics' ) ) {
   
   class BravePop_Analytics {

      static function fetchPopupStats( $popupID ) {
         if(!$popupID){ return; }
         global $wpdb; $viewTable = $wpdb->prefix . 'bravepopup_stats';
         $popupID = absint( $popupID );
         $sql = $wpdb->prepare("SELECT * FROM $viewTable WHERE `popup` = %d ORDER BY id DESC", $popupID);
         return $wpdb->get_results( $sql );
      }

      static function fetchAllStats(  ) {
         global $wpdb; $viewTable = $wpdb->prefix . 'bravepopup_stats';
         $sql   = 'SELECT * FROM ' . $viewTable . "";
         return $wpdb->get_results( $sql );
      }

      static function fetchPopupGoals( $popupID,  $startDate, $endDate, $offset=3 ) {
         if(!$popupID || !$startDate || !$endDate){ return; }
         global $wpdb; $goalTable = $wpdb->prefix . 'bravepopup_goal_stats';
         $popupID = absint( $popupID ); $pagination ='';
         $sql = $wpdb->prepare(
            "SELECT * FROM $goalTable WHERE (`popup` = %d AND `goal_time` BETWEEN %s AND %s) ORDER BY goal_time",
            $popupID,
            $startDate,
            $endDate
        );
         return $wpdb->get_results( $sql );
      }

      static function fetchAllPopupGoals( $startDate, $endDate ) {
         if(!$startDate || !$endDate){ return; }
         global $wpdb; $goalTable = $wpdb->prefix . 'bravepopup_goal_stats';
         //$startDate = '2020-08-24';  $endDate = '2020-08-25 23:59:59';
         $sql = $wpdb->prepare(
            "SELECT * FROM $goalTable WHERE `goal_time` BETWEEN %s AND %s",
            $startDate,
            $endDate
        );
         return $wpdb->get_results( $sql );
      }

      static function insertGoal( $data ) {
         if(!$data){ return; }
         global $wpdb; $goalTable = $wpdb->prefix . 'bravepopup_goal_stats';
         $wpdb->insert( $goalTable, $data );
      }

      static function updateGoal( $data, $where ) {
         if(!$data || !$where){ return; }
         global $wpdb; $goalTable = $wpdb->prefix . 'bravepopup_goal_stats';
         $wpdb->update( $goalTable, $data, $where );
      }

      static function deleteGoal( $goalID ) {
         if(!$goalID){ return; }
         global $wpdb; $goalTable = $wpdb->prefix . 'bravepopup_goal_stats';
         return $wpdb->delete( $goalTable, array( 'id' => $goalID ) );
      }

      static function insertStat( $data ) {
         if(!$data){ return; }
         global $wpdb; $viewTable = $wpdb->prefix . 'bravepopup_stats';
         $wpdb->insert( $viewTable, $data );
      }

      static function updatePopupStat( $popupID, $type, $date, $bothTypes=false ) {
         if(!$date || !$popupID){ return; }
         $startTime = microtime(true);
         global $wpdb; $viewTable = $wpdb->prefix . 'bravepopup_stats';
         $popupID = absint($popupID);
         $sql = $wpdb->prepare("SELECT * FROM $viewTable WHERE `popup` = %d", $popupID);
         $popupViewRow = $wpdb->get_results( $sql );
         // error_log($type.' '.wp_json_encode($popupViewRow));
         if(isset($popupViewRow[0])){
            $foundRow = $popupViewRow[0];
            $where = array('id'=> absint($foundRow->id));
            $popupViews = json_decode($foundRow->stats);
            if(isset($popupViews)){
               if(!isset($popupViews->$date)){ $popupViews->$date = new stdClass();  }
               if($bothTypes){
                  $currentViewCount = isset($popupViews->$date->views) ? intval($popupViews->$date->views) : 0;
                  $currentGoalCount = isset($popupViews->$date->goals) ? intval($popupViews->$date->goals) : 0;
                  $popupViews->$date->views = $currentViewCount + 1; 
                  $popupViews->$date->goals = $currentGoalCount + 1;
               }else{
                  $currentCount = isset($popupViews->$date->$type) ? intval($popupViews->$date->$type) : 0;
                  $popupViews->$date->$type = $currentCount + 1;
               }
   
               $foundRow->stats = wp_json_encode($popupViews);
               $wpdb->update( $viewTable, array('stats'=> $foundRow->stats), $where );
            }
         }else{
            $newdata = array('stats'=>'', 'popup'=> $popupID);
            $viewData = array();
            $viewData[$date] = array('views'=> 1, 'goals'=> ($bothTypes ? 1 : 0) );
            $newdata['stats'] = wp_json_encode($viewData);
            //error_log('ROW NOT FOUND. ADDING: '.wp_json_encode($viewData). ' Type: '. $type);
            $wpdb->insert( $viewTable, $newdata );
         }
         $endTime = microtime(true);
         $diff = $endTime - $startTime;
         //error_log("script execution time: $diff");
      }

      static function removePopupStat( $popupID ) {
         if(!$popupID){ return; }
         global $wpdb; 
         $goalTable = $wpdb->prefix . 'bravepopup_goal_stats';
         $viewTable = $wpdb->prefix . 'bravepopup_stats';
         $popupID = absint($popupID);
         update_post_meta( $popupID, 'popup_views', 0 );
         update_post_meta( $popupID, 'popup_conversion', 0 );
         $wpdb->delete( $goalTable, array( 'popup' => $popupID ) );
         $wpdb->delete( $viewTable, array( 'popup' => $popupID ) );
         //error_log('ALL Stats Removed!!!');
         return true;
      }

      static function get_analytics_csv( $popupID ) {
         if(!$popupID){ return; }
         global $wpdb; 
         $popupID = absint($popupID);
         $goalTable = $wpdb->prefix . 'bravepopup_goal_stats';
         $sql = $wpdb->prepare("SELECT * FROM $goalTable WHERE `popup` = %d", $popupID);
         $allEntries =  $wpdb->get_results( $sql );
         $analytics_entries = array();
         $settings = get_option('_bravepopup_settings');
         $saveIp = isset($settings['analytics']->ipaddress) && $settings['analytics']->ipaddress === true ? true : false;

         foreach ($allEntries as $key => $entry) {
            //error_log(wp_json_encode($theEntry));
            $stat = new stdClass(); 
            $stat->id = intval($entry->id);
            $stat->campaign_ID = intval($entry->popup);
            $stat->goal_time = $entry->goal_time;
            $stat->goal_type = $entry->goaltype;
            $stat->url = esc_url(home_url($entry->url));
            
            $stat->user_id = intval($entry->user) === 0 ? 'Visitor' : intval($entry->user);
            $stat->country = $entry->country ? brave_get_country_name($entry->country) : '';
            $stat->ip = $saveIp ? $entry->ip : '';
            $stat->device = $entry->device;
            $stat->success_rate =  (( 1 / (intval($entry->viewed)||1)) * 100).'%';

            $analytics_entries[] = $stat;
         }

         return $analytics_entries; 
      }

   }

}
?>