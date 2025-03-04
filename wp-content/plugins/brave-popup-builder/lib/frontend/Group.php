<?php

if ( ! class_exists( 'BravePop_Element_Group' ) ) {
   

   class BravePop_Element_Group {

      protected $data;
      protected $popupID;
      protected $stepIndex;
      protected $elementIndex;
      protected $childrenHTML;

      function __construct($data=null, $popupID=null, $stepIndex=0, $elementIndex=0, $childrenHTML='') {
         $this->data = $data;
         $this->popupID = $popupID;
         $this->stepIndex =  $stepIndex;
         $this->elementIndex = $elementIndex;
         $this->childrenHTML =  $childrenHTML;
      }

      
      public function render_css() { 
         //$elementBullet = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' ul li span{  '.$bulletColor .'}';
         return  '';

      }


      public function render( ) { 
         $hoverAnimClass = !empty($this->data->hoverAnimation) ? ' element_hover_animation element_hover_animation-'.$this->data->hoverAnimation : '';

         return '<div id="brave_element-'.$this->data->id.'" class="brave_element brave_element--group">
                  <div class="brave_element__wrap '.$hoverAnimClass.'">
                     <div class="brave_element__styler">
                        '.$this->childrenHTML.'
                     </div>
                  </div>
               </div>';
      }


   }


}
?>