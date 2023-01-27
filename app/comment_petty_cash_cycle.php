<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class comment_petty_cash_cycle extends Model
{
    protected $fillable = [
        'petty_cash_cycle_id',
        'content',
        'user_id',
      ];

        public function attachment_petty_cash_cycle(){
          
            return $this->hasMany(attachment_petty_cash_cycle::class,'comment_petty_cash_cycle_id');
    
        }

        public function user(){
          return $this->belongsto(User::class,'user_id');
        }

        
}
