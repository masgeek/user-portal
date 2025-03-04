<?php


function bravepop_get_integration_lists($service='', $apiKey='', $secretKey='', $accessToken='', $apiURL='', $refresh='', $domain=''){
   //error_log('get_integration_lists: '.$service . $apiKey . $secretKey . $accessToken. $apiURL .$refresh);
   if(!$service){ return false; }
   $currentSettings = get_option('_bravepopup_settings');
   $currentIntegrations = $currentSettings && isset($currentSettings['integrations']) ? $currentSettings['integrations'] : array() ;

   if(!$apiKey && !$secretKey && !$accessToken && !$apiURL && !$refresh){
      $apiKey = isset($currentIntegrations[$service]->api)  ? $currentIntegrations[$service]->api  : '';
      $secretKey = isset($currentIntegrations[$service]->secret)  ? $currentIntegrations[$service]->secret  : '';
      $accessToken = isset($currentIntegrations[$service]->access)  ? $currentIntegrations[$service]->access  : '';
      $apiURL = isset($currentIntegrations[$service]->url)  ? $currentIntegrations[$service]->url  : '';
      $refresh = isset($currentIntegrations[$service]->refresh)  ? $currentIntegrations[$service]->refresh  : '';
      $domain = isset($currentIntegrations[$service]->domain)  ? $currentIntegrations[$service]->domain  : '';
   }

   if($service === 'mailchimp') { 
      $mailchimp = new BravePop_Mailchimp();  
      $lists = $mailchimp->get_lists($apiKey);
      return $lists;
   }
   if($service === 'mailjet')   { 
      $mailjet =   new BravePop_Mailjet();
      $lists = $mailjet->get_lists($apiKey, $secretKey);
      return $lists;
   }
   if($service === 'sendgrid')   { 
      $sendgrid =   new BravePop_SendGrid();
      $lists = $sendgrid->get_lists($apiKey);
      return $lists;
   }
   if($service === 'sendinblue')   { 
      $sendinblue =   new BravePop_SendinBlue();
      $lists = $sendinblue->get_lists($apiKey);
      return $lists;
   }
   if($service === 'hubspot')   { 
      $hubspot =   new BravePop_Hubspot();
      $lists = $hubspot->get_lists($apiKey);
      return $lists;
   }
   if($service === 'activecampaign')   { 
      $activeCamp =   new BravePop_ActiveCampaign();
      $lists = $activeCamp->get_lists($apiURL, $apiKey);
      return $lists;
   }
   if($service === 'convertkit')   { 
      $convertkit =   new BravePop_ConvertKit();
      $lists = $convertkit->get_lists($apiKey);
      return $lists;
   }
   if($service === 'constantcontact')   { 
      $constantcontact =   new BravePop_ConstantContact();
      $lists = $constantcontact->get_lists($apiKey, $secretKey, $accessToken);
      return $lists;
   }
   if($service === 'getresponse')   { 
      $getresponse =   new BravePop_GetResponse();
      $lists = $getresponse->get_lists( $accessToken);
      return $lists;
   }
   if($service === 'aweber')   { 
      $aweber =   new BravePop_Aweber();
      $lists = $aweber->get_lists($refresh);
      return $lists;
   }
   if($service === 'zoho')   { 
      $zoho =   new BravePop_Zoho();
      $lists = $zoho->get_lists( $apiKey, $secretKey, $refresh, $domain);
      return $lists;
   }
   if($service === 'mailerlite')   { 
      $mailjet =   new BravePop_MailerLite();
      $lists = $mailjet->get_lists($apiKey);
      return $lists;
   }
   if($service === 'moosend')   { 
      $moosend =   new BravePop_Moosend();
      $lists = $moosend->get_lists($apiKey);
      return $lists;
   }
   if($service === 'pabbly')   { 
      $pabbly =   new BravePop_Pabbly();
      $lists = $pabbly->get_lists($apiKey);
      return $lists;
   }
   if($service === 'klaviyo')   { 
      $klaviyo =   new BravePop_Klaviyo();
      $lists = $klaviyo->get_lists($apiKey);
      return $lists;
   }
   if($service === 'ontraport')   { 
      $ontraport =   new BravePop_Ontraport();
      $lists = $ontraport->get_lists($apiKey, $secretKey);
      return $lists;
   }
   if($service === 'sendpulse')   { 
      $sendpulse =   new BravePop_SendPulse();
      $lists = $sendpulse->get_lists($apiKey, $secretKey);
      return $lists;
   }
   if($service === 'campaignmonitor')   { 
      $campaignmonitor =   new BravePop_CampaignMonitor();
      $lists = $campaignmonitor->get_lists($apiKey, $secretKey);
      return $lists;
   }
   if($service === 'mailpoet' && class_exists(\MailPoet\API\API::class)) { 
      $mailpoet = new BravePop_MailPoet();  
      $lists = $mailpoet->get_lists();
      return $lists;
   }
   if($service === 'tnp' && class_exists('TNP')) { 
      $the_newsletter_plugin = new BravePop_TNP();  
      $lists = $the_newsletter_plugin->get_lists();
      return $lists;
   }
   if($service === 'mailster' && function_exists('mailster')) { 
      $the_newsletter_plugin = new BravePop_Mailster();  
      $lists = $the_newsletter_plugin->get_lists();
      return $lists;
   }
   if($service === 'fluentcrm' && function_exists('fluentcrm_get_option')) { 
      $the_newsletter_plugin = new BravePop_FluentCRM();  
      $lists = $the_newsletter_plugin->get_lists();
      return $lists;
   }
   if($service === 'omnisend')   { 
      $omnisend =   new BravePop_Omnisend();
      $lists = $omnisend->get_lists($apiKey);
      return $lists;
   }
   if($service === 'sender')   { 
      $sendgrid =   new BravePop_Sender();
      $lists = $sendgrid->get_lists($apiKey);
      return $lists;
   }
   if(function_exists('bravepop_external_integration_get_list')){
      return bravepop_get_external_integration_list($service, $apiKey, $secretKey, $accessToken, $apiURL);
   }
}

