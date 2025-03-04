<?php
if ( ! class_exists( 'BravePop_CampaignMonitor' ) ) {
   
   class BravePop_CampaignMonitor {

      protected $api_key;
      protected $clientID;

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['campaignmonitor']->api)  ? $integrations['campaignmonitor']->api  : '';
         $this->clientID = isset($integrations['campaignmonitor']->secret)  ? $integrations['campaignmonitor']->secret  : '';
      }


      public function get_lists($apiKey='', $clientID=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         $clientID  = $clientID ? $clientID : $this->clientID;
         if(!$apiKey || !$clientID){ return; }

         $args = array(
            'method' => 'GET',
            'headers' => array(  'content-type' => 'application/json', 'Authorization' => 'Basic '.base64_encode($apiKey.":x" ) ),
         );

         $response = wp_remote_get( 'https://api.createsend.com/api/v3.2/clients/'.$clientID.'/lists.json', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         
         if($data && is_array($data) && isset($data[0])){
            $lists = $data;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->ListID) ? $list->ListID : '';
                  $listItem->name = isset($list->Name) ? $list->Name : '';
                  $finalLists[] = $listItem;
               }
            }
            // error_log(wp_json_encode($finalLists));
            return wp_json_encode($finalLists);
         }else{
            return false;
         }

      }


      public function add_to_lists($email, $list_id, $fname='', $lname='', $phone='', $customFields=array(), $tags=array(), $userData=array()){
         if(!$email || !$list_id){ return null; }
         if(!$this->api_key){ 
            //error_log('API KEY or SECRET Missing!');
            return false;
         }

         $firstname = trim($fname);
         $lastname = trim($lname);
         $fullname = $firstname.($lname ? ' '.$lname : $lname);

         $contact = array( 'EmailAddress' => $email, 'Name' => $fullname, 'ConsentToTrack' => 'Yes' );
         
         //Add Custom Field Values
         if(count($customFields) > 0){
            $fieldValues = array();
            foreach ($customFields as $key => $value) {
               if(is_array($value)){
                  foreach ($value as $indx => $val) {
                     $fieldValues[] = array('Key'=> $key, 'Value' => $val);
                  }
               }else{
                  $fieldValues[] = array('Key'=> $key, 'Value' => $value);
               }
            }  
            $contact['CustomFields'] = $fieldValues;
         }

         $args = array(
            'method' => 'POST',
            'headers' => array(
               'content-type' => 'application/json', 
               'Authorization' => 'Basic '.base64_encode($this->api_key.":x" )
            ),
            'body' => wp_json_encode($contact)
         );

         $response = wp_remote_post( 'https://api.createsend.com/api/v3.2/subscribers/'.$list_id.'.json', $args );
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         if(isset($response['response']['code']) && $response['response']['code'] === 201){
            $addedData = array(
               'action'=> isset($userData['action']) ? $userData['action'] : 'visitor_added',  
               'user_id'=> isset($userData['userData']['ID']) ? $userData['userData']['ID'] : false,
               'user_mail'=> $email, 'esp_user_id'=> 'none'
            ); 
            do_action( 'bravepop_addded_to_list', 'campaignmonitor', $addedData );

            return $response['response']['code']; 
         }else{
            return false;
         }

      }


   }

}
?>