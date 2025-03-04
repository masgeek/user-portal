<?php
/**
 *  Brave Deal Notice.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'BravePopup_Deal_Notice' ) ) {
    class BravePopup_Deal_Notice {

      function __construct() {
         add_action( 'init', array( $this, 'init' ) );
      }

      public function init(){
         if ( ! is_admin() ) {   return;  }
         if ( !empty(get_option('brave_bfcm2020')) ) { return; }
         $expired = strtotime(date('Y-m-d', strtotime('2020-12-04') ) ); //Do not Show after Dec 4th.
         if($expired < time()){ return; }

         add_action( 'wp_ajax_bravepop_deal_ajax', array( $this, 'bravepop_deal_ajax' ) );
         add_action( 'admin_notices', array( $this, 'bravepop_deal_notice' ) );
         add_action( 'admin_print_footer_scripts', array( $this, 'bravepop_review_script' ) );
      }

      public function bravepop_deal_ajax() {
         check_ajax_referer( 'bravepop-deal', 'security' );
         if ( ! isset( $_POST['type'] ) ) {   wp_die( 'ok' );   }

         update_option( 'brave_bfcm2020', true );
         wp_die( 'ok' );
   
      }
      
      public function bravepop_review_script(){ ?>
         <script>
            function bravePop_hide_deal(action_type){
               var bravepop_review_request = new XMLHttpRequest();
               bravepop_review_request.open('POST', '<?php echo esc_url(admin_url( 'admin-ajax.php' )) ?>', true);
               bravepop_review_request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded' );
               bravepop_review_request.onload = function () {  
                  document.getElementById('bravepop-deal-notice').style.display = 'none';
               };
               bravepop_review_request.onerror = function(error) {  console.log(error);   };
               
               var bravePopuReviewDataArray = [];
               var bravepop_review_ajaxData = { security: '<?php echo esc_html(wp_create_nonce( "bravepop-deal" )); ?>', action: 'bravepop_deal_ajax', type: action_type };
               Object.keys(bravepop_review_ajaxData).forEach(function(element) {
                  bravePopuReviewDataArray.push(  encodeURIComponent(element) + "=" + encodeURIComponent(bravepop_review_ajaxData[element]) ) 
               });
               var bravePopuReviewSend = bravePopuReviewDataArray.join("&");
               bravepop_review_request.send( bravePopuReviewSend );
            }

         </script>
         
      <?php }
        
      public function bravepop_deal_notice(){
            $icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="1.8em" height="1.6em" style="position: relative; top: 4px;" preserveAspectRatio="xMidYMid meet" viewBox="0 0 36 36"><path fill="#FDD888" d="M33 31c0 2.2-1.8 4-4 4H7c-2.2 0-4-1.8-4-4V14c0-2.2 1.8-4 4-4h22c2.2 0 4 1.8 4 4v17z"/><path fill="#FDD888" d="M36 11c0 2.2-1.8 4-4 4H4c-2.2 0-4-1.8-4-4s1.8-4 4-4h28c2.2 0 4 1.8 4 4z"/><path fill="#FCAB40" d="M3 15h30v2H3z"/><path fill="#DA2F47" d="M19 3h-2a3 3 0 0 0-3 3v29h8V6a3 3 0 0 0-3-3z"/><path fill="#DA2F47" d="M16 7c1.1 0 1.263-.516.361-1.147L9.639 1.147a1.795 1.795 0 0 0-2.631.589L4.992 5.264C4.446 6.219 4.9 7 6 7h10zm4 0c-1.1 0-1.263-.516-.361-1.147l6.723-4.706a1.796 1.796 0 0 1 2.631.589l2.016 3.527C31.554 6.219 31.1 7 30 7H20z"/></svg>';
            $messages = esc_html__( "Brave Black Friday Deal!! Get 30% OFF on Brave PRO! Offer available for limited time only!", 'bravepop' );
            $rateBtn = 'View Deal';
            $cancelBtn = "Hide Message";
         ?>
         
            <div id="bravepop-deal-notice" class="notice notice-success is-dismissible" style="margin-top:30px; font-weight: 600; font-size: 15px;">
               <p style="font-size: 15px;">
                  <?php echo esc_html( $icon.' '.$messages ); ?> 
                  <a id="bravepop-view-deal" onclick="bravePop_hide_deal('viewed')" href="https://getbrave.io/brave-popup-builder-pro-upgrade/" target="_blank" class="button button-primary bravepop-review-button" style="margin-left: 20px; background: #5d70e2; border-color: transparent;"><?php echo esc_html($rateBtn);?></a>
                  <a id="bravepop-dismiss-deal" href="#" onclick="bravePop_hide_deal('dismissed')" style="margin-left:10px;color: #5d70e2;"><?php echo esc_html($cancelBtn);?></a>
               </p>

            </div>
       <?php }

    }
    

   if ( ! class_exists( 'BravePop_Geolocation' ) ) {
      new BravePopup_Deal_Notice();
   }
}

?>