function bravepop_update_newsletter_integrations( $integrations ){
   $currentSettings = get_option('_bravepopup_settings');
   $currentIntegrations = $currentSettings && isset($currentSettings['integrations']) ? $currentSettings['integrations'] : array() ;
   
   $updatedIntegrations = $currentIntegrations;
   if($integrations){
      $decodedIntegration = json_decode($integrations);
      
      if(isset($decodedIntegration->service)){
         
         $apiSettings = new stdClass();
         $apiSettings->api = isset($decodedIntegration->api) ? $decodedIntegration->api : '';
         $apiSettings->secret = isset($decodedIntegration->secret) ? $decodedIntegration->secret : '';
         $apiSettings->access = isset($decodedIntegration->access) ? $decodedIntegration->access : '';
         $apiSettings->url = isset($decodedIntegration->url) ? $decodedIntegration->url : '';
         $apiSettings->refresh = isset($decodedIntegration->refresh) ? $decodedIntegration->refresh : '';
         $apiSettings->domain = isset($decodedIntegration->domain) ? $decodedIntegration->domain : '';
         if(isset($decodedIntegration->doubleoptin)){
            $apiSettings->doubleoptin = $decodedIntegration->doubleoptin;
         }

         $updatedIntegrations[$decodedIntegration->service] = $apiSettings;
         $validateIntegration = false;
         
         if($decodedIntegration->service === 'zoho'){
            $zoho =   new BravePop_Zoho();
            $accessToken = $zoho->get_access_token( $apiSettings->api, $apiSettings->secret, $apiSettings->refresh, $apiSettings->domain);
            $validateIntegration = $accessToken;
         } if($decodedIntegration->service === 'sendy'){
            $sendy =   new BravePop_Sendy();
            $validateIntegration = $sendy->validate_integration($apiSettings->url, $apiSettings->api);
         }else{
            $validateIntegration = bravepop_get_integration_lists($decodedIntegration->service, $apiSettings->api, $apiSettings->secret, $apiSettings->access, $apiSettings->url, $apiSettings->refresh, $apiSettings->domain );
         }

         if($validateIntegration !== false){
            //error_log('Lists Found: '.$validateIntegration);
            $updatedIntegrations[$decodedIntegration->service]->enabled = true;
            $settings = array( 'integrations' => $updatedIntegrations );
            BravePopup_Settings::save_settings( $settings );
         }else{
            // error_log('NO LISTSS FOUND!!!!!!');
            $updatedIntegrations[$decodedIntegration->service]->enabled = false;
            $settings = array( 'integrations' => $updatedIntegrations );
            BravePopup_Settings::save_settings( $settings );
            return new WP_REST_Response(array('error'=>'Invalid API key'));
         }

      }
   }

   return new WP_REST_Response(get_option('_bravepopup_settings'));
}

