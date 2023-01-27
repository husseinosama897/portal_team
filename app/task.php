<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class task extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'dis',
        'user_id',
        'start_at',
        'expires_in',
        'point',
        'noteManager',
        'status',
        'note'
    ];
    public function manager(){
        return $this->belongsto(User::class,'user_id');
    }

    public function user(){
        return $this->belongstomany(User::class);
    }

    public function taskEmpAttachments(){
        return $this->hasMany(taskEmpAttachments::Class,'task_id');
    }

    public function attachment_task_manager(){
        return $this->hasMany(attachment_task_manager::Class,'task_id');
    }

    
}
