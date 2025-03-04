<?php

/**
 * The plugin bootstrap file
 *
 * @link              https://plugins360.com
 * @since             1.0.0
 * @package           Automatic_YouTube_Gallery
 *
 * @wordpress-plugin
 * Plugin Name:       Automatic YouTube Gallery
 * Plugin URI:        https://plugins360.com/automatic-youtube-gallery/
 * Description:       Create responsive, modern & dynamic video galleries by simply adding a YouTube USERNAME, CHANNEL, PLAYLIST, SEARCH KEYWORDS, or a custom list of YouTube URLs.
 * Version:           2.5.6
 * Author:            Team Plugins360
 * Author URI:        https://plugins360.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       automatic-youtube-gallery
 * Domain Path:       /languages
 * 
 */
// Exit if accessed directly
if ( !defined( 'WPINC' ) ) {
    die;
}
if ( function_exists( 'ayg_fs' ) ) {
    ayg_fs()->set_basename( false, __FILE__ );
    return;
}
// Current version of the plugin
if ( !defined( 'AYG_VERSION' ) ) {
    define( 'AYG_VERSION', '2.5.6' );
}
// Unique identifier of the plugin
if ( !defined( 'AYG_SLUG' ) ) {
    define( 'AYG_SLUG', 'automatic-youtube-gallery' );
}
// Path to the plugin directory
if ( !defined( 'AYG_DIR' ) ) {
    define( 'AYG_DIR', plugin_dir_path( __FILE__ ) );
}
// URL of the plugin
if ( !defined( 'AYG_URL' ) ) {
    define( 'AYG_URL', plugin_dir_url( __FILE__ ) );
}
// The plugin file name
if ( !defined( 'AYG_FILE_NAME' ) ) {
    define( 'AYG_FILE_NAME', plugin_basename( __FILE__ ) );
}
if ( !function_exists( 'ayg_fs' ) ) {
    // Create a helper function for easy SDK access
    function ayg_fs() {
        global $ayg_fs;
        if ( !isset( $ayg_fs ) ) {
            // Activate multisite network integration
            if ( !defined( 'WP_FS__PRODUCT_2922_MULTISITE' ) ) {
                define( 'WP_FS__PRODUCT_2922_MULTISITE', true );
            }
            // Include Freemius SDK
            require_once dirname( __FILE__ ) . '/vendor/freemius/start.php';
            $ayg_fs = fs_dynamic_init( array(
                'id'             => '2922',
                'slug'           => 'automatic-youtube-gallery',
                'type'           => 'plugin',
                'public_key'     => 'pk_7734619fa98d4e2b76a390a890739',
                'is_premium'     => false,
                'premium_suffix' => 'Premium',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'trial'          => array(
                    'days'               => 7,
                    'is_require_payment' => false,
                ),
                'menu'           => array(
                    'slug'       => 'automatic-youtube-gallery',
                    'first-path' => 'admin.php?page=automatic-youtube-gallery',
                ),
                'is_live'        => true,
            ) );
        }
        return $ayg_fs;
    }

    // Init Freemius
    ayg_fs();
    // Signal that SDK was initiated
    do_action( 'ayg_fs_loaded' );
}
if ( !function_exists( 'activate_ayg' ) ) {
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/activator.php
     */
    function activate_ayg() {
        require_once AYG_DIR . 'includes/activator.php';
        AYG_Activator::activate();
    }

    register_activation_hook( __FILE__, 'activate_ayg' );
}
if ( !function_exists( 'deactivate_ayg' ) ) {
    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/deactivator.php
     */
    function deactivate_ayg() {
        require_once AYG_DIR . 'includes/deactivator.php';
        AYG_Deactivator::deactivate();
    }

    register_deactivation_hook( __FILE__, 'deactivate_ayg' );
}
if ( !function_exists( 'run_ayg' ) ) {
    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since 1.0.0
     */
    function run_ayg() {
        require_once AYG_DIR . 'includes/init.php';
        $plugin = new AYG_Init();
        $plugin->run();
    }

    run_ayg();
}
if ( !function_exists( 'ayg_fs_uninstall_cleanup' ) ) {
    /**
     * Plugin uninstall cleanup.
     *
     * @since 1.0.0
     */
    function ayg_fs_uninstall_cleanup() {
        global $wpdb;
        // Delete all the plugin transients
        $transient_keys = get_option( 'ayg_transient_keys', array() );
        foreach ( $transient_keys as $key ) {
            delete_transient( $key );
        }
        // Delete all the plugin options
        delete_option( 'ayg_general_settings' );
        delete_option( 'ayg_gallery_settings' );
        delete_option( 'ayg_player_settings' );
        delete_option( 'ayg_livestream_settings' );
        delete_option( 'ayg_seo_settings' );
        delete_option( 'ayg_privacy_settings' );
        delete_option( 'ayg_gallery_page_ids' );
        delete_option( 'ayg_channel_ids' );
        delete_option( 'ayg_playlist_ids' );
        delete_option( 'ayg_transient_keys' );
        delete_option( 'ayg_version' );
        // Delete our custom database table "{$wpdb->prefix}ayg_videos"
        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}ayg_videos" );
    }

    ayg_fs()->add_action( 'after_uninstall', 'ayg_fs_uninstall_cleanup' );
}