function bravepop_update_validator_integrations( $integrations ){
   $currentSettings = get_option('_bravepopup_settings');
   $currentIntegrations = $currentSettings && isset($currentSettings['integrations']) ? $currentSettings['integrations'] : array() ;
   $updatedIntegrations = $currentIntegrations;

   $decodedIntegration = json_decode($integrations);
   if(isset($decodedIntegration->service)){
      $validateIntegration = false;
      if($decodedIntegration->service === 'neverbounce' && class_exists('BravePop_NeverBounce') ){   $validator = new BravePop_NeverBounce();  }
      if($decodedIntegration->service === 'zerobounce' && class_exists('BravePop_ZeroBounce') ){   $validator = new BravePop_ZeroBounce();  }
      if($decodedIntegration->service === 'truemail' && class_exists('BravePop_TrueMail') ){   $validator = new BravePop_TrueMail();  }

      $validateIntegration = $validator ? $validator->validate_email('contact@getbrave.io', $decodedIntegration->api) : false;

      if($validateIntegration){
         $apiSettings = new stdClass();
         $apiSettings->api = isset($decodedIntegration->api) ? $decodedIntegration->api : '';
         $apiSettings->enabled = true;
         $updatedIntegrations[$decodedIntegration->service] = $apiSettings;
        // $updatedIntegrations[$decodedIntegration->service]->enabled = true;
         $settings = array( 'integrations' => $updatedIntegrations );
         BravePopup_Settings::save_settings( $settings );
      }else{
         $updatedIntegrations[$decodedIntegration->service]->enabled = false;
         $settings = array( 'integrations' => $updatedIntegrations );
         BravePopup_Settings::save_settings( $settings );
         return new WP_REST_Response(array('error'=>'Invalid API key'));
      }

   }

   return new WP_REST_Response(get_option('_bravepopup_settings'));
}

function bravepop_update_captcha_integrations( $integrations ){
   $currentSettings = get_option('_bravepopup_settings');
   $currentIntegrations = $currentSettings && isset($currentSettings['integrations']) ? $currentSettings['integrations'] : array() ;
   $updatedIntegrations = $currentIntegrations;

   $decodedIntegration = json_decode($integrations);
   if(isset($decodedIntegration->service) && $decodedIntegration->service === 'recaptcha'){
      $validateIntegration = true;

      if($validateIntegration){
         $apiSettings = new stdClass();
         $apiSettings->api = isset($decodedIntegration->api) ? $decodedIntegration->api : '';
         $apiSettings->secret = isset($decodedIntegration->secret) ? $decodedIntegration->secret : '';
         $apiSettings->enabled = true;
         $updatedIntegrations[$decodedIntegration->service] = $apiSettings;
        // $updatedIntegrations[$decodedIntegration->service]->enabled = true;
         $settings = array( 'integrations' => $updatedIntegrations );
         BravePopup_Settings::save_settings( $settings );
      }
   }

   return new WP_REST_Response(get_option('_bravepopup_settings'));
}

function bravepop_update_social_integrations( $integrations ){
   $currentSettings = get_option('_bravepopup_settings');
   $currentIntegrations = $currentSettings && isset($currentSettings['integrations']) ? $currentSettings['integrations'] : array() ;
   $updatedIntegrations = $currentIntegrations;
   $decodedIntegration = json_decode($integrations);

   $apiSettings = new stdClass();
   $apiSettings->api = isset($decodedIntegration->api) ? $decodedIntegration->api : '';
   $apiSettings->secret = isset($decodedIntegration->secret) ? $decodedIntegration->secret : '';
   $apiSettings->enabled = isset($decodedIntegration->enabled) ? $decodedIntegration->enabled : false;;
   $updatedIntegrations[$decodedIntegration->service] = $apiSettings;
   $updatedIntegrations[$decodedIntegration->service]->enabled = !empty($decodedIntegration->api) ? true : false;
   $settings = array( 'integrations' => $updatedIntegrations );
   BravePopup_Settings::save_settings( $settings );

   return new WP_REST_Response(get_option('_bravepopup_settings'));
}


