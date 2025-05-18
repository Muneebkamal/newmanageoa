<?php

namespace App\Services;

use Carbon\Carbon;
use Keepa\API\Request;
use Keepa\API\ResponseStatus;
use Keepa\helper\CSVType;
use Keepa\helper\CSVTypeWrapper;
use Keepa\helper\KeepaTime;
use Keepa\helper\ProductAnalyzer;
use Keepa\helper\ProductType;
use Keepa\KeepaAPI;
use Keepa\objects\AmazonLocale;
use Illuminate\Support\Facades\Http;

class KeepaService
{
    protected $api;

    public function __construct()
    {
        $this->api = new KeepaAPI('60tr09241j7lk7uo6bsk3p09bpfk6bi9m8bems70rlgrl5fbcfankv63ciai872e'); // Load API key from .env
    }

    public function getProductDetails($asin)
    {
        $request = Request::getProductRequest(AmazonLocale::US, 0, "2023-01-01", "2025-12-31", 0, true, [$asin]);
        $response = $this->api->sendRequestWithRetry($request);
    
        if ($response->status !== ResponseStatus::OK) {
            return ['error' => 'Failed to fetch product data'];
        }
    
        $products = [];
    
        foreach ($response->products as $product) {
            dd($product);
            if ($product->productType == ProductType::STANDARD || $product->productType == ProductType::DOWNLOADABLE) {
                if (!empty($product->csv[CSVType::AMAZON])) {
                    $amazonPrices = $product->csv[CSVType::AMAZON];
                    $hasValidPrices = false;
                    foreach ($amazonPrices as $price) {
                        if ($price != -1) {
                            $hasValidPrices = true;
                            break;
                        }
                    }
                    if (!$hasValidPrices) {
                        return "No valid Amazon price data available for this ASIN.\n";
                        continue;
                    }
                }
                

                
                // ✅ Amazon Price
                $currentAmazonPrice = ProductAnalyzer::getLast($product->csv[CSVType::AMAZON], CSVTypeWrapper::getCSVTypeFromIndex(CSVType::AMAZON));
                $amazonPriceCsv = $product->csv[CSVType::AMAZON];
                
                

                $currentAmazonPrice = ($amazonPriceCsv[0] !== -1) ? $amazonPriceCsv[0] / 100 : 'Out of stock';

                // ✅ FBA Price (if available)
                $currentFbaPrice = ProductAnalyzer::getLast($product->csv[CSVType::NEW_FBA], CSVTypeWrapper::getCSVTypeFromIndex(CSVType::NEW_FBA));

                $currentFbaPrice = ($currentFbaPrice !== -1) ? $currentFbaPrice / 100 : 'No FBA';

                // ✅ 90-day Weighted Mean Price
                $weightedMean90days = ProductAnalyzer::calcWeightedMean(
                    $product->csv[CSVType::AMAZON], KeepaTime::nowMinutes(), 90, CSVTypeWrapper::getCSVTypeFromIndex(CSVType::AMAZON)
                );
                $weightedMean90days = ($weightedMean90days !== -1) ? $weightedMean90days / 100 : 'No Data';

                // ✅ Net Profit Calculation
                $costPrice = 20; // Replace this with the actual cost price from your data
                $amazonFees = 5; // Estimated Amazon Fees (Adjust based on product category)
                $netProfit = ($currentAmazonPrice !== 'No FBA') ? ($currentAmazonPrice - $costPrice - $amazonFees) : 'No FBA';

                // ✅ ROI Calculation
                $roi = ($currentAmazonPrice !== 'No FBA' && $costPrice > 0) ? round(($netProfit / $costPrice) * 100, 2) . '%' : 'No FBA';
                // dd($product->lastUpdate);
               
                $lastUpdateDate = $this->convertKeepaTime($product->lastPriceChange);
                // $lastUpdateDate = Carbon::createFromTimestamp($lastUpdateTimestamp)->toDateTimeString();
                $products[] = [
                    'asin' => $product->asin,
                    'title' => $product->title,
                    'current_price' => $currentAmazonPrice,
                    'average_90_day_price' => $weightedMean90days,
                    'last_update' => $lastUpdateDate,
                ];
            }
        }
    
        return $products;
    }


    
    function convertKeepaTime($keepaMinutes) {
         // Keepa's reference start time (2011-01-01 00:00:00 UTC)
        $keepaStart = Carbon::create(2011, 1, 1, 0, 0, 0, 'UTC'); 

        // Add the Keepa minutes
        return $keepaStart->addMinutes($keepaMinutes)->toDateTimeString();
        // $keepaStart = Carbon::createFromTimestampUTC(1293840000); // 2011-01-01 00:00:00 UTC
        // return $keepaStart->addMinutes($keepaMinutes)->toDateTimeString();
    }
    public function getLatestAmazonPriceFromCsv($product)
    {
        $csv = $product['csv'] ?? [];

        if (!isset($csv[0]) || empty($csv[0])) {
            return null; // No Amazon price data
        }
        $amazonPrices = $csv[0]; // Index 0 = amazon price history
        return $amazonPrices[0] /100;
    }
}
