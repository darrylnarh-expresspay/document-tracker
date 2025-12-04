<?php
namespace App\Services;

use App\Interfaces\FileHostInterface;
use Exception;

class S3FileUploader implements FileHostInterface {
    private $s3Client;
    private $bucket;

    public function __construct($s3Client, string $bucketName) {
        $this->s3Client = $s3Client;
        $this->bucket = $bucketName;
    }

    public function upload(array $file, string $namePrefix): string {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return "";
        }

        $filename = basename($file["name"]);
        $key = strtolower($namePrefix) . "/" . time() . "_" . $filename;

        try {
            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
                'SourceFile' => $file['tmp_name'],
                'ACL'    => 'public-read'
            ]);
            return $result['ObjectURL'];
        } catch (Exception $e) {
            error_log("S3 Upload Error: " . $e->getMessage());
            return "";
        }
    }
}