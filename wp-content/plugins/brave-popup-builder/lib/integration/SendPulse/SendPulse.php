<?php
if ( ! class_exists( 'BravePop_SendPulse' ) ) {
   
   class BravePop_SendPulse {

      protected $api_key;
      protected $api_secret;

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['sendpulse']->api)  ? $integrations['sendpulse']->api  : '';
         $this->api_secret = isset($integrations['sendpulse']->secret)  ? $integrations['sendpulse']->secret  : '';
         //$this->doubleoptin = isset($integrations['sendpulse']->doubleoptin)  ? $integrations['sendpulse']->doubleoptin  : false;
      }

      public function get_access_token($api_key='', $api_secret=''){
         if(!$api_key && !$api_secret){  return error_log('Sendpulse Refresh Token Missing!'); }
         $access_args = array('grant_type'=>'client_credentials', 'client_id'=> $api_key, 'client_secret'=>$api_secret);
         $args = array( 'method' => 'POST','headers' => array( 'Content-Type' => 'application/json' ), 'body' => wp_json_encode($access_args)  );
         $response = wp_remote_post( 'https://api.sendpulse.com/oauth/access_token', $args );
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         return isset($data->access_token) ? $data->access_token : '';
      }

      public function get_lists($api_key='', $api_secret=''){
         $api_key  = $api_key ? $api_key : $this->api_key;
         $api_secret  = $api_secret ? $api_secret : $this->api_secret;
         if(!$api_key && !$api_secret){  return error_log('Sendpulse API Key/Secret Missing!');  }

         $access_token  = $this->get_access_token($api_key, $api_secret); 
         if(!$access_token){ return error_log('Sendpulse access_token could not be generated!'); }

         $headerArgs = array( 'headers' => array(  'Authorization' => 'Bearer ' . $access_token ) );
         $lresponse = wp_remote_get( 'https://api.sendpulse.com/addressbooks?limit=100', $headerArgs );

         if( is_wp_error( $lresponse ) ) {
            return false; // Bail early
         }

         $lbody = wp_remote_retrieve_body( $lresponse );
         $ldata = json_decode( $lbody );

         $finalLists = array();
         
         if(isset($ldata) && is_array($ldata)){
            $lists = $ldata;
            foreach ($lists as $key => $list) {
               $listItem = new stdClass();
               $listItem->id = isset($list->id) ? $list->id : '';
               $listItem->name = isset($list->name) ? $list->name : '';
               $finalLists[] = $listItem;
            }

            return wp_json_encode($finalLists);
         }else{
            return false;
         }

      }

      public function add_to_lists($email, $list_id, $fname='', $lname='', $phone='', $customFields=array(), $tags=array(), $userData=array()){
         if(!$email || !$list_id){ return null; }
         if( !$this->api_key || !$this->api_secret ){  return error_log('Sendpulse API Key/Secret Missing!'); }

         $firstname = trim($fname);
         $lastname = trim($lname);
         $fullname = $firstname;

         //Convert firstname and lastname to Fullname. 
         if($firstname && $lastname){
            $fullname = $firstname.' '.$lastname;
         }

         $access_token  = $this->get_access_token($this->api_key, $this->api_secret); 
         if(!$access_token){ return error_log('Sendpulse access_token could not be generated!'); }

         $contact = array( 'emails'=> array() );
         $contact['emails'][] = array('email' => $email, 'variables'=> array( 'Name' => trim($fullname), 'Phone' => trim($phone)  )) ;

         //Add Custom Field Values
         if(count($customFields) > 0){
            foreach ($customFields as $key => $value) {
               if( $contact['emails'][0]['variables']){
                  $contact['emails'][0]['variables'][$key] = !empty($value) && is_array($value) ?  implode(',', $value) : $value;
               }
            }
         }
      
         $args = array(
            'method' => 'POST',
            'headers' => array( 'Authorization' => 'Bearer ' . $access_token, 'Content-Type' => 'application/json'  ),
            'body' => wp_json_encode($contact)
         );
         //https://web.sendpulse.com/integrations/api/bulk-email#add-email
         $response = wp_remote_post( 'https://api.sendpulse.com/addressbooks/'.$list_id.'/emails', $args );
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         //error_log(wp_json_encode($response));

         if(isset($data->result)){
            $addedData = array(
               'action'=> isset($userData['action']) ? $userData['action'] : 'visitor_added',  
               'user_id'=> isset($userData['userData']['ID']) ? $userData['userData']['ID'] : false,
               'user_mail'=> $email, 'esp_user_id'=> 'none'
            ); 
            do_action( 'bravepop_addded_to_list', 'sendpulse', $addedData );
            
            return true;
         }else{
            return false;
         }

      }


   }

}
?>