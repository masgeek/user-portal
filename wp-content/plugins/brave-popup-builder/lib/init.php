<?php
/**
 * Brave Dashboard.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
//Initialize The Plugin Settings
if( is_admin() ){
    new BravePopup_Settings();
}


if ( ! class_exists( 'BravePopup_Initialize' ) ) {
    class BravePopup_Initialize {
        function __construct() {
            add_action( 'admin_menu', array( $this, 'add_dashboard_page' ) );
            add_action( 'admin_bar_menu', array( $this, 'bravepopu_admin_topbar' ) , 100);
            add_action( 'admin_enqueue_scripts', array( $this, 'bravepop_inline_script' ));
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_dashboard_script' ) );
            $pluginDir = plugin_basename(plugin_dir_path(dirname( __FILE__) ));
            $pluginBaseFile = $pluginDir.'/index.php';
            add_filter( 'plugin_action_links_'.$pluginBaseFile, array($this, 'bravepop_plugin_action_links') );
            add_action( 'admin_init', array( $this, 'remove_emoji_scripts' ) );
            //add_action( 'admin_init', array( $this, 'redirect_to_welcome_page' ) );
        }

        public function add_dashboard_page() {
         $svg = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="25px" height="30px" viewBox="0 0 25 30" enable-background="new 0 0 25 30" xml:space="preserve">
                  <path id="Shape_1_copy_5_2_" fill="#FFFFFF" d="M6.01,3.659v14.62c0,0-0.446,8.17,6.891,8.17c7.829,0,7.977-7.298,7.977-7.298
                     s0.87-7.031-7.132-7.031h-1.16v4.29c0,0,0.126-0.104,0.696-0.058c1.884,0.076,2.519,1.003,2.608,2.275
                     c0.282,2.241-1.954,2.668-2.294,2.681c-1.18,0.033-2.507-0.553-2.664-2.046V7.953c0.081-0.627-0.186-1.249-0.695-1.625
                     C9.187,5.6,6.009,2.847,6.009,2.847S6.002,2.882,6.01,3.659z"/>
                  </svg>';

            add_menu_page(  __( 'Brave', 'bravepop' ), __( 'Brave', 'bravepop' ), 'access_brave_menus', 'bravepop', array( $this, 'bravepop_welcome_content' ), 'data:image/svg+xml;base64,' . base64_encode( $svg ) );
            add_submenu_page( 'bravepop', 'All Campaigns', 'All Campaigns', 'access_brave_menus', 'bravepop', array( $this, 'bravepop_welcome_content' ) );
            add_submenu_page( 'bravepop', 'Submissions', 'Submissions', 'access_brave_menus', 'bravepop-submissions', array( $this, 'bravepop_submissions_content' ));
            add_submenu_page( 'bravepop', 'Integrations', 'Integrations', 'access_brave_menus', 'bravepop-integrations', array( $this, 'bravepop_integrations_content' ));
            add_submenu_page( 'bravepop', 'Analytics', 'Analytics', 'access_brave_menus', 'bravepop-analytics', array( $this, 'bravepop_analytics_content' ));
            add_submenu_page( 'bravepop', 'Settings', 'Settings', 'manage_options', 'bravepop-settings', array( $this, 'bravepop_settings_content' ));
        }

        public function enqueue_dashboard_script( $hook ) {

            if ( is_admin() ) {
                //error_log(wp_json_encode($hook));
                $current_url =  "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

               if ( 'toplevel_page_bravepop' === $hook || 'brave_page_bravepop-integrations' === $hook || 'brave_page_bravepop-analytics' === $hook || 'brave_page_bravepop-submissions' === $hook || 'brave_page_bravepop-settings' === $hook) {
                     wp_enqueue_media();
                     wp_register_script( 'tinymce-js', includes_url().'js/tinymce/tinymce.min.js' , '', '', true );
                     wp_register_style( 'tinymce-css', includes_url().'css/editor.min.css' , '', '', 'all' );
                     wp_register_style( 'bravepop-fontawesome-css', BRAVEPOP_PLUGIN_PATH . 'assets/fonts/fontawesome.css' , '', '', 'all' );

                     wp_register_script( 'react-js', includes_url().'js/dist/vendor/react.min.js' , '', '', true );
                     wp_register_script( 'reactdom-js', includes_url().'js/dist/vendor/react-dom.min.js' , '', '', true );

                     wp_enqueue_script( 'bravepop-popups', BRAVEPOP_PLUGIN_PATH . 'assets/js/admin_popups.js', array( 'wp-i18n', 'react-js', 'reactdom-js', 'jquery-ui-selectable', 'jquery-ui-sortable', 'jquery-ui-resizable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'tinymce-js' ), false, true);
                     //wp_set_script_translations( 'bravepop-popups', 'bravepop', plugin_dir_path(dirname( __FILE__) ) . 'languages/'  );
                     if(function_exists('wp_set_script_translations')){
                        wp_set_script_translations( 'bravepop-popups', 'bravepop'  );
                     }
                     wp_enqueue_style('bravepop-fontawesome-css');
                     wp_enqueue_style('tinymce-css');
                     wp_enqueue_style('bravepop_admin_css',  BRAVEPOP_PLUGIN_PATH . 'assets/css/admin.min.css');
               }
               if(('brave_page_bravepop-submissions' === $hook || 'brave_page_bravepop-analytics' === $hook) && class_exists('BravePop_Geolocation')){
                  wp_enqueue_script( 'papaparse-js', BRAVEPOP_PLUGIN_PATH . 'assets/js/exportcsv.js', array(),'1.1.0', true );
               }
               
            }
            
        }

   
        public function bravepopu_admin_topbar($admin_bar){
            if(is_admin()){
               $current_screen = get_current_screen();
               if( $current_screen->id === 'toplevel_page_bravepop'){
                  $admin_bar->add_menu( array(
                     'id'    => 'brave-documentation',
                     'title' => 'Brave Documentation',
                     'href'  => 'https://getbrave.io/brave-documentation/',
                     'meta'  => array(
                           'title' => __('Brave Documentation', 'bravepop'),  
                           'target' => '_blank'          
                     ),
                  ));
               }
            }
         }

        public function bravepop_inline_script() {
            $current_url =  "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $currentpage = ''; $popup_title = "";
            $page = filter_input(INPUT_GET, 'page');
            if(!empty($page) && strpos($page, "bravepop") === false){ return; }
            if(($page === 'bravepop' || $page === 'bravepop-analytics' || $page === 'bravepop-submissions') && isset($_GET['id'])){  
                $popup_id = (int)$_GET['id'];
                $popup_title = get_the_title( $popup_id );
            }else{
                $popup_id = "null";
            };

            if($page === 'bravepop' ){  
                $currentpage = 'popups';
            }else if($page  === 'bravepop' && isset($_GET['id'])){
               $currentpage = 'popup_editor';
            }else if($page === 'bravepop-integrations'){
               $currentpage = 'integrations';
            }else if($page === 'bravepop-settings'){
               $currentpage = 'settings';
            }else if($page === 'bravepop-analytics'){
               $currentpage = 'analytics';
            }else if($page === 'bravepop-submissions'){
               $currentpage = 'submissions';
            }

            $woocommerce_pages = new stdClass();
            $active = class_exists('BravePop_Geolocation') ? get_option('bravepop_license_status', false) : false ;
            $lcnse = class_exists('BravePop_Geolocation') ? get_option('bravepop_license_key') : false ;
            ?>
            <script>
                //JS Variables
                //get_option('stylesheet')
                var currentheme_screenshot = '';
                

                <?php  $themes = wp_get_themes();
                    foreach( $themes as $theme ){ ?>
                    <?php if(get_option('stylesheet') === $theme->get_stylesheet()) { ?>
                        currentheme_screenshot = "<?php print_r(esc_url($theme->get_screenshot())); ?>";
                    <?php } ?>

                <?php  } ?>
                <?php
                     //Get Enabled Integrations
                     $enabledServices = array();$enabledServices = array();$uaf_fonts = array();
                     $currentSettings = get_option('_bravepopup_settings');
                     $welcomeTour = isset($currentSettings['welcome_tour']) && $currentSettings['welcome_tour'] ? $currentSettings['welcome_tour'] : false;
                     $currentIntegrations = $currentSettings && isset($currentSettings['integrations']) ? $currentSettings['integrations'] : array() ;
                     $emailvalidator = $currentSettings && isset($currentSettings['emailvalidator']) ? $currentSettings['emailvalidator'] : array() ;
                     $customFonts = $currentSettings && isset($currentSettings['fonts']) ? $currentSettings['fonts'] : array() ;
                     $appSettings = $currentSettings && isset($currentSettings['app_settings']) ? $currentSettings['app_settings'] : false ;
                     foreach( $currentIntegrations as $key=>$service ){
                        if(isset($service->enabled) && $service->enabled === true){
                           $enabledServices[] = $key;
                           //If Zoho is Integrated, make zoho crm integration marked true
                           if($key === 'zoho'){  $enabledServices[] = 'zohocrm'; }
                        }
                     }
                     
                     if (function_exists('uaf_get_font_families')){
                        $uaf_fonts = uaf_get_font_families(); // Returns Array
                        foreach ($uaf_fonts as $key => $value) {
                           $uafont = new stdClass(); $uafont->id = str_replace(' ','_',$value); $uafont->name = $value; $uafont->url = 'UAF';
                           $customFonts[] = $uafont;
                        }
                     }

                     if (class_exists(\MailPoet\API\API::class)) {   $enabledServices[] = 'mailpoet';  }
                     if (class_exists('TNP')) {    $enabledServices[] = 'tnp';  }
                     if (function_exists('fluentcrm_get_option')) {   $enabledServices[] = 'fluentcrm';   }
                     if (function_exists('mailster')) {   $enabledServices[] = 'mailster';  }

                     //Get Polylang Languages
                     $languages = array('type'=>'', 'langs'=> array());
                     if(function_exists('pll_languages_list')){
                        $languages['type'] = 'Polylang';
                        $languages['langs'] = pll_languages_list();
                     }
                     //Get WPML Languages
                     if(class_exists( 'SitePress' )){
                        $languages['type'] = 'WPML';
                        $currentLangs =  apply_filters( 'wpml_active_languages', NULL, 'orderby=id&order=desc' );
                        foreach ($currentLangs as $key => $value) {
                           $languages['langs'][] = $key;
                        }
                     }
                     //Get WeGlot Languages
                     if(function_exists('weglot_get_languages_available') ) {
                        $languages['type'] = 'WeGlot';
                        $currentLangs = weglot_get_languages_available();
                        foreach ($currentLangs as $code => $value) {
                           $languages['langs'][] = $code;
                        }
                     }
                ?>
                var bravepopup_settings_vars__ = {
                    'isPro': false,
                    'active': <?php print_r(wp_json_encode(($active ? $active : false)));?>,
                    'lcnse': "<?php print_r($lcnse);?>",
                    'root': "<?php print_r(esc_url_raw( rest_url() ));?>",
                    'ajax_url': "<?php print_r(esc_url(admin_url( 'admin-ajax.php' )));?>",
                    'rest_identifier': "<?php print_r(get_option('permalink_structure') ? '?' : '&');?>", 
                    'nonce': "<?php print_r(esc_html(wp_create_nonce( 'wp_rest' ))); ?>",
                    'esp_nonce': "<?php print_r(esc_html(wp_create_nonce( 'esp_nonce' ))); ?>",
                    'admin_email': "<?php print_r(sanitize_email(get_option('admin_email'))); ?>",
                    'site_name': "<?php print_r(get_bloginfo('name')); ?>",
                    'current_url' :"<?php print_r(esc_url_raw($current_url)); ?>",
                    'current_page' :"<?php print_r(esc_attr($currentpage)); ?>",
                    'home_url': "<?php print_r(esc_url(get_home_url()));?>",
                    'popup_id' : <?php print_r(absint($popup_id)); ?>,
                    'popup_title' : "<?php print_r($popup_title);?>",
                    'integrations': <?php print_r(wp_json_encode($enabledServices));?>,
                    'email_validation': <?php print_r(wp_json_encode($emailvalidator));?>,
                    'app_settings': <?php print_r(wp_json_encode($appSettings));?>,
                    'theme_screenshot': currentheme_screenshot,
                    'plugin_url': "<?php print_r(esc_url(BRAVEPOP_PLUGIN_PATH)); ?>",
                    'woocommerce': <?php in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ? print_r(wp_json_encode(true)) : print_r(wp_json_encode(false)); ?>,
                    'woocommerce_pages': <?php print_r(wp_json_encode($woocommerce_pages));?>,
                    'welcome_tour': '<?php print_r($welcomeTour); ?>',
                    'languages': <?php print_r(wp_json_encode($languages));?>,
                    'learnDash': <?php print_r(wp_json_encode( function_exists('ld_course_list') ? true : false ));?> ,
                    'edd': <?php print_r(wp_json_encode( class_exists( 'Easy_Digital_Downloads' ) ? true : false ));?> ,
                    'roles':<?php global $wp_roles; isset($wp_roles->role_names) ? print_r(wp_json_encode($wp_roles->role_names)) : '{}' ;?>,
                    'customFonts': <?php print_r(wp_json_encode($customFonts)); ?>,
                    'UAFonts': <?php print_r(wp_json_encode($uaf_fonts)); ?>,
                    'isCloudFlare': <?php print_r(wp_json_encode( isset($_SERVER['HTTP_CF_VISITOR']) ? true : false ));?> ,
                    'timezone': "<?php function_exists('date_default_timezone_get') ? print_r(date_default_timezone_get()) : '';?>",
                    'showRatingBox' : <?php print_r(wp_json_encode(!get_option('brave_plugin_rated', false) && wp_count_posts('popup')->publish > 1 ? true : false)); ?>,
                }
            </script>
            <?php
        }
        
        public function bravepop_welcome_content() {
            ?>
            <div class="wrap">
                <div id="opti_popup"></div>
            </div>
            <?php
        }
        public function bravepop_settings_content() {
         ?>
         <div class="wrap">
             <div id="opti_popup" class="bravepop_settings"></div>
         </div>
         <?php
     }

        public function bravepop_integrations_content() {
            ?>
            <div class="wrap">
                <div id="opti_popup" class="bravepop_integrations"></div>
            </div>
            <?php
        }
        public function bravepop_analytics_content() {
            ?>
            <div class="wrap">
               <div id="opti_popup" class="bravepop_analytics"></div>
            </div>
            <?php
         }

         public function bravepop_submissions_content() {
            ?>
            <div class="wrap">
               <div id="opti_popup" class="bravepop_submissions"></div>
            </div>
            <?php
         }
         
        /**
         * Adds a marker to remember to redirect after activation.
         * Redirecting right away will not work.
         */
        public static function start_redirect_to_welcome_page() {
            update_option( 'bravepop_redirect_to_welcome', '1' );
        }

        /**
         * Redirect to the welcome screen if our marker exists.
         */
        public function redirect_to_welcome_page() {
            if ( get_option( 'bravepop_redirect_to_welcome' ) ) {
                delete_option( 'bravepop_redirect_to_welcome' );
                wp_redirect( esc_url( admin_url( 'admin.php?page=bravepop' ) ) );
                wp_die();
            }
        }

        public function bravepop_plugin_action_links($actions)
        {
            $custom_actions = array(
               'popups' => sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=bravepop' ), __( 'Campaigns', 'bravepop' ) ),
               'docs'   => sprintf( '<a href="%s" target="_blank">%s</a>', 'https://getbrave.io/brave-documentation/', __( 'Documentation', 'bravepop' ) ),
            );
    
            // add the links to the front of the actions list
            return array_merge( $custom_actions, $actions );
        }

        
         public function remove_emoji_scripts() {
            $page = filter_input(INPUT_GET, 'page');
            if(!empty($page) && strpos($page, 'bravepop') !== false){
               remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
               remove_action( 'admin_print_styles', 'print_emoji_styles' );
            }
         }
        
    }

    new BravePopup_Initialize();
}
?>