<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class rfq extends Model
{
   protected $fillable = [
    'project_id',
    'date',
'subject',
  'status',
   'user_id',


    'ref',
    'to',
    
     'content',
   ];

   public function mention(){
    
    return $this->belongstoMany(User::class,'rfq_User')->withPivot(['rfq_id','user_id']);
 }

 public function project(){
    return $this->belongsto(project::class,'project_id');
 }
 

 public function rfq_cycle(){
   return $this->HasMany(rfq_cycle::class,'rfq_id');
}

public function user(){
   return $this->belongsto(User::class,'user_id');
}


}
