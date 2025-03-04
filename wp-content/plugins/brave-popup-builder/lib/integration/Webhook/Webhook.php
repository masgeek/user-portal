<?php
if ( ! class_exists( 'BravePop_Webhook' ) ) {
   
   class BravePop_Webhook {

      function __construct() {

      }

      public function post($url, $hooktype, $contentType, $fieldSettings, $current_user, $visitor_country, $visitor_ip ,$pageURL){
         if(!$url || !$fieldSettings){ return null; }
         $finalData = array();



         foreach ((array)$fieldSettings as $key => $field) {
            $defaultKey = isset($field->label) ? $field->label : ''; 
            $defaultKey = !$defaultKey && isset($field->placeholder) ? $field->placeholder : $defaultKey;

            $fieldKey = isset($field->uid) ? $field->uid : $defaultKey;
            $fieldValue = isset($field->value) && is_string($field->value) && $field->value ? wp_strip_all_tags($field->value) : '';
            $fieldValue = isset($field->value) && is_array($field->value) && $field->value ? wp_strip_all_tags(implode(", ", $field->value)) : $fieldValue;
            
            if(isset($field->value) && is_array($field->value) && $field->type === 'input' && $field->validation === 'name'){
               $defaultKey2 = isset($field->secondLabel) ? $field->secondLabel : ''; 
               $defaultKey2 = !$defaultKey && isset($field->secondPlaceholder) ? $field->secondPlaceholder : $defaultKey2;

               $fieldKey2 = isset($field->uidl) ? $field->uidl : $defaultKey2;
               $firstname = isset($field->value[0]) && $field->value[0] ? $field->value[0] : '';
               $lastname = isset($field->value[1]) && $field->value[1] ? $field->value[1] : '';

                $finalData[$fieldKey] = $firstname;
                $finalData[$fieldKey2] = $lastname;
            }else{
                $finalData[$fieldKey] = $fieldValue;
            }
         }

         if(!empty($current_user['username'])){
            $finalData['registered_user'] = "true";
            if(!empty($current_user['name'])){
               $finalData['registered_user_fullname'] = $current_user['name'];
            }
         }
         if(!empty($visitor_ip)){
            $finalData['sender_ip'] = $visitor_ip;
         }
         if(!empty($visitor_country)){
            $finalData['sender_country'] = $visitor_country;
         }
         if(!empty($pageURL)){
            $finalData['page_url'] = $pageURL;
         }

         $args = array(
            'method' => 'POST',
            'timeout' => 10,
            'headers' => ($hooktype === 'integromat' || $hooktype === 'integrately'  || ($hooktype === 'custom' && $contentType === 'application/json' )) ? array(  'content-type' => 'application/json') : array(),
            'body' => wp_json_encode($finalData),
            'data_format' => 'body',
         );


         $response = wp_remote_post( $url, $args );
         

         $body = wp_remote_retrieve_body( $response );
         $data = json_decode( $body );

         // error_log(wp_json_encode( $body ));
         
         if($data && isset($data->id)){
            return $data->id; 
         }else{
            return false;
         }

      }


   }

}
?>