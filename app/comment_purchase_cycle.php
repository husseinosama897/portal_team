<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class comment_purchase_cycle extends Model
{
    protected $fillable = [
        'purchase_order_cycle_id',
        'content',
        'user_id',
      ];
        public function attachment_purchase_order_cycle(){
            return $this->hasMany(attachment_purchase_cycle::class,'comment_purchase_cycle_id');
          }

          public function user(){
            return $this->belongsto(User::class,'user_id');
          }

          
}
