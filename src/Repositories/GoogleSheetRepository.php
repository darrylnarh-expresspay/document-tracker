<?php
namespace App\Repository;

use App\Interfaces\StorageInterface;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
use Google_Service_Sheets_BatchUpdateSpreadsheetRequest;

class GoogleSheetRepository implements StorageInterface {
    private $service;
    private $spreadsheetId;

    public function __construct(string $credentialsPath, string $spreadsheetId) {
        $client = new Google_Client();
        $client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
        $client->setAuthConfig($credentialsPath);
        $this->service = new Google_Service_Sheets($client);
        $this->spreadsheetId = $spreadsheetId;
    }

    public function getAll(): array {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, "Sheet1!A:H");
        return $response->getValues() ?? [];
    }

    public function create(array $data): void {
        $valueRange = new Google_Service_Sheets_ValueRange();
        $valueRange->setValues([$data]);
        $this->service->spreadsheets_values->append(
            $this->spreadsheetId,
            "Sheet1",
            $valueRange,
            ['valueInputOption' => 'USER_ENTERED']
        );
    }

    public function update(int $rowIndex, array $data): void {
        // [ ] Sheets is 1-based for A1 notation, existing logic used passed index
        // [ ] Assuming $rowIndex is the actual array index from the docs list
        $range = "Sheet1!A" . ($rowIndex + 1) . ":H" . ($rowIndex + 1);
        $valueRange = new Google_Service_Sheets_ValueRange();
        $valueRange->setValues([$data]);

        $this->service->spreadsheets_values->update(
            $this->spreadsheetId,
            $range,
            $valueRange,
            ['valueInputOption' => 'USER_ENTERED']
        );
    }

    public function delete(int $rowIndex): void {
        if ($rowIndex === 0) throw new \Exception("Cannot delete header");
        
        $requestBody = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest([
            'requests' => [
                'deleteDimension' => [
                    'range' => [
                        'sheetId' => 0,
                        'dimension' => 'ROWS',
                        'startIndex' => $rowIndex,
                        'endIndex' => $rowIndex + 1,
                    ]
                ]
            ]
        ]);
        $this->service->spreadsheets->batchUpdate($this->spreadsheetId, $requestBody);
    }
}