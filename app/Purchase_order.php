<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Purchase_order extends Model
{
   protected $fillable = [
    'quotation',
    'project_id',
    'closed',
    'status',
    'supplier_id',
    'draft',
    'matrial_request_id',
    'on_vat',
    'cash',
    'subtotal',
    'percentage_discount',
    'discount',
    
    'cost_center_id',
    'date',
'subject',
   'material_avalibility',
  'transportation',
 'delivery_date',
   'cc',
    'ref',
    'user_id',
    'to',
     'order_for',
     'total',
     'vat',
     'paid',
   ];

public function paids(){
   return $this->HasMany(purchase_paid::Class,'purchase_order_id');
}

   public function attributes(){
      return $this->belongstomany(product::class,'purchase_order_products')
      ->withPivot('dis','qty',
      'unit','unit_price','total','purchase_order_id','product_id');
   }
   public function attributes2(){
      return $this->hasMany(purchase_order_product::class,'purchase_order_id');
    
   }

   public function note(){
      return $this->HasMany(payment_term::class,'purchase_order_id');
   }


   public function project(){
      return $this->belongsto(project::class,'project_id');
   }

   public function user(){
      return $this->belongsto(User::class,'user_id');
   }
   

public function cost_center(){
   return $this->belongsto(cost_center::class,'cost_center_id');
}

 public function mention(){
    
   return $this->belongstoMany(user::class,'purchase_order_user')->withPivot(['purchase_order_id','user_id']);
}

public function purchase_order_cycle(){
   return $this->HasMany(purchase_order_cycle::class,'purchase_order_id');
}


public function supplier(){
   return $this->belongsto(supplier::class,'supplier_id');
}

public function purchase_order_attachment(){
   return $this->HasMany(purchase_order_attachment::class, 'purchase_id');
}
public function payment_term (){
   return $this->HasMany(payment_term::class, 'purchase_order_id');
}
public function purchase_order_attr (){
   return $this->HasMany(Purchase_order_attr::class, 'purchase_order_id');
}
}
