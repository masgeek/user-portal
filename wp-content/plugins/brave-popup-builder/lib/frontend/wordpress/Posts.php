<?php

if ( ! class_exists( 'BravePop_Element_Posts' ) ) {
   

   class BravePop_Element_Posts {

      protected $data;
      protected $popupID;
      protected $stepIndex;
      protected $elementIndex;
      protected $goalItem;

      function __construct($data=null, $popupID=null, $stepIndex=0, $elementIndex=0, $device='desktop', $goalItem=false) {
         $this->data = $data;
         $this->popupID = $popupID;
         $this->stepIndex =  $stepIndex;
         $this->elementIndex = $elementIndex;
         $this->goalItem = $goalItem;
      }

      public function render_js() { ?>
         <script>
            <?php if(isset($this->data->autoSlide) && $this->data->autoSlide === true) { ?>
               document.addEventListener("DOMContentLoaded", function(event) {
                  setInterval("brave_autochange_slide('<?php print_r(esc_attr($this->data->id));?>')", <?php print_r(isset($this->data->slideDuration) ? absint($this->data->slideDuration) * 1000 : 2000) ; ?>);
               });
            <?php } ?>
         </script>
      <?php }
      
      public function render_css() { 

         //POSTS WIDTH
         $slide = isset($this->data->slide) ? $this->data->slide : false;
         $postType = isset($this->data->postType) ? $this->data->postType : 'multiple';
         $filterType = isset($this->data->filterType) ? $this->data->filterType : 'categories';
         $postCount = isset($this->data->postCount) ? $this->data->postCount : 3;
         $slidePosts = isset($this->data->slidePosts) ? $this->data->slidePosts : 1;
         $layout = isset($this->data->layout) ? $this->data->layout : 1;
         $customIds = isset($this->data->customIds) ? $this->data->customIds : array();
         if($postType==='multiple' && $filterType=== 'custom' && count($customIds) > 0){ $postCount = count($customIds); }

         $postWidth = 'width: '.((100 / $postCount) - 2).'%;'; 
         if($slide){ $postWidth = 'width: '.((100 / $slidePosts) - 2 ).'%;'; }else{ $postWidth = 'width: '.(100 - 2).'%;'; }

         $fontFamily = isset($this->data->fontFamily) && $this->data->fontFamily !== 'None' ?  'font-family: '.$this->data->fontFamily.';' : '';
         $fontBold = !empty($this->data->fontVariation) && $this->data->fontVariation !== 'regular' ?  'font-weight: '.str_replace('italic','', $this->data->fontVariation).';' : '';
         $fontItalic = ( (!empty($this->data->fontVariation) && strpos($this->data->fontVariation, 'italic') !== false)) ? 'font-style: italic;' : '';
         $titlefontFamily = isset($this->data->titlefontFamily) ?  'font-family: '.$this->data->titlefontFamily.';' : '';
         $titlefontBold = isset($this->data->titlefontVariation) && $this->data->titlefontVariation !== 'regular' ?  'font-weight: '.str_replace('italic','', $this->data->titlefontVariation).';' : '';
         $titlefontItalic = ( (isset($this->data->titlefontVariation) && strpos($this->data->titlefontVariation, 'italic') !== false)) ? 'font-style: italic;' : '';
         $fontSize = bravepop_generate_style_props(isset($this->data->contentSize) ? $this->data->contentSize : '', 'font-size');
         $imageHeight = isset($this->data->imageHeight) ?  'height: '.$this->data->imageHeight.'px;' : '';
         $fontColor = bravepop_generate_style_props(isset($this->data->fontColor) ? $this->data->fontColor : '', 'color', '0,0,0', '1');
         $backgroundColor = bravepop_generate_style_props(isset($this->data->backgroundColor) ? $this->data->backgroundColor : '', 'background-color', '255,255,255', '0');
         $btnTxtColor = bravepop_generate_style_props(isset($this->data->buttonTextColor) ? $this->data->buttonTextColor : '', 'color', '109,120,216', '1');
         $postTextColor = bravepop_generate_style_props(isset($this->data->contentColor) ? $this->data->contentColor : '', 'color', '153,153,153', '1');
         $postTitleColor = bravepop_generate_style_props(isset($this->data->titleColor) ? $this->data->titleColor : '', 'color', '0,0,0', '1');
         $sliderNavColor = bravepop_generate_style_props(isset($this->data->slideNavColor) ? $this->data->slideNavColor : '', 'border-color', '0,0,0', '1');
         $sliderNavActiveColor = bravepop_generate_style_props(isset($this->data->slideNavColor) ? $this->data->slideNavColor : '', 'background-color', '0,0,0', '1');

         //Post Chunk Width
         $postChunkWidth = $slide ? 'width: '.$this->data->width.'px' : 'width: '.((100 / $postCount)).'%;';
         $postChunkPadd = '';
         if(!$slide && isset($this->data->backgroundColor) && isset($this->data->backgroundColor->opacity) && ($layout !== 3) ){
            $postChunkPadd =  $this->data->backgroundColor->opacity > 0 ? 'padding: 0 5px;' :'';
         }
         

         $elementInnerStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__styler{'. $fontSize . $postTextColor . $fontFamily . $fontBold . $fontItalic.'}';

         $elementPostStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_post{'.$postWidth . $backgroundColor .'}';
         $elementButtonStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_post__readMore a{'. $btnTxtColor .'}';
         $elementTitleStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_post h4 a{'. $postTitleColor .$titlefontFamily . $titlefontBold . $titlefontItalic.'}';
         $elementImageStyle = $imageHeight ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_post__image{'. $imageHeight .'}': '';
         $elementChunkStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_post__chunk{'. $postChunkWidth . $postChunkPadd.'}';
         $elementSliderNavStyle = $slide ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_carousel__slide__navs span{'. $sliderNavColor .'}' : '';
         $elementSliderNavActiveStyle = $slide ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .slide__nav__active span{'. $sliderNavActiveColor .'}' : '';
         $elementContentStyle = $slide && $layout === 3 ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_post__image__content, #brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_post__image__content a{'. $postTextColor .'}' : '';

         return  $elementInnerStyle . $elementPostStyle .$elementButtonStyle .$elementTitleStyle .$elementImageStyle .$elementChunkStyle . $elementSliderNavStyle .$elementSliderNavActiveStyle. $elementContentStyle ;

      }


      public function renderMeta($cats , $postID){
         $displayDate = isset($this->data->date) ? $this->data->date : true;
         $displayCat = isset($this->data->category) ? $this->data->category : true;
         $metaHTML = '';

         if($displayDate || $displayCat){
            $metaHTML .=  '<div class="brave_post__meta '.(!$displayDate ? 'brave_post__meta--hide_date':'').'">';
            $metaHTML .=  $displayDate ? '<div class="brave_post__content__date">'.get_the_time( get_option('date_format') , $postID).'</div>' : '';
            if($displayCat){
               $metaHTML .=  '<div class="brave_post__content__category">';
               foreach ( $cats as $key=>$category ) {
                  $comma = (count($cats) - 1) !== $key ? ', ' : '';
                  $metaHTML .=  '<a href="'.get_category_link($category->term_id).'">'.$category->name.'</a></li>'.$comma;
               }
               $metaHTML .=  '</div>';
            }
            $metaHTML .=  '</div>';
         }

         return $metaHTML;
      }


      public function renderPosts(){
         $slide = isset($this->data->slide) ? $this->data->slide : false;
         $slidePosts = isset($this->data->slidePosts) ? $this->data->slidePosts : 1;
         $displayTitle = isset($this->data->title) && $this->data->title === false ? false : true;
         $fulltitle = isset($this->data->fulltitle) && $this->data->fulltitle === true ? true : false;
         $displayContent = isset($this->data->content) ? $this->data->content : true;
         $displayImage = isset($this->data->image) ? $this->data->image : true;
         $displayButton = isset($this->data->button) ? $this->data->button : true;
         $newWindow = isset($this->data->newWindow) ? $this->data->newWindow : true;
         $slideDuration = isset($this->data->slideDuration) ? $this->data->slideDuration : 3000;
         $newWindowHTML = $newWindow ? 'target="_blank"' : '';

         $layout = isset($this->data->layout) ? $this->data->layout : 1;
         $buttonText = isset($this->data->buttonText) ? $this->data->buttonText : 'Read More';
         $contentWords = isset($this->data->contentWords) ? (int)$this->data->contentWords : 100;
         $imageAlignLeft = isset($this->data->imageAlignLeft) ? $this->data->imageAlignLeft : false;
         $imageAlignLeftClass = $imageAlignLeft && $layout=== 2 ? 'brave_post--left-image' : '';
         $noContentClass = $displayContent === true ? '' : 'brave_post--no-content';
         $slideClass = $slide ? 'brave_post__chunk--carousel' : 'brave_post__chunk--notCarousel';
         $goalAction = $this->goalItem ? 'onclick="brave_complete_goal('.$this->popupID.', \'click\');"':'';

         //QUERY VARS
         $postType = isset($this->data->postType) ? $this->data->postType : 'latest';
         $filterType = isset($this->data->filterType) ? $this->data->filterType : 'categories';
         $postCount = isset($this->data->postCount) ? $this->data->postCount : 3;
         $categories =  isset($this->data->categories) ? $this->data->categories : '';
         $tags =  isset($this->data->tags) ? $this->data->tags : false;
         $customIds=  isset($this->data->customIds) ? $this->data->customIds : false; 
         $orderby =  !empty($this->data->random) ? 'rand' : 'date';

         $the_query = new WP_Query( array( 'post_type' => 'post' , 'posts_per_page' => $postCount, 'orderby' => $orderby, 'ignore_sticky_posts' => 1 ) );

         // if($postType === 'latest'){
         //    $the_query = new WP_Query( array( 'post_type' => 'post' , 'posts_per_page' => $postCount, 'orderby' => $orderby ) );
         // }
         if($postType === 'related'){
            $filterType = isset($this->data->relatedPostType) ? $this->data->relatedPostType : 'categories';
         }
         if($postType === 'multiple' || $postType === 'popular' || $postType === 'related'){
            if(function_exists('bravepop_posts_element_query')){
               $the_query = bravepop_posts_element_query($postType, $filterType, $postCount, $orderby, $customIds, $categories, $tags);
            }
         }

         //error_log(wp_json_encode($the_query));

         if(!$the_query){ return ''; }

         // The Loop
         $postHTML =  '';
         $blocks = array_chunk($the_query->posts, $slide && $slidePosts? $slidePosts : 1);
         $totalPosts = $the_query->post_count; 
         $imageSize = isset($this->data->imageHeight) && $this->data->imageHeight > 249 ? 'large' : 'medium'; 
         $postHTML .= $slide ? '<div class="brave_carousel__slider"><div class="brave_carousel__slider_wrap"> <div class="brave_carousel__slides" style="width: '.$totalPosts * $this->data->width.'px" id="brave_carousel__slides-'.$this->data->id.'" data-totalslides="'.count($blocks).'" data-width="'.$this->data->width.'" data-duration="'.$slideDuration.'" data-hovered="false" onmouseenter="brave_carousel_pause(\''.$this->data->id.'\', false)" onmouseleave="brave_carousel_pause(\''.$this->data->id.'\', true)">' : '';

         if ( $the_query->have_posts() ) {
            foreach ($blocks as $block) {
               $postHTML .= '<div class="brave_post__chunk '.$slideClass.'">';
                     foreach ($block as $thepost) {
                        $postHTML .=  '<div id="brave_post-'.$thepost->ID.'" class="brave_post '.$imageAlignLeftClass.' '.$noContentClass.'">';
                           //POST IMAGE
                           if($displayImage) {
                              $postHTML .=  '<div class="brave_post__image">';
                              if($layout === 3){
                                 $postHTML .=  '<div class="brave_post__image__content">';
                                    $postHTML .= $this->renderMeta(get_the_category($thepost->ID), $thepost->ID);
                                    $postHTML .=  '<h4><a '.$newWindowHTML.' href="'.get_the_permalink($thepost->ID).'" '.$goalAction.'>'. get_the_title($thepost->ID) .'</a></h4>';
                                 $postHTML .=  '</div>';
                              }
                              $lazyLoad = bravepop_should_lazyload();
                              $imgURL = get_the_post_thumbnail_url($thepost->ID, $imageSize);
                              $imgSrc = $lazyLoad ? 'data-lazy="'.$imgURL.'" src="'.bravepop_get_preloader().'"' : 'src="'.$imgURL.'"';

                              $postHTML .=  has_post_thumbnail($thepost->ID) ? '<a '.$newWindowHTML.' href="'.get_the_permalink($thepost->ID).'"><img class="brave_element_img_item skip-lazy no-lazyload" '.$imgSrc.' alt="'. get_the_title($thepost->ID) .'" /></a>' : '<div class="brave_post__image__fake"></div>';
                              
                              $postHTML .=  '</div>';
                           }

                           //POST CONTENT
                           if($layout  !== 3) { 
                              $excerptraw = get_the_excerpt($thepost->ID);
                              $excerptraw =  mb_strimwidth($excerptraw, 0, $contentWords);
                              //$excerpt = isset($this->data->contentWords) ? substr($excerptraw, 0, strrpos($excerptraw, ' ')) : get_the_excerpt($thepost->ID);
                              $excerpt = $excerptraw.'...';

                              $postHTML .=  '<div class="brave_post__content_wrap">';
                                 $postHTML .=  '<div class="brave_post__title '.($fulltitle?'brave_post__title--full':'').'"><h4><a '.$newWindowHTML.' href="'.get_the_permalink($thepost->ID).'">'. get_the_title($thepost->ID) .'</a></h4></div>';
                                 $postHTML .=  '<div class="brave_post__content">';
                                    $postHTML .= $this->renderMeta(get_the_category($thepost->ID), $thepost->ID);
                                    $postHTML .=  $displayContent && ($layout===1 || $layout===2) ?'<div class="brave_post__content__excerpt">'.$excerpt.'</div>' : '';
                                 $postHTML .=  '</div>';

                                 $postHTML .= $displayButton  && $buttonText && ($layout===1 || $layout===2) ? '<div class="brave_post__readMore"><a '.$newWindowHTML.' href="'.get_the_permalink($thepost->ID).'">'.$buttonText.'</a></div>' : '';

                              $postHTML .=  '</div>';
                           }
                        $postHTML .=  '</div>';
                     }
               $postHTML .= '</div>';
            }
         }
         wp_reset_postdata();

         $postHTML .= $slide ? $this->renderCarouselNav($totalPosts) : '';
         $postHTML .= $slide ? '</div></div></div>' : '';

         return $postHTML;

      }


      public function renderCarouselNav($totalPosts=3) { 
         $slide = isset($this->data->slide) ? $this->data->slide : false;
         $slidePosts = isset($this->data->slidePosts) ? $this->data->slidePosts : 1;

         $slideNavCount = $totalPosts / $slidePosts;
         if(is_float($slideNavCount)){
            $slideNavCount = floor($slideNavCount) + 1;
         }
         $showNavigation = true;
         $navItems = '';
         if($showNavigation){
            $navItems .= '<div class="brave_carousel__slide__navs" id="brave_carousel__navs-'.$this->data->id.'" data-currentslide="0"><ul>';
            for ($x = 0; $x < $slideNavCount; $x++) {
               $navItems .= '<li id="brave_carousel__nav-'.$this->data->id.'_'.$x.'" onclick="brave_change_slide(\''.$this->data->id.'\', \''.$x.'\', \''.$this->data->width.'\');" class="'.($x === 0 ? 'slide__nav__active':'').'"><span>'.$x.'</span></li>';
            }
            $navItems .= '</ul></div>';
         }

         return $navItems;
      }


      public function render( ) { 
         $layout = isset($this->data->layout) ? $this->data->layout : 1;
         $customClass = !empty($this->data->classes) ? ' '. str_replace(',',' ',$this->data->classes) : '';

         return '<div id="brave_element-'.$this->data->id.'" class="brave_element brave_element--posts '.$customClass.'">
                  <div class="brave_element__wrap">
                     <div class="brave_element__styler">
                        <div class="brave_element__inner">
                           <div class="brave_wpPosts brave_wpPosts--layout'.$layout.'">
                              '.$this->renderPosts().'
                           </div>
                        </div>
                     </div>
                  </div>
               </div>';
      }


   }


}
?>