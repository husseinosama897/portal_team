<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class subcontractor extends Model
{
    protected $fillable = [
     'project_id',
        'user_id',
        'status',
      'total',
      'closed',
      'vat',
      'cost_center_id',
       'date',
        'subject',
        'ref',
        'contractor_id',
        'to',  
        'invoice_ON',
'payment_no',
'contract_withsubcontractor_id',
'contract_no',
'paid'
    ];
    
    
   public function attributes(){
    return $this->HasMany(subcontractor_attr::class,'subcontractor_id');
 }

 public function files(){
    return $this->HasMany(subcontractor_file::class,'subcontractor_id');
 }



 public function cost_center(){
   return $this->belongsto(cost_center::class,'cost_center_id');
}

 public function mention(){
    
    return $this->belongstoMany(User::class,'subcontractor_User')->withPivot(['subcontractor_id','user_id']);
 }

 public function subcontractor(){
   return $this->HasMany(subcontractor_request_cycle::class,'subcontractor_id');
}



public function user(){
   return $this->belongsto(User::class,'user_id');
}

public function project(){
   return $this->belongsto(project::class,'project_id');
}


public function contract_withsubcontractor(){
   return $this->belongsto(contract_withsubcontractor::Class,'contract_withsubcontractor_id');
}



public function paids(){
   return $this->HasMany(subcontractor_paid::class,'subcontractor_id');
}



}