function bravepop_remove_integration( $service ){
   if(!$service){  return false;  }
   $currentSettings = get_option('_bravepopup_settings');
   $currentIntegrations = $currentSettings && isset($currentSettings['integrations']) ? $currentSettings['integrations'] : array() ;
   
   if(isset($currentIntegrations[$service])){
      unset($currentIntegrations[$service]);
   }
   //error_log(wp_json_encode($currentIntegrations));

   $settings = array( 'integrations' => $currentIntegrations );
   BravePopup_Settings::save_settings( $settings );

   return new WP_REST_Response(array('removed'=>true));
}


add_action('wp_ajax_bravepop_ajax_zoho_init_token', 'bravepop_ajax_zoho_init_token', 0);
add_action('wp_ajax_nopriv_bravepop_ajax_zoho_init_token', 'bravepop_ajax_zoho_init_token');
function bravepop_ajax_zoho_init_token(){
   $securityPassed = check_ajax_referer('esp_nonce', 'security', false);
   if($securityPassed === false) {
      wp_die();
   }

   if(!current_user_can('manage_options') && empty($_POST['client_id']) || empty($_POST['client_secret']) || empty($_POST['code']) || empty($_POST['domain'])){ wp_die(); }
   $zohoDomain = isset($_POST['domain']) ? $_POST['domain'] : 'com';
   $availableTlds = ['com', 'eu', 'in', 'com.au', 'com.cn', 'jp'];
   if(!in_array($zohoDomain, $availableTlds)){
      $zohoDomain = 'com';
   }

   $args = array( 
      'method' => 'POST',
      'headers' => array( 
         'Content-Type' => 'application/x-www-form-urlencoded'  
      ),
   );

   $response = wp_remote_post( 'https://accounts.zoho.'.$zohoDomain.'/oauth/v2/token?&client_id='.$_POST['client_id'].'&client_secret='.$_POST['client_secret'].'&code='.$_POST['code'].'&grant_type=authorization_code', $args );
   $body = wp_remote_retrieve_body( $response );
   $data = json_decode( $body );
   
   if(isset($data->refresh_token)){
      //error_log($data->refresh_token);
      echo esc_html($data->refresh_token);
   }else{
      echo 'FALSE';
   }
   wp_die();
}

function bravepop_add_to_newsletter($actionType='form', $type='', $emailValue='', $listID=false, $nameValue='', $phoneValue='', $customFields=array(), $tags=array(), $userData=array(), $doubleOptin=false, $misc=array()){
   if( $emailValue && $listID){
      if($type === 'mailchimp'){    $service = new BravePop_Mailchimp();   }
      if($type === 'mailjet'){      $service = new BravePop_Mailjet();   }
      if($type === 'hubspot'){      $service = new BravePop_Hubspot();   }
      if($type === 'constantcontact'){      $service = new BravePop_ConstantContact();   }
      if($type === 'activecampaign'){      $service = new BravePop_ActiveCampaign();   }
      if($type === 'sendgrid'){      $service = new BravePop_SendGrid();   }
      if($type === 'sendinblue'){      $service = new BravePop_SendinBlue();   }
      if($type === 'convertkit'){      $service = new BravePop_ConvertKit();   }
      if($type === 'getresponse'){      $service = new BravePop_GetResponse();   }
      if($type === 'aweber'){      $service = new BravePop_Aweber();   }
      if($type === 'zoho'){      $service = new BravePop_Zoho();   }
      if($type === 'zohocrm'){      $service = new BravePop_ZohoCRM();   }
      if($type === 'mailerlite'){      $service = new BravePop_MailerLite();   }
      if($type === 'moosend'){      $service = new BravePop_Moosend();   }
      if($type === 'mailpoet'){      $service = new BravePop_MailPoet();   }
      if($type === 'pabbly'){      $service = new BravePop_Pabbly();   }
      if($type === 'klaviyo'){      $service = new BravePop_Klaviyo();   }
      if($type === 'sendpulse'){      $service = new BravePop_SendPulse();   }
      if($type === 'ontraport'){      $service = new BravePop_Ontraport();   }
      if($type === 'campaignmonitor'){      $service = new BravePop_CampaignMonitor();   }
      if($type === 'tnp'){      $service = new BravePop_TNP();   }
      if($type === 'mailster'){      $service = new BravePop_Mailster();   }
      if($type === 'fluentcrm'){      $service = new BravePop_FluentCRM();   }
      if($type === 'sendy'){      $service = new BravePop_Sendy();   }
      if($type === 'omnisend'){      $service = new BravePop_Omnisend();   }
      if($type === 'sender'){      $service = new BravePop_Sender();   }


      if(function_exists('bravepop_external_integration_add_contact')){
         $service =  bravepop_external_integration_add_contact($type);
      }
      if(isset($service)){
         // error_log('bravepop_add_to_newsletter!!!');
         $userSync = array('enabled' => false);
         if(($actionType === 'sync_add' || $actionType === 'sync_update') ){
            $userSync['sync'] = true; $userSync['action'] = $actionType;  $userSync['userData'] = $userData;
         }
         $subScriptionSuccess = $service->add_to_lists($emailValue, $listID, $nameValue, '', $phoneValue, $customFields, $tags, $userSync, $doubleOptin, $misc);
         
         return $subScriptionSuccess;
      }
   }
}

