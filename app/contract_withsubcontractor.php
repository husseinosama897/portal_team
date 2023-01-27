<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class contract_withsubcontractor extends Model
{
    use HasFactory;

    protected $fillable = [
        'contractor_id',
        'user_id',
    'ref',
       'date',
       'total',
       'status',
       'project_id'

    ];

    public function contract_withsubcontractor_cycle(){
        return $this->HasMany(contract_withsubcontractorcycle::class,'contract_withsubcontractor_id');
     }

     public function contract_withsubcontractor_attachment(){
        return $this->HasMany(contract_withsubcontractor_attachment::class, 'contract_withsubcontractor_id');
     }
   

     public function condition(){
        return $this->hasMany(contract_condition::class,'withsubcontractor_id');
     }


     

     public function contractor(){
      return $this->belongsto(contractor::class,'contractor_id');
     }
     
public function attributes(){
   return $this->hasMany(attributes_contract::class,'contract_withsubcontractor_id');
}

     public function invoice(){
      return $this->hasMany(subcontractor::class,'contract_withsubcontractor_id');
     }

public function user(){
    return $this->belongsto(User::class,'user_id');



}

}
