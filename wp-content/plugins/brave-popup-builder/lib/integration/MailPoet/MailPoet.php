<?php
if ( ! class_exists( 'BravePop_MailPoet' ) ) {

   class BravePop_MailPoet {

      protected $api_key;

      function __construct() {
         $this->api_key = class_exists(\MailPoet\API\API::class)  ? \MailPoet\API\API::MP('v1') : '';
      }


      public function get_lists(){
         if($this->api_key){
            $lists = $this->api_key->getLists();
            $finalLists = array();

            if($lists && is_array($lists)){
               foreach ($lists as $key => $list) {
                  $listItem = new stdClass();
                  $listItem->id = isset($list['id']) ? $list['id'] : '';
                  $listItem->name = isset($list['name']) ? $list['name'] : '';
                  $listItem->count = isset($list->contact_count)  ? $list->contact_count : 0;
                  $finalLists[] = $listItem;
               }
            }
            // error_log(wp_json_encode($finalLists));
            return wp_json_encode($finalLists);
         }
         
      }


      public function add_to_lists($email, $list_id, $fname='', $lname='', $phone=''){
         if(!class_exists(\MailPoet\API\API::class)){ return null; }
         if(!$email || !$list_id){ return null; }
         if(!$this->api_key){   return false;   }

         $firstname = trim($fname);
         $lastname = trim($lname);

         //Convert Full name to firstname and lastname. 
         if(!$lastname && $firstname && strpos($firstname, ' ') !== false){
            $splitted = explode(" ",$firstname);
            $firstname = $splitted[0] ? $splitted[0] : '';
            $lastname = $splitted[1] ? trim(str_replace($firstname, '', $fname)) : '';
         }

         $subscriber = [];
         $subscriber['email'] = $email;
         if($firstname){  $subscriber['first_name'] = $firstname;  }
         if($lastname){  $subscriber['last_name'] = $lastname;  }

         // Check if subscriber exists. If subscriber doesn't exist an exception is thrown
         $get_subscriber = '';
         try {
            $get_subscriber = $this->api_key->getSubscriber($subscriber['email']);
         } catch (\Exception $e) {}

         try {
            if (!$get_subscriber) {
               // Subscriber doesn't exist let's create one
               $get_subscriber = $this->api_key->addSubscriber($subscriber, [intval($list_id)]);
            } else {
               // In case subscriber exists just add him to new lists
               $this->api_key->subscribeToLists($subscriber['email'], [intval($list_id)]);
            }
            return true;
         } catch (\Exception $e) {
            $error_message = $e->getMessage(); 
            return true;
            //error_log($error_message);
            //return false;
         }

      }


   }

}
?>