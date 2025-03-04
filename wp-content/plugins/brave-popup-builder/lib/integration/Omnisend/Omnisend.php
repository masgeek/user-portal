<?php
if ( ! class_exists( 'BravePop_Omnisend' ) ) {
   
   class BravePop_Omnisend {

      protected $api_key;

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['omnisend']->api)  ? $integrations['omnisend']->api  : '';
      }

      public function get_lists($apiKey=''){
         $apiKey = $apiKey ? $apiKey : $this->api_key;
         if(!$apiKey){ return false;}
         $args = array(
            'method' => 'GET',
            'headers' => array(
               'X-API-KEY' => $apiKey
            ),         );
         $response = wp_remote_get( 'https://api.omnisend.com/v3/contacts?limit=100', $args );
         //error_log(wp_json_encode($response));
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         
         if($data && isset($data->contacts)){
            return wp_json_encode(array());
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
            $firstname = $splitted[0] ? trim($splitted[0]) : '';
            $lastname = $splitted[1] ? trim(str_replace($firstname, '', $fname)) : '';
         }
         

         $identifiers = array();
         $emailObject = new stdClass();
         $emailObject->channels = new stdClass();
         $emailObject->channels->email = new stdClass();
         $emailObject->channels->email->status = 'subscribed';
         $emailObject->type = 'email';
         $emailObject->id = $email;
         $emailObject->sendWelcomeMessage = true;
         $identifiers[] = $emailObject;

         if(!empty($phone)){
            $phoneObject = new stdClass();
            $phoneObject->channels = new stdClass();
            $phoneObject->channels->sms = new stdClass();
            $phoneObject->channels->sms->status = 'nonSubscribed';
            $phoneObject->type = 'phone';
            $phoneObject->id = $phone;
            $phoneObject->sendWelcomeMessage = true;
            $identifiers[] = $phoneObject;
         }
         
         $contact = array( 
            'identifiers' => $identifiers, 
            'firstName' => trim($firstname),
            'lastname' => trim($lastname)
         );

         //Add Tags
         if(isset($tags[0]) && !empty($tags[0])){
            $tagsArray = explode(",",$tags[0]);
            if(count($tagsArray) > 0){
               $contact['tags'] = $tagsArray;
            }
         }

         //Add Custom Field Values
         if(count($customFields) > 0){
            $cFields = new stdClass();
            foreach ($customFields as $key => $value) {
               if(in_array($key, array('country', 'countryCode', 'state', 'city', 'address', 'postalCode', 'gender', 'birthdate'))){
                  $contact[$key] = !empty($value) && is_array($value) ?  implode(',', $value) : $value;
               }else{
                  $keyID  = trim($key);
                  $cFields->$keyID = !empty($value) && is_array($value) ?  implode(',', $value) : $value;
               }
            }
            $contact['customProperties'] = $cFields;
         }

         $args = array(
            'method' => 'POST',
            'headers' => array(
               'content-type' => 'application/json',
               'X-API-KEY' => $this->api_key
            ),
            'body' => wp_json_encode($contact)
         );
         
         $response = wp_remote_post( 'https://api.omnisend.com/v3/contacts', $args );
         // error_log(wp_json_encode($contact));
         // error_log(wp_json_encode($response));
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         if($data && isset($data->email)){
            //error_log('##### USER ADDED ##### '. $data->contactID);
            $addedData = array(
               'action'=> isset($userData['action']) ? $userData['action'] : 'visitor_added',  
               'user_id'=> isset($userData['userData']['ID']) ? $userData['userData']['ID'] : false,
               'user_mail'=> $email, 'esp_user_id'=> $data->contactID
            ); 
            do_action( 'bravepop_addded_to_list', 'omnisend', $addedData );

            return $data->contactID; 
         }else{
            //error_log('##### ERROR '. $body);
            return false;
         }

      }

   }

}
?>