<?php

namespace App;

/**
 * Class CredentialsHandler
 * Handles Google Drive API credential operations
 */
class CredentialsHandler
{
    /**
     * Path to credentials directory
     *
     * @var string
     */
    private string $credentials_dir;

    /**
     * Path to credentials file
     *
     * @var string
     */
    private string $credentials_file;

    /**
     * Constructor
     *
     * @param string $credentials_dir  Directory to store credentials
     * @param string $credentials_file Path to credentials file
     */
    public function __construct( string $credentials_dir, string $credentials_file )
    {
        $this->credentials_dir  = $credentials_dir;
        $this->credentials_file = $credentials_file;
    }

    /**
     * AJAX handler for saving credentials
     */
    public function ajax_save_credentials(): void
    {
        // Check permissions
        $permission_check = $this->check_ajax_permissions('nonce');
        if (is_wp_error($permission_check) ) {
            wp_send_json_error([ 'message' => $permission_check->get_error_message() ]);
        }

        $encoded_credentials = $_POST['credentials'] ?? '';
        // Decode the URL-encoded JSON string
        $credentials_json = urldecode($encoded_credentials);

        // Validate credentials
        $decoded = $this->validate_credentials($credentials_json);
        if (is_wp_error($decoded) ) {
            wp_send_json_error([ 'message' => $decoded->get_error_message() ]);

            return;
        }

        // Ensure credentials directory exists
        $dir_check = $this->ensure_credentials_directory();
        if (is_wp_error($dir_check) ) {
            wp_send_json_error([ 'message' => $dir_check->get_error_message() ]);

            return;
        }

        // Encrypt the credentials
        $encrypted = $this->encryptData($credentials_json);

        // Write encrypted credentials to file
        $result = file_put_contents($this->credentials_file, $encrypted);

        if (false === $result ) {
            wp_send_json_error(
                [
                'message' => 'Failed to save credentials. Please check file permissions.'
                ] 
            );

            return;
        }

        // If the credentials include an email address, extract and save it
        if (! empty($decoded['client_email']) ) {
            update_option('gdi_service_account_email', $decoded['client_email']);
        }

        // Success response
        wp_send_json_success(
            [
            'message' => 'Credentials saved successfully.'
            ] 
        );
    }

    /**
     * AJAX handler for testing connection
     */
    public function ajax_test_connection(): void
    {
        // Check permissions
        $permission_check = $this->check_ajax_permissions('nonce');
        if (is_wp_error($permission_check) ) {
            wp_send_json_error([ 'message' => $permission_check->get_error_message() ]);
        }

        // First try to get credentials from the POST request
        $credentials_json = urldecode($_POST['credentials']) ?? '';

        // If POST is empty, fallback to the credentials file
        if (empty($credentials_json) && file_exists($this->credentials_file) ) {
            $encrypted        = file_get_contents($this->credentials_file);
            $credentials_json = $this->decryptData($encrypted);
        }

        // Validate credentials
        $decoded = $this->validate_credentials($credentials_json);

        if (is_wp_error($decoded) ) {
            wp_send_json_error([ 'message' => $decoded->get_error_message() ]);

            return;
        }

        // For demo purposes, we'll return success if JSON validation passes
        wp_send_json_success(
            [
            'data'    => $decoded,
            'message' => __('JSON format validated. Connection test will be implemented in GoogleDriveService.', 'google-drive-integration')
            ] 
        );
    }

    /**
     * Common validation functionality for credentials
     *
     * @param string $credentials_json JSON string containing credentials
     *
     * @return array|\WP_Error Decoded credentials array or WP_Error on failure
     */
    private function validate_credentials( $credentials_json )
    {
        if (empty($credentials_json) ) {
            return new \WP_Error('empty_credentials', 'Service account credentials are required');
        }

        // Validate JSON format
        $decoded = json_decode($credentials_json, true);
        if (json_last_error() !== JSON_ERROR_NONE ) {
            return new \WP_Error('invalid_json', 'Invalid JSON format: ' . json_last_error_msg());
        }

        // Check for required fields
        $required_fields = [ 'type', 'project_id', 'private_key_id', 'private_key', 'client_email' ];
        foreach ( $required_fields as $field ) {
            if (empty($decoded[ $field ]) ) {
                return new \WP_Error('missing_field', "Missing required field: {$field}");
            }
        }

        return $decoded;
    }

    /**
     * Ensure credentials directory exists with proper protection
     *
     * @return bool|\WP_Error True on success, WP_Error on failure
     */
    private function ensure_credentials_directory()
    {
        if (file_exists($this->credentials_dir) ) {
            return true;
        }

        if (! wp_mkdir_p($this->credentials_dir) ) {
            return new \WP_Error('directory_creation_failed', 'Failed to create credentials directory. Please check file permissions.');
        }

        // Create .htaccess file to protect credentials
        $htaccess_content = "# Prevent direct access to credentials\n";
        $htaccess_content .= "<Files \"*\">\n";
        $htaccess_content .= "  Require all denied\n";
        $htaccess_content .= "</Files>\n";
        file_put_contents($this->credentials_dir . '.htaccess', $htaccess_content);

        // Create index.php to prevent directory listing
        file_put_contents($this->credentials_dir . 'index.php', '<?php // Silence is golden');

        return true;
    }

    /**
     * Check permissions for AJAX requests
     *
     * @param string $nonce_value The nonce value to verify
     *
     * @return bool|\WP_Error True if authorized, WP_Error otherwise
     */
    private function check_ajax_permissions( $nonce_value )
    {
        // Check nonce
        if (! check_ajax_referer('gdi_nonce', $nonce_value, false) ) {
            return new \WP_Error('invalid_nonce', 'Security check failed');
        }

        // Check user capabilities
        if (! current_user_can('manage_options') ) {
            return new \WP_Error('insufficient_permissions', 'Insufficient permissions');
        }

        return true;
    }


    /**
     * Encrypt credentials using WordPress salts
     *
     * @param string $data Data to encrypt
     *
     * @return string Encrypted data
     */
    private function encryptData( string $data ): string
    {
        if (! function_exists('sodium_crypto_secretbox') ) {
            // Fallback if sodium not available
            return base64_encode($data);
        }

        $key       = substr(hash('sha256', AUTH_KEY . AUTH_SALT, true), 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
        $nonce     = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $encrypted = sodium_crypto_secretbox($data, $nonce, $key);

        return base64_encode($nonce . $encrypted);
    }

    /**
     * Decrypt credentials using WordPress salts
     *
     * @param string $encrypted Encrypted data
     *
     * @return string Decrypted data
     */
    public function decryptData( string $encrypted ): string
    {
        if (! function_exists('sodium_crypto_secretbox_open') ) {
            // Fallback if sodium not available
            return base64_decode($encrypted);
        }

        $decoded = base64_decode($encrypted);
        if ($decoded === false ) {
            return '';
        }

        $key        = substr(hash('sha256', AUTH_KEY . AUTH_SALT, true), 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
        $nonce_size = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;

        if (strlen($decoded) < $nonce_size ) {
            return '';
        }

        $nonce      = substr($decoded, 0, $nonce_size);
        $ciphertext = substr($decoded, $nonce_size);

        $data = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
        if ($data === false ) {
            return '';
        }

        return $data;
    }
}
