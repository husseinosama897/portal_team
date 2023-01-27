<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class product extends Model
{
    protected $fillable = [
     
        'name',
        'unit',
        'type',
        'value',
       'barcode',
       'size',
      'dis',
  'img',
 'p_account3_id',
 'p_account4_id',

    ];




    public function selling_account3(){
      return $this->belongsto(sub_account3::class,'selling_account3_id');
    }
    public function selling_account4(){
      return $this->belongsto(sub_account3::class,'selling_account4_id');
    }


    public function p_account3(){
      return $this->belongsto(sub_account3::class,'p_account3_id');
    }
    public function p_account4(){
      return $this->belongsto(sub_account4::class,'p_account4_id');
    }




    public function suppiler(){
      return $this->belongsto(supplier::class,'supplier_id');
    }

    public function category(){
      return $this->belongsto(category::class,'group');
    }
    public function unit(){
      return $this->belongsto(unit::class,'unit');
    }

   
    
    public function offerpaid(){
      return $this->belongstomany(offerpaid::class,'offerpaid_product')
      ->withPivot('offerpaid_id','product_id','quantity')->orderBy('created_at', 'DESC');
  }

    
  public function inventorym(){
    return $this->belongstomany(inventory::class,'inventory_product')
    ->withPivot('inventory_id','product_id','quantity');
}

public function supplierorder(){
  return $this->belongsTomany(supplierorder::class,'supplierorders_product')->withPivot('supplierorder_id','product_id','quantity');
}



public function pricing_supplier(){

  return $this->belongstomany(pricing_supplier::class,'pricing_supplier_product')->withPivot('pricing_supplier_id','selling','product_id','quantity');

}

public function showprice(){
  return $this->belongstomany(product::class,'showprice_product')->withPivot('showprice_id','product_id','quantity');
}


  public function purchase_order(){
    return $this->belongstomany(purchase_order::class,'purchase_order_product')->withPivot('purchase_order_id','product_id','quantity','selling');
}



}
