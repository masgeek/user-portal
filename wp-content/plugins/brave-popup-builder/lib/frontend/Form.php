<?php

if ( ! class_exists( 'BravePop_Element_Form' ) ) {
   

   class BravePop_Element_Form {

      protected $data;
      protected $popupID;
      protected $stepIndex;
      protected $elementIndex;
      protected $device;
      protected $nolabel;
      protected $ratingStyles = '';
      protected $hasDate = false;
      protected $totalSteps = 0;
      protected $changesFormHeight = false;
      protected $recaptcha = false;
      protected $formHeightData;
      protected $wrappedSteps = 1;
      protected $goalItem;
      protected $buttonGroupStyles = '';
      protected $consentField;
      protected $currentUser;
      protected $formDataIntercepted;
      protected $formData;
      protected $conditionedFields;
      protected $formFields;
      protected $social_optin;
      protected $takeConsent;
      protected $disabelStar;
      protected $social_settings;

      function __construct($data=null, $popupID=null, $stepIndex=0, $elementIndex=0, $device='desktop', $goalItem=false) {
         $this->data = $data;
         $this->popupID = $popupID;
         $this->stepIndex =  $stepIndex;
         $this->elementIndex = $elementIndex;
         $this->device = $device;
         $this->nolabel = $this->has_noLabel();
         $this->formHeightData = array(isset($data->height) ? $data->height : '');
         $this->goalItem = $goalItem;
         $this->currentUser = bravepop_getCurrentUser();
         $this->formDataIntercepted = do_action( 'bravepop_form_element_data', $this->data->formData, $popupID, $this->data->id );
         $this->formData = $this->formDataIntercepted ? $this->formDataIntercepted : $this->data->formData;
         // error_log(wp_json_encode($this->formData));
         $this->conditionedFields = array();
         $this->formFields = $this->processFields($this->data->formData);
         $this->social_optin = !empty($this->formData->settings->action->newsletter->advancedSettings->social) ? true : false;
         $this->takeConsent = !empty($this->formData->settings->action->newsletter) && !empty($this->formData->settings->action->newsletter->consent) && !empty($this->formData->settings->action->newsletter->consentField) && !empty($this->formData->settings->action->newsletter->consentField) ? $this->formData->settings->action->newsletter->consentField : '';
         $this->disabelStar = isset($this->formData->settings->style->disableStar) && $this->formData->settings->style->disableStar === true ? true : false ;
         $this->social_settings = !empty($this->formData->settings->action->newsletter->advancedSettings->social_settings) ? $this->formData->settings->action->newsletter->advancedSettings->social_settings : false;
         if($this->social_optin && function_exists('bravepop_default_social_buttons') && !$this->social_settings){
            $this->social_settings = bravepop_default_social_buttons('optin');
         }
         //Check if Fields has date or steps
         $this->has_dateOrSteps();
         if($this->totalSteps > 0){ $this->totalSteps = $this->totalSteps+1;}
         //error_log('Total Steps: '.$this->totalSteps);

         if($this->hasDate && class_exists( 'BravePop_Geolocation' ) ){
            add_action( 'wp_footer', array( $this, 'enqueue_date_js' ), 10 );
         }
         if(!empty($this->formData->settings->action->recaptcha) && class_exists( 'BravePop_Geolocation' ) ){
            $currentSettings = get_option('_bravepopup_settings');
            $currentIntegrations = $currentSettings && isset($currentSettings['integrations']) ? $currentSettings['integrations'] : array() ;
            $reCAPTCHA_site_key = isset($currentIntegrations['recaptcha']->api)  ? $currentIntegrations['recaptcha']->api  : '';
            $this->recaptcha = $reCAPTCHA_site_key;
            add_action( 'wp_footer', array( $this, 'enqueue_recaptcha_js' ), 10 );
         }
         if($this->social_optin && $this->social_settings && class_exists( 'BravePop_Geolocation' ) ) {
            add_action( 'wp_footer', array( $this, 'enqueue_social_optin_js' ), 10 );
         }
      }

      public function has_dateOrSteps() { 
         if(isset($this->formFields)){
            foreach ($this->formFields as $index => $field) {
               if(isset($field->type) && $field->type === 'step' && ($index !== 0 && $index !== count($this->formFields) - 1)){ 
                  $this->totalSteps = $this->totalSteps+1; 
               }
               if(isset($field->type) && $field->type === 'date'){ $this->hasDate = true; }
               if($this->takeConsent && $this->takeConsent === $field->id){ $this->consentField =  $field; }
            }
         }
      }

      public function has_noLabel() { 
         $noLabel = false;
         if(isset($this->formFields)){
            foreach ($this->formFields as $index => $field) {
               if((isset($field->label) && !$field->label) || !isset($field->label)){ $noLabel = true; }
            }
         }
         return $noLabel;
      }

      public function enqueue_date_js( $hook ) {
         wp_enqueue_script( 'brave_pikaday_js', BRAVEPOP_PLUGIN_PATH . 'assets/frontend/pikaday.min.js' ,array(),'1.8.2',true);
         wp_enqueue_script( 'brave_pikaday_init', BRAVEPOP_PLUGIN_PATH . 'assets/frontend/formdate.js' ,array(),'1.1.0',true);
      }

      public function enqueue_recaptcha_js( $hook ) {
         if($this->recaptcha){
            wp_enqueue_script( 'brave_recaptcha_js', 'https://www.google.com/recaptcha/api.js?render='.$this->recaptcha ,'','',true);
         }
      }

      public function enqueue_social_optin_js( ) {
         $socialEnabled = array();
         $currentSettings = get_option('_bravepopup_settings');
         $integrations = $currentSettings && isset($currentSettings['integrations']) ? $currentSettings['integrations'] : array() ;
         $fbAppID = isset($integrations['facebook']) && isset($integrations['facebook']->api) ? $integrations['facebook']->api : ''; 
         $googleClientID = isset($integrations['google']) && isset($integrations['google']->api) ? $integrations['google']->api : ''; 
         $linkedInClientID = isset($integrations['linkedin']) && isset($integrations['linkedin']->api) ? $integrations['linkedin']->api : ''; 

         foreach ($this->social_settings as $key => $item) {  if(!empty($item->enabled)){ $socialEnabled[$item->type] = true;}  }

         if ( !is_admin() ) {
            $vars = array('errors'=>array());

            if($socialEnabled['facebook'] && $fbAppID){
               $vars['facebook_app_id'] = $fbAppID;
               $vars['errors']['facebook'] = __('Sorry, Could not Connect to your Facebook Account.','bravepop');
               wp_enqueue_script('bravepop_facebook_login_js', 'https://connect.facebook.net/en_US/sdk.js#version=v9.0&appId='.$fbAppID.'&cookie=true&xfbml=true');
            }
            if($socialEnabled['google'] && $googleClientID){
               $vars['google_client_id'] = $googleClientID;
               $vars['errors']['google'] = __('Sorry, Could not Connect to your Google Account.','bravepop');
               wp_enqueue_script('bravepop_google_login_js', 'https://accounts.google.com/gsi/client');
            }
            if($socialEnabled['linkedin'] && $linkedInClientID){
               $vars['linkedin_client_id'] = $linkedInClientID;
               $vars['security'] = wp_create_nonce('brave-linkedin-nonce');
               $vars['ajaxURL'] = esc_url(admin_url( 'admin-ajax.php' ));
               $vars['errors']['linkedin'] = __('Sorry, Could not Connect to your LinkedIn Account.','bravepop');
               $vars['linkedin_rediret_url'] = urlencode(esc_url( home_url( '/' ) ).'?brave_linkedin_auth');
            }
            
            wp_register_script( 'bravepop_social_login_js', BRAVEPOP_PLUGIN_PATH . 'assets/frontend/social_login.js' ,array(),'1.1.0',true);
            wp_localize_script( 'bravepop_social_login_js', 'brave_social_global', $vars );
            wp_enqueue_script('bravepop_social_login_js');
         }
      }

      public function render_js() { ?>
         <script>
            document.addEventListener("DOMContentLoaded", function(event) {


               <?php 
               $fieldSettings = new stdClass();
               $theFormFields = isset($this->formFields) ? $this->formFields: null; $fieldJS = '';
               $totalQuizQuestions = 0; $allfieldTypes = new stdClass(); $allConditions = array(); $fieldConditionsMatch = new stdClass(); $fieldConditionVals = new stdClass();
               if($theFormFields){
                  foreach ($theFormFields as $key => $value) { $fieldID = $value->id;  $allfieldTypes->$fieldID = isset($value->type) ? $value->type : '';  }

                  foreach ($theFormFields as $key => $value) {
                           $fieldID = $value->id;
                           $fieldSettings->$fieldID = new stdClass();
                           $fieldSettings->$fieldID->uid = isset($value->uid) ? $value->uid : '';
                           $fieldSettings->$fieldID->type = isset($value->type) ? $value->type : '';
                           $fieldSettings->$fieldID->required = isset($value->required) ? $value->required : false;
                           $fieldSettings->$fieldID->validation = isset($value->validation) ? $value->validation : '';

                           if($fieldSettings->$fieldID->type === 'date' && isset($value->dateType) && $value->dateType === 'dropdown'){
                              $fieldSettings->$fieldID->validation = 'multi';
                           }

                           if(isset($this->formData->settings->options->type) && $this->formData->settings->options->type === 'quiz' && isset($value->options)){
                              $theOptions = array(); $highestOptionPoint = 0;
                              foreach ($value->options as $key => $optField) {
                                 $option = new stdClass();
                                 $option->label = isset($optField->label) ? esc_attr($optField->label): '';
                                 $option->value = isset($optField->value) ? esc_attr($optField->value): '';
                                 $option->score = isset($optField->score) ? intval($optField->score): 0;
                                 $option->correct = !empty($optField->correct) ? true : false;
                                 $theOptions[] = $option;
                                 if(isset($optField->score) && $optField->score > $highestOptionPoint){
                                    $highestOptionPoint = $optField->score;
                                 }
                              }
                              $fieldSettings->$fieldID->options = $theOptions;
                              $fieldSettings->$fieldID->topScore = $highestOptionPoint;
                              $totalQuizQuestions = $totalQuizQuestions+1;
                           }
                           if(isset($value->save_cookie) && isset($value->cookie_name) && !empty($value->save_cookie) && !empty($value->cookie_name)){
                              $fieldSettings->$fieldID->save_cookie = esc_attr(str_replace(' ','',$value->cookie_name));
                           }
                           if(isset($value->multi)){ $fieldSettings->$fieldID->multi = true; }
                           if(isset($value->defaultType) && !empty(($value->defaultType))){
                              $fieldNode = !empty($value->type) && $value->type === 'textarea' ? 'textarea':'input';
                              if($value->defaultType === 'cookie' && !empty($value->defaultValue)){ 
                                 $fieldJS .= "if(document.querySelector('#brave_form_field{$fieldID} {$fieldNode}')){";
                                    $fieldJS .= "document.querySelector('#brave_form_field{$fieldID} {$fieldNode}').value = localStorage.getItem('{$value->defaultValue}') || '';";
                                 $fieldJS .= "}";
                              }
                              if($value->defaultType === 'pageurl'){ 
                                 $fieldJS .= "if(document.querySelector('#brave_form_field{$fieldID} {$fieldNode}')){";
                                    $fieldJS .= "document.querySelector('#brave_form_field{$fieldID} {$fieldNode}').value = window.location.href || '';";
                                 $fieldJS .= "}";
                              }
                           }
                           if(isset($value->conditions) && is_array($value->conditions)){
                              $conditions = new stdClass();
                              foreach ($value->conditions as $key => $condition) {
                                 if(isset($condition->field) && !empty($condition->field)){
                                    $fieldKey =$condition->field;
                                    $condition->fieldType = isset($allfieldTypes->$fieldKey) ? $allfieldTypes->$fieldKey : '';
                                    $condition->fieldDependent = $fieldID;
                                    if(isset($condition->value)){
                                       $conditionValues = explode(",",$condition->value); $conditionValues_array = array(); foreach ($conditionValues as $key => $value) { $conditionValues_array[] = trim($value);  };
                                       $condition->value = $conditionValues_array;
                                    }
                                    $conditions->$fieldKey = $condition;
                                    $allConditions[] = $condition;
                                    if(!isset($fieldConditionsMatch->$fieldID)){  $fieldConditionsMatch->$fieldID = new stdClass();  }
                                    if(!isset($fieldConditionVals->$fieldID)){  $fieldConditionVals->$fieldID = new stdClass();  }
                                    $fieldConditionVals->$fieldID->$fieldKey = isset($conditionValues_array)? $conditionValues_array : [];
                                    $fieldConditionsMatch->$fieldID->$fieldKey = false;
                                 }
                              }
                              $fieldSettings->$fieldID->conditions = $conditions;
                           }

                  }
               }
               // error_log(wp_json_encode($fieldSettings));
               if($fieldJS){ echo $fieldJS; }
               ?>
               brave_popup_formData['<?php print_r(esc_attr($this->data->id)); ?>'] = {
                  formID: '<?php print_r(esc_attr($this->data->id)); ?>',
                  popupID: '<?php print_r(esc_attr($this->popupID)); ?>',
                  stepID: '<?php print_r(esc_attr($this->stepIndex)); ?>',
                  device: '<?php print_r(esc_attr($this->device)); ?>',
                  fields: '<?php print_r(wp_json_encode($fieldSettings)); ?>',
                  track: '<?php print_r(wp_json_encode(isset($this->formData->settings->action->track) ? $this->formData->settings->action->track : null)); ?>',
                  changesFormHeight: <?php print_r(wp_json_encode($this->changesFormHeight)); ?>,
                  heightData: <?php print_r(wp_json_encode($this->formHeightData)); ?>,
                  goal: <?php print_r(wp_json_encode($this->goalItem)); ?>,
                  recaptcha: <?php print_r(wp_json_encode(!empty($this->recaptcha) ? $this->recaptcha : false )); ?>,
                  social_optin: <?php print_r(wp_json_encode(!empty($this->social_optin) ? $this->social_optin : false )); ?>,
                  totalSteps: <?php print_r($this->totalSteps) ?>,
                  quiz: <?php print_r(wp_json_encode(isset($this->formData->settings->options->type) && $this->formData->settings->options->type === 'quiz' ? true : false)); ?>,
                  quizScoring: <?php print_r(wp_json_encode(isset($this->formData->settings->options->scoring) ? $this->formData->settings->options->scoring : 'points')); ?>,
                  totalQuestions: <?php print_r($totalQuizQuestions); ?>,
                  totalScore: <?php print_r(0); ?>,
                  totalCorrect: <?php print_r(0); ?>,
                  freemailAllow: <?php print_r(wp_json_encode(!empty($this->formData->settings->action->allowfreemail) ? true : false));?>,
                  conditions: <?php print_r(wp_json_encode($allConditions)); ?>,
                  conditionsMatch: <?php print_r(wp_json_encode($fieldConditionsMatch)); ?>,
                  conditionsVals: <?php print_r(wp_json_encode($fieldConditionVals)); ?>,
                  onSubmit: <?php print_r('function(formData, response){  '.((!empty($this->formData->settings->action->onSubmitJS) && !empty($this->formData->settings->action->onSubmitCode)) ? $this->formData->settings->action->onSubmitCode : '').'}'); ?>,
               }
               <?php if($this->hasDate){  
                  //echo '//Load Date init Call';
               }?>

            });
         </script>

      <?php }

      
      public function render_css() { 

         $formStyle = isset($this->formData->settings->style) ? $this->formData->settings->style : null;
         $buttonStyle = isset($this->formData->settings->button) ? $this->formData->settings->button : null;
         $theFormFields = isset($this->formFields) ? $this->formFields : array();
         $visibleFields = array_filter($theFormFields, function($field) {  return $field->type !== 'hidden'; });

         //Form
         $fontSize = bravepop_generate_style_props(isset($formStyle->fontSize) ? $formStyle->fontSize : 12, 'font-size');
         $fontFamily = isset($formStyle->fontFamily) && $formStyle->fontFamily !=='None' ?  'font-family: '.$formStyle->fontFamily.';' : 'font-family: inherit;';
         $fontBold = !empty($formStyle->fontVariation) && $formStyle->fontVariation !== 'regular' ?  'font-weight: '.str_replace('italic','', $formStyle->fontVariation).';' : '';
         $fontItalic = ( (!empty($formStyle->fontVariation) && strpos($formStyle->fontVariation, 'italic') !== false)) ? 'font-style: italic;' : '';
         $fontColor = bravepop_generate_style_props(isset($formStyle->fontColor) ? $formStyle->fontColor : '', 'color', '107, 107, 107', '1');
         
         $successFontSize = bravepop_generate_style_props(isset($formStyle->successFontSize) ? $formStyle->successFontSize : 13, 'font-size');
         $successFontColor = bravepop_generate_style_props(isset($formStyle->successFontColor) ? $formStyle->successFontColor : '', 'color', '107, 107, 107', '1');
         $progressBGColor =  !empty($this->formData->settings->options->progress) ? bravepop_generate_style_props(isset($formStyle->progressColor) ? $formStyle->progressColor : '', 'background-color', '109,120,216', '1'):'';
         $progressColor =  !empty($this->formData->settings->options->progress) ? bravepop_generate_style_props(isset($formStyle->progressColor) ? $formStyle->progressColor : '', 'color', '109,120,216', '1'):'';
         $progressBorder =  !empty($this->formData->settings->options->progress) ? bravepop_generate_style_props(isset($formStyle->progressColor) ? $formStyle->progressColor : '', 'border-color', '109,120,216', '1'):'';

         //Labels
         $labelColor = bravepop_generate_style_props(isset($formStyle->labelColor) ? $formStyle->labelColor : '', 'color', '68,68,68', '1');
         $labelBold = isset($formStyle->boldLabel) && $formStyle->boldLabel === true ?  'font-weight:bold;':'';
         $labelSize = bravepop_generate_style_props(isset($formStyle->labelSize) ? $formStyle->labelSize : 12, 'font-size');

         

         //Fields
         $borderColor = bravepop_generate_style_props(isset($formStyle->borderColor) ? $formStyle->borderColor : '', 'border-color', '221,221,221', '1');
         $inputBgColor = bravepop_generate_style_props(isset($formStyle->inputBgColor) ? $formStyle->inputBgColor : '', 'background-color', '255, 255, 255', '1');
         $inputFontColor = bravepop_generate_style_props(isset($formStyle->inputFontColor) ? $formStyle->inputFontColor : '', 'color', '51,51,51', '1');
         $inputFontSize = bravepop_generate_style_props(isset($formStyle->inputFontSize) ? $formStyle->inputFontSize : 12, 'font-size');
         $borderRadius = isset($formStyle->borderRadius) ?  'border-radius: '.$formStyle->borderRadius.'px;' : '';
         $borderSize = isset($formStyle->borderSize) ?  'border-width: '.$formStyle->borderSize.'px;' : 'border-width: 1px;';
         $spacing = isset($formStyle->spacing) ?  'margin: '.((isset($formStyle->spacing) ? $formStyle->spacing : 15)/2).'px 0px;' : 'margin: 7.5px 0px;';
         $lineHeight = isset($formStyle->lineHeight) ? 'line-height: '.$formStyle->lineHeight.'px;':'line-height: 18px;';
         $fielsdWidth = isset($formStyle->inline) && $formStyle->inline ?  'width: '.(100/count($visibleFields)).'%;' : '';
         $innerSpacing = isset($formStyle->innerSpacing) ?  'padding: '.$formStyle->innerSpacing.'px;' : 'padding: 12px;';

         //Button
         $buttonFont = isset($buttonStyle->fontFamily) && $buttonStyle->fontFamily !=='None'  ?  'font-family: '.$buttonStyle->fontFamily.';' : 'font-family: inherit;';
         $buttonBold =  isset($buttonStyle->bold) && $buttonStyle->bold === true ?  'font-weight: bold;' : '';
         $buttonfontBold = !empty($buttonStyle->fontVariation) && $buttonStyle->fontVariation !== 'regular' ?  'font-weight: '.str_replace('italic','', $buttonStyle->fontVariation).';' : $buttonBold;
         $buttonfontItalic = ( (!empty($buttonStyle->fontVariation) && strpos($buttonStyle->fontVariation, 'italic') !== false)) ? 'font-style: italic;' : '';
         $buttonWidth =  !empty($buttonStyle->fullwidth) || !empty($formStyle->inline) ?  'width: 100%;' : '';
         $buttonHeight =  isset($buttonStyle->height) ?  'height: '.$buttonStyle->height.'px;' : '';
         $buttonAlign =  isset($buttonStyle->align) && ($buttonStyle->align ==='left' || $buttonStyle->align ==='right') ? 'float: '.$buttonStyle->align.';' : '';
         $buttonRadius =  isset($buttonStyle->borderRadius) ?  'border-radius: '.$buttonStyle->borderRadius.'px;' : '';
         $buttonBgColor = bravepop_generate_style_props(isset($buttonStyle->bgColor) ? $buttonStyle->bgColor : '', 'background-color', '76,194,145', '1');
         $buttonFontColor = bravepop_generate_style_props(isset($buttonStyle->fontColor) ? $buttonStyle->fontColor : '', 'color', '255, 255, 255', '1');
         $buttonFontSize = bravepop_generate_style_props(isset($buttonStyle->fontSize) ? $buttonStyle->fontSize : 13, 'font-size');
         $stepLineHeight =  isset($buttonStyle->height) ?  'line-height: '.$buttonStyle->height.'px;' : 'line-height: 40px;';
         $selectonColor = bravepop_generate_style_props(isset($buttonStyle->bgColor) ? $buttonStyle->bgColor : '', 'color', '255, 255, 255', '1');
         $buttonBorderSize = isset($buttonStyle->borderSize) ?  'border-width: '.$buttonStyle->borderSize.'px;' : 'border-width: 0px;';
         $buttonBorderColor = bravepop_generate_style_props(isset($buttonStyle->borderColor) ? $buttonStyle->borderColor : '', 'border-color', '0,0,0', '1');
         if(isset($buttonStyle->bgColor->hex) && $buttonStyle->bgColor->hex === '#ffffff'){
            $selectonColor = 'color: inherit';
         }
      
         $iconColor = bravepop_generate_style_props(isset($buttonStyle->iconColor) ? $buttonStyle->iconColor : '', 'fill', '255, 255, 255', '1');
         $iconSize = isset($buttonStyle->icon) && isset($buttonStyle->fontSize) ? 'font-size: '.(($buttonStyle->fontSize * 85)/100).'px' : '';

         $imageSelectColor = isset($buttonStyle->bgColor->rgb) ? $buttonStyle->bgColor->rgb : '76, 194, 145';

         //buttonIconStyle = { color: button.icon && button.iconColor && button.iconColor.rgb ? `rgba(${button.iconColor.rgb}, 1)` : null, marginRight: '8px'}

         $elementLabelStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .braveform_label { '. $labelSize . $fontFamily . $fontBold . $fontItalic . $labelColor . $labelBold .'}';

         $elementInnerStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__styler, #brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_form_fields .formfield__checkbox_label{ '. $fontSize . $fontFamily . $fontColor . '}';

         $elementInputStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' input, #brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' textarea, #brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' select{ 
         '. $innerSpacing . $inputBgColor . $inputFontColor . $inputFontSize . $borderSize . $borderColor . $borderRadius . $fontFamily.' border-style: solid;}';

         $elementFieldSelctionStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' input[type="checkbox"]:checked:before, #brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' input[type="radio"]:checked:before{ '. $selectonColor . '}';
         
         $elementFieldsStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_form_field { '. $spacing . $lineHeight. $fielsdWidth .'}';
         
         $elementButtonStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_form_button button{ '. $fontFamily .$buttonWidth . $buttonHeight . $buttonRadius . $buttonBgColor . $buttonFontColor . $buttonFontSize . $buttonAlign. $buttonfontBold.$buttonFont.$buttonfontItalic.$buttonBorderSize. $buttonBorderColor.'}';
         $elementStepButtonStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_form_field--step .brave_form_stepNext{ '. $buttonFont .$buttonWidth . $buttonHeight . $buttonRadius . $buttonBgColor . $buttonFontColor . $buttonFontSize . $stepLineHeight. $buttonAlign.'}';
         $elementStepSkipStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_form_field--step .brave_form_skipstep{ '. $fontFamily . $buttonHeight .  $fontSize . $fontColor . $stepLineHeight.'}';

         $socialButtonStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .bravepopform_socialOptin_button{'. $fontFamily .$buttonHeight . $buttonRadius . $buttonFontSize . $buttonBold.$buttonFont.$buttonBorderSize. $buttonBorderColor.$stepLineHeight.'}';
         $socialButtonStyle .= '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .bravepopform_socialOptin_button--email{'. $buttonBgColor .$buttonFontColor .'}';

         $elementIconSize = isset($buttonStyle->icon) && $buttonStyle->icon ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element-icon{ '.$iconSize. '}' : '';
         $elementIconColor = isset($buttonStyle->icon) && $buttonStyle->icon ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_icon svg{ '.$iconColor. '}' : '';

         $elementImageSelect = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .formfield__inner__image--selected img{ border-color: rgba('.$imageSelectColor.', 1);}';
         $elementImageSelectIcon = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .formfield__inner__image__selection{ border-color: rgba('.$imageSelectColor.', 1) transparent transparent transparent;}';
         
         $formSuccessStyle = !empty($successFontSize) || !empty($successFontColor) ?'#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_form_custom_content{ '. $successFontSize .$successFontColor.'}' : '';
         $progressColorStyle = $progressBGColor ?  '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .bravepopupform_theProgressbar__bar{ '. $progressBGColor.'}' : '';
         $progressColorStyle .= $progressColor ?  '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .bravepopupform_theProgressbar_progress{ '. $progressColor.'}' : '';
         $progressColorStyle .= $progressBorder ?  '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .braveformBolt{ '. $progressBorder.' }' : '';

         $formInlineFieldWrap = !empty($formStyle->inline) ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_form_fields{width: calc(100% - '.(isset($formStyle->buttonWidth) ? $formStyle->buttonWidth : 100).'px)}' :'';
         $formInlineFieldButtonWrap = !empty($formStyle->inline) ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_form_button{width: '.(isset($formStyle->buttonWidth) ? $formStyle->buttonWidth : 100).'px; '.$spacing.' }' :'';

         $formCheckboxBordered = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_form_field--checkbox_borderd .formfield__inner__checkbox label{'. $borderSize . $borderColor . $borderRadius .'}';

         $hideGaptchaBadge = !empty($this->formData->settings->action->recaptcha) ? '.grecaptcha-badge{visibility: hidden}' : '';

         return  $elementInnerStyle . $elementInputStyle .$elementFieldsStyle .$elementLabelStyle. $formInlineFieldWrap.$formInlineFieldButtonWrap. $elementFieldSelctionStyle . $elementButtonStyle.$elementStepButtonStyle.$elementStepSkipStyle. $socialButtonStyle .$elementIconSize . $elementIconColor. $elementImageSelect.$elementImageSelectIcon.$formCheckboxBordered.$formSuccessStyle.$hideGaptchaBadge.$progressColorStyle.$this->ratingStyles.$this->buttonGroupStyles;

      }

      protected function renderInput($field){
         global $bravepop_global;
         $requiredStar = isset($field->required) && $field->required === true && !$this->disabelStar ? '*' : '';
         $firstlabel = isset($field->label) && $field->label ? $field->label : '';
         $secondLabel = isset($field->secondLabel) && $field->secondLabel ? $field->secondLabel: '';
         $placeholder = isset($field->placeholder) && $field->placeholder ? $field->placeholder.$requiredStar: '';
         $isHidden = isset($field->hidden) && $field->hidden ? 'brave_form_field--input_hidden': '';
         $secondPlaceholder = isset($field->secondPlaceholder) && $field->secondPlaceholder ? $field->secondPlaceholder.$requiredStar: '';
         $firstname= $field->id;
         $secondname= $field->id;
         $validation = isset($field->validation) && $field->validation ? $field->validation : 'text';
         $loggedin_user_email = !empty($this->currentUser['email']) ? $this->currentUser['email'] : '';
         $loggedin_user_fullname = !empty($this->currentUser['name']) ? $this->currentUser['name'] : '';
         $newsletter_name_field = !empty($this->formData->settings->action->newsletter) && !empty($this->formData->settings->action->newsletter->nameField) ? $this->formData->settings->action->newsletter->nameField : '';
         $newsletter_email_field = !empty($this->formData->settings->action->newsletter) && !empty($this->formData->settings->action->newsletter->emailField) ? $this->formData->settings->action->newsletter->emailField : '';
         $defaultValue = ''; $userIP = bravepop_getVisitorIP();
         $hasConditionClass = isset($field->has_condition) && !empty($field->has_condition) && isset($field->conditions) && is_array($field->conditions) && count($field->conditions) > 0 ? ' brave_form_field--hasCondition' : '';
         $condtionCheckAction = in_array($field->id, $this->conditionedFields) ? 'oninput="brave_check_field_condition(event, \''.$field->id.'\', \''.$this->data->id.'\')"' : '';

         if(isset($field->validation) && $field->validation === 'email' && $loggedin_user_email && $newsletter_email_field === $field->id){ $defaultValue = 'value="'.$loggedin_user_email.'"'; }
         if($loggedin_user_fullname && $newsletter_name_field === $field->id){ $defaultValue = 'value="'.$loggedin_user_fullname.'"'; }
         if(isset($field->defaultType) && $field->defaultType === 'static' && !empty($field->defaultValue) ){ $defaultValue = 'value="'.$field->defaultValue.'"';  }
         if(isset($field->defaultType) && $field->defaultType === 'utm' && !empty($field->defaultValue) && isset($_GET[$field->defaultValue]) ){ $defaultValue = 'value="'.$_GET[$field->defaultValue].'"';  }
         if(isset($field->defaultType) && $field->defaultType === 'country'  && !empty($bravepop_global['user_country']) ){ $defaultValue = 'value="'.$bravepop_global['user_country'].'"';  }
         if(isset($field->defaultType) && $field->defaultType === 'ip' && $userIP ){ $defaultValue = 'value="'.$userIP.'"';  }
         if(isset($field->defaultType) && $field->defaultType === 'user_email' && !empty($this->currentUser['email']) ){ $defaultValue = 'value="'.$this->currentUser['email'].'"';  }
         if(isset($field->defaultType) && $field->defaultType === 'user_name' && !empty($this->currentUser['name']) ){ $defaultValue = 'value="'.$this->currentUser['name'].'"';  }
         
         $fieldHTML = '<div id="brave_form_field'.$field->id.'" class="brave_form_field brave_form_field--input '.$isHidden.$hasConditionClass.'">';
            if(isset($field->validation) && $field->validation === 'name'){
               $firstNameLabel = $firstlabel? '<label class="braveform_label">'.$firstlabel.$requiredStar.'</label>' : '';
               $secondNameLabel = $secondLabel ? '<label class="braveform_label">'.$secondLabel.$requiredStar.'</label>' : '';
               $fieldHTML .= '<div class="formfield__inner__firstname">'.$firstNameLabel.'<div class="brave_form_field_error brave_form_field_error--firstname"></div><input class="brave_form_field_input-firstname" type="text" placeholder="'.esc_attr($placeholder).'" name="'.esc_attr($firstname).'[]" '.$condtionCheckAction.' /></div>';
               $fieldHTML .= '<div class="formfield__inner__lastname">'.$secondNameLabel.'<div class="brave_form_field_error brave_form_field_error--lastname"></div><input class="brave_form_field_input-lastname" type="text" placeholder="'.esc_attr($secondPlaceholder).'" name="'.esc_attr($secondname).'[]" '.$condtionCheckAction.' /></div>';

            }else{
               $fieldHTML .= $firstlabel ? '<label class="braveform_label">'.$field->label.$requiredStar.'</label>' : '';
               $fieldHTML .= '<div class="brave_form_field_error"></div>';
               if(isset($field->validation) && $field->validation === 'email'){
                  $fieldHTML .= '<input type="email" placeholder="'.esc_attr($placeholder).'"  name="'.esc_attr($firstname).'" '.($defaultValue).' class="'.($newsletter_email_field === $field->id ? 'brave_newsletter_emailField' : '').'" '.$condtionCheckAction.' />';
               }else{
                  $fieldHTML .= '<input type="text" placeholder="'.esc_attr($placeholder).'"  name="'.esc_attr($firstname).'" '.($defaultValue).' class="'.($newsletter_name_field === $field->id ? 'brave_newsletter_nameField' : '').'" '.$condtionCheckAction.' />';
               }
               
            }
         $fieldHTML .= '</div>';

        return  $fieldHTML;
      }

      protected function renderHidden($field){
         $defaultValue = '';  $userIP = bravepop_getVisitorIP();
         global $bravepop_global;
         if(isset($field->defaultType) && $field->defaultType === 'static' && !empty($field->defaultValue) ){ $defaultValue = $field->defaultValue;  }
         if(isset($field->defaultType) && $field->defaultType === 'utm' && !empty($field->defaultValue) && isset($_GET[$field->defaultValue]) ){ $defaultValue = $_GET[$field->defaultValue];  }
         if(isset($field->defaultType) && $field->defaultType === 'country' && !empty($bravepop_global['user_country']) ){ $defaultValue = $bravepop_global['user_country'];  }
         if(isset($field->defaultType) && $field->defaultType === 'ip' && $userIP ){ $defaultValue = $userIP;  }
         if(isset($field->defaultType) && $field->defaultType === 'user_email' && !empty($this->currentUser['email']) ){ $defaultValue = $this->currentUser['email'];  }
         if(isset($field->defaultType) && $field->defaultType === 'user_name' && !empty($this->currentUser['name']) ){ $defaultValue = $this->currentUser['name'];  }
         if(isset($field->defaultType) && $field->defaultType === 'language' ){ $defaultValue = bravepop_get_curent_lang();  }
         if(isset($field->defaultType) && $field->defaultType === 'pagetitle' ){
            global $wp_query;
            if(isset($wp_query) && isset($wp_query->post) && isset($wp_query->post->post_title)){
               $defaultValue = $wp_query->post->post_title;  
            }
         }

         $fieldHTML = '<div id="brave_form_field'.$field->id.'" class="brave_form_field brave_form_field--hidden">';
            $fieldHTML .= '<input type="hidden"  name="'.esc_attr($field->id).'" value="'.do_shortcode($defaultValue).'" />';
         $fieldHTML .= '</div>';

        return  $fieldHTML;
      }

      protected function renderTextarea($field){
         global $bravepop_global;
         $requiredStar = isset($field->required) && $field->required === true && !$this->disabelStar ? '*' : '';
         $label = isset($field->label) && $field->label ? $field->label : '';
         $height = isset($field->minHeight) && $field->minHeight ? $field->minHeight.'px' : '100px';
         $placeholder = isset($field->placeholder) && $field->placeholder ? $field->placeholder.$requiredStar: '';
         $fieldName= $field->id;
         $validation =isset($field->validation) && $field->validation ? $field->validation : 'text';
         $defaultValue = '';  $userIP = bravepop_getVisitorIP();
         $hasConditionClass = isset($field->has_condition) && !empty($field->has_condition) && isset($field->conditions) && is_array($field->conditions) && count($field->conditions) > 0 ? ' brave_form_field--hasCondition' : '';
         $condtionCheckAction = in_array($field->id, $this->conditionedFields) ? 'oninput="brave_check_field_condition(event, \''.$field->id.'\', \''.$this->data->id.'\')"' : '';

         if(isset($field->defaultType) && $field->defaultType === 'static' && !empty($field->defaultValue) ){ $defaultValue = $field->defaultValue;  }
         if(isset($field->defaultType) && $field->defaultType === 'utm' && !empty($field->defaultValue) && isset($_GET[$field->defaultValue]) ){ $defaultValue = $_GET[$field->defaultValue];  }
         if(isset($field->defaultType) && $field->defaultType === 'country'  && !empty($bravepop_global['user_country']) ){ $defaultValue = 'value="'.$bravepop_global['user_country'].'"';  }
         if(isset($field->defaultType) && $field->defaultType === 'ip' && $userIP ){ $defaultValue = 'value="'.$userIP.'"';  }
         if(isset($field->defaultType) && $field->defaultType === 'user_email' && !empty($this->currentUser['email']) ){ $defaultValue = 'value="'.$this->currentUser['email'].'"';  }
         if(isset($field->defaultType) && $field->defaultType === 'user_name' && !empty($this->currentUser['name']) ){ $defaultValue = 'value="'.$this->currentUser['name'].'"';  }

         $fieldHTML = '<div id="brave_form_field'.$field->id.'" class="brave_form_field brave_form_field--textarea '.$hasConditionClass.'">';
            $fieldHTML .= $label ? '<label class="braveform_label">'.$field->label.$requiredStar.'</label>' : '';
            $fieldHTML .= '<div class="brave_form_field_error"></div>';
            $fieldHTML .= '<textarea placeholder="'.esc_attr($placeholder).'" name="'.esc_attr($fieldName).'" style="height:'.esc_attr($height).'" '.$condtionCheckAction.' >'.$defaultValue.'</textarea>';
         $fieldHTML .= '</div>';

        return  $fieldHTML;
      }

      protected function renderCustomLabel($field){
         $label = isset($field->label) && $field->label ? html_entity_decode($field->label) : '';
         $color = bravepop_generate_style_props(isset($field->color) && $field->color ? $field->color : '', 'color', '51, 51, 51', '1');
         $fontSize = isset($field->fontSize) && $field->fontSize ? (Int)$field->fontSize: 16;
         $lineHeight = ($fontSize + ($fontSize/2));
         $fontWeight = isset($field->bold) && $field->bold === true ? 'font-weight:bold;' : '';
         $fieldStyle = 'style="'.$color.' font-size:'.$fontSize.'px; line-height: '.$lineHeight.'px;'.$fontWeight.'"'; 
         $hasConditionClass = isset($field->has_condition) && !empty($field->has_condition) && isset($field->conditions) && is_array($field->conditions) && count($field->conditions) > 0 ? ' brave_form_field--hasCondition' : '';


         $fieldHTML = '<div id="brave_form_field'.$field->id.'" class="brave_form_field brave_form_field--customLabel '.$hasConditionClass.'">';
            $fieldHTML .= '<div '.$fieldStyle.' >'.$label.'</div>';
         $fieldHTML .= '</div>';

        return  $fieldHTML;
      }

      protected function renderCustomMedia($field){
         $label = isset($field->label) && $field->label ? $field->label : '';
         $mediaType = isset($field->mediaType) && $field->mediaType ? $field->mediaType : 'image';
         $mediaURL = isset($field->url) && $field->url ? $field->url : '';
         $hasConditionClass = isset($field->has_condition) && !empty($field->has_condition) && isset($field->conditions) && is_array($field->conditions) && count($field->conditions) > 0 ? ' brave_form_field--hasCondition' : '';


         $fieldHTML = '<div id="brave_form_field'.$field->id.'" class="brave_form_field brave_form_field--media '.$hasConditionClass.'">';
            $fieldHTML .= $label ? '<label class="braveform_label">'.$field->label.'</label>' : '';
            $fieldHTML .= '<div class="brave_form_field_mediaWrap">';
            $fieldHTML .= $mediaURL && $mediaType === 'image' ? '<img src="'.$mediaURL.'" />' : '';
            $fieldHTML .= $mediaURL && $mediaType === 'video' ? ' <video width="100%" controls="true"><source src="'.$mediaURL.'"></source></video>' : '';
            $fieldHTML .= '</div>';
         $fieldHTML .= '</div>';

        return  $fieldHTML;
      }

      protected function renderButtons($field){
         $label = isset($field->label) && $field->label ? $field->label : '';
         $requiredStar = isset($field->required) && $field->required && $field->required === true && !$this->disabelStar ? '*' : '';
         $gotoNextStep = !empty($field->step) ? 'true' : 'false';
         $color = bravepop_generate_style_props(isset($field->color) && $field->color? $field->color : '', 'color', '255, 255, 255', '1');
         $background = bravepop_generate_style_props(isset($field->background) && $field->background ? $field->background : '', 'background-color', '51, 51, 51', '1');
         $fontSize = isset($field->fontSize) && $field->fontSize ? (Int)$field->fontSize: 14;
         $roundness = isset($field->roundness) && $field->roundness ? (Int)$field->roundness: 4;
         $height = isset($field->height) && $field->height ? (Int)$field->height: 12;
         //$stepData = str_replace('"','\'',wp_json_encode(array('formID'=> $this->data->id, 'totalSteps'=> $this->totalSteps, 'goto'=> $this->wrappedSteps )));
         $stepData = !empty($field->step) && $this->totalSteps > 0 ? ', \''.$this->data->id.'\', '.$this->totalSteps.', '.$this->wrappedSteps.'' :'';
         $hasConditionClass = isset($field->has_condition) && !empty($field->has_condition) && isset($field->conditions) && is_array($field->conditions) && count($field->conditions) > 0 ? ' brave_form_field--hasCondition' : '';
         $condtionCheckAction = in_array($field->id, $this->conditionedFields) ? 'brave_check_field_condition(event, \''.$field->id.'\', \''.$this->data->id.'\', \'checkbox\');' : '';

         $this->buttonGroupStyles .= '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' #brave_form_field'.$field->id.' .brave_form_field__buttonGroup{ padding: '.$height.'px 0px; font-size: '.$fontSize.'px;'.$color.$background.';border-radius: '.$roundness.'px;}';

         $fieldHTML = '<div id="brave_form_field'.$field->id.'" class="brave_form_field brave_form_field--buttons '.$hasConditionClass.'">';
         $fieldHTML .= $label ? '<label class="braveform_label">'.$field->label.$requiredStar.'</label>' : '';
         $fieldHTML .= '<div class="brave_form_field_error"></div>';
            $fieldHTML .= '<div class="brave_form_field__buttons">';
               if($field->options && is_array($field->options)){
                  foreach ($field->options as $index => $option) {
                     $optionLabel = !empty($option->label) ? $option->label : ''; $optionValue = !empty($option->value) ? $option->value : (!empty($option->label) ? $option->label : ''); 
                     $fieldHTML .= '<div class="brave_form_field__buttonGroup" onclick="brave_select_form_ButtonGroup(\''.$field->id.'\', \''.$index.'\', '.$gotoNextStep.' '.$stepData.');'.$condtionCheckAction.'" id="brave_form_field'.$field->id.'_opt-'.$index.'">'.$optionLabel.'<input name="'.esc_attr($field->id).'" type="radio" value="'.esc_attr($optionValue).'" /></div>';
                  }
               }
            $fieldHTML .= '</div>';
         $fieldHTML .= '</div>';

        return  $fieldHTML;
      }

      protected function renderDate($field){
         $requiredStar = isset($field->required) && $field->required === true  && !$this->disabelStar ? '*' : '';
         $label = isset($field->label) && $field->label ? $field->label : '';
         $placeholder = isset($field->placeholder) && $field->placeholder ? $field->placeholder.$requiredStar: '';
         $startDate = isset($field->startDate) && $field->startDate ? 'data-startdate="'.$field->startDate.'"' : '';
         $endDate = isset($field->endDate) && $field->endDate ? 'data-enddate="'.$field->endDate.'"' : '';
         $dateType = isset($field->dateType) && $field->dateType ? $field->dateType : '';
         $dateFormat = isset($field->dateFormat) && $field->dateFormat ? $field->dateFormat : '';
         $dateFormatNoYear = isset($field->dateFormat) && ($field->dateFormat === 'd/m' || $field->dateFormat === 'd-m' ||$field->dateFormat === 'm/d' || $field->dateFormat === 'm-d') ? true : false;
         $hasConditionClass = isset($field->has_condition) && !empty($field->has_condition) && isset($field->conditions) && is_array($field->conditions) && count($field->conditions) > 0 ? ' brave_form_field--hasCondition' : '';
         $fieldName= $field->id;
         $condtionCheckAction= in_array($field->id, $this->conditionedFields) ? 'onchange="brave_check_field_condition(event, \''.$field->id.'\', \''.$this->data->id.'\')"' : '';

         $fieldHTML = '<div id="brave_form_field'.$field->id.'" class="brave_form_field brave_form_field--date" '.$startDate.' '.$endDate.$hasConditionClass.'>';
            $fieldHTML .= $label ? '<label class="braveform_label">'.$field->label.$requiredStar.'</label>' : '';
            $fieldHTML .= '<div class="brave_form_field_error"></div>';
            $fieldHTML .= $dateType !== 'dropdown' ? '<input type="text" placeholder="'.esc_attr($placeholder).'" name="'.$fieldName.'" autoComplete="off" '.$condtionCheckAction.' />':'';
            
            if($dateType === 'dropdown'){
               $fieldHTML .= '<div id="brave_form_dropdown_date'.$field->id.'" class="brave_form_dropdown_dates '.($dateFormatNoYear ? 'brave_form_dropdown_dates--noYear' : '').'">';
               $daysSelection = array(); $monthsSelection = array(); $yearsSelection = array();
               for ($i=1; $i < 32; $i++) { $daysSelection[] = $i;} for ($m=1; $m < 13; $m++) { $monthsSelection[] = $m;}  
               for ($y=1930; $y < (date("Y")+1); $y++) { $yearsSelection[] = $y;} $yearsSelection = array_reverse($yearsSelection);

                  $fieldHTML .= '<select id="brave_form_field'.$fieldName.'-date" name="'.$fieldName.'" '.$condtionCheckAction.'>';
                     $fieldHTML .= '<option value="">'.__('Date', 'bravepop').'</option>';
                     foreach ($daysSelection as $key => $day) {
                        $fieldHTML .= '<option value="'.$day.'">'.$day.'</option>';
                     }
                  $fieldHTML .= '</select>';

                  $fieldHTML .= '<select id="brave_form_field'.$fieldName.'-month" name="'.$fieldName.'" '.$condtionCheckAction.'>';
                     $fieldHTML .= '<option value="">'.__('Month', 'bravepop').'</option>';
                     foreach ($monthsSelection as $key => $month) {
                        $fieldHTML .= '<option value="'.$month.'">'.$month.'</option>';
                     }
                  $fieldHTML .= '</select>';

                  if($dateFormatNoYear === false){
                     $fieldHTML .= '<select id="brave_form_field'.$fieldName.'-year" name="'.$fieldName.'" '.$condtionCheckAction.'>';
                        $fieldHTML .= '<option value="">'.__('Year', 'bravepop').'</option>';
                        foreach ($yearsSelection as $key => $year) {
                           $fieldHTML .= '<option value="'.$year.'">'.$year.'</option>';
                        }
                     $fieldHTML .= '</select>';
                  }

               $fieldHTML .= '</div>';
            }

         $fieldHTML .= '</div>';

        return  $fieldHTML;
      }

      protected function renderSelect($field){
         $requiredStar = isset($field->required) && $field->required === true && !$this->disabelStar ? '*' : '';
         $label = isset($field->label) && $field->label ? $field->label : '';
         $multi = isset($field->multi) && $field->multi === true ? 'multiple' : '';
         $defaultText = isset($field->defaultText) && $field->defaultText ? $field->defaultText : 'Select an Option...';
         $fieldName= isset($field->multi) && $field->multi? 'name="'.$field->id.'[]"' : 'name="'.$field->id.'"';
         $dropdownType = isset($field->dropdownType) && $field->dropdownType ?  $field->dropdownType : 'custom';
         $selectedCountry = isset($field->country) && $field->country ?  $field->country : false;
         $hasConditionClass = isset($field->has_condition) && !empty($field->has_condition) && isset($field->conditions) && is_array($field->conditions) && count($field->conditions) > 0 ? ' brave_form_field--hasCondition' : '';
         $condtionCheckAction = in_array($field->id, $this->conditionedFields) ? 'onchange="brave_check_field_condition(event, \''.$field->id.'\', \''.$this->data->id.'\')"' : '';

         $fieldHTML = '<div id="brave_form_field'.$field->id.'" class="brave_form_field brave_form_field--select '.$hasConditionClass.'">';
            $fieldHTML .= $label ? '<label class="braveform_label">'.$field->label.$requiredStar.'</label>' : '';
            $fieldHTML .= '<div class="brave_form_field_error"></div>';
            $fieldHTML .= '<select '.$multi.' '.$fieldName.' '.$condtionCheckAction.'  aria-label="'.($label ? $label : $defaultText).'">';
            $fieldHTML .= '<option value="none">'.$defaultText.'</option>';

            if($dropdownType === 'custom'){
               foreach ($field->options as $index => $option) {
                  $optionLabel = !empty($option->label) ? $option->label : ''; $optionValue = !empty($option->value) ? $option->value : ''; 
                  $fieldHTML .= '<option value="'.esc_attr($optionValue).'">'.$optionLabel.'</option>';
               }
            }else{
               if($dropdownType === 'country' || $dropdownType === 'city' || $dropdownType === 'state'){
                  $fieldHTML .= bravepopup_get_country_fields($dropdownType, $selectedCountry);
               }

            }

            $fieldHTML .= '</select>';
         $fieldHTML .= '</div>';

        return  $fieldHTML;
      }

      protected function renderCheckbox($field){
         $requiredStar = isset($field->required) && $field->required === true && !$this->disabelStar ? '*' : '';
         $label = isset($field->label) && $field->label ? html_entity_decode($field->label) : '';
         $inline = isset($field->inline) && $field->inline ? $field->inline : false;
         $bordered = isset($field->border) && $field->border ? $field->border : false;
         $hasConditionClass = isset($field->has_condition) && !empty($field->has_condition) && isset($field->conditions) && is_array($field->conditions) && count($field->conditions) > 0 ? ' brave_form_field--hasCondition' : '';
         $condtionCheckAction = in_array($field->id, $this->conditionedFields) ? 'onclick="brave_check_field_condition(event, \''.$field->id.'\', \''.$this->data->id.'\', \'checkbox\' )"' : '';

         $fieldName= $field->id;
         $fieldHTML = '<div id="brave_form_field'.$field->id.'" class="brave_form_field brave_form_field--checkbox '.$hasConditionClass.' '.($inline ? 'brave_form_field--checkbox_inline' : '').' '.($bordered ? ' brave_form_field--checkbox_borderd' : '').'">';
         $fieldHTML .= $label ? '<label class="braveform_label">'.$field->label.$requiredStar.'</label>' : '';
         $fieldHTML .= '<div class="brave_form_field_error"></div>';
         foreach ($field->options as $index => $option) {
            $optionLabel = !empty($option->label) ? html_entity_decode($option->label) : ''; $optionValue = !empty($option->value) ? $option->value : '';  $optionValue = $optionValue ? $optionValue : esc_attr($option->label);
            $fieldHTML .= '<div class="formfield__inner__checkbox"><label '.$condtionCheckAction.'><input name="'.esc_attr($fieldName).'[]" type="checkbox" value="'.esc_attr($optionValue).'" /><span class="formfield__checkbox_label">'.$optionLabel.'</span></label></div>';
         }

         $fieldHTML .= '</div>';

        return  $fieldHTML;
      }

      protected function renderImageSelect($field){
         $requiredStar = isset($field->required) && $field->required === true && !$this->disabelStar ? '*' : '';
         $label = isset($field->label) && $field->label ? $field->label : '';
         $fieldName= $field->id;
         $multi = isset($field->multi) && $field->multi? $field->multi : false;
         $imageCount =  isset($field->imageCount) && $field->imageCount ? $field->imageCount : 2;
         $hasConditionClass = isset($field->has_condition) && !empty($field->has_condition) && isset($field->conditions) && is_array($field->conditions) && count($field->conditions) > 0 ? ' brave_form_field--hasCondition' : '';
         $condtionCheckAction = in_array($field->id, $this->conditionedFields) ? 'brave_check_field_condition(event, \''.$field->id.'\', \''.$this->data->id.'\', \'checkbox\');' : '';
         $lazyLoad = bravepop_should_lazyload();

         $imageWidthStyle = 'style="width:'.((100/$imageCount) - 3).'%;"';
         $fieldHTML = '<div id="brave_form_field'.$field->id.'" class="brave_form_field brave_form_field--image '.$hasConditionClass.'">';
         $fieldHTML .= $label ? '<label class="braveform_label">'.$field->label.$requiredStar.'</label>' : '';
         $fieldHTML .= '<div class="brave_form_field_error"></div>';
         $fieldHTML .= '<div class="brave_form_field__imgWrap">';
         foreach ($field->options as $index => $option) {
            $imgSrc = $lazyLoad ? 'src="'.bravepop_get_preloader().'" data-lazy="'.$option->image.'"' : 'src="'.$option->image.'"';
            $optionLabel = !empty($option->label) ? $option->label : ''; $optionValue = !empty($option->value) ? $option->value : '';   $optionValue = $optionValue ? $optionValue : esc_attr($option->label);
            $fieldHTML .= '<div id="brave_form_field'.$field->id.'_opt-'.$index.'" class="formfield__inner__image" onclick="brave_select_imageField(\''.$field->id.'\', \''.$index.'\', '.wp_json_encode($multi).');'.$condtionCheckAction.'" '.$imageWidthStyle.'>';
            $fieldHTML .= '<div class="formfield__inner__image__selection">'.bravepop_renderIcon('check', '#fff').'</div>';
            $fieldHTML .= isset($option->image) && $option->image ? '<div class="formfield__inner__image_img"><img class="brave_element__form_imageselect brave_element_img_item skip-lazy no-lazyload" '.$imgSrc.' alt="'.$label.'" /></div>' : '<div class="formfield__inner__image_fake"></div>';
            $fieldHTML .= $multi ? '<input name="'.esc_attr($fieldName).'[]" type="checkbox" value="'.esc_attr($optionValue).'"   /><span>'.$optionLabel.'</span>' : '<input name="'.esc_attr($fieldName).'" type="radio" value="'.esc_attr($optionValue).'" '.($index === 0 ?'checked':'').'  /><span>'.$optionLabel.'</span>';
            $fieldHTML .= '</div>';
         }

         $fieldHTML .= '</div></div>';

        return  $fieldHTML;
      }

      protected function renderRadio($field){
         $requiredStar = isset($field->required) && $field->required === true && !$this->disabelStar ? '*' : '';
         $label = isset($field->label) && $field->label ? html_entity_decode($field->label) : '';
         $fieldName= $field->id;
         $inline = isset($field->inline) && $field->inline ? $field->inline : false;
         $bordered = isset($field->border) && $field->border ? $field->border : false;
         $hasConditionClass = isset($field->has_condition) && !empty($field->has_condition) && isset($field->conditions) && is_array($field->conditions) && count($field->conditions) > 0 ? ' brave_form_field--hasCondition' : '';
         $condtionCheckAction = in_array($field->id, $this->conditionedFields) ? 'oninput="brave_check_field_condition(event, \''.$field->id.'\', \''.$this->data->id.'\')"' : '';

         $fieldHTML = '<div id="brave_form_field'.$field->id.'" class="brave_form_field brave_form_field--radio '.$hasConditionClass.' '.($inline ? 'brave_form_field--radio_inline' : '').' '.($bordered ? ' brave_form_field--checkbox_borderd' : '').'">';
         $fieldHTML .= '<div class="brave_form_field_error"></div>';
         $fieldHTML .= $label ? '<label class="braveform_label">'.$field->label.$requiredStar.'</label>' : '';
         foreach ($field->options as $index => $option) {
            $optionLabel = !empty($option->label) ? html_entity_decode($option->label) : ''; $optionValue = !empty($option->value) ? $option->value : ''; $optionValue = $optionValue ? $optionValue : esc_attr($option->label);
            $fieldHTML .= '<div class="formfield__inner__checkbox"><label><input name="'.esc_attr($fieldName).'" type="radio" value="'.esc_attr($optionValue).'" '.$condtionCheckAction.' /><span class="formfield__checkbox_label">'.$optionLabel.'</span></label></div>';
         }

         $fieldHTML .= '</div>';

        return  $fieldHTML;
      }


      protected function renderRating($field){
         $requiredStar = isset($field->required) && $field->required === true && !$this->disabelStar ? '*' : '';
         $label = isset($field->label) && $field->label ? $field->label : '';
         $fieldName= $field->id;
         $ratingType = isset($field->ratingType) && $field->ratingType ? $field->ratingType : 'star';
         $tenItems = isset($field->tenItems) && $field->tenItems ? $field->tenItems : false;
         $ratingSize = isset($field->ratingSize) && $field->ratingSize ? $field->ratingSize : 20;
         $ratingColor = isset($field->ratingColor->hex) && $field->ratingColor->hex ? $field->ratingColor->hex : '#cccccc';
         $ratingTxtColor = isset($field->ratingTxtColor->hex) && $field->ratingTxtColor->hex ? $field->ratingTxtColor->hex : '#ffffff';
         $ratingFillColor = isset($field->ratingFillColor->hex) && $field->ratingFillColor->hex ? $field->ratingFillColor->hex : '#EFBA25';
         $hasConditionClass = isset($field->has_condition) && !empty($field->has_condition) && isset($field->conditions) && is_array($field->conditions) && count($field->conditions) > 0 ? ' brave_form_field--hasCondition' : '';
         $condtionCheckAction = in_array($field->id, $this->conditionedFields) ? 'brave_check_field_condition(event, \''.$field->id.'\', \''.$this->data->id.'\', \'rating\' );' : '';

         $itemCount = $tenItems ? 10 : 5;

         if($ratingType === 'smiley'){
            $this->ratingStyles .= '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' #brave_form_field'.$field->id.' .formfield__inner__ratings_smiley{ width: '.$ratingSize.'px; height: '.$ratingSize.'px;}';
            $this->ratingStyles .= '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' #brave_form_field'.$field->id.' .formfield__inner__ratings--selected svg circle{ stroke: '.$ratingFillColor.';}';
         }
         if($ratingType === 'number'){
            $this->ratingStyles .= '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' #brave_form_field'.$field->id.' .formfield__inner__ratings_number{ font-size: '.$ratingSize.'px; line-height: '.$ratingSize.'px; background: '.$ratingColor.';color: '.$ratingTxtColor.';}';
            $this->ratingStyles .= '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' #brave_form_field'.$field->id.' .formfield__inner__ratings--hovered, #brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' #brave_form_field'.$field->id.' .formfield__inner__ratings--selected{ background: '.$ratingFillColor.'}';
         }
         if($ratingType === 'star'){
            $this->ratingStyles .= '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' #brave_form_field'.$field->id.' .formfield__inner__ratings_star{ width: '.$ratingSize.'px;}';
            $this->ratingStyles .= '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' #brave_form_field'.$field->id.' .formfield__inner__ratings--selected svg path{ fill: '.$ratingFillColor.'}';
            $this->ratingStyles .= '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' #brave_form_field'.$field->id.' .formfield__inner__ratings--hovered svg path{ fill: '.$ratingFillColor.'}';
         }

         $fieldHTML = '<div id="brave_form_field'.$field->id.'" class="brave_form_field brave_form_field--rating '.$hasConditionClass.'" data-ratingtype="'.$ratingType.'" data-rated="false">';
         $fieldHTML .= $label ? '<label class="braveform_label">'.$field->label.$requiredStar.'</label>' : '';
         $fieldHTML .= '<div class="brave_form_field_error"></div>';

         if($ratingType === 'star' || $ratingType === 'number'){
            $numRatingSize = 'normal'; if($ratingSize > 28){  $numRatingSize = 'large';  } if($ratingSize < 16){  $numRatingSize = 'small';  }
            //$numStyle = $ratingSize ? 'style="font-size: '.$ratingSize.'px;"' : '';
            $fieldHTML .= '<div class="brave_form_ratings_wrap brave_form_ratings_wrap--'.$ratingType.'" onmouseleave="brave_form_rating_unhover(\''.$field->id.'\',)">';
            for ($index = 1; $index <= $itemCount; $index++) {
               $item = $ratingType === 'number'? '<span>'.$index.'</span>' : bravepop_renderIcon('star', $ratingColor);
               $fieldHTML .= '<div class="formfield__inner__ratings_'.$ratingType.' formfield__inner__ratings_'.$ratingType.'--'.$numRatingSize.'" onclick="brave_form_rate(\''.$field->id.'\','.$index.');'.$condtionCheckAction.'" onmouseenter="brave_form_rating_hover(\''.$field->id.'\','.$index.')"><label>'.$item.'<input name="'.$fieldName.'" type="radio" value="'.$index.'" /></label></div>';
            }
            $fieldHTML .= '</div>';
         }else if( $ratingType === 'smiley'){
            $fieldHTML .= '<div class="brave_form_ratings_wrap">';
            $fieldHTML .= '<div class="formfield__inner__ratings_smiley" onclick="brave_form_rate(\''.$field->id.'\',1, true);'.$condtionCheckAction.'">'.bravepop_renderIcon('smiley1').'<input name="'.esc_attr($fieldName).'" type="radio" value="1" /></div>';
            $fieldHTML .= '<div class="formfield__inner__ratings_smiley" onclick="brave_form_rate(\''.$field->id.'\',2, true);'.$condtionCheckAction.'">'.bravepop_renderIcon('smiley2').'<input name="'.esc_attr($fieldName).'" type="radio" value="2" /></div>';
            $fieldHTML .= '<div class="formfield__inner__ratings_smiley" onclick="brave_form_rate(\''.$field->id.'\',3, true);'.$condtionCheckAction.'">'.bravepop_renderIcon('smiley3').'<input name="'.esc_attr($fieldName).'" type="radio" value="3" /></div>';
            $fieldHTML .= '<div class="formfield__inner__ratings_smiley" onclick="brave_form_rate(\''.$field->id.'\',4, true);'.$condtionCheckAction.'">'.bravepop_renderIcon('smiley4').'<input name="'.esc_attr($fieldName).'" type="radio" value="4" /></div>';
            $fieldHTML .= '<div class="formfield__inner__ratings_smiley" onclick="brave_form_rate(\''.$field->id.'\',5, true);'.$condtionCheckAction.'">'.bravepop_renderIcon('smiley5').'<input name="'.esc_attr($fieldName).'" type="radio" value="5" /></div>';
            $fieldHTML .= '</div>';
         }
         $fieldHTML .= '</div>';

        return  $fieldHTML;
      }

      protected function renderStep($field, $index){
         if($index === 0){ return; }
         $totalFields = isset($this->formFields) ? count($this->formFields) : 0;
         if($index === ($totalFields - 1)){ return; }
         //$this->totalSteps = $this->totalSteps+1;
         $hideStepBack = isset($this->formData->settings->options->hideStepBack) && $this->formData->settings->options->hideStepBack ? true : false;
         $formStyle = isset($this->formData->settings->style) ? $this->formData->settings->style : null;
         $fontColor = bravepop_generate_style_props(isset($formStyle->fontColor) ? $formStyle->fontColor : '', 'color', '107, 107, 107', '1');
         $buttonStyle = isset($this->formData->settings->button) ? $this->formData->settings->button : null;
         $buttonFull = isset($buttonStyle->fullwidth) && $buttonStyle->fullwidth ? true : false;
         $buttonIconColor = isset($buttonStyle->fontColor) && isset($buttonStyle->fontColor->rgb) ? 'rgb('.$buttonStyle->fontColor->rgb.')' : '#fff';
         $stepColor = $buttonFull ? (isset($buttonStyle->fontColor) && isset($buttonStyle->fontColor->rgb) ? 'rgb('.$buttonStyle->fontColor->rgb.')' :'#fff' ) : (isset($formStyle->fontColor)&& isset($formStyle->fontColor->rgb) ? 'rgb('.$formStyle->fontColor->rgb.')' : '#fff');
         $buttonAlign =  isset($buttonStyle->align) ?  $buttonStyle->align : 'right';
         $stepButtonRight = $buttonAlign==='left' ? 'brave_form_stepBack--right': '';
         $hasConditionClass = isset($field->has_condition) && !empty($field->has_condition) && isset($field->conditions) && !empty($field->has_condition) && is_array($field->conditions) && count($field->conditions) > 0 ? ' brave_form_field--hasCondition' : '';
         $buttonFullCalss = $buttonFull ? ' brave_form_step--fullbutton' : '';
         $buttonAlignCalss = ' brave_form_step--'.$buttonAlign;

         $label = isset($field->label) && $field->label ? $field->label : '';
         $skippable = isset($field->skippable) && !empty($field->skippable) ? $field->skippable : '';
         $skipLabel = isset($field->skipLabel) && !empty($field->skipLabel) ? $field->skipLabel : '';
         $arrow = isset($field->arrow) && $field->arrow === true ? ' &rarr;' : '';
         $changeHeight = isset($field->changeHeight) && $field->changeHeight === true ? true : false; 
         if($changeHeight && $this->changesFormHeight === false){  $this->changesFormHeight = true; }
         $formHeight = isset($this->data->height) ? $this->data->height : '';
         $newHeight = isset($field->height) && $field->height && $changeHeight === true ? $field->height : false;
         $this->formHeightData[$this->wrappedSteps] = $changeHeight ? (Int)$newHeight : (Int)$formHeight  ;

         $fieldHTML = '<div id="brave_form_field'.$field->id.'" class="brave_form_field brave_form_field--step '.$hasConditionClass.$buttonAlignCalss.$buttonFullCalss.'" data-steps="'.$this->wrappedSteps.'">';
         $fieldHTML .= ($this->wrappedSteps > 1 && !$hideStepBack) ? '<a class="brave_form_stepBack '.$stepButtonRight.'" onclick="brave_form_goBack(\''.$this->data->id.'\', '.$this->totalSteps.')">'.bravepop_renderIcon('arrow-left', $stepColor).'</a>':'';
         
         $fieldHTML .= ($buttonAlign ==='right') && ($skippable && $skipLabel) ? '<a class="brave_form_skipstep" onclick="brave_form_gotoStep(\''.$this->data->id.'\', '.$this->totalSteps.', '.($this->wrappedSteps).')">'.$skipLabel.' </a>' : '';
         $fieldHTML .= '<a class="brave_form_stepNext" onclick="brave_form_gotoStep(\''.$this->data->id.'\', '.$this->totalSteps.', '.($this->wrappedSteps).')">'.$label.$arrow.' </a>';
         $fieldHTML .= ($buttonAlign ==='left' || $buttonAlign ==='center') && ($skippable && $skipLabel) ? '<a class="brave_form_skipstep" onclick="brave_form_gotoStep(\''.$this->data->id.'\', '.$this->totalSteps.', '.($this->wrappedSteps).')">'.$skipLabel.' </a>' : '';

         $fieldHTML .= '</div>';
         if($this->totalSteps > 1){
            $fieldHTML .= '</div><div class="brave_form_fields_step brave_form_fields_step'.$this->wrappedSteps.'">';
         }
         $this->wrappedSteps = $this->wrappedSteps + 1;
        return  $fieldHTML;
      }

      protected function renderButton($button){
         $hideStepBack = !empty($this->formData->settings->options->hideStepBack) ? true : false;
         $buttonText = !empty($button->buttonText) && $button->buttonText ? $button->buttonText : '';
         $formStyle = isset($this->formData->settings->style) ? $this->formData->settings->style : null;
         $fontColor = bravepop_generate_style_props(isset($formStyle->fontColor) ? $formStyle->fontColor : '', 'color', '107, 107, 107', '1');
         //$buttonIcon = isset($button->icon) ? $this->buttonIcon : '';
         $buttonStyle = isset($this->formData->settings->button) ? $this->formData->settings->button : null;
         $buttonIconColor = isset($buttonStyle->fontColor) && isset($buttonStyle->iconColor->rgb) ? 'rgb('.$buttonStyle->iconColor->rgb.')' : '#fff';
         $buttonFull = isset($buttonStyle->fullwidth) && $buttonStyle->fullwidth ? 'brave_form_button--full' : '';
         $socBorderRadius = !empty($buttonStyle->borderRadius) ? ' style="border-radius: '.$buttonStyle->borderRadius.'px;"' : ''; 
         $stepColor = $buttonFull ? (isset($buttonStyle->fontColor) && isset($buttonStyle->fontColor->rgb) ? 'rgb('.$buttonStyle->fontColor->rgb.')' :'#fff' ) : (isset($formStyle->fontColor)&& isset($formStyle->fontColor->rgb) ? 'rgb('.$formStyle->fontColor->rgb.')' : '#fff');
         $buttonAlign =  isset($buttonStyle->align) ?  $buttonStyle->align : 'right';
         $stepButtonRight = $buttonAlign==='left' ? 'brave_form_stepBack--right': '';
         $centerButtonAlign = $buttonAlign==='center' ? ' brave_form_button--center': '';

         $stepHideClass = $this->totalSteps > 0 ? 'brave_form_button--hide' : '';
         $loadingIcon = '<span id="brave_form_loading_'.$this->data->id.'" class="brave_form_loading">'.bravepop_renderIcon('reload', $buttonIconColor).'</span>';

         $stepBackButton = ($this->wrappedSteps > 1 && !$hideStepBack) ? '<a class="brave_form_stepBack '.$stepButtonRight.'" onclick="brave_form_goBack(\''.$this->data->id.'\', '.$this->totalSteps.')">'.bravepop_renderIcon('arrow-left', $stepColor).'</a>':'';
         $socialBackButton = ($this->social_optin && $this->totalSteps === 0) ? '<a class="brave_form_stepBack brave_social_optin_stepBack" onclick="brave_social_optin_goBack(\''.$this->data->id.'\')" '.$socBorderRadius.'>'.bravepop_renderIcon('arrow-left', '#ffffff').'</a>':'';

         $iconHTML = '';
         if(isset($this->formData->settings->button->icon->body)){
            $iconHTML = '<span class="brave_element-icon"><svg viewBox="0 0 '.$this->formData->settings->button->icon->width.' '.$this->formData->settings->button->icon->height.'" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">'.str_replace('currentColor', $buttonIconColor ,html_entity_decode($this->formData->settings->button->icon->body)).'</svg></span>';
         }

         return '<div class="brave_form_button '.$buttonFull.$centerButtonAlign.' '.$stepHideClass.'">'.$stepBackButton.$socialBackButton.'<button id="brave_form_button-'.esc_attr($this->data->id).'">'.$loadingIcon.$iconHTML.$buttonText.'</button></div>';
      }

      protected function renderFields(){
         if(!isset($this->formFields)){  return ''; }
         $fieldsHTML = '<div class="brave_form_fields" data-step="0">';
         if($this->totalSteps > 0){   $fieldsHTML .= '<div class="brave_form_fields_step brave_form_fields_step0 brave_form_fields_step--show">';  }
         foreach ($this->formFields as $index => $field) {
            if(isset($field->type)){
               switch ($field->type) {
                  case 'input':
                     $fieldsHTML .=  $this->renderInput($field);
                     break;
                  case 'hidden':
                     $fieldsHTML .=  $this->renderHidden($field);
                     break;
                  case 'textarea':
                     $fieldsHTML .=   $this->renderTextarea($field);
                     break;
                  case 'checkbox':
                     $fieldsHTML .=   $this->renderCheckbox($field);
                     break;
                  case 'image':
                     $fieldsHTML .=   $this->renderImageSelect($field);
                     break;
                  case 'radio':
                     $fieldsHTML .=   $this->renderRadio($field);
                     break;
                  case 'date':
                     $fieldsHTML .=  $this->renderDate($field);
                     break;
                  case 'select':
                     $fieldsHTML .=  $this->renderSelect($field);
                     break;
                  case 'rating':
                     $fieldsHTML .=  $this->renderRating($field);
                     break;
                  case 'label':
                     $fieldsHTML .=  $this->renderCustomLabel($field);
                     break;
                  case 'media':
                     $fieldsHTML .=  $this->renderCustomMedia($field);
                     break;
                  case 'buttons':
                     $fieldsHTML .=  $this->renderButtons($field);
                     break;
                  case 'step':
                     $fieldsHTML .=  $this->renderStep($field, $index);
                     break;
                  default:
                     $fieldsHTML .='';
                     break;
               }
            }
            
         }

         //Security Field
         $fieldsHTML .= wp_nonce_field( 'brave-ajax-form-nonce', 'brave_form_security'.$this->data->id );

         //Closing Tags
         if($this->totalSteps > 0){   $fieldsHTML .= '</div>';  }
         $fieldsHTML .= '</div>';

         //if($this->totalSteps > 0){  $fieldsHTML .= '<a class="brave_form_stepBack brave_form_stepBack--hide" onclick="brave_form_goBack(\''.$this->data->id.'\', '.$this->totalSteps.')">'.bravepop_renderIcon('arrow-left', '#fff').'</a>';}
         $fieldsHTML .= $this->renderButton(isset($this->formData->settings->button) ? $this->formData->settings->button : '');

         return $fieldsHTML;
      }

      
      protected function processFields($formData){
         $filteredFormData = apply_filters( 'bravepop_form_element_customFields', $formData );
         $finalFields = isset($filteredFormData->fields) ? $filteredFormData->fields : array();
         
         foreach ($finalFields as $key => $value) {
            if(isset($value->conditions) && is_array($value->conditions)){
               foreach ($value->conditions as $key => $condition) {
                  if(isset($condition->field)){
                     $this->conditionedFields[] = $condition->field;
                  }
               }
            }
         }

        return $finalFields;
      }


      protected function renderSocialOptins(){
         $socialFillOnly = !empty($this->formData->settings->action->newsletter->advancedSettings->socialFillOnly) ? 'true' : 'false';
         $socHTML = '<div class="bravepopupform_socialOptin"  id="bravepopupform_socialOptin-'.$this->data->id.'" data-fillonly="'.$socialFillOnly.'">';
         $socHTML .= '<div class="bravepopupform_socialOptin_loader"><span class="bravepopupform_socialOptin_icon"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18.364 5.636L16.95 7.05A7 7 0 1 0 19 12h2a9 9 0 1 1-2.636-6.364z" fill="#626262"/><rect x="0" y="0" width="24" height="24" fill="rgba(0, 0, 0, 0)" /></svg></span></div>';
            $socHTML .= '<div class="bravepopupform_socialOptin_inner">';
               foreach ($this->social_settings as $key => $item) {  
                  if(!empty($item->enabled)){ 
                     $icons = array(
                        'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" width="20" height="20"><path d="M279.14 288l14.22-92.66h-88.91v-60.13c0-25.35 12.42-50.06 52.24-50.06h40.42V6.26S260.43 0 225.36 0c-73.22 0-121.08 44.38-121.08 124.72v70.62H22.89V288h81.39v224h100.17V288z" fill="#fff"/></svg>',
                        'google' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" width="20" height="20"><path d="M881 442.4H519.7v148.5h206.4c-8.9 48-35.9 88.6-76.6 115.8c-34.4 23-78.3 36.6-129.9 36.6c-99.9 0-184.4-67.5-214.6-158.2c-7.6-23-12-47.6-12-72.9s4.4-49.9 12-72.9c30.3-90.6 114.8-158.1 214.7-158.1c56.3 0 106.8 19.4 146.6 57.4l110-110.1c-66.5-62-153.2-100-256.6-100c-149.9 0-279.6 86-342.7 211.4c-26 51.8-40.8 110.4-40.8 172.4S151 632.8 177 684.6C240.1 810 369.8 896 519.7 896c103.6 0 190.4-34.4 253.8-93c72.5-66.8 114.4-165.2 114.4-282.1c0-27.2-2.4-53.3-6.9-78.5z" fill="#fff" /><rect x="0" y="0" width="1024" height="1024" fill="transparent" /></svg>',
                        'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="20" height="20"><path d="M416 32H31.9C14.3 32 0 46.5 0 64.3v383.4C0 465.5 14.3 480 31.9 480H416c17.6 0 32-14.5 32-32.3V64.3c0-17.8-14.4-32.3-32-32.3zM135.4 416H69V202.2h66.5V416zm-33.2-243c-21.3 0-38.5-17.3-38.5-38.5S80.9 96 102.2 96c21.2 0 38.5 17.3 38.5 38.5 0 21.3-17.2 38.5-38.5 38.5zm282.1 243h-66.4V312c0-24.8-.5-56.7-34.5-56.7-34.6 0-39.9 27-39.9 54.9V416h-66.4V202.2h63.7v29.2h.9c8.9-16.8 30.6-34.5 62.9-34.5 67.2 0 79.7 44.3 79.7 101.9V416z" fill="#fff"/></svg>',
                        'email' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20"><path d="M20.572 5.083l-7.896 7.037a1 1 0 0 1-1.331 0L3.416 5.087A2 2 0 0 1 4 5h16a2 2 0 0 1 .572.083zm1.356 1.385c.047.17.072.348.072.532v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 .072-.534l7.942 7.148a3 3 0 0 0 3.992 0l7.922-7.146z" fill="#fff"/><rect x="0" y="0" width="24" height="24" fill="transparent" /></svg>'
                     );
                     $onMouseOver = $item->type === 'google' ? 'onmouseover="window.loginButtonElementID=\'bravepopform_socialOptin_button-'.$this->data->id.'-'.$item->type.'\'"' : '';
                     $socHTML .= '<a id="bravepopform_socialOptin_button-'.$this->data->id.'-'.$item->type.'" class="bravepopform_socialOptin_button bravepopform_socialOptin_button--'.$item->type.'" onclick="bavepop_social_optin(\''.$item->type.'\', \''.$this->data->id.'\')" data-id="'.$this->data->id.'" data-action="optin" data-popupid="'.$this->popupID.'" '.$onMouseOver.'><span>'.$icons[$item->type].'</span> '.(isset($item->label) ? $item->label : '').'</a>';
                  }  
               }
               if($this->consentField && isset($this->consentField->id)){
                  $socConsentHTML = '<div class="bravepopupform_socialOptin_consent">'.$this->renderCheckbox($this->consentField).'</div>';
                  $socConsentHTML = str_replace('brave_form_field'.$this->consentField->id,'brave_form_field'.$this->consentField->id.'_consent', $socConsentHTML);
                  $socConsentHTML = str_replace('<label>','<label onclick="bravepop_social_optin_consent(\''.$this->data->id.'\', \''.$this->consentField->id.'\')">', $socConsentHTML);
                  $socHTML .= $socConsentHTML;
               }
            $socHTML .= '</div>';
         $socHTML .= '</div>';

         return $socHTML;
      }

      protected function renderProgressbar(){
         if(empty($this->formData->settings->options->progress)){ return '';}
         $progressStyle = $this->formData->settings->options->progress;
         
         $progressHTML = ' <div id="'.$this->data->id.'__form_progress" class="bravepopupform_theProgressbar bravepopupform_theProgressbar--'.$progressStyle.'" data-style="'.$progressStyle.'">';
            $progressHTML .= '<div class="bravepopupform_theProgressbar__barWrap">';
               $progressHTML .= $progressStyle === 'style1' ? '<span class="bravepopupform_theProgressbar_progress">'.(round((1/$this->totalSteps)*100)).'%</span>':'';
               if($progressStyle === 'style2'){
                  $progressHTML .= '<div class="bravepopupform_theProgressbar__bolts">';
                     for ($i=0; $i < $this->totalSteps ; $i++) { 
                        $progressHTML .= '<i class="braveformBolt"></i>';
                     }
                  $progressHTML .= '</div>';
               }
               $progressHTML .= '<div class="bravepopupform_theProgressbar__bar" style="width: '.($progressStyle === 'style2' ? 0 : ((1/$this->totalSteps)*100)).'%"></div>';

            $progressHTML .= '</div>';
         if($progressStyle === 'style1'){
            $progressHTML .= '<span class="bravepopupform_theProgressbar_steps">1/'.($this->totalSteps).'</span>';
         }

         $progressHTML .= '</div>';

         return $progressHTML;
      }


      public function render( ) { 
         $formStyle = isset($this->formData->settings->style) ? $this->formData->settings->style : null;
         $underlineClass = isset($formStyle->underline) && $formStyle->underline === true ? 'brave_form_form--underline' : '';
         $inlineClass = isset($formStyle->inline) && $formStyle->inline === true ? 'brave_form_form--inline' : '';
         $hasDateClass = $this->hasDate ? 'brave_form_form--hasDate' : '';
         $nolabelClass = $this->nolabel ? 'brave_form_form--noLabel' : '';
         $cookiesToCheck = function_exists('bravepop_newsletter_cookie_conditions') && !empty($this->formData->settings->action->newsletter->advancedSettings->conditional) && isset($this->formData->settings->action->newsletter->advancedSettings->conditions) ? bravepop_newsletter_cookie_conditions($this->formData->settings->action->newsletter->advancedSettings->conditions) : '' ;
         $customClass = !empty($this->data->classes) ? ' '. str_replace(',',' ',$this->data->classes) : ''; 
         $hasConditionClass = is_array($this->conditionedFields)  && count($this->conditionedFields) > 0 ? ' brave_element__form_inner--hasConditions' : '';
         $hasSteps = $this->totalSteps > 0 ? 'brave_element__form_inner--hasSteps ' : '';

         return '<div id="brave_element-'.$this->data->id.'" class="brave_element brave_element--form '.$customClass.'">
                  <div class="brave_element__wrap">
                     <div class="brave_element__styler">
                        <div class="brave_element__inner">
                           '.($this->social_optin && $this->social_settings ? $this->renderSocialOptins():'').'
                           <div class="brave_element__form_inner '.$hasSteps.($this->social_optin && $this->social_settings ? 'brave_element__form_inner--hide':'').$hasConditionClass.'">
                           '.$this->renderProgressbar().'
                              <form id="brave_form_'.$this->data->id.'" class="brave_form_form '.$underlineClass.' '.$hasDateClass.' '.$inlineClass.' '.$nolabelClass.'" method="post" data-cookies="'.$cookiesToCheck.'" onsubmit="brave_submit_form(event, brave_popup_formData[\''.$this->data->id.'\'] )">
                                 <div class="brave_form_overlay"></div>'
                                 .$this->renderFields().
                                 do_action( 'bravepop_after_form_fields', $this->data->id ).
                              '</form>
                              <div id="brave_form_custom_content'.$this->data->id.'" class="brave_form_custom_content"></div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>';
      }


   }


}
?>