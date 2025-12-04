<?php
namespace App\Services;

use App\Interfaces\FileHostInterface;
use Sabre\DAV\Client;
use Exception;

class OwnCloudFileUploader implements FileHostInterface {
    
    private $client;
    private $baseUri;

    /**
     * @param string $baseUri Base URL to your WebDAV directory (e.g., 'https://example.com/remote.php/webdav/Documents/')
     * @param string $username OwnCloud/WebDAV username
     * @param string $password OwnCloud/WebDAV password or app token
     */
    public function __construct(string $baseUri, string $username, string $password) {
        $this->baseUri = rtrim($baseUri, '/') . '/'; // Ensure trailing slash
        
        $settings = [
            'baseUri' => $this->baseUri,
            'userName' => $username,
            'password' => $password,
            // [ ] adjust authentication types depending on the server e.g: 'authType' => Client::AUTH_BASIC, 
        ];
        
        $this->client = new Client($settings);
    }

    public function upload(array $file, string $namePrefix): string {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ""; // nothing uploaded
        }
        
        // define path
        // Use the original filename to keep the structure clean
        $filename = basename($file["name"]);
        // Folder/Timestamp_Filename (e.g., "contract_name/1678886400_document.pdf")
        $remotePath = strtolower($namePrefix) . "/" . time() . "_" . $filename; 
        
        // read file contents
        $fileContent = file_get_contents($file['tmp_name']);

        if ($fileContent === false) {
            error_log("Failed to read temporary file: " . $file['tmp_name']);
            return "";
        }

        try {
            // use the PUT method for file upload
            $response = $this->client->request('PUT', $remotePath, $fileContent);

            // success status code (201 Created or 204 No Content)
            if ($response['statusCode'] >= 200 && $response['statusCode'] < 300) {
                // return url
                return $this->baseUri . $remotePath;
            } else {
                error_log("WebDAV Upload Failed. Status: " . $response['statusCode'] . ", Body: " . $response['body']);
                return "";
            }
        } catch (Exception $e) {
            error_log("WebDAV Client Error: " . $e->getMessage());
            return "";
        }
    }
}