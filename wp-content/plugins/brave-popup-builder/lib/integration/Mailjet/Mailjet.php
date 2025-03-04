<?php
if ( ! class_exists( 'BravePop_Mailjet' ) ) {
   
   class BravePop_Mailjet {

      protected $api_key;
      protected $secret;

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['mailjet']->api)  ? $integrations['mailjet']->api  : '';
         $this->secret = isset($integrations['mailjet']->secret)  ? $integrations['mailjet']->secret  : '';

      }


      public function get_lists($apiKey='', $secretKey=''){
         $apiKey     = $apiKey ? $apiKey : $this->api_key;
         $secretKey  = $secretKey ? $secretKey : $this->secret;

         if(!$apiKey || !$secretKey){ return false;}

         $args = array(
            'headers' => array(
               'Authorization' => 'Basic ' . base64_encode( $apiKey.':'.$secretKey )
            )
         );
         $response = wp_remote_get( 'https://api.mailjet.com/v3/REST/contactslist?limit=200', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         if($data && isset($data->Data)){
            $lists = $data->Data;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->ID) ? $list->ID : '';
                  $listItem->name = isset($list->Name) ? $list->Name : '';
                  $listItem->count = isset($list->SubscriberCount)  ? $list->SubscriberCount : 0;
                  $finalLists[] = $listItem;
               }
            }
            return wp_json_encode($finalLists);
         }else{
            return false;
         }

      }


      public function add_to_lists($email, $list_id, $fname='', $lname='', $phone='', $customFields=array(), $tags=array(), $userData=array()){
         if(!$email || !$list_id){ return false; }
         if(!$this->api_key || !$this->secret){ 
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
         

         $args = array(
            'method' => 'POST',
            'headers' => array(
               'content-type' => 'application/json',
               'Authorization' => 'Basic ' . base64_encode( $this->api_key.':'.$this->secret )
            ),
            'body' => wp_json_encode(array(
               'Email'     => $email,
               'Name'      => trim($fullname),
               'Action'    => 'addforce'
            ))
         );

         $response = wp_remote_post( 'https://api.mailjet.com/v3/REST/contactslist/' . $list_id . '/managecontact/', $args );
         

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         //error_log(wp_json_encode($data));
         if($data && isset($data->Data) && isset($data->Data[0]->ContactID)){

            if(class_exists('BravePop_Mailjet_Advanced') && count($customFields) > 0){
               $mailjetAdv = new BravePop_Mailjet_Advanced();
               $mailjetAdv->add_fields($customFields, $data->Data[0]->ContactID);
            }

            $addedData = array(
               'action'=> isset($userData['action']) ? $userData['action'] : 'visitor_added',  
               'user_id'=> isset($userData['userData']['ID']) ? $userData['userData']['ID'] : false,
               'user_mail'=> $email, 'esp_user_id'=> isset($data->Data[0]->ContactID) ? $data->Data[0]->ContactID : ''
            ); 
            do_action( 'bravepop_addded_to_list', 'mailjet', $addedData );

            return $data->Data; 
         }else{
            return false;
         }

      }


   }

}
?>