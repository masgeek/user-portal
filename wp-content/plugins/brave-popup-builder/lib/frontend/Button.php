<?php

if ( ! class_exists( 'BravePop_Element_Button' ) ) {
   

   class BravePop_Element_Button {

      protected $data;
      protected $popupID;
      protected $stepIndex;
      protected $elementIndex;
      protected $goalItem;
      protected $dynamicData;

      function __construct($data=null, $popupID=null, $stepIndex=0, $elementIndex=0, $device='desktop', $goalItem=false, $dynamicData=null) {
         $this->data = $data;
         $this->popupID = $popupID;
         $this->stepIndex =  $stepIndex;
         $this->elementIndex = $elementIndex;
         $this->goalItem = $goalItem;
         $this->dynamicData = $dynamicData ? $dynamicData : new stdClass();
      }


      public function render_css() { 

         $lineHeight = isset($this->data->height) ? 'line-height: '.$this->data->height.'px;' : '';
         $textAlign = isset($this->data->textAlign) ?  'text-align: '.$this->data->textAlign.';' : '';
         $fontSize = isset($this->data->fontSize) ?   'font-size: '.$this->data->fontSize.'px;' : '';
         $letterSpacing = isset($this->data->letterSpacing) ?   'letter-spacing: '.$this->data->letterSpacing.'px;' : '';
         $fontFamily = isset($this->data->fontFamily) && $this->data->fontFamily !== 'None' ?  'font-family: '.$this->data->fontFamily.';' : '';
         $fontBold = isset($this->data->bold) && $this->data->bold === true ?  'font-weight: bold;' : '';
         $fontBold = !empty($this->data->fontVariation) && $this->data->fontVariation !== 'regular' ?  'font-weight: '.str_replace('italic','', $this->data->fontVariation).';' : $fontBold;
         $fontItalic = ( (!empty($this->data->fontVariation) && strpos($this->data->fontVariation, 'italic') !== false)) ? 'font-style: italic;' : '';

         $fontColorRGB = isset($this->data->fontColor) && isset($this->data->fontColor->rgb) ? $this->data->fontColor->rgb :'0,0,0';
         $fontColorOpacity = isset($this->data->fontColor) && isset($this->data->fontColor->opacity) ? $this->data->fontColor->opacity :'1';
         $fontColor = 'color: rgba('.$fontColorRGB.', '.$fontColorOpacity.');';
         $borderRadius= isset($this->data->borderRadius) ?   'border-radius: '.$this->data->borderRadius.'px;' : '';

         $bgColorRGB = isset($this->data->bgColor) && isset($this->data->bgColor->rgb) ? $this->data->bgColor->rgb :'0,0,0';
         $bgColorOpacity = isset($this->data->bgColor) && isset($this->data->bgColor->opacity) ? $this->data->bgColor->opacity :'1';
         $bgColor = 'background-color: rgba('.$bgColorRGB.', '.$bgColorOpacity.');';

         $borderStyle = '';  $shadowStyle = '';
         if(isset($this->data->border) && $this->data->border === true){
            $borderColorRGB = isset($this->data->borderColor) && isset($this->data->borderColor->rgb) ? $this->data->borderColor->rgb :'0,0,0';
            $borderColorOpacity = isset($this->data->borderColor) && isset($this->data->borderColor->opacity) ? $this->data->borderColor->opacity :'1';
            $borderColor = 'rgba('.$borderColorRGB.', '.$borderColorOpacity.')';
            $borderSize = isset($this->data->borderSize) ? $this->data->borderSize.'px' : '1px';
            $borderStyle = 'border: '.$borderSize .' solid '.$borderColor.';';
         }

         if(isset($this->data->shadow) && $this->data->shadow === true){
            $shadowColorRGB = isset($this->data->shadowColor) && isset($this->data->shadowColor->rgb) ? $this->data->shadowColor->rgb :'0,0,0';
            $shadowColorOpacity = isset($this->data->shadowColor) && isset($this->data->shadowColor->opacity) ? $this->data->shadowColor->opacity :'1';
            $shadowColor = 'rgba('.$shadowColorRGB.', '.$shadowColorOpacity.')';
            $shadowSize = isset($this->data->shadowSize) ? $this->data->shadowSize.'px' : '10px';
            $shadowStyle = 'box-shadow: 0 0 '.$shadowSize .' '.$shadowColor.';';
         }

         $iconSize = isset($this->data->icon) && isset($this->data->fontSize) ? 'font-size: '.(($this->data->fontSize * 85)/100).'px' : '';


         $elementInnerStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__styler{
            '.$textAlign .  $fontSize .  $fontFamily .  $borderRadius . $fontBold. $fontItalic . $fontColor . $borderStyle . $shadowStyle . $bgColor .  $lineHeight .$letterSpacing.
         '}';

         $elementIconSize = isset($this->data->icon) ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element-icon{ '.$iconSize. '}' : '';
         $elementHoverStyle = '';

         if(isset($this->data->hoverAnimation) && isset($this->data->hoverColors) && $this->data->hoverAnimation === 'color' ){
            $hoverBgRgb = isset($this->data->hoverColors->background) && isset($this->data->hoverColors->background->rgb) ? $this->data->hoverColors->background->rgb :'';
            $hoverBgOpacity = isset($this->data->borderColor) && isset($this->data->borderColor->opacity) ? $this->data->borderColor->opacity :'1';
            $hoverBg = $hoverBgRgb ? 'background-color: rgba('.$hoverBgRgb.', '.$hoverBgOpacity.');' : '';
            $fontColor = isset($this->data->hoverColors->text) && isset($this->data->hoverColors->text->hex) ? 'color: '.$this->data->hoverColors->text->hex.';' : '';
            $elementHoverStyle = ($hoverBg || $fontColor) ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__styler:hover{ '.$hoverBg.$fontColor. '}' : '';
         }

         return  $elementInnerStyle . $elementIconSize . $elementHoverStyle;

      }


      public function render( ) { 
         $buttonText = isset($this->data->buttonText) ? esc_html($this->data->buttonText) : '';
         $hoverClass = isset($this->data->hoverAnimation) && $this->data->hoverAnimation !== 'none' ? 'brave_element--hasHoverAnim brave_element--button--hover_'.$this->data->hoverAnimation :'';
         $actionType = isset($this->data->action->type) ? $this->data->action->type : 'none';
         $actionURL  = isset($this->data->action->actionData->url) ? $this->data->action->actionData->url : '';
         $actionPhone  = !empty($this->data->action->actionData->phone) ? $this->data->action->actionData->phone : '';
         $actionDownload = !empty($this->data->action->actionData->download) ? 'download': '';
         $actionNoFollow  = !empty($this->data->action->actionData->nofollow) ? 'nofollow' : '';
         $relType = isset($this->data->action->actionData->rel_type) ? $this->data->action->actionData->rel_type : $actionNoFollow;
         $actionNewWindow  = isset($this->data->action->actionData->new_window) ? $this->data->action->actionData->new_window : '';
         $actionStepNum  = isset($this->data->action->actionData->step) ? (Int)$this->data->action->actionData->step  - 1 : '';
         $actionCouponCode  = isset($this->data->action->actionData->coupon) ? $this->data->action->actionData->coupon : '';
         $actionCouponAfter  = isset($this->data->action->actionData->onCouponApply) ? $this->data->action->actionData->onCouponApply : '';
         $actionCouponAfterStep  = !empty($this->data->action->actionData->couponStep) ? (Int)$this->data->action->actionData->couponStep - 1 : '';
         $closeAfterClick = ($actionType === 'dynamic' ||$actionType === 'url' || $actionType === 'call'|| $actionType === 'javascript') && !empty($this->data->action->actionData->closeAfter) ? true : false;
         
         $track = isset($this->data->action->track) ? $this->data->action->track : false;
         $eventCategory = isset($this->data->action->trackData->eventCategory) ? $this->data->action->trackData->eventCategory : 'popup';
         $eventAction = isset($this->data->action->trackData->eventAction) ? $this->data->action->trackData->eventAction : 'click';
         $eventLabel = isset($this->data->action->trackData->eventLabel) ? $this->data->action->trackData->eventLabel : '';
         //url, dynamic, close, call, step, javascript
         $actionInlineTrack = $track ? 'brave_send_ga_event(\''.$eventCategory.'\', \''.$eventAction.'\', \''.$eventLabel.'\');':'';
         $goalAction = $this->goalItem ? 'brave_complete_goal('.$this->popupID.', \'click\');"':'';
         $closeAfter = $closeAfterClick ? 'brave_close_popup(\''.$this->popupID.'\', \''.$this->stepIndex.'\'); ':'';
         $actionJS = $actionType === 'javascript' && isset($this->data->action->actionData->javascript) ? 'onclick="'.$this->data->action->actionData->javascript.' '.$actionInlineTrack.' '.$goalAction.$closeAfter.'"': '';

         //Dynamic Data
         $dynamicAttrs = ''; $dynamicClasses = '';
         if(isset($this->data->action->actionData->dynamicURL)){
            $dynamicActionLink  = bravepopup_dynamicLink_data($this->data->action->actionData, $this->dynamicData, $this->data->id);
            //error_log(wp_json_encode($dynamicActionLink));
            if(isset($dynamicActionLink->link)){   $actionURL  =  $dynamicActionLink->link;  }
            if(!empty($dynamicActionLink->attr)){   $dynamicAttrs = $dynamicActionLink->attr;    }
            if(!empty($dynamicActionLink->classes)){   $dynamicClasses = $dynamicActionLink->classes;    }
         }

         
         $actionLink = ($actionType === 'dynamic' ||$actionType === 'url') && $actionURL ? 'onclick="'.$goalAction.$closeAfter.'" href="'.do_shortcode($actionURL).'" '.($actionNewWindow ? 'target="_blank"' : '').' '.($relType ? 'rel="'.$relType.'"' : '').'':'';
         $actionCall = ($actionType === 'call') && $actionPhone ? 'onclick="'.$goalAction.$closeAfter.'" href="tel:'.$actionPhone.'"':'';
         $actionStep = $actionType === 'step' && $actionStepNum >=0 ? 'onclick="brave_action_step('.$this->popupID.', '.$this->stepIndex.', '.$actionStepNum.'); '.$actionInlineTrack.' '.$goalAction.'"':'';
         $actionClose = $actionType === 'close' ? 'onclick="brave_close_popup(\''.$this->popupID.'\', \''.$this->stepIndex.'\'); '.$actionInlineTrack.' '.$goalAction.'"':'';
         $actionCouponStep = $actionType === 'coupon' && $actionCouponAfterStep ? 'brave_action_step('.$this->popupID.', '.$this->stepIndex.', '.$actionCouponAfterStep.');':'';
         $actionCoupon = $actionType === 'coupon' && $actionCouponCode ? 'onclick="brave_apply_woo_coupon(\''.$actionCouponCode.'\', '.$this->popupID.', \''.$this->data->id.'\', \''.$actionCouponAfter.'\'); '.$actionInlineTrack.' '.$goalAction.''.$actionCouponStep.'"':'';
         $hasClickAction = ($actionType === 'dynamic' || $actionType === 'url' || $actionType === 'call' || $actionType === 'step' || $actionType === 'close' || $actionType === 'javascript' || $actionType === 'coupon') ? 'brave_element--has-click-action' : '';
         $customClass = !empty($this->data->classes) ? ' '. str_replace(',',' ',$this->data->classes) : '';


         $loadingIconColor = isset($this->data->fontColor) && isset($this->data->fontColor->rgb) ? 'rgb('.$this->data->fontColor->rgb.')' : '#fff';
         $loadingIcon = $actionType === 'coupon' && $actionCouponCode ? '<span id="brave_button_loading_'.$this->data->id.'" class="brave_button_loading">'.bravepop_renderIcon('reload', $loadingIconColor).'</span>' : '';

         $iconHTML = '';
         $iconRight = !empty($this->data->iconRight) ? true : false;
         $iconColor = isset($this->data->iconColor->rgb) ? 'rgba('.$this->data->iconColor->rgb.', '.(isset($this->data->iconColor->opacity) ? $this->data->iconColor->opacity : 1).')' : '';
         if(isset($this->data->icon->body)){
            $iconHTML = '<span class="brave_element-icon '.($iconRight ? 'brave_element-icon--right' : '').'"><svg viewBox="0 0 '.$this->data->icon->width.' '.$this->data->icon->height.'" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">'.str_replace('currentColor', $iconColor ,html_entity_decode($this->data->icon->body)).'</svg></span>';
         }

         if((isset($this->data->action->actionData->dynamicURL->data) && ($this->data->action->actionData->dynamicURL->data === 'cart' || $this->data->action->actionData->dynamicURL->data === 'custom_cart_link' || $this->data->action->actionData->dynamicURL->data === 'current_cart_link'))){
            $fontSize = isset($this->data->fontSize) ? $this->data->fontSize.'px;' : 'inherit';
            $fontColorRGB = isset($this->data->fontColor) && isset($this->data->fontColor->rgb) ? 'rgb('.$this->data->fontColor->rgb.')' :'rgb(0,0,0)';
            $iconHTML .= '<span class="brave_element-cart_icon" style="width: '.$fontSize.'">'.bravepop_renderIcon('check', $fontColorRGB).'</span>';
         }

         return '<div id="brave_element-'.$this->data->id.'" class="brave_element brave_element--button '.$hoverClass .' '.$hasClickAction.$customClass.'">
                  <div class="brave_element__wrap">
                     <div class="brave_element__inner">
                        <div class="brave_element__styler">
                           <a class="brave_element__inner_link '.$dynamicClasses.'" '.$actionLink.' '.$actionDownload.' '.$actionCall.' '.$actionStep . $actionClose. $actionJS. $actionCoupon. $dynamicAttrs.'>
                              '.(!$iconRight ? $iconHTML:'').'<div class="brave_element__button_text">'.$buttonText.$loadingIcon.'</div>'.($iconRight ? $iconHTML:'').'
                           </a>
                        </div>
                     </div>
                  </div>
               </div>';
      }


   }


}
?>