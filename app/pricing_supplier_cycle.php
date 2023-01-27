<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pricing_supplier_cycle extends Model
{
    use HasFactory;


    protected $fillable = [
      'step',
     'status',
'flowwork_step_id',
'role_id',
'pricing_supplier_id',
];


        
    public function pricing_supplier(){
        return $this->belongsto(pricing_supplier::class,'pricing_supplier_id')->orderBy('created_at','DESC');
    }
    
    public function role(){
      return $this->belongsto(role::class,'role_id');
    }
    
    
    public function pricing_supplier_comment_cycle(){
      return $this->hasone(pricing_supplier_comment_cycle::class,'pricing_supplier_cycle_id');
    }
    

}
