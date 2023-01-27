<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class matrial_request extends Model
{
 protected $fillable = [
   'project_id',
   'user_id',
   'status',
   'to',
   'total',
   'vat',
   'ref',
   'date',
  'subject',
  'content',
 ];
 public function matrial_request_cycle(){
   return $this->HasMany(matrial_request_cycle::class,'matrial_request_id');
}

 public function attributes(){
    return $this->HasMany(matrial_attr::class,'matrial_request_id');
 }

 public function files(){
   return $this->HasMany(matrial_request_attachment::class,'matrial_request_id');
}


 public function note(){
    return $this->HasMany(matrial_condition::class,'matrial_request_id');
 }


 public function project(){
    return $this->belongsto(project::class,'project_id');
 }

 public function user(){
   return $this->belongsto(User::class,'user_id');
}
 

public function mention(){
  
 return $this->belongstoMany(User::class,'matrial_request_user')->withPivot(['matrial_request_id','user_id']);
}


}
