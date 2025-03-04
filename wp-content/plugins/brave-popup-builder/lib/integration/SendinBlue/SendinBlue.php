<?php
if ( ! class_exists( 'BravePop_SendinBlue' ) ) {

   class BravePop_SendinBlue {

      protected $api_key;

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['sendinblue']->api)  ? $integrations['sendinblue']->api  : '';
      }


      public function get_lists($apiKey=''){
         $apiKey  = $apiKey ? $apiKey : $this->api_key;
         $args = array(
            'headers' => array(
               'api-key' => $apiKey,
            )
         );
         $response = wp_remote_get( 'https://api.sendinblue.com/v3/contacts/lists?limit=50', $args );
         if( is_wp_error( $response ) ) {
            return false; // Bail early
         }

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         //error_log($body);
         if($data && isset($data->lists)){
            $lists = $data->lists;
            $finalLists = array();
            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list->id) ? $list->id : '';
                  $listItem->name = isset($list->name) ? $list->name : '';
                  $listItem->count = isset($list->totalSubscribers)  ? $list->totalSubscribers : 0;
                  $finalLists[] = $listItem;
               }
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


         $attributes = array( 'FIRSTNAME'=> $firstname, 'LASTNAME'=> $lastname );

         //Add Custom Field Values
         if(count($customFields) > 0){
            foreach ($customFields as $key => $value) {
               $attributes[trim($key)] = !empty($value) && is_array($value) ?  implode(',', $value) : $value;
            }
         }

         $APIURL = 'https://api.sendinblue.com/v3/contacts';
         $dataToSend = array(
            'listIds'   => array((int)$list_id),
            'email'     => $email,
            'attributes' => $attributes,
            'updateEnabled'=> true
         );

         //IF Double Optin
         if($doubleOptin && !empty($misc['sendinblue_template']) && !empty($misc['sendinblue_redirect'])){
            unset($dataToSend['listIds']); unset($dataToSend['updateEnabled']);
            $APIURL = 'https://api.sendinblue.com/v3/contacts/doubleOptinConfirmation';
            $dataToSend['includeListIds'] = array((int)$list_id);
            $dataToSend['templateId'] = (int)$misc['sendinblue_template']; 
            $dataToSend['redirectionUrl'] = esc_url($misc['sendinblue_redirect']);
         }

         $addUserargs = array(
            'method' => 'POST',
            'timeout' => 30,
            'headers' => array(
               'content-type' => 'application/json',
               'api-key' => $this->api_key,
            ),
            'body' => wp_json_encode($dataToSend)
         );


         $response = wp_remote_post( $APIURL, $addUserargs );
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         // error_log('##### SNB Response: '.wp_json_encode($response));
         if( !is_wp_error( $response ) && ( isset($response['response']['code']) && ($response['response']['code'] === 204 || $response['response']['code'] === 201)) ){
            $addedData = array(
               'action'=> isset($userData['action']) ? $userData['action'] : 'visitor_added',  
               'user_id'=> isset($userData['userData']['ID']) ? $userData['userData']['ID'] : false,
               'user_mail'=> $email, 'esp_user_id'=> isset($data->id) ? isset($data->id) : ''
            ); 
            do_action( 'bravepop_addded_to_list', 'sendinblue', $addedData );

            return true; 
         }else{
            return false;
         }


      }


   }

}
?>