<?php
function bravepop_get_wooProducts($type='', $filterType='', $count=3, $categories='', $tags='', $postIDs='', $postID=''){

   $allProducts= [];

   $wpData = new stdClass();
   $args = array( 'limit' => $count) ;

   if($type === 'most_sold'){
       $most_soldIDItems = get_posts(array(
           'post_type'             => 'product',
           'post_status'           => 'publish',
           'ignore_sticky_posts'   => 1,
           'posts_per_page'        => $count,            
           'meta_key'              => 'total_sales',
           'orderby'               => 'meta_value_num',
           'order'                 => 'DESC',
           'meta_query'        => array(
               array(
                   'key'           => 'total_sales',
                   'value'         => 1,
                   'compare'       => '>='
               )
           )
       ));

       $most_sold_IDS = array();
       foreach ($most_soldIDItems as $product) {
           $most_sold_IDS[] =  $product->ID;
       }

       $args = array( 'include' => $most_sold_IDS );

       //error_log(wp_json_encode( $most_sold_IDS ));
   }

   if($type === 'multiple'){
       if($filterType === 'categories' && is_array($categories)){
           
           $args = array( 'post_type' => 'product', 'limit' => $count, 'category'=> $categories );
       }else if($filterType === 'tags' && is_array($tags)){
           $args = array( 'post_type' => 'product', 'limit' => $count, 'tag'=> $tags );
       }else if($filterType === 'custom' && is_array($postIDs)){
           $args = array( 'include' => $postIDs );
       }
   }

   if($postID){
       $args = array( 'include' => $postID );
   }

   //error_log(wp_json_encode( $args ));
   if(class_exists( 'SitePress' )){
      $args['suppress_filters'] = true;
   }

   $products = wc_get_products( $args );
   $currencySymbol = function_exists('get_woocommerce_currency_symbol') ?  get_woocommerce_currency_symbol() : '$';

   foreach ( $products as $product ) {
       $object = new stdClass();
       $productDate = $product->get_date_created();
       $object->ID = $product->get_id() ;
       $object->type = $product->get_type();
       $object->title = $product->get_name();
       $object->slug = $product->get_slug();
       $object->date = $productDate;
       $object->status = $product->get_status();
       $object->featured = $product->get_featured();
       $object->visibility = $product->get_catalog_visibility();
       $object->description = $product->get_description();
       $object->excerpt = $product->get_short_description();
       $object->sku = $product->get_sku();
       $object->virtual = $product->get_virtual();
       $object->link = get_permalink( $product->get_id() );
       // Get Product Prices
       $object->currency_symbol = $currencySymbol;
       $object->price = $product->get_price();
       $object->regular_price = $product->is_type( 'variable' ) ? $product->get_variation_regular_price( 'min' ) : $product->get_regular_price();
       $object->sale_price = $product->is_type( 'variable' ) ? $product->get_variation_sale_price( 'min' ) : $product->get_sale_price();
       $object->sold = $product->get_total_sales();
       // Get Product Variations
       $object->attributes = $product->get_attributes();
       $object->default_attributes = $product->get_default_attributes();
       // Get Product Taxonomies
       //$object->category_ids = $product->get_category_ids();
       //$object->labels = bravepop_get_product_labels($product);
       $object->categories = bravepop_get_product_terms($product->get_category_ids());
       $object->tag_ids =  bravepop_get_product_terms($product->get_tag_ids());
       // Get Product Downloads
       $object->downloads = $product->get_downloads();
       $object->downloadable = $product->get_downloadable();
       // Get Product Images
       //$object->image_id = $product->get_image_id();
       $object->image = bravepop_prepare_product_image($product->get_id() );
       $object->gallery_image_ids = $product->get_gallery_image_ids();
       // Get Product Reviews
       $object->reviews_allowed = $product->get_reviews_allowed();
       $object->rating_counts = $product->get_rating_counts();
       $object->average_rating = $product->get_average_rating();
       $object->review_count = $product->get_review_count();

       $allProducts[] = $object;
   }

   //Upsell, Cross Sell Dummy Data 
   if($type === 'upsell' || $type === 'cross_sell' || $type === 'related'){
      $allProducts = array();
      for ($i=1; $i < $count+1 ; $i++) { 
         $object = new stdClass();
         $productDate = $product->get_date_created();
         $object->ID = 9999 + $i ;
         $object->type = 'simple';
         $object->title = ucwords(str_replace('_', ' ', $type)).__(' Product ', 'bravepop').$i;
         $object->slug = $type.'_product_'.$i;
         $object->status = 'publish';
         $object->featured = false;
         $object->description = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
         $object->excerpt = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit sed do eiusmod. ';
         $object->sku = $type.'_product_'.$i;
         $object->link = '';
         $object->currency_symbol = $currencySymbol;
         $object->price = 99;
         $object->regular_price = 99;
         $object->sale_price = 89;
         $object->sold = 10 + $i;
         $termObject = new stdClass(); $termObject->ID = 99 + $i;  $termObject->link = '';  $termObject->title =  'Dummy Category';
         $object->categories = array($termObject);
         $object->image = BRAVEPOP_PLUGIN_PATH.'assets/images/image.png';
         $object->reviews_allowed = true;
         $object->rating_counts = 5;
         $object->average_rating = 5;
         $object->review_count = 5;
  
         $allProducts[] = $object;
      }
   }

   //error_log(wp_json_encode($allProducts));

   $wpData->posts = $allProducts;
   return new WP_REST_Response($wpData);
  
}

