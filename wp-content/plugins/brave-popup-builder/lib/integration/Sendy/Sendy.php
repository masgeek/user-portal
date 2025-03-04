<?php
if ( ! class_exists( 'BravePop_Sendy' ) ) {

   class BravePop_Sendy {

      protected $api_key;
      protected $api_url;

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_url = isset($integrations['sendy']->url)  ? esc_url(rtrim($integrations['sendy']->url, '/')).'/subscribe'  : '';
         $this->api_key = isset($integrations['sendy']->api)  ? $integrations['sendy']->api  : '';
      }

      public function validate_integration($api_url, $api_key){
         if(!$api_url || !$api_key){ return false;}
         $theURL = esc_url(rtrim($api_url, '/')).'/subscribe';
         $contact = array('boolean'=> true, 'list'=> '', 'email'=> 'test@test.com', 'api_key'=> $api_key);
         $args = array( 'method' => 'POST','body'=> ($contact), 'timeout' => 30 );
         $response = wp_remote_post( $theURL, $args );
         $body = wp_remote_retrieve_body( $response );
         //Unable to decrypt string with openssl_decrypt() || Invalid list ID.
         if($body && ($body === 'Invalid list ID.' || $body === 'Unable to decrypt string with openssl_decrypt()')){
            return true;
         }else{
            return false;
         }
         
      }

      public function add_to_lists($email, $list_id, $fname='', $lname='', $phone='', $customFields=array(), $tags=array(), $userData=array(), $doubleOptin=false, $misc=array()){
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

         $contact = array('boolean'=> true, 'list'=> $list_id, 'email'=> $email, 'name'=> trim($fullname), 'api_key'=> $this->api_key);
         
         //Add Custom Field Values
         if(count($customFields) > 0){
            foreach ($customFields as $key => $value) {
               $value = !empty($value) && is_array($value) ?  implode(',', $value) : $value;
               $contact[$key] = $value;
            }
         }

         $args = array( 'method' => 'POST', 'body'=> ($contact), 'timeout' => 30 );

         $response = wp_remote_post( $this->api_url, $args );
                  
         $body = wp_remote_retrieve_body( $response );
         $success =   (strpos($body, "You're subscribed!") !== false) || (strpos($body, "You're already subscribed!" ) !== false)  ? true : false; 
         // error_log('### Sendy ADD Response: '. wp_json_encode($body));
         // error_log('### Sendy ADD Success: '. wp_json_encode($success));

         if($success){
            $addedData = array(
               'action'=> isset($userData['action']) ? $userData['action'] : 'visitor_added',  
               'user_id'=> isset($userData['userData']['ID']) ? $userData['userData']['ID'] : false,
               'user_mail'=> $email
            ); 
            do_action( 'bravepop_addded_to_list', 'sendy', $addedData );

            return true; 
         }else{
            return false;
         }
      }


   }

}
?>