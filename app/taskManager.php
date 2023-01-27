<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class taskManager extends Model
{
    use HasFactory;

    public function task(){
        return $this->belongsto(task::class,'task_id');
    }


}
