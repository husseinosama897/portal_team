<?php

namespace App\Models;

use App\contract_withsubcontractor;
use App\employee;
use App\matrial_request;
use App\notification;
use App\petty_cash;
use App\project;
use App\Purchase_order;
use App\rfq;
use App\role;
use App\salary;
use App\site;
use App\subcontractor;
use App\supplier;
use App\task;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name' ,
        'email' ,
        'password' ,
        'role_id',
        'laborer',
        'sign',
        'image',
        'emp_on',
        'supplier_id'
];



public function personal_overall(){
    return $this->hasMany(personal_overall::class,'user_id');
}

public function pricing_supplier(){
    return $this->hasMany(pricing_supplier::class,'user_id');
}


public function service(){
    return $this->hasMany(service::class,'user_id');
}


public function joboffer(){
    return $this->hasMany(joboffer::class,'user_id');
}


public function user_file(){
    return $this->hasmany(user_file::class,'user_id');
}

public function user_daily_report(){
    return $this->belongstomany(dailyReport::class,'user_daily_report')->withpivot('performance','commitment');
}

public function daily_report(){
    return $this->hasMany(dailyReport::class,'supervisor_id');
}


public function timesheet_project_personal(){
    return $this->hasMany(timesheet_project_personal::class,'user_id');
 }




public function marketing(){
    return $this->HasMany(marketing::class,'user_id');
}


public function projectmanager()
{
    return $this->HasMany(project::class,'projectmanager_id');
}
public function Attending_and_leaving(){
    return $this->hasMany(Attending_and_leaving::class,'user_id');
}
public function contract(){
    return $this->hasone(contract::class,'user_id');
}

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function contract_withsubcontractor(){
        return $this->HasMany(contract_withsubcontractor::class,'user_id')->orderBy('created_at','DESC');
    }

public function suppliersystem(){
    return $this->belongsto(supplier::class,'supplier_id');
}


    public function salary(){
        return $this->HasMany(salary::class,'user_id')->orderBy('created_at','DESC');
    }


    public function purchase(){
        return $this->HasMany(Purchase_order::class,'user_id')->orderBy('created_at','DESC');
    }
    public function subcontractor(){
        return $this->HasMany(subcontractor::class,'user_id')->orderBy('created_at','DESC');
    }
    public function rfq(){
        return $this->HasMany(rfq::class,'user_id')->orderBy('created_at','DESC');
    }
    public function employee(){
        return $this->HasMany(employee::class,'user_id')->orderBy('created_at','DESC');
    }
    public function site(){
        return $this->HasMany(site::class,'user_id')->orderBy('created_at','DESC');
    }
    public function petty_cash(){
        return $this->HasMany(petty_cash::class,'user_id')->orderBy('created_at','DESC');
    }
    public function matrial_request(){
        return $this->HasMany(matrial_request::class,'user_id')->orderBy('created_at','DESC');
    }


       public function task(){
        return $this->belongstomany(task::class);
    }

    public function role(){
        return $this->belongsto(role::class,'role_id');
    }

    public function project(){
        return $this->belongsto(project::class,'project_id');
    }

    public function notification(){
        return $this->hasMany(notification::class,'user_id_to')->orderBy('created_at','DESC');
    }


}
