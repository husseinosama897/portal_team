<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class contractor extends Model
{
    protected $fillable = [ 
        'personal',
        'type_customer',
      'contractor_name',
        'stype',
        'comp',
        'package_id',
      'postal_code',
      'status',
    'building_num',
    'street_name',
    'group_id',
        'country',
    'tax_number',
    
    'representative',
    'phone',
    'location',
    'city',
        
    'email',
        
    
       ];
   
       
    use HasFactory;


    public function cws(){

      return $this->hasMany(contract_withsubcontractor::class,'contractor_id');

    }

    
    public function file(){
      
      return $this->hasMany(contractor_attacment::class,'contractor_id');

    }
}
