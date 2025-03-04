<?php

namespace App;

use Psr\Cache\InvalidArgumentException;

/**
 * Google Drive Browser - handles shortcode, display and AJAX functionality
 */
class GoogleDriveShortCode
{

    /**
     * @var GoogleDriveService
     */
    private GoogleDriveService $driveService;

    /**
     * @var array File type icon mappings
     */
    private static array $fileTypeIcons = [
        'folder' => 'fa-folder',
        'document' => 'fa-file-alt',
        'spreadsheet' => 'fa-file-excel',
        'presentation' => 'fa-file-powerpoint',
        'pdf' => 'fa-file-pdf',
        'image' => 'fa-file-image',
        'text' => 'fa-file-alt',
        'archive' => 'fa-file-archive',
        'video' => 'fa-file-video',
        'audio' => 'fa-file-audio',
        'generic' => 'fa-file'
    ];

    /**
     * @var array MIME type to file type mappings
     */
    private static array $mimeTypeMap = [
        'application/vnd.google-apps.folder' => 'folder',
        'application/vnd.google-apps.document' => 'document',
        'application/vnd.google-apps.spreadsheet' => 'spreadsheet',
        'application/vnd.google-apps.presentation' => 'presentation',
        'application/pdf' => 'pdf',
        'image/jpeg' => 'image',
        'image/png' => 'image',
        'image/gif' => 'image',
        'text/plain' => 'text',
        'text/csv' => 'spreadsheet',
        'application/zip' => 'archive',
        'video/mp4' => 'video',
        'audio/mpeg' => 'audio'
    ];

