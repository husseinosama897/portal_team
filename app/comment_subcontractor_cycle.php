<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class comment_subcontractor_cycle extends Model
{


  protected $fillable = [
    'subcontractor_request_cycle_id',
    'content',
    'user_id',
  ];
    public function attachment_subcontractor_cycle(){
        return $this->hasMany(attachment_subcontractor_cycle::class,'comment_subcontractor_cycle_id');
      }
      
      public function user(){
        return $this->belongsto(User::class,'user_id');
      }
      
}
