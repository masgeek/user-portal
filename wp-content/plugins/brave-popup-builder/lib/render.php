<?php
/**
 * Popup Renderer
 * Finds the Popup assigned to current page and renders it.
**/

add_action('wp_head', 'bravepop_render_popup', 9);
function bravepop_render_popup() {
   $brave_popupID = filter_input(INPUT_GET, 'brave_popup');
   $brave_popupStep = filter_input(INPUT_GET, 'popup_step');

   //Popup Preview
   if($brave_popupID && is_user_logged_in()){ 
      return new BravePop_Popup( $brave_popupID, 'popup', true, $brave_popupStep  ? absint($brave_popupStep) : false); 
   }

   //Bail if is Customizing the Website from Appearance > Customize or with Elementor
   $is_elemntor_page_editor = filter_input(INPUT_GET, 'elementor-preview');
   if(is_customize_preview() || isset($is_elemntor_page_editor)){
      return;
   }

   $filtered_popups = bravepop_get_current_page_popups();
   $filtered_popups = apply_filters("bravepop_rendarable_campaigns", $filtered_popups);

   do_action( 'bravepop_before_render', array($filtered_popups) );
   
   //error_log(wp_json_encode($filtered_popups));
   global $bravepop_settings;
   $appSettings = isset($bravepop_settings['app_settings']) ? $bravepop_settings['app_settings'] : new stdClass();
   $hideForAdmins = isset($appSettings->hideForAdmins) ? wp_json_encode($appSettings->hideForAdmins) : 'false'; 
   $hiddenForAdmins = current_user_can('administrator') && $hideForAdmins === 'true' ? true : false;

   if($filtered_popups && count($filtered_popups) > 0 && !$hiddenForAdmins){
      foreach($filtered_popups as $key=>$value) {
         $popupID = $value->id;
         $popupType = $value->type;
         $popupStatus = $value->status;

         if($popupStatus === 'publish' ){
            //Check if Popup has active ABTest. If does, display a variation randomly
            $post_abtest = json_decode(get_post_meta( $popupID, 'popup_abtest', true ));
            //error_log(wp_json_encode($post_abtest));
            if(isset($post_abtest->active) && $post_abtest->active === true && count($post_abtest->items) > 0){
               $popupID = get_AbTest_Campaign($post_abtest, $popupID);
            }

            new BravePop_Popup($popupID, $popupType);
         }

         //Handle Popup Schedule
         if($popupStatus === 'scheduled' ){
            $post_schedule = json_decode(get_post_meta( $popupID, 'popup_schedule', true ));
            if(!empty($post_schedule->active) && !empty($post_schedule->type)){
               if(($post_schedule->type === 'days' && count($post_schedule->days) > 0) || ($post_schedule->type === 'dates' && count($post_schedule->dates) > 0)){
                  new BravePop_Popup($popupID, $popupType);
               }     
            }
         }

      }
   }
   
   do_action( 'bravepop_after_render', array($filtered_popups) );
}



