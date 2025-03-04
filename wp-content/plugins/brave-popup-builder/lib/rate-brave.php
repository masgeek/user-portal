<?php
/**
 *  Brave Review Notice.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'BravePopup_Review_Notice' ) ) {
    class BravePopup_Review_Notice {

      function __construct() {
         add_action( 'init', array( $this, 'init' ) );
      }

      public function init(){
         if ( ! is_admin() ) {   return;  }
         if ( !empty(get_option('brave_plugin_rated')) ) { return; }
         $totalpopups = get_posts( array( 'numberposts' => 2, 'post_status' => 'publish',  'post_type' => 'popup' ) );
         if(count($totalpopups) === 0){ return; }

         add_action( 'wp_ajax_bravepop_review_ajax', array( $this, 'bravepop_review_ajax' ) );
         add_action( 'admin_notices', array( $this, 'bravepop_review_notice' ) );
         add_action( 'admin_print_footer_scripts', array( $this, 'bravepop_review_script' ) );
      }

      public function bravepop_review_ajax() {
         check_ajax_referer( 'bravepop-plugin-review', 'security' );
         if ( ! isset( $_POST['type'] ) ) {   wp_die( 'ok' );   }

         update_option( 'brave_plugin_rated', true );
         wp_die( 'ok' );
   
      }
      
      public function bravepop_review_script(){ ?>
         <script>
            function bravePop_review_submit(action_type){
               var bravepop_review_request = new XMLHttpRequest();
               bravepop_review_request.open('POST', '<?php echo esc_url(admin_url( 'admin-ajax.php' )) ?>', true);
               bravepop_review_request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded' );
               bravepop_review_request.onload = function () {  
                  document.getElementById('bravepop-review-notice').style.display = 'none';
               };
               bravepop_review_request.onerror = function(error) {  console.log(error);   };
               
               var bravePopuReviewDataArray = [];
               var bravepop_review_ajaxData = { security: '<?php echo esc_html(wp_create_nonce( "bravepop-plugin-review" )); ?>', action: 'bravepop_review_ajax', type: action_type };
               Object.keys(bravepop_review_ajaxData).forEach(function(element) {
                  bravePopuReviewDataArray.push(  encodeURIComponent(element) + "=" + encodeURIComponent(bravepop_review_ajaxData[element]) ) 
               });
               var bravePopuReviewSend = bravePopuReviewDataArray.join("&");
               bravepop_review_request.send( bravePopuReviewSend );
            }

         </script>
         
      <?php }
        
      public function bravepop_review_notice(){
            $icon = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" focusable="false" width="1em" height="1em" style="-ms-transform: rotate(360deg); -webkit-transform: rotate(360deg); transform: rotate(360deg);" preserveAspectRatio="xMidYMid meet" viewBox="0 0 36 36"><path fill="#FFCC4D" d="M36 18c0 9.941-8.059 18-18 18S0 27.941 0 18S8.059 0 18 0s18 8.059 18 18"/><path fill="#664500" d="M18 21.849c-2.966 0-4.935-.346-7.369-.819c-.557-.106-1.638 0-1.638 1.638c0 3.275 3.763 7.369 9.007 7.369s9.007-4.094 9.007-7.369c0-1.638-1.082-1.745-1.638-1.638c-2.434.473-4.402.819-7.369.819"/><path fill="#DD2E44" d="M16.65 3.281a4.666 4.666 0 0 0-8.884.254a4.666 4.666 0 0 0-4.225-.58A4.67 4.67 0 0 0 .692 8.911c.122.344.284.663.472.958c1.951 3.582 7.588 6.1 11.001 6.131c2.637-2.167 5.446-7.665 4.718-11.677a4.712 4.712 0 0 0-.233-1.042zm2.7 0a4.67 4.67 0 0 1 5.956-2.85a4.67 4.67 0 0 1 2.929 3.104a4.666 4.666 0 0 1 4.225-.58a4.671 4.671 0 0 1 2.85 5.956a4.72 4.72 0 0 1-.473.958c-1.951 3.582-7.588 6.1-11.002 6.131c-2.637-2.167-5.445-7.665-4.717-11.677c.037-.348.112-.698.232-1.042z"/></svg>';
            $messages = esc_html__( "Hi there! Great to see you have created an Awesome Popup with Brave Conversion Engine. If you like the plugin please consider rating it. It would mean the world to us.", 'bravepop' );
            $rateBtn = 'Rate';
            $cancelBtn = "I don't want to Rate";
         ?>
         
            <div id="bravepop-review-notice" class="notice notice-success is-dismissible" style="margin-top:30px; font-weight: 600; font-size: 15px;">
               <p style="font-size: 15px;"><?php echo esc_html( $icon.' '.$messages ); ?></p>
               <p class="actions">
                  <a id="bravepop-rate" onclick="bravePop_review_submit('rated')" href="https://wordpress.org/support/plugin/brave-popup-builder/reviews/#new-post" target="_blank" class="button button-primary bravepop-review-button"><span class="dashicons dashicons-yes" style="position: relative; top: 4px; margin-left: -8px; width: 20px; height: 20px;"></span> <?php echo esc_html($rateBtn);?></a>
                  <a id="bravepop-no-rate" href="#" onclick="bravePop_review_submit('not_rated')" style="margin-left:10px"><?php echo esc_html($cancelBtn);?></a>
               </p>
            </div>
       <?php }

    }
    

   if ( ! class_exists( 'BravePop_Geolocation' ) ) {
      new BravePopup_Review_Notice();
   }
}

?>