<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class service extends Model
{
    use HasFactory;
    protected $fillable = [
      'user_id',
   'status',
       'total',
       'date',
       'subject',
       'ref',
      'content',
        'employee_id',
        'employee_request_id',
        'role_id'
    ];

    public function user(){
        return $this->belongsto(User::class,'user_id');
      }

      public function employee(){
        return $this->belongsto(User::class,'employee_id');
      }


    public function service_cycle(){
        return $this->hasmany(service_cycle::class,'service_id');
    }

    public function files(){
        return $this->hasMany(service_attachment::class,'service_id');
    }

public function attributes(){
    return $this->hasMany(service_attribute::class,'service_id');
}

}
