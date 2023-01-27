<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class comment_contract_withsubcontractor_cycle extends Model
{
    use HasFactory;

protected $fillable = [
  'contract_withsubcontractor_cycle_id',
  'content',
  'user_id',
];

    public function attachment_contract_withsubcontractor_cycle(){
        return $this->HasMany(attachment_contract_withsubcontractor_cycle::class, 'comment_contract_withsubcontractor_cycle_id');
     }


     public function user(){
        return $this->belongsto(User::class,'user_id');
      }

      public function role(){
        return $this->belongsto(role::class,'role_id');
      }


      

}
