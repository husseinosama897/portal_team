<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class project_overall extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'percentage_performance',
        'cash_out',
        'percentage_attendance',
        'cash_in',
        'num_of_performers',
        'num_of_attendance',
        'performance_point',
        'time_attendance',
        'project_id'
    ];
}
