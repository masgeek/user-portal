<?php
if ( ! class_exists( 'BravePop_SendGrid' ) ) {

   //SENDGRID API
   //https://sendgrid.api-docs.io/v3.0/lists/get-all-lists
   
   class BravePop_SendGrid {

      protected $api_key;

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['sendgrid']->api)  ? $integrations['sendgrid']->api  : '';
      }


      public function get_lists($apiKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         $args = array(  'headers' => array( 'Authorization' => 'Bearer ' . $apiKey  ));
         $response = wp_remote_get( 'https://api.sendgrid.com/v3/marketing/lists', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         if($data && isset($data->result)){
            $lists = $data->result;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->id) ? $list->id : '';
                  $listItem->name = isset($list->name) ? $list->name : '';
                  $listItem->count = isset($list->contact_count)  ? $list->contact_count : 0;
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
            //error_log('API KEY Missing!');
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


         $contact = new stdClass();
         $contact->email = $email;
         $contact->first_name = $firstname;
         $contact->last_name = $lastname;

         $reserved = array(
            "first_name", "last_name", "email","alternate_emails", "address_line_1","address_line_2","city",
            "state_province_region", "postal_code","country", "phone_number","whatsapp","line", "facebook",
            "unique_name", "email_domains","last_clicked", "last_opened", "last_emailed",
            "singlesend_id", "automation_id", "created_at", "updated_at", "contact_id"
         );
         //Add Custom Field Values
         if(count($customFields) > 0){
            $customFieldValues = array();
            foreach ($customFields as $key => $value) {
               if(in_array(trim($key), $reserved)){
                  $contact->$key = $value;
               }else{
                  $customFieldValues[trim($key)] = $value;
               }
            }
            if(count($customFieldValues) > 0){
               $contact->custom_fields = $customFieldValues;
            }
         }

         $addUserargs = array(
            'method' => 'PUT',
            'headers' => array(
               'content-type' => 'application/json',
               'Authorization' => 'Bearer ' . $this->api_key,
            ),
            'body' => wp_json_encode(array(
               'list_ids'        => array($list_id),
               'contacts'        => array($contact),
            ))
         );

         $response = wp_remote_post( 'https://api.sendgrid.com/v3/marketing/contacts', $addUserargs );
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         //error_log(wp_json_encode($response));

         if($data && isset($data->job_id)){
            $addedData = array(
               'action'=> isset($userData['action']) ? $userData['action'] : 'visitor_added',  
               'user_id'=> isset($userData['userData']['ID']) ? $userData['userData']['ID'] : false,
               'user_mail'=> $email, 'esp_user_id'=> 'none'
            ); 
            do_action( 'bravepop_addded_to_list', 'sendinblue', $addedData );

            return $data->job_id; 
         }else{
            return false;
         }


      }


   }

}
?>