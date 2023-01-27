<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class pricing_supplier extends Model
{
    protected $fillable = [
  
   
        'status',
        'supplier_id',
        'on_vat',
        'cash',
        'subtotal',
        'percentage_discount',
        'discount',
        
        'date',
    'subject',
     
   
        'ref',
        'user_id',
        'to',
         'order_for',
         'total',
         'vat'
           ];


           
   public function user(){
    return $this->belongsto(User::class,'user_id');
 }
 



public function files(){
    return $this->hasMany(pricing_supplier_attachment::class,'pricing_supplier_id');
}

           public function product(){

            return $this->belongstomany(product::class,'pricing_supplier_products')->withPivot('pricing_supplier_id','selling','product_id','quantity');
      
        }

        public function note(){
            return $this->HasMany(payment_pricing::class,'pricing_supplier_id');
         }
      
         
         public function pricing_supplier_cycle(){
            return $this->HasMany(pricing_supplier_cycle::class,'pricing_supplier_id');
         }


   public function attributes(){
            return $this->belongstomany(product::class,'pricing_supplier_products')
            ->withPivot('dis','qty',
            'unit','unit_price','total','product_id');
         }
 public function attributes2(){
            return $this->hasMany(pricing_supplier_product::class,'pricing_supplier_id');
          
         }
        
    public function supplier(){
        return $this->belongsto(supplier::class,'supplier_id');
    }
}
