<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class daily_financial_report_item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'daily_financial_report_id',
       'type',
        'supplier_id' ,
        'purchase_order_id',
        'petty_cash_id',
        'pay',
        'subcontractor'
    
    ];

    public function purchase_order(){
        return $this->belongsto(Purchase_order::class,'purchase_order_id');
    }

    public function petty_cash(){
        return $this->belongsto(petty_cash::class,'petty_cash_id');
    }

    public function subcontractor(){
        return $this->belongsto(subcontractor::class,'subcontractor_id');
    }

    public function supplier(){
        return $this->belongsto(supplier::class,'supplier_id');
    }
    

    public function attachment(){
        return $this->HasMany(daily_financial_report_item_attachment::class,'daily_financial_report_item_id');
    }

    
}
