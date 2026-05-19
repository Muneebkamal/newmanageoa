<?php

namespace App\Http\Controllers;

use App\Jobs\ImportGoogleSheetJob;
use Illuminate\Http\Request;
use App\Models\NewLead;
use App\Models\NewSource;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Http;

class NewLeadController extends Controller
{
    public function importPage()
    {
        return view('google-sheet-import');
    }

    public function sourcesPage()
    {
        $sources = NewSource::latest()->get();

        return view('sources', compact('sources'));
    }
    public function dispatchGoogleSheetJobs()
    {
        try {
            \Log::info('starting');

            ini_set('memory_limit', '-1');

            set_time_limit(0);

            $folderId = '1K5UhluWVR0jLCoJzIjnxIbjudRh1EHmD';

            $accessToken = $this->generateGoogleAccessToken();

            if (!$accessToken) {

                \Log::error('Google Access Token Failed');

                return;
            }

            $nextPageToken = null;

            do {

                // ROOT FOLDERS
                $response = Http::withToken($accessToken)
                    ->timeout(300)
                    ->get(
                        'https://www.googleapis.com/drive/v3/files',
                        [
                            'q' => "'{$folderId}' in parents and trashed=false",

                            'fields' => 'nextPageToken,files(id,name,mimeType)',

                            'pageSize' => 1000,

                            'pageToken' => $nextPageToken,
                        ]
                    );

                if (!$response->successful()) {

                    \Log::error('Root Folder Fetch Failed');

                    return;
                }

                $responseData = $response->json();

                $folders = $responseData['files'] ?? [];

                $nextPageToken = $responseData['nextPageToken'] ?? null;

                foreach ($folders as $folder) {

                    try {

                        $subFolderId = $folder['id'];

                        $subNextPageToken = null;

                        do {

                            $subResponse = Http::withToken($accessToken)
                                ->timeout(300)
                                ->get(
                                    'https://www.googleapis.com/drive/v3/files',
                                    [
                                        'q' => "'{$subFolderId}' in parents and trashed=false",

                                        'fields' => 'nextPageToken,files(id,name,mimeType)',

                                        'pageSize' => 1000,

                                        'pageToken' => $subNextPageToken,
                                    ]
                                );

                            if (!$subResponse->successful()) {

                                \Log::error(
                                    'Sub Folder Fetch Failed: ' . $subFolderId
                                );

                                break;
                            }

                            $subData = $subResponse->json();

                            $files = $subData['files'] ?? [];

                            $subNextPageToken =
                                $subData['nextPageToken'] ?? null;

                            foreach ($files as $file) {

                                try {

                                    // ONLY GOOGLE SHEETS
                                    if (
                                        $file['mimeType']
                                        != 'application/vnd.google-apps.spreadsheet'

                                        &&

                                        $file['mimeType']
                                        != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                                    ) {

                                        continue;
                                    }

                                    // SKIP IMPORTED
                                    $alreadyImported =
                                        DB::table('google_sheet_syncs')
                                        ->where(
                                            'google_file_id',
                                            $file['id']
                                        )
                                        ->exists();

                                    if ($alreadyImported) {

                                        continue;
                                    }

                                    // DISPATCH JOB
                                    ImportGoogleSheetJob::dispatch(
                                        $file['id'],
                                        $file['name'],
                                        $accessToken
                                    )
                                    ->onQueue('google-sheet-import');

                                } catch (\Exception $e) {

                                    \Log::error(
                                        'File Loop Error: ' .
                                        $e->getMessage()
                                    );
                                }
                            }

                        } while ($subNextPageToken);

                    } catch (\Exception $e) {

                        \Log::error(
                            'Folder Loop Error: ' .
                            $e->getMessage()
                        );
                    }
                }

            } while ($nextPageToken);
        \Log::info('ending');
        } catch (\Exception $e) {

            \Log::error(
                'Dispatch Google Sheet Jobs Error: ' .
                $e->getMessage()
            );
        }
    }
    private function generateGoogleAccessToken()
    {
        try {

            $json = json_decode(
                file_get_contents(
                    storage_path('app/google-service-account.json')
                ),
                true
            );

            $clientEmail = $json['client_email'];

            $privateKey = $json['private_key'];

            $now = time();

            /*
            |--------------------------------------------------------------------------
            | JWT HEADER
            |--------------------------------------------------------------------------
            */

            $header = [
                'alg' => 'RS256',
                'typ' => 'JWT'
            ];

            /*
            |--------------------------------------------------------------------------
            | JWT PAYLOAD
            |--------------------------------------------------------------------------
            */

            $payload = [

                'iss' => $clientEmail,

                'scope' =>
                    'https://www.googleapis.com/auth/drive.readonly',

                'aud' =>
                    'https://oauth2.googleapis.com/token',

                'exp' => $now + 3600,

                'iat' => $now
            ];

            /*
            |--------------------------------------------------------------------------
            | ENCODE
            |--------------------------------------------------------------------------
            */

            $base64UrlHeader = str_replace(
                ['+', '/', '='],
                ['-', '_', ''],
                base64_encode(json_encode($header))
            );

            $base64UrlPayload = str_replace(
                ['+', '/', '='],
                ['-', '_', ''],
                base64_encode(json_encode($payload))
            );

            $signatureInput =
                $base64UrlHeader .
                '.' .
                $base64UrlPayload;

            /*
            |--------------------------------------------------------------------------
            | SIGN
            |--------------------------------------------------------------------------
            */

            openssl_sign(

                $signatureInput,

                $signature,

                $privateKey,

                'sha256WithRSAEncryption'
            );

            $base64UrlSignature = str_replace(
                ['+', '/', '='],
                ['-', '_', ''],
                base64_encode($signature)
            );

            /*
            |--------------------------------------------------------------------------
            | FINAL JWT
            |--------------------------------------------------------------------------
            */

            $jwt =
                $base64UrlHeader .
                '.' .
                $base64UrlPayload .
                '.' .
                $base64UrlSignature;

            /*
            |--------------------------------------------------------------------------
            | ACCESS TOKEN REQUEST
            |--------------------------------------------------------------------------
            */

            $response =
                \Illuminate\Support\Facades\Http::asForm()
                ->post(
                    'https://oauth2.googleapis.com/token',
                    [

                        'grant_type' =>
                            'urn:ietf:params:oauth:grant-type:jwt-bearer',

                        'assertion' => $jwt
                    ]
                );

            if (!$response->successful()) {

                \Log::error(
                    'Google token failed'
                );

                return null;
            }

            return $response['access_token'];

        } catch (\Exception $e) {

            \Log::error($e->getMessage());

            return null;
        }
    }
    
