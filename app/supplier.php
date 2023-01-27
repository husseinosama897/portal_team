<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class supplier extends Model
{
  protected $fillable = [ 
    'personal',
    'type_customer',
  'customer_name',
    'vat',

    'package_id',
  'postal_code',
  'status',
'building_num',
'comp',
'street_name',
'group_id',
    'orderpackage_id',
'tax_number',

'representative',
'phone',
'location',
'city',
    
'email',
    

   ];

   public function purchase_order(){
     return $this->HasMany(Purchase_order::class,'supplier_id');
   }
   

   public function entry_manual_account(){
    return $this->hasMany(entry_manual_account::class,'supplier_id');
  }

}
