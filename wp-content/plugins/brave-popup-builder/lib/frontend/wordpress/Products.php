<?php

if ( ! class_exists( 'BravePop_Element_Products' ) ) {
   

   class BravePop_Element_Products {

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
                  setInterval("brave_autochange_slide('<?php print_r(esc_attr($this->data->id))?>')", <?php print_r(isset($this->data->slideDuration) ? absint($this->data->slideDuration) * 1000 : 2000); ?>);
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
         $slideProducts = isset($this->data->slideProducts) ? $this->data->slideProducts : 1;
         $layout = isset($this->data->layout) ? $this->data->layout : 1;
         $customIds = isset($this->data->customIds) ? $this->data->customIds : '';
         $imageWidth=  isset($this->data->imageWidth) ? $this->data->imageWidth : 100;
         if($postType==='multiple' && $filterType=== 'custom' && count($customIds) > 0){ $postCount = count($customIds); }

         $postWidth = 'width: '.((100 / $postCount) - 2).'%;'; 
         if($slide){ $postWidth = 'width: '.((100 / $slideProducts) - 4 ).'%;'; }else{ $postWidth = 'width: 98%;'; }

         //Post Chunk Width
         $postChunkWidth = $slide ? 'width: '.$this->data->width.'px' : 'width: '.((100 / $postCount)).'%;';
         $contentWrapStyle = '';
         if($layout === 2){   $contentWrapStyle =  'width: calc(100% - '.$imageWidth.'px);';    }
         if($layout === 2 && !$slide){   $postChunkWidth = 'width: 100%;'; }
         
         $fontSize = isset($this->data->contentSize) ? bravepop_generate_style_props( $this->data->contentSize, 'font-size') :'font-size:17px;';
         $imageHeight = isset($this->data->imageHeight) ?  'height: '.$this->data->imageHeight.'px;' : '';
         $imageWidth = $layout === 2 && $imageWidth ?  'width: '.$imageWidth.'px;' : '';
         $fontColor = bravepop_generate_style_props(isset($this->data->fontColor) ? $this->data->fontColor : '', 'color', '0,0,0', '1');
         
         $backgroundColor = bravepop_generate_style_props(isset($this->data->backgroundColor) ? $this->data->backgroundColor : '', 'background-color', '255, 255, 255', '0');
         $textColor = bravepop_generate_style_props(isset($this->data->contentColor) ? $this->data->contentColor : '', 'color', '107, 107, 107', '1');
         $titleColor = bravepop_generate_style_props(isset($this->data->titleColor) ? $this->data->titleColor : '', 'color', '68, 68, 68', '1');
         $btnTxtColor =  bravepop_generate_style_props(isset($this->data->buttonTextColor) ? $this->data->buttonTextColor : '', 'color', '255, 255, 255', '1');
         $btnBgColor = bravepop_generate_style_props(isset($this->data->buttonBackgroundColor) ? $this->data->buttonBackgroundColor : '', 'background-color', '109, 120, 216', '1');
         $prcColor = bravepop_generate_style_props(isset($this->data->priceColor) ? $this->data->priceColor : '', 'color', '0, 0, 0', '1'); 
         $saleRibbonTxtColor  = bravepop_generate_style_props(isset($this->data->saleTextColor) ? $this->data->saleTextColor : '', 'color', '255, 255, 255', '1'); 
         $saleRibbonBg  = bravepop_generate_style_props(isset($this->data->saleBgColor) ? $this->data->saleBgColor : '', 'fill', '109, 120, 216', '1'); 
         $sliderNavColor = bravepop_generate_style_props(isset($this->data->slideNavColor) ? $this->data->slideNavColor : '', 'border-color', '0,0,0', '1');
         $sliderNavActiveColor = bravepop_generate_style_props(isset($this->data->slideNavColor) ? $this->data->slideNavColor : '', 'background-color', '0,0,0', '1');


         $elementInnerStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_element__styler{'. $fontSize . $textColor .'}';
         
         $elementContentWStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_product__content_wrap{'. $fontSize .'}';
         $elementPostStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_product{'.$postWidth . $backgroundColor .'}';
         $elementButtonStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_product__addtocart a{'. $btnTxtColor . $btnBgColor .'}';
         $elementTitleStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_product h4 a{'. $titleColor .'}';
         $elementImageStyle = $imageHeight ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_product__image{'. $imageHeight .$imageWidth .'}': '';
         $elementChunkStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_product__price{'. $prcColor .'}';
         $elementPriceDelStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_product__price del{'. $textColor .'}';
         $elementPriceStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_product__chunk{'. $postChunkWidth .'}';
         $elementSliderNavStyle = $slide ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_carousel__slide__navs span{'. $sliderNavColor .'}' : '';
         $elementSliderNavActiveStyle = $slide ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .slide__nav__active span{'. $sliderNavActiveColor .'}' : '';
         $elementContentStyle =  $contentWrapStyle ? '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_product__content_wrap{'. $contentWrapStyle .'}' : '';
         $elementRibonStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_product_sale_ribon span{'. $saleRibbonTxtColor . '}';
         $elementRibonBGStyle = '#brave_popup_'.$this->popupID.'__step__'.$this->stepIndex.' #brave_element-'.$this->data->id.' .brave_product_sale_ribon svg{'. $saleRibbonBg . '}';

         
         return  $elementInnerStyle . $elementContentWStyle . $elementPostStyle .$elementButtonStyle .$elementTitleStyle .$elementImageStyle .$elementPriceStyle .$elementChunkStyle . $elementPriceDelStyle . $elementSliderNavStyle .$elementSliderNavActiveStyle .$elementRibonStyle .$elementRibonBGStyle .$elementContentStyle ;

      }


      public function renderPosts(){
         $slide = isset($this->data->slide) ? $this->data->slide : false;
         $slideProducts = isset($this->data->slideProducts) ? $this->data->slideProducts : 1;
         $displayTitle = isset($this->data->title) && $this->data->title === false ? false : true;
         $fulltitle = isset($this->data->fulltitle) && $this->data->fulltitle === true ? true : false;
         $displayContent = isset($this->data->content) ? $this->data->content : false;
         $displayImage = isset($this->data->image) ? $this->data->image : true;
         $displayButton = isset($this->data->button) ? $this->data->button : true;
         $displayCat = isset($this->data->category) ? $this->data->category : true;
         $displayRibbon = isset($this->data->saleText) && $this->data->saleText==='' ?false : true;
         $price  = isset($this->data->price) ? $this->data->price : true;
         $newWindow = isset($this->data->newWindow) ? $this->data->newWindow : true;
         $closeOnAddToCart = isset($this->data->closeOnAddToCart) ? $this->data->closeOnAddToCart : false;
         $centerSlideNav = isset($this->data->centerSlideNav) ? $this->data->centerSlideNav : false;
         $slideDuration = isset($this->data->slideDuration) ? $this->data->slideDuration : 2000;
         $newWindowHTML = $newWindow ? 'target="_blank"' : '';
         $saleText = isset($this->data->saleText) ? $this->data->saleText : 'Sale';
         $currency = function_exists('get_woocommerce_currency_symbol') ?  get_woocommerce_currency_symbol() : '$';

         $layout = isset($this->data->layout) ? $this->data->layout : 1;
         $linkImage = isset($this->data->linkImage) ? $this->data->linkImage : false;
         $buttonText = isset($this->data->buttonText) ? $this->data->buttonText : 'Add to Cart';
         $buttonTextVar = isset($this->data->buttonTextVar) ? $this->data->buttonTextVar : 'View Product';
         $btnTxtColor =  bravepop_generate_style_props(isset($this->data->buttonTextColor) ? $this->data->buttonTextColor : '', 'raw', '255, 255, 255', '1');
         $contentWords = isset($this->data->contentWords) ? $this->data->contentWords : 100;
         $imageAlignLeft = isset($this->data->imageAlignLeft) ? $this->data->imageAlignLeft : false;
         $showRegularDelPrice = isset($this->data->showRegularDelPrice) && $this->data->showRegularDelPrice ? true : false;
         $hasBackgroundClass = isset($this->data->backgroundColor) && isset($this->data->backgroundColor->opacity) && ($this->data->backgroundColor->opacity > 0) ? 'brave_product__chunk--hasBackground ' : '';
         $buttonAlwaysClass = $layout === 1 && !empty($this->data->buttonAlways) ? 'brave_product__chunk--buttonAlways':'';
         $slideClass = $slide ? 'brave_product__chunk--carousel ' : 'brave_product__chunk--notCarousel ';
         $centerSlideNavClass = $centerSlideNav ? 'brave_carousel__slider--center': '';
         $buttonNoBGClass = isset($this->data->buttonBackgroundColor) && isset($this->data->buttonBackgroundColor->opacity) && ($this->data->buttonBackgroundColor->opacity === 0) ? 'brave_product__addtocart--noBG':'';
         $addToCartActionFuncs = '';
         $addToCartActionFuncs .= $closeOnAddToCart ? 'brave_close_on_add_to_cart('.$this->popupID.');' :'';
         $addToCartActionFuncs .= $this->goalItem ? 'brave_complete_goal('.$this->popupID.', \'click\');':'';
         $addToCartActionFuncs = function_exists('is_checkout') && is_checkout() ? 'brave_update_checkout_on_add_to_cart();':'';
         $addToCartAction = $addToCartActionFuncs ? 'onclick="'.$addToCartActionFuncs.'"':'';

         // The Loop
         $postHTML =  '';
         $products = $this->buildQuery();
         //error_log($slideProducts);
         if(!$products){ return '';}
         $blocks = array_chunk($products, $slide && $slideProducts? $slideProducts : 1);
         $totalPosts = count($products); 
         $postHTML .= $slide ? '<div class="brave_carousel__slider '.$centerSlideNavClass.'"><div class="brave_carousel__slider_wrap"> <div class="brave_carousel__slides" style="width: '.$totalPosts * $this->data->width.'px" id="brave_carousel__slides-'.$this->data->id.'" data-totalslides="'.count($blocks).'" data-width="'.$this->data->width.'" data-duration="'.$slideDuration.'" data-hovered="false" onmouseenter="brave_carousel_pause(\''.$this->data->id.'\', false)" onmouseleave="brave_carousel_pause(\''.$this->data->id.'\', true)">' : '';

         if ( $totalPosts > 0) {
            foreach ($blocks as $block) {
               $postHTML .= '<div class="brave_product__chunk '.$slideClass. $hasBackgroundClass. $buttonAlwaysClass.'">';
                     foreach ($block as $theproduct) {
                        $productID = $theproduct->get_id();
                        $productSKU = $theproduct->get_sku();
                        $variableProduct = $theproduct->is_type( 'variable' );
                        $postHTML .=  '<div id="brave_product-'.$productID.'" class="brave_product">';
                           
                           //Add to Cart Button
                           $cartIcons = '<span class="brave_product__addtocart_add">'.bravepop_renderIcon('plus', $btnTxtColor).'</span><span class="brave_product__addtocart_added">'.bravepop_renderIcon('check', $btnTxtColor).'</span>';
                           $cartButton = '<a href="?add-to-cart='.$productID.'" '.$addToCartAction.' data-quantity="1" class="add_to_cart_button ajax_add_to_cart" data-product_id="'.$productID.'" data-product_sku="'.$productSKU.'" rel="nofollow">'.$cartIcons.$buttonText.'</a>';
                           if($variableProduct){
                              $cartButton = '<a href="'.get_the_permalink($productID).'" '.$addToCartAction.' data-quantity="1" data-product_id="'.$productID.'" data-product_sku="'.$productSKU.'" rel="nofollow">'.$buttonTextVar.'</a>';
                           }

                           //SALE RIBBON
                           $postHTML .= $displayRibbon && $theproduct->is_on_sale() ? '<div class="brave_product_sale_ribon"><span>'.$saleText.'</span><svg preserveAspectRatio="none"  version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="40px" height="40px" viewBox="0 0 40 40" enableBackground="new 0 0 40 40" xmlSpace="preserve"><polygon points="39.998,0.001 -0.002,40.001 -0.002,0" /></svg></div> ' : '';

                           //POST IMAGE
                           if($displayImage) {
                              $lazyLoad = bravepop_should_lazyload();
                              $imgURL = get_the_post_thumbnail_url($productID, 'large');
                              $imgSrc = $lazyLoad ? 'data-lazy="'.$imgURL.'" src="'.bravepop_get_preloader().'"' : 'src="'.$imgURL.'"';

                              $postHTML .=  '<div class="brave_product__image">';
                              if($layout === 1 && $displayButton){
                                 $postHTML .=  '<div class="brave_product__addtocart">'.$cartButton.'</div>';

                                 // $postHTML .=  '<div class="brave_product__addtocart">'.do_shortcode('[add_to_cart show_price="false" style="" id="'.$productID.'"]').'</div>';
                              }
                              $postHTML .= ($layout === 2 || $linkImage ) ? '<a '.$newWindowHTML.' href="'.get_the_permalink($productID).'">' : '';
                              $postHTML .= get_the_post_thumbnail_url($productID, 'large') ? '<img class="brave_element_img_item skip-lazy no-lazyload" '.$imgSrc.' alt="' . $theproduct->get_name() . '" />' : '<div class="brave_product__image__fake"><span class="fas fa-image"></span></div>';
                              $postHTML .= ($layout === 2 || $linkImage ) ? '</a>' : '';
                              $postHTML .=  '</div>';
                           }

                           //POST CONTENT
                           $postHTML .=  '<div class="brave_product__content_wrap">';
                              $postHTML .=  '<div class="brave_product__title '.($fulltitle?'brave_product__title--full':'').'"><h4 title="'.$theproduct->get_name().'"><a '.$newWindowHTML.' href="'.get_the_permalink($productID).'">' . $theproduct->get_name() . '</a></h4></div>';
                              
                              //brave_product__content START
                              $postHTML .=  '<div class="brave_product__content">';
                              if($displayCat){
                                 $postHTML .=  '<div class="brave_product__meta">';   
                                    $postHTML .=  '<div class="brave_post__content__category">';
                                    $cats = $theproduct->get_category_ids();
                                    foreach ( $cats as $key=>$categoryID ) {
                                       if($key === 0 || $key === 1){
                                          $comma = (count($cats) > 1) && $key === 0 ? ', ' : '';
                                          $postHTML .=  '<a href="'.get_term_link($categoryID).'">'.get_term( $categoryID )->name.'</a></li>'.$comma;
                                       }
                                    }
                                    $postHTML .=  '</div>';
                                 $postHTML .=  '</div>';
                              }

                                 //PRICE
                                 $regularPrice = $variableProduct ? $theproduct->get_variation_regular_price( 'min' ) : $theproduct->get_regular_price();
                                 $salePrice = $variableProduct ? $theproduct->get_variation_sale_price( 'min' ) : $theproduct->get_sale_price();
                                 $crossedOutRegularPrice = $showRegularDelPrice && $theproduct->is_on_sale() ? '<del>'.$currency.wc_get_price_to_display( $theproduct, array( 'price' => $regularPrice  )).'</del>': '';
                                 $postPrice = wc_get_price_to_display( $theproduct, array( 'price' => $theproduct->is_on_sale() ?  $salePrice : $regularPrice ) );

                                 $postHTML .= $price && $postPrice ?'<div class="brave_product__price">'.$crossedOutRegularPrice.$currency.$postPrice.'</div>': '';
                                 $postDesc = $theproduct->get_short_description();

                                 $postHTML .=  $displayContent ?'<div class="brave_product__content__excerpt">'.mb_strimwidth($postDesc, 0, $contentWords).'</div>' : '';
                              $postHTML .=  '</div>';
                              //brave_product__content END

                              $postHTML .= $displayButton  && $layout===2 ? '<div class="brave_product__addtocart '.$buttonNoBGClass.'">'.$cartButton.'</div>' : '';

                           $postHTML .=  '</div>';
                           //POST CONTENT END

                           
                        $postHTML .=  '</div>';
                     }
               $postHTML .= '</div>';
            }
         }

         $postHTML .= $slide ? $this->renderCarouselNav($totalPosts) : '';
         $postHTML .= $slide ? '</div></div></div>' : '';

         return $postHTML;

      }

      public function buildQuery(){
         //QUERY VARS
         $type = isset($this->data->postType) ? $this->data->postType : 'multiple';
         $filterType = isset($this->data->filterType) ? $this->data->filterType : 'categories';
         $count = isset($this->data->postCount) ? $this->data->postCount : 3;
         $categories =  isset($this->data->categories) ? $this->data->categories : false;
         $tags =  isset($this->data->tags) ? $this->data->tags : false;
         $postIDs=  isset($this->data->customIds) ? $this->data->customIds : false; 
         $autoFilter=  isset($this->data->autoFilter) ? $this->data->autoFilter : false; 

         if($type === 'latest' || $type === 'most_sold'){
            return bravepop_woo_latest_and_bestsellers( $type, $count, $autoFilter );
         }
 
         if($type === 'multiple'){
            $args  = array( 'limit' => $count );
             if($filterType === 'categories' && is_array($categories)){
                 $args = array( 'post_type' => 'product', 'limit' => $count, 'category'=> $categories );
             }else if($filterType === 'tags' && is_array($tags)){
                 $args = array( 'post_type' => 'product', 'limit' => $count, 'tag'=> $tags );
             }else if($filterType === 'custom' && is_array($postIDs)){
                 $args = array( 'include' => $postIDs );
             }
             return wc_get_products( $args );
         }
         if($type === 'upsell' || $type === 'cross_sell' || $type === 'related'){
            return bravepop_related_products($count, $type);
         }
      }


      public function renderCarouselNav($totalPosts=3) { 
         $slide = isset($this->data->slide) ? $this->data->slide : false;
         $slideProducts = isset($this->data->slideProducts) ? $this->data->slideProducts : 1;

         $slideNavCount = $totalPosts / $slideProducts;
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

         return '<div id="brave_element-'.$this->data->id.'" class="brave_element brave_element--wooProducts '.$customClass.'">
                  <div class="brave_element__wrap">
                     <div class="brave_element__styler">
                        <div class="brave_element__inner">
                           <div class="brave_wooProducts brave_wooProducts--layout'.$layout.'">
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