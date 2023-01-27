<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class salary extends Model
{
    use HasFactory;

    protected $fillable = [
       'user_id',
      'month',
    'salary_day',

    'salary_Hour',

'transportation_allowance',

'communication_allowance',

'food_allowance',

'other_allowance',

'working_days',

'working_hour',
'over_time',
'Deduction',
'Amount',

'approved_by'
    ];

    public function employee(){
        return $this->belongsto(User::class,'user_id');
    }

    public function user_approved_by(){
        return $this->belongsto(User::class,'approved_by');
    }
}
