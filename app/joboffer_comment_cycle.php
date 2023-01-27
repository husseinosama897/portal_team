<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class joboffer_comment_cycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'subcontractor_request_cycle_id',
        'content',
        'user_id',
      ];


        public function joboffer_cycle(){
            return $this->hasMany(joboffer_cycle::class,'joboffer_cycle_id');
          }
          

          public function files(){
            return $this->hasMany(joboffer_cycle_attachment::class,'joboffer_comment_cycle_id');
          }
          



          public function user(){
            return $this->belongsto(User::class,'user_id');
          }
          
 

}
