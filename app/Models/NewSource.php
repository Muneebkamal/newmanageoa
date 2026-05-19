<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class NewSource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'list_name',
        'employee_id'
    ];
    public function leads()
    {
        return $this->hasMany(NewLead::class, 'source_id');
    }
}
