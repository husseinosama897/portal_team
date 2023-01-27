<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class comment_rfq_cycle extends Model
{
    protected $fillable = [
        'rfq_cycle_id',
        'content',
        'user_id',
      ];
        public function attachment_rfq_cycle(){
            return $this->hasMany(attachment_rfq_cycle::class,'comment_rfq_cycle_id');
          }
          
        public function user(){
          return $this->belongsto(User::class,'user_id');
        }
        
}
