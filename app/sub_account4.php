<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class sub_account4 extends Model
{
    protected $fillable = [
        'name',
        'ename',
        'code',
        'dis',
        'sub_account3_id',
    ];


    public function ledger(){
        return $this->hasone(ledger::class,'sub_account4_id');
     }


    public function sub_account3(){
        return $this->belongsto(sub_account3::class,'sub_account3_id');
    }

    public function creditor_sub_account4(){
        return $this->hasmany(easy_restriction::class,'creditor_sub_account4_id');
    }


    public function debtor_sub_account4(){
        return $this->hasmany(easy_restriction::class,'debtor_sub_account4_id');
    }





    
    public function manule_creditor_sub_account4(){
        return $this->hasmany(entry_manual_account::class,'creditor_sub_account4_id');
    }

    
    public function manule_debtor_sub_account4(){
        return $this->hasmany(entry_manual_account::class,'debtor_sub_account4_id');
    }


}
