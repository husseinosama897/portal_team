<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class site_cycle extends Model
{


    public function site(){
        return $this->belongsto(site::class,'site_id');
    }
    
    public function role(){
      return $this->belongsto(role::class,'role_id');
    }

    public function comment_site_cycle(){
        return $this->hasone(comment_site_cycle::class,'site_cycle_id');
      }
}
