<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class entry_manual_account extends Model
{
    protected $fillable = [
       'debtor_account3_id',
 'debtor_account3_id',
        
      'debtor_sub_account4_id',
        'value1',

        'creditor_sub_account4_id',
        'creditor_sub_account3_id',

        'value',
        'date',
    'creditor_sub_account3_id',
        'supplier_id',
        'customer_id',

        'product_id',
       'creditor_sub_account4_id',
        
        
'entry_manual_id',
    ];

    public function sub_account3(){
        return $this->belongsto(sub_account3::class,'debtor_account3_id');
    }
    public function sub_account4(){
        return $this->belongsto(sub_account4::class,'debtor_sub_account4_id');
    }

    public function creditor_sub_account3(){
        return $this->belongsto(sub_account3::class,'creditor_sub_account3_id');
    }
    public function creditor_sub_account4(){
        return $this->belongsto(sub_account4::class,'creditor_sub_account4_id');
    }

}
