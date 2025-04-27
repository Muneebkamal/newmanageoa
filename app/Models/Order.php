<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Order extends Model
{
    use HasFactory;
    // Define the fillable attributes
    protected $fillable = [
        'order_id',
        'source',
        'destination',
        'email',
        'status',
        'date',
        'card_used',
        'cash_back_source',
        'cash_back_percentage',
        'note',
        'pre_tax_discount',
        'post_tax_discount',
        'shipping_cost',
        'sales_tax',
        'is_sale_tax_shipping',
        'total',
        'subtotal',
        'sales_tax_rate',
        'total_units_purchased',
        'total_units_received',
        'total_units_shipped',
        'unit_errors',
        'tax_paid',
        'tax_percent',
        'created_by',
        'buyer_id'
    ];
    public function LineItems(){
         return $this->hasMany(LineItem::class, 'order_id', 'id'); // Adjust foreign and local keys as per your schema
    }
    public function createdBy(){
        return $this->hasOne(User::class, 'id');
    }
    protected static function booted()
    {
        static::creating(function ($order) {
            // Set the auth_user_id if the user is authenticated
            if (Auth::check()) {
                $order->created_by = Auth::id();  // Set the authenticated user's ID
            }
        });
    }
}