function bravepop_get_current_page_popups(){
   $fit_popups = array();
   global $bravepop_settings; global $bravepop_global;
   $currentPopups = isset($bravepop_settings['visibility']) ? $bravepop_settings['visibility'] : null;
   $pageInfo = bravepop_get_current_pageInfo();
   //echo wp_json_encode($currentPopups);
   $currentPageType = $pageInfo->type;
   $currentPageID = $pageInfo->pageID;
   $currentSingleType = $pageInfo->singleType;



   if($currentPopups && is_array($currentPopups)){
      //Get Popup Status
      $popupStatusArray = array();
      $allPublishedPopups = get_posts( array( 'numberposts' => -1, 'post_status' => 'publish',  'post_type' => 'popup', 'suppress_filters' => true, 'lang' => '' ) );
      $allScheduledPopups = get_posts( array( 'numberposts' => -1, 'post_status' => 'draft',  'post_type' => 'popup', 'suppress_filters' => true, 'lang' => '', 
         'meta_query' => array( array( 'key' => 'popup_schedule', 'value' => 'true','compare' => 'LIKE' ) ) 
      ) );
      foreach ($allScheduledPopups as $key => $item) {   $popupStatusArray[$item->ID] = 'scheduled'; }
      foreach ($allPublishedPopups as $key => $item) {   $popupStatusArray[$item->ID] = $item->post_status; }
      //error_log(wp_json_encode($popupStatusArray));

      //echo wp_json_encode($currentPopups);
      foreach($currentPopups as $key=>$value) {
         $popupID = $value->id;
         $itemType = !empty($value->type) ? $value->type : 'popup' ;
         $placement = $value->placement;
         $placementType = $placement && isset($placement->placementType) ? $placement->placementType : 'sitewide';
         $popupData = new stdClass(); 
         $popupData->type = $itemType; 
         $popupData->id = $popupID; 
         $popupData->status = isset($popupStatusArray[$popupID]) ? $popupStatusArray[$popupID] : 'draft';
         $popupData->exclude = isset($placement->exclude) ? $placement->exclude : new stdClass();
         if(isset($placement->popup_exclude_filter) && $placement->popup_exclude_filter === true && isset($placement->exclude_popups) && is_array($placement->exclude_popups)){
            $popupData->exclude_popups =  $placement->exclude_popups;
         }

         if($itemType === 'content' && $placementType === 'selected'){
            $popupData->placement = $placement;
            $popupData->matchedPage =  $currentPageID;
         }

         //Check if has ad block detection enabled
         if(isset($placement->adblock_check) && !empty($placement->adblock_check) && !$bravepop_global['adblock_detect']){
            $bravepop_global['adblock_detect'] = true;
         }

 
         //error_log( wp_json_encode($placementType));
         if($placementType === 'sitewide'){
            $fit_popups[] = $popupData;
         }elseif ($currentPageType === 'front' && $placementType === 'front'){
            $fit_popups[] = $popupData;
         }elseif (isset($placement) && ($placementType === 'selected')){
               //IF is Page
               if($currentPageType === 'front' && isset($placement->pages) && is_array($placement->pages) && (in_array( 'front', $placement->pages) || in_array( $currentPageID, $placement->pages) )){
                     $fit_popups[] = $popupData;
               }elseif($currentPageType === 'search'&& isset($placement->pages) && is_array($placement->pages) && in_array( 'search', $placement->pages)){
                  $fit_popups[] = $popupData;
               }elseif($currentPageType === 'notfound'&& isset($placement->pages) && is_array($placement->pages) && in_array( '404', $placement->pages)){
                  $fit_popups[] = $popupData;
               }elseif($currentPageType === 'single' && $currentSingleType === 'page'&& isset($placement->pages) && is_array($placement->pages) && isset($placement->pages)){
                  if(isset($placement->pages[0]) && $placement->pages[0] === 'all'){
                     $fit_popups[] = $popupData;
                  }elseif($currentPageID && isset($placement->pages) && is_array($placement->pages) && in_array( $currentPageID, $placement->pages)){
                     $fit_popups[] = $popupData;
                  }elseif(isset($placement->pages) && is_array($placement->pages) && in_array( 989898989898, $placement->pages)){
                     //Woocommerce Thank You Page
                     global $wp;
                     if( isset($wp->query_vars['order-received']) ){
                        $fit_popups[] = $popupData;
                     }
                  }
               //IF is Post
               }elseif($currentPageType === 'single' && $currentSingleType === 'post'){
                  if(isset($placement->posts[0]) && $placement->posts[0] === 'all'){
                     $fit_popups[] = $popupData;
                  }else{
                     global $post;
                     if(isset($post->ID) && isset($placement->posts_with_tags) && is_array( $placement->posts_with_tags) && (count( $placement->posts_with_tags) > 0) ){
                        $postTerms = get_the_terms( $post->ID, 'post_tag' );
                        $currentPostTags = array();
                        if ( ! empty( $postTerms ) && ! is_wp_error( $postTerms ) ){  foreach ( $postTerms as $term ){ if(isset($term->term_id)){ $currentPostTags[] = $term->term_id; }  }  }
                        $hasTags = is_array($currentPostTags) && count($currentPostTags) > 0 ? true : false;
                        $matchedTags = $hasTags ? array_intersect($currentPostTags, $placement->posts_with_tags) : array();
                        if(count($matchedTags) > 0){
                           $fit_popups[] = $popupData;
                        }
                     } 
                     if(isset($post->ID) && !empty($placement->posts_with_cats) && is_array( $placement->posts_with_cats) && (count( $placement->posts_with_cats) > 0)){
                        $postTerms = get_the_terms( $post->ID, 'category' );
                        $currentPostCats = array();
                        if ( ! empty( $postTerms ) && ! is_wp_error( $postTerms ) ){  foreach ( $postTerms as $term ) { if(isset($term->term_id)){ $currentPostCats[] = $term->term_id; }  }  }
                        $hasTags = is_array($currentPostCats) && count($currentPostCats) > 0 ? true : false;
                        $matchedTags = $hasTags ? array_intersect($currentPostCats, $placement->posts_with_cats) : array();
                        if(count($matchedTags) > 0){
                           $fit_popups[] = $popupData;
                        }
                     }
                     if($currentPageID && isset($placement->posts) && is_array( $placement->posts) && (count( $placement->posts) > 0) && in_array( $currentPageID, $placement->posts)){
                        $fit_popups[] = $popupData;
                     }  
                  }
               //IF is Product
               }elseif($currentPageType === 'single' && $currentSingleType === 'product'){
                  if(isset($placement->products[0]) && $placement->products[0] === 'all'){
                     $fit_popups[] = $popupData;
                  }else{
                     global $post;
                     
                     if(isset($post->ID) && isset($placement->products_with_tags) && is_array( $placement->products_with_tags) && (count( $placement->products_with_tags) > 0) ){
                        $productTerms = get_the_terms( $post->ID, 'product_tag' );
                        $currentProductTags = array();
                        if ( ! empty( $productTerms ) && ! is_wp_error( $productTerms ) ){  foreach ( $productTerms as $term ){ if(isset($term->term_id)){ $currentProductTags[] = $term->term_id; }  }  }
                        $hasTags = is_array($currentProductTags) && count($currentProductTags) > 0 ? true : false;
                        $matchedTags = $hasTags ? array_intersect($currentProductTags, $placement->products_with_tags) : array();
                        if(count($matchedTags) > 0){
                           $fit_popups[] = $popupData;
                        }
                     } 
                     if(isset($post->ID) && !empty($placement->products_with_cats) && is_array( $placement->products_with_cats) && (count( $placement->products_with_cats) > 0)){
                        $productTerms = get_the_terms( $post->ID, 'product_cat' );
                        $currentProductTags = array();
                        if ( ! empty( $productTerms ) && ! is_wp_error( $productTerms ) ){  foreach ( $productTerms as $term ) { if(isset($term->term_id)){ $currentProductTags[] = $term->term_id; }  }  }
                        $hasTags = is_array($currentProductTags) && count($currentProductTags) > 0 ? true : false;
                        $matchedTags = $hasTags ? array_intersect($currentProductTags, $placement->products_with_cats) : array();
                        
                        if(count($matchedTags) > 0){
                           $fit_popups[] = $popupData;
                        }
                     }
                     if($currentPageID && isset($placement->products) && is_array( $placement->products) && (count( $placement->products) > 0) && in_array( $currentPageID, $placement->products)){
                        $fit_popups[] = $popupData;
                     }
                  }

               //IF is Category
               }elseif($currentPageType === 'category' && isset($placement->categories) && is_array($placement->categories)){
                  if(isset($placement->categories[0]) && $placement->categories[0] === 'all'){
                     $fit_popups[] = $popupData;
                  }elseif($currentPageID && in_array( $currentPageID, $placement->categories)){
                     $fit_popups[] = $popupData;
                  }
               //IF is Product Category
               }elseif($currentPageType === 'tax' && $currentSingleType === 'product_cat' && isset($placement->product_categories) && is_array($placement->product_categories)){
                  if(isset($placement->product_categories[0]) && $placement->product_categories[0] === 'all'){
                     $fit_popups[] = $popupData;
                  }elseif($currentPageID && in_array( $currentPageID, $placement->product_categories)){
                     $fit_popups[] = $popupData;
                  }
               //IF is Custom Post Type
               }elseif(isset($placement->post_types) && is_array($placement->post_types) && count($placement->post_types) > 0 && is_singular($placement->post_types)){
                  $fit_popups[] = $popupData;
               //IF is Custom Post Type Taxonomy
               }elseif(isset($placement->post_types_taxes) && $currentPageType === 'tax' && in_array($currentSingleType, $placement->post_types_taxes)){
                  $fit_popups[] = $popupData;
               }elseif(isset($placement->cpt_posts) && ($currentPageType === 'single')){
                  global $post;
                  if(isset($post->ID)){
                     foreach ($placement->cpt_posts as $key => $selectedCPT) {
                        if(isset($selectedCPT->ID) && isset($selectedCPT->type) && $post->ID === $selectedCPT->ID && $selectedCPT->type === $currentSingleType){
                           $fit_popups[] = $popupData;
                        }
                     }
                  }
               }
         }elseif($placementType === 'custom' ){
            $current_page_link = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            //error_log( wp_json_encode($current_page_link));
            if($current_page_link && isset($placement->urls) && count($placement->urls) > 0){
               foreach($placement->urls as $key=>$urlItem) {
                  if(!empty($urlItem)){
                     if(isset($urlItem->link) && ($current_page_link === $urlItem->link)){
                        $fit_popups[] = $popupData;
                     }
                     if(isset($urlItem->link) && strpos($urlItem->link, "*") !== false && (strpos( $current_page_link, str_replace('*','', $urlItem->link) ) !== false)){
                        $fit_popups[] = $popupData;
                     }
                  }
               }
            }
         }
      }

   }

   //Remove Popups that matches The excluded pages filter
   $filtered_popups = bravepop_exclude_pages($fit_popups, $currentPageType, $currentSingleType, $currentPageID);

   //Remove Popups if another popup is present
   $filtered_popups = bravepop_exclude_popups($filtered_popups);

   //If the Popup is Hidden from a certain page, do not show it.
   if(is_singular() && get_the_ID()){
      $brave_hidden_popups = get_post_meta( get_the_ID(), 'brave_hidden_popups', true ) ? get_post_meta( get_the_ID(), 'brave_hidden_popups', true ) : array();
      if($filtered_popups && count($filtered_popups) > 0){
         foreach($filtered_popups as $key=>$popupData) {
            if(in_array( $popupData->id, $brave_hidden_popups )){
               unset($filtered_popups[$key]);
            }
         }
      }
   }
   //error_log('$filtered_popups: '. wp_json_encode($filtered_popups));

   //Set Auto Embedded Content & Check if Popup has Ad blocker detection enabled.
   if(count($filtered_popups) > 0){
      $autoEmbedded = array();
      foreach ($filtered_popups as $key => $popup) {
         if((isset($popup->type) && $popup->type === 'content') && (isset($popup->status) && $popup->status === 'publish') && (isset($popup->placement->placementType) && $popup->placement->placementType === 'selected')){
            $autoEmbedded[] = $popup;
         }
      }
      $bravepop_global['autoEmbedded'] = $autoEmbedded;
   }

   //Apply Custom Filters to let developers force show a certain campaign based on their own logic
   if(count($filtered_popups) > 0){
      foreach ($filtered_popups as $key => $popup) {
         $popup_loadable = apply_filters( 'bravepop_campaign_is_loadable', false, $popup->id );
         if($popup_loadable === true){
            $popup->status = 'publish';
            $filtered_popups[$key] = $popup;
         }
      }
   }

   // error_log(wp_json_encode($filtered_popups));

   $bravepop_global['current_popups'] = $filtered_popups;
   $filtered_popups = apply_filters( 'bravepop_loadable_campaigns', $filtered_popups );

   return $filtered_popups;
}

