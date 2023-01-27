<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class personal_overall extends Model
{
    protected $fillable = [
        'user_id',
        'date'
      ,
      'cost_reduction',
        'time',
        'percentage_attendance',
        'num_of_performers',
        'num_of_attendance',
        'percentage_performance',
        'percentage_attendance',
        'percentage_section',
        'marketing_project',
        'marketing_deal',
        'percentage_deal',
    ];
    use HasFactory;

    public function user(){
      return $this->belongsto(User::class,'user_id');
    }
}
