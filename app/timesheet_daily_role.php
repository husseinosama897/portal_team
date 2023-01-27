<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class timesheet_daily_role extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'role_id',
           'time',
           'date',
        
         ];
}
