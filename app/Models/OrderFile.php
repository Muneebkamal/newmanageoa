<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderFile extends Model
{
    use HasFactory;
    protected $fillable = ['order_id','name', 'path', 'note', 'uploaded_by'];
}
