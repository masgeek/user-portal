<?php
if ( ! class_exists( 'BravePop_MailerLite' ) ) {
   
   class BravePop_MailerLite {

      protected $api_key;
      protected $versionTwo;
      protected $apiURL;

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['mailerlite']->api)  ? $integrations['mailerlite']->api  : '';
         $this->versionTwo = isset($integrations['mailerlite']->api) && strlen($integrations['mailerlite']->api) > 50 ? true  : false;
         $this->apiURL = $this->versionTwo ? 'https://connect.mailerlite.com/api/' : 'https://api.mailerlite.com/api/v2/';
      }

      public function get_lists($apiKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;

         $args = array(
            'method' => 'GET',
            'headers' => $this->versionTwo ? array( 'Authorization' => 'Bearer '.$apiKey ) : array( 'X-MailerLite-ApiKey' => $apiKey )
         );

         if($this->versionTwo){ 
            $args['user-agent'] = 'Mozilla/5.0 (Windows; U; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)';
         }
         
         $response = wp_remote_get( $this->apiURL.'groups?limit=300', $args );

         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         $lists = isset($data->data) && $this->versionTwo ? $data->data : $data;
         $finalLists = array();

         if($lists && is_array($lists)){
            
            foreach ($lists as $key => $list) {
               $listItem = new stdClass();
               $listItem->id = isset($list->id) ? $list->id : '';
               $listItem->name = isset($list->name) ? $list->name : '';
               if($this->versionTwo && isset($list->active_count)){
                  $listItem->count = isset($list->active_count)  ? $list->active_count : 0;
               }elseif (isset($list->total)){
                  $listItem->count = isset($list->total)  ? $list->total : 0;
               }
               $finalLists[] = $listItem;
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
         $fullname = $firstname;

         //Convert firstname and lastname to Fullname. 
         if($firstname && $lastname){
            $fullname = $firstname.' '.$lastname;
         }

         $contact = array('autoresponders'=> true, 'resubscribe'=> true, 'email'=> $email, 'name'=> trim($fullname), 'fields'=> array('phone'=> trim($phone)  ));
         
         //Add Custom Field Values
         if(count($customFields) > 0 && $contact['fields']){
            foreach ($customFields as $key => $value) {
               $value = !empty($value) && is_array($value) ?  implode(',', $value) : $value;
               $contact['fields'][$key] = $value;
            }
         }


         $args = array( 'method' => 'POST', 'headers' => array( 'content-type' => 'application/json' ) );
         
         if($this->versionTwo){ 
            //IF version TWO, change the headers and the payload
            $args['headers']['Authorization'] = 'Bearer '.$this->api_key;
            $args['user-agent'] = 'Mozilla/5.0 (Windows; U; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 2.0.50727)';
            $contact['groups'] = array($list_id);
            if($fullname){  $contact['fields']['name'] = trim($fullname);  }
         }else{
            $args['headers']['X-MailerLite-ApiKey'] = $this->api_key;
         }

         $args['body'] = wp_json_encode($contact);

         // Send the Add Request
         $response = wp_remote_post( $this->versionTwo ? $this->apiURL.'subscribers' : $this->apiURL.'groups/'.$list_id.'/subscribers', $args );
                  
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         $addData = isset($data->data) && $this->versionTwo ? $data->data : $data;

         // error_log('### Add to Group Response: '. wp_json_encode(isset($addData->id)).' ==> '. wp_json_encode($addData));

         if($addData && isset($addData->id)){
            $userID = $addData->id;
            $addedData = array(
               'action'=> isset($userData['action']) ? $userData['action'] : 'visitor_added',  
               'user_id'=> isset($userData['userData']['ID']) ? $userData['userData']['ID'] : false,
               'user_mail'=> $email, 'esp_user_id'=> $userID
            ); 
            do_action( 'bravepop_addded_to_list', 'mailerlite', $addedData );

            return true; 
         }else{
            return false;
         }
      }


   }

}
?>