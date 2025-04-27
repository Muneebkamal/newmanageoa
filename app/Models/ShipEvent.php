<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipEvent extends Model
{
    use HasFactory;
    protected $fillable = [
        'shipping_batch',
        'items',
        'qc_check',
        'expire_date',
        'asin_override',
        'product_name_override',
        'min_orverride',
        'list_price_orverride',
        'max_orverride',
        'condition',
        'product_upc',
        'msku_orderride',
        'shipping_notes',
        'description_matches_flag',
        'image_matches_flag',
        'title_matches_flag',
        'upc_matches_flag',
        'order_id',
        'order_item_id'        
    ];
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id'); // Assuming the primary key in Orders is 'id'
    }

    public function orderItem()
    {
        return $this->belongsTo(LineItem::class, 'order_item_id', 'id'); // Assuming the primary key in OrderItems is 'id'
    }
    public function shippingbatch()
    {
        return $this->belongsTo(Shipping::class, 'shipping_batch', 'id'); // Assuming the primary key in OrderItems is 'id'
    }
}
