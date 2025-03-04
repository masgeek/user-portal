<?php
if ( ! class_exists( 'BravePop_Mailchimp' ) ) {
   
   class BravePop_Mailchimp {

      protected $api_key;
      protected $dc;

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['mailchimp']->api)  ? $integrations['mailchimp']->api  : '';
         $this->dc = substr($this->api_key,strpos($this->api_key,'-')+1); 
      }

      public function get_lists($apiKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         $dc      = $apiKey ?substr($apiKey,strpos($apiKey,'-')+1) : $this->dc;

         $args = array(
            'headers' => array(
               'Authorization' => 'Basic ' . base64_encode( 'user:'.  $apiKey )
            )
         );
         $response = wp_remote_get( 'https://'.$dc.'.api.mailchimp.com/3.0/lists?count=200', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         $lists = $data->lists;
         $finalLists = array();
         //error_log($apiKey . $body);
         
         if($lists && is_array($lists)){
            
            foreach ($lists as $key => $list) {
               $listItem = new stdClass();
               $listItem->id = isset($list->id) ? $list->id : '';
               $listItem->name = isset($list->name) ? $list->name : '';
               $listItem->count = isset($list->stats) && isset($list->stats->member_count)  ? $list->stats->member_count : 0;
               $finalLists[] = $listItem;
            }

            //error_log(wp_json_encode($finalLists));
            return wp_json_encode($finalLists);
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

         $status = $doubleOptin ? 'pending' : 'subscribed'; // subscribed, cleaned, pending
         
         $firstname = trim($fname);
         $lastname = trim($lname);

         //Convert Full name to firstname and lastname. 
         if(!$lastname && $firstname && strpos($firstname, ' ') !== false){
            $splitted = explode(" ",$firstname);
            $firstname = $splitted[0] ? trim($splitted[0]) : '';
            $lastname = $splitted[1] ? trim(str_replace($firstname, '', $fname)) : '';
         }
         
         $contact = array(
            'email_address' => $email,
            'merge_fields'  => array('FNAME' => $firstname, 'LNAME' => $lastname ),
            'status'        => $status
         );
         
         //Add IP Address
         if(class_exists('BravePop_Geolocation')){
            $ipAddress = bravepop_getVisitorIP();
            if(filter_var($ipAddress, FILTER_VALIDATE_IP)){
               $contact['ip_signup'] = $ipAddress;
            }
         }
         //Add Custom Field Values
         if(count($customFields) > 0){
            foreach ($customFields as $key => $value) {
               $contact['merge_fields'][trim($key)] = !empty($value) && is_array($value) ? implode(", ", $value) : $value;
            }
         }
         //Add Tags
         if(isset($tags[0]) && !empty($tags[0])){
            $tagsArray = explode(",",$tags[0]);
            if(count($tagsArray) > 0){
               $contact['tags'] = $tagsArray;
            }
         }

         //Add Groups
         if(isset($misc['mailchimp_groups']) && is_array($misc['mailchimp_groups']) && count($misc['mailchimp_groups']) > 0){
            $finalGroups = array();
            foreach ($misc['mailchimp_groups'] as $key => $interesID) {
               $finalGroups[$interesID] = true;
            }
            if(count($finalGroups) > 0){
               $contact['interests'] = $finalGroups;
            }
         }

         $args = array(
            'method' => 'PUT',
            'timeout' => 30,
            'headers' => array(
               'Authorization' => 'Basic ' . base64_encode( 'user:'. $this->api_key )
            ),
            'body' => wp_json_encode($contact)
         );

         // https://mailchimp.com/developer/api/marketing/list-members/add-member-to-list/
         $response = wp_remote_post( 'https://' . $this->dc . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5(strtolower($email)), $args );
         

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         //error_log('######## MC Result: '.wp_json_encode($response));
         $addedResult = new stdClass();
         if($data && isset($data->id)){
               if(isset($contact['tags']) && is_array($contact['tags']) && count($contact['tags']) > 0){
                  //Update Tags If user already Exist
                  //https://mailchimp.com/developer/marketing/guides/organize-contacts-with-tags/
                  $tagsFinal = array();
                  foreach ($contact['tags'] as $key => $tag) {   $tagsFinal[] = array('name'=> $tag, 'status'=>'active'); }
                  $tagargs = array( 
                     'method' => 'POST', 'timeout' => 30, 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( 'user:'. $this->api_key ) ),
                     'body' => wp_json_encode(array('tags'=> $tagsFinal))
                  );
                  $tagsResponse = wp_remote_post( 'https://' . $this->dc . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5(strtolower($email)).'/tags', $tagargs );
               }

               $addedData = array(
                  'action'=> isset($userData['action']) ? $userData['action'] : 'visitor_added',  
                  'user_id'=> isset($userData['userData']['ID']) ? $userData['userData']['ID'] : false,
                  'user_mail'=> $email, 'esp_user_id'=> $data->id
               ); 
               do_action( 'bravepop_addded_to_list', 'mailchimp', $addedData );

            return true; 
            $addedResult->status = true;
         }else{
            $addedResult->status = false;
            $addedResult->error = isset($data->detail) ? $data->detail : '';
            return false;
         }
      }


   }

}
?>