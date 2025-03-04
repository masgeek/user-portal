<?php

namespace App;

/**
 * Main plugin class
 */
class GoogleDriveIntegration
{

    private static ?GoogleDriveIntegration $instance = null;

    private ?CredentialsHandler $credentials_handler;

    /**
     * Get singleton instance
     *
     * @return GoogleDriveIntegration
     */
    public static function get_instance(): GoogleDriveIntegration
    {
        if (null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        // Initialize credentials handler
        $this->credentials_handler = new CredentialsHandler(
            GDI_CREDENTIALS_DIR,
            GDI_CREDENTIALS_FILE
        );
        // Add hooks
        $this->add_hooks();
    }

    /**
     * Add WordPress hooks
     */
    private function add_hooks()
    {
        // Admin menu
        add_action('admin_menu', [ $this, 'add_admin_menu' ]);

        // Settings
        add_action('admin_init', [ $this, 'register_settings' ]);

        // AJAX handlers via credentials handler
        add_action('wp_ajax_gdi_test_connection', [ $this->credentials_handler, 'ajax_test_connection' ]);
        add_action('wp_ajax_gdi_save_credentials', [ $this->credentials_handler, 'ajax_save_credentials' ]);

        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ]);
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu()
    {
        add_menu_page(
            __('Google Drive Integration', 'google-drive-integration'),
            __('Google Drive', 'google-drive-integration'),
            'manage_options',
            'google-drive-integration',
            [ $this, 'display_admin_page' ],
            'dashicons-cloud'
        );

        add_submenu_page(
            'google-drive-integration',
            __('Files Browser', 'google-drive-integration'),
            __('Files Browser', 'google-drive-integration'),
            'manage_options',
            'google-drive-integration',
            [ $this, 'display_admin_page' ]
        );

        add_submenu_page(
            'google-drive-integration',
            __('Settings', 'google-drive-integration'),
            __('Settings', 'google-drive-integration'),
            'manage_options',
            'google-drive-integration-settings',
            [ $this, 'display_settings_page' ]
        );
    }

    /**
     * Initialize default settings (should be called only once)
     */
    private function initialize_default_settings(): void
    {
        // Option to track if defaults have been set
        $defaults_initialized = get_option('gdi_defaults_initialized', false);

        if (! $defaults_initialized ) {
            // Define default values
            $defaults = [
            'gdi_root_folder_id'        => '13-S74hrLKkT82t3yMaht6OegnC70aBxc',
            'gdi_service_account_email' => 'gdrive@fuelrod-87f7b.iam.gserviceaccount.com',
            'gdi_cache_duration'        => 3600 // 1 hour
            ];

            // Add default options
            foreach ( $defaults as $option_name => $default_value ) {
                add_option($option_name, $default_value);
            }

            // Mark defaults as initialized
            update_option('gdi_defaults_initialized', true);
        }
    }

    /**
     * Register plugin settings
     */
    public function register_settings(): void
    {
        // Initialize default settings if needed
        $this->initialize_default_settings();

        register_setting(
            'gdi_settings', 'gdi_root_folder_id', [
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => null
            ] 
        );

        register_setting(
            'gdi_settings', 'gdi_service_account_email', [
            'sanitize_callback' => 'sanitize_email',
            'default'           => null
            ] 
        );

        register_setting(
            'gdi_settings', 'gdi_cache_duration', [
            'sanitize_callback' => 'absint',
            'default'           => 3600 // 1 hour
            ] 
        );

        add_settings_section(
            'gdi_settings_section',
            __('Google Drive Settings', 'google-drive-integration'),
            [ $this, 'settings_section_callback' ],
            'gdi_settings'
        );

        add_settings_field(
            'gdi_service_account_json',
            __('Service Account JSON', 'google-drive-integration'),
            [ $this, 'service_account_json_callback' ],
            'gdi_settings',
            'gdi_settings_section'
        );

        add_settings_field(
            'gdi_service_account_email',
            __('Service Account Email', 'google-drive-integration'),
            [ $this, 'service_account_email_callback' ],
            'gdi_settings',
            'gdi_settings_section'
        );

        add_settings_field(
            'gdi_root_folder_id',
            __('Root Folder ID', 'google-drive-integration'),
            [ $this, 'root_folder_id_callback' ],
            'gdi_settings',
            'gdi_settings_section'
        );

        add_settings_field(
            'gdi_cache_duration',
            __('Cache Duration (seconds)', 'google-drive-integration'),
            [ $this, 'cache_duration_callback' ],
            'gdi_settings',
            'gdi_settings_section'
        );
    }

