<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class purchase_order_cycle extends Model
{
    protected $fillable = [
        'purchase_order_id',
     'step',
        'status',
        'flowwork_step_id',
       'role_id',
      ];
    
      public function purchase_order(){
        return $this->belongsto(Purchase_order::class,'purchase_order_id')->orderBy('created_at','DESC');
    }
    
    public function role(){
      return $this->belongsto(role::class,'role_id');
    }
    
    
    public function comment_purchase_order_cycle(){
      return $this->hasone(comment_purchase_cycle::class,'purchase_order_cycle_id');
    }
    
    
    
}
