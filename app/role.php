<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class role extends Model
{

protected $fillable = [
    'name',
    'section_id'
];




public function service_cycle(){
    return $this->HasMany(service_cycle::class,'role_id')->orderBy('id','DESC');
}

public function joboffer_cycle(){
    return $this->HasMany(joboffer_cycle::class,'role_id')->orderBy('id','DESC');
}


public function pricing_supplier_cycle(){
    return $this->HasMany(pricing_supplier_cycle::class,'role_id')->orderBy('id','DESC');
}



    public function contract_withsubcontractor_cycle(){
        return $this->HasMany(contract_withsubcontractorcycle::class,'role_id')->orderBy('id','DESC');
    }



    public function petty_cash_cycle(){
        return $this->HasMany(petty_cash_cycle::class,'role_id')->orderBy('id','DESC');
    }

    public function matrial_request_cycle(){
        return $this->HasMany(matrial_request_cycle::class,'role_id')->orderBy('id','DESC');
    }
    public function rfq_cycle(){
        return $this->HasMany(rfq_cycle::class,'role_id')->orderBy('id','DESC');
    }
    
    public function employee_cycle(){
        return $this->HasMany(employee_cycle::class,'role_id')->orderBy('id','DESC');
    }

    public function site_cycle(){
        return $this->HasMany(site_cycle::class,'role_id')->orderBy('id','DESC');
    }
    public function subcontractor(){
        return $this->HasMany(subcontractor_request_cycle::class,'role_id')->orderBy('id','DESC');
    }

    public function purchase_order_cycle(){
        return $this->HasMany(purchase_order_cycle::class,'role_id');
    }

    public function user(){
        return $this->HasMany(User::class,'role_id');
    }
    
    public function section(){
        return $this->belongsto(section::class,'section_id');
    }

    public function permission(){
        return $this->belongstomany(permission::class,'permission_role');
    }
}
