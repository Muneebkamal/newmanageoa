<?php

namespace App\Exports;

use App\Models\LineItem;
use App\Models\Order;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;


class OrdersExport implements FromCollection, WithHeadings, WithMapping, WithCustomCsvSettings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        // Ensure startDate and endDate are only dates, not including time
        $startDate = Carbon::parse($this->startDate)->startOfDay();  // Set to the beginning of the day
        $endDate = Carbon::parse($this->endDate)->endOfDay();  // Set to the end of the day

        return Order::whereBetween('date', [$startDate, $endDate])->orderBy('date','desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Name',
            'Source Link',
            'Host Link',
            'Order #',
            'AMZ Short',
            'AMZ ASIN',
            'AMZ TITLE',
            'Quantity Purchased',
            'Cost Per Unit',
            'SKU Total',
            'Order Total',
            'BLANK1',
            'BLANK2',
            'Tax Paid',
            'Tax %',
            'Order Notes',
            'Item Notes',
            'Cash Back',
            'Credit Card',
            'Received Status',
            'UPC Match ASIN?',
            'Title Match?',
            'Image Match?',
            'New ASIN?',
            'Shipping Notes',
            'Shipping Status',
            'MSKU',
            'List Price',
            'Order Status',
            'Min List Price',
            'Max List Price',
            'UPC',
            'Destination',
        ];
    }

    public function map($order): array
    {
        $mappedData = [];
        $linteITems = LineItem::where('order_id', $order->id)->where('is_rejected',0)->get();
        foreach ( $linteITems as $lineItem) {
            $amzoneUrl = '=HYPERLINK("https://www.amazon.com/dp/'.$lineItem->asin.'")';
            $mappedData[] = [
                $order->date,
                $lineItem->name,
                $lineItem->source_url,
                '-',
                $order->order_id,
                $lineItem->asin,
                $amzoneUrl,
                $lineItem->name,
                $lineItem->unit_purchased,
                $lineItem->buy_cost,
                $lineItem->sku_total,
                $order->total,
                '',
                '',
                $lineItem->tax_paid,
                $lineItem->tax_percent,
                $lineItem->order_note,
                $lineItem->product_buyer_notes,
                $order->cash_back_percentage,
                $order->card_used,
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                $lineItem->msku,
                $lineItem->list_price,
                $order->status,
                $lineItem->min,
                $lineItem->max,
                $lineItem->upc,
                $order->destination,
                
            ];
        }

        return $mappedData;
    }
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '"',
            'line_ending' => PHP_EOL,
            'use_bom' => true, // Optional: For better compatibility
        ];
    }
    
}