function bravepop_get_current_pageInfo(){
   
   $pageInfo = new stdClass();
   $currentPageType = '';
   $currentSingleID = '';
   $currentSingleType = '';

   global $wp_query;

   if ( $wp_query->is_page ) {
      if(is_front_page()){
         $currentPageType = 'front';
         $currentSingleID = isset($wp_query->post->ID) ? $wp_query->post->ID : false;
      }else{
         $currentPageType = 'single';
         if(isset($wp_query->post)){
            $currentSingleID = $wp_query->post->ID;
            $currentSingleType = $wp_query->post->post_type;
         }
      }
   } elseif ( $wp_query->is_home ) {
       $currentPageType = 'front';
       $currentSingleID = isset($wp_query->post->ID) ? $wp_query->post->ID : false;
   } elseif ( $wp_query->is_single ) {
      if(( $wp_query->is_attachment )){
         $currentPageType = 'attachment';
      }else{
         $currentPageType = 'single';
         if(isset($wp_query->post)){
            $currentSingleID = $wp_query->post->ID;
            $currentSingleType = $wp_query->post->post_type;
         }
      }

   } elseif ( $wp_query->is_category ) {
       $currentPageType = 'category';
       $currentSingleID = $wp_query->queried_object_id;
   } elseif ( $wp_query->is_tag ) {
       $currentPageType = 'tag';
   } elseif ( $wp_query->is_tax ) {
       $currentPageType = 'tax';
       if($wp_query->queried_object->taxonomy){
         $currentSingleType = $wp_query->queried_object->taxonomy;
       }
       if(isset($wp_query->queried_object->term_id)){
         $currentSingleID = $wp_query->queried_object->term_id;
       }

   } elseif ( $wp_query->is_archive ) {
      $currentPageType = 'archive';
      if(isset($wp_query->query['post_type'])){
         $currentSingleType = $wp_query->query['post_type'];
      }
   } elseif ( $wp_query->is_search ) {
       $currentPageType = 'search';
   } elseif ( $wp_query->is_404 ) {
       $currentPageType = 'notfound';
   }
   
   //If User selected Woocommerce Shop page and the current page is the shop Archive, the current pagetype from archive to single
   if($currentPageType==='archive' && $currentSingleType === 'product' && get_option( 'woocommerce_shop_page_id' )){
      $currentPageType = 'single';
      $currentSingleType = 'page';
      $currentSingleID = get_option( 'woocommerce_shop_page_id' );
   }

   //If Page ID returns zero, collect id from the queried object.
   if($currentSingleType === 'page' && isset($wp_query->queried_object->ID)){
      $currentSingleID = $wp_query->queried_object->ID;
   }

   //Handle the pages that are set to Posts Page from Settings > Reading
   if(!empty($wp_query->is_home) && !empty($wp_query->is_posts_page) && !empty($wp_query->queried_object_id)){
      $currentPageType = 'single';
      $currentSingleID = $wp_query->queried_object_id;
      $currentSingleType = 'page';
   }

   $pageInfo->type = $currentPageType;
   $pageInfo->pageID = $currentSingleID;
   $pageInfo->singleType = $currentSingleType;
   //error_log(wp_json_encode($pageInfo));
   return $pageInfo;
}

