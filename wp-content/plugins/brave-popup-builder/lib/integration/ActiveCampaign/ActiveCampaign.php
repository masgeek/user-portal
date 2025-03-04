<?php
if ( ! class_exists( 'BravePop_ActiveCampaign' ) ) {
   
   class BravePop_ActiveCampaign {

      protected $api_key;
      protected $api_url;

      function __construct() {

         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['activecampaign']->api)  ? $integrations['activecampaign']->api  : '';
         $this->api_url = isset($integrations['activecampaign']->url)  ? $integrations['activecampaign']->url  : '';
      }


      public function get_lists($apiURL='', $apiKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         $apiURL  = $apiURL ? $apiURL : $this->api_url;

         $args = array(
            'headers' => array(
               'Api-Token' => $apiKey
            )
         );
         $response = wp_remote_get( $apiURL.'/api/3/lists?limit=100', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         if($data && isset($data->lists)){
            $lists = $data->lists;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->id) ? $list->id : '';
                  $listItem->name = isset($list->name) ? $list->name : '';
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
         if(!$this->api_key || !$this->api_url){ 
            //error_log('API KEY or URL Missing!');
            return false;
         }
         $firstname = trim($fname); $lastname = '';

         //Convert Full name to firstname and lastname. 
         if(!$lname && $firstname && strpos($firstname, ' ') !== false){
            $splitted = explode(" ",$firstname);
            $firstname = $splitted[0] ? $splitted[0] : '';
            $lastname = $splitted[1] ? trim(str_replace($firstname, '', $fname)) : '';
            if(isset($splitted[2])){
               $lastname .= ''.$splitted[2];
            }
         }

         $contact = array("email"=> $email, "firstName"=> $firstname, "lastName"=> $lastname, "phone"=> $phone );
         
         //Add Custom Field Values
         if(count($customFields) > 0){
            $fieldValues = array();
            foreach ($customFields as $key => $value) {
               $value = !empty($value) && is_array($value) ?  implode(',', $value) : $value;
               $fieldValues[] = array('field'=> $key, 'value' => $value);
            }
            $contact['fieldValues'] = $fieldValues;
         }

         //First Add User in ActiveCampaign
         $addUserargs = array(
            'method' => 'POST',
            'headers' => array( 'content-type' => 'application/json', 'Api-Token' => $this->api_key ),
            'body' => wp_json_encode(array( 'contact' => $contact ))
         );

         $response = wp_remote_post( $this->api_url.'/api/3/contact/sync', $addUserargs );
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         // error_log('AC Create CONTACT: '. wp_json_encode($response));

         if($data && isset($data->contact) && isset($data->contact->id)){
            //Then Add User to a List in ActiveCampaign
            $user_id = $data->contact->id;

            $userToList = array(
               'method' => 'POST',
               'headers' => array( 'content-type' => 'application/json', 'Api-Token' => $this->api_key ),
               'body' => wp_json_encode(array(
                  'contactList' => array(
                     'list' => $list_id,
                     'contact' => $user_id,
                     'status' => 1
                  ),
               ))
            );
            $listresponse = wp_remote_post( $this->api_url.'/api/3/contactLists', $userToList );
            $listbody = wp_remote_retrieve_body( $listresponse );
            $listdata = json_decode( $listbody );

            // error_log('AC ADD CONTACT to LIST: '. wp_json_encode($listresponse));

            //Add Tags
            if(count($tags) > 0){
               foreach ($tags as $tagIndex => $tag) {
                  if(isset($tag->id)){
                     $userToTags = array(
                        'method' => 'POST', 
                        'headers' => array( 'content-type' => 'application/json', 'Api-Token' => $this->api_key ),
                        'body' => wp_json_encode(array(  'contactTag' => array( 'tag' => $tag->id, 'contact' => $user_id )  ))
                     );
                     $tagsresponse = wp_remote_post( $this->api_url.'/api/3/contactTags', $userToTags );
                     $tagsbody = wp_remote_retrieve_body( $tagsresponse );
                     $tagsdata = json_decode( $tagsbody );
                     //error_log(wp_json_encode($tagsresponse));
                  }
               }
            }

            //error_log(wp_json_encode($listdata->contacts));
            // $resCode = wp_remote_retrieve_response_code($listresponse);
            // isSuccess = $resCode >= 200 && $resCode < 300;
            if(is_wp_error( $listresponse ) === false && isset($listdata->contacts[0]->id)){
               $addedData = array(
                  'action'=> isset($userData['action']) ? $userData['action'] : 'visitor_added',  
                  'user_id'=> isset($userData['userData']['ID']) ? $userData['userData']['ID'] : false,
                  'user_mail'=> $email, 'esp_user_id'=> $listdata->contacts[0]->id
               ); 
               do_action( 'bravepop_addded_to_list', 'activecampaign', $addedData );
            }

            if(is_wp_error( $listresponse ) === false){
               return $listdata->contacts; 
            }else{
               return false;
            }

         }else{
            return false;
         }



      }


   }

}
?>