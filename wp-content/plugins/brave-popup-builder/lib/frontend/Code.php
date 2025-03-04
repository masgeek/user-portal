<?php

if ( ! class_exists( 'BravePop_Element_Code' ) ) {
   

   class BravePop_Element_Code {

      protected $data;
      protected $popupID;
      protected $stepIndex;
      protected $elementIndex;
      protected $goalItem;
      protected $code;

      function __construct($data=null, $popupID=null, $stepIndex=0, $elementIndex=0, $device='desktop', $goalItem=false) {
         $this->data = $data;
         $this->popupID = $popupID;
         $this->stepIndex =  $stepIndex;
         $this->elementIndex = $elementIndex;
         $this->goalItem = $goalItem;

         $default_attribs = array( 'id' => true, 'class' => true, 'title' => true, 'style' => true, 'data-*' => true );
         $allowed_tags = array(
            'div' => $default_attribs,
            'p' => $default_attribs,
            'span' => $default_attribs,
            'strong' => $default_attribs,
            'b' => $default_attribs,
            'ul' => $default_attribs,
            'ol' => $default_attribs,
            'li' => $default_attribs,
            'h1' => $default_attribs,
            'h2' => $default_attribs,
            'h3' => $default_attribs,
            'h4' => $default_attribs,
            'h5' => $default_attribs,
            'h6' => $default_attribs,
            'code' => $default_attribs,
            'a' => array_merge(array('href' => true, 'target' => true, 'rel'=> true,), $default_attribs),
            'img' => array_merge(array( 'src' => true, 'alt' => true, 'width' => true , 'height' => true ), $default_attribs),
            'iframe' => array_merge(array('src' => true, 'width'=> true, 'height'=> true,'frameBorder'=> true, 'allowfullscreen' => true), $default_attribs),
         );
         
         $this->code = isset($this->data->code) ?  wp_kses( $this->data->code, $allowed_tags) : '';
      }

      
      public function render_css() { 
         return '';
      }


      public function render( ) { 

         $customClass = !empty($this->data->classes) ? ' '. str_replace(',',' ',$this->data->classes) : '';

         return '<div id="brave_element-'.$this->data->id.'" class="brave_element brave_element--code'.$customClass.'">
                  <div class="brave_element__wrap">
                     <div class="brave_element__styler">
                        <div class="brave_element__inner">
                           <div class="brave_element__code '.($this->goalItem ? 'brave_element__code--goaled':'').'">
                              '.do_shortcode($this->code).'
                           </div>
                        </div>
                     </div>
                  </div>
               </div>';
      }


   }


}
?>