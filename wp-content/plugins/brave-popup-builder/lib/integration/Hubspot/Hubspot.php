<?php
if ( ! class_exists( 'BravePop_Hubspot' ) ) {

   class BravePop_Hubspot {

      protected $api_key;
      protected $isAccessToken;

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['hubspot']->api)  ? $integrations['hubspot']->api  : '';
         $this->isAccessToken = strpos($this->api_key, "pat-") !== false ? true : false;
      }


      public function get_lists($apiKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         if(!$apiKey){ return false; }

         $APIisAccessToken = strpos($apiKey, "pat-") !== false ? true : false;

         $args = $APIisAccessToken ? array(  'headers' => array( 'Authorization' => 'Bearer ' . $apiKey  )) : array();

         $response = wp_remote_get( 'https://api.hubapi.com/contacts/v1/lists/static?count=30'.(!$APIisAccessToken ? '&hapikey='.$apiKey:''), $args);
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         //error_log(wp_json_encode($response));
         if($data && isset($data->lists)){
            $lists = $data->lists;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->listId) ? $list->listId : '';
                  $listItem->name = isset($list->name) ? $list->name : '';
                  $listItem->count = isset($list->metaData) && isset($list->metaData->size) ? $list->metaData->size : 0;
                  $finalLists[] = $listItem;
               }
            }
            //error_log(wp_json_encode($finalLists));
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

         //Convert Full name to firstname and lastname. 
         if(!$lastname && $firstname && strpos($firstname, ' ') !== false){
            $splitted = explode(" ",$firstname);
            $firstname = $splitted[0] ? $splitted[0] : '';
            $lastname = $splitted[1] ? trim(str_replace($firstname, '', $fname)) : '';
         }

         $contact = array(
            array( "property"=> "email",  "value"=> $email ),
            array( "property"=> "firstname", "value"=> $firstname ),
            array( "property"=> "lastname",  "value"=> $lastname ),
            array( "property"=> "phone",  "value"=> $phone )
         );

         //Add Custom Field Values
         if(count($customFields) > 0){
            foreach ($customFields as $key => $value) {
               $contact[] = array( "property"=> $key,  "value"=> !empty($value) && is_array($value) ?  implode(';', $value) : $value );
            }
         }

         //https://legacydocs.hubspot.com/docs/methods/contacts/create_or_update
         $addUserargs = array(
            'method' => 'POST',
            'headers' => array(
               'content-type' => 'application/json',
            ),
            'body' => wp_json_encode(array(
               'properties' => $contact
            ))
         );
         if($this->isAccessToken && $addUserargs['headers']){   $addUserargs['headers']['Authorization'] = 'Bearer ' . $this->api_key;  }
         $response = wp_remote_post( 'https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/'.$email.(!$this->isAccessToken ? '/?hapikey='.$this->api_key:''), $addUserargs );
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );


         if($data && isset($data->vid)){
            $vid = $data->vid;

            return $this->add_to_list_action($email, $vid, $list_id, $userData);

         }else{
            return false;
         }

      }


      public function add_to_list_action($email, $vid, $list_id, $userData){
         if(!$vid){ return false; }
         $userToList = array( 'method' => 'POST', 'headers' => array( 'content-type' => 'application/json' ),'body' => wp_json_encode(array( 'vids' => array($vid) )) );
         if($this->isAccessToken && $userToList['headers']){   $userToList['headers']['Authorization'] = 'Bearer ' . $this->api_key;  }
         $listresponse = wp_remote_post( 'https://api.hubapi.com/contacts/v1/lists/'.$list_id.'/add'.(!$this->isAccessToken ? '?hapikey='.$this->api_key:''), $userToList );
         $listbody = wp_remote_retrieve_body( $listresponse );
         $listdata = json_decode( $listbody );
         //error_log('#####ADD TO LIST RESPONSE: '.wp_json_encode($listresponse));

         if($listdata && isset($listdata->updated)){
            $addedData = array(
               'action'=> isset($userData['action']) ? $userData['action'] : 'visitor_added',  
               'user_id'=> isset($userData['userData']['ID']) ? $userData['userData']['ID'] : false,
               'user_mail'=> $email, 'esp_user_id'=> $vid
            ); 
            do_action( 'bravepop_addded_to_list', 'hubspot', $addedData );

            return true; 
         }else{
            return false;
         }
      }


   }

}
?>