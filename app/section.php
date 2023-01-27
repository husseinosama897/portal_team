<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class section extends Model
{
    use HasFactory;

    protected $fillable  =[
        'name',
        'marketing_project',
        'percentage_attendance',
        'percentage_performance',
        'percentage_deal',
        'percentage_cash_flow',
        'percentage_section'
    ];

    public function role(){
        return $this->hasMany(role::class,'section_id');
    }

    public function monthly_section(){
        return $this->HasMany(monthly_section::class,'section_id');
    }
}
