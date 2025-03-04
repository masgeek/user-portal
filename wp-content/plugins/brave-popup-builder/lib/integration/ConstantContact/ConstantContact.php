<?php
if ( ! class_exists( 'BravePop_ConstantContact' ) ) {
   
   class BravePop_ConstantContact {

      protected $api_key;
      protected $access_key;

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['constantcontact']->api)  ? $integrations['constantcontact']->api  : '';
         $this->access_key = isset($integrations['constantcontact']->access)  ? $integrations['constantcontact']->access  : '';

      }


      public function get_lists($apiKey='', $accessKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         $accessKey  = $accessKey ? $accessKey : $this->access_key;
         $args = array(
            'headers' => array(
               'Authorization' => 'Bearer ' . $accessKey
            )
         );
         $response = wp_remote_get( 'https://api.constantcontact.com/v2/lists?api_key='.$apiKey, $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         $lists = $data;

         //error_log('Response BODY: '.$body);
         if($data && is_array($lists) && !isset($data[0]->error_key)){
            $finalLists = array();
            foreach ($lists as $key => $list) {
               $listItem = new stdClass();
               $listItem->id = isset($list->id) ? $list->id : '';
               $listItem->name = isset($list->name) ? $list->name : '';
               $listItem->count = isset($list->contact_count)  ? $list->contact_count : 0;
               $finalLists[] = $listItem;
            }
            return wp_json_encode($finalLists);
         }else{
            return false;
         }

      }


      public function add_to_lists($email, $list_id, $fname='', $lname='', $phone='', $customFields=array(), $tags=array(), $userData=array()){
         if(!$email || !$list_id){ return null; }
         if(!$this->api_key || !$this->access_key){ 
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
         
         $lists = array();
         $list = new stdClass();
         $list->id  = $list_id;
         $lists[] = $list;

         $emails = array();
         $theEmail = new stdClass();
         $theEmail->email_address  = $email;
         $emails[] = $theEmail;

         $getResponse = wp_remote_get( 'https://api.constantcontact.com/v2/contacts?email='.$email.'&api_key='.$this->api_key, array('method' => 'GET','timeout' => 30, 'headers' => array( 'Authorization' => 'Bearer ' . $this->access_key)) );
         $getBody = wp_remote_retrieve_body( $getResponse );
         $getData = json_decode( $getBody );

         //IF Contact Exist but is removed/unsubscribe, add them again.
         if($getData && isset($getData->results) && is_array($getData->results) && isset($getData->results[0])  && isset($getData->results[0]->id)){
            //error_log('User Found, updating User!!!');
            $exitingContact = $getData->results[0];
            $exitingContactID = isset($exitingContact->id) ? $exitingContact->id : '';
            $lists = isset($exitingContact->lists) ? $exitingContact->lists : array();
            $lists[] = $list;

            $updatedContact = array(
               'confirmed' =>  true,
               'status' =>  'ACTIVE',
               'source' =>  'web page',
               'id' =>  $exitingContactID,
               "lists"=> $lists,
               "email_addresses"=> isset($exitingContact->email_addresses) ? $exitingContact->email_addresses : $emails,
               "first_name"=> isset($exitingContact->first_name) ? $exitingContact->first_name : $firstname,
               "last_name"=> isset($exitingContact->last_name) ? $exitingContact->last_name : $lastname,
               "fax"=> isset($exitingContact->fax) ? $exitingContact->fax : '',
               "addresses"=> isset($exitingContact->addresses) ? $exitingContact->addresses : array(),
               "notes"=> isset($exitingContact->notes) ? $exitingContact->notes : array(),
               "prefix_name"=> isset($exitingContact->prefix_name) ? $exitingContact->prefix_name : '',
               "middle_name"=> isset($exitingContact->middle_name) ? $exitingContact->middle_name : '',
               "job_title"=> isset($exitingContact->job_title) ? $exitingContact->job_title : '',
               "company_name"=> isset($exitingContact->company_name) ? $exitingContact->company_name : '',
               "home_phone"=> isset($exitingContact->home_phone) ? $exitingContact->home_phone : '',
               "work_phone"=> isset($exitingContact->work_phone) ? $exitingContact->work_phone : '',
               "cell_phone"=> isset($exitingContact->cell_phone) ? $exitingContact->cell_phone : '',
               "custom_fields"=> isset($exitingContact->custom_fields) ? $exitingContact->custom_fields : '',
               "created_date"=> isset($exitingContact->created_date) ? $exitingContact->created_date : '',
            );

            $putArgs = array('method' => 'PUT','timeout' => 30, 'headers' => array( 'content-type' => 'application/json', 'Authorization' => 'Bearer ' . $this->access_key), 'body' => wp_json_encode($updatedContact)  );
            $putResponse = wp_remote_post( 'https://api.constantcontact.com/v2/contacts/'.$exitingContactID.'?action_by=ACTION_BY_VISITOR&api_key='.$this->api_key, $putArgs );
            $putBody = wp_remote_retrieve_body( $putResponse );
            //$putData = json_decode( $putBody );

            return true;
   
         }else{
            //User Does not exit, create User
            //error_log('User Does not exit, create User');
            $args = array(
               'method' => 'POST',
               'timeout' => 30,
               'headers' => array(
                  'content-type' => 'application/json',
                  'Authorization' => 'Bearer ' . $this->access_key
               ),
               'body' => wp_json_encode(array(
                  'email_addresses' => $emails,
                  'first_name'      => $firstname,
                  'last_name'       => $lastname,
                  'lists'           => $lists,
                  'confirmed'       =>  true
               ))
            );
            
            $response = wp_remote_post( 'https://api.constantcontact.com/v2/contacts?action_by=ACTION_BY_VISITOR&api_key='.$this->api_key, $args );
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body );

            //error_log($body);
            
            if($data && isset($data->id)){
               $addedData = array(
                  'action'=> isset($userData['action']) ? $userData['action'] : 'visitor_added',  
                  'user_id'=> isset($userData['userData']['ID']) ? $userData['userData']['ID'] : false,
                  'user_mail'=> $email, 'esp_user_id'=> $data->id
               ); 
               do_action( 'bravepop_addded_to_list', 'constantcontact', $addedData );
               return $data->id; 
            }else{
               return false;
            }
         }

      }


   }

}
?>