<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class workflow extends Model
{
    protected $fillable = [
        'name'
    ];

    public function flowworkStep(){
        return $this->HasMany(flowworkStep::class,'work_flow_id');
    }
}
