# Google Drive Browser

This project is a Google Drive Browser built with PHP. It allows users to browse and interact with their Google Drive files and folders through a web interface.

## Features

- Browse Google Drive folders and files
- View file details and open files in Google Drive
- Navigate through folder breadcrumbs
- Caching of folder contents and breadcrumbs for improved performance

## Requirements

- PHP 8.1 or higher
- Composer
- Google API Client Library for PHP
- Whoops error handling library
- Symfony Cache component
- vlucas/phpdotenv for environment variable management

## Installation

1. Clone the repository:

    ```sh
      git clone https://github.com/masgeek/gdrive-browser.git
      cd gdrive-browser
    ```

2. Install dependencies using Composer:
    ```sh
      composer install
    ```

   ## Configuration

3. Create a `.env` file in the root directory of the project and add the following environment variables:

   ```markdown
       GOOGLE_CREDENTIALS_PATH=path/to/your/credentials.json
       GOOGLE_SERVICE_ACCOUNT=your-service-account-email
       GOOGLE_DEFAULT_FOLDER_ID=your-default-folder-id
       GOOGLE_APPLICATION_NAME=your-application-name
   ```

4. Start a local PHP server:

   ```sh
       php -S localhost:8000
   ```

5. Open your browser and navigate to http://localhost:8000.

   ## Project Structure
   
   ```markdown
   .env
   .gitignore
   cache/
   composer.json
   composer.lock
   credentials.json
   index.php
   src/
       CacheService.php
       change_folder.php
       GoogleDriveService.php
   vendor/
   ```

- `index.php`: The main entry point of the application.
- `src/GoogleDriveService.php`: Contains the GoogleDriveService class for interacting with the Google Drive API.
- `src/CacheService.php`: Contains the CacheService class for caching folder contents and breadcrumbs.
- `src/change_folder.php`: Handles folder change requests and returns updated folder contents and breadcrumbs.
- `vendor`: Contains the Composer dependencies.
  
## Usage

- Open the application in your browser. 
- Use the breadcrumb navigation to navigate through folders. 
- Click on folder names to open them. 
- Click on file names to open them in Google Drive.
  
## License

This project is licensed under the MIT License. See the LICENSE file for details.

## Contributing

Contributions are welcome! Please open an issue or submit a pull request for any improvements or bug fixes.

## Acknowledgements

- Google API Client Library for PHP
- Whoops 
- Symfony Cache 
- vlucas/phpdotenv