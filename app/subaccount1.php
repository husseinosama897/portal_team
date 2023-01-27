<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class subaccount1 extends Model
{
    protected $fillable = [
        'name',
        'code',

        'account_id',
    ];


    public function sub_account3(){
        return $this->hasMany(sub_account3::class,'sub_account1_id');
    }


    public function accountbank(){
        return $this->belongsto(accountbank::class,'account_id');
    }

    public function ledger(){
        return $this->hasone(ledger::class,'subaccount1_id');
     }

     

}
