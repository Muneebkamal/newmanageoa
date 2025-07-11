<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $appends = ['lead_tags','bundles'];
    protected $fillable = [
        'source_id',
        'bundle',
        'date',
        'name',
        'asin',
        'url',
        'supplier',
        'cost',
        'sell_price',
        'net_profit',
        'roi',
        'bsr',
        'category',
        'promo',
        'notes',
        'currency',
        'coupon',
        'is_hazmat',
        'quantity',
        'is_disputed',
        'is_rejected',
        'created_by'
    ];

    public function source()
    {
        return $this->belongsTo(Source::class, 'source_id');
    }
    public function getLeadTagsAttribute(){
        $tagsss = explode(',', $this->tags); // Get tag IDs as an array
        $tags = Tag::whereIn( 'id', $tagsss)->get();
        return $tags;
    }
    public function getBundlesAttribute(){
        $bundles = Item::where('lead_id',$this->id)->get();
        return $bundles;
    }
    public function createdBy()
    {
        return $this->hasOne(User::class,'id','created_by');
    }
}
