<?php
   /**
    * Plugin Name: Brave Conversion Engine
    * Plugin URI:  https://getbrave.io
    * Description: A plugin to create highly effective conversion widgets & Popups to convert your visitors to leads or Customers.
    * Version:     0.7.6
    * Author:      Brave
    * Author URI:  https://getbrave.io/
    * Text Domain: bravepop
    * Domain Path: /languages
    * License:     GPL2+
    * License URI: http://www.gnu.org/licenses/gpl-2.0.html
    */
   $bravepop_settings = get_option('_bravepopup_settings');
   $bravepop_global = array('status_array'=> array(), 'current_popups'=>array(), 'autoEmbedded'=>array(), 'embededAssets'=>array(), 'user_country'=>'', 'user_country_code'=>'', 'user_geo_checked' => false, 'adblock_detect' => false);
   if (!defined('BRAVEPOP_PLUGIN_PATH')) {  define('BRAVEPOP_PLUGIN_FILE', __FILE__); }
   if (!defined('BRAVEPOP_WOO_ACTIVE')) {  define('BRAVEPOP_WOO_ACTIVE', in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )); }
   add_action( 'plugins_loaded', 'bravepop_require_files_free', 100 );
   function bravepop_require_files_free() {
      if ( !function_exists( 'bravepop_require_files_pro' ) ) {
         include __DIR__ . '/lib/helpers/dynamic.php';
         include __DIR__ . '/lib/rate-brave.php';
         include __DIR__ . '/lib/Analytics.php';
         include __DIR__ . '/includes.php';
      }
   }
   include __DIR__ . '/lib/integration/Uncanny/Uncanny.php';
   