<?php

if ( ! class_exists( 'BravePop_Element_Text' ) ) {
   

   class BravePop_Element_Text {

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
         $this->dynamicData = $dynamicData ? $dynamicData : new stdClass() ;
      }

      
      public function render_css() { 


         $textAlign = isset($this->data->textAlign) ?  'text-align: '.$this->data->textAlign.';' : '';
         $fontSize = isset($this->data->fontSize) ?   'font-size: '.$this->data->fontSize.'px;' : '';
         $fontFamily = isset($this->data->fontFamily) && ($this->data->fontFamily !== 'None') ?  'font-family: \''.$this->data->fontFamily.'\';' : '';
         $lineHeight = isset($this->data->lineHeight) ?  'line-height: '.$this->data->lineHeight.'em;' : 'line-height: 1.7em;';
         $letterSpacing = isset($this->data->letterSpacing) ?  'letter-spacing: '.$this->data->letterSpacing.'px;' : '';
         $fontBold = isset($this->data->fontBold) && $this->data->fontBold === true ?  'font-weight: bold;' : '';
         $fontBold = !empty($this->data->fontVariation) && $this->data->fontVariation !== 'regular' ?  'font-weight: '.str_replace('italic','', $this->data->fontVariation).';' : $fontBold;
         $fontItalic = ((isset($this->data->fontItalic) && $this->data->fontItalic === true) || (isset($this->data->fontVariation) && strpos($this->data->fontVariation, 'italic') !== false)) ? 'font-style: italic;' : '';
         $fontUnderline = isset($this->data->fontUnderline) && $this->data->fontUnderline === true ?  'text-decoration: underline;' : '';
         $fontStrike = isset($this->data->fontStrike) && $this->data->fontStrike === true ?  'text-decoration: line-through;' : '';
         $fontUppercase = isset($this->data->fontUppercase) && $this->data->fontUppercase === true ?  'text-transform: uppercase;' : '';

         $fontColorRGB = isset($this->data->fontColor) && isset($this->data->fontColor->rgb) ? $this->data->fontColor->rgb :'0,0,0';
         $fontColorOpacity = isset($this->data->fontColor) && isset($this->data->fontColor->opacity) ? $this->data->fontColor->opacity :'1';
         $fontColor = 'color: rgba('.$fontColorRGB.', '.$fontColorOpacity.');';
         


         $elementInnerStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__text_inner{
            '.$textAlign .  $fontSize .  $fontFamily .  $lineHeight . $letterSpacing . $fontBold . $fontUnderline . $fontStrike . $fontItalic . $fontUppercase . $fontColor . 
         '}';

         return  $elementInnerStyle;

      }

      public function dynamicText(){
         $dnmcType = isset($this->data->dynamicData->type) ? $this->data->dynamicData->type : '';
         $dnmcPostType = isset($this->data->dynamicData->post) ? $this->data->dynamicData->post : '';
         $dnmcDataType = isset($this->data->dynamicData->data) ? $this->data->dynamicData->data : '';
         $dnmcIndex = isset($this->data->dynamicData->index) ? $this->data->dynamicData->index : '';
         $dynamicText = '';

         //error_log('dnmcType: '.$dnmcType. ' dnmcPostType: '.$dnmcPostType. ' dnmcDataType: '.$dnmcDataType. ' dnmcIndex: '.$dnmcIndex);
         if($dnmcPostType === 'date'){
            if(!empty($this->dynamicData['date']->$dnmcType)){
               $dynamicText = $this->dynamicData['date']->$dnmcType;
            }
         }else if($dnmcPostType === 'country'){
            global $bravepop_global;
            if(!empty($bravepop_global['user_country'])){
               $dynamicText = $bravepop_global['user_country'];
            }
         }else if($dnmcPostType === 'quiz'){
            if($dnmcDataType){
               $total =  $dnmcType === 'scoretotal' ? 'data-total="true"' : 'false';
               $dummyScore = $dnmcType === 'scoretotal' ? '0/0' :  '0';
               $dynamicText = '<div class="bravepop_quizScore bravepop_quizScore-'.$dnmcDataType.'" data-form="'.$dnmcDataType.'" '.$total.'>'.$dummyScore.'</div>';
            }
         }else if($dnmcType === 'general'){
            if(!empty($this->dynamicData['general']->$dnmcPostType->$dnmcDataType)){
               $dynamicText = $this->dynamicData['general']->$dnmcPostType->$dnmcDataType;
            }
         }else{
            if(!empty($this->dynamicData[$dnmcPostType]->$dnmcType)){
               foreach ($this->dynamicData[$dnmcPostType]->$dnmcType as $item) {
                  if(($item->index === $dnmcIndex) && !empty($item->$dnmcDataType)){
                     $dynamicText = $item->$dnmcDataType;
                  }
               }
            }
         }

         if($dnmcType){
            
         }

         return $dynamicText;
      }

      public function clickable_html( ) { 
         $clickable = isset($this->data->clickable) ? $this->data->clickable : false;
         $actionType = isset($this->data->action->type) ? $this->data->action->type : 'none';
         $track = isset($this->data->action->track) ? $this->data->action->track : false;
         $eventCategory = isset($this->data->action->trackData->eventCategory) ? $this->data->action->trackData->eventCategory : 'popup';
         $eventAction = isset($this->data->action->trackData->eventAction) ? $this->data->action->trackData->eventAction : 'click';
         $eventLabel = isset($this->data->action->trackData->eventLabel) ? $this->data->action->trackData->eventLabel : '';
         $actionTrack = ($actionType !== 'step' || $actionType !== 'close') && $track && $clickable ? ' onclick="brave_send_ga_event(\''.$eventCategory.'\', \''.$eventAction.'\', \''.$eventLabel.'\');"':'';
         $actionInlineTrack = ($actionType === 'step' || $actionType === 'close') && $track && $clickable ? ' brave_send_ga_event(\''.$eventCategory.'\', \''.$eventAction.'\', \''.$eventLabel.'\');':'';
         $goalAction = $this->goalItem ? 'brave_complete_goal('.$this->popupID.', \'click\');"':'';
         $actionURL  = isset($this->data->action->actionData->url) ? $this->data->action->actionData->url : '';
         $actionPhone  = !empty($this->data->action->actionData->phone) ? $this->data->action->actionData->phone : '';
         $actionDownload = !empty($this->data->action->actionData->download) ? 'download': '';
         $actionNoFollow  = !empty($this->data->action->actionData->nofollow) ? 'nofollow' : '';
         $relType = isset($this->data->action->actionData->rel_type) ? $this->data->action->actionData->rel_type : $actionNoFollow;
         $actionNewWindow  = isset($this->data->action->actionData->new_window) ? $this->data->action->actionData->new_window : '';
         $actionStepNum  = isset($this->data->action->actionData->step) ? (Int)$this->data->action->actionData->step  - 1 : '';
         $closeAfterClick = ($actionType === 'dynamic' ||$actionType === 'url' || $actionType === 'call'|| $actionType === 'javascript') && !empty($this->data->action->actionData->closeAfter) ? true : false;
         $closeAfter = $closeAfterClick ? 'brave_close_popup(\''.$this->popupID.'\', \''.$this->stepIndex.'\'); ':'';
         

         $actionJS = $actionType === 'javascript' && isset($this->data->action->actionData->javascript) ? 'onclick="'.$this->data->action->actionData->javascript.$closeAfter.' '.$actionInlineTrack.' '.$goalAction.'"': '';
         if(isset($this->data->action->actionData->dynamicURL)){
            $dynamicURL  = bravepopup_dynamicLink_data($this->data->action->actionData, $this->dynamicData, $this->data->id);
            if(isset($dynamicURL->link)){   $actionURL  =  $dynamicURL->link; }
         }
         $actionLink = $clickable && ($actionType === 'url' || $actionType === 'dynamic') && $actionURL ? 'onclick="'.$goalAction.$closeAfter.'" href="'.do_shortcode($actionURL).'" '.($actionNewWindow ? 'target="_blank"' : '').' '.($relType ? 'rel="'.$relType.'"' : '').'':'';
         $actionCall = ($actionType === 'call') && $actionPhone ? 'onclick="'.$goalAction.$closeAfter.'" href="tel:'.$actionPhone.'"':'';
         $actionStep = $clickable && $actionType === 'step' && $actionStepNum >=0 ? 'onclick="brave_action_step('.$this->popupID.', '.$this->stepIndex.', '.$actionStepNum.'); '.$actionInlineTrack.' '.$goalAction.'"':'';
         $actionClose = $clickable && $actionType === 'close' ? 'onclick="brave_close_popup(\''.$this->popupID.'\', \''.$this->stepIndex.'\'); '.$actionInlineTrack.' '.$goalAction.'"':'';
         $actionCopy = $clickable && $actionType === 'copy' ? 'onclick="brave_copy_to_clipboard(\''.$this->data->id.'\', \''.__('Copied to Clipboard','bravepop').'\', \''.(!empty($this->data->copyTextPos) ? $this->data->copyTextPos : 'bottom').'\'); '.$actionInlineTrack.' '.$goalAction.'"':'';
         
         $html = new stdClass();
         $html->start = '<a class="brave_element__inner_link" '.$actionLink.' '.$actionCall.' '.$actionDownload.' '.$actionStep . $actionClose. $actionTrack.$actionJS.$actionCopy.'>';
         $html->end = '</a>';

         return $html;
      }



      public function render( ) { 
         $content = isset($this->data->content) ? html_entity_decode($this->data->content) : '';      
         $dynamiClass = '';
         if(!empty($this->data->dynamic) && !empty($this->data->dynamicData->type) && $this->dynamicData){
            $content = $this->dynamicText();
            $dynamiClass = ' brave_element--text_dynamic';
         }
         if(function_exists('bravepop_prepare_text_content')){
            $content = bravepop_prepare_text_content($content); 
            if(strpos($content, "{{cookie-") !== false){ $dynamiClass .= ' brave_element--text_hasCookie';}
         }
         if(!empty($this->data->advanced) && $this->goalItem){
            $content = str_replace('href=', 'onclick="brave_complete_goal('.$this->popupID.', \'click\');" href=', $content);
         }
         $content = apply_filters( 'brave_text_element_content', $content, $this->data->id );
         $content = wp_kses_post($content);
         $content = do_shortcode($content);
         $advClass = !empty($this->data->advanced) ? ' brave_element--text_advanced' : '';
         $customClass = !empty($this->data->classes) ? ' '. str_replace(',',' ',$this->data->classes) : '';

         $clickable = !empty($this->data->clickable) ? $this->data->clickable : false;
         $playCustomAnim = !empty($this->data->clickable) && !empty($this->data->action->play_animation) && isset($this->data->action->custom_animation) ? 'onclick="brave_animate_popup(null, '.$this->popupID.', '.$this->stepIndex.', \''.$this->data->action->custom_animation.'\');"':'';
         $clickableHTML = $this->clickable_html();
         $clickStart = $clickable && isset($clickableHTML->start) ? $clickableHTML->start : '';
         $clickEnd = $clickable && isset($clickableHTML->end) ? $clickableHTML->end : '';
         $scrollbar = !empty($this->data->scrollbar) ? 'brave_element__wrap--has-scrollbar' : '';
         $hoverAnimClass = !empty($this->data->action->hoverAnimation) ? ' element_hover_animation element_hover_animation-'.$this->data->action->hoverAnimation : '';
         $copyToClipbpardHTML = !empty($this->data->clickable) && !empty($this->data->action->type) && $this->data->action->type === 'copy' ? '<input id="bravepopup_text_copy-'.$this->data->id.'" value="'.$content.'" style="height:0; opacity:0;position:absolute" />' : '';

         return '<div id="brave_element-'.$this->data->id.'" class="brave_element brave_element--text '.($clickable ? 'brave_element--has-click-action' : ''). $dynamiClass.$advClass.$customClass. '">
                  <div class="brave_element__wrap '.$scrollbar.'">
                     <div class="brave_element__styler '.$hoverAnimClass.'">
                        <div class="brave_element__inner" '.$playCustomAnim.'>
                           '.$clickStart.'
                              <div class="brave_element__text_inner">'.wp_staticize_emoji($content).'</div>
                              '.$copyToClipbpardHTML.'
                           '.$clickEnd.'
                        </div>
                     </div>
                  </div>
               </div>';
      }


   }


}
?>