<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class contract_withsubcontractorcycle extends Model
{
    use HasFactory;

    protected $fillable = [
       'step',
      'status',
 'flowwork_step_id',
'role_id',
'contract_withsubcontractor_id',
];

public function contract_withsubcontractor(){
    return $this->belongsto(contract_withsubcontractor::class,'contract_withsubcontractor_id');
}

public function role(){
  return $this->belongsto(role::class,'role_id');
}


public function comment_contract_withsubcontractor_cycle(){
  return $this->hasone(comment_contract_withsubcontractor_cycle::class,'contract_withsubcontractor_cycle_id');
}




}
