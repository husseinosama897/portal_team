<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class timesheet_daily_section extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'section_id',
           'time',
           'date',
         
         ];

}
