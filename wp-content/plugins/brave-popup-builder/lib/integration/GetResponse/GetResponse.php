<?php
if ( ! class_exists( 'BravePop_GetResponse' ) ) {
   
   class BravePop_GetResponse {

      protected $access_key;

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->access_key = isset($integrations['getresponse']->access)  ? $integrations['getresponse']->access  : '';
      }


      public function get_lists($accessKey=''){
         $accessKey = $accessKey ? $accessKey : $this->access_key;
         $args = array(
            'headers' => array(
               'Authorization' => 'Bearer ' . $accessKey
            )
         );
         $response = wp_remote_get( 'https://api.getresponse.com/v3/campaigns', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         // error_log(wp_json_encode($body));
         if(!$data || (isset($data->httpStatus) && $data->httpStatus === 401)){
            return false;
         }

         if($data){
            $lists = $data;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->campaignId) ? $list->campaignId : '';
                  $listItem->name = isset($list->name) ? $list->name : '';
                  $finalLists[] = $listItem;
               }
            }
            return wp_json_encode($finalLists);
         }

      }


      public function add_to_lists($email, $list_id, $fname='', $lname='', $phone='', $customFields=array(), $tags=array(), $userData=array(), $doubleOptin=false, $misc=array()){
         if(!$email || !$list_id){ return null; }
         if(!$this->access_key){ 
            //error_log('ACCESSE KEY Missing!');
            return false;
         }
         $firstname = trim($fname);
         $lastname = trim($lname);
         $fullname = $firstname;

         //Convert firstname and lastname to Fullname. 
         if($firstname && $lastname){
            $fullname = $firstname.' '.$lastname;
         }
         
         $campaign = new stdClass();
         $campaign->campaignId = $list_id;
         $contact = array('campaign'  => $campaign, 'name' => $fullname, 'email' => $email );

         //Add IP Address
         if(class_exists('BravePop_Geolocation')){
            $ipAddress = bravepop_getVisitorIP();
            if(filter_var($ipAddress, FILTER_VALIDATE_IP)){
               $contact['ipAddress'] = $ipAddress;
            }
         }
         //Add Custom Field Values
         if(count($customFields) > 0){
            $fieldValues = array();
            foreach ($customFields as $key => $value) {
               $fieldValues[] = array('customFieldId'=> $key, 'value' => [(!empty($value) && is_array($value) ?  implode(',', $value) : $value)]);
            }
            $contact['customFieldValues'] = $fieldValues;
         }
         //Add Tags
         if(count($tags) > 0){
            $allTags = array();
            foreach ($tags as $tagIndex => $tag) {
               if(isset($tag->id)){
                  $allTags['tagId'] = $tag->id;
               }
            }
            $contact['tags'] = $allTags;
         }

         if(isset($misc['dayOfCycle'])){
            $contact['dayOfCycle'] = $misc['dayOfCycle'];
         }

         $args = array(
            'method' => 'POST',
            'headers' => array(
               'content-type' => 'application/json',
               'Authorization' => 'Bearer ' . $this->access_key,
               'accept-encoding'=> '', 
            ),
            'body' => wp_json_encode($contact)
         );

         $response = wp_remote_post( 'https://api.getresponse.com/v3/contacts', $args );
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );


         if($response && isset($response['response']) && isset($response['response']['code']) && $response['response']['code'] === 202){
            $addedData = array(
               'action'=> isset($userData['action']) ? $userData['action'] : 'visitor_added',  
               'user_id'=> isset($userData['userData']['ID']) ? $userData['userData']['ID'] : false,
               'user_mail'=> $email, 'esp_user_id'=> ''
            ); 
            do_action( 'bravepop_addded_to_list', 'getresponse', $addedData );

            return true; 
         }else{
            return false;
         }

      }


   }

}
?>