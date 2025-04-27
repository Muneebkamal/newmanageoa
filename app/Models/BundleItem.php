<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BundleItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'notes',
        'supplier',
        'source_url',
        'cost',
        'coupon_code',
        'promo',
        'item_id',
        'is_buylist'
    ];
}
