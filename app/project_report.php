<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class project_report extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'total_cash_out',
        'project_id',
        'total_cash_in'
    ];
}
