<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class comment_matrial_cycle extends Model
{
 protected $fillable = [
    'matrial_request_cycle_id',
    'content',
    'user_id',
 ];

 public function attachment_matrial_cycle(){
   return $this->hasMany(attachment_matrial_cycle::class,'comment_matrial_cycle_id');
 }

 
 public function user(){
  return $this->belongsto(User::class,'user_id');
}

}
