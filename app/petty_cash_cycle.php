<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class petty_cash_cycle extends Model
{
  protected $fillable = [
    'petty_cash_id',
 'step',
    'status',
   
  ];

    public function petty_cash(){
        return $this->belongsto(petty_cash::class,'petty_cash_id');
    }
    
    public function role(){
      return $this->belongsto(role::class,'role_id');
    }
    
    
    public function comment_petty_cash_cycle(){
      return $this->hasone(comment_petty_cash_cycle::class,'petty_cash_cycle_id');
    }
    
}
