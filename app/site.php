<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class site extends Model
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
     return $this->belongstoMany(User::class,'site_user')->withPivot(['site_id','user_id']);
 }

 
 public function site_cycle(){
  return $this->HasMany(site_cycle::class,'site_id');
}


public function project(){
  return $this->belongsto(project::class,'project_id');
}

public function user(){
  return $this->belongsto(User::class,'user_id');
}

}