    /**
     * Settings section callback
     */
    public function settings_section_callback(): void
    {
        echo '<p>' . __('Configure your Google Drive integration settings.', 'google-drive-integration') . '</p>';

        // Add documentation panel
        ?>
        <div class="gdi-docs-panel">
            <h3><?php _e('Shortcode Usage', 'google-drive-integration'); ?></h3>
            <p><?php _e('To display Google Drive files on any page or post, use the following shortcode:', 'google-drive-integration'); ?></p>
            <pre><code>[gdrive-browser]</code></pre>
            <pre><code>[gdrive-browser-test]</code></pre>

            <p><?php _e('Optional parameters:', 'google-drive-integration'); ?></p>
            <ul>
                <li><code>folder_id</code>
                    - <?php _e('Specify a different folder ID than the default', 'google-drive-integration'); ?></li>
                <li><code>title</code> - <?php _e('Custom title for the browser', 'google-drive-integration'); ?></li>
                <li><code>show_breadcrumbs</code>
                    - <?php _e('Show or hide breadcrumb navigation (true/false)', 'google-drive-integration'); ?></li>
            </ul>

            <p><?php _e('Example with parameters:', 'google-drive-integration'); ?></p>
            <pre><code>[gdrive-browser folder_id="FOLDER_ID" title="My Documents" show_breadcrumbs="true"]</code></pre>

            <h3><?php _e('Cache Configuration', 'google-drive-integration'); ?></h3>
            <p><?php _e('Google Drive API responses are cached to improve performance. You can adjust the cache duration above.', 'google-drive-integration'); ?></p>
            <p><?php _e('Advanced cache configuration can be done by adding the following filters to your theme\'s functions.php file:', 'google-drive-integration'); ?></p>

            <h4><?php _e('Change Cache Adapter', 'google-drive-integration'); ?></h4>
            <pre><code>add_filter('gdi_cache_adapter', function() {
    return 'redis'; // Options: 'filesystem', 'array', 'redis', 'memcached'
});</code></pre>

            <h4><?php _e('Configure Cache Options', 'google-drive-integration'); ?></h4>
            <pre><code>add_filter('gdi_cache_options', function() {
    // Example for Redis adapter
    $redis = new \Redis();
    $redis->connect('127.0.0.1', 6379);

    return [
        'redis' => $redis,
        'namespace' => 'my-gdrive-cache'
    ];
});</code></pre>

            <h4><?php _e('Manually Clear Cache', 'google-drive-integration'); ?></h4>
            <p><?php _e('You can clear the cache programmatically:', 'google-drive-integration'); ?></p>
            <pre><code>
$cacheService = new \App\CacheService();
$cacheService->clearAll(); // Clear all cache
$cacheService->clear('specific-key'); // Clear specific item
                </code></pre>
        </div>
        <?php
    }

    /**
     * Service account JSON callback
     */
    public function service_account_json_callback(): void
    {
        $credentials_exist = file_exists(GDI_CREDENTIALS_FILE);
        ?>
        <div class="gdi-json-container">
            <textarea id="gdi_service_account_json" rows="10" cols="50"
                      class="large-text code"></textarea>
            <p class="description">
        <?php _e('Paste your Google Service Account JSON credentials here. The credentials will be securely encrypted and stored in a file, not in the database.', 'google-drive-integration'); ?>
            </p>

            <div class="gdi-credentials-status">
        <?php if ($credentials_exist ) : ?>
                    <div class="notice notice-success inline">
                        <p><?php _e('Service account credentials are already configured.', 'google-drive-integration'); ?></p>
                    </div>
                <?php else : ?>
                    <div class="notice notice-warning inline">
                        <p><?php _e('Service account credentials not found. Please add them using the field above.', 'google-drive-integration'); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <button type="button" id="gdi-save-credentials" class="button button-primary" style="margin-top: 10px;">
        <?php _e('Save Credentials', 'google-drive-integration'); ?>
            </button>
            <button type="button" id="gdi-test-connection" class="button button-secondary"
                    style="margin-top: 10px; margin-left: 5px;">
        <?php _e('Test Connection', 'google-drive-integration'); ?>
            </button>
            <span id="gdi-connection-status"></span>
        </div>
        <?php
    }

