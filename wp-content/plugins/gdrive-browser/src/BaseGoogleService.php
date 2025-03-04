<?php
/***
 * The `class BaseGoogleService` is a PHP class that serves as a base for interacting with the Google
 * Drive API
 **/

namespace App;

use Dotenv\Dotenv;
use Google\Client as Google_Client;
use Google\Exception;
use Google\Service\Drive as Google_Service_Drive;

class BaseGoogleService
{
    /**
     * The function initializes a Google Drive client with specified credentials and settings, allowing
     * access to Google Drive API functionalities.
     *
     * @param string|null $folderId The `__construct` function in the code snippet is a constructor method
     *                              for a class that sets up a Google Drive API client. It takes an optional parameter ``,
     *                              which is used to specify the default folder ID to work with. If `` is not provided, it
     *                              falls back
     *
     * @throws Exception
     */
    public function __construct( string $folderId = null )
    {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));

        $dotenv->load();

        $dotenv->required(
            [
            'GOOGLE_CREDENTIALS_PATH',
            'GOOGLE_SERVICE_ACCOUNT',
            'GOOGLE_DEFAULT_FOLDER_ID',
            'GOOGLE_APPLICATION_NAME'
            ]
        )->notEmpty();

        $credentialsPath = $_ENV['GOOGLE_CREDENTIALS_PATH'];
        $appName         = $_ENV['GOOGLE_APPLICATION_NAME'];
        $serviceAccount  = $_ENV['GOOGLE_SERVICE_ACCOUNT'];
        if ($folderId === null ) {
            $defaultFolderId = $_ENV['GOOGLE_DEFAULT_FOLDER_ID'];
        } else {
            $defaultFolderId = $folderId;
        }


        $this->client = new Google_Client();
        $this->client->setApplicationName($appName);
        $this->client->setScopes(Google_Service_Drive::DRIVE_READONLY);
        $this->client->setAuthConfig($credentialsPath);

        if ($serviceAccount ) {
            $this->client->setSubject($serviceAccount);
        }

        $this->service         = new Google_Service_Drive($this->client);
        $this->currentFolderId = $defaultFolderId;
        $this->cacheService    = new CacheService();
    }
}
