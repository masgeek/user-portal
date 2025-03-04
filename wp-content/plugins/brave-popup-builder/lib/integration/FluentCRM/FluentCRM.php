<?php
if ( ! class_exists( 'BravePop_FluentCRM' ) ) {

   class BravePop_FluentCRM {


      public function get_lists(){
         if(function_exists('fluentcrm_get_option')){
            $fluentLists = FluentCrmApi('lists')->all();

            //error_log('TNP Lists: '.wp_json_encode($lists));
            $noListItem = new stdClass();
            $noListItem->id = 'no_list';
            $noListItem->name = 'No List'; 
            $finalLists = array($noListItem);

            foreach ($fluentLists as $key => $list) {
               $listItem = new stdClass();
               $listItem->id =  $list->id;
               $listItem->name = !empty($list->title) ? $list->title: ''; 
               $listItem->count =  0;
               $finalLists[] = $listItem;
            }

            //error_log(wp_json_encode($finalLists));
            return wp_json_encode($finalLists);
         }
         
      }

      public function get_fields(){
         if(function_exists('fluentcrm_get_option')){
            $theData = array('fields'=>array(), 'tags' => array());

            //Tags
            $all_Tags = FluentCrmApi('tags')->all();
            foreach ($all_Tags as $key => $tag) {
               if (isset($tag->id)) {
                  $tagItem = new stdClass();
                  $tagItem->id =  $tag->id;
                  $tagItem->name = $tag->title; 
                  $theData['tags'][] = $tagItem;
               }
            }

            //Custom Fields
            $finalFields = array();
            $default_fields = [
               'prefix'         => __('Prefix', 'bravepop'),
               'phone'          => __('Phone', 'bravepop'),
               'address_line_1' => __('Address Line 1', 'bravepop'),
               'address_line_2' => __('Address Line 2', 'bravepop'),
               'city'           => __('City', 'bravepop'),
               'state'          => __('State', 'bravepop'),
               'country'        => __('Country', 'bravepop'),
               'postal_code'    => __('Postal Code', 'bravepop'),
               'date_of_birth'  => __('Date of Birth', 'bravepop')
            ];

            foreach ($default_fields as $key => $fieldVal) {
               $fieldItem = new stdClass();
               $fieldItem->id =  $key;
               $fieldItem->name = $fieldVal; 
               $finalFields[] = $fieldItem;
            }

            $custom_fields = fluentcrm_get_option('contact_custom_fields', []);
            foreach ($custom_fields as $key => $field) {
               $fieldItem = new stdClass();
               $fieldItem->id =  $field['slug'];
               $fieldItem->name = $field['label']; 
               $finalFields[] = $fieldItem;
            }
            
            $theData['fields'] = $finalFields;
            return wp_json_encode($theData);
         }
         
      }

      public function add_to_lists($email, $list_id, $fname='', $lname='', $phone='', $customFields=array(), $tags=array(), $userData=array(), $doubleOptin=false, $misc=array()){
         if(!function_exists('fluentcrm_get_option')){ return null; }
         if(!$email){ return null; }

         $firstname = trim($fname);
         $lastname = trim($lname);

         //Convert Full name to firstname and lastname. 
         if(!$lastname && $firstname && strpos($firstname, ' ') !== false){
            $splitted = explode(" ",$firstname);
            $firstname = $splitted[0] ? trim($splitted[0]) : '';
            $lastname = $splitted[1] ? trim(str_replace($firstname, '', $fname)) : '';
         }

         $contact = array('email'=> $email, 'first_name'=> trim($firstname), 'last_name'=> trim($lastname), 'lists' => [$list_id], 'status'=> !$doubleOptin ? 'subscribed' : 'pending', 'custom_values' => [], 'tags' => []);

         //Add Custom Field Values
         if(count($customFields) > 0){
            foreach ($customFields as $key => $value) {
               if(!empty($value)){
                  $contact['custom_values'][$key] = !empty($value) && is_array($value) ?  implode(',', $value) : $value;
               }
            }
         }


         if(count($tags) > 0){
            foreach ($tags as $key => $tagItem) {
               if(!empty($tagItem->id)){
                  $contact['tags'][] = intval($tagItem->id);
               }
            }
         }

         //error_log(wp_json_encode($contact));

         $FCrmApi = FluentCrmApi('contacts');

         $response = $FCrmApi->createOrUpdate($contact);
         if ($response->status == 'pending') {
            $response->sendDoubleOptinEmail();
         }

         if(is_wp_error($response)){
            return false;
         }else{
            return true;
         }

      }


   }

}
?>