<?php

if ( ! class_exists( 'BravePop_Element_Single' ) ) {
   

   class BravePop_Element_Single {

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

         $roundness = isset($this->data->roundness) ?  'border-radius: '.$this->data->roundness.'px;' : '';
         $shadowStr = isset($this->data->shadow) && $this->data->shadow > 30 ? 0.2 : 0.12;
         $shadow = isset($this->data->shadow) ?  'box-shadow: 0 0 '.$this->data->shadow.'px rgba(0, 0, 0, '.$shadowStr.');' : '';
         $textAlign = isset($this->data->textAlign) ?  'text-align: '.$this->data->textAlign.';' : '';
         $fontFamily = isset($this->data->fontFamily) && $this->data->fontFamily !== 'None' ?  'font-family: '.$this->data->fontFamily.';' : '';
         $fontBold = !empty($this->data->fontVariation) && $this->data->fontVariation !== 'regular' ?  'font-weight: '.str_replace('italic','', $this->data->fontVariation).';' : '';
         $fontItalic = ( (!empty($this->data->fontVariation) && strpos($this->data->fontVariation, 'italic') !== false)) ? 'font-style: italic;' : '';
         $backgroundColorRGB = isset($this->data->backgroundColor) && isset($this->data->backgroundColor->rgb) ? $this->data->backgroundColor->rgb :'255,255,255';
         $backgroundColorOpacity = isset($this->data->backgroundColor) && isset($this->data->backgroundColor->opacity) ? $this->data->backgroundColor->opacity :'0';
         $backgroundColor = 'background-color: rgba('.$backgroundColorRGB.', '.$backgroundColorOpacity.');';
         
         $infoColorRGB = isset($this->data->infoColor) && isset($this->data->infoColor->rgb) ? $this->data->infoColor->rgb :'153,153,153';
         $infoColorOpacity = isset($this->data->infoColor) && isset($this->data->infoColor->opacity) ? $this->data->infoColor->opacity :'1';
         $infoColor = 'color: rgba('.$infoColorRGB.', '.$infoColorOpacity.');';

         $titleSize = isset($this->data->titleSize) ?   'font-size: '.$this->data->titleSize.'px;' : '';
         $titleFontFamily = isset($this->data->titlefontFamily) && $this->data->titlefontFamily !== 'None' ?  'font-family: '.$this->data->titlefontFamily.';' : '';
         $titlefontBold = isset($this->data->titlefontVariation) && $this->data->titlefontVariation !== 'regular' ?  'font-weight: '.str_replace('italic','', $this->data->titlefontVariation).';' : '';
         $titlefontItalic = ( (isset($this->data->titlefontVariation) && strpos($this->data->titlefontVariation, 'italic') !== false)) ? 'font-style: italic;' : '';
         $titleColorRGB = isset($this->data->titleColor) && isset($this->data->titleColor->rgb) ? $this->data->titleColor->rgb :'109, 120, 216';
         $titleColorOpacity = isset($this->data->titleColor) && isset($this->data->titleColor->opacity) ? $this->data->titleColor->opacity :'1';
         $titleColor = 'color: rgba('.$titleColorRGB.', '.$titleColorOpacity.');';

         $contentFontSize = isset($this->data->contentSize) ?   'font-size: '.$this->data->contentSize.'px;' : '';
         $contentColorRGB = isset($this->data->contentColor) && isset($this->data->contentColor->rgb) ? $this->data->contentColor->rgb :'153,153,153';
         $contentColorOpacity = isset($this->data->contentColor) && isset($this->data->contentColor->opacity) ? $this->data->contentColor->opacity :'1';
         $contentColor = 'color: rgba('.$contentColorRGB.', '.$contentColorOpacity.');';
 
      
         $elementInnerStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__styler{
            '.$textAlign .  $contentFontSize .  $fontFamily . $fontBold . $fontItalic  .  $contentColor . $shadow .
         '}';
         $elementWrapStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_wpSingle__wrap{ '.$height . $backgroundColor. $roundness .'}';
         $elementTitleStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_post__title h2{'.$titleSize . $titleFontFamily . $titlefontBold . $titlefontItalic . $titleColor . '}';
         $elementMetaStyle = $infoColor ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_post__meta{'. $infoColor . '}' : '';


         return  $elementInnerStyle .$elementWrapStyle . $elementTitleStyle .$elementMetaStyle;

      }

      public function renderPost(){

         $postType = isset($this->data->postType) ? $this->data->postType : 'post';
         $fistPost = get_posts("post_type='.$postType.'&numberposts=1");
         $fistPostID = $fistPost[0]->ID;
         $singleID = isset($this->data->singleID) ? $this->data->singleID : $fistPostID;
         $displayTitle = isset($this->data->title) && $this->data->title === false ? false : true;
         $displayDate = isset($this->data->date) ? $this->data->date : true;
         $displayCat = isset($this->data->category) ? $this->data->category : true;
         $displayMeta = ($postType === 'post' && ($displayDate || $displayCat)) || ($postType === 'page' && $displayDate) ? true : false;

         $the_query = new WP_Query( array( 'post_type' => $postType , 'post__in' => array( $singleID ) ) );
         $postHTML = '';
         // The Loop
         if ( $the_query->have_posts() ) {
            $postHTML .=  '<div class="brave_post__content_wrap">';
                  while ( $the_query->have_posts() ) {
                     $the_query->the_post();
                     $postHTML .=  $displayTitle ? '<div class="brave_post__title"><h2>' . get_the_title() . '</h2></div>' : '';
                     $postHTML .=  '<div class="brave_post__content">';
                        if($displayMeta){
                           $postHTML .=  '<div class="brave_post__meta">';
                              $postHTML .=  $displayDate ? '<div class="brave_post__content__date">'.get_the_time( get_option('date_format') ).'</div>' : '';
                              
                              if($displayCat){
                                 //error_log(wp_json_encode(get_the_category()));
                                 $postHTML .=  '<div class="brave_post__content__category">';
                                    $cats = get_the_category();
                                    foreach ( $cats as $key=>$category ) {
                                       $comma = (count($cats) - 1) !== $key ? ', ' : '';
                                       $postHTML .=  '<a href="'.get_category_link($category->term_id).'">'.$category->name.'</a></li>'.$comma;
                                    }
                                $postHTML .=  '</div>';
                              }
                    
                           $postHTML .=  '</div>';
                        }
                           
                     $postHTML .=  '<div class="brave_post__content__content">'.get_the_content().'</div>';
                     $postHTML .=  '</div>';
                  }
            $postHTML .=  '</div>';
         }
         wp_reset_postdata();

         return $postHTML;         
      }


      public function render( ) { 
         $singleLayout  = isset($this->data->layout) ? $this->data->layout : 1; 
         $customClass = !empty($this->data->classes) ? ' '. str_replace(',',' ',$this->data->classes) : '';
         
         return '<div id="brave_element-'.$this->data->id.'" class="brave_element brave_element--wpSingle '.$customClass.'">
                  <div class="brave_element__wrap">
                     <div class="brave_element__styler">
                        <div class="brave_element__inner">
                           <div id="wpSingle_'.$this->data->id.'" class="`brave_wpSingle brave_wpSingle--'.$singleLayout.'">
                              <div class="brave_wpSingle__wrap">
                                 '.$this->renderPost().'
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>';
      }


   }


}
?>