    public function importGoogleSheet(Request $request)
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);
        $folderId = $request->sheet_url;

        /*
        |--------------------------------------------------------------------------
        | SERVICE ACCOUNT JSON
        |--------------------------------------------------------------------------
        */

        $json = json_decode(
            file_get_contents(
                storage_path('app/google-service-account.json')
            ),
            true
        );

        $clientEmail = $json['client_email'];

        $privateKey = $json['private_key'];

        /*
        |--------------------------------------------------------------------------
        | CREATE JWT
        |--------------------------------------------------------------------------
        */

        $now = time();

        $header = [
            'alg' => 'RS256',
            'typ' => 'JWT'
        ];

        $payload = [
            'iss' => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/drive.readonly',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ];

        $base64UrlHeader = str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode(json_encode($header))
        );

        $base64UrlPayload = str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode(json_encode($payload))
        );

        $signatureInput =
            $base64UrlHeader . "." . $base64UrlPayload;

        openssl_sign(
            $signatureInput,
            $signature,
            $privateKey,
            'sha256WithRSAEncryption'
        );

        $base64UrlSignature = str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($signature)
        );

        $jwt =
            $base64UrlHeader .
            "." .
            $base64UrlPayload .
            "." .
            $base64UrlSignature;

        /*
        |--------------------------------------------------------------------------
        | GET ACCESS TOKEN
        |--------------------------------------------------------------------------
        */

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL =>
                'https://oauth2.googleapis.com/token',

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_POST => true,

            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' =>
                    'urn:ietf:params:oauth:grant-type:jwt-bearer',

                'assertion' => $jwt
            ]),
        ]);

        $response = curl_exec($ch);

        curl_close($ch);

        $tokenData = json_decode($response, true);

        if (!isset($tokenData['access_token'])) {

            return response()->json([
                'success' => false,
                'response' => $tokenData
            ]);
        }

        $accessToken = $tokenData['access_token'];

        /*
        |--------------------------------------------------------------------------
        | GET FILES FROM GOOGLE DRIVE FOLDER
        |--------------------------------------------------------------------------
        */

        $url =
            "https://www.googleapis.com/drive/v3/files?q='" .
            $folderId .
            "'+in+parents&fields=files(id,name,mimeType)";

        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer {$accessToken}"
            ],
        ]);
        $filesResponse = curl_exec($ch);
        curl_close($ch);
        $filesData = json_decode($filesResponse, true);
        // ROOT FOLDERS LOOP
        foreach ($filesData['files'] as $folder) {

            $folderId2 = $folder['id'];
            $url ="https://www.googleapis.com/drive/v3/files?q='" .$folderId2 ."'+in+parents&fields=files(id,name,mimeType)";
            // GET SHEETS INSIDE FOLDER
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,

                CURLOPT_RETURNTRANSFER => true,

                CURLOPT_HTTPHEADER => [
                    "Authorization: Bearer {$accessToken}"
                ],
            ]);
            $folderFilesResponse = curl_exec($ch);
            curl_close($ch);
            $filesData = json_decode($folderFilesResponse, true);

            // SHEETS LOOP
            foreach( $filesData['files'] as $file ) {
                $fileId = $file['id'];
                $spreadsheetTitle = $file['name'];

                $downloadUrl =
                    "https://www.googleapis.com/drive/v3/files/" .
                    $fileId .
                    "?alt=media";

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

                $tempFile = storage_path(
                    'app/temp_' . $fileId . '.xlsx'
                );

                file_put_contents(
                    $tempFile,
                    $fileContent
                );

                $spreadsheet = IOFactory::load($tempFile);
                $source = NewSource::updateOrCreate(
                        [
                            'list_name' => $spreadsheetTitle,
                        ],
                        [
                            'employee_id' => auth()->id() ?? null,
                        ]
                    );
                /*
                |--------------------------------------------------------------------------
                | LOOP TABS
                |--------------------------------------------------------------------------
                */

                foreach ($spreadsheet->getWorksheetIterator() as $sheet) {

                    $sheetTitle = trim($sheet->getTitle());

                    /*
                    |--------------------------------------------------------------------------
                    | SKIP BLANK SHEET
                    |--------------------------------------------------------------------------
                    */

                    if (strtolower($sheetTitle) == 'blank') {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | DATE LOGIC
                    |--------------------------------------------------------------------------
                    */

                    $sourceDate = null;

                    preg_match('/\d+/', $sheetTitle, $match);

                    $sheetNumber = $match[0] ?? null;

                    // if ($sheetNumber) {

                    //     $sheetNumber = (string) $sheetNumber;

                    //     $month = substr($sheetNumber, 0, 1);

                    //     $day = substr($sheetNumber, 1);

                    //     if ($day == '') {

                    //         $day = $month;
                    //         $month = 1;
                    //     }

                    //     $month = (int) $month;
                    //     $day = (int) $day;

                    //     if (
                    //         $month >= 1 &&
                    //         $month <= 12 &&
                    //         $day >= 1 &&
                    //         $day <= 31
                    //     ) {

                    //         $sourceDate = Carbon::create(
                    //             2025,
                    //             $month,
                    //             $day
                    //         )->format('Y-m-d');
                    //     }
                    // }
                    if ($sheetNumber) {

                        $originalSheetNumber = $sheetNumber;

                        $sheetNumber = trim((string) $sheetNumber);

                        logger()->info('TAB NAME CHECK', [
                            'original' => $originalSheetNumber,
                            'string' => $sheetNumber,
                            'length' => strlen($sheetNumber),
                        ]);

                        $month = null;
                        $day = null;

                        // 4 digits => 1015 => 10/15
                        if (strlen($sheetNumber) == 4) {

                            $month = substr($sheetNumber, 0, 2);
                            $day = substr($sheetNumber, 2, 2);

                        }

                        // 3 digits => 930 => 9/30
                        elseif (strlen($sheetNumber) == 3) {

                            $month = substr($sheetNumber, 0, 1);
                            $day = substr($sheetNumber, 1, 2);

                        }

                        // 2 digits
                        elseif (strlen($sheetNumber) == 2) {

                            // 10,11,12 => Oct/Nov/Dec first day
                            if (in_array($sheetNumber, ['10', '11', '12'])) {

                                $month = (int) $sheetNumber;
                                $day = 1;

                            } else {

                                // 91 => 9/1
                                // 44 => 4/4
                                // 19 => 1/9

                                $month = substr($sheetNumber, 0, 1);
                                $day = substr($sheetNumber, 1, 1);
                            }

                        }

                        // 1 digit => 8 => 1/8
                        elseif (strlen($sheetNumber) == 1) {

                            $month = 1;
                            $day = (int) $sheetNumber;
                        }

                        logger()->info('PARSED DATE VALUES', [
                            'month_raw' => $month,
                            'day_raw' => $day,
                        ]);

                        $month = (int) $month;
                        $day = (int) $day;

                        logger()->info('FINAL INTEGER VALUES', [
                            'month' => $month,
                            'day' => $day,
                        ]);

                        if (
                            $month >= 1 &&
                            $month <= 12 &&
                            $day >= 1 &&
                            $day <= 31
                        ) {

                            try {

                                $sourceDate = Carbon::create(
                                    2025,
                                    $month,
                                    $day
                                )->format('Y-m-d');

                                logger()->info('FINAL SOURCE DATE', [
                                    'source_date' => $sourceDate,
                                ]);

                            } catch (\Exception $e) {

                                logger()->warning('INVALID DATE GENERATED', [
                                    'sheet_number' => $sheetNumber,
                                    'month' => $month,
                                    'day' => $day,
                                    'error' => $e->getMessage(),
                                ]);
                            }

                        } else {

                            logger()->warning('INVALID DATE GENERATED', [
                                'sheet_number' => $sheetNumber,
                                'month' => $month,
                                'day' => $day,
                            ]);
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | TOTAL ROWS/COLS
                    |--------------------------------------------------------------------------
                    */

                    $highestRow = $sheet->getHighestRow();

                    $highestColumn = $sheet->getHighestColumn();

                    if ($highestRow <= 1) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | HEADERS
                    |--------------------------------------------------------------------------
                    */

                    $headerRow = [];

                    $headers = $sheet->rangeToArray(
                        "A1:{$highestColumn}1",
                        null,
                        true,
                        true,
                        true
                    )[1];

                    foreach ($headers as $column => $heading) {

                        $heading = strtolower(trim($heading));

                        $heading = preg_replace('/\s+/', ' ', $heading);

                        $headerRow[$heading] = $column;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | LOOP ROWS
                    |--------------------------------------------------------------------------
                    */

                    for ($row = 2; $row <= $highestRow; $row++) {

                        $productName = $this->getCellValue(
                            $sheet,
                            $headerRow,
                            [
                                'product name',
                                'product',
                                'name'
                            ],
                            $row
                        );

                        $image = $this->getImageUrl(
                            $sheet,
                            $headerRow,
                            [
                                'product image',
                                'image'
                            ],
                            $row
                        );

                        $storeName = $this->getCellValue(
                            $sheet,
                            $headerRow,
                            [
                                'store name',
                                'supplier'
                            ],
                            $row
                        );

                        $category = $this->getCellValue(
                            $sheet,
                            $headerRow,
                            [
                                'category'
                            ],
                            $row
                        );

                        $storeUrl = $this->getCellUrl(
                            $sheet,
                            $headerRow,
                            [
                                'store url',
                                'source url',
                                'supplier link',
                                'store link',
                                'url'
                            ],
                            $row
                        );

                        $amazonUrl = $this->getCellUrl(
                            $sheet,
                            $headerRow,
                            [
                                'amazon url & asin',
                                'amazon url',
                                'amazon link',
                                'asin'
                            ],
                            $row
                        );

                        $amazonText = $this->getCellValue(
                            $sheet,
                            $headerRow,
                            [
                                'amazon url & asin',
                                'asin'
                            ],
                            $row
                        );

                        preg_match(
                            '/([A-Z0-9]{10})/',
                            (string) $amazonText,
                            $asinMatch
                        );

                        $asin = $asinMatch[1] ?? null;

                        /*
                        |--------------------------------------------------------------------------
                        | SKIP EMPTY ROW
                        |--------------------------------------------------------------------------
                        */

                        if (
                            empty($productName) ||
                            empty($asin)
                        ) {
                            continue;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | OTHER FIELDS
                        |--------------------------------------------------------------------------
                        */

                        $storePrice = $this->cleanPrice(
                            $this->getCellValue(
                                $sheet,
                                $headerRow,
                                ['store price'],
                                $row
                            )
                        );

                        $amzPrice = $this->cleanPrice(
                            $this->getCellValue(
                                $sheet,
                                $headerRow,
                                ['amz price'],
                                $row
                            )
                        );

                        $priceBadge = $this->getCellValue(
                            $sheet,
                            $headerRow,
                            ['price badge'],
                            $row
                        );

                        $netProfit = $this->cleanPrice(
                            $this->getCellValue(
                                $sheet,
                                $headerRow,
                                ['net profit'],
                                $row
                            )
                        );

                        $roi = $this->cleanPercent(
                            $this->getCellValue(
                                $sheet,
                                $headerRow,
                                ['roi'],
                                $row
                            )
                        );

                        $currentBSR = $this->cleanNumber(
                            $this->getCellValue(
                                $sheet,
                                $headerRow,
                                ['current bsr'],
                                $row
                            )
                        );

                        $day90BSR = $this->cleanNumber(
                            $this->getCellValue(
                                $sheet,
                                $headerRow,
                                ['90 day bsr'],
                                $row
                            )
                        );

                        $salesPerMonth = $this->cleanNumber(
                            $this->getCellValue(
                                $sheet,
                                $headerRow,
                                [
                                    'sales / mo',
                                    'sales per month'
                                ],
                                $row
                            )
                        );

                        $fbaSellers = $this->cleanNumber(
                            $this->getCellValue(
                                $sheet,
                                $headerRow,
                                ['# fba sellers'],
                                $row
                            )
                        );

                        $buyBox = $this->getCellValue(
                            $sheet,
                            $headerRow,
                            ['buy box?'],
                            $row
                        );

                        $notes = $this->getCellValue(
                            $sheet,
                            $headerRow,
                            ['notes / coupons'],
                            $row
                        );

                        $shipping = $this->getCellValue(
                            $sheet,
                            $headerRow,
                            ['shipping'],
                            $row
                        );

                        $cashback = $this->cleanPercent(
                            $this->getCellValue(
                                $sheet,
                                $headerRow,
                                ['cashback %'],
                                $row
                            )
                        );

                        $giftcard = $this->cleanPercent(
                            $this->getCellValue(
                                $sheet,
                                $headerRow,
                                ['giftcard %'],
                                $row
                            )
                        );

                        /*
                        |--------------------------------------------------------------------------
                        | SAVE
                        |--------------------------------------------------------------------------
                        */

                        NewLead::updateOrCreate(
                            [
                                'source_id' => $source->id,
                                'asin' => $asin,
                            ],
                            [

                                'date' => $sourceDate,

                                'name' => $productName,

                                'supplier' => $storeName,

                                'category' => $category,

                                'url' => $storeUrl,

                                'amazon_url' => $amazonUrl,

                                'asin' => $asin,

                                'image' => $image,

                                'store_price' => $storePrice,

                                'sell_price' => $amzPrice,

                                'promo' => $priceBadge,

                                'net_profit' => $netProfit,

                                'roi' => $roi,

                                'bsr' => $currentBSR,

                                'bsr_90_day' => $day90BSR,

                                'sales_per_month' => $salesPerMonth,

                                'fba_sellers' => $fbaSellers,

                                'buy_box' => $buyBox,

                                'notes' => $notes,

                                'shipping' => $shipping,

                                'cashback_percent' => $cashback,

                                'giftcard_percent' => $giftcard,

                                'created_by' => auth()->id(),
                            ]
                        );
                    }
                }
                /*
                |--------------------------------------------------------------------------
                | DELETE TEMP FILE
                |--------------------------------------------------------------------------
                */

                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
                
            }

           
        }
        return response()->json([
            'success' => true,
            'files' => $filesData
        ]);
    }

    public function importGoogleSheet1(Request $request)
    {
        $request->validate([
            'sheet_url' => 'required'
        ]);

        $folderUrl = $request->sheet_url;

        /*
        |--------------------------------------------------------------------------
        | GET FOLDER ID
        |--------------------------------------------------------------------------
        */

        preg_match('/folders\/([a-zA-Z0-9_-]+)/', $folderUrl, $matches);

        $folderId = $matches[1] ?? null;

        if (!$folderId) {

            return response()->json([
                'success' => false,
                'message' => 'Invalid Google Drive Folder URL'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | GOOGLE API
        |--------------------------------------------------------------------------
        */

        $apiKey = 'AIzaSyBgL8higaoj5KrM0Rvyxjdk0I8DfALX3pM';

        $url = "https://www.googleapis.com/drive/v3/files?q='{$folderId}'+in+parents+and+(mimeType='application/vnd.google-apps.spreadsheet'+or+mimeType='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')&fields=files(id,name,mimeType)&key={$apiKey}";

 

        $response = file_get_contents($url);

        $data =    json_decode($response, true);

        $files = $data['files'] ?? [];

        if (empty($files)) {

            return response()->json([
                'success' => false,
                'message' => 'No Google Sheets Found'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | LOOP ALL SHEETS
        |--------------------------------------------------------------------------
        */

        foreach ($files as $googleFile) {

            $spreadsheetId = $googleFile['id'];

            $spreadsheetTitle = trim(
                $googleFile['name'] ?? 'Untitled'
            );

            /*
            |--------------------------------------------------------------------------
            | DOWNLOAD XLSX
            |--------------------------------------------------------------------------
            */

            $downloadUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=xlsx";

            $tempFile = storage_path(
                'app/temp_' . $spreadsheetId . '.xlsx'
            );

            file_put_contents(
                $tempFile,
                file_get_contents($downloadUrl)
            );

            $spreadsheet = IOFactory::load($tempFile);

            /*
            |--------------------------------------------------------------------------
            | SOURCE CREATE
            |--------------------------------------------------------------------------
            */
            // dd($spreadsheetTitle);
            $source = NewSource::updateOrCreate(
                [
                    'list_name' => $spreadsheetTitle,
                ],
                [
                    'employee_id' => auth()->id() ?? null,
                ]
            );

            /*
            |--------------------------------------------------------------------------
            | LOOP TABS
            |--------------------------------------------------------------------------
            */

            foreach ($spreadsheet->getWorksheetIterator() as $sheet) {

                $sheetTitle = trim($sheet->getTitle());

                /*
                |--------------------------------------------------------------------------
                | SKIP BLANK SHEET
                |--------------------------------------------------------------------------
                */

                if (strtolower($sheetTitle) == 'blank') {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | DATE LOGIC
                |--------------------------------------------------------------------------
                */

                $sourceDate = null;

                preg_match('/\d+/', $sheetTitle, $match);

                $sheetNumber = $match[0] ?? null;

                if ($sheetNumber) {

                    $sheetNumber = (string) $sheetNumber;

                    $month = substr($sheetNumber, 0, 1);

                    $day = substr($sheetNumber, 1);

                    if ($day == '') {

                        $day = $month;
                        $month = 1;
                    }

                    $month = (int) $month;
                    $day = (int) $day;

                    if (
                        $month >= 1 &&
                        $month <= 12 &&
                        $day >= 1 &&
                        $day <= 31
                    ) {

                        $sourceDate = Carbon::create(
                            2025,
                            $month,
                            $day
                        )->format('Y-m-d');
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | TOTAL ROWS/COLS
                |--------------------------------------------------------------------------
                */

                $highestRow = $sheet->getHighestRow();

                $highestColumn = $sheet->getHighestColumn();

                if ($highestRow <= 1) {
                    continue;
                }

                /*
                |--------------------------------------------------------------------------
                | HEADERS
                |--------------------------------------------------------------------------
                */

                $headerRow = [];

                $headers = $sheet->rangeToArray(
                    "A1:{$highestColumn}1",
                    null,
                    true,
                    true,
                    true
                )[1];

                foreach ($headers as $column => $heading) {

                    $heading = strtolower(trim($heading));

                    $heading = preg_replace('/\s+/', ' ', $heading);

                    $headerRow[$heading] = $column;
                }

                /*
                |--------------------------------------------------------------------------
                | LOOP ROWS
                |--------------------------------------------------------------------------
                */

                for ($row = 2; $row <= $highestRow; $row++) {

                    $productName = $this->getCellValue(
                        $sheet,
                        $headerRow,
                        [
                            'product name',
                            'product',
                            'name'
                        ],
                        $row
                    );

                    $image = $this->getImageUrl(
                        $sheet,
                        $headerRow,
                        [
                            'product image',
                            'image'
                        ],
                        $row
                    );

                    $storeName = $this->getCellValue(
                        $sheet,
                        $headerRow,
                        [
                            'store name',
                            'supplier'
                        ],
                        $row
                    );

                    $category = $this->getCellValue(
                        $sheet,
                        $headerRow,
                        [
                            'category'
                        ],
                        $row
                    );

                    $storeUrl = $this->getCellUrl(
                        $sheet,
                        $headerRow,
                        [
                            'store url',
                            'source url',
                            'supplier link',
                            'store link',
                            'url'
                        ],
                        $row
                    );

                    $amazonUrl = $this->getCellUrl(
                        $sheet,
                        $headerRow,
                        [
                            'amazon url & asin',
                            'amazon url',
                            'amazon link',
                            'asin'
                        ],
                        $row
                    );

                    $amazonText = $this->getCellValue(
                        $sheet,
                        $headerRow,
                        [
                            'amazon url & asin',
                            'asin'
                        ],
                        $row
                    );

                    preg_match(
                        '/([A-Z0-9]{10})/',
                        (string) $amazonText,
                        $asinMatch
                    );

                    $asin = $asinMatch[1] ?? null;

                    /*
                    |--------------------------------------------------------------------------
                    | SKIP EMPTY ROW
                    |--------------------------------------------------------------------------
                    */

                    if (
                        empty($productName) ||
                        empty($asin)
                    ) {
                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | OTHER FIELDS
                    |--------------------------------------------------------------------------
                    */

                    $storePrice = $this->cleanPrice(
                        $this->getCellValue(
                            $sheet,
                            $headerRow,
                            ['store price'],
                            $row
                        )
                    );

                    $amzPrice = $this->cleanPrice(
                        $this->getCellValue(
                            $sheet,
                            $headerRow,
                            ['amz price'],
                            $row
                        )
                    );

                    $priceBadge = $this->getCellValue(
                        $sheet,
                        $headerRow,
                        ['price badge'],
                        $row
                    );

                    $netProfit = $this->cleanPrice(
                        $this->getCellValue(
                            $sheet,
                            $headerRow,
                            ['net profit'],
                            $row
                        )
                    );

                    $roi = $this->cleanPercent(
                        $this->getCellValue(
                            $sheet,
                            $headerRow,
                            ['roi'],
                            $row
                        )
                    );

                    $currentBSR = $this->cleanNumber(
                        $this->getCellValue(
                            $sheet,
                            $headerRow,
                            ['current bsr'],
                            $row
                        )
                    );

                    $day90BSR = $this->cleanNumber(
                        $this->getCellValue(
                            $sheet,
                            $headerRow,
                            ['90 day bsr'],
                            $row
                        )
                    );

                    $salesPerMonth = $this->cleanNumber(
                        $this->getCellValue(
                            $sheet,
                            $headerRow,
                            [
                                'sales / mo',
                                'sales per month'
                            ],
                            $row
                        )
                    );

                    $fbaSellers = $this->cleanNumber(
                        $this->getCellValue(
                            $sheet,
                            $headerRow,
                            ['# fba sellers'],
                            $row
                        )
                    );

                    $buyBox = $this->getCellValue(
                        $sheet,
                        $headerRow,
                        ['buy box?'],
                        $row
                    );

                    $notes = $this->getCellValue(
                        $sheet,
                        $headerRow,
                        ['notes / coupons'],
                        $row
                    );

                    $shipping = $this->getCellValue(
                        $sheet,
                        $headerRow,
                        ['shipping'],
                        $row
                    );

                    $cashback = $this->cleanPercent(
                        $this->getCellValue(
                            $sheet,
                            $headerRow,
                            ['cashback %'],
                            $row
                        )
                    );

                    $giftcard = $this->cleanPercent(
                        $this->getCellValue(
                            $sheet,
                            $headerRow,
                            ['giftcard %'],
                            $row
                        )
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | SAVE
                    |--------------------------------------------------------------------------
                    */

                    NewLead::updateOrCreate(
                        [
                            'source_id' => $source->id,
                            'asin' => $asin,
                        ],
                        [

                            'date' => $sourceDate,

                            'name' => $productName,

                            'supplier' => $storeName,

                            'category' => $category,

                            'url' => $storeUrl,

                            'amazon_url' => $amazonUrl,

                            'asin' => $asin,

                            'image' => $image,

                            'store_price' => $storePrice,

                            'sell_price' => $amzPrice,

                            'promo' => $priceBadge,

                            'net_profit' => $netProfit,

                            'roi' => $roi,

                            'bsr' => $currentBSR,

                            'bsr_90_day' => $day90BSR,

                            'sales_per_month' => $salesPerMonth,

                            'fba_sellers' => $fbaSellers,

                            'buy_box' => $buyBox,

                            'notes' => $notes,

                            'shipping' => $shipping,

                            'cashback_percent' => $cashback,

                            'giftcard_percent' => $giftcard,

                            'created_by' => auth()->id(),
                        ]
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | DELETE TEMP FILE
            |--------------------------------------------------------------------------
            */

            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'All Google Sheets Imported Successfully'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET IMAGE URL
    |--------------------------------------------------------------------------
    */

    private function getImageUrl($sheet, $headerRow, $possibleHeaders, $row)
    {
        foreach ($possibleHeaders as $header) {

            if (isset($headerRow[$header])) {

                $column = $headerRow[$header];

                $cell = $sheet->getCell($column . $row);

                $formula = $cell->getValue();

                if (!$formula) {
                    return null;
                }

                preg_match('/https?:\/\/[^"]+/', $formula, $matches);

                return $matches[0] ?? null;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | GET CELL VALUE
    |--------------------------------------------------------------------------
    */

    private function getCellValue($sheet, $headerRow, $possibleHeaders, $row)
    {
        foreach ($possibleHeaders as $header) {

            if (isset($headerRow[$header])) {

                $column = $headerRow[$header];

                $cell = $sheet->getCell($column . $row);

                try {

                    $value = $cell->getCalculatedValue();

                } catch (\Exception $e) {

                    $value = $cell->getValue();
                }

                if (is_string($value)) {

                    if (
                        str_contains($value, '=IMAGE(') ||
                        str_contains($value, '#NAME?')
                    ) {
                        return null;
                    }

                    $value = preg_replace('/\s+/', ' ', trim($value));
                }

                return $value;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | GET URL
    |--------------------------------------------------------------------------
    */

    private function getCellUrl($sheet, $headerRow, $possibleHeaders, $row)
    {
        foreach ($possibleHeaders as $header) {

            if (isset($headerRow[$header])) {

                $column = $headerRow[$header];

                $cell = $sheet->getCell($column . $row);

                $url = $cell->getHyperlink()->getUrl();

                if (!empty($url)) {
                    return trim($url);
                }

                return null;
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | CLEAN PRICE
    |--------------------------------------------------------------------------
    */

    private function cleanPrice($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = preg_replace('/[^0-9.]/', '', (string) $value);

        if (substr_count($value, '.') > 1) {

            $parts = explode('.', $value);

            $value = array_shift($parts) . '.' . implode('', $parts);
        }

        return is_numeric($value)
            ? (float) $value
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | CLEAN PERCENT
    |--------------------------------------------------------------------------
    */

    private function cleanPercent($value)
    {
        return $this->cleanPrice($value);
    }

    /*
    |--------------------------------------------------------------------------
    | CLEAN NUMBER
    |--------------------------------------------------------------------------
    */

    private function cleanNumber($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        return preg_replace('/[^0-9]/', '', (string) $value);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX LEADS
    |--------------------------------------------------------------------------
    */

    public function getSourceLeads($id = null)
    {
        $query = NewLead::query();

        // agar source selected hai to filter karo
        if ($id && $id != 'all') {
            $query->where('source_id', $id);
        }

        $leads = $query->latest('date')->get();

        return response()->json($leads);
    }
}