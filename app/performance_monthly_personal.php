<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class performance_monthly_personal extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'point',
        'user_id'
    ];
}
