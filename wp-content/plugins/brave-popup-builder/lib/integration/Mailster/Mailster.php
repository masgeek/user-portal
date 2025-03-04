<?php
if ( ! class_exists( 'BravePop_Mailster' ) ) {

   class BravePop_Mailster {


      public function get_lists(){
         if(function_exists('mailster')){

            $mailsterLists = mailster( 'lists' )->get();
            foreach ($mailsterLists as $key => $list) {
               $listItem = new stdClass();
               $listItem->id =  $list->ID;
               $listItem->name = !empty($list->name) ? $list->name: ''; 
               $listItem->count =  0;
               $finalLists[] = $listItem;
            }

            //error_log(wp_json_encode($finalLists));
            return wp_json_encode($finalLists);
         }
         
      }

      public function get_fields(){
         if(function_exists('mailster')){
            $theData = array('fields'=>array(), 'tags' => array());
            $profileFields = mailster()->get_custom_fields();
            $finalFields = array();

            foreach ($profileFields as $key => $field) {
               $fieldItem = new stdClass();
               $fieldItem->id =  $key;
               $fieldItem->name = $field['name']; 
               $finalFields[] = $fieldItem;
            }
            
            $theData['fields'] = $finalFields;
            return wp_json_encode($theData);
         }
         
      }

      public function add_to_lists($email, $list_id, $fname='', $lname='', $phone='', $customFields=array(), $tags=array(), $userData=array(), $doubleOptin=false, $misc=array()){
         if(!function_exists('mailster')){ return null; }
         if(!$email){ return null; }

         $firstname = trim($fname);
         $lastname = trim($lname);

         //Convert Full name to firstname and lastname. 
         if(!$lastname && $firstname && strpos($firstname, ' ') !== false){
            $splitted = explode(" ",$firstname);
            $firstname = $splitted[0] ? trim($splitted[0]) : '';
            $lastname = $splitted[1] ? trim(str_replace($firstname, '', $fname)) : '';
         }

         $contact = array('email'=> $email, 'firstname'=> $firstname, 'lastname'=> $lastname, 'status'=> $doubleOptin ? 0 : 1);

         //Add Custom Field Values
         if(count($customFields) > 0){
            foreach ($customFields as $key => $value) {
               if(!empty($value)){
                  $contact[$key] = !empty($value) && is_array($value) ?  implode(',', $value) : $value;
               }
            }
         }

         //error_log(wp_json_encode($contact));
         $subscriber_id = mailster( 'subscribers' )->add( $contact , true );
         $userAdded = mailster( 'subscribers' )->assign_lists( $subscriber_id, $list_id, false);
         //error_log(wp_json_encode($subscriber_id));
      
         if(is_wp_error($userAdded)){
            return false;
         }else{
            return true;
         }

      }


   }

}
?>