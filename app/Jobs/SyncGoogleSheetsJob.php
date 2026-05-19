<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SyncGoogleSheetsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $accessToken = 'YOUR_ACCESS_TOKEN';
        /*
        |--------------------------------------------------------------------------
        | ROOT FOLDER
        |--------------------------------------------------------------------------
        */

        $rootFolderId = '1K5UhluWVR0jLCoJzIjnxIbjudRh1EHmD';

        $query = "'{$rootFolderId}' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false";

        $url = "https://www.googleapis.com/drive/v3/files?q=" . urlencode($query) . "&fields=files(id,name,mimeType)";

        $curl = curl_init();

        curl_setopt_array($curl, [

            CURLOPT_URL => $url,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$accessToken}"
            ],
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        $foldersData = json_decode($response, true);

        if (empty($foldersData['files'])) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | LOOP FOLDERS
        |--------------------------------------------------------------------------
        */

        foreach ($foldersData['files'] as $folder) {

            $folderId = $folder['id'];

            $sheetQuery = "'{$folderId}' in parents and trashed=false";

            $sheetUrl = "https://www.googleapis.com/drive/v3/files?q=" . urlencode($sheetQuery) . "&fields=files(id,name,mimeType)";

            $folderCurl = curl_init();

            curl_setopt_array($folderCurl, [

                CURLOPT_URL => $sheetUrl,

                CURLOPT_RETURNTRANSFER => true,

                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$accessToken}"
                ],
            ]);

            $folderFilesResponse = curl_exec($folderCurl);

            curl_close($folderCurl);

            $folderFilesData = json_decode($folderFilesResponse, true);

            if (empty($folderFilesData['files'])) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | LOOP SHEETS
            |--------------------------------------------------------------------------
            */

            foreach ($folderFilesData['files'] as $file) {

                $mimeType = $file['mimeType'];

                if (
                    $mimeType != 'application/vnd.google-apps.spreadsheet'
                    &&
                    $mimeType != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ) {
                    continue;
                }

                $fileId = $file['id'];
                $spreadsheetTitle = $file['name'];

                /*
                |--------------------------------------------------------------------------
                | EXPORT GOOGLE SHEET
                |--------------------------------------------------------------------------
                */

                if ($mimeType == 'application/vnd.google-apps.spreadsheet') {

                    $downloadUrl =
                        "https://www.googleapis.com/drive/v3/files/" .
                        $fileId .
                        "/export?mimeType=application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";

                } else {

                    $downloadUrl =
                        "https://www.googleapis.com/drive/v3/files/" .
                        $fileId .
                        "?alt=media";
                }

                $ch = curl_init();

                curl_setopt_array($ch, [

                    CURLOPT_URL => $downloadUrl,

                    CURLOPT_RETURNTRANSFER => true,

                    CURLOPT_HTTPHEADER => [
                        "Authorization: Bearer {$accessToken}"
                    ],
                ]);

                $fileContent = curl_exec($ch);

                curl_close($ch);

                $tempFile = storage_path('app/temp_' . $fileId . '.xlsx');

                file_put_contents($tempFile, $fileContent);

                $spreadsheet = IOFactory::load($tempFile);

                /*
                |--------------------------------------------------------------------------
                | SAVE SHEET DATA
                |--------------------------------------------------------------------------
                */

                foreach ($spreadsheet->getWorksheetIterator() as $sheet) {

                    $sheetTitle = trim($sheet->getTitle());

                    if (strtolower($sheetTitle) == 'blank') {
                        continue;
                    }

                    $rows = $sheet->toArray();

                    foreach ($rows as $row) {

                        if (empty($row[0])) {
                            continue;
                        }

                        // INSERT OR UPDATE YOUR DB DATA HERE

                        // Example:
                        // Lead::updateOrCreate([...], [...]);
                    }
                }

                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }
        }
    }
}

