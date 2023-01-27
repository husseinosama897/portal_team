<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class supplierorder extends Model
{
    protected $fillable = [
       'code',
       'supplier_id',
'fq',
'orderpackage_id' 
   ];

    public function product(){
        return $this->belongsTomany(product::class,'supplierorders_product')->withPivot('supplierorder_id','product_id','quantity');
    }


    public function supplier(){
        return $this->belongsto(supplier::class,'supplier_id');
    }
}
