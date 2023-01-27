<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class financial_daily_report extends Model
{
    use HasFactory;

    protected $fillable = [
 
    'type',
       'amount',
       'date',
       'ref',
       'user_id',
       'status'
    ];

    public function item(){
        return $this->hasMany(daily_financial_report_item::class,'daily_financial_report_id');
    }
}
