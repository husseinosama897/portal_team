<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cost_center extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'budget',
        'name',
    ];
    


    public function purchase_order(){

        return $this->HasMany(Purchase_order::class,'cost_center_id');
    
       }
    
       public function petty_cash(){ 
        return $this->HasMany(petty_cash::class,'cost_center_id');
       }
    
       public function subcontractor(){
        return $this->HasMany(subcontractor::class,'cost_center_id');
       }


       public function project(){
        return $this->belongsto(project::class,'project_id');
       }
}
