<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class timesheet_monthly_project extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'project_id',
           'time',
           'date',
           'percentage'  
         ];

}
