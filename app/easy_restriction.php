<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class easy_restriction extends Model
{
    public function creditor_sub_account3(){
        return $this->belongsto(sub_account3::class,'creditor_sub_account3_id');
    }

    
    public function debtor_sub_account3(){
        return $this->belongsto(sub_account3::class,'debtor_account3_id');
    }


    public function creditor_sub_account4(){
        return $this->belongsto(sub_account4::class,'creditor_sub_account4_id');
    }


    public function debtor_sub_account4(){
        return $this->belongsto(sub_account4::class,'debtor_sub_account4_id');
    }

}
