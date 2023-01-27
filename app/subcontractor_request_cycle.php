<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class subcontractor_request_cycle extends Model
{
protected $fillable = [
  'status'
];

    public function subcontractor_real(){
        return $this->belongsto(subcontractor::class,'subcontractor_id');
    }
    
    public function role(){
      return $this->belongsto(role::class,'role_id');
    }
    
    
    public function comment_subcontractor_cycle(){
      return $this->hasone(comment_subcontractor_cycle::class,'subcontractor_request_cycle_id');
    }
    
    
}
