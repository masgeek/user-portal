<?php
if ( ! class_exists( 'BravePop_Ontraport' ) ) {
   
   class BravePop_Ontraport {

      protected $api_key;
      protected $api_secret;

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['ontraport']->api)  ? $integrations['ontraport']->api  : '';
         $this->api_secret = isset($integrations['ontraport']->secret)  ? $integrations['ontraport']->secret  : '';
      }

      public function get_lists($apiKey='', $secretKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         $secretKey  = $secretKey ? $secretKey : $this->api_secret;
         if(!$apiKey || !$secretKey){ return  error_log( 'Ontraport API or APP ID missing!' );}


         $args = array( 'headers' => array( 'Api-Key' => $this->api_key,'Api-Appid' => $this->api_secret ) );
         $response = wp_remote_get( 'https://api.ontraport.com/1/CampaignBuilderItems?listFields=ids%2Cname', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
     
         $finalLists = array();
         
         if(isset($data->data) && is_array($data->data)){
            $lists = $data->data;
            foreach ($lists as $key => $list) {
               $listItem = new stdClass();
               $listItem->id = isset($list->id) ? $list->id : '';
               $listItem->name = isset($list->name) ? $list->name : '';
               $listItem->count = 0;
               $finalLists[] = $listItem;
            }

            return wp_json_encode($finalLists);
         }else{
            return false;
         }


      }

      public function add_to_lists($email, $list_id, $fname='', $lname='', $phone='', $customFields=array(), $tags=array(), $userData=array()){
         if(!$email || !$list_id){ return null; }
         if(!$this->api_key || !$this->api_secret){ 
           // error_log('Ontraport API KEY or SECRET Missing!');
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
         
         $contact = array( 'email' => $email, 'firstname'  => $firstname, 'lastname'  => $lastname );
         
         //Add Custom Field Values
         if(count($customFields) > 0){
            foreach ($customFields as $key => $value) {
               $contact[trim($key)] = !empty($value) && is_array($value) ?  implode(',', $value) : $value;
            }
         }

         $args = array( 'method' => 'POST', 'timeout' => 30, 'headers' => array( 'Api-Key' => $this->api_key, 'Api-Appid' => $this->api_secret  ), 'body' => wp_json_encode($contact) );

         // https://api.ontraport.com/doc/#merge-or-create-a-contact
         $response = wp_remote_post( 'https://api.ontraport.com/1/Contacts/saveorupdate', $args );
         
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         //error_log(wp_json_encode($response));

         if(isset($data->data) && (isset($data->data->id) || (isset($data->data->attrs) && isset($data->data->attrs->id)) )){
            $esp_user_id = isset($data->data->id) ? $data->data->id : false;
            if( (isset($data->data->attrs) && isset($data->data->attrs->id))){   $esp_user_id = $data->data->attrs->id;  }

            $addedtoList = $this->add_to_list_action($email, $esp_user_id, $list_id, $userData);

            return $addedtoList; 
         }else{
            return false;
         }
      }


      public function add_to_list_action($email, $espID, $list_id, $userData){
            //error_log('#### ESP ID: '. $espID.' | List ID: '.$list_id);
            if(!$espID || !$list_id){ return false; }
            //https://api.ontraport.com/doc/#subscribe-an-object-to-a-campaign-or-sequence
            $subargs = array('method' => 'PUT', 'timeout' => 30,
               'headers' => array( 'Api-Key' => $this->api_key, 'Api-Appid' => $this->api_secret, ),
               'body' => wp_json_encode(array('objectID'=> 0,'ids'=>$espID, 'sub_type'=> 'Campaign', 'add_list'=> $list_id ))
            );
            $subResponse = wp_remote_post( 'https://api.ontraport.com/1/objects/subscribe', $subargs );
            $body = wp_remote_retrieve_body( $subResponse );
            $data = json_decode( $body );
            //error_log(wp_json_encode($subResponse));

            if(isset($data->account_id)){
               $addedData = array(
                  'action'=> isset($userData['action']) ? $userData['action'] : 'visitor_added',  
                  'user_id'=> isset($userData['userData']['ID']) ? $userData['userData']['ID'] : false,
                  'user_mail'=> $email, 'esp_user_id'=> $espID
               ); 
               do_action( 'bravepop_addded_to_list', 'ontraport', $addedData );

               return true;

            }else{

               return false;

            }

      }


   }

}
?>