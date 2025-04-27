<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'buylist_id',
        'is_buylist',
        'name',
        'asin',
        'buy_cost',
        'sku_total',
        'unit_purchased',
        'product_buyer_notes',
        'upc',
        'list_price',
        'min',
        'max',
        'category',
        'supplier',
        'source_url',
        'order_note',
        'selling_price',
        'net_profit',
        'bsr',
        'promo',
        'coupon_code',
        'is_hazmat',
        'is_disputed',
        'msku',
        'is_rejected',
        'rejection_reason',
        'sales_tax_rate',
        'total_units_purchased',
        'total_units_received',
        'total_units_shipped',
        'unit_errors',
        'tax_paid',
        'tax_percent',
        'created_by'
    ];    
    public function bundles(){
        return $this->hasMany(BundleItem::class, 'item_id');
    }
    public function createdBy(){
        return $this->belongsTo(User::class, 'created_by');
    }
}
