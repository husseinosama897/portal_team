<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class comment_employee_cycle extends Model
{
    protected $fillable = [
        'employee_cycle_id',
        'content',
        'user_id',
      ];
        public function attachment_employee_cycle(){
            return $this->hasMany(attachment_employee::class,'comment_employee_cycle_id');
          }
       
        public function user(){
          return $this->belongsto(User::class,'user_id');
        }

}
