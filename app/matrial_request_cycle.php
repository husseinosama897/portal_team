<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class matrial_request_cycle extends Model
{
  protected $fillable = [
    'step',
    'status',
    'flowwork_step_id',
    'role_id',
    'matrial_request_id'
  ];

  public function matrial_request(){
    return $this->belongsto(matrial_request::class,'matrial_request_id');
}

public function role(){
  return $this->belongsto(role::class,'role_id');
}


public function comment_matrial_cycle(){
  return $this->hasone(comment_matrial_cycle::class,'matrial_request_cycle_id');
}


public function user(){
  return $this->belongsto(User::class,'user_id');
}





}
