<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class timesheet_monthly_personal extends Model
{
    use HasFactory;
    protected $fillable = [
   'user_id',
      'time',
   'month',
       'year',
        
    ];
}
