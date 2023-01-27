<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class joboffer_cycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'step',
       'status',
  'flowwork_step_id',
 'role_id',
 'joboffer_id',
 ];
 
 public function joboffer(){
     return $this->belongsto(joboffer::class,'joboffer_id');
 }
 
 public function role(){
   return $this->belongsto(role::class,'role_id');
 }
 
 
 public function joboffer_comment_cycle(){
   return $this->hasone(joboffer_comment_cycle::class,'joboffer_cycle_id');
 }
 

 
}
