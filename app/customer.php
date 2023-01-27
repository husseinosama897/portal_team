<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class customer extends Model
{
   protected $fillable = [ 
    'personal',
    'type_customer',
  'customer_name',
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
   
   public function entry_manual_account(){
    return $this->hasMany(entry_manual_account::class,'customer_id');
  }

   public function invoice(){
     return $this->hasMany(invoice::class,'customer_id');
   }
}
