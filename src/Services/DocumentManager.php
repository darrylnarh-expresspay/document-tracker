<?php
// src/Services/DocumentManager.php
namespace App\Services;

use DateTime;

class DocumentManager {
    
    public static function calculateStatus(string $expiryDate, string $isPerpetual): string {
        if ($isPerpetual === "Yes") {
            return "PERPETUAL";
        }
        
        if (empty($expiryDate)) return "ACTIVE";

        $expiry = new DateTime($expiryDate);
        $today = new DateTime();
        $days = $today->diff($expiry)->format('%r%a');

        if ($days <= 0) return "EXPIRED";
        if ($days <= 183) return "REVIEW";
        
        return "ACTIVE";
    }

    public static function prepareRow(array $postData, string $fileUrl, string $existingUrl = ""): array {
        return [
            uniqid(),
            $postData['name'] ?? "",
            $postData['category'] ?? "",
            $postData['effective_date'] ?? "",
            $postData['expiry_date'] ?? "",
            $postData['perpetual'] ?? "No",
            self::calculateStatus($postData['expiry_date'] ?? "", $postData['perpetual'] ?? "No"),
            !empty($fileUrl) ? $fileUrl : $existingUrl
        ];
    }
}