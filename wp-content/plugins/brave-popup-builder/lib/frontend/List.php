<?php

if ( ! class_exists( 'BravePop_Element_List' ) ) {
   

   class BravePop_Element_List {

      protected $data;
      protected $popupID;
      protected $stepIndex;
      protected $elementIndex;

      function __construct($data=null, $popupID=null, $stepIndex=0, $elementIndex=0) {
         $this->data = $data;
         $this->popupID = $popupID;
         $this->stepIndex =  $stepIndex;
         $this->elementIndex = $elementIndex;
      }

      
      public function render_css() { 

         $bold = isset($this->data->bold) && $this->data->bold === true ?  'font-weight: bold;' : '';
         $textAlign = isset($this->data->textAlign) ?  'text-align: '.$this->data->textAlign.';' : '';
         $fontSize = isset($this->data->fontSize) ?   'font-size: '.$this->data->fontSize.'px;' : '';
         $fontFamily = isset($this->data->fontFamily) && $this->data->fontFamily !== 'None' ?  'font-family: '.$this->data->fontFamily.';' : '';
         $fontBold = !empty($this->data->fontVariation) && $this->data->fontVariation !== 'regular' ?  'font-weight: '.str_replace('italic','', $this->data->fontVariation).';' : $bold;
         $fontItalic = ( (!empty($this->data->fontVariation) && strpos($this->data->fontVariation, 'italic') !== false)) ? 'font-style: italic;' : '';
         $lineHeight = isset($this->data->lineHeight) ?  'line-height: '.$this->data->lineHeight.'px;' : '';


         $fontColor = bravepop_generate_style_props(isset($this->data->color) ? $this->data->color : '', 'color', '0, 0, 0', '1');
         $bulletColor = bravepop_generate_style_props(isset($this->data->bulletColor) ? $this->data->bulletColor : '', 'color', '0, 0, 0', '1');
         $iconColor = bravepop_generate_style_props(isset($this->data->bulletColor) ? $this->data->bulletColor : '', 'fill', '0, 0, 0', '1');
         $iconSize = isset($this->data->icon) && isset($this->data->fontSize) ? 'font-size: '.(($this->data->fontSize * 90)/100).'px' : '';


         $elementInnerStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__styler{
            '.$textAlign . $fontBold  . $fontItalic . $fontSize .  $fontFamily .  $lineHeight . $fontColor . 
         '}';
         $elementList = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' ul li{  '.$fontSize .  $fontFamily .'}';
         $elementBullet = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' ul li span{  '.$bulletColor .'}';
         $elementIconSize = isset($this->data->icon) ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element-icon{ '.$iconSize. '}' : '';


         return  $elementInnerStyle . $elementList . $elementBullet . $elementIconSize;

      }


      public function render( ) { 
         $list = isset($this->data->list) ? $this->data->list : array();
         $customClass = !empty($this->data->classes) ? ' '. str_replace(',',' ',$this->data->classes) : ''; 
         $listItems = '';
         $iconHTML = '';
         $iconColor = isset($this->data->bulletColor->rgb) ? 'rgba('.$this->data->bulletColor->rgb.', '.(isset($this->data->bulletColor->opacity) ? $this->data->bulletColor->opacity : 1).')' : '';
         if(isset($this->data->icon->body)){
            $iconHTML = '<span class="brave_element-icon"><svg viewBox="0 0 '.$this->data->icon->width.' '.$this->data->icon->height.'" width="1em" height="1em" preserveAspectRatio="xMidYMid meet" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">'.str_replace('currentColor', $iconColor ,html_entity_decode($this->data->icon->body)).'</svg></span>';
         }

         foreach ($list as $key => $listItem) {
            $bullet =  isset($this->data->bulletType) && $this->data->bulletType === 'numbered' ? '<span className="brave_list__bullet_number">'.($key+1).'.</span> ' : $iconHTML;
            $listItems .= isset($listItem->item) ? '<li id="'.$listItem->id.'__list__'.$key.'">'.$bullet. wp_kses_post($listItem->item).'</li>' : '';
         }

         return '<div id="brave_element-'.$this->data->id.'" class="brave_element brave_element--list '.$customClass.'">
                  <div class="brave_element__wrap">
                     <div class="brave_element__styler">
                        <div class="brave_element__inner">
                           <ul class="brave_element__list">
                              '.$listItems.'
                           </ul>
                        </div>
                     </div>
                  </div>
               </div>';
      }


   }


}
?>