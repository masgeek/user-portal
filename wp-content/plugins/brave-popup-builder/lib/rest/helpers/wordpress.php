<?php
function bravepop_get_wpdata( $type, $ids=array() ) {

   $wpData = new stdClass();

   if($type == 'all'){

       //GET ALL PAGES
       $pages = get_pages(array('number' => 300, 'suppress_filters' => true, 'lang' => '')); 
       $posts = get_posts(array('post_type' => 'post', 'numberposts' => 300, 'suppress_filters' => true, 'lang' => ''));
       $categories = get_terms(array( 'taxonomy' => 'category', 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false));
       $tags = get_terms(array( 'taxonomy' => 'post_tag', 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false)); 
       $attachments = get_posts( array( 'post_type' => 'attachment', 'numberposts' => 100,'post_status' => null,'post_parent' => null) );
       $userFields = [];
       $allPages = [];
       $allPosts = [];
       $allMedia = [];
       $allCategories = [];
       $allTags = [];
       $allPostTypes = [];
       $allPostTTaxonomies = [];
       $addedItemIDs =[];

       if(is_array($ids) && count($ids) > 0){
         $alreadyLoadedPosts = array_column($posts, 'ID'); $alreadyLoadedPages = array_column($pages, 'ID');  
         $alreadyLoadedIDs = array_merge($alreadyLoadedPosts, $alreadyLoadedPages);
         $notLoadeditemIDs = array_diff ($ids, $alreadyLoadedIDs);
         if(is_array($notLoadeditemIDs) && count($notLoadeditemIDs) > 0){
            $slectedPosts = get_posts(array( 'post_type' => array('page','post'), 'include'=> $notLoadeditemIDs, 'suppress_filters' => true, 'lang' => ''));
            foreach ( $slectedPosts as $item ) {
               if(isset($item->post_type) && $item->post_type === 'page'){
                  $pages[] = $item;
               }
               if(isset($item->post_type) && $item->post_type === 'post'){
                  $posts[] = $item;
               }
            }
         }
      }

      $allWPMLPostTitles = class_exists( 'SitePress' ) && function_exists('icl_get_languages') ? bravepop_get_wpml_posts_n_pages() : array();

       foreach ( get_post_types( array('public'=> true), 'objects' ) as $post_type ) {
         if($post_type->name !== 'post' && $post_type->name !== 'page' && $post_type->name !== 'product' && $post_type->name !== 'popup' ){
            $postType = new stdClass();
            $postType->ID = $post_type->name;
            $postType->title = $post_type->label;
            $allPostTypes[] = $postType;
            $postTypeTaxes = get_object_taxonomies($post_type->name, 'objects');
            foreach ((array)$postTypeTaxes as $taxKey => $taxonomy) {
               if(isset($taxonomy->label) && !empty($taxonomy->public)){
                  $postTax = new stdClass();
                  $postTax->ID = $taxKey;
                  $postTax->title = $taxonomy->label;
                  $allPostTTaxonomies[] = $postTax;
               }
            }
            
         }
      }
       
       foreach ( $pages as $page ) {
           $object = new stdClass();
           $object->ID = $page->ID ;
           $object->title = !empty($allWPMLPostTitles['pages'][$page->ID]) ? $allWPMLPostTitles['pages'][$page->ID] : $page->post_title;
           $object->link = esc_url(get_page_link( $page->ID )) ;
           $object->slug = $page->post_name ;
           if(!in_array($page->ID, $addedItemIDs)){
            $allPages[] = $object;
            $addedItemIDs[] = $page->ID;
           }

       }
       //Add Woocommerce Thank You Page
       if ( BRAVEPOP_WOO_ACTIVE) {
         $wooThankYouPage = new stdClass();
         $wooThankYouPage->ID = 989898989898 ;
         $wooThankYouPage->title = 'Woocommerce Thank You Page' ;
         $wooThankYouPage->link = '';
         $wooThankYouPage->slug = '' ;
         if(!in_array($wooThankYouPage->ID, $addedItemIDs)){
            $allPages[] = $wooThankYouPage;
            $addedItemIDs[] = $wooThankYouPage->ID;
         }
       }

       foreach ( $posts as $post ) {
           $object = new stdClass();
           $object->ID = $post->ID ;
           $object->title = !empty($allWPMLPostTitles['posts'][$post->ID]) ? $allWPMLPostTitles['posts'][$post->ID] : $post->post_title;
           $object->link = esc_url(get_permalink( $post->ID )) ;
           $object->slug = $post->post_name ;
           if(!in_array($post->ID, $addedItemIDs)){
            $allPosts[] = $object;
            $addedItemIDs[] = $post->ID;
           }
       }

       foreach ( $categories as $category) {
           $object = new stdClass();
           $object->ID = $category->term_id ;
           $object->title = $category->name ;
           $object->link = esc_url( get_term_link( $category ) ) ;
           $object->slug = $category->slug ;
           $allCategories[] = $object;
       }
       foreach ( $tags as $tag) {
           $object = new stdClass();
           $object->ID = $tag->term_id ;
           $object->title = $tag->name ;
           $object->link = esc_url( get_term_link( $tag ) ) ;
           $object->slug = $tag->slug ;
           $allTags[] = $object;
       }
       foreach ( $attachments as $attachment ) {
           $object = new stdClass();
           $object->ID = $attachment->ID ;
           $object->title = $attachment->post_title;
           $object->type = $attachment->post_mime_type;
           $object->image = esc_url($attachment->guid);
           $object->thumbnail = wp_get_attachment_thumb_url( $attachment->ID );
           $object->width = wp_get_attachment_metadata( $attachment->ID ) && wp_get_attachment_metadata( $attachment->ID )['width'] ? wp_get_attachment_metadata( $attachment->ID )['width'] : '';
           $object->height = wp_get_attachment_metadata( $attachment->ID ) && wp_get_attachment_metadata( $attachment->ID )['height'] ? wp_get_attachment_metadata( $attachment->ID )['height'] : '';
           $object->last_modified = $attachment->post_modified;
           $allMedia[] = $object;
       }

         $currentUserID = get_current_user_id ();
         if($currentUserID){
            $allUserFields = get_user_meta ($currentUserID);
            foreach ( $allUserFields as $key => $uField) {
               $userFields[] = $key;
           }
         }

       //LearnDash
       if(function_exists('ld_course_list') && function_exists('bravepop_rest_LearnDash_objects')){
         $LDObjects = bravepop_rest_LearnDash_objects($addedItemIDs);
         $wpData->learnDash = new stdClass();
         $wpData->learnDash->courses = $LDObjects->courses;
         $wpData->learnDash->lessons = $LDObjects->lessons;
         $wpData->learnDash->quizzes = $LDObjects->quizzes;
       }

       //EDD
       if( class_exists( 'Easy_Digital_Downloads' ) ){
         $EDDObjects = bravepop_rest_EDD_objects($addedItemIDs);
         $wpData->edd = new stdClass();
         $wpData->edd->downloads = $EDDObjects->downloads;
       }

       $wpData->pages = $allPages;
       $wpData->posts = $allPosts;
       $wpData->categories = $allCategories;
       $wpData->tags = $allTags;
       $wpData->media = $allMedia;
       $wpData->post_types = $allPostTypes;
       $wpData->post_types_taxes = $allPostTTaxonomies;
       $wpData->userFields = $userFields;

       //PRODUCTS
       if ( BRAVEPOP_WOO_ACTIVE) {

           $products = wc_get_products( array( 'limit' => 300, 'suppress_filters' => true, 'lang' => ''));
           $products = apply_filters( 'bravepop_posts_for_rest', $products, 'product' );

           if(is_array($ids) && count($ids) > 0){
            $alreadyLoadedPosts = array_column($products, 'ID');
            $alreadyLoadedIDs = array_merge($alreadyLoadedPosts, $alreadyLoadedPages);
            $notLoadeditemIDs = array_diff ($ids, $alreadyLoadedIDs);

               if(is_array($notLoadeditemIDs) && count($notLoadeditemIDs) > 0){
                  $customProducts = wc_get_products(array( 'include' => $notLoadeditemIDs, 'suppress_filters' => true, 'lang' => ''));
                  if(is_array($customProducts) && count($customProducts) > 0){
                     foreach ($customProducts as $key => $cprdct) {
                        $products[] = $cprdct;
                     }
                  }
               }
           }

           $productCategories = get_terms(array( 'taxonomy' => 'product_cat', 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false));
           $productTags = get_terms(array( 'taxonomy' => 'product_tag', 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false)); 
           $allProducts = [];
           $allProductCategories = [];
           $allProductTags = [];

            if(class_exists( 'SitePress' )){
               $translatedTerms = bravepop_get_translated_terms();
               if(isset($translatedTerms['categories'])){ $productCategories = $translatedTerms['categories']; }    
               if(isset($translatedTerms['tags'])){ $productTags = $translatedTerms['tags']; } 
            }

           foreach ( $products as $product ) {
               $object = new stdClass();
               $object->ID = $product->get_id() ;
               // $object->title = $product->get_name();
               $object->title = !empty($allWPMLPostTitles['products'][$object->ID]) ? $allWPMLPostTitles['products'][$object->ID] : $product->get_name();
               $object->link = get_permalink( $product->get_id() );
               $object->price = $product->get_price();

               if(!in_array($object->ID, $addedItemIDs)){
                  $allProducts[] = $object;
                  $addedItemIDs[] = $object->ID;
               }
           }
           foreach ( $productCategories as $productCat) {
               $object = new stdClass();
               $object->ID = $productCat->term_id ;
               $object->title = $productCat->name ;
               $object->link = esc_url( get_term_link( $productCat ) ) ;
               $object->slug = $productCat->slug ;
               $allProductCategories[] = $object;
           }
           foreach ( $productTags as $productTag) {
               $object = new stdClass();
               $object->ID = $productTag->term_id ;
               $object->title = $productTag->name ;
               $object->link = esc_url( get_term_link( $productTag ) ) ;
               $object->slug = $productTag->slug ;
               $allProductTags[] = $object;
           }

           $wpData->products = $allProducts;
           $wpData->product_categories = $allProductCategories;
           $wpData->product_tags = $allProductTags;

       }

       //error_log(wp_json_encode($wpData));
       
   }

   if($type == 'media'){
       $allMedia = [];
       $attachments = get_posts( array( 'post_type' => 'attachment', 'numberposts' => 300,'post_status' => null,'post_parent' => null) );
       foreach ( $attachments as $attachment ) {
           $object = new stdClass();
           $object->ID = $attachment->ID ;
           $object->title = $attachment->post_title;
           $object->type = $attachment->post_mime_type;
           $object->image = esc_url($attachment->guid);
           $object->thumbnail = wp_get_attachment_thumb_url( $attachment->ID );
           $object->width = wp_get_attachment_metadata( $attachment->ID ) && wp_get_attachment_metadata( $attachment->ID )['width'] ? wp_get_attachment_metadata( $attachment->ID )['width'] : '';
           $object->height = wp_get_attachment_metadata( $attachment->ID ) && wp_get_attachment_metadata( $attachment->ID )['height'] ? wp_get_attachment_metadata( $attachment->ID )['height'] : '';
           $object->last_modified = $attachment->post_modified;
           $allMedia[] = $object;
       }
       $wpData->media = $allMedia;
   }

   return $wpData;
}
function bravepop_search_wpdata( $type, $query ) {
   if(!$type || !$query){ return false; }

   $allItems = array();
   
   if($type === 'page' || $type === 'post'){
      $pages = get_posts(array('post_type' => $type, 's' => $query, 'numberposts' => 300, 'suppress_filters' => true, 'lang' => '')); 
      foreach ( $pages as $page ) {
           $object = new stdClass();
           $object->ID = $page->ID ;
           $object->title = $page->post_title ;
           $object->link = esc_url(get_page_link( $page->ID )) ;
           $object->slug = $page->post_name ;
           $allItems[] = $object;
      }
   }
   if($type === 'product'){
      $products = wc_get_products( array( 's' => $query, 'limit' => 300, 'suppress_filters' => true, 'status' => 'publish', 'lang' => ''));
      foreach ( $products as $product ) {
         $object = new stdClass();
         $object->ID = $product->get_id() ;
         $object->title = $product->get_name();
         $object->link = get_permalink( $product->get_id() );
         $object->price = $product->get_price();
         $allItems[] = $object;
      }
   }

   if($type !== 'page' && $type !== 'post' && $type !== 'product'){
      $cposts = get_posts(array('post_type' => $type, 's' => $query, 'numberposts' => 300, 'suppress_filters' => true, 'lang' => '')); 
      foreach ( $cposts as $cpost ) {
           $object = new stdClass();
           $object->ID = $cpost->ID ;
           $object->title = $cpost->post_title;
           $object->type = $cpost->post_type;
           $object->link = esc_url(get_page_link( $cpost->ID )) ;
           $object->slug = $cpost->post_name ;
           $allItems[] = $object;
      }
   }
   return $allItems;
}