function bravepop_newsletter_misc_settings($service, $newsletterSettings, $formFields){

   $miscSettings = array();
   // SendInBlue template settings
   if($service==='sendinblue' && !empty($newsletterSettings->double_optin) && !empty($newsletterSettings->sendinblue_template) && !empty($newsletterSettings->sendinblue_redirect)){
      $miscSettings['sendinblue_template'] = $newsletterSettings->sendinblue_template;
      $miscSettings['sendinblue_redirect'] = $newsletterSettings->sendinblue_redirect;
   }

   //Mailchimp Groups Settings
   $groupType = !empty($newsletterSettings->advancedSettings->groupType) ? $newsletterSettings->advancedSettings->groupType : '';
   if($service==='mailchimp' && $groupType && function_exists('bravepop_map_mailchimp_groups') && empty($newsletterSettings->advancedSettings->conditional)){
      $groups = bravepop_map_mailchimp_groups($newsletterSettings->advancedSettings->groupType, isset($newsletterSettings->advancedSettings->groups) ? $newsletterSettings->advancedSettings->groups: array(), $formFields);
      $miscSettings['mailchimp_groups'] = $groups;
   }

   //GetResponse Double Optin Settings
   if($service==='getresponse' && isset($newsletterSettings->dayOfCycle)){
      $miscSettings['dayOfCycle'] = $newsletterSettings->dayOfCycle;
   }
   //error_log('bravepop_newsletter_misc_settings: '.wp_json_encode($miscSettings));
   return $miscSettings;

}


function bravepop_subscription_failed_notificaion($popupID, $emailAddress, $service, $fullName, $subEmailAddress){
   if(!$popupID || !$emailAddress || !$subEmailAddress || !$service){ return false; }
   $firstname = $fullName ? $fullName : ''; $lastname = '';
   if(( strpos($fullName, ' ') !== false)){
      $fullname_parts = preg_split('/\s+/', $fullName);
      $firstname = $fullname_parts[0] ? $fullname_parts[0] : '';
      $lastname = $fullname_parts[1] ? $fullname_parts[1] : '';
   }
   $popupName = get_the_title($popupID);
   //error_log($popupName .' '. $emailAddress .' '. $subEmailAddress .' '. $service);
   if($popupName && $emailAddress && $subEmailAddress && $service){
      $sendto =  $emailAddress;
      $subject = '[Brave][Error] Newsletter Subscription Failed';
      $headers = "Content-Type: text/plain; charset=\"iso-8859-1\"";
      $theMessage = "Hi,\r\n\r\nYour Brave Campaign '".$popupName."' failed to subscribe a visitor to your Newsletter mailing list (Due to ".$service." API issues, incomplete data or other reasons).\r\nPlease add the visitor to your list manually from your ".$service." Dashboard:  \r\n\r\nFirst Name: ".($firstname ? $firstname: 'Not Given')."\r\nLast Name: ".($lastname ? $lastname: 'Not Given')."\r\nEmail Address: ".$subEmailAddress."\r\n\r\nMessage Sent By Brave Plugin.\r\n".get_bloginfo( 'name' )."";
      wp_mail( $sendto, $subject, $theMessage, $headers);
   }
}