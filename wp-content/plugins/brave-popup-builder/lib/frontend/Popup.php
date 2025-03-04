<?php
/**
 * Popup Element.
 * Renders the Popup
 */


if ( ! class_exists( 'BravePop_Popup' ) ) {
    class BravePop_Popup {

         public $elementStyles = array(); 
         public $elementScripts = array();
         public $popupID;
         public $popupType;
         public $forceLoad;
         public $forceStep;
         public $popupData;
         public $animationData;
         public $advancedAnimation;
         public $hasAnimation;
         public $hasContAnim;
         public $hasDesktopEmbed;
         public $hasMobileEmbed;
         public $dynamicData;
         public $popupOrder;
         public $ajaxLoad;
         public $popupfonts = array();
         public $closeData = array();
         public $videoData = array();
         public $hasVideo = false;
         public $hasVimeo = false;
         public $hasYoutube = false;
         public $hasLoginElement = false;
         public $hasWpPosts = false;
         public $hasWpProducts = false;
         public $userTypeMatch = true;
         public $refererMatch = true;
         public $languageMatch = true;
         public $hasCartItems = true;
         public $countryMatch  = true;
         public $learnDashMatch = true;
         public $EDDMatch = true;
         public $cartFilterMatch = array(true,true,true,true,true);
         public $purchaseMatch = true;
         public $notpurchaseMatch = true;
         public $madePurchaseBeforeMatch = true;
         public $bodyClassMatch = true;
         public $utmMatch = true;
         public $utmNegativeMatch = true;
         public $timers = array();

         

        function __construct($popupID=null, $popupType='popup', $forceLoad=false, $forceStep=false, $customContent=false ) {
               $this->popupID = $popupID;
               $this->popupType = $popupType;
               if($customContent === false && !get_post_meta($popupID, 'popup_data', true)) { return; }
               $this->forceLoad = $forceLoad;
               $this->forceStep = $forceStep;
               $this->popupData = $customContent ? json_decode($customContent) : json_decode(get_post_meta($popupID, 'popup_data', true));
               $preparedAnimation = bravepop_prepare_animation($this->popupData);
               $this->animationData = $preparedAnimation['animationData'];
               $this->advancedAnimation = $preparedAnimation['advancedAnimation'];
               $this->hasAnimation = $preparedAnimation['hasAnimation'];
               $this->hasContAnim = $preparedAnimation['hasContAnim'];
               $this->hasDesktopEmbed = $popupType === 'content' ? true : false;
               $this->hasMobileEmbed = $popupType === 'content' ? true : false;
               $this->dynamicData = $this->popup_get_dynamicData();
               $this->popup_visibility_filter();
               $this->popupData->settings = isset($this->popupData->settings) ? $this->popupData->settings : new stdClass();
               $this->popupOrder = !empty($this->popupData->settings->advanced->orderOverride) && isset($this->popupData->settings->advanced->order) ? $this->popupData->settings->advanced->order : null;
               $this->ajaxLoad = !empty($this->popupData->settings->advanced->ajaxLoad) ? $this->popupData->settings->advanced->ajaxLoad : false;
               $this->timers = [];

               //If its a Child Popup, use the parent popups' settings except trigger and frequency settings
               $parentPopupID = json_decode(get_post_meta($popupID, 'popup_parentID', true));
               if($parentPopupID){
                  $parentPopupData =  json_decode(get_post_meta($parentPopupID, 'popup_data', true));
                  if($parentPopupData && $parentPopupData->settings){
                     //$this->popupData->settings->goal = $parentPopupData->settings->goal;
                     //$this->popupData->settings->goalAction = $parentPopupData->settings->goalAction;
                     $this->popupData->settings->audience = $parentPopupData->settings->audience;
                     $this->popupData->settings->placement = $parentPopupData->settings->placement;
                  }
               }

               //error_log('$popupType: '.$this->popupID. ' - '. $popupType); 
               if($this->popupID && $this->popupData && $popupType === 'popup') {
                  if($this->userTypeMatch && $this->refererMatch && $this->languageMatch && $this->countryMatch && $this->hasCartItems && $this->utmMatch && $this->utmNegativeMatch && !in_array(false, $this->cartFilterMatch) && $this->purchaseMatch && $this->notpurchaseMatch && $this->madePurchaseBeforeMatch && $this->learnDashMatch && $this->EDDMatch && $this->bodyClassMatch) {
                     add_action('wp_footer', array( $this, 'popup_render' ), 5 );
                     add_action('wp_footer', array( $this, 'popup_inline_script' ), 16); //must render after popup_render
                     add_action('wp_footer', array( $this, 'popup_external_scripts' ) );
                     add_action('wp_footer', array( $this, 'popup_embedlock_script' ), 65 );
                  }
               }

               if($popupType === 'content'){
                  add_action('wp_head', array( $this, 'popup_content_lock_script' ), 5 );
               }

               //trace();
         }


      public function popup_visibility_filter() {
         $userTypeMatch = true; $refererMatch = true;
         $brave_preview = filter_input(INPUT_GET, 'brave_popup');

         // User Type Filter
         if(isset($this->popupData->settings->audience->userType) && $this->popupData->settings->audience->userType === 'guest' && is_user_logged_in()){
            $userTypeMatch  = false;
         }
         if(isset($this->popupData->settings->audience->userType) && $this->popupData->settings->audience->userType === 'registered' && !is_user_logged_in() && empty($this->popupData->settings->audience->rolesFilter)){
            $userTypeMatch  = false;
         }
         if(isset($this->popupData->settings->audience->userType) && $this->popupData->settings->audience->userType === 'registered' && !empty($this->popupData->settings->audience->rolesFilter)){
            if(isset($this->popupData->settings->audience->roles) && is_array($this->popupData->settings->audience->roles) && count( $this->popupData->settings->audience->roles) > 0){
               if(is_user_logged_in()){
                  $user = wp_get_current_user();
                  $roles = ( array ) $user->roles;
                  if(!array_intersect($roles, $this->popupData->settings->audience->roles)){
                     $userTypeMatch  = false;
                  }
               }
               if(!in_array('guest',$this->popupData->settings->audience->roles) && !is_user_logged_in()){
                  $userTypeMatch  = false;
               }
            }
         }
         if(isset($this->popupData->settings->audience->userType) && $this->popupData->settings->audience->userType === 'registered' && !empty($this->popupData->settings->audience->userFieldMatch)){
            if(isset($this->popupData->settings->audience->userfieldConditions) && is_array($this->popupData->settings->audience->userfieldConditions) && count( $this->popupData->settings->audience->userfieldConditions) > 0){
               $userFieldValmatched = true;
               if(is_user_logged_in()){
                  $user = wp_get_current_user();
                  
                  foreach ($this->popupData->settings->audience->userfieldConditions as $key => $condition) {
                     if(isset($condition->fieldID) && isset($condition->action)){
                        $fKey = $condition->fieldID; $fVal = isset($condition->value) ? $condition->value : false;
                        $fieldVal = get_user_meta( $user->ID, $fKey, true );
                        if($userFieldValmatched && $condition->action === 'noval' && $fieldVal){   $userFieldValmatched = false;  }
                        if($userFieldValmatched && $condition->action === 'hasval' && !$fieldVal){   $userFieldValmatched = false;  }
                        if($userFieldValmatched && $condition->action === 'equal' && (!$fieldVal || ($fieldVal && ($fieldVal !== $fVal))) ){   $userFieldValmatched = false;  }
                        if($userFieldValmatched && $condition->action === 'notequal' && (($fieldVal && ($fieldVal === $fVal))) ){   $userFieldValmatched = false;  }
                     }
                  }
               }

               if(!$userFieldValmatched){
                  $userTypeMatch  = false;
               }
            }
         }
         if(isset($this->popupData->settings->audience->userType) && $this->popupData->settings->audience->userType === 'admin' && !current_user_can('activate_plugins')){
            $userTypeMatch  = false;
         }

         $this->userTypeMatch = $userTypeMatch;


         //body class match
         if(!empty($this->popupData->settings->placement->body_class_filter) && (!empty($this->popupData->settings->placement->body_classes) || !empty($this->popupData->settings->placement->body_classes_exclude))){
            $current_body_classes = get_body_class();
         }

         if(!empty($this->popupData->settings->placement->body_class_filter) && !empty($this->popupData->settings->placement->body_classes)){
            $body_classes_raw = explode(",",$this->popupData->settings->placement->body_classes); 
            $body_classes = array(); foreach ($body_classes_raw as $key => $value) { $body_classes[] = trim($value);  };
            $foundItems = array_intersect($current_body_classes, $body_classes);
            if(count($foundItems) === 0){
               $this->bodyClassMatch = false;
            }
         }

         if(!empty($this->popupData->settings->placement->body_class_filter) && !empty($this->popupData->settings->placement->body_classes_exclude)){
            $body_classes_raw = explode(",",$this->popupData->settings->placement->body_classes_exclude); 
            $body_classes = array(); foreach ($body_classes_raw as $key => $value) { $body_classes[] = trim($value);  };
            $foundItems = array_intersect($current_body_classes, $body_classes);
            if(count($foundItems) > 0){
               $this->bodyClassMatch = false;
            }
         }

         //Referer match
         if(isset($this->popupData->settings->audience->referrals) && count($this->popupData->settings->audience->referrals) > 0){
            foreach ($this->popupData->settings->audience->referrals as $key => $referal) {
               //error_log('$refererMatch: '.wp_get_referer().'----'.$referal->link.'----'.wp_json_encode(strpos(wp_get_referer(), $referal->link)));
               $refered_site = wp_get_referer(); 
               $defined_site = !empty($referal->link) ? $referal->link : '';
               $conditionMatch = !empty($referal->disabled) ? true : false;
               $domainMatch = !empty($referal->domain) ? true : false;
               if(!empty($refered_site) && $domainMatch){
                  $refered_site = bravepopup_url_to_domain($refered_site);
               }
               if(!empty($defined_site) && $domainMatch){
                  $defined_site = bravepopup_url_to_domain($defined_site);
               }
               if(!empty($defined_site) && strpos($refered_site, $defined_site) === false && $conditionMatch === false){
                  $refererMatch = false;
               }
               if(!empty($defined_site) && strpos($refered_site, $defined_site) !== false && $conditionMatch === true){
                  $refererMatch = false;
               }
            }
         }

         $this->refererMatch = $refererMatch;

         //Woocommerce Filters Match
         $wooFilters = isset($this->popupData->settings->woocommerce) ? $this->popupData->settings->woocommerce : false;
         if(!empty($this->popupData->settings->woocommerce->has_cart_item) && function_exists('bravepop_woo_cart_filter')){
            $cartFileterd = bravepop_woo_cart_filter($wooFilters);
            $totalCartItems = isset($GLOBALS['bravepop_cart_data']->count) ? (int)$GLOBALS['bravepop_cart_data']->count : 0;
            if(($totalCartItems === 0)){
               $this->hasCartItems = false;
            }
            if( ($totalCartItems > 0) && !empty($wooFilters->cart_includes) || !empty($wooFilters->cart_excludes) ){
               $this->cartFilterMatch[0] =  $cartFileterd->cart_includes;
               $this->cartFilterMatch[1] =  $cartFileterd->cart_excludes;
            }
            if(!empty($wooFilters->cart_value_more) || !empty($wooFilters->cart_value_less)){
               $this->cartFilterMatch[2] =  $cartFileterd->cart_value_more;
               $this->cartFilterMatch[3] =  $cartFileterd->cart_value_less;
            }
         }
         if(is_user_logged_in() && (!empty($this->popupData->settings->woocommerce->purchase_filter) ||!empty($this->popupData->settings->woocommerce->made_purchase_filter)) && function_exists('bravepop_woo_purchase_filter')){
            $purchaseFiltered = bravepop_woo_purchase_filter($wooFilters);
            $this->purchaseMatch = $purchaseFiltered->purchased;
            $this->notpurchaseMatch = $purchaseFiltered->notpurchased;
            $this->madePurchaseBeforeMatch = $purchaseFiltered->purchased_before;
         }

         //Language Filter
         if(!empty($this->popupData->settings->filters->language) && function_exists('pll_current_language')){
            if($this->popupData->settings->filters->language === pll_current_language()){
               $this->languageMatch  = true;
            }else{
               $this->languageMatch  = false;
            }
         }
         if(!empty($this->popupData->settings->filters->language) && class_exists( 'SitePress' )){
            $currentLang = apply_filters( 'wpml_current_language', NULL );
            if($this->popupData->settings->filters->language === $currentLang ){
               $this->languageMatch  = true;
            }else{
               $this->languageMatch  = false;
            }
         }
         if(!empty($this->popupData->settings->filters->language) && function_exists('weglot_get_current_language')){
            if($this->popupData->settings->filters->language === weglot_get_current_language()){
               $this->languageMatch  = true;
            }else{
               $this->languageMatch  = false;
            }
         }
         

         //Country Filter
         global $bravepop_global;
         $country_code = !empty($bravepop_global['user_country_code']) ? $bravepop_global['user_country_code'] : '';
         if(!empty($this->popupData->settings->filters->countryFilter) && isset($this->popupData->settings->filters->countries) && is_array($this->popupData->settings->filters->countries) && count($this->popupData->settings->filters->countries) > 0){
            if(class_exists('BravePop_Geolocation') && !$country_code){
               $geolocation_instance = new BravePop_Geolocation();
               $user_ip_address = $geolocation_instance->get_ip_address();
               if(!empty($user_ip_address) && strlen($user_ip_address) > 7){
                  $user_geolocation = $geolocation_instance->geolocate_ip( $user_ip_address );
                  $country_code = $user_geolocation && $user_geolocation['iso_code'] ? $user_geolocation['iso_code'] : '';
               }
               $bravepop_global['user_country_code'] = $country_code;
            }
            if(in_array($country_code, $this->popupData->settings->filters->countries)){
               $this->countryMatch  = true;
            }else{
               $this->countryMatch  = false;
            }
         }

         //LearnDash Filter
         if( function_exists('learndash_is_lesson_complete') && function_exists('bravepop_LearnDash_filter_match') && !empty($this->popupData->settings->filters->learnDashFilter) && isset($this->popupData->settings->filters->learnDash) 
            && is_array($this->popupData->settings->filters->learnDash) && count($this->popupData->settings->filters->learnDash) > 0){
            $this->learnDashMatch = bravepop_LearnDash_filter_match($this->popupData->settings->filters->learnDash);
         }

         //EDD Filter
         if( class_exists( 'Easy_Digital_Downloads' ) && function_exists('bravepop_EDD_filter_match') && !empty($this->popupData->settings->filters->eddFilter) && isset($this->popupData->settings->filters->EDD) 
            && is_array($this->popupData->settings->filters->EDD) && count($this->popupData->settings->filters->EDD) > 0){
            $this->EDDMatch = bravepop_EDD_filter_match($this->popupData->settings->filters->EDD);
         }

         // UTM Keywords Filter
         if(!empty($this->popupData->settings->placement->utm) && !empty($this->popupData->settings->placement->utmKeywords)){
            $utm_keywords_positive = explode(",",$this->popupData->settings->placement->utmKeywords); 
            $containsKeyword = false;

            foreach ($utm_keywords_positive as $key => $keyword) { 
               $ukeyword =  trim($keyword);
               $kMatchesArr  = explode("=",$ukeyword);
               $kMatcher = str_replace('*', '([^=]*)', $kMatchesArr[0]);
               $matchValue = strpos($ukeyword, '=') !== false;

               foreach ($_GET as $currentKey => $currentVal) { 
                  $kMatches  = preg_match ('/'.$kMatcher.'/i', $currentKey);
                  if($kMatches){
                     if($matchValue && isset($kMatchesArr[1])){
                        $valueToMatch = $kMatchesArr[1];
                        $vMatcher = str_replace('*', '([^=]*)', $valueToMatch);
                        $vMatches  = preg_match ('/'.$vMatcher.'/i', $currentVal);
                        if($vMatches){
                           $containsKeyword = true;
                        }
                     }else{
                        $containsKeyword = true;
                     }
                  }
               }
            };

            if(!$containsKeyword){
               $this->utmMatch = false;
            }
         }

         // UTM Keywords Negative Filter
         if(!empty($this->popupData->settings->placement->utm) && !empty($this->popupData->settings->placement->utmKeywordsNegative)){
            $utm_keywords_negative = explode(",",$this->popupData->settings->placement->utmKeywordsNegative); 
            $containsNegKeyword = false;

            foreach ($utm_keywords_negative as $key => $keyword) { 
               $ukeyword =  trim($keyword);
               $kMatchesArr  = explode("=",$ukeyword);
               $kMatcher = str_replace('*', '[a-zA-Z0-9]', $kMatchesArr[0]);
               $matchValue = strpos($ukeyword, '=') !== false;

               foreach ($_GET as $currentKey => $currentVal) { 
                  $kMatches  = preg_match ('/'.$kMatcher.'/i', $currentKey);
                  if($kMatches){
                     if($matchValue && isset($kMatchesArr[1])){
                        $valueToMatch = $kMatchesArr[1];
                        $vMatcher = str_replace('*', '[a-zA-Z0-9]', $valueToMatch);
                        $vMatches  = preg_match ('/'.$vMatcher.'/i', $currentVal);
                        if($vMatches){
                           $containsNegKeyword = true;
                        }
                     }else{
                        $containsNegKeyword = true;
                     }
                     
                  }
               }
              
            };

            if($containsNegKeyword){
               $this->utmNegativeMatch = false;
            }
         }

         //If is Previewing, skip all Filtering
         if(!empty($brave_preview)){
            $this->userTypeMatch  = true; 
            $this->refererMatch = true; 
            $this->countryMatch  = true; 
            $this->languageMatch  = true;
            $this->utmMatch = true;
            $this->utmNegativeMatch = true;
            $this->bodyClassMatch = true;
         }

      }

      
      public function popup_external_scripts() {
         if($this->advancedAnimation && $this->hasAnimation) {
            wp_enqueue_script( 'bravepop_animejs', BRAVEPOP_PLUGIN_PATH . 'assets/frontend/anime.min.js' ,array(),'3.1.0',true);
            wp_enqueue_script( 'bravepop_animation', BRAVEPOP_PLUGIN_PATH . 'assets/frontend/animate.js' ,array(),'1.1.0',true);
         }
         if($this->hasLoginElement){
            wp_enqueue_script( 'bravepop_loginjs', BRAVEPOP_PLUGIN_PATH . 'assets/frontend/login.js' ,array(),'1.1.0',true);
            wp_enqueue_style('bravepop_login_element',  BRAVEPOP_PLUGIN_PATH . 'assets/css/wp_login.min.css' );
         }
         if($this->hasWpPosts){
            wp_enqueue_style('bravepop_posts_element',  BRAVEPOP_PLUGIN_PATH . 'assets/css/wp_posts.min.css');
         }
         if($this->hasWpProducts){
            wp_enqueue_style('bravepop_woocommerce_element',  BRAVEPOP_PLUGIN_PATH . 'assets/css/woocommerce.min.css');
         }
      }

      public function popup_content_lock_script(){
         wp_enqueue_script( 'bravepop_embedlock', BRAVEPOP_PLUGIN_PATH . 'assets/frontend/embedlock.js' ,array(),'1.1.0',false);
      }

      public function popup_embedlock_script() { ?>
         <script>
            <?php if($this->hasDesktopEmbed || $this->hasMobileEmbed){ ?>
               brave_lockContent(<?php print_r(absint($this->popupID));?>);
            <?php } ?>
         </script>
      <?php }

      public function popup_inline_js() {  ?>
            <script>
               <?php   
                  //Remove Sensetive Data       
                  $settingsData = $this->popupData->settings;
                  if(isset($settingsData->notification->zapierURL)){  unset($settingsData->notification->zapierURL);   }
                  if(isset($settingsData->notification->emailAddresses)){  unset($settingsData->notification->emailAddresses);   }

                  //Set Default GoalAction Data
                  if(!isset($settingsData->goalAction)){ 
                     $settingsData->goalAction = new stdClass(); 
                     $settingsData->goalAction->type = 'step';
                     $settingsData->goalAction->step = 0;
                  }

                  //If AB test Active Get all the Variant Ids for the frequency settings
                  $popup_parentID = get_post_meta( $this->popupID, 'popup_parentID', true );
                  $popVariantsData = get_post_meta( $popup_parentID ? $popup_parentID : $this->popupID, 'popup_abtest', true );
                  $popVariants = $popVariantsData ? json_decode($popVariantsData) : false;
                  $allVariants = [];
                  if($popVariants && !empty($popVariants->items) && is_array($popVariants->items)){
                     foreach ($popVariants->items as $key => $pop) {
                        if(!empty($pop->id)){
                           $allVariants[] = $pop->id;
                        }
                     }
                  }
               ?>

               document.addEventListener("DOMContentLoaded", function(event) {
                  brave_popup_data[<?php print_r(absint($this->popupID));?>] = {
                  title: '<?php print_r(esc_attr(get_the_title(absint($this->popupID)))); ?>',
                  type: '<?php print_r(esc_attr($this->popupType));?>',
                  fonts: <?php print_r(apply_filters( 'bravepop_google_fonts', wp_json_encode($this->popupfonts) )); ?>,
                  advancedAnimation:<?php print_r(wp_json_encode($this->advancedAnimation));?>,
                  hasAnimation: <?php print_r(wp_json_encode($this->hasAnimation)); ?>,
                  hasContAnim:  <?php print_r(wp_json_encode($this->hasContAnim)); ?>,
                  animationData: <?php print_r(wp_json_encode($this->animationData)); ?>,
                  videoData: <?php print_r(wp_json_encode($this->videoData)); ?>,
                  hasYoutube: <?php print_r(wp_json_encode($this->hasYoutube)); ?>,
                  hasVimeo: <?php print_r(wp_json_encode($this->hasVimeo)); ?>,
                  settings: <?php print_r(wp_json_encode($settingsData)); ?>,
                  close: <?php print_r(wp_json_encode($this->closeData)); ?>,
                  forceLoad: <?php print_r(wp_json_encode($this->forceLoad)); ?>,
                  forceStep: <?php print_r(wp_json_encode($this->forceStep)); ?>,
                  hasDesktopEmbed: <?php print_r(wp_json_encode($this->hasDesktopEmbed)); ?>,
                  hasMobileEmbed: <?php print_r(wp_json_encode($this->hasMobileEmbed)); ?>,
                  hasLoginElement: <?php print_r(wp_json_encode($this->hasLoginElement)); ?>,
                  schedule:<?php $popup_schedule = get_post_meta( $this->popupID, 'popup_schedule', true ); print_r($popup_schedule ? $popup_schedule : '{}');?>,
                  parentID:<?php print_r($popup_parentID ? $popup_parentID : 'false');?>,
                  variants: <?php print_r(wp_json_encode($allVariants)); ?>,
                  embedLock: false,
                  ajaxLoad: <?php print_r(wp_json_encode($this->ajaxLoad)); ?>,
                  ajaxLoaded: false,
                  timers: <?php print_r(wp_json_encode($this->timers)); ?>,
               }
                  brave_init_popup(<?php print_r(absint($this->popupID));?>, brave_popup_data[<?php print_r(absint($this->popupID));?>]);
               });

               <?php 
                  $elementScripts = implode('', $this->elementScripts);
                  print_r(esc_js($elementScripts)); 
               ?>
         </script>
      <?php }

      public function popup_inline_css() {  ?>
            <style type='text/css'>
               <?php
                  //Popup Styles
                  if($this->popupData->steps){
                     foreach ($this->popupData->steps as $key => $step) {
                        if($step->desktop){
                           print_r($this->popup_generate_styles($step->desktop, absint($key), 'desktop'));
                        } 
                        if($step->mobile){
                           print_r($this->popup_generate_styles($step->mobile, absint($key), 'mobile'));
                        } 
                     }
                  }

                  if($this->popupOrder){
                     print_r('#brave_popup_'.$this->popupID.' { z-index: '.(999999999 + intval($this->popupOrder)).'}');
                  }

                  //Element Styles
                  $elementStyles = implode('', $this->elementStyles);
                  print_r($elementStyles);

                  //---END----//
               ?>
            </style>
      <?php }

      public function popup_inline_script() { 
         $inlineScript = $this->popup_inline_js();
         $inlineStyle = $this->popup_inline_css();
         print_r($inlineScript.$inlineStyle);
      }


      public function popup_get_element_fonts($element) { 
         $systemfonts = array('Arial', 'Arial Black', 'Cursive', 'Impact', 'Lucida Grande', 'Geneva', 'Verdana', 'Courier', 'Georgia', 'Palatino Linotype', 'None');
         if(isset($element->fontFamily) && !in_array( $element->fontFamily, $systemfonts)){
            $fontFamily = $element->fontFamily.(!empty($element->fontVariation) ? ':'.$element->fontVariation : '');
            if(!in_array($fontFamily, $this->popupfonts)){ $this->popupfonts[] = $fontFamily; }
         }
         if(isset($element->titlefontFamily) && !in_array( $element->titlefontFamily, $systemfonts)){
            $titlefontFamily = $element->titlefontFamily.(!empty($element->titlefontVariation) ? ':'.$element->titlefontVariation : '');
            if(!in_array($titlefontFamily, $this->popupfonts)){ $this->popupfonts[] = $titlefontFamily; }
         }
         if(isset($element->type) && $element->type === 'form' && isset($element->formData->settings->style->fontFamily) && !in_array( $element->formData->settings->style->fontFamily, $systemfonts)){
            $formfontFamily = $element->formData->settings->style->fontFamily.(!empty($element->formData->settings->style->fontVariation) ? ':'.$element->formData->settings->style->fontVariation : '');
            if(!in_array($formfontFamily, $this->popupfonts)) { $this->popupfonts[] = $formfontFamily; }
         }
         if(isset($element->type) && $element->type === 'form' && isset($element->formData->settings->button->fontFamily) && !in_array( $element->formData->settings->button->fontFamily, $systemfonts)){
            $formButtonfontFamily = $element->formData->settings->button->fontFamily.(!empty($element->formData->settings->button->fontVariation) ? ':'.$element->formData->settings->button->fontVariation : '');
            if(!in_array($formButtonfontFamily, $this->popupfonts)){  $this->popupfonts[] = $formButtonfontFamily;  }
         }
      }

      public function popup_get_dynamicData() {
         $hasDynamicData = false;
         if($this->popupData->steps){
            foreach ($this->popupData->steps as $key => $step) {
               $mobileElements = $step->mobile->content;
               $desktopElements = $step->desktop->content;
               $allElements = array_merge($desktopElements,$mobileElements);
               foreach ($allElements as $key => $element) {
                  if(!$hasDynamicData){
                     if(!empty($element->dynamic) || !empty($element->action->actionData->dynamicURL) ){
                        $hasDynamicData = true;
                     }
                  }
               }
            }
         }
         if($hasDynamicData){
            if(!isset($GLOBALS['bravepop_dynamic_data'])){
               $dynamicData = bravepop_dynamic_data();
               $GLOBALS['bravepop_dynamic_data'] = $dynamicData;
               return $dynamicData;
            }else{
               return $GLOBALS['bravepop_dynamic_data'];
            }
         }else{
            return false;
         }
      }

       
      public function popup_renderCSSClasses($stepSettings) { 
         if(!$stepSettings){ return ''; }
         $noContent = isset($stepSettings->content) && is_array($stepSettings->content) && count($stepSettings->content) === 0 ? 'brave_popup__step--noContent' : '' ;
         $position = isset($stepSettings->position) ? esc_attr($stepSettings->position) : 'center';
         $layout = isset($stepSettings->layout) ? esc_attr($stepSettings->layout) : 'boxed';
         $closeIcon = isset($stepSettings->closeButton) ? esc_attr($stepSettings->closeButton) : 'icon';
         $closeButtonPosition = isset($stepSettings->closeButtonPosition) ? esc_attr($stepSettings->closeButtonPosition) : 'inside_right';
         if($this->popupType === 'content'){$layout = 'embedded';}

         $classes = [];

         $classes[] = $noContent;
         $classes[] = 'position_'.$position;
         $classes[] = 'closeButton_'.($closeIcon === 'text' ? 'text':'icon');
         $classes[] = 'brave_popup__step--'.$layout;
         $classes[] = 'closeButtonPosition_'. $closeButtonPosition; 
         if(isset($stepSettings->fullWidth) && $stepSettings->fullWidth === true) {  $classes[] = 'brave_popup_fullWidth';  }
         if(isset($stepSettings->fullHeight) && $stepSettings->fullHeight === true) {  $classes[] = 'brave_popup_fullHeight';  }
         if(isset($stepSettings->overlay) && $stepSettings->overlay === false){}else{ $classes[] = 'has_overlay'; }
         if(isset($stepSettings->overlayClose) && $stepSettings->overlayClose === true){ $classes[] = 'has_overlay_close'; }
         if(isset($stepSettings->autoClose) && $stepSettings->autoClose === true){ $classes[] = 'has_autoClose'; }
         if((isset($stepSettings->scrollbar) && $stepSettings->scrollbar === true) && $layout !== 'float') {  $classes[] = 'brave_popup_show_scrollbar';  }
         
         //return wp_json_encode($stepSettings);
         return implode(" ",$classes);
      } 

      public function popup_renderCloseButton($stepSettings, $stepIndex, $position='above') { 
         if(!$stepSettings || (isset($stepSettings->closeButton) && ($stepSettings->closeButton === 'none'))){ return ''; }
         $closeStep = isset($stepSettings->closeStep) && $stepSettings->closeStep !== 'none' ? $stepSettings->closeStep : wp_json_encode(false);
         $closeButtonPosition = isset($stepSettings->closeButtonPosition) ? $stepSettings->closeButtonPosition : 'inside_right';
         $closeButtonType = isset($stepSettings->closeButton) && $stepSettings->closeButton === 'text' ? 'brave_popup__close--text' :'brave_popup__close--icon';
         $closeposition = 'brave_popup__close--'.$closeButtonPosition.'';
         $closeButtonText = isset($stepSettings->closeButtonText) ? $stepSettings->closeButtonText : 'Yes, Hide this Popup.';
         $closeColorRGB = isset($stepSettings->closeColor) && isset($stepSettings->closeColor->rgb) ? $stepSettings->closeColor->rgb :'0,0,0';
         $closeColorOpacity = isset($stepSettings->closeColor) && isset($stepSettings->closeColor->opacity) ? $stepSettings->closeColor->opacity :'1';
         $closeColor = isset($stepSettings->closeColor) && isset($stepSettings->closeColor->rgb) ? 'rgba('.$stepSettings->closeColor->rgb.', '.$closeColorOpacity.')' : 'rgba(0,0,0, 1)';
         $closeDelay = !empty($stepSettings->closeButtonDelay) && !empty($stepSettings->closeButtonDelayDuration) ? $stepSettings->closeButtonDelayDuration * 1000 : false;
         $closeDelayHide = $closeDelay ? 'style="display:none;"' : '';

         $closeIconSVG = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20"/><g><path d="M14.95 6.46L11.41 10l3.54 3.54-1.41 1.41L10 11.42l-3.53 3.53-1.42-1.42L8.58 10 5.05 6.47l1.42-1.42L10 8.58l3.54-3.53z"/></g></svg>';
         if(isset($stepSettings->closeButton) && $stepSettings->closeButton === 'back_icon'){
            $closeIconSVG = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M18 9v2H6l4 4l-1 2l-7-7l7-7l1 2l-4 4h12z" width="20" height="20" /></svg>';
         }
         if(isset($stepSettings->closeButton) && $stepSettings->closeButton === 'forward_icon'){
            $closeIconSVG = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2 11V9h12l-4-4l1-2l7 7l-7 7l-1-2l4-4H2z" width="20" height="20" /></svg>';
         }
         $closeText = isset($stepSettings->closeButton)  &&  $stepSettings->closeButton === 'text' ? '<span class="brave_popup__close__customtext">'.$closeButtonText.'</span>' : $closeIconSVG;
         $closeFunction = 'brave_close_popup(\''.$this->popupID.'\', '.$stepIndex.', '.$closeStep.')';

         $closeProgress = $closeDelay ? '<div class="brave_popup__close__progress-circle" data-duration="'.($closeDelay).'">
                              <span class="brave_popup__close__progress-text">'.($closeDelay/1000).'s</span>
                              <svg class="brave_popup__close__progress-ring" width="40" height="40">
                              <circle class="brave_popup__close__progress-ring-circle" stroke="'.$closeColor.'" stroke-width="3" fill="transparent" r="16" cx="20" cy="20"></circle>
                              </svg>
                           </div>' : '';
         return '<div class="brave_popup__close '.$closeposition.' '.$closeButtonType.'">
                  <div class="brave_popup__close__button" '.$closeDelayHide.' onclick="'.$closeFunction.'" onkeypress="if(event.key == \'Enter\') { '.$closeFunction.' }">'.$closeText.'</div>
                  '.$closeProgress.
               '</div>';
         
      } 

      public function popup_renderOverlay($stepSettings, $stepIndex) { 
         if(!$stepSettings || (isset($stepSettings->overlay) && $stepSettings->overlay === false)){ return ''; }
         if($this->popupType === 'content' && (!isset($stepSettings->overlay))){ return ''; }
         $overlayClose = !empty($stepSettings->overlayClose) ? 'onclick="brave_close_popup(\''.$this->popupID.'\', '.$stepIndex.')"' : '';
         $bgImage = '';
         if(!empty($stepSettings->overlayImage->image)){
            $bgImage = '<img class="brave_popup__step__overlay__img" src="'.$stepSettings->overlayImage->image.'" alt="Overlay Image" />';
         }
         return '<div class="brave_popup__step__overlay '.($overlayClose ? 'brave_popup__step__overlay--closable' : '').'" '.$overlayClose.'>'.$bgImage.'</div>';
      } 

      function popup_render_element($element, $stepIndex, $index, $device, $goalElementIDs, $ajaxRender, $currentURL ){
         $elements = '';
         switch ($element->type) {
            case 'text':
               $elmCalss = new BravePop_Element_Text($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ), $this->dynamicData);
               $elements .= $elmCalss->render();
               $elmStyle = $elmCalss->render_css();
               $this->elementStyles[$element->id] = $elmStyle;
               $this->popup_get_element_fonts($element);

               break;
            case 'button':
               $elmCalss = new BravePop_Element_Button($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ), $this->dynamicData);
               $elements .= $elmCalss->render();
               $elmStyle = $elmCalss->render_css();
               $this->elementStyles[$element->id] = $elmStyle;
               $this->popup_get_element_fonts($element);
               break;
            case 'shape':
               $elmCalss = new BravePop_Element_Shape($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ), $this->dynamicData);
               $elements .= $elmCalss->render();
               $elmStyle = $elmCalss->render_css();
               $this->elementStyles[$element->id] = $elmStyle;
               break;
            case 'image':
               $elmCalss = new BravePop_Element_Image($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ), $this->dynamicData);
               $elements .= $elmCalss->render();
               $elmStyle = $elmCalss->render_css();
               $this->elementStyles[$element->id] = $elmStyle;
               break;
            case 'sticker':
               if (class_exists('BravePop_Element_Sticker')) {
                  $elmCalss = new BravePop_Element_Sticker($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ), $this->dynamicData);
                  $elements .= $elmCalss->render();
                  $elmStyle = $elmCalss->render_css();
                  $this->elementStyles[$element->id] = $elmStyle;
               }
               break;
            case 'list':
               $elmCalss = new BravePop_Element_List($element, $this->popupID, $stepIndex, $index, $device);
               $elements .= $elmCalss->render();
               $elmStyle = $elmCalss->render_css();
               $this->elementStyles[$element->id] = $elmStyle;
               $this->popup_get_element_fonts($element);
               break;
            case 'social':
               if (class_exists('BravePop_Element_Social')) {
                  $elmCalss = new BravePop_Element_Social($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ), $currentURL);
                  $elements .= $elmCalss->render();
                  $elmStyle = $elmCalss->render_css();
                  $this->elementStyles[$element->id] = $elmStyle;
               }
               break;
            case 'video':
               if (class_exists('BravePop_Element_Video')) {
                  $elmCalss = new BravePop_Element_Video($element, $this->popupID, $stepIndex, $index, $device);
                  $elements .= $elmCalss->render();
                  $elmStyle = $elmCalss->render_css();
                  $elmJavascript = $elmCalss->render_js();
                  $this->elementStyles[$element->id] = $elmStyle;
                  $this->elementScripts[$element->id] = $elmJavascript;
                  if(!isset($this->videoData[$stepIndex])){
                     $this->videoData[$stepIndex] = new stdClass();
                  }
                  $this->videoData[$stepIndex]->$device = $element;

                  $videoType = isset($element->videoType) ? $element->videoType : 'youtube';
                  if($this->hasVideo === false){  $this->hasVideo = true; }
                  if($this->hasYoutube === false && $videoType === 'youtube'){  $this->hasYoutube = true; }
                  if($this->hasVimeo === false && $videoType === 'vimeo'){ $this->hasVimeo = true; }
               }
               break;
            case 'dynamic':
               if (class_exists('BravePop_Element_Dynamic')) {
                  $elmCalss = new BravePop_Element_Dynamic($element, $this->popupID, $stepIndex, $index, $device);
                  $elements .= $elmCalss->render();
                  $elmStyle = $elmCalss->render_css();
                  $elmJavascript = $elmCalss->render_js();
                  $this->elementStyles[$element->id] = $elmStyle;
                  $this->elementScripts[$element->id] = $elmJavascript;
                  $this->popup_get_element_fonts($element);
               }
               break;
            case 'countdown':
               if (class_exists('BravePop_Element_Countdown')) {
                  $elmCalss = new BravePop_Element_Countdown($element, $this->popupID, $stepIndex, $index, $device);
                  $elements .= $elmCalss->render();
                  $elmStyle = $elmCalss->render_css();
                  $elmJavascript = $elmCalss->render_js();
                  $this->timers[] = $elmCalss->generate_timer_stats();
                  $this->elementStyles[$element->id] = $elmStyle;
                  $this->elementScripts[$element->id] = $elmJavascript;
                  $this->popup_get_element_fonts($element);
               }
               break;
            case 'form':
               $elmCalss = new BravePop_Element_Form($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ));
               $elements .= $elmCalss->render();
               $elmStyle = $elmCalss->render_css();
               $elmJavascript = $elmCalss->render_js();
               $this->elementStyles[$element->id] = $elmStyle;
               $this->elementScripts[$element->id] = $elmJavascript;
               $this->popup_get_element_fonts($element);
               break;
            case 'search':
               if (class_exists('BravePop_Element_Search')) {
                  $elmCalss = new BravePop_Element_Search($element, $this->popupID, $stepIndex, $index, $device);
                  $elements .= $elmCalss->render();
                  $elmStyle = $elmCalss->render_css();
                  $this->elementStyles[$element->id] = $elmStyle;
                  $this->popup_get_element_fonts($element);
               }
               break;
            case 'single':
               $elmCalss = new BravePop_Element_Single($element, $this->popupID, $stepIndex, $index, $device);
               $elements .= $elmCalss->render();
               $elmStyle = $elmCalss->render_css();
               $this->elementStyles[$element->id] = $elmStyle;
               $this->popup_get_element_fonts($element);
               break;
            case 'product':
               if (class_exists('BravePop_Element_Product') && BRAVEPOP_WOO_ACTIVE ) {
                  $elmCalss = new BravePop_Element_Product($element, $this->popupID, $stepIndex, $index, $device);
                  $elements .= $elmCalss->render();
                  $elmStyle = $elmCalss->render_css();
                  $this->elementStyles[$element->id] = $elmStyle;
                  $this->popup_get_element_fonts($element);
               }
               break;
            case 'posts':
               if (class_exists('BravePop_Element_Posts')) {
                  $elmCalss = new BravePop_Element_Posts($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ));
                  $elements .= $elmCalss->render();
                  $elmStyle = $elmCalss->render_css();
                  $elmJavascript = $elmCalss->render_js();
                  $this->elementScripts[$element->id] = $elmJavascript;
                  $this->elementStyles[$element->id] = $elmStyle;
                  $this->popup_get_element_fonts($element);
                  $this->hasWpPosts = true;
               }
               break;
            case 'products':
               if ( BRAVEPOP_WOO_ACTIVE ) {
                  $elmCalss = new BravePop_Element_Products($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ));
                  $elements .= $elmCalss->render();
                  $elmStyle = $elmCalss->render_css();
                  $elmJavascript = $elmCalss->render_js();
                  $this->elementScripts[$element->id] = $elmJavascript;
                  $this->elementStyles[$element->id] = $elmStyle;
                  $this->popup_get_element_fonts($element);
                  $this->hasWpProducts = true;
               }
               break;
            case 'login':
               if (class_exists('BravePop_Element_Login')) {
                  $elmCalss = new BravePop_Element_Login($element, $this->popupID, $stepIndex, $index, $device);
                  $elements .= $elmCalss->render();
                  $elmStyle = $elmCalss->render_css();
                  $this->elementStyles[$element->id] = $elmStyle;
                  $this->popup_get_element_fonts($element);
                  $this->hasLoginElement = true;
               }
               break;
            case 'code':
               $elmCalss = new BravePop_Element_Code($element, $this->popupID, $stepIndex, $index, $device, in_array($element->id, $goalElementIDs ));
               $elements .= $elmCalss->render();
               $elmStyle = $elmCalss->render_css();
               $this->elementStyles[$element->id] = $elmStyle;
               break;
            default:
              // $elmCalss = '';
               break;
         }
         return $elements;
      }

      public function popup_renderElements($stepSettings, $stepIndex, $device, $ajaxRender, $currentURL) { 
         if(!$stepSettings){ return ''; }

         $goalElementIDs = isset($this->popupData->settings->goalAction->elementIDs->$device) ?  explode(",",$this->popupData->settings->goalAction->elementIDs->$device)  : array();
         $centerElements = !empty($stepSettings->forceCenter) && $stepSettings->layout === 'sidebar' ? 'brave_popup__elements_wrap--vertical_center' : '';
         $centerElements = !empty($stepSettings->forceCenter) && $stepSettings->layout === 'float' ? 'brave_popup__elements_wrap--horizontal_center' : $centerElements;

         $elements = '<div class="brave_popup__step__content">';
            $elements .= '<div class="brave_popup__step__elements"><div class="brave_popup__elements_wrap '.$centerElements.'">';
               if(isset($stepSettings->content)){
                  foreach ($stepSettings->content as $index => $element) {
                     //Render Non-grouped Elements
                     if(isset($element->type) && $element->type !== 'group' && empty($element->group) && (!isset($element->hidden) || $element->hidden === false)){
                        $elements .= $this->popup_render_element($element, $stepIndex, $index, $device, $goalElementIDs, $ajaxRender , $currentURL );
                     }

                     //Render grouped Elements
                     if(isset($element->type) && $element->type === 'group' && (!isset($element->hidden) || $element->hidden === false)){
                        if (class_exists('BravePop_Element_Group')) {
                           $childrenHTML = '';
                           if(isset($element->elements)){
                              foreach ($element->elements as $chid => $childElmID) {
                                 $childElement = false;
                                 foreach($stepSettings->content as $elm) {
                                    if ($childElmID === $elm->id) {
                                        $childElement = $elm;
                                        break;
                                    }
                                }
                                 if($childElement){
                                    $childrenHTML .= $this->popup_render_element($childElement, $stepIndex, $index, $device, $goalElementIDs, $ajaxRender , $currentURL);
                                 }
                              }
                              $elmCalss = new BravePop_Element_Group($element, $this->popupID, $stepIndex, $index, $childrenHTML);
                              $elements .= $elmCalss->render();
                              $elmStyle = $elmCalss->render_css();
                              $this->elementStyles[$element->id] = $elmStyle;
                           }

                        }
                     }
                  }
               }
            $elements .= '</div></div>';
            $elements .= isset($stepSettings->backgroundImage->image) && $stepSettings->backgroundImage->image ? '<div class="brave_popup__step__content__overlay"></div>' : '';
         $elements .= '</div>';
         return $elements;

      } 


      public function popup_renderPopup($stepSettings, $stepIndex, $device, $ajaxRender, $currentURL) { 
         if(!$stepSettings){ return ''; }
         $closeButtonAbove = ''; $closeButtonBelow = ''; $closeButtonTop = '';
         $closeButtonPosition = isset($stepSettings->closeButtonPosition) ? $stepSettings->closeButtonPosition : 'inside_right';
         if(!isset($this->closeData[$stepIndex])){ $this->closeData[$stepIndex] = new stdClass(); }
         if(!isset($this->closeData[$stepIndex]->$device)){ $this->closeData[$stepIndex]->$device = new stdClass(); }
         if(isset($stepSettings->autoClose) && $stepSettings->autoClose){
            $this->closeData[$stepIndex]->$device->autoClose = true;
            $this->closeData[$stepIndex]->$device->autoCloseDuration = isset($stepSettings->autoCloseDuration) ? $stepSettings->autoCloseDuration : 10;
         }
         if(isset($stepSettings->closeStep) && $stepSettings->closeStep && $stepSettings->closeStep !== 'none'){
            $this->closeData[$stepIndex]->$device->closeStep = $stepSettings->closeStep;
         }
         
         if($closeButtonPosition !== 'below_right' && $closeButtonPosition !== 'below_left' && $closeButtonPosition !== 'below_center' && $closeButtonPosition !== 'top_left' && $closeButtonPosition !== 'top_right'){
            $closeButtonAbove = '<div class="brave_popup__step__close">'.$this->popup_renderCloseButton($stepSettings, $stepIndex, 'above').'</div>';
         }
         if($closeButtonPosition === 'below_right' || $closeButtonPosition === 'below_left' || $closeButtonPosition === 'below_center'){
            $closeButtonBelow = '<div class="brave_popup__step__close">'.$this->popup_renderCloseButton($stepSettings, $stepIndex, 'below').'</div>';
         }
         if($closeButtonPosition === 'top_left' || $closeButtonPosition === 'top_right'){
            $closeButtonTop = '<div class="brave_popup__step__close">'.$this->popup_renderCloseButton($stepSettings, $stepIndex, 'top').'</div>';
         }

         return $closeButtonTop .
                  '<div class="brave_popup__step__inner">
                     <div class="brave_popupSections__wrap">
                        <div class="brave_popupMargin__wrap">
                           '.$closeButtonAbove .'
                           <div class="brave_popup__step__popup">'.$this->popup_renderElements($stepSettings, $stepIndex, $device, $ajaxRender, $currentURL).'</div>
                           '.$closeButtonBelow .'
                        </div>
                     </div>
                  </div>';

      }
      
      public function popup_render_content($ajaxRender=false, $currentURL=''){
         $popupContentHTML = '';
         if($this->popupData->steps){
            foreach ($this->popupData->steps as $key => $step) {
               $exitAnim = '';
               $mobilenoContent = isset($step->mobile->content) && is_array($step->mobile->content) && count($step->mobile->content) === 0 ? true : false ;
               $mobilenoContentClass = $mobilenoContent ? 'brave_popup__step--mobile-noContent' : 'brave_popup__step--mobile-hasContent' ;
               $desktopExitAnim =  isset($step->desktop->animation->exit->preset) && $step->desktop->animation->exit->preset !== 'none' ? true  : false;
               $desktopExitAnimType =  $desktopExitAnim && isset($step->desktop->animation->exit->preset) ? 'data-exitanimtype="'.esc_attr($step->desktop->animation->exit->preset).'"' : '';
               $desktopExitAnimDuration = $exitAnim && $desktopExitAnimType && isset($step->desktop->animation->exit->duration) ? 'data-exitanimlength="'.esc_attr((Int)$step->desktop->animation->exit->duration/1000).'"' : 'data-exitanimlength="'.(0.5).'"';
               $dekstopPopupLayout = isset($step->desktop->layout) ? 'data-layout="'.esc_attr($step->desktop->layout).'"' : 'data-layout="boxed"';
               $dekstopPopupPosition = isset($step->desktop->position) ? 'data-position="'.esc_attr($step->desktop->position).'"' : 'data-position="center"';
               $noMobileContentData = $mobilenoContent ? 'data-nomobilecontent="true"' : 'data-nomobilecontent="false"';
               $scrollockDesktop = !empty($step->desktop->lockScroll) ? true : false; $scrollockMobile = !empty($step->mobile->lockScroll) ? true : false; 

               $mobileExitAnim =  isset($step->mobile->animation->exit->preset) && $step->mobile->animation->exit->preset !== 'none' ? true  : false;
               $mobileExitAnimType =  $mobileExitAnim && isset($step->mobile->animation->exit->preset) ? 'data-exitanimtype="'.$step->mobile->animation->exit->preset.'"' : '';
               $mobileExitAnimDuration = $mobileExitAnim && $mobileExitAnimType && isset($step->mobile->animation->exit->duration) ? 'data-exitanimlength="'.esc_attr((Int)$step->mobile->animation->exit->duration/1000).'"' : 'data-exitanimlength="'.(0.5).'"';
               $mobilePopupLayout = isset($step->mobile->layout) ? 'data-layout="'.esc_attr($step->mobile->layout).'"' : 'data-layout="boxed"';
               $mobilePopupPosition = isset($step->mobile->position) ? 'data-position="'.esc_attr($step->mobile->position).'"' : 'data-position="center"';
               // if(isset($step->desktop->layout) && esc_attr($step->desktop->layout) === 'embedded'){  $this->hasDesktopEmbed = true; }
               // if(isset($step->mobile->layout) && esc_attr($step->mobile->layout) === 'embedded'){  $this->hasMobileEmbed = true; }
               //error_log($this->popupID.$key.$mobilenoContent);
               $popupContentHTML .= '<div id="brave_popup_'.$this->popupID.'__step__'.$key.'" class="brave_popup__step_wrap '.esc_attr($mobilenoContentClass).'">';
               if(isset($step->desktop)){
                  $stepWidth = isset($step->desktop->width) ? absint($step->desktop->width) : 700; $stepHeight = isset($step->desktop->height) ? absint($step->desktop->height) : 500;
                  $dekstopPopupPosition = $dekstopPopupPosition;
                  $popupContentHTML .= '<div class="brave_popup__step brave_popup__step__desktop '.$this->popup_renderCSSClasses($step->desktop).'" '.$noMobileContentData.' data-scrollock="'.esc_attr($scrollockDesktop).'" data-width="'.esc_attr($stepWidth).'" data-height="'.esc_attr($stepHeight).'" data-popopen="false" style="z-index:'.(99999 - $key).'" '.$desktopExitAnimType.' '.$desktopExitAnimDuration.' '.$dekstopPopupLayout.' '.$dekstopPopupPosition.'>'.$this->popup_renderPopup($step->desktop, $key, 'desktop', $ajaxRender, $currentURL).''.$this->popup_renderOverlay($step->desktop, $key).'</div>';
               } 
               if(isset($step->mobile)){
                  $mobileStepWidth = isset($step->mobile->width) ? absint($step->mobile->width) : 320; $mobileStepHeight = isset($step->mobile->height) ? absint($step->mobile->height) : 480;
                  $mobilePopupPosition = $mobilePopupPosition;
                  $popupContentHTML .= '<div class="brave_popup__step brave_popup__step__mobile '.$this->popup_renderCSSClasses($step->mobile).'" data-width="'.esc_attr($mobileStepWidth).'" data-scrollock="'.esc_attr($scrollockMobile).'" data-height="'.esc_attr($mobileStepHeight).'" data-popopen="false" style="z-index:'.(99999 - $key).'" '.$noMobileContentData.' '.$mobileExitAnimType.' '.$mobileExitAnimDuration.' '.$mobilePopupLayout.' '.$mobilePopupPosition.'>'.$this->popup_renderPopup($step->mobile, $key, 'mobile', $ajaxRender, $currentURL).''.$this->popup_renderOverlay($step->mobile, $key).'</div>';
               } 
               $popupContentHTML .= '</div>';
            }
         }

         return $popupContentHTML;
               
      }

      public function popup_render() {  ?>
            <div class="brave_popup <?php $this->popupType === 'content' ?  print_r('brave_popup--embedded') : print_r('brave_popup--popup');?>" id="brave_popup_<?php print_r(esc_attr($this->popupID)); ?>" data-loaded="false" >
               <!-- <p><?php //print_r($this->popupData); ?></p> -->
               <?php if($this->ajaxLoad){ 
                     $this->popup_render_content(); //Dont Output the HTML, just run the function.
                  }else{ 
                     echo $this->popup_render_content(); 
                  } ?>
            </div>
         <?php
      }


      public function calculate_popup_innerSize($step, $stepIndex, $device){
         $innerStyles = array();
         if($step && $this->popupType === 'popup'){
            $heightsteps = array('desktop'=>0, 'mobile'=>0); $heightPadding = array('desktop'=>0, 'mobile'=>0);
            $widthsteps = array('desktop'=>0, 'mobile'=>0);  $widthPadding = array('desktop'=>0, 'mobile'=>0);
            //foreach ($this->popupData->steps as $stepIndex => $step) {
               $devices = array('desktop', 'mobile');
               // foreach ($devices as $i => $device) {
                  if(!empty($step->forceCenter)  && is_array($step->content) && count($step->content)){
                     foreach ($step->content as $elmIndex => $element) {
                        $elmTotalHeight = (isset($element->height) ? $element->height : 0) +  (isset($element->top) ? $element->top : 0);
                        $elmTotalWidth = (isset($element->width) ? $element->width : 0) +  (isset($element->left) ? $element->left : 0);
                        if($heightsteps[$device] < $elmTotalHeight ){  $heightsteps[$device] = $elmTotalHeight;  }
                        if($widthsteps[$device] < $elmTotalWidth ){   $widthsteps[$device] = $elmTotalWidth;  }
                        if($elmIndex === 0){
                           if(isset($element->top)){ $heightPadding[$device] = $element->top; }
                           if(isset($element->left)){ $widthPadding[$device] = $element->left; }
                        }else{
                           if(isset($element->top) && $heightPadding[$device] > $element->top ){   $heightPadding[$device] = $element->top;  }
                           if(isset($element->left) && $widthPadding[$device] > $element->left ){   $widthPadding[$device] = $element->left;  }
                        }
                     }
                     if($step->layout === 'sidebar'){
                        $innerStyles[] = '#brave_popup_'.$this->popupID.'__step__'.$stepIndex.' .brave_popup__step__'.$device.' .brave_popup__elements_wrap--vertical_center{ height: '.$heightsteps[$device].'px; padding-top:'.$heightPadding[$device].'px; }';
                     }
                     if($step->layout === 'float'){
                        $innerStyles[] = '#brave_popup_'.$this->popupID.'__step__'.$stepIndex.' .brave_popup__step__'.$device.' .brave_popup__elements_wrap--horizontal_center{ width: '.$widthsteps[$device].'px; padding-left:'.$widthPadding[$device].'px; }';
                     }
                  }
               // }
            //}
         }
         return $innerStyles;
      }
      
      public function popup_generate_styles($stepSettings, $index, $device){
         if(!$stepSettings || !$device){ return ''; }
         //Popup
         $popupLayout = isset($stepSettings->layout) ? $stepSettings->layout : 'boxed';
         $popupWidth = isset($stepSettings->width) ? $stepSettings->width : 700;
         $popupHeight = isset($stepSettings->height) ? $stepSettings->height : 450;
         $fullWidth = isset($stepSettings->fullWidth) ? $stepSettings->fullWidth :false;
         $fullHeight = isset($stepSettings->fullHeight) ? $stepSettings->fullHeight :false;
         $popupPosition = isset($stepSettings->position) ? $stepSettings->position : 'center';
         $popupFont = isset($stepSettings->fontFamily) && $stepSettings->fontFamily !== 'None' ? 'font-family:'.$stepSettings->fontFamily.';' : 'font-family:Arial;';
         $fontBold = !empty($stepSettings->fontVariation) && $stepSettings->fontVariation !== 'regular' ?  'font-weight: '.str_replace('italic','', $stepSettings->fontVariation).';' : '';
         $fontItalic = ( (isset($stepSettings->fontVariation) && strpos($stepSettings->fontVariation, 'italic') !== false)) ? 'font-style: italic;' : '';
         //if($this->popupType === 'landing'){$popupPosition = 'top_center';}
         $notsticky = !empty($stepSettings->notsticky) ? $stepSettings->notsticky : false;

         $this->popup_get_element_fonts($stepSettings);


         $width =  $popupWidth.'px'; if($fullWidth === true || ($popupLayout==='float')){ $width = '100%'; }
         $height =  $popupHeight.'px'; if($fullHeight === true || ($popupLayout==='sidebar')){ $height = '100%'; }
         $shadowStyle = ''; $borderRadius = ''; $borderStyle = '';
         if(isset($stepSettings->shadow) && $stepSettings->shadow === true){
            $shadowSize = isset($stepSettings->shadowSize) ? $stepSettings->shadowSize.'px' : '15px';  
            $shadowColorOpacity = isset($stepSettings->shadowColor) && isset($stepSettings->shadowColor->opacity) ? $stepSettings->shadowColor->opacity : 0.15;
            $shadowColor = isset($stepSettings->shadowColor) && isset($stepSettings->shadowColor->rgb) ? 'rgba('.$stepSettings->shadowColor->rgb.', '.$shadowColorOpacity.')' : 'rgba(0, 0, 0, 0.2)';
            $shadowStyle = 'box-shadow: 0 0 '.$shadowSize.' '.$shadowColor.';';
         }
         if(isset($stepSettings->borderRadius) && $stepSettings->borderRadius > 0){
            $borderRadius = 'border-radius: '.$stepSettings->borderRadius.'px;';
         }
         if(isset($stepSettings->borderSize) && $stepSettings->borderSize > 0){
            $borderSize = $stepSettings->borderSize; 
            $borderColorOpacity = isset($stepSettings->borderColor) && isset($stepSettings->borderColor->opacity) ? $stepSettings->borderColor->opacity : 1;
            $borderColor = isset($stepSettings->borderColor) && isset($stepSettings->borderColor->rgb) ? 'rgba('.$stepSettings->borderColor->rgb.', '.$borderColorOpacity.')' : 'rgba(0, 0, 0, 1)';
            $borderStyle = 'border: '.$borderSize.'px solid '.$borderColor.' ;';
         }

         //Popup Background
         $backgroundColorOpacity = isset($stepSettings->backgroundColor) && isset($stepSettings->backgroundColor->opacity) ? $stepSettings->backgroundColor->opacity : 1;
         $backgroundColor = isset($stepSettings->backgroundColor) && isset($stepSettings->backgroundColor->rgb) ? 'background-color: rgba('.$stepSettings->backgroundColor->rgb.', '.$backgroundColorOpacity.');' : 'background-color: rgba(255,255,255, 1);';
         $backgroundImage = '';$backgroundSize = '';$backgroundPositionX ='';$backgroundPositionY = ''; $backgroundOverlay ='';
         if(isset($stepSettings->backgroundImage) && isset($stepSettings->backgroundImage->image)){
            $backgroundImage = 'background-image: url('.$stepSettings->backgroundImage->image.');';
            $backgroundImageAutoFit = isset($stepSettings->backgroundImage->autoFit) && $stepSettings->backgroundImage->autoFit=== false ? false : true;
            $backgroundSize = isset($stepSettings->backgroundImage->size) && $backgroundImageAutoFit === false ? 'background-size: '.$stepSettings->backgroundImage->size.'%;' : 'background-size: cover;';
            $backgroundPositionX = isset($stepSettings->backgroundImage->posX) ? 'background-position-x: '.$stepSettings->backgroundImage->posX.'%;' : '';
            $backgroundPositionY = isset($stepSettings->backgroundImage->posY) ? 'background-position-y: '.$stepSettings->backgroundImage->posY.'%;' : '';
            $backgroundOverlay = bravepop_generate_style_props(isset($stepSettings->backgroundImageOverlay) ? $stepSettings->backgroundImageOverlay : '', 'background-color', '0,0,0', '0');

         }

         
         //Overlay
         $overlaybgColor = ''; 
         $overlaybgColorRGB = isset($stepSettings->overlayColor->rgb) ? $stepSettings->overlayColor->rgb :'0,0,0';
         $overlaybgColorOpacity = isset($stepSettings->overlayColor) && isset($stepSettings->overlayColor->opacity) ? $stepSettings->overlayColor->opacity :'0.7';
         $overlayBlur =  isset($stepSettings->overlayImage) && !empty($stepSettings->overlayImage->blur) ? 'filter: blur('.$stepSettings->overlayImage->blur.'px); transform: scale(1.1); ' : '';
         $overlayOpacity = isset($stepSettings->overlayImage) && isset($stepSettings->overlayImage->opacity) ? 'opacity: '.$stepSettings->overlayImage->opacity.';'  : '';

         //Close Button
         $closeButton = isset($stepSettings->closeButton)  ? $stepSettings->closeButton : 'icon';
         $closeButtonSize = isset($stepSettings->closeButton) && $stepSettings->closeButton === 'text' ? 13 : 24;
         $closeButtonPosition = isset($stepSettings->closeButtonPosition) ? $stepSettings->closeButtonPosition : 'inside_right';
         $closePositionVal = 'auto';
         $closeColorRGB = isset($stepSettings->closeColor) && isset($stepSettings->closeColor->rgb) ? $stepSettings->closeColor->rgb :'0,0,0';
         $closeColorOpacity = isset($stepSettings->closeColor) && isset($stepSettings->closeColor->opacity) ? $stepSettings->closeColor->opacity :'1';
         $closeColor = isset($stepSettings->closeColor) && isset($stepSettings->closeColor->rgb) ? 'rgba('.$stepSettings->closeColor->rgb.', '.$closeColorOpacity.')' : 'rgba(0,0,0, 1)';
         $closeButtonSize = isset($stepSettings->closeButtonSize) ? $stepSettings->closeButtonSize : $closeButtonSize;
         $closePos = ($closeButtonPosition === 'below_right' || $closeButtonPosition === 'below_left' || $closeButtonPosition === 'below_center') ? 'below' : 'above';
         
         if($closePos === 'below'){
            $closePositionVal = ($closeButtonSize + ($closeButton !== 'text' ? ($closeButtonSize/3) :  $closeButtonSize + 10));
            $closePositionStyle = 'bottom:-'.$closePositionVal.'px';
         }
         if($closePos === 'above'){
            $closePositionVal = ($closeButtonSize + ($closeButton !== 'text' ? ($closeButtonSize/3) :  $closeButtonSize) );
            $closePositionStyle = 'top:-'.$closePositionVal.'px';
         }

         //POPUP Margin
         $margin ='';
         if(isset($stepSettings->margin)){
            $marginTop = isset($stepSettings->margin->top) ? 'top:'.$stepSettings->margin->top.'px; ' : '';
            $marginLeft = isset($stepSettings->margin->left) ? 'left:'.$stepSettings->margin->left.'px; ' : '';
            $margin = $marginTop.$marginLeft;
            // if($this->popupType === 'landing'){
            //    $margin = !empty($stepSettings->margin->top) ? 'margin:'.$stepSettings->margin->top.'px 0; ' : '';
            // }
         }
  
         //POPUP Position
         $popPositionStyle = '';
         if((isset($stepSettings->fullHeight) && $stepSettings->fullHeight === false) || !isset($stepSettings->fullHeight)){
            if($popupPosition === 'center' || $popupPosition === 'center_left' || $popupPosition === 'center_right' ){
               $marginTopVal = round($popupHeight / 2);
               $popPositionStyle = 'margin-top:-'.$marginTopVal.'px;';
            }
         }

         //Don't Stick to Top
         $noStickyStyle = '';$noStickyPopupStyle = '';
         if($popupPosition === 'top_center' && $popupLayout === 'float' && $notsticky ){
            $noStickyStyle = 'position:absolute; z-index:99999;';
            $noStickyPopupStyle = 'div#brave_popup_'.$this->popupID.'{position:unset;}';
         }

         //Final Style
         $popupStyle = '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__step__inner{ width: '.$width.';  height: '.$height.';'.$borderRadius . $popPositionStyle . $popupFont . $fontBold . $fontItalic.$noStickyStyle.'}';
         $popupStyle = $popupFont ? $popupStyle.'#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_element__wrap{ '. $popupFont . $fontBold . $fontItalic.'}': $popupStyle;
         $popupMarginStyle = $margin && $this->popupType === 'popup' ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popupMargin__wrap{ '. $margin.'}':'';

         $popupInnerStyle = ($shadowStyle || $borderRadius || $borderStyle) ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__step__popup{ '.$shadowStyle . $borderRadius. $borderStyle.' }':'';
         $popupBackground = $backgroundColor || $backgroundImage ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__step__content{ '.$backgroundColor . $backgroundImage . $backgroundSize . $backgroundPositionX . $backgroundPositionY.' }': '';
         $popupBackgroundOverlay = $backgroundOverlay ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__step__content__overlay{ '.$backgroundOverlay.' }': '';

         $overlayStyle = $overlaybgColorRGB ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__step__overlay{ background-color: rgba('.$overlaybgColorRGB.', '.$overlaybgColorOpacity.');}' : '';
         $overlayImageStyle = isset($stepSettings->overlayImage) ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__step__overlay__img{ '.$overlayBlur . $overlayOpacity.' }' : '';
         $closeStyle = '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__close{ font-size:'.$closeButtonSize.'px'.'; '.($closeButton !== 'text' ? 'width:'.$closeButtonSize.'px'.';' :'').' color:'.$closeColor.';'.$closePositionStyle.'}
                        #brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__close svg{ width:'.$closeButtonSize.'px'.'; height:'.$closeButtonSize.'px;}
                        #brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popup__close svg path{ fill:'.$closeColor.';}';

         //Animation 
         $contAnim = isset($stepSettings->animation->continious->enable) ? $stepSettings->animation->continious->enable  : false;
         $contAnimType = isset($stepSettings->animation->continious->preset) ? $stepSettings->animation->continious->preset  : 'none';
         $contAnimDuration = isset($stepSettings->animation->continious->duration) ? (Int)$stepSettings->animation->continious->duration / 1000  : 0.5;
         $contAnimDelay = isset($stepSettings->animation->continious->delay) ? (Int)$stepSettings->animation->continious->delay / 1000 : 0;
         $contAnimInfinite = $contAnimDelay === 0 ? 'infinite':'';

         
         //Open Animation
         $openAnim =  isset($stepSettings->animation->load->preset) && $stepSettings->animation->load->preset !== 'none' ? true  : false;
         $openAnimType =  isset($stepSettings->animation->load->preset) ? $stepSettings->animation->load->preset : false;
         $openAnimDuration =  isset($stepSettings->animation->load->duration) ? (Int)$stepSettings->animation->load->duration/1000 : 0.5;

         //Close Animation
         $exiAnim =  isset($stepSettings->animation->exit->preset) && $stepSettings->animation->exit->preset !== 'none' ? true  : false;
         $exitAnimType =  isset($stepSettings->animation->exit->preset) ? $stepSettings->animation->exit->preset : false;
         $exitAnimDuration =  isset($stepSettings->animation->exit->duration) ? (Int)$stepSettings->animation->exit->duration/1000 : 0.5;

         $popupAnimation = $contAnim && $contAnimType !== 'none' ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popupSections__wrap.brave_element-'.$this->popupID.'_contAnim{ animation: brave'.$contAnimType.' '.$contAnimDuration.'s '.$contAnimInfinite.'; animation-timing-function: linear;  }' : '';
         $popupAnimationHover = $contAnim && $contAnimType !== 'none' ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popupSections__wrap.brave_element-'.$this->popupID.'_contAnim:hover{ animation: none;  }' : '';

         $popupExitAnimation = $exiAnim ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popupSections__wrap.brave_element-'.$this->popupID.'_'.$index.'_exitAnim{ animation: brave'.$exitAnimType.' '.$exitAnimDuration.'s linear; animation-timing-function: linear; animation-direction: reverse; opacity:0; }' : '';

         $popupOpenAnimation = $openAnim ? '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.' .brave_popupSections__wrap.brave_element-'.$this->popupID.'_'.$index.'_openAnim{ animation: brave'.$openAnimType.' '.$openAnimDuration.'s linear; animation-timing-function: linear; }' : '';

         //ELEMENT STYLES
         $elementStyles = $this->popup_generate_element_styles($stepSettings, $index, $device);
         $bodyStyle = '';
         //If the popup is fullwidth and set to top (Hellobar), add top body margin to push the body down.
         
         if($popupPosition === 'top_center' && $popupLayout ==='float'){
            if(!isset($stepSettings->fullHeight) || (isset($stepSettings->fullHeight) && $stepSettings->fullHeight === false)){
               //$bodyStyle = 'html{margin-top:'.$popupHeight.'px!important;}';
               //.brave_popup__step__elements { width: 1024px; height: '.$popupHeight.'px; margin: 0 auto;}
               //.brave_popup .brave_element {  position: relative; float: left;}
               //.brave_popup__step__elements { position: relative;  z-index: 1; width: auto; height: 100%; float: left; left: 50%; right: 0; transform: translate(-50%, 0); }
            }
         }
         if($this->popupType === 'content' && !empty($stepSettings->overlay) && !empty($stepSettings->margin->top)){
            $contentPadding = !empty($stepSettings->margin->top) ? 'padding: '.$stepSettings->margin->top.'px 0' : '';
            $bodyStyle = '#brave_popup_'.$this->popupID.'__step__'.$index.' .brave_popup__step__'.$device.'{'.$contentPadding.'}';
         }

         $calculatedInerSize = $this->calculate_popup_innerSize($stepSettings, $index, $device);
         $calculatedInerStyles = count($calculatedInerSize) > 0 ? implode(' ', $calculatedInerSize) : '';
         // error_log($calculatedInerStyles);
         //error_log($this->popupID.'__step__'.$index.$popupPosition.$popupLayout.$popupHeight);
         //error_log(wp_json_encode($stepSettings));
         return $bodyStyle . $popupStyle .$popupMarginStyle . $popupInnerStyle . $popupBackground . $popupBackgroundOverlay . $overlayStyle . $overlayImageStyle . $closeStyle. $popupAnimation. $popupAnimationHover. $popupExitAnimation. $popupOpenAnimation. $elementStyles. $calculatedInerStyles.$noStickyPopupStyle;

      }

      public function popup_generate_element_styles($stepSettings, $stepIndex, $device){
         $elementStyles = '';
         if(isset($stepSettings->content)){
            $groupData = array();
            foreach ($stepSettings->content as $index => $element) {
               if($element->type === 'group'){
                  $elmID = $element->id; $groupData[$elmID] = $element;
               }
            }
            foreach ($stepSettings->content as $index => $element) {
               $width = isset($element->width) ?  'width: '.$element->width.'px;' : '';
               $height = isset($element->height) ?  'height: '.$element->height.'px;' : '';
               $top = isset($element->top) ?  'top: '.$element->top.'px;' : '';
               $left = isset($element->left) ?  'left: '.$element->left.'px;' : '';
               $zIndex = isset($element->left) ?  'z-index: '.$index.';' : '';
               $rotate = isset($element->rotate) ?  'transform: rotate('.$element->rotate.'deg);' : '';
               $breakFree = isset($element->breakFree) && $element->breakFree == true ?  'position: fixed;' : '';
               $contAnim = isset($element->animation->continious->enable) ? $element->animation->continious->enable  : false;
               $contAnimType = isset($element->animation->continious->preset) ? $element->animation->continious->preset  : 'none';
               $contAnimDuration = isset($element->animation->continious->duration) ? (Int)$element->animation->continious->duration / 1000  : 0.5;
               $contAnimDelay = isset($element->animation->continious->delay) ? (Int)$element->animation->continious->delay / 1000 : 0;
               //If element is inside group
               // if(isset($element->group) && isset($groupData[$element->group])){
               //    if($groupData[$element->group]->top){
               //       $top = isset($element->top) ?  'top: '.($element->top + $groupData[$element->group]->top).'px;' : '';
               //    }
               //    if($groupData[$element->group]->left){
               //       $left = isset($element->left) ?  'left: '.($element->left + $groupData[$element->group]->left).'px;' : '';
               //    }
               // }

               //Open Animation
               $openAnim =  isset($element->animation->load->preset) && $element->animation->load->preset !== 'none' ? true  : false;
               $openAnimType =  isset($element->animation->load->preset) ? $element->animation->load->preset : false;
               $openAnimDuration =  isset($element->animation->load->duration) ? (Int)$element->animation->load->duration/1000 : 0.5;
               $openAnimDelay = isset($element->animation->load->delay) ? (Int)$element->animation->load->delay / 1000 : 0;
               $openAnimDelayStyle = $openAnimDelay ? 'animation-delay:'.$openAnimDelay.'s;' : '';

               $contAnimInfinite = $contAnimDelay === 0 ? 'infinite':'';

               $elementStyle = '#brave_popup_'.$this->popupID.'__step__'.$stepIndex.' #brave_element-'.$element->id.'{ '.$width .  $height .  $top .  $left . $zIndex . $breakFree .'}';
               $elementRotateStyle = $rotate?'#brave_popup_'.$this->popupID.'__step__'.$stepIndex.' #brave_element-'.$element->id.' .brave_element__styler{ '.$rotate.'}':'';
      
               $elementContAnimation = $contAnim && $contAnimType !== 'none' ? '#brave_popup_'.$this->popupID.'__step__'.$stepIndex.' #brave_element-'.$element->id.'.brave_element-'.$element->id.'_contAnim{ animation: brave'.$contAnimType.' '.$contAnimDuration.'s '.$contAnimInfinite.'; animation-timing-function: linear;  }' : '';
               $elementAnimationHover = $contAnim && $contAnimType !== 'none' ? '#brave_popup_'.$this->popupID.'__step__'.$stepIndex.' #brave_element-'.$element->id.'.brave_element-'.$element->id.'_contAnim:hover{ animation: none;  }' : '';

               $elementOpenAnimation = $openAnim ? '#brave_popup_'.$this->popupID.'__step__'.$stepIndex.' #brave_element-'.$element->id.'.brave_element-'.$element->id.'_'.$stepIndex.'_openAnim{ animation: brave'.$openAnimType.' '.$openAnimDuration.'s linear; animation-timing-function: linear; '.$openAnimDelayStyle.' }' : '';

               $elementStyles .= $elementStyle.$elementRotateStyle.$elementContAnimation.$elementAnimationHover.$elementOpenAnimation;
            }
         }

         return $elementStyles;

      }


   }

}

?>