function bravepop_get_wpPosts( $type='', $postType='', $filterType='', $count=3, $categories='', $tags='', $postIDs='', $postID='' ) {

   $allPosts= [];

   $wpData = new stdClass();
   $args = array( 'post_type' => 'post', 'numberposts' => $count) ;

   if($type === 'popular'){
       $args = array( 'post_type' => 'post', 'numberposts' => $count, 'orderby' => 'comment_count');
   }
   if($type === 'related'){
       $args = array( 'post_type' => 'post', 'numberposts' => $count);
   }

   if($type === 'multiple'){
       if($filterType === 'categories' && is_array($categories)){
           $args = array( 'post_type' => 'post', 'numberposts' => $count, 'category__in' => $categories );
       }else if($filterType === 'tags' && is_array($tags)){
           $args = array( 'post_type' => 'post', 'numberposts' => $count, 'tag__in' => $tags );
       }else if($filterType === 'custom' && is_array($postIDs)){
           $args = array( 'post_type' => 'post', 'numberposts' => 99, 'post__in' => $postIDs );
       }
   }
   if($postType === 'post'){
       $args = array( 'post_type' => 'post', 'post__in' => $postID);
   }
   if($postType === 'page'){
       $args = array( 'post_type' => 'page', 'post__in' => $postID );
   }
   //error_log(wp_json_encode( $args ));
   //error_log(wp_json_encode(get_posts( $args )));

   $posts = get_posts( $args );
   foreach ( $posts as $post ) {
       $object = new stdClass();

       $theContent = '';
       if ($postType) {
           $blocks = parse_blocks( $post->post_content );
           foreach ($blocks as $block) {
               //error_log(wp_json_encode($block));
               if ($block['blockName']) {
                   $theContent .= $block['innerHTML'];
               }
           }
           //error_log(wp_json_encode($theContent));
       }
       
       $object->ID = $post->ID ;
       $object->title = $post->post_title ;
       $object->date = $post->post_date ;
       $object->link = esc_url(get_permalink( $post->ID )) ;
       $object->slug = $post->post_name ;
       $object->contentHTML = $theContent;
       $object->content = $postType ? parse_blocks($post->post_content) : '' ;
       $object->excerpt = get_the_excerpt($post->ID);
       $object->image =  bravepop_prepareImageData($post->ID);
       $object->categories = get_the_category($post->ID);
       $object->tags = get_the_tags($post->ID);
       $allPosts[] = $object;
   }

   $wpData->posts = $allPosts;
   return new WP_REST_Response($wpData);

}

