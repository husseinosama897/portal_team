<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class employee extends Model
{
    protected $fillable = [
        'project_id',
        'date',
    'subject',
      'status',
       'user_id',
        'loan_value',
        'loan_option',
        'ref',
        'to',
        
         'content',
       ];

       
public function employee_loan()
{
  return $this->hasmany(employee_loan::class,'employee_id');
}
   public function mention(){
     return $this->belongstoMany(User::class,'employee_user')->withPivot(['employee_id','user_id']);
 }

 public function project(){
  return $this->belongsto(project::class,'project_id');
}
public function user(){
  return $this->belongsto(User::class,'user_id');
}


 public function employee_cycle(){
  return $this->HasMany(employee_cycle::class,'employee_id');
}

}
