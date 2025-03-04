<?php
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
require __DIR__ . '/helpers/integrations.php';
require __DIR__ . '/helpers/presets.php';
require __DIR__ . '/helpers/wordpress.php';
require __DIR__ . '/helpers/woocommerce.php';

class BravePop_Rest_Server extends WP_REST_Controller {
 
   //The namespace and version for the REST SERVER
   var $plugin_namespace = 'brave/v';
   var $plugin_version   = '1';
   var $metaData = array('popup_devices', 'popup_views', 'popup_type', 'popup_ctr', 'popup_conversion', 'popup_goal', 'popup_goal_action', 'popup_placement', 'popup_parentID', 'popup_abtest', 'popup_schedule');

  public function register_routes() {
    $namespace = $this->plugin_namespace . $this->plugin_version;

   register_rest_route( $namespace, '/popups', array(
        array(
            'methods'         => WP_REST_Server::READABLE,
            'callback'        => array( $this, 'get_popups' ),
            'permission_callback'   => array( $this, 'check_user_permission' ),
            'args'                => array(
                'filter' => array(
                    'type'        => 'string',
                    'default'     => '',
                    'description' => __('Filter Popups','bravepop'),
                ),
                'sort' => array(
                    'type'        => 'string',
                    'default'     => '',
                    'description' => __('Sort Popups','bravepop'),
                    'validate_callback' => 'sanitize_text_field',
                ),
                'page' => array(
                    'type'        => 'number',
                    'default'     => 0,
                    'description' => __('Popups Pagination','bravepop'),
                    'validate_callback' => 'absint',
                ),
            )
        ),
        array(
            'methods'         => WP_REST_Server::CREATABLE,
            'callback'        => array( $this, 'add_popup' ),
            'permission_callback'   => array( $this, 'check_user_permission' )
        ),
    ));


    //Single Routes
    register_rest_route( $namespace, '/popup(?:/(?P<id>\d+))?', array(
        array(
            'methods'         => WP_REST_Server::READABLE,
            'callback'        => array( $this, 'get_popup' ),
            'permission_callback'   => array( $this, 'check_user_permission' ),
            'args' => array( 'id' => array('type' => 'integer', 'description' => __( 'Popup ID' )) ),
        ),
        array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'update_popup' ),
            'permission_callback' => array( $this, 'check_user_permission' ),
            'args'                => array(
                'id' => array(
                    'type'        => 'integer',
                    'description' => __( 'Popup ID' ),
                    'validate_callback' => 'absint',
                    'required'          => true,
                ),
                'popup_title' => array(
                    'type'        => 'string',
                    'description' => __( 'Popup Title' ),
                    'validate_callback' => 'sanitize_textarea_field',
                ),
                'popup_data' => array(
                    'type'        => 'string',
                    'description' => __( 'Popup Main Data' ),
                    'validate_callback' => 'sanitize_textarea_field',
                ),
                'popup_parentID' => array(
                  'type'        => 'integer',
                  'description' => __( 'If the Popup is a Child, its parent ID' ),
                  'validate_callback' => 'absint',
                ),
                'popup_abtest' => array(
                  'type'        => 'string',
                  'description' => __( 'Popup abtest Data' ),
                  'validate_callback' => 'sanitize_textarea_field',
                ),
                'popup_schedule' => array(
                  'type'        => 'string',
                  'description' => __( 'Popup Schedule' ),
                  'validate_callback' => 'sanitize_textarea_field',
                ),
                'popup_goal_action' => array(
                  'type'        => 'string',
                  'description' => __( 'Popup Goal Action' ),
                  'validate_callback' => 'sanitize_textarea_field',
                ),
                'goal' => array(
                  'type'        => 'integer',
                  'description' => __( 'Popup Goal' ),
                  'validate_callback' => 'absint',
               ),
               'devices' => array(
                  'type'        => 'string',
                  'description' => __( 'Popup Settings Data' ),
                  'validate_callback' => 'sanitize_textarea_field',
               ),
               'placement' => array(
                  'type'        => 'string',
                  'description' => __( 'Popup Placement' ),
                  'validate_callback' => 'sanitize_text_field',
               ),
               'updated' => array(
                  'type'        => 'integer',
                  'description' => __( 'Popup Last Updated' ),
                  'validate_callback' => 'absint',
               ),
               'status' => array(
                  'type'        => 'string',
                  'description' => __( 'Popup Status' ),
                  'validate_callback' => 'sanitize_text_field',
               )
            ),
        )
    ));

   register_rest_route( $namespace, '/deletepopup', array(
      array(
         'methods'             => 'POST',
         'callback'            => array( $this, 'delete_popup' ),
         'permission_callback' => array( $this, 'check_user_permission' ),
       )
   ));


    //Plugin Settings Route
    register_rest_route( $namespace, '/settings', array(
        array(
            'methods'         => WP_REST_Server::READABLE,
            'callback'        => array( $this, 'get_settings' ),
            'permission_callback'   => array( $this, 'check_user_permission' ),
        ),
        array(
        'methods'             => 'POST',
        'callback'            => array( $this, 'update_settings' ),
        'permission_callback' => array( $this, 'check_user_permission' ),
        )
    ));
    //Presets
    register_rest_route( $namespace, '/presets', array(
        array(
            'methods'         => WP_REST_Server::READABLE,
            'callback'        => array( $this, 'rest_get_presets' ),
            'permission_callback'   => array( $this, 'check_user_permission' ),
        ),
        array(
        'methods'             => 'POST',
        'callback'            => array( $this, 'rest_update_presets' ),
        'permission_callback' => array( $this, 'check_user_permission' ),
        )
    ));
    //Integrations
    register_rest_route( $namespace, '/integrations', array(
      array(
          'methods'         => WP_REST_Server::READABLE,
          'callback'        => array( $this, 'rest_get_integration_lists' ),
          'permission_callback'   => array( $this, 'check_user_permission' ),
          'args' => array( 'service' => array('type' => 'string', 'description' => __( 'Service ID' ))),
      ),
      array(
      'methods'             => 'POST',
      'callback'            => array( $this, 'rest_update_integrations' ),
      'permission_callback' => array( $this, 'check_user_permission' ),
      )
   ));
   register_rest_route( $namespace, '/integrations/fetchadvanced', array(
      array(
          'methods'         => WP_REST_Server::READABLE,
          'callback'        => array( $this, 'rest_get_advanced_integration_data' ),
          'permission_callback'   => array( $this, 'check_user_permission' ),
          'args' => array( 
             'service' => array('type' => 'string', 'description' => __( 'Service ID' ) ),
             'listID' => array('type' => 'string', 'description' => __( 'Newsletter List ID' ) )
            ),
      )
   ));
   //Remove Integrations
      register_rest_route( $namespace, '/integrations/remove', array(
      array(
      'methods'             => 'POST',
      'callback'            => array( $this, 'rest_remove_integrations' ),
      'permission_callback' => array( $this, 'check_user_permission' ),
      )
   ));
   //Aweber Integration
   register_rest_route( $namespace, '/integrations/aweberverfier', array(
      array(
      'methods'             => WP_REST_Server::READABLE,
      'callback'            => array( $this, 'rest_get_aweber_verfiers' ),
      'permission_callback' => array( $this, 'check_user_permission' ),
      )
   )); 

   //Custom Wordpress Data Collection Route
    register_rest_route( $namespace, '/wpdata', array(
        array(
            'methods'         => WP_REST_Server::READABLE,
            'callback'        => array( $this, 'rest_get_wpdata' ),
            'permission_callback'   => array( $this, 'check_user_permission' ),
            'args' => [ 'type' => array('type' => 'string', 'description' => __( 'Data Type' ))],
            )
        )
    );
    register_rest_route( $namespace, '/wpsearch', array(
      array(
          'methods'         => WP_REST_Server::READABLE,
          'callback'        => array( $this, 'rest_search_wpdata' ),
          'permission_callback'   => array( $this, 'check_user_permission' ),
          'args' => [ 
             'type' => array('type' => 'string', 'description' => __( 'Post Type' )),
             'query' => array('type' => 'string', 'description' => __( 'Search Query' ))
            ],
          )
      )
   );

    //Custom Wordpress Data Collection Route for Posts Element
    register_rest_route( $namespace, '/posts', array(
        array(
            'methods'         => WP_REST_Server::READABLE,
            'callback'        => array( $this, 'rest_get_wpPosts' ),
            'permission_callback'   => array( $this, 'check_user_permission' ),
            'args' => [ 'type' => array('type' => 'string', 'description' => __( 'Data Type' ))],
            )
        )
    );
    //Custom Wordpress Data Collection Route for Posts Element
    register_rest_route( $namespace, '/products', array(
        array(
            'methods'         => WP_REST_Server::READABLE,
            'callback'        => array( $this, 'rest_get_wooProducts' ),
            'permission_callback'   => array( $this, 'check_user_permission' ),
            'args' => [ 'type' => array('type' => 'string', 'description' => __( 'Data Type' ))],
            )
        )
    );

   //Send Email
   register_rest_route( $namespace, '/sendmail', array(
      array(
      'methods'             => 'POST',
      'callback'            => array( $this, 'send_email' ),
      'permission_callback' => array( $this, 'check_user_permission' ),
      )
   ));
   //Pick Winning Abtest
   register_rest_route( $namespace, '/selectabtest', array(
      array(
      'methods'             => 'POST',
      'callback'            => array( $this, 'pick_winning_abtest' ),
      'permission_callback' => array( $this, 'check_user_permission' ),
      )
   ));
    //Analytics Route
    register_rest_route( $namespace, '/analytics', array(
      array(
          'methods'         => WP_REST_Server::READABLE,
          'callback'        => array( $this, 'get_analytics' ),
          'permission_callback'   => array( $this, 'check_user_permission' ),
      ),
   ));
   register_rest_route( $namespace, '/stat(?:/(?P<id>\d+))?', array(
      array(
          'methods'         => WP_REST_Server::READABLE,
          'callback'        => array( $this, 'get_popup_stat' ),
          'permission_callback'   => array( $this, 'check_user_permission' ),
          'args' => array( 
            'id' => array('type' => 'integer', 'description' => __( 'Popup ID' )),
            'start' => array('type' => 'string', 'description' => __( 'Stat Start Date' )),
            'end' => array('type' => 'string', 'description' => __( 'Stat End Date' ))
           ),
      ),
      array(
          'methods'             => WP_REST_Server::DELETABLE,
          'callback'            => array( $this, 'delete_popup_stat' ),
          'permission_callback' => array( $this, 'check_user_permission' ),
        ),
   ));
   register_rest_route( $namespace, '/analytics_csv(?:/(?P<id>\d+))?', array(
      array(
          'methods'         => WP_REST_Server::READABLE,
          'callback'        => array( $this, 'get_analytics_csv' ),
          'permission_callback'   => array( $this, 'check_user_permission' ),
          'args' => array( 
            'id' => array('type' => 'integer', 'description' => __( 'Campaign ID' )),
           ),
      ),
   ));
   //Submissions Route
   register_rest_route( $namespace, '/submissions', array(
      array(
          'methods'         => WP_REST_Server::READABLE,
          'callback'        => array( $this, 'get_submissions' ),
          'permission_callback'   => array( $this, 'check_user_permission' ),
      ),
   ));
   register_rest_route( $namespace, '/submission(?:/(?P<id>\d+))?', array(
      array(
          'methods'         => WP_REST_Server::READABLE,
          'callback'        => array( $this, 'get_submission_entries' ),
          'permission_callback'   => array( $this, 'check_user_permission' ),
          'args' => array( 
            'id' => array('type' => 'integer', 'description' => __( 'Campaign ID' )),
            'count' => array('type' => 'integer', 'description' => __( 'Number of Entries' )),
            'page' => array('type' => 'integer', 'description' => __( 'pagination' )),
           ),
      ),
   ));
   register_rest_route( $namespace, '/submission_csv(?:/(?P<id>\d+))?', array(
      array(
          'methods'         => WP_REST_Server::READABLE,
          'callback'        => array( $this, 'get_submission_csv' ),
          'permission_callback'   => array( $this, 'check_user_permission' ),
          'args' => array( 
            'id' => array('type' => 'integer', 'description' => __( 'Campaign ID' )),
            'fields_only' => array('type' => 'integer', 'description' => __( 'If only fields should be in the csv.' )),
           ),
      ),
   ));
   register_rest_route( $namespace, '/entry(?:/(?P<id>\d+))?', array(
      array(
          'methods'         => WP_REST_Server::READABLE,
          'callback'        => array( $this, 'get_submission_entry' ),
          'permission_callback'   => array( $this, 'check_user_permission' ),
          'args' => array( 
            'id' => array('type' => 'integer', 'description' => __( 'Entry ID' )),
            'start' => array('type' => 'string', 'description' => __( 'Stat Start Date' )),
            'end' => array('type' => 'string', 'description' => __( 'Stat End Date' ))
           ),
      ),
      array(
         'methods'             => 'POST',
         'callback'            => array( $this, 'update_entry' ),
         'permission_callback' => array( $this, 'check_user_permission' ),
         'args' => array( 
            'id' => array('type' => 'integer', 'description' => __( 'Entry ID' )),
         ),
       )
   ));
   register_rest_route( $namespace, '/deletesubmissions', array(
      array(
         'methods'             => 'POST',
         'callback'            => array( $this, 'delete_submission_entries' ),
         'permission_callback' => array( $this, 'check_user_permission' ),
       )
   ));
   register_rest_route( $namespace, '/removesubmissioncampaign', array(
      array(
         'methods'             => 'POST',
         'callback'            => array( $this, 'delete_submission_campaign' ),
         'permission_callback' => array( $this, 'check_user_permission' ),
       )
   ));
   register_rest_route( $namespace, '/dismissrating', array(
      array(
         'methods'             => 'POST',
         'callback'            => array( $this, 'dismiss_rating_box' ),
         'permission_callback' => array( $this, 'check_user_permission' ),
       )
   ));
  }




  

    // Register our REST Server
    public function hook_rest_server(){
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    public function bravepopup_theme_arg_validate_callback( $value, $request, $param ) {
        // If the 'filter' argument is not a string return an error.
        if ( ! is_string( $value ) ) {
            return new WP_Error( 'rest_invalid_param', esc_html__( 'The theme argument must be a string.', 'bravepop' ), array( 'status' => 400 ) );
        }
        // Get the registered attributes for this endpoint request.
        $attributes = $request->get_attributes();

        // Grab the theme param schema.
        $args = $attributes['args'][ $param ];

        // If the theme param is not a value in our enum then we should return an error as well.
        if ( ! in_array( $value, $args['enum'], true ) ) {
         /* translators: 1: Parameter name, 2: List of allowed values */
         return new WP_Error( 'rest_invalid_param', sprintf( __( '%1$s is not one of %2$s', 'bravepop' ), $param, implode( ', ', $args['enum'] ) ), array( 'status' => 400 ) );
        }
    }


    public function check_user_permission(){
         if ( ! current_user_can( 'access_brave_menus' ) ) {
            return new WP_Error( 'rest_forbidden', esc_html__( 'You do not have permissions to view this data.', 'bravepop' ), array( 'status' => 401 ) );
         }
        return true;
    }


   public function send_email( WP_REST_Request $request ){
      $params = $request->get_params();
      $sendto = isset($params['to']) ? $params['to'] : '';
      $subject = isset($params['subject']) ? wp_iso_descrambler($params['subject']) : '';
      $message = isset($params['message']) ? $params['message'] : '';
      $emailfrom =  'From: "'.wp_iso_descrambler(get_bloginfo('name')).'" <'.(!empty($params['emailfrom']) ? $params['emailfrom'] : get_bloginfo('admin_email')).'>';
      $emailreplyto = !empty($params['emailreplyto']) ? 'Reply-To: "'.get_bloginfo('name').'" <'.$params['emailreplyto'].'>' : '';
      $contentType = isset($params['contentType']) ? $params['contentType'] : 'text';
      $headers = "Content-Type: text/plain;"; 
      if($contentType === 'html'){  $headers = "Content-Type: text/html;";  }
      if($emailfrom){  $headers .= $emailfrom; }
      if($emailreplyto){ $headers .= $emailreplyto; }

      // error_log($message);
      // error_log($contentType);
      // error_log($headers);
      if($contentType === 'html'){
         $formattedMsg = wp_json_encode($message);
         $theMessage =  str_replace('\n', '&lt;br&gt;',  $formattedMsg);
         $theMessage = json_decode($theMessage);
         $theMessage = html_entity_decode($theMessage); 
      }else{
         $formattedMsg = wp_json_encode($message);
         $theMessage =  str_replace('\n', '\r\n',  $formattedMsg);
         $theMessage = json_decode($theMessage);
      }
      $mailResult = wp_mail( $sendto, $subject, $theMessage, $headers);

      if($mailResult){
         return new WP_REST_Response(true);
      }else{
         return new WP_REST_Response(false);
      }
   }

   public function pick_winning_abtest( WP_REST_Request $request ){
      $params = $request->get_params();
      $popupID = isset($params['id']) ? (int)$params['id'] : '';
      $parentID = isset($params['parentID']) ? (int)$params['parentID'] : '';
      if(!empty($popupID) && !empty($parentID)){
         try{
            //merge the variation's design and parent's settings
            $childPopupData = json_decode(get_post_meta($popupID, 'popup_data', true));
            // $parentPopupData = json_decode(get_post_meta($parentID, 'popup_data', true));
            // $parentPopupSettings = isset($this->popupData->settings) ? $this->popupData->settings : new stdClass();
            // $childPopupData->settings = $parentPopupSettings;
            
            //remove all children popups of the parent
            $parentPopupAbtest = json_decode(get_post_meta($parentID, 'popup_abtest', true));
            foreach ($parentPopupAbtest->items as $index => $popItem) {
               if($popItem->id !== $parentID){
                  //error_log('Remove Post: '.$popItem->id);
                  wp_delete_post( $popItem->id, true );
               }
            }
            //then update the parent popup's popup_data & popup_abtest meta
            update_post_meta($parentID, 'popup_data', wp_slash(wp_json_encode($childPopupData)));
            update_post_meta($parentID, 'popup_abtest','');
            return new WP_REST_Response(array('success'=>true));
         }catch(Exception $e){
            error_log(wp_json_encode($e->getMessage()));
            return new WP_REST_Response(array('error'=>'Unexpected Error Occured!'));
         }

      }else{
         return new WP_REST_Response(array('error'=>'Parent ID or Popup ID missing.'));
      }

   }

   public function get_settings( WP_REST_Request $request ) {
      $settings = get_option('_bravepopup_settings');
      return new WP_REST_Response($settings);
    }

   public function update_settings( WP_REST_Request $request ) {
      $params = $request->get_params();
      $visibility = $params && isset($params['visibility']) ? $params['visibility'] : null;
      $goal = $params && isset($params['goal']) ? $params['goal'] : null;
      $welcome_tour = $params && isset($params['welcome_tour']) ? $params['welcome_tour'] : 'false';
      $analytics = $params && isset($params['analytics']) ? $params['analytics'] : null;
      $emailvalidator = $params && isset($params['emailvalidator']) ? $params['emailvalidator'] : null;
      $customFonts = $params && isset($params['fonts']) ? $params['fonts'] : null;
      $submission = $params && isset($params['submission']) ? json_decode($params['submission']) : null;
      $appSettings = $params && isset($params['app_settings']) ? json_decode($params['app_settings']) : null;
      $currentSettings = get_option('_bravepopup_settings');
      //$resetSubmissions = array('campaigns'=> new stdClass());

      if($visibility || $goal){
         $this->update_popup_placement_settings(json_decode($visibility),json_decode($goal));
      }else{
         //Save Settings that are Not Goal or Visibility
         try{
            if($welcome_tour){   BravePopup_Settings::save_settings( array('welcome_tour' => $welcome_tour) );  }
            if($analytics){   BravePopup_Settings::save_settings( array('analytics' => json_decode($analytics)) );  }
            if($appSettings){   BravePopup_Settings::save_settings( array('app_settings' => $appSettings) );  }
            if($emailvalidator){   BravePopup_Settings::save_settings( array('emailvalidator' => json_decode($emailvalidator)) );  }
            if($customFonts){   BravePopup_Settings::save_settings( array('fonts' => json_decode($customFonts)) );  }
            if($submission && $submission->campaigns){   
               $updatedSubmission = new stdClass();
               $updatedSubmission->campaigns = $submission->campaigns;
               BravePopup_Settings::save_settings( array('submission' => $updatedSubmission ) );  
            }
         }catch(Exception $e){
            error_log(wp_json_encode($e->getMessage()));
         }
      }

      // Update Backend Access Roles.
      if($appSettings){
         $currentAppSettings = isset($currentSettings['app_settings']) ? $currentSettings['app_settings'] : new stdClass();
         $previousRoles = isset($currentAppSettings->allowedRoles) ? $currentAppSettings->allowedRoles : array();
         $allowOthersAccess =  !empty($appSettings->allowOthersAccess) ? true : false;
         $allowedRoles = $allowOthersAccess && !empty($appSettings->allowedRoles) ? $appSettings->allowedRoles : array(); 
         bravepop_updated_menu_roles($previousRoles, $allowedRoles);
      }

      return new WP_REST_Response(get_option('_bravepopup_settings'));
   }

   public function update_popup_placement_settings($visibility, $goal){
      $currentSettings = get_option('_bravepopup_settings');
      $currentVis = $currentSettings && isset($currentSettings['visibility']) ? $currentSettings['visibility'] : array() ;
      $currentGoals = $currentSettings && isset($currentSettings['goal']) ? $currentSettings['goal'] : array() ;
      
      if($visibility){
         $decodedVis = $visibility;
         $popupID = $decodedVis->id;
         $currentVis[(int)$popupID] = $decodedVis;
      }

      if($goal){
         $decodedGoal = $goal;
         $popupID = $decodedGoal->id;
         $currentGoals[(int)$popupID] = $decodedGoal;
      }

      $settings = array(
         'visibility' => $currentVis,
         'goals' => $currentGoals,
         //'settings' => $settings ? $settings : null,
         //'license' => $license && $license ? $license : null,
      );
      
      //Save Settings
      BravePopup_Settings::save_settings( $settings );

   }


   public function rest_update_integrations( WP_REST_Request $request ){
      $params = $request->get_params();
      $type = $params && isset($params['type']) ? $params['type'] : 'newsletter';
      $integrations = $params && isset($params['integrations']) ? $params['integrations'] : null;
      if($type ==='newsletter'){
         return bravepop_update_newsletter_integrations( $integrations );
      }
      if($type ==='validator'){
         return bravepop_update_validator_integrations( $integrations );
      }
      if($type ==='captcha'){
         return bravepop_update_captcha_integrations( $integrations );
      }
      if($type ==='social'){
         return bravepop_update_social_integrations( $integrations );
      }
      
   }

   public function rest_remove_integrations( WP_REST_Request $request ){
      $params = $request->get_params();
      $service = $params && isset($params['service']) ? $params['service'] : null;

      if(!$service) {
         return new WP_REST_Response(array('error'=>'Service ID is required.'));
      }
      
      return bravepop_remove_integration( $service );
   }


   public function rest_get_aweber_verfiers( WP_REST_Request $request ){
      $verifier_bytes = random_bytes(64);
      $code_verifier = rtrim(strtr(base64_encode($verifier_bytes), "+/", "-_"), "=");
      $challenge_bytes = hash("sha256", $code_verifier, true);
      $code_challenge = rtrim(strtr(base64_encode($challenge_bytes), "+/", "-_"), "=");
      return new WP_REST_Response(array('challenge'=> $code_challenge, 'verifier'=> $code_verifier, 'state'=> uniqid()));
   }

   public function rest_get_integration_lists(WP_REST_Request $request){
      try{
         $params = $request->get_params();
         $service = $params && isset($params['service']) ? $params['service'] : '';
         $apiKey = $params && isset($params['api']) ? $params['api'] : '';
         $secretKey = $params && isset($params['secret']) ? $params['secret'] : '';
         $accessToken = $params && isset($params['access']) ? $params['access'] : '';
         $apiURL = $params && isset($params['url']) ? $params['url'] : '';
         $refresh = $params && isset($params['refresh']) ? $params['refresh'] : '';
         
         // error_log('rest_get_integration_lists: '.$service . $apiKey . $secretKey . $accessToken. $apiURL, $refresh);
         
         if(!$service){  
            return new WP_REST_Response(array('error'=>'Provide a service name.'));
         }
   
         $lists = bravepop_get_integration_lists($service, $apiKey, $secretKey, $accessToken, $apiURL, $refresh);
         return $lists;

      }catch(Exception $e){
         error_log(wp_json_encode($e->getMessage()));
         return new WP_REST_Response(array('error'=>'Error Getting Lists.'));
      }

   }

   public function rest_get_advanced_integration_data(WP_REST_Request $request){
      try{
         $params = $request->get_params();
         $service = $params && isset($params['service']) ? $params['service'] : '';
         $listID = $params && isset($params['listID']) ? $params['listID'] : '';
         $integrationData = new stdClass();

         if(!$service){  
            return new WP_REST_Response(array('error'=>'Provide a service name.'));
         }
         if(function_exists('bravepop_get_advanced_integration_data')){
            $integrationData = bravepop_get_advanced_integration_data($service, $listID);
         }
         return $integrationData;
      }catch(Exception $e){
         error_log(wp_json_encode($e->getMessage()));
      }

   }

    public function rest_get_presets( WP_REST_Request $request ){
        $params = $request->get_params();
        $type = $params && isset($params['type']) ? $params['type'] : null;
        $presetID = $params && isset($params['presetID']) ? $params['presetID'] : null;

        return bravepop_get_presets($type, $presetID);
    }

    public function rest_update_presets( WP_REST_Request $request ){
        $params = $request->get_params();
        $preset = $params && isset($params['preset']) ? $params['preset'] : null;
        $presetAction = $params && isset($params['presetAction']) ? $params['presetAction'] : null;
        $presetID = $params && isset($params['presetID']) ? $params['presetID'] : null;
        $presetImageID = $params && isset($params['presetImageID']) ? $params['presetImageID'] : null;

        return bravepop_update_presets($preset, $presetAction, $presetID, $presetImageID);
    }


    public function get_popup( WP_REST_Request $request ) {
      $params = $request->get_params();
      //error_log(wp_json_encode($params['id']) );
      $id = $params['id'];
      //return 'get_popup Called!!';
      $error = new WP_Error( 'rest_post_invalid_id', __( 'Invalid popup ID.' ), array( 'status' => 404 ) );
      $notFoundError = new WP_Error( 'rest_post_not_found', __( 'Popup Not Found' ), array( 'status' => 404 ) );
      if ( !$id || (int) $id <= 0 ) {
          return $error;
      }
  
      $popup = get_post( (int) $id );
      if ( empty( $popup ) || empty( $popup->ID ) || $popup->post_type !== 'popup' ) {
          return $notFoundError;
      }

      //Get MetaData
      foreach ($this->metaData as $value) {
          if($value === 'popup_type'){
            $theType = get_post_meta($id, $value, true);
            $popup->$value  = $theType ? $theType : 'popup';
          }else{
            $popup->$value  = get_post_meta($id, $value, true);
          }
      }
      $popup->{"popup_data"}  = get_post_meta($id, 'popup_data', false);
      return new WP_REST_Response($popup);
      
  }

  public function get_popups( WP_REST_Request $request ){

      $posts_query = new WP_Query();
      $query_args = array('posts_per_page' => 400, 'post_type' => 'popup', 'post_status' => array( 'publish', 'pending', 'draft', 'future', 'private' ));
      $query_result = $posts_query->query( $query_args );
      $controller = new WP_REST_Posts_Controller('post');
      $posts = array();
      foreach ( $query_result as $popup ) {
          $thepopup =  new stdClass();
          $thepopup->ID = $popup->ID;
          $thepopup->popup_title = $popup->post_title;
          $thepopup->popup_author = $popup->post_author;
          $thepopup->popup_status = $popup->post_status;
          $thepopup->popup_name = $popup->post_name;
          $thepopup->popup_date = $popup->post_date;
          $thepopup->popup_modified = $popup->post_modified;

          //Get MetaData
          foreach ($this->metaData as $value) {
             if($value === 'popup_type'){
               $theType = get_post_meta($popup->ID, $value, true);
               $thepopup->$value  = $theType ? $theType : 'popup';
             }else{
               $thepopup->$value  = get_post_meta($popup->ID, $value, true);
             }
          }

          if(!empty($thepopup->popup_devices) ||  !empty($thepopup->popup_placement) || !empty($thepopup->popup_goal)){
            $posts[] = $thepopup;
          }
      }
      return new WP_REST_Response($posts);
  }

    public function add_popup( WP_REST_Request $request ){
        //Let Us use the helper methods to get the parameters
        $popupData = $request->get_param( 'popup_data' );
         //CloudFlare Fix
         if($request->get_param( 'cf' ) === true  && function_exists('bravepopup_prepare_CF_data')){
            $popupData = bravepopup_prepare_CF_data($popupData);
         }
         
        $args = array(
            'post_title' => $request->get_param( 'popup_title' ),
            'post_excerpt' => array( $request->get_param( 'popup_excerpt' ) ),
            'popup_data' => $popupData,
            'popup_parentID' => $request->get_param( 'popup_parentID' ) ? $request->get_param( 'popup_parentID' ) : false,
        );
        $popupGoal = $request->get_param( 'popup_goal' ) ? $request->get_param( 'popup_goal' ) : 'custom';
        $popupGoalAction = $request->get_param( 'popup_goal_action' ) ? $request->get_param( 'popup_goal_action' ) : array('action'=>'view', 'step'=>0); 
        $visibility = $request->get_param( 'popup_visibility' ) ? $request->get_param( 'popup_visibility' ) : 'sitewide';
        $devices = $request->get_param( 'popup_devices' ) ? $request->get_param( 'popup_devices' ) : 'all';
        $type = $request->get_param( 'popup_type' ) ? $request->get_param( 'popup_type' ) : 'popup';
        $notFoundError = new WP_Error( 'rest_post_not_found', __( 'Popup Not Found' ), array( 'status' => 404 ) );
        $noTitleError = new WP_Error( 'rest_no_title_give', __( 'Popup Title Not Given' ), array( 'status' => 404 ) );

        
         if($args['post_title']){
            if ( false !== ( $id = wp_insert_post( array('post_title' => $args['post_title'],'post_type' => 'popup', 'post_status' => 'draft') ) ) ){
               //error_log('Popup Created!!!!'); 
               //error_log($id);
               if($args['popup_data']){    update_post_meta($id, 'popup_data',wp_slash($args['popup_data']));   }
               if($type){   update_post_meta($id, 'popup_type',$type);     } 
               if($popupGoal){   update_post_meta($id, 'popup_goal', $popupGoal);    }
               if($popupGoalAction){   update_post_meta($id, 'popup_goal_action', $popupGoalAction);    }
               if($visibility){   update_post_meta($id, 'popup_placement',$visibility);     } 
               if($devices){   update_post_meta($id, 'popup_devices',$devices);     } 
               if($args['popup_parentID']){   update_post_meta($id, 'popup_parentID',$args['popup_parentID']);     }  
               //Save the Goal and The Visibility Setting
               $theGoal = new stdClass(); $theGoal->id = $id; $theGoal->goal = $popupGoal;
               $theVis = new stdClass(); $theVis->id = $id; $theVis->type = $type; $theVis->placement = new stdClass();  $theVis->placement->placementType = $visibility;
               $this->update_popup_placement_settings($theVis, $theGoal);

               //Return the Newly Added Popup
               $popup = get_post( (int) $id );
               if ( empty( $popup ) || empty( $popup->ID ) || $popup->post_type !== 'popup' ) {
                  return new WP_REST_Response( $notFoundError, $request );
               }
      
               //Get MetaData
               foreach ($this->metaData as $value) {
                  $popup->$value  = get_post_meta($id, $value, true);
               }
               $popup->{"popup_data"}  = get_post_meta($id, 'popup_data', false);

               return new WP_REST_Response( $popup );
            }
         }else{
            
            return new WP_REST_Response( $noTitleError, $request );
         }

    }

    public function update_popup( WP_REST_Request $request ){
        //Let Us use the helper methods to get the parameters
        $args = array( 'id' => $request->get_param( 'id' ));

        if ( isset($args['id']) ){
            if(get_post_type($args['id']) != "popup") return;
            
            //$popup_data = get_post_meta($args['id'], 'popup_data', true);
            $dataBodyArray = $request->get_json_params();
            
            //error_log(wp_json_encode($dataBodyArray));

            if(isset($dataBodyArray['popup_data'])){
               $popupData =  wp_slash($dataBodyArray['popup_data']);

               //CloudFlare Fix
               if($request->get_param( 'cf' ) === true && function_exists('bravepopup_prepare_CF_data')){
                  $popupData = bravepopup_prepare_CF_data($popupData);
               }

               update_post_meta($args['id'], 'popup_data', $popupData);
            }

            if (isset($dataBodyArray['popup_title'])) {
               //error_log(wp_json_encode($dataBodyArray['popup_title']));
               wp_update_post(array('ID' => $args['id'], 'post_title' => $dataBodyArray['popup_title']));
            }
            if(isset($dataBodyArray['status'])){
               wp_update_post(array('ID' => $args['id'], 'post_status' => $dataBodyArray['status']));
            }
            if(isset($dataBodyArray['updated'])){
               $datetime  = date( 'Y-m-d H:i:s', current_time( $dataBodyArray['updated'], 0 ) ); 
               wp_update_post(array('ID' => $args['id'], 'post_modified' => $datetime));
            }

            if(isset($dataBodyArray['popup_goal'])){
               update_post_meta($args['id'], 'popup_goal',$dataBodyArray['popup_goal']);
               //Save Settings
               // $currentSettings = get_option('_bravepopup_settings');
               // $currentGoals = $currentSettings && isset($currentSettings['goal']) ? $currentSettings['goal'] : array() ;
               // $currentGoals[(int)$args['id']] = $dataBodyArray['popup_goal'];
               // BravePopup_Settings::save_settings( array( 'goals' => $currentGoals ) );
            }
            if(isset($dataBodyArray['popup_goal_action'])){
               update_post_meta($args['id'], 'popup_goal_action',$dataBodyArray['popup_goal_action']);
            }
            if(isset($dataBodyArray['devices'])){
               update_post_meta($args['id'], 'popup_devices',$dataBodyArray['devices']);
            }
            if(isset($dataBodyArray['placement'])){

               update_post_meta($args['id'], 'popup_placement',$dataBodyArray['placement']);
            }
            if(isset($dataBodyArray['popup_parentID']) && $dataBodyArray['popup_parentID'] !== 0){
               update_post_meta($args['id'], 'popup_parentID',$dataBodyArray['popup_parentID']);
            }
            if(isset($dataBodyArray['popup_abtest'])){
               update_post_meta($args['id'], 'popup_abtest',$dataBodyArray['popup_abtest']);
            }
            if(isset($dataBodyArray['popup_schedule'])){
               $currentSchedule = json_decode($dataBodyArray['popup_schedule']);
               if($currentSchedule && isset($currentSchedule->active) && $currentSchedule->active === true){
                  wp_update_post(array('ID' => $args['id'], 'post_status' => 'draft'));
               }
               update_post_meta($args['id'], 'popup_schedule',$dataBodyArray['popup_schedule']);
            }

            if(isset($dataBodyArray['form_submission']) || isset($dataBodyArray['form_submission_fields'])){
               $popupID = $args['id'];
               $currentSettings = get_option('_bravepopup_settings');
               $submissionSettings = $currentSettings && isset($currentSettings['submission']) ? $currentSettings['submission'] : new stdClass();
 
               if(!isset($submissionSettings->campaigns)){
                  $submissionSettings->campaigns = new stdClass();
               }
               if(!isset($submissionSettings->campaigns->$popupID)){
                  $submissionSettings->campaigns->$popupID = new stdClass();
               }

               if(isset($dataBodyArray['form_submission_fields'])){
                  $popupSubmissionSettings = isset($submissionSettings->campaigns->$popupID) ? $submissionSettings->campaigns->$popupID :  new stdClass();
                  $allFields = isset($popupSubmissionSettings->allFields) ? $popupSubmissionSettings->allFields : array();
                  $newFields = json_decode($dataBodyArray['form_submission_fields']);
                  if(is_array($newFields)){
                     $updatedFields = array_unique(array_merge($allFields, $newFields));
                     //error_log('updatedFields: '.wp_json_encode($updatedFields));
                     $submissionSettings->campaigns->$popupID->allFields = $updatedFields;
                  }
               }
               if(isset($dataBodyArray['form_submission'])){
                  $submissionSettings->campaigns->$popupID->status = $dataBodyArray['form_submission'];
               }
               if(isset($dataBodyArray['form_submission_title'])){
                  $submissionSettings->campaigns->$popupID->popup_title = $dataBodyArray['form_submission_title'];
               }

               // error_log('$submissionSettings: '.wp_json_encode($submissionSettings));
               BravePopup_Settings::save_settings( array( 'submission' => $submissionSettings ) );
            }
            
            //Send the Updated Popup
            $popup = get_post( (int) $args['id'] );
            if ( empty( $popup ) || empty( $popup->ID ) || $popup->post_type !== 'popup' ) {   
               return new WP_Error( 'rest_post_not_found', __( 'Popup Not Found' ), array( 'status' => 404 ) ); 
            }
            //Get MetaData
            foreach ($this->metaData as $value) {
                $popup->$value  = get_post_meta($args['id'], $value, true);
            }
            $popup->{"popup_data"}  = get_post_meta($args['id'], 'popup_data', false);
    
            return new WP_REST_Response($popup);
        }

        return false;

    }

    public function delete_popup( $request ) {
      $popup = get_post( (int) $request->get_param( 'id' ) );

      if ( is_wp_error( $popup ) ) {
            return $popup;
      }

      $id    = (int)$popup->ID;

      //Remove the Popup
      $previous = $this->prepare_item_for_response( $popup, $request );
      $result   = wp_delete_post( $id, true );
      $response = new WP_REST_Response();
      $response->set_data( array( 'deleted' => true, 'previous' => $previous ) );
      
      if($result){
         //Update Popup Placement Database
         try{
            $currentSettings = get_option('_bravepopup_settings');
            $currentVis = $currentSettings && isset($currentSettings['visibility']) ? $currentSettings['visibility'] : array() ;
            $currentGoals = $currentSettings && isset($currentSettings['goal']) ? $currentSettings['goal'] : array() ;
            if(isset($currentVis[$id])){ unset($currentVis[$id]); }
            if(isset($currentGoals[$id])){ unset($currentGoals[$id]); }
            $settings = array( 'visibility' => $currentVis , 'goals' => $currentGoals );
            BravePopup_Settings::save_settings( $settings );

            //Remove The Stats from Stats Database
            if (class_exists('BravePop_Analytics') && class_exists('BravePop_Geolocation')) {
               $braveStats = new BravePop_Analytics();
               $braveStats->removePopupStat( intval($id) );
            }

         }catch(Exception $e){
            error_log('Delete Error: '.wp_json_encode($e->getMessage()));
         }
      }


      if ( ! $result ) {
         return new WP_Error( 'rest_cannot_delete', __( 'The popup cannot be deleted.' ), array( 'status' => 500 ) );
      }

      return $response;
   }

   public function rest_get_wpdata( $request ) {
      $type = $request->get_param( 'type' );
      $ids = $request->get_param( 'ids' );
      if($ids){
         $ids = array_map('intval', explode(',', $ids));
      }else{ $ids  = array(); }

      $wpData = bravepop_get_wpdata( $type , $ids);
      if(function_exists('bravepop_dynamic_data')){
         $wpData->dynamic = bravepop_dynamic_data();
      }

      return new WP_REST_Response($wpData);
   }

   public function rest_search_wpdata( $request ) {
      $type = $request->get_param( 'type' );
      $query = $request->get_param( 'query' );
      $foundData = array();
      if($type && $query){
         if($type === 'pages'){ $type= 'page';}
         if($type === 'posts'){ $type= 'post';}
         if($type === 'products'){ $type= 'product';}
         $foundData = bravepop_search_wpdata( $type, $query );
      }
      return new WP_REST_Response($foundData);
   }
   

    public function rest_get_wpPosts( $request ) {

        $type = $request->get_param( 'type' );
        $postType = $request->get_param( 'postType' );
        $filterType = $request->get_param( 'filterType' );
        $count = $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' )  : 3;

        $categories = $request->get_param( 'categories' ) ?  explode (",", $request->get_param( 'categories' )) : ''; 
        $tags = $request->get_param( 'tags' ) ?  explode (",", $request->get_param( 'tags' )) : ''; 
        $postIDs = $request->get_param( 'include' ) ?  explode (",", $request->get_param( 'include' )) : ''; 
        $postID  = $request->get_param( 'postID' ) ?  explode (",", $request->get_param( 'postID' )) : ''; 
        
        return bravepop_get_wpPosts( $type, $postType, $filterType, $count, $categories, $tags, $postIDs, $postID );

    }


    public function rest_get_wooProducts($request){
      if ( BRAVEPOP_WOO_ACTIVE ) {
            // Put your plugin code here
        
        $type = $request->get_param( 'type' );
        $filterType = $request->get_param( 'filterType' );
        $count = $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' )  : 3;

        $categories = $request->get_param( 'categories' ) ?  explode (",", $request->get_param( 'categories' )) : ''; 
        $tags = $request->get_param( 'tags' ) ?  explode (",", $request->get_param( 'tags' )) : ''; 
        $postIDs = $request->get_param( 'include' ) ?  explode (",", $request->get_param( 'include' )) : ''; 
        $postID  = $request->get_param( 'productID' ) ?  explode (",", $request->get_param( 'productID' )) : ''; 

        return bravepop_get_wooProducts($type, $filterType, $count, $categories, $tags, $postIDs, $postID);

      }else{
            return new WP_REST_Response(array('error'=> 'Woocommerce Not Installed!'));
      }
    }

   public function get_analytics($request){
      $braveStats = new BravePop_Analytics();
      $stats = array();
      $stats['stats'] = $braveStats->fetchAllStats();
      return new WP_REST_Response($stats);
   }

   public function get_popup_stat($request){
      $popupID = $request->get_param( 'id' );
      $startDate = $request->get_param( 'start' ) ? $request->get_param( 'start' ).' 00:00:00' : '2020-01-01 00:00:00';
      $endDate = $request->get_param( 'end' ) ? $request->get_param( 'end' ).' 23:59:59' : date('Y').'-'.date('m').'-'.date('d').' 23:59:59';
      // error_log('$startDate: '.$startDate);
      // error_log('$endDate: '.$endDate);
      $braveStats = new BravePop_Analytics();
      $conversions = $braveStats->fetchPopupGoals($popupID, $startDate, $endDate);
      return new WP_REST_Response($conversions);
   }

   public function delete_popup_stat($request){
      $popupID = $request->get_param( 'id' );
      $braveStats = new BravePop_Analytics();
      $deleted = $braveStats->removePopupStat($popupID);
      return new WP_REST_Response($deleted);
   }

   public function get_analytics_csv($request){
      $popupID = $request->get_param( 'id' );
      $entries = array();

      if($popupID){
         $braveStats =  new BravePop_Analytics();
         $entries = $braveStats->get_analytics_csv( $popupID);
      }

      return new WP_REST_Response($entries);
   }

   public function get_submissions($request){
      $currentSettings = get_option('_bravepopup_settings');
      $submissionSettings = $currentSettings && isset($currentSettings['submission']) ? ($currentSettings['submission']) : new stdClass();

      //Add Camapign's Current Status Property
      if(isset($currentSettings['submission']->campaigns)){
         $campaginsWithStatus = new stdClass();
         foreach ($currentSettings['submission']->campaigns as $popupID => $value) {
            $campaginsWithStatus->$popupID  = $value;
            $campaginsWithStatus->$popupID->removed  = get_post( intval($popupID) ) ? false : true;
         }
         $currentSettings['submission']->campaigns = $campaginsWithStatus;
      }

      
      return new WP_REST_Response($submissionSettings);
   }

   public function get_submission_entries($request){
      $popupID = !empty($request->get_param( 'id' )) ? $request->get_param( 'id' ) : false;
      $count = !empty($request->get_param( 'count' )) ? $request->get_param( 'count' ) : 100;
      $page = !empty($request->get_param( 'page' )) ? (int)$request->get_param( 'page' ) : 0;
      
      if($popupID){
         $submissionClass =  new BravePop_Submissions();
         $submClass = $submissionClass->fetchSubmissions( $popupID,  $count, $page );
         //error_log('get_submission_entries: '.wp_json_encode($submClass));
         $submission_entries = array();

         if(isset($submClass['submissions']) ){
            foreach ($submClass['submissions'] as $key => $entry) {

               $theEntry =  $entry;
               $theEntry->id = intval($theEntry->id);
               $theEntry->popup = intval($theEntry->popup);
               $theEntry->automation = $theEntry->automation ? json_decode($theEntry->automation) :'';
               $theEntry->settings = $theEntry->settings ? json_decode($theEntry->settings) : '';
               $theEntry->form_settings = $theEntry->form_settings ? json_decode($theEntry->form_settings) : '';
               $theEntry->submission = $theEntry->submission ? json_decode($theEntry->submission) : '';
               $theEntry->user = $theEntry->user ? json_decode($theEntry->user) : '';
   
               $submission_entries[] = $theEntry;
            }
         }

         return new WP_REST_Response(array('total'=> isset($submClass['total']) ? $submClass['total'] : 0,'submissions'=>$submission_entries) );
         
      }else{
         return new WP_REST_Response(array());
      }
      

   }
   public function get_submission_entry($request){
      $entryID = $request->get_param( 'id' );
      if($entryID){
         $submissionClass =  new BravePop_Submissions();
         $entry = $submissionClass->getSingleSubmission( $entryID );
         return new WP_REST_Response($entry);
      }else{
         return new WP_REST_Response(array('error'=>'Entry ID not Provided!'));
      }
   }

   public function delete_submission_entries($request){
      $entryIDs = $request->get_param( 'entries' );
      
      $deleted  = false;
      if($entryIDs){
         $ids = implode( ',', array_map( 'absint', json_decode($entryIDs )) );
         $submissionClass =  new BravePop_Submissions();
         $deleted = $submissionClass->deleteSubmissions( $ids );
      }
      return new WP_REST_Response($deleted);
   }

   public function delete_submission_campaign($request){
      $campaignID = $request->get_param( 'ID' );
      
      $deleted  = false;
      if($campaignID){
         $submissionClass =  new BravePop_Submissions();
         $deleted = $submissionClass->deleteAllSubmissions( intval($campaignID) );

         $currentSettings = get_option('_bravepopup_settings');
         $submissionSettings = $currentSettings && isset($currentSettings['submission']) ? ($currentSettings['submission']) : new stdClass();
         unset($submissionSettings->campaigns->$campaignID);
         BravePopup_Settings::save_settings( array( 'submission' => $submissionSettings ) );
         
      }
      return new WP_REST_Response($deleted);
   }
   

   public function get_submission_csv($request){
      $popupID = $request->get_param( 'id' );
      $fieldsOnly = $request->get_param( 'fields_only' );
      $entries = array();

      if($popupID){
         $submissionClass =  new BravePop_Submissions();
         $entries = $submissionClass->get_submission_csv( $popupID, $fieldsOnly );
      }

      return new WP_REST_Response($entries);
   }

   public function update_entry($request){
      $entryID = $request->get_param( 'id' );
      $dataBodyArray = $request->get_json_params();
      $data_key = isset($dataBodyArray['data_key']) ? ($dataBodyArray['data_key']) : false;
      $data_value = isset($dataBodyArray['data_value']) ? json_decode($dataBodyArray['data_value']) : false;
      $entries = array();

      if($entryID && $data_key && $data_value){
         $dataToMerge = array($data_key => $data_value);
         $submissionClass =  new BravePop_Submissions();
         $entries = $submissionClass->updateSubmission( $dataToMerge, array('id'=> (int)$entryID) );
      }

      return new WP_REST_Response($entries);
   }

   public function dismiss_rating_box(WP_REST_Request $request){
      try{
         update_option('brave_plugin_rated', true);
         return new WP_REST_Response(true);
      }catch(Exception $e){
         error_log(wp_json_encode($e->getMessage()));
      }

   }
}
$brave_rest_server = new BravePop_Rest_Server();
$brave_rest_server->hook_rest_server();