    /**
     * Service account email callback
     */
    public function service_account_email_callback(): void
    {
        $service_account_email = get_option('gdi_service_account_email');
        ?>
        <input type="email" id="gdi_service_account_email" name="gdi_service_account_email"
               value="<?php echo esc_attr($service_account_email); ?>" class="regular-text">
        <p class="description">
        <?php _e('Enter the service account email address (e.g., service-account-name@project-id.iam.gserviceaccount.com).', 'google-drive-integration'); ?>
        </p>
        <?php
    }

    /**
     * Root folder ID callback
     */
    public function root_folder_id_callback(): void
    {
        $root_folder_id = get_option('gdi_root_folder_id');
        ?>
        <input type="text" id="gdi_root_folder_id" name="gdi_root_folder_id"
               value="<?php echo esc_attr($root_folder_id); ?>" class="regular-text">
        <p class="description">
        <?php _e('Enter the Google Drive folder ID to use as the root folder for this integration. You can find this in the URL when viewing the folder in Google Drive.', 'google-drive-integration'); ?>
        </p>
        <?php
    }

    /**
     * Cache duration callback
     */
    public function cache_duration_callback(): void
    {
        $cache_duration = get_option('gdi_cache_duration', 3600);
        ?>
        <input type="number" id="gdi_cache_duration" name="gdi_cache_duration"
               value="<?php echo esc_attr($cache_duration); ?>" min="0" step="1" class="small-text">
        <p class="description">
        <?php _e('Duration in seconds to cache Google Drive API responses. Set to 0 to disable caching.', 'google-drive-integration'); ?>
        </p>
        <?php
    }

    /**
     * Display settings page
     */
    public function display_settings_page(): void
    {
        // Check user capabilities
        if (! current_user_can('manage_options') ) {
            return;
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
        <?php
        settings_fields('gdi_settings');
        do_settings_sections('gdi_settings');
        submit_button();
        ?>
            </form>
        </div>
        <?php
    }

    /**
     * Display admin page
     */
    public function display_admin_page(): void
    {
        // Check user capabilities
        if (! current_user_can('manage_options') ) {
            return;
        }

        // Check if credentials are configured
        $credentials_exist = file_exists(GDI_CREDENTIALS_FILE);
        $root_folder_id    = get_option('gdi_root_folder_id', '');

        if (! $credentials_exist || empty($root_folder_id) ) {
            ?>
            <div class="wrap">
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
                <div class="notice notice-warning">
                    <p><?php _e('Google Drive Integration is not configured. Please go to the settings page to configure your service account credentials and root folder ID.', 'google-drive-integration'); ?></p>
                    <p><a href="<?php echo admin_url('admin.php?page=google-drive-integration-settings'); ?>"
                          class="button button-primary"><?php _e('Go to Settings', 'google-drive-integration'); ?></a>
                    </p>
                </div>
            </div>
            <?php
            return;
        }

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <div class="notice notice-info">
                <p><?php _e('Google Drive file browser will be implemented in GoogleDriveService class.', 'google-drive-integration'); ?></p>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook The current admin page
     */
    public function enqueue_admin_assets( $hook ): void
    {
        if (! in_array(
            $hook, [
            'toplevel_page_google-drive-integration',
            'google-drive_page_google-drive-integration-settings'
            ] 
        ) 
        ) {
            return;
        }

        wp_enqueue_style(
            'gdi-admin-styles',
            GDI_PLUGIN_URL . 'assets/css/admin.css',
            [],
            GDI_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'gdi-admin-scripts',
            GDI_PLUGIN_URL . 'assets/js/admin.js',
            [ 'jquery' ],
            GDI_PLUGIN_VERSION,
            true
        );

        wp_localize_script(
            'gdi-admin-scripts', 'gdiData', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('gdi_nonce')
            ] 
        );
    }

}
