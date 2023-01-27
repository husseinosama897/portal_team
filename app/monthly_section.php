<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class monthly_section extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'point',
        'section_id',
        'percentage_attendance',
        'num_of_attendance',
        'percentage_performance',
        'saving_percentage',
        'cost_reduction',
        'num_of_performers',
              'time',
              'num_marketing_project',
              'percentage_marketing_project',
              'percentage_deal',
              'num_deal',
              'percentage_pricing',
              'project_pricing'
    ];
}
