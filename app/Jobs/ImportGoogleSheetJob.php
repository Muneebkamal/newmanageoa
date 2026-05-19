<?php

namespace App\Jobs;

use App\Models\NewLead;
use App\Models\NewSource;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportGoogleSheetJob implements ShouldQueue
{
    use Dispatchable,
        InteractsWithQueue,
        Queueable,
        SerializesModels;

    public $timeout = 1200;

    protected $fileId;

    protected $sheetName;

    protected $accessToken;

    public function __construct(
        $fileId,
        $sheetName,
        $accessToken
    ) {
        $this->fileId = $fileId;

        $this->sheetName = $sheetName;

        $this->accessToken = $accessToken;
    }

    public function handle()
    {
        ini_set('memory_limit', '-1');

        set_time_limit(0);
        try {

            // SKIP IF ALREADY IMPORTED
            $alreadyImported =
                DB::table('google_sheet_syncs')
                ->where(
                    'google_file_id',
                    $this->fileId
                )
                ->exists();

            if ($alreadyImported) {
                return;
            }

            // DOWNLOAD FILE
            $downloadUrl =
                "https://www.googleapis.com/drive/v3/files/" .
                $this->fileId .
                "?alt=media";

            $response =
                Http::withToken($this->accessToken)
                ->timeout(300)
                ->get($downloadUrl);

            if (!$response->successful()) {

                Log::error(
                    'Failed Download: ' .
                    $this->sheetName
                );

                return;
            }

            // SAVE TEMP FILE
            $tempFile = storage_path(
                'app/temp_' .
                $this->fileId .
                '.xlsx'
            );

            file_put_contents(
                $tempFile,
                $response->body()
            );

            // LOAD FILE
            $spreadsheet = IOFactory::load($tempFile);

           $source = NewSource::updateOrCreate(
                [
                    'list_name' => $this->sheetName ,
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
            // SAVE IMPORT RECORD
            DB::table('google_sheet_syncs')->insert([

                'google_file_id' => $this->fileId,

                'sheet_name' => $this->sheetName,

                'last_synced_at' => now(),

                'created_at' => now(),

                'updated_at' => now(),
            ]);

            // DELETE TEMP FILE
            if (file_exists($tempFile)) {

                unlink($tempFile);
            }

        } catch (\Exception $e) {

            Log::error($e->getMessage());
        }
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

    public function getSourceLeads($id)
    {
        $leads = NewLead::where('source_id', $id)
            ->latest('date')
            ->get();

        return response()->json($leads);
    }
}