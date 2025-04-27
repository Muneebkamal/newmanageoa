<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'order_item_id',
        'issue_type',
        'status',
        'cancelled',
        'cc_charged',
        'refunded',
        'received',
        'issue_notes',
        'item_quantity',
        'refund_actual',
        'refund_expected',
        'supplier_notes',
        'tracking_number',
    ];
    public function LineItem()
    {
        return $this->belongsTo(LineItem::class, 'order_item_id','id');
    }
    
    
}
