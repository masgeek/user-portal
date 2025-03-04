<?php

if ( ! class_exists( 'BravePop_Element_Shape' ) ) {
   

   class BravePop_Element_Shape {

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
         $elementBlur = !empty($this->data->blur) && !empty($this->data->blurSize) ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__styler{filter: blur('.$this->data->blurSize.'px)}' : '';
         $elementSize = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element_shape-icon{ font-size:'.($this->data->width - 10).'px;}';
         $elementHoverStyle = '';
         if(isset($this->data->shapeType) && $this->data->shapeType === 'icon' && isset($this->data->hoverAnimation) && isset($this->data->hoverColor) && $this->data->hoverAnimation === 'color' ){
            $hoverBgRgb = isset($this->data->hoverColor) && isset($this->data->hoverColor->rgb) ? $this->data->hoverColor->rgb :'0,0,0';
            $hoverBgOpacity = isset($this->data->hoverColor) && isset($this->data->hoverColor->opacity) ? $this->data->hoverColor->opacity :'1';
            $hoverBg = 'fill: rgba('.$hoverBgRgb.', '.$hoverBgOpacity.');';
            $elementHoverStyle = ($hoverBg) ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__styler:hover svg path{ '.$hoverBg. '}' : '';
         }

         return  $elementSize.$elementBlur.$elementHoverStyle;
      }


      public function clickable_html( ) { 
         $clickable = isset($this->data->clickable) ? $this->data->clickable : false;
         $actionType = isset($this->data->action->type) ? $this->data->action->type : 'none';
         $track = isset($this->data->action->track) ? $this->data->action->track : false;
         $eventCategory = isset($this->data->action->trackData->eventCategory) ? $this->data->action->trackData->eventCategory : 'popup';
         $eventAction = isset($this->data->action->trackData->eventAction) ? $this->data->action->trackData->eventAction : 'click';
         $eventLabel = isset($this->data->action->trackData->eventLabel) ? $this->data->action->trackData->eventLabel : '';
         $customAnim = !empty($this->data->action->play_animation) && isset($this->data->action->custom_animation) ? 'brave_animate_popup(null, '.$this->popupID.', '.$this->stepIndex.', \''.$this->data->action->custom_animation.'\');':'';
         $actionTrack = ($actionType !== 'step' || $actionType !== 'close') && $track && $clickable ? ' onclick="brave_send_ga_event(\''.$eventCategory.'\', \''.$eventAction.'\', \''.$eventLabel.'\');'.$customAnim.'"':'';
         $actionInlineTrack = ($actionType === 'step' || $actionType === 'close') && $track && $clickable ? ' brave_send_ga_event(\''.$eventCategory.'\', \''.$eventAction.'\', \''.$eventLabel.'\');'.$customAnim.'':'';
         $goalAction = $this->goalItem ? 'brave_complete_goal('.$this->popupID.', \'click\');':'';
         $closeAfterClick = ($actionType === 'dynamic' ||$actionType === 'url' || $actionType === 'call'|| $actionType === 'javascript') && !empty($this->data->action->actionData->closeAfter) ? true : false;
         $closeAfter = $closeAfterClick ? 'brave_close_popup(\''.$this->popupID.'\', \''.$this->stepIndex.'\'); ':'';
         
         $actionJS = $actionType === 'javascript' && isset($this->data->action->actionData->javascript) ? 'onclick="'.$this->data->action->actionData->javascript.$closeAfter.' '.$actionInlineTrack.' '.$goalAction.''.$customAnim.'"': '';
         $actionURL  = isset($this->data->action->actionData->url) ? $this->data->action->actionData->url : '';
         $actionPhone  = !empty($this->data->action->actionData->phone) ? $this->data->action->actionData->phone : '';
         $actionDownload = !empty($this->data->action->actionData->download) ? 'download': '';
         $actionNewWindow  = isset($this->data->action->actionData->new_window) ? $this->data->action->actionData->new_window : '';
         $actionNoFollow  = !empty($this->data->action->actionData->nofollow) ? 'nofollow' : '';
         $relType = isset($this->data->action->actionData->rel_type) ? $this->data->action->actionData->rel_type : $actionNoFollow;
         $actionStepNum  = isset($this->data->action->actionData->step) ? (Int)$this->data->action->actionData->step  - 1 : false;
         if(isset($this->data->action->actionData->dynamicURL)){
            $dynamicURL  = bravepopup_dynamicLink_data($this->data->action->actionData, $this->dynamicData, $this->data->id);
            if(isset($dynamicURL->link)){   $actionURL  =  $dynamicURL->link;   }
         }

         $actionLink = $clickable && ($actionType === 'url' || $actionType === 'dynamic') && $actionURL ? 'onclick="'.$goalAction.''.$customAnim.$closeAfter.'" href="'.$actionURL.'" '.($actionNewWindow ? 'target="_blank"' : '').' '.($relType ? 'rel="'.$relType.'"' : '').'':'';
         $actionCall = ($actionType === 'call') && $actionPhone ? 'onclick="'.$goalAction.$closeAfter.'" href="tel:'.$actionPhone.'"':'';
         $actionStep = $clickable && $actionType === 'step' && $actionStepNum >=0 ? 'onclick="brave_action_step('.$this->popupID.', '.$this->stepIndex.', '.$actionStepNum.'); '.$actionInlineTrack.' '.$goalAction.' "':'';
         $actionClose = $clickable && $actionType === 'close' ? 'onclick="brave_close_popup(\''.$this->popupID.'\', \''.$this->stepIndex.'\'); '.$actionInlineTrack.' '.$goalAction.'"':'';
         $noActionAnim = $customAnim && $clickable && $actionType === 'none' ? 'onclick="'.$customAnim.'"' :'';

         $html = new stdClass();
         $html->start = '<a class="brave_element__inner_link" '.$actionLink.' '.$actionCall.' '.$actionDownload.' '.$actionStep . $actionClose. $actionTrack. $actionJS.$noActionAnim.'>';
         $html->end = '</a>';

         return $html;
      }

      public function render( ) { 

         $shape = isset($this->data->shape) ? $this->data->shape : 'square';
         $shapeType =  isset($this->data->shapeType) ? $this->data->shapeType : 'shape';
         $icon =  isset($this->data->icon) ? $this->data->icon : new stdClass();
         $border = new stdClass(); $shadow = new stdClass(); $shapeData  = new stdClass();
         $fillImage =  isset($this->data->fillImage) ? $this->data->fillImage : null;

         $fillColorRGB = isset($this->data->fillColor) && isset($this->data->fillColor->rgb) ? $this->data->fillColor->rgb :'0,0,0';
         $fillColorOpacity = isset($this->data->fillColor) && isset($this->data->fillColor->opacity) ? $this->data->fillColor->opacity :'1';
         $shapeData->fillColor =  'rgba('.$fillColorRGB.', '.$fillColorOpacity.')';
         $shapeData->width = isset($this->data->width) ? $this->data->width.'px' : '100px';
         $shapeData->height = isset($this->data->height) ? $this->data->height.'px' : '100px';
         if(!empty($this->data->border)){
            $border->size = isset($this->data->borderSize) ? $this->data->borderSize : null;
            $border->color =  isset($this->data->borderColor) ? $this->data->borderColor : null;
         }
         if(!empty($this->data->shadow)){
            $shadow->size = isset($this->data->shadowSize) ? $this->data->shadowSize : null;
            $shadow->color = isset($this->data->shadowColor) ? $this->data->shadowColor : null;
         }
         $clickable = isset($this->data->clickable) ? $this->data->clickable : false;
         $clickableHTML = $this->clickable_html();
         $clickStart = $clickable && isset($clickableHTML->start) ? $clickableHTML->start : '';
         $clickEnd = $clickable && isset($clickableHTML->end) ? $clickableHTML->end : '';
         $iconHTML = '';
         if($shapeType === 'icon' && isset($this->data->icon->body)){
            $iconHTML = '<div class="brave_element_shape-icon"><svg viewBox="0 0 '.$icon->width.' '.$icon->height.'" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">'.str_replace('currentColor', $shapeData->fillColor , html_entity_decode($icon->body)).'</svg></div>';
         }
         if($shapeType === 'icon' && !isset($this->data->icon->body)){
            $iconHTML = '<div class="brave_element_shape-icon"><svg viewBox="0 0 576 512" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path fill="'.$shapeData->fillColor.'" d="M485.5 0L576 160H474.9L405.7 0h79.8zm-128 0l69.2 160H149.3L218.5 0h139zm-267 0h79.8l-69.2 160H0L90.5 0zM0 192h100.7l123 251.7c1.5 3.1-2.7 5.9-5 3.3L0 192zm148.2 0h279.6l-137 318.2c-1 2.4-4.5 2.4-5.5 0L148.2 192zm204.1 251.7l123-251.7H576L357.3 446.9c-2.3 2.7-6.5-.1-5-3.2z" /></svg></div>';
         }
         $hoverAnimClass = !empty($this->data->hoverAnimation) ? ' element_hover_animation element_hover_animation-'.$this->data->hoverAnimation : '';
         $tooltip = !empty($this->data->tooltip) ? ' onmouseenter="brave_tooltip_open(\''.$this->data->id.'\', \''.esc_attr($this->data->tooltip).'\', \''.(isset($this->data->tooltipPos) ? $this->data->tooltipPos : 'left').'\')" onmouseleave="brave_tooltip_close()"' : ''; 
         $tooltipClass = $tooltip ? ' brave_element--hasTooltip' : '';
         $customClass = !empty($this->data->classes) ? ' '. str_replace(',',' ',$this->data->classes) : '';

         return '<div id="brave_element-'.$this->data->id.'" class="brave_element brave_element--shape '.($clickable ? 'brave_element--has-click-action' : '').$tooltipClass.' '.$customClass.'" '.$tooltip.'>
                  <div class="brave_element__wrap">
                     <div class="brave_element__styler '.$hoverAnimClass.'">
                        <div class="brave_element__inner">
                              '.$clickStart.'
                                 '.($iconHTML ? $iconHTML : renderShape($this->data->id, $shape, $shapeData, $fillImage, $border, isset($this->data->shadow) && $this->data->shadow === true ? $shadow: false )).'
                              '.$clickEnd.'
                        </div>
                     </div>
                  </div>
               </div>';
      }


   }


}
?>