<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class service_cycle extends Model
{
    use HasFactory;

    protected $fillable = [
      'step',
     'status',
'flowwork_step_id',
'role_id',
'service_id',
];
     
 public function service(){
    return $this->belongsto(service::class,'service_id');
}

public function role(){
  return $this->belongsto(role::class,'role_id');
}


public function service_comment_cycle(){
  return $this->hasone(service_comment_cycle::class,'service_cycle_id');
}


}
