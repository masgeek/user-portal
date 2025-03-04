<?php

/**
 * Plugin Name:       Kenta Companion
 * Description:       Kenta Companion is an extension to the Kenta theme. It provides a lot of features and one-click demo import for Kenta Theme.
 * Requires at least: 5.4
 * Requires PHP:      7.4
 * Version:           1.3.1
 * Author:            WP Moose
 * Author URI:        https://www.wpmoose.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kenta-companion
 *
 */
// Exit if accessed directly.
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Defining plugin constants.
 *
 * @since 1.0.0
 */
define( 'KCMP_VERSION', '1.3.1' );
define( 'MIN_KENTA_VERSION', '1.4.0' );
define( 'KCMP_PLUGIN_FILE', __FILE__ );
define( 'KCMP_PLUGIN_PATH', trailingslashit( plugin_dir_path( KCMP_PLUGIN_FILE ) ) );
define( 'KCMP_PLUGIN_URL', trailingslashit( plugins_url( '/', KCMP_PLUGIN_FILE ) ) );
define( 'KCMP_ASSETS_PATH', KCMP_PLUGIN_PATH . 'assets/' );
define( 'KCMP_ASSETS_URL', KCMP_PLUGIN_URL . 'assets/' );
define( 'KCMP_DEMO_SITE_URL', 'https://kentatheme.com/' );
// Require must files
require_once KCMP_PLUGIN_PATH . 'mu.php';
if ( function_exists( 'kenta_fs' ) ) {
    kenta_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'kenta_fs' ) ) {
        // Create a helper function for easy SDK access.
        function kenta_fs() {
            global $kenta_fs;
            if ( !isset( $kenta_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $kenta_fs = fs_dynamic_init( array(
                    'id'             => '10804',
                    'slug'           => 'kenta-companion',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_64db37825bd0972890eb37821be91',
                    'is_premium'     => false,
                    'premium_suffix' => 'Premium',
                    'anonymous_mode' => true,
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'menu'           => array(
                        'slug'    => 'kenta-companion',
                        'pricing' => false,
                        'contact' => true,
                        'support' => false,
                    ),
                    'is_live'        => true,
                ) );
            }
            return $kenta_fs;
        }

        // Init Freemius.
        kenta_fs();
        // Signal that SDK was initiated.
        do_action( 'kenta_fs_loaded' );
    }
    add_action( 'admin_enqueue_scripts', 'kcmp_enqueue_admin_scripts' );
    // Kenta theme not match requirement
    $kenta_version = wp_get_theme( 'kenta' )->get( 'Version' );
    if ( $kenta_version !== false && version_compare( $kenta_version, MIN_KENTA_VERSION, '<' ) ) {
        if ( is_admin() ) {
            add_action( 'admin_notices', 'kcmp_kenta_need_upgrade_notice' );
        }
        // Don't load plugin on incompatible kenta version
        return;
    }
    /**
     * Including composer autoloader globally.
     *
     * @since 1.0.0
     */
    require_once KCMP_PLUGIN_PATH . 'vendor/autoload.php';
    /**
     * Activation hook
     *
     * @since v1.2.2
     */
    register_activation_hook( __FILE__, 'kcmp_bust_cache' );
    /**
     * Run plugin after all others plugins
     *
     * @since 1.0.0
     */
    add_action( 'plugins_loaded', function () {
        \KentaCompanion\Core\Bootstrap::run();
    } );
}