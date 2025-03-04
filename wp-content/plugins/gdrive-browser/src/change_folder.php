<?php
/**
 * 
 *
 * @noinspection PhpUnhandledExceptionInspection 
 */
require_once __DIR__ . '/../vendor/autoload.php';

session_start();

use App\GoogleDriveService;

$whoops = new \Whoops\Run;
$whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler());
$whoops->register();


$defaultFolderId = null;
// Initialize session folder ID if not set
if (!isset($_SESSION['currentFolderId'])) {
    $_SESSION['currentFolderId'] = $defaultFolderId;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['folderId'])) {
    $folderId = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['folderId']); // Sanitize input

    // Store the new folder ID in session
    $_SESSION['currentFolderId'] = $folderId;

    // Fetch updated folder contents
    $driveService = new GoogleDriveService(folderId: $_SESSION['currentFolderId']);

    $files = $driveService->getFolderContents();
    $breadcrumbs = $driveService->getBreadcrumbs();

    $fileList = [];
    foreach ($files as $file) {
        if ($file->getMimeType() == 'application/vnd.google-apps.folder') {
            $fileList[] = [
                'type' => 'folder',
                'id' => $file->getId(),
                'name' => $file->getName(),
            ];
        } else {
            $fileList[] = [
                'type' => 'file',
                'id' => $file->getId(),
                'name' => $file->getName(),
                'link' => $file->getWebViewLink(),
            ];
        }
    }

    echo json_encode(
        [
        'status' => 'success',
        'folderId' => $folderId,
        'files' => $fileList,
        'breadcrumbs' => $breadcrumbs,
        ]
    );
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