    private string $rootFolderId;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->registerHooks();
    }

    /**
     * Register hooks and shortcode
     */
    private function registerHooks(): void
    {
        // Register shortcode
        add_shortcode('gdrive-browser-test', [$this, 'helloWorldShortCode']);
        add_shortcode('gdrive-browser', [$this, 'renderShortcode']);

        // Register scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'registerAssets']);

        // Register AJAX handlers
        add_action('wp_ajax_gdrive_change_folder', [$this, 'ajaxChangeFolder']);
        add_action('wp_ajax_nopriv_gdrive_change_folder', [$this, 'ajaxChangeFolder']);
    }

    /**
     * Register scripts and styles
     */
    public function registerAssets()
    {
        // Font Awesome from CDN
        wp_register_style(
            'font-awesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css',
            [],
            '5.15.4'
        );

        wp_register_style(
            'gdrive-browser-style',
            GDI_PLUGIN_URL . 'assets/css/gdrive-browser.css',
            ['font-awesome'],
            GDI_PLUGIN_VERSION
        );

        // Add theme color support for Twenty Twenty-One and other themes that use CSS variables
        wp_add_inline_style('gdrive-browser-style', $this->getThemeIntegrationCSS());

        wp_register_script(
            'gdrive-browser-script',
            GDI_PLUGIN_URL . 'assets/js/gdrive-browser.js',
            ['jquery'],
            GDI_PLUGIN_VERSION,
            true
        );

        wp_localize_script(
            'gdrive-browser-script', 'gdriveData', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('gdrive_nonce'),
                'rootFolderId' => get_option('gdi_root_folder_id', '')
            ]
        );
    }

    /**
     * Get current theme colors for JavaScript
     *
     * @return array Theme colors
     */
    private function getThemeColors()
    {
        // Default colors
        $colors = [
            'primary' => '#4285f4',
            'secondary' => '#5f6368',
            'background' => '#ffffff',
            'foreground' => '#202124',
            'border' => '#e0e0e0',
        ];

        // Try to detect theme colors - this will vary by theme
        if (function_exists('get_theme_mod')) {
            // Try to get colors from theme mods
            $primary_color = get_theme_mod('primary_color', '');
            if (!empty($primary_color)) {
                $colors['primary'] = $primary_color;
            }

            $link_color = get_theme_mod('link_color', '');
            if (!empty($link_color)) {
                $colors['primary'] = $link_color;
            }
        }

        return $colors;
    }

    /**
     * Generate additional CSS for theme integration
     *
     * @return string CSS rules
     */
    private function getThemeIntegrationCSS()
    {
        $css = '';

        // Current theme
        $theme = wp_get_theme();
        $theme_name = strtolower($theme->get('Name'));

        // Check for specific themes and add optimizations
        if (strpos($theme_name, 'twenty twenty-two') !== false
            || strpos($theme_name, 'twenty twenty-three') !== false
        ) {
            // Block theme support
            $css .= '
            .gdrive-browser-container {
                --wp--style--global--content-size: 100%;
                max-width: var(--wp--style--global--content-size);
                margin-left: auto;
                margin-right: auto;
            }
        ';
        } elseif (strpos($theme_name, 'divi') !== false) {
            // Divi theme support
            $css .= '
            .gdrive-browser-container {
                font-family: "Open Sans",Arial,sans-serif;
            }
            
            .gdrive-browser-title {
                font-weight: 500;
                line-height: 1.7em;
            }
            
            .gdrive-search-input {
                border-radius: 3px;
            }
        ';
        } elseif (strpos($theme_name, 'astra') !== false) {
            // Astra theme support
            $css .= '
            .gdrive-browser-container {
                font-size: 15px;
            }
            
            .gdrive-search-input {
                border-radius: 3px;
            }
        ';
        } elseif (str_contains($theme_name, 'elementor')
            || str_contains($theme_name, 'hello')
        ) {
            // Elementor/Hello theme support
            $css .= '
            .gdrive-browser-container {
                font-family: -apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,sans-serif;
            }
        ';
        }

        // Additional CSS for admin pages
        if (is_admin()) {
            $css .= '
            .gdrive-browser-container {
                font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
                color: #3c434a;
            }
            
            .gdrive-browser-title {
                background-color: #f0f0f1;
                color: #1d2327;
            }
            
            .gdrive-breadcrumbs,
            .gdrive-files-table thead {
                background-color: #f0f0f1;
            }
            
            .gdrive-folder-link:hover, 
            .gdrive-file-link:hover,
            .gdrive-breadcrumb-link {
                color: #2271b1;
            }
        ';
        }

        return $css;
    }

    function helloWorldShortCode($atts)
    {
        return "<h3>Hello, World!</h3>";
    }

    /**
     * Render shortcode
     *
     * @param array $atts Shortcode attributes
     *
     * @return string HTML output
     * @throws InvalidArgumentException
     */
    public function renderShortcode(array $atts = []): string
    {
        // Enqueue required assets
        wp_enqueue_style('gdrive-browser-style');
        wp_enqueue_script('gdrive-browser-script');

        // Process attributes
        $attributes = shortcode_atts(
            [
                'folder_id' => get_option('gdi_root_folder_id'),
                'title' => __('Google Drive Files', 'google-drive-integration'),
                'show_breadcrumbs' => true,
                'items_per_page' => 20,
                'columns' => 'name,size,modified,actions',
                'restrict_to_folder' => true,
                'theme_match' => true,    // Whether to try matching the theme
                'max_width' => '',        // Custom max width (e.g., '800px', '100%')
                'container_class' => '',  // Additional CSS classes
            ], $atts
        );

        $this->rootFolderId = $attributes['folder_id'];
        // Initialize drive service
        try {
            $this->driveService = new GoogleDriveService($attributes['folder_id']);
            $files = $this->driveService->getFolderContents();
            $breadcrumbs = ($attributes['show_breadcrumbs']) ? $this->driveService->getBreadcrumbs() : [];

            // Get selected columns
            $columns = explode(',', $attributes['columns']);
            $columns = array_map('trim', $columns);

            // Start output buffering
            ob_start();

            echo $this->renderBrowserContainer($attributes, $files, $breadcrumbs, $columns);

            return ob_get_clean();

        } catch (\Exception $e) {
            return $this->renderError(__('Error: Unable to access Google Drive files.' . $e->getMessage(), 'google-drive-integration'));
        }
    }

    /**
     * Render browser container
     */
    private function renderBrowserContainer($attributes, $files, $breadcrumbs, $columns): string
    {
        $classes = 'gdrive-browser-container';
        if (!empty($attributes['container_class'])) {
            $classes .= ' ' . esc_attr($attributes['container_class']);
        }

        $styles = '';
        if (!empty($attributes['max_width'])) {
            $styles .= 'max-width:' . esc_attr($attributes['max_width']) . ';';
        }

        $html = '<div class="' . $classes . '" 
                data-current-folder="' . esc_attr($attributes['folder_id']) . '"
                data-root-folder="' . esc_attr($attributes['folder_id']) . '"
                data-restrict-folder="' . esc_attr($attributes['restrict_to_folder'] ? '1' : '0') . '"';

        if (!empty($styles)) {
            $html .= ' style="' . $styles . '"';
        }

        $html .= '>';


        // Title
        if (!empty($attributes['title'])) {
            $html .= '<h3 class="gdrive-browser-title">' . esc_html($attributes['title']) . '</h3>';
        }

        // Search box
        $html .= $this->renderSearchBox();

        // Breadcrumbs
        if ($attributes['show_breadcrumbs'] && !empty($breadcrumbs)) {
            $html .= $this->renderBreadcrumbs($breadcrumbs, $attributes['restrict_to_folder'] ? $attributes['folder_id'] : null);
        }

        // Loading indicator
        $html .= '<div class="gdrive-loading"><span class="gdrive-spinner"></span><span>' . __('Loading...', 'google-drive-integration') . '</span></div>';

        // Files list
        $html .= '<div class="gdrive-files-container">';

        if (empty($files)) {
            $html .= $this->renderEmptyFolder();
        } else {
            $html .= $this->renderFilesTable($files, $columns);
        }

        $html .= '</div>'; // End files container

        // Pagination placeholder
        $html .= '<div class="gdrive-pagination"></div>';

        $html .= '</div>'; // End main container

        return $html;
    }

    /**
     * Render search box
     */
    private function renderSearchBox()
    {
        return '<div class="gdrive-search-container">
            <input type="text" class="gdrive-search-input" placeholder="' . esc_attr__('Search files...', 'google-drive-integration') . '">
        </div>';
    }

    /**
     * Render breadcrumbs
     */
    private function renderBreadcrumbs($breadcrumbs, $rootFolderId = null)
    {
        $html = '<div class="gdrive-breadcrumbs">';

        // Find the index of the root folder in breadcrumbs
        $rootIndex = -1;
        if ($rootFolderId) {
            foreach ($breadcrumbs as $index => $crumb) {
                if ($crumb['id'] === $rootFolderId) {
                    $rootIndex = $index;
                    break;
                }
            }
        }

        $startIndex = ($rootIndex > -1) ? $rootIndex : 0;
        $last = count($breadcrumbs) - 1;

        for ($i = $startIndex; $i <= $last; $i++) {
            $crumb = $breadcrumbs[$i];

            if ($i === $last) {
                $html .= '<span class="gdrive-breadcrumb-current">' . esc_html($crumb['name']) . '</span>';
            } else {
                $html .= '<a href="#" class="gdrive-breadcrumb-link" data-folder-id="' . esc_attr($crumb['id']) . '">';
                $html .= esc_html($crumb['name']);
                $html .= '</a>';
                $html .= '<span class="gdrive-breadcrumb-separator">/</span>';
            }
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render empty folder message
     */
    private function renderEmptyFolder()
    {
        return '<div class="gdrive-empty-folder">
            <i class="fas fa-folder-open"></i>
            <p>' . __('No files found in this folder.', 'google-drive-integration') . '</p>
        </div>';
    }

    /**
     * Render error message
     */
    private function renderError($message)
    {
        return '<div class="gdrive-error">' . esc_html($message) . '</div>';
    }

    /**
     * Render files table
     */
    private function renderFilesTable($files, $columns)
    {
        // Sort files: folders first, then alphabetically
        usort(
            $files, function ($a, $b) {
            $aIsFolder = $a->getMimeType() === 'application/vnd.google-apps.folder';
            $bIsFolder = $b->getMimeType() === 'application/vnd.google-apps.folder';

            if ($aIsFolder && !$bIsFolder) {
                return -1;
            }
            if (!$bIsFolder && $aIsFolder) {
                return 1;
            }

            return strcmp($a->getName(), $b->getName());
        }
        );

        $html = '<table class="gdrive-files-table">';

        // Table header
        $html .= '<thead><tr>';

        if (in_array('name', $columns)) {
            $html .= '<th class="gdrive-col-name">' . __('Name', 'google-drive-integration') . '</th>';
        }

        if (in_array('size', $columns)) {
            $html .= '<th class="gdrive-col-size">' . __('Size', 'google-drive-integration') . '</th>';
        }

        if (in_array('modified', $columns)) {
            $html .= '<th class="gdrive-col-modified">' . __('Modified', 'google-drive-integration') . '</th>';
        }

        if (in_array('actions', $columns)) {
            $html .= '<th class="gdrive-col-actions">' . __('Actions', 'google-drive-integration') . '</th>';
        }

        $html .= '</tr></thead>';

        // Table body
        $html .= '<tbody>';

        foreach ($files as $file) {
            $html .= $this->renderFileRow($file, $columns);
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Render a single file row
     */
    private function renderFileRow($file, $columns): string
    {
        $isFolder = $file->getMimeType() === 'application/vnd.google-apps.folder';
        $fileId = $file->getId();
        $fileName = $file->getName();
        $fileLink = $file->getWebViewLink();
        $fileSize = $file->getSize() ?? 0;
        $fileModified = $file->getModifiedTime() ?? '';

        // Get file type and icon
        $fileType = $this->getFileType($file->getMimeType());
        $iconClass = $this->getFileIconClass($fileType);

        $html = '<tr class="gdrive-file-row ' . ($isFolder ? 'gdrive-folder-row' : 'gdrive-document-row') . '">';

        // Name column
        if (in_array('name', $columns)) {
            $html .= '<td class="gdrive-col-name">';

            if ($isFolder) {
                $html .= '<a href="#" class="gdrive-folder-link" data-folder-id="' . esc_attr($fileId) . '">';
                $html .= '<span class="gdrive-icon ' . esc_attr($iconClass) . '"></span>';
                $html .= '<span class="gdrive-file-name">' . esc_html($fileName) . '</span>';
                $html .= '</a>';
            } else {
                $html .= '<a href="' . esc_url($fileLink) . '" target="_blank" class="gdrive-file-link">';
                $html .= '<span class="gdrive-icon ' . esc_attr($iconClass) . '"></span>';
                $html .= '<span class="gdrive-file-name">' . esc_html($fileName) . '</span>';
                $html .= '</a>';
            }

            $html .= '</td>';
        }

        // Size column
        if (in_array('size', $columns)) {
            $formattedSize = $isFolder ? '-' : $this->formatFileSize($fileSize);
            $html .= '<td class="gdrive-col-size">' . esc_html($formattedSize) . '</td>';
        }

        // Modified column
        if (in_array('modified', $columns)) {
            $formattedDate = $fileModified ? date_i18n(get_option('date_format'), strtotime($fileModified)) : '-';
            $html .= '<td class="gdrive-col-modified">' . esc_html($formattedDate) . '</td>';
        }

        // Actions column
        if (in_array('actions', $columns)) {
            $html .= '<td class="gdrive-col-actions">';

            if ($isFolder) {
                $html .= '<a href="#" class="gdrive-action-button gdrive-open-folder" data-folder-id="' . esc_attr($fileId) . '" title="' . esc_attr__('Open folder', 'google-drive-integration') . '"></a>';
            } else {
                $html .= '<a href="' . esc_url($fileLink) . '" target="_blank" class="gdrive-action-button gdrive-view-file" title="' . esc_attr__('View file', 'google-drive-integration') . '"></a>';
            }

            $html .= '</td>';
        }

        $html .= '</tr>';

        return $html;
    }

    /**
     * Format file size in human-readable format
     *
     * @param int $bytes File size in bytes
     *
     * @return string Formatted file size
     */
    private function formatFileSize($bytes)
    {
        if ($bytes == 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    /**
     * Get file type from MIME type
     *
     * @param string $mimeType MIME type
     *
     * @return string File type
     */
    private function getFileType($mimeType)
    {
        return self::$mimeTypeMap[$mimeType] ?? 'generic';
    }

    /**
     * Get file icon class based on file type
     *
     * @param string $fileType File type
     *
     * @return string CSS class for icon
     */
    private function getFileIconClass($fileType)
    {
        return 'fas ' . (self::$fileTypeIcons[$fileType] ?? self::$fileTypeIcons['generic']);
    }

    /**
     * AJAX handler for changing folders
     */
    public function ajaxChangeFolder()
    {
        // Verify nonce
        check_ajax_referer('gdrive_nonce', 'nonce');

        $folder_id = isset($_POST['folder_id']) ? sanitize_text_field($_POST['folder_id']) : '';
        $root_folder_id = isset($_POST['root_folder_id']) ? sanitize_text_field($_POST['root_folder_id']) : '';
        $restrict_to_folder = isset($_POST['restrict_to_folder']) ? (bool)$_POST['restrict_to_folder'] : true;


        if (empty($folder_id)) {
            wp_send_json_error(['message' => __('Invalid folder ID', 'google-drive-integration')]);

            return;
        }

        try {
            $this->driveService = new GoogleDriveService();
            if ($restrict_to_folder && !empty($root_folder_id) && $folder_id !== $root_folder_id) {
                // Verify the folder is a child of the root folder
                $isAllowed = $this->isFolderWithinBoundary($folder_id, $root_folder_id);

                if (!$isAllowed) {
                    wp_send_json_error(
                        [
                            'message' => __('Navigation restricted to the specified folder', 'google-drive-integration')
                        ]
                    );

                    return;
                }
            }

            $this->driveService->changeFolder($folder_id);

            $files = $this->driveService->getFolderContents();
            $breadcrumbs = $this->driveService->getBreadcrumbs();

            wp_send_json_success(
                [
                    'files' => $files,
                    'breadcrumbs' => $breadcrumbs,
                    'current_folder' => $folder_id
                ]
            );

        } catch (\Exception $e) {
            wp_send_json_error(
                [
                    'message' => __('Error accessing Google Drive folder', 'google-drive-integration'),
                    'error' => $e->getMessage()
                ]
            );
        }
    }

    /**
     * @param $folderId
     * @param $rootFolderId
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    private function isFolderWithinBoundary($folderId, $rootFolderId): bool
    {
        try {
            // Save current folder to restore it later
            $currentFolder = $this->driveService->getCurrentFolderId();

            // Temporarily change folder to check breadcrumbs
            $this->driveService->changeFolder($folderId);
            $breadcrumbs = $this->driveService->getBreadcrumbs();

            // Restore original folder
            $this->driveService->changeFolder($currentFolder);

            // Check if root folder ID is in the breadcrumbs
            foreach ($breadcrumbs as $crumb) {
                if ($crumb['id'] === $rootFolderId) {
                    return true;
                }
            }

            // Root folder not found in breadcrumbs
            return false;
        } catch (\Exception $e) {
            // If there's an error, deny access
            return false;
        }
    }

}
