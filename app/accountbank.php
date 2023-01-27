<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class accountbank extends Model
{
   protected $fillable = [
    'code',
'name',
 'type',
'number',
'initial_balance',
  'orderpackage_id',
    
   ];

   public function subaccount1(){
      return $this->hasMany(subaccount1::class,'account_id');
   }
   

public function ledger(){
   return $this->hasone(ledger::class,'accountbank_id');
}



}