function bravepop_get_product_labels($product){
   $labels = array();
   foreach ($product->get_attributes() as $taxonomy => $attribute_obj ) {
       $labels[] = wc_attribute_label($taxonomy);
   }
   return $labels;
}

function bravepop_get_product_terms($termIDs){
   $terms = array();
   foreach ($termIDs as $termID) {
       $termObject = new stdClass();
       $theTerm = get_term($termID);
       $termObject->ID = $termID;
       $termObject->link = get_term_link($termID);
       $termObject->title =  $theTerm->name;
       $terms[] = $termObject;
   }
   return $terms;
}

function bravepop_prepare_product_image( $postID ){
   $object = new stdClass();
   $imgDataArray = wp_get_attachment_image_src( get_post_thumbnail_id( $postID ), 'large', false );
   $object->url = $imgDataArray[0] ? $imgDataArray[0] : '';
   $object->width = $imgDataArray[1] ? $imgDataArray[1] : '';
   $object->height = $imgDataArray[2] ? $imgDataArray[2] : '';
   return $object;
}

function bravepop_woo_latest_and_bestsellers( $type='latest', $count=3, $autoFilter=false ){
   if(!BRAVEPOP_WOO_ACTIVE){ return array(); }
   $autoFilterCatID = false;
   $finalProducts = array();
   $productsToExclude = array();
   $currentProduct = array();
   if(is_product()){
      $product = wc_get_product();
      if($product){
         $currentProduct[] = $product->get_id();
      }
   }

   //If Auto filter, query latest or best sellers of current category.
   if(!empty($autoFilter)){
      if(is_product()){
         $product = wc_get_product();
         if($product){ 
            $currentCats = $product->get_category_ids();
            if(isset($currentCats[0])){   $autoFilterCategory = get_term($currentCats[0])->slug;  }
         }
      }
      if(is_product_category()){
         $category = get_queried_object();
         if(isset($category->term_id)){
            $autoFilterCategory = $category->slug;
         }
      }
      if(!empty($autoFilterCategory)){
         //Finally prepend the category specific latest/bestsellers to the generic product lists $finalProducts
         $filteredArgs= array( 'limit' => $count, 'category'=> array($autoFilterCategory), 'exclude' => $currentProduct );
         if($type === 'most_sold'){
            $filteredArgs =  array( 'limit' => $count, 'category'=> array($autoFilterCategory), 'meta_key' => 'total_sales', 'orderby'  => array( 'meta_value_num' => 'DESC', 'title' => 'ASC' ), 'exclude' => $currentProduct );
         }
         $filteredQuery = new WC_Product_Query( $filteredArgs );
         $finalProducts = $filteredQuery->get_products();
         foreach ($finalProducts as $key => $fProduct) {
            $productsToExclude[] = $fProduct->get_id();
         }
         
      }
   }

   if(count($finalProducts) < $count){
      $allQueryArgs = array( 'limit' => $count, 'exclude' => array_merge($currentProduct, $productsToExclude)) ;
      if($type === 'most_sold'){
         $allQueryArgs =  array( 'limit' => $count, 'meta_key' => 'total_sales', 'orderby'  => array( 'meta_value_num' => 'DESC', 'title' => 'ASC' ), 'exclude' => array_merge($currentProduct, $productsToExclude) );
      }
   
      $allQuery = new WC_Product_Query( $allQueryArgs );
      $allQueryProducts = $allQuery->get_products();
   
      if(is_array($allQueryProducts) && count($allQueryProducts)){
         foreach ($allQueryProducts as $key => $aProduct) {
            $finalProducts[] = $aProduct;
         }
      }
   }


   //error_log('finalProducts: '. wp_json_encode($finalProducts));

   return $finalProducts;
}