<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class project extends Model
{
   protected $fillable = [
       'name',
       'log',
       'lat',
       'expenses',
       'budget',
    
       'po_budget',
       'subcontractor_budget',
       'petty_cash_budget',
       'qr',
       'employee_budget',
       "bid_value",
       'duration',
 'receive_date',
 'initial_delivery_date',
 'final_delivery_date',
'po_expenses',
'subcontractor_expenses',
'salaries_expenses',
'petty_cash_expenses',
'projectmanager_id',
 'customer_id',

   ];

public function projectmanager(){
   return $this->belongsto(User::class,'projectmanager_id');
}

public function invoice(){
   return $this->hasMany(invoice::class,'project_id');
}


public function Attending_and_leaving(){
   return $this->hasMany(Attending_and_leaving::class,'project_id');
}


   public function purchase_order(){

    return $this->HasMany(Purchase_order::class,'project_id');

   }

   public function project_overall(){
      return $this->hasMany(project_overall::class,'project_id')->orderby('date','ASC');
   }

  

public function timesheet_project_personal(){
   return $this->hasMany(timesheet_project_personal::class,'project_id');
}


public function project_report(){
   return $this->hasMany(project_report::class,'project_id');
}
   
   public function matrial_request(){

      return $this->HasMany(matrial_request::class,'project_id');
  
     }

   public function petty_cash(){
    
    return $this->HasMany(petty_cash::class,'project_id');

   }
   

public function dailyReport(){
   return $this->hasMany(dailyReport::Class,'project_id');
}


   public function subcontractor(){
    
    return $this->HasMany(subcontractor::class,'project_id');

   }


   public function cost_center(){
    return $this->HasMany(cost_center::class,'project_id');
   }

   public function user(){
    return $this->HasMany(User::class,'project_id');
   }


   public function contract(){
    return $this->HasMany(contract::class,'project_id');
   }



}
