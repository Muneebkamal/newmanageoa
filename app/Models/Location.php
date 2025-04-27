<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    protected $fillable = ['location','type','street_address', 
        'apartment', 
        'city', 
        'state', 
        'country', 
        'zip','created_by','updated_by'];
}
