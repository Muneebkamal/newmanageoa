<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewLead extends Model
{
    use HasFactory;
    protected $fillable = [
    'source_id',
    'created_by',
    'date',
    'image',
    'name',
    'supplier',
    'category',
    'url',
    'asin',
    'dual_link_keepa',
    'cost',
    'sell_price',
    'price_30_day',
    'price_90_day',
    'fba_fees',
    'extra_costs',
    'change_30_day',
    'change_90_day',
    'net_profit',
    'roi',
    'bsr',
    'bsr_90_day',
    'sales_per_month',
    'fba_sellers',
    'buy_box',
    'promo',
    'notes',
    'shipping',
    'cashback_percentage',
    'giftcard_percentage',
    'is_hazmat',
    'is_disputed',
    'is_rejected',
    'reason',
    ];
}
