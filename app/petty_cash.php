<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class petty_cash extends Model
{
   
   protected $fillable = [
    'quotation',
    'closed',
'project_id',
'matrial_request_id',
'draft',
 'user_id',
'status',
'total',
'vat',
  'date',
  'cost_center_id',
'expected_amount',
  'subject',
   'material_avalibility',	
 'transportation',
'delivery_date',
'cc',
 'ref',
'to',
   'content',
   'paid',

   ];

   public function paids(){
      return $this->HasMany(petty_cash_paid::class,'petty_cash_id');
   }
   
   public function project(){
    return $this->belongsto(project::class,'project_id');
 }

 public function attributes(){
    return $this->HasMany(petty_attr::class,'petty_cash_id');
 }

 public function user(){
   return $this->belongsto(User::class,'user_id');
}

public function petty_cash_attachment(){
   return $this->HasMany(petty_cash_attachment::class, 'petty_id');
}

 
public function mention(){
  
 return $this->belongstoMany(User::class,'petty_cash_user')->withPivot(['petty_cash_id','user_id']);
}


public function petty_cash_cycle(){
   return $this->HasMany(petty_cash_cycle::class,'petty_cash_id');
}


public function cost_center(){
   return $this->belongsto(cost_center::class,'cost_center_id');
}


}
