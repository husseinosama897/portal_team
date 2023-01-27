<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class comment_site_cycle extends Model
{
    protected $fillable = [
        'site_cycle_id',
        'content',
        'user_id',
      ];
        public function attachment_site_cycle(){
            return $this->hasMany(attachment_site_cycle::class,'comment_site_cycle_id');
          }

          
        public function user(){
          return $this->belongsto(User::class,'user_id');
        }

        
}
