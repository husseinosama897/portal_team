<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class performance_monthly_project extends Model
{
    use HasFactory;

    protected $fillable = [
           'date',
                'point',
                'project_id',
                'num_of_performers',
                'percentage'
                
    ];
}
