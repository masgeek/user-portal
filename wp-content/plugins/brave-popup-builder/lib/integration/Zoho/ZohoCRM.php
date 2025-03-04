<?php
if ( ! class_exists( 'BravePop_ZohoCRM' ) ) {
   
   class BravePop_ZohoCRM {

      function __construct() {
         $braveSettings = get_option('_bravepopup_settings');
         $integrations = $braveSettings && isset($braveSettings['integrations']) ? $braveSettings['integrations'] : array() ;
         $this->api_key = isset($integrations['zoho']->api)  ? $integrations['zoho']->api  : '';
         $this->api_secret = isset($integrations['zoho']->secret)  ? $integrations['zoho']->secret  : '';
         $this->redirect = isset($integrations['zoho']->url)  ? $integrations['zoho']->url  : '';
         $this->refresh_token = isset($integrations['zoho']->refresh)  ? $integrations['zoho']->refresh  : '';
         $this->domain = isset($integrations['zoho']->domain)  ? $integrations['zoho']->domain  : 'com';
      }

      public function get_access_token($apiKey='', $apiSecret='', $refresh_token='',$domain=''){
         $apiKey = $this->api_key ? $this->api_key : $apiKey;
         $apiSecret = $this->api_secret ? $this->api_secret : $apiSecret;
         $refresh_token = $this->refresh_token ? $this->refresh_token : $refresh_token;
         $domain = $this->domain ? $this->domain : $domain;
         if(!$apiKey || !$apiSecret || !$refresh_token || !$domain){ 
            return false;
         }
         //error_log('REFRESH TOKEN: '.$this->refresh_token);
         $args = array( 'method' => 'POST','headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded'  ) );
         $response = wp_remote_post( 'https://accounts.zoho.'.$domain.'/oauth/v2/token?refresh_token='.$refresh_token.'&client_id='.$apiKey.'&client_secret='.$apiSecret.'&grant_type=refresh_token', $args );
         
         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );
         //error_log('ACCESS TOKEN: '.$body);
         return isset($data->access_token) ? $data->access_token : false;
      }



      public function add_to_lists($email, $list_id='', $fname='', $lname='', $phone='', $customFields=array(), $tags=array(), $userData=array()){
         //error_log('#Zoho CRM ADD to List: '.$this->api_key.' '.$this->api_secret.' '.$this->refresh_token.' ');
         if(!$email){ return null; }
         if(!$this->api_key || !$this->api_secret || !$this->refresh_token){ 
            // error_log('API KEY, SECRET or Access/Refresh Token Missing!');
            return false;
         }
         
         $firstname = trim($fname);
         $lastname = trim($lname);
         $fullname = $firstname;

         //If FullName, and no last name, extract firstname and lastname from fullname
         if(!$lastname && ( strpos($firstname, ' ') !== false)){
            $fullname_parts = preg_split('/\s+/', $firstname);
            $firstname = $fullname_parts[0] ? $fullname_parts[0] : $firstname;
            $lastname = $fullname_parts[1] ? $fullname_parts[1] : $firstname;
         }

         $lastname = $lastname ? $lastname : $firstname; //Last Name is Mandatory
         
         //Contact Data
         $contact = new stdClass();
         $contact->First_Name = $firstname;
         $contact->Last_Name = $lastname;
         $contact->Email = $email;
         $contact->Lead_Source = "Website Popup";
         if($phone){   $contact->Phone = $phone; }

         //Add Custom Field Values
         if(count($customFields) > 0){
            foreach ($customFields as $key => $value) {
               $fieldKey = str_replace(' ', '_', $key);
               $contact->$fieldKey = !empty($value) && is_array($value) ?  implode(',', $value) : $value;
            }
         }

         $access_token  = $this->get_access_token($this->api_key, $this->api_secret, $this->refresh_token, $this->domain); 

         if($access_token){
            $args = array( 
               'method' => 'POST',
               'headers' => array( "Authorization"=> 'Zoho-oauthtoken ' . $access_token   ),
               'body'=>'{
                  "data": ['.wp_json_encode($contact).']
                  }'
            );

            $response = wp_remote_post( 'https://www.zohoapis.'.$this->domain.'/crm/v2/Leads', $args);
            $body = wp_remote_retrieve_body( $response );
            $data = json_decode( $body );
            // error_log('$body->status: '.wp_json_encode($data->data[0]->status));
            //error_log('$body: '.wp_json_encode($body));
            if(isset($data->data[0]->status) && $data->data[0]->status === 'success'){
               //error_log(wp_json_encode($response['response']['code']));
               return $data->data[0]->status;
            }else{
               return false;
            }

         }else{
            // error_log('NO ACCESS TOKEN');
            return false;
         }

      }


   }

}
?>