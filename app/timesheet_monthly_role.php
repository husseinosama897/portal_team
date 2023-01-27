<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class timesheet_monthly_role extends Model
{
    use HasFactory;

    protected $fillable = [
        'role_id',
           'time',
           'month',
           'year'  
         ];
}
