<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class rfq_cycle extends Model
{
    public function rfq(){
        return $this->belongsto(rfq::class,'rfq_id');
    }
    
    public function role(){
      return $this->belongsto(role::class,'role_id');
    }
    
    
    public function comment_rfq_cycle(){
      return $this->hasone(comment_rfq_cycle::class,'rfq_cycle_id');
    }
}