function bravepop_prepareImageData( $postID ){
   $object = new stdClass();
   $imgDataArray = wp_get_attachment_image_src( get_post_thumbnail_id( $postID ), 'large', false );
   $object->url = $imgDataArray[0] ? $imgDataArray[0] : '';
   $object->width = $imgDataArray[1] ? $imgDataArray[1] : '';
   $object->height = $imgDataArray[2] ? $imgDataArray[2] : '';
   return $object;
}

function bravepop_get_translated_terms( ){
   $allTerms = array();
   if(class_exists( 'SitePress' )){
      global $sitepress;
      $original_lang = ICL_LANGUAGE_CODE; // Save the current language
      $currentLangs =  apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' );
      $allCats = array(); $allTags = array();
      
      foreach ($currentLangs as $key => $value) {
         $sitepress->switch_lang($key); // Switch to new language
         $pcats = get_terms(array( 'taxonomy' => 'product_cat', 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false));
         $ptags = get_terms(array( 'taxonomy' => 'product_tag', 'orderby' => 'name', 'order' => 'ASC', 'hide_empty' => false)); 
         //Get Categories
         foreach ($pcats as $index => $cat) {
            if($cat->term_id && !isset($allCats[$cat->term_id])){
               $allCats[$cat->term_id] = $cat;
            }
         }
         //Get Tags
         foreach ($ptags as $index => $tag) {
            if($tag->term_id && !isset($allTags[$tag->term_id])){
               $allTags[$tag->term_id] = $tag;
            }
         }
      }

      $allTerms['categories'] = $allCats;      
      $allTerms['tags'] = $allTags;   
      $sitepress->switch_lang($original_lang);
   }

   return $allTerms;
}
function bravepop_get_wpml_posts_n_pages( ){
   $original_lang = ICL_LANGUAGE_CODE; // Save the current language
   $languages = icl_get_languages( 'skip_missing=0' );
   $allWPMLPages = array(); $allWPMLPosts = array();  $allWPMLProducts = array();
   foreach( (array) $languages as $lang ) {
      do_action( 'wpml_switch_language', $lang['code'] ); 
      $pagesQ = get_pages(array('numberposts' => 300, 'suppress_filters' => false, 'lang' => '')); 
      $postsQ = get_posts(array('post_type' => 'post','numberposts' => 100, 'suppress_filters' => false, 'lang' => '')); 
      $productsQ = get_posts(array('post_type' => 'product','numberposts' => 100, 'suppress_filters' => false, 'lang' => '')); 
      foreach ( $pagesQ as $page ) {
         $pageItem = $page;
         $pageItem->post_title = isset($page->post_title) ? $page->post_title.' ('.strtoupper($lang['code']).')' : '';
         $allWPMLPages[$page->ID] = $pageItem->post_title;
      }
      foreach ( $postsQ as $post ) {
         $postItem = $post;
         $postItem->post_title = isset($post->post_title) ? $post->post_title.' ('.strtoupper($lang['code']).')' : '';
         $allWPMLPosts[$post->ID] = $postItem->post_title;
      }
      foreach ( $productsQ as $product ) {
         $productItem = $product;
         $productItem->post_title = isset($product->post_title) ? $product->post_title.' ('.strtoupper($lang['code']).')' : '';
         $allWPMLProducts[$product->ID] = $productItem->post_title;
      }
   }
   do_action( 'wpml_switch_language', $original_lang ); 
   return array('pages'=>$allWPMLPages, 'posts'=>$allWPMLPosts, 'products'=>$allWPMLProducts);
}

// add_filter('bravepop_posts_for_rest', 'bravepop_get_posts_for_rest', 10, 2);
// function bravepop_get_posts_for_rest($initialItems, $postType='') {

//    $allItems = $initialItems;
//    if($postType){
//       $totalItems = wp_count_posts( $postType );

//       if(isset($totalItems->publish) && ($totalItems->publish > 300)){
//          if($postType === 'product' && $allItems){
//             $loopCount = round($totalItems->publish/300);
//             for ($i=1; $i < $loopCount; $i++) { 
//                $qItems = wc_get_products( array( 'limit' => 300, 'page'=> $i, 'suppress_filters' => true, 'status' => 'publish', 'lang' => ''));
//                if(count($qItems) > 0){
//                   $allItems = array_merge($allItems, $qItems);
//                }
//             }
//          }
//       }

//    }

//    return $allItems;

//  }