function bravepop_exclude_pages($popups, $currentPageType, $currentSingleType, $currentPageID){
   $fit_popups = $popups;
   foreach($popups as $key=>$popupData) {
      if(isset($popupData->exclude)){
         $exclude = $popupData->exclude;
         //Pages
         if( isset($exclude->pages) && is_array($exclude->pages) && count($exclude->pages) > 0 &&
         ($currentPageType === 'front' || $currentPageType === '404' || $currentPageType === 'search' || ($currentPageType === 'single' && $currentSingleType === 'page'))){
            if($currentPageType === 'front' && in_array( 'front', $exclude->pages)){
               unset($fit_popups[$key]);
            }elseif($currentPageType === 'search'&& in_array( 'search', $exclude->pages)){
               unset($fit_popups[$key]);
            }elseif($currentPageType === 'notfound'&& in_array( '404', $exclude->pages)){
               unset($fit_popups[$key]);
            }elseif(($currentPageType === 'single' && $currentSingleType === 'page') && in_array( $currentPageID, $exclude->pages )){
               unset($fit_popups[$key]);
            }
         }
         //Posts
         if( isset($exclude->posts) && is_array($exclude->posts) && count($exclude->posts) > 0 && ($currentPageType === 'single' && $currentSingleType === 'post') 
         && in_array( $currentPageID, $exclude->posts )){
            unset($fit_popups[$key]);
         }
         //Products
         if( isset($exclude->products) && is_array($exclude->products) && count($exclude->products) > 0 && ($currentPageType === 'single' && $currentSingleType === 'product') 
         && in_array( $currentPageID, $exclude->products )){
            unset($fit_popups[$key]);
         }

         //Custom URL
         $current_page_link = (isset($_SERVER['HTTPS']) ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
         
         if($current_page_link && !empty($exclude->urls) && count($exclude->urls) > 0){
            foreach($exclude->urls as $ukey=>$urlItem) {
               if(isset($urlItem->link) && ($current_page_link === $urlItem->link)){
                  unset($fit_popups[$key]);
               }
               if(isset($urlItem->link) && strpos($urlItem->link, "*") !== false && (strpos( $current_page_link, str_replace('*','', $urlItem->link) ) !== false)){
                  unset($fit_popups[$key]);
               }
            }
         }

         //IF is Custom Post Type
         if(isset($exclude->post_types) && is_array($exclude->post_types) && count($exclude->post_types) > 0 && is_singular($exclude->post_types)){
            unset($fit_popups[$key]);
         }
         //IF is Custom Post Type Taxonomy
         if(isset($exclude->post_types_taxes) && $currentPageType === 'tax' && in_array($currentSingleType, $exclude->post_types_taxes)){
            unset($fit_popups[$key]);
         }
         //IF is Custom Posts
         if(isset($exclude->cpt_posts) && ($currentPageType === 'single')){
            global $post;
            if(isset($post->ID)){
               foreach ($exclude->cpt_posts as $key => $selectedCPT) {
                  if(isset($selectedCPT->ID) && isset($selectedCPT->type) && $post->ID === $selectedCPT->ID && $selectedCPT->type === $currentSingleType){
                     unset($fit_popups[$key]);
                  }
               }
            }
         }

      }

   }
   
   return $fit_popups;

}

function bravepop_exclude_popups($popups){
   $fit_popups = $popups;
   $allPopupIDs = [];
   foreach($popups as $key=>$popupData) {
      if(isset($popupData->id) && isset($popupData->type) && isset($popupData->status) && $popupData->status === 'publish'){
         $allPopupIDs[] = $popupData->id;
      }
   }

   foreach($popups as $key=>$popupData) {
      if(isset($popupData->exclude_popups) && isset($popupData->exclude_popups) && is_array($popupData->exclude_popups) && count($popupData->exclude_popups) > 0 ){
         if(count($allPopupIDs) > 0 && count(array_intersect($allPopupIDs, $popupData->exclude_popups)) > 0){
            unset($fit_popups[$key]);
         }
      }
   }
   return $fit_popups;

}

function get_AbTest_Campaign($post_abtest, $parentID){
   $popupVariations = array();
   $last_served = $post_abtest->last_served;
   $popupID = $parentID;
   $ViewsArray = [];
   
   foreach ($post_abtest->items as $index => $popItem) {
      if(isset($post_abtest->random) && $post_abtest->random === false){
         $ViewsArray[] = array('id'=> $popItem->id, 'views'=> intval(get_post_meta( $popItem->id, 'popup_views', true)) );
      }else{
         if($last_served && intval($last_served) === $popItem){
         }else{
            $popupVariations[] = $popItem->id;
         }
      }

   }

   if(isset($post_abtest->random) && $post_abtest->random === false){
      //Serve Equally
      usort($ViewsArray, function($previous, $next) {
         return $previous["views"] > $next["views"] ? 1 : -1;
     });
     if(isset($ViewsArray[0]['id'])){
      $popupID = $ViewsArray[0]['id'];
     }else{
      $popupID = $popupVariations[array_rand($popupVariations)];
     }
   }else{
      //Serve Randomly
      $popupID = $popupVariations[array_rand($popupVariations)];
   }

   return $popupID;
}


add_action('wp_ajax_bravepop_ajax_load_popup_content', 'bravepop_ajax_load_popup_content', 0);
add_action('wp_ajax_nopriv_bravepop_ajax_load_popup_content', 'bravepop_ajax_load_popup_content');
function bravepop_ajax_load_popup_content(){
   if(!isset($_POST['popupID']) || !isset($_POST['security']) || !isset($_POST['type'])){ wp_die(); }
   check_ajax_referer('brave-ajax-nonce', 'security');
   $Popup = new BravePop_Popup(intval($_POST['popupID']), $_POST['type']);
   $currentURL = isset($_POST['current_url']) ? $_POST['current_url'] : ''; 
   $popupContent = $Popup->popup_render_content(true, $currentURL);

   echo $popupContent;

   wp_die();
}