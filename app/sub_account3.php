<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class sub_account3 extends Model
{
    protected $fillable = [
        'name',
        'code',
'ename',
'dis',
        'sub_account1_id',
    ];


    public function sub_account4(){
        return $this->hasMany(sub_account4::class,'sub_account3_id');
    }

    public function subaccount1(){
        return $this->belongsto(subaccount1::class,'sub_account1_id');
    }

    public function ledger(){
        return $this->hasone(ledger::class,'sub_account3_id');
     }


     
     public function creditor_sub_account3(){
        return $this->hasmany(easy_restriction::class,'creditor_sub_account3_id');
    }

    
    public function debtor_sub_account3(){
        return $this->hasmany(easy_restriction::class,'debtor_account3_id');
    }



    
    public function manule_creditor(){
        return $this->hasmany(entry_manual::class,'creditor_id');
    }



    public function manule_creditor_sub_account3(){
        return $this->hasmany(entry_manual_account::class,'creditor_sub_account3_id');
    }

    
    public function manule_debtor_sub_account3(){
        return $this->hasmany(entry_manual_account::class,'debtor_account3_id');
    }



    


}

