<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class employee_cycle extends Model
{
    public function employee(){
        return $this->belongsto(employee::class,'employee_id');
    }
    
    public function role(){
      return $this->belongsto(role::class,'role_id');
    }
    

    public function comment_employee_cycle(){
      return $this->hasone(comment_employee_cycle::class,'employee_cycle_id');
    }
}
