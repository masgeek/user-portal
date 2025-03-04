<?php
if ( ! class_exists( 'BravePop_Pabbly' ) ) {
   
   class BravePop_Pabbly {

      protected $api_key;

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['pabbly']->api)  ? $integrations['pabbly']->api  : '';
      }


      public function get_lists($apiKey=''){
         $apiKey     = $apiKey ? $apiKey : $this->api_key;

         if(!$apiKey){ return false;}

         $args = array(
            'headers' => array(
               'Authorization' => 'Bearer ' .$apiKey
            )
         );
         $response = wp_remote_get( 'https://emails.pabbly.com/api/subscribers-list', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         if($data && isset($data->subscribers_list)){
            $lists = $data->subscribers_list;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->list_id) ? $list->list_id : '';
                  $listItem->name = isset($list->list_name) ? $list->list_name : '';
                  $listItem->count = 0;
                  $finalLists[] = $listItem;
               }
            }
            return wp_json_encode($finalLists);
         }else{
            return false;
         }

      }


      public function add_to_lists($email, $list_id, $fname='', $lname='', $phone='', $customFields=array(), $tags=array()){
         if(!$email || !$list_id){ return false; }
         if(!$this->api_key){ 
            //error_log('API KEY or SECRET Missing!');
            return false;
         }

         $firstname = trim($fname);
         $lastname = trim($lname);
         $fullname = $firstname;

         //Convert firstname and lastname to Fullname. 
         if($firstname && $lastname){
            $fullname = $firstname.' '.$lastname;
         }
         
         $contact = array( 'import' => 'single', 'email' => $email, 'name' => trim($fullname), 'list_id' => $list_id );

         //Add Custom Field Values
         if(count($customFields) > 0){
            foreach ($customFields as $key => $value) {
               $contact[$key] = !empty($value) && is_array($value) ?  implode(',', $value) : $value;
            }
         }

         $args = array(
            'method' => 'POST',
            'headers' => array(
               'content-type' => 'application/json',
               'Authorization' => 'Bearer ' .$this->api_key 
            ),
            'body' => wp_json_encode($contact)
         );

         $response = wp_remote_post( 'https://emails.pabbly.com/api/subscribers', $args );

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         //error_log(wp_json_encode($data->Data[0]));
         if($data && isset($data->status) && $data->status === 'success'){
            //error_log('##### USER ADDED #####');
            return $data->status; 
         }else{
            return false;
         }

      }


   }

}
?>