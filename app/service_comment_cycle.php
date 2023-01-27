<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class service_comment_cycle extends Model
{
    use HasFactory;

protected $fillable = [
    'service_cycle_id',
    'content',
    'user_id',
];

    public function files(){
        return $this->hasMany(service_cycle_attacment::class,'service_comment_cycle_id');
    }
    public function user(){
        return $this->belongsto(User::class,'user_id');
      }
}
