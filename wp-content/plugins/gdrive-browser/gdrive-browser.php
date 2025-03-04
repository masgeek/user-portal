<?php
/**
 * Plugin Name: Google Drive Browser
 * Plugin URI: https://github.com/masgeek/gdrive-browser/releases
 * Description: A WordPress plugin to integrate Google Drive functionality and browse the files
 * Version: 1.0.0
 * Author: Masgeek
 * Author URI: https://github.com/masgeek
 * Text Domain: gdrive-browser
 * License: GPL-2.0+
 * Requires PHP: 8.1
 * Requires at least: 5.8
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
const GDI_PLUGIN_VERSION = '1.0.0';
define('GDI_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('GDI_PLUGIN_URL', plugin_dir_url(__FILE__));
const GDI_CACHE_DIR = GDI_PLUGIN_PATH . 'cache/';
const GDI_CREDENTIALS_FILE = GDI_PLUGIN_PATH . 'credentials/service-account.enc';
const GDI_CREDENTIALS_DIR = GDI_PLUGIN_PATH . 'credentials/';
const GDI_COMPOSER_PHAR = GDI_PLUGIN_PATH . 'composer.phar';

// Load Composer autoloader
$autoload_path = GDI_PLUGIN_PATH . 'vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
} else {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>Google Drive Browser: Missing vendor/autoload.php. Running composer install...</p></div>';
    });
    google_drive_install_composer();
}

// Import classes
use App\GoogleDriveIntegration;

// Initialize the plugin
add_action('plugins_loaded', 'google_drive_integration_init');
register_activation_hook(__FILE__, 'google_drive_integration_activate');

function google_drive_integration_init(): void
{
    GoogleDriveIntegration::get_instance();
    new \App\GoogleDriveShortCode();
}

/**
 * Checks and installs Composer if not found, then runs composer install --no-dev.
 */
function google_drive_install_composer(): void
{
    if (!is_dir(GDI_PLUGIN_PATH)) {
        return;
    }

    $composer_cmd = null;

    // Check if Composer is installed globally
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        exec('where composer', $output, $return_var);
    } else {
        exec('which composer', $output, $return_var);
    }

    if ($return_var === 0 && !empty($output)) {
        $composer_cmd = 'composer';
    } elseif (file_exists(GDI_COMPOSER_PHAR)) {
        $composer_cmd = 'php ' . escapeshellarg(GDI_COMPOSER_PHAR);
    } else {
        if (file_exists(GDI_COMPOSER_PHAR)) {
            $composer_cmd = 'php ' . escapeshellarg(GDI_COMPOSER_PHAR);
        } else {
            add_action('admin_notices', function () {
                echo '<div class="error"><p>Google Drive Browser: Composer installation failed. Please install Composer manually.</p></div>';
            });
            return;
        }
    }

    // Run composer install --no-dev
    $command = 'cd ' . escapeshellarg(GDI_PLUGIN_PATH) . ' && ' . $composer_cmd . ' install --no-dev';
    exec($command);
}


/**
 * Create necessary directories on plugin activation and run composer install if needed.
 */
function google_drive_integration_activate(): void
{
    global $wpdb;

    // Check PHP version requirement
    if (version_compare(PHP_VERSION, '8.1', '<')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('Google Drive Integration requires PHP 8.1 or higher.');
    }

    // Create cache directory if it doesn't exist
    if (!file_exists(GDI_CACHE_DIR)) {
        wp_mkdir_p(GDI_CACHE_DIR);
    }

    // Run Composer installation and setup if needed
    google_drive_install_composer();
}
