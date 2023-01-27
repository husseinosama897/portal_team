<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class flowworkStep extends Model
{
    protected $fillable = [
        'role_id',
        'step',
        'work_flow_id',
        
    ];


    public function role(){
        return $this->belongsto(role::class,'role_id');
    }
}
