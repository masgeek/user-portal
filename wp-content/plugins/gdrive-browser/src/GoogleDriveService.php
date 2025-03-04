<?php

namespace App;

require_once __DIR__ . '/../vendor/autoload.php'; //ensure this is still included, as it will not be included by index.php when using namespaces.


use Dotenv\Dotenv;
use Exception;
use Google\Client as Google_Client;

//use Google_Client;
use Google\Service\Drive as Google_Service_Drive;
use Psr\Cache\InvalidArgumentException;

//use Google_Service_Drive;

/* The `GoogleDriveService` class is a PHP class that provides functionality to interact with Google
Drive API */

class GoogleDriveService extends BaseGoogleService
{
    private Google_Client $client;
    private Google_Service_Drive $service;

    private CacheService $cacheService;
    private string $currentFolderId;


    /**
     * The function constructs a Google Drive client with specified credentials and settings, allowing
     * access to Google Drive API functionalities.
     *
     * @param string folderId The `folderId` parameter in the constructor is used to specify the ID of
     * the Google Drive folder that will be used as the default folder for operations if a specific
     * folder ID is not provided when creating an instance of the class. If `folderId` is not provided,
     * the default folder ID specified
     */
    /**
     * Constructor for GoogleDriveService using WordPress settings
     *
     * @param string $folderId Optional folder ID to override the default one
     *
     * @throws \Google\Exception
     */
    public function __construct( string $folderId = null )
    {
        // Get WordPress options instead of using dotenv
        $credentials_exist     = file_exists(GDI_CREDENTIALS_FILE);
        $service_account_email = get_option('gdi_service_account_email', null);
        $default_folder_id     = get_option('gdi_root_folder_id', null);
        $app_name              = 'WordPress Google Drive Integration';

        // Check for required settings
        if (! $credentials_exist ) {
            throw new \Exception('Google Drive credentials file not found');
        }

        if (empty($service_account_email) ) {
            throw new \Exception('Google service account email not configured');
        }

        if (empty($default_folder_id) ) {
            throw new \Exception('Google Drive root folder ID not configured');
        }

        // Use provided folder ID or default
        $current_folder_id = ( $folderId !== null ) ? $folderId : $default_folder_id;

        // Set up Google client
        $this->client = new Google_Client();
        $this->client->setApplicationName($app_name);
        $this->client->setScopes(Google_Service_Drive::DRIVE_READONLY);

        // Read and decrypt credentials
        $encrypted           = file_get_contents(GDI_CREDENTIALS_FILE);
        $credentials_handler = new CredentialsHandler(GDI_CREDENTIALS_DIR, GDI_CREDENTIALS_FILE);
        $credentials_json    = $credentials_handler->decryptData($encrypted);

        // Set auth from JSON
        $this->client->setAuthConfig(json_decode($credentials_json, true));

        // Set service account
        if ($service_account_email ) {
            $this->client->setSubject($service_account_email);
        }

        $this->service         = new Google_Service_Drive($this->client);
        $this->currentFolderId = $current_folder_id;
        $this->cacheService    = new CacheService();
    }

    /**
     * This PHP function retrieves the contents of a folder, caching the results to improve
     * performance.
     *
     * @param int pageSize The `pageSize` parameter in the `getFolderContents` function is used to
     * specify the number of items to be retrieved per page when fetching the contents of a folder. By
     * default, the `pageSize` is set to 50, but you can provide a different value when calling the
     * function to customize
     *
     * @return array The function `getFolderContents` returns an array of files from the current
     * folder. If the files are not found in the cache, it fetches the files from the service and
     * stores them in the cache before returning them.
     */
    public function getFolderContents( int $pageSize = 50 ): array
    {

        $cacheKey = md5($this->currentFolderId);


        $files = $this->cacheService->get($cacheKey);

        if ($files === null ) {
            $results = $this->service->files->listFiles(
                [
                'q'        => "'$this->currentFolderId' in parents and trashed = false",
                'fields'   => 'nextPageToken, files(id, name, mimeType, webViewLink)',
                'pageSize' => $pageSize
                ] 
            );
            $files   = $results->getFiles();
            $this->cacheService->store($cacheKey, $files);
        }

        return $files;
    }


    /**
     * This PHP function retrieves breadcrumbs for a given folder ID, caching the result for future
     * use.
     *
     * @return array The `getBreadcrumbs` function returns an array of breadcrumbs for the current
     * folder. The breadcrumbs are retrieved from a cache using the current folder ID as a cache key.
     * If the breadcrumbs are not found in the cache, they are generated by iterating through the
     * parent folders of the current folder and storing the folder ID and name in the breadcrumbs
     * array. Finally, the generated breadcrumbs are stored in the cache before
     * @throws InvalidArgumentException
     */
    public function getBreadcrumbs(): array
    {
        $cacheKey        = md5($this->currentFolderId . '-crumbs');
        $currentFolderId = $this->currentFolderId;
        $breadcrumbs     = $this->cacheService->get($cacheKey);
        if ($breadcrumbs === null ) {
            $breadcrumbs = [];
            while ( $currentFolderId && $currentFolderId !== 'root' ) {
                $folder = $this->service->files->get($currentFolderId, [ 'fields' => 'id, name, parents' ]);
                array_unshift(
                    $breadcrumbs, [
                    'id'   => $folder->getId(),
                    'name' => $folder->getName()
                    ] 
                );
                $currentFolderId = $folder->getParents()[0] ?? null;
            }
            $this->cacheService->store($cacheKey, $breadcrumbs);
        }

        return $breadcrumbs;
    }

    public function getCurrentFolderId()
    {
        return $this->currentFolderId;
    }

    public function getFolder( $folderId )
    {
        try {
            return $this->service->files->get($folderId, [ 'fields' => 'id, name, parents' ]);
        } catch ( \Exception $e ) {
            // Log error or handle gracefully
            return null;
        }
    }

    /**
     * The function `changeFolder` sets the current folder ID to the provided folder ID in PHP.
     *
     * @param string folderId The `changeFolder` function takes a parameter `folderId` of type string.
     * This function sets the `currentFolderId` property of the class to the value of the `folderId`
     * parameter.
     */
    public function changeFolder( string $folderId ): void
    {
        $this->currentFolderId = $folderId;
    }
}
