<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class contract extends Model
{
    

    protected $fillable = [
        'vacations',
        'weekly_vacation',
        'project_id',
         'contract_date',
       'contract_ex',
       'first_name',
       'Transportation_Allowance',
                'Communication_Allowance',
                'Food_Allowance',
                'Other_Allowance',
     'salary_per_hour',
     'salary_per_month',
     'fahther_name',
     'address',
     'type_of_identity',
     'identity',
'user_id',
     'working_hours',
     'country',
     'identity_date',
     'identity_source',
     'build_number' ,
     'city',
     'street',
     'phone',
     'age'

    ];

    public function project(){
        
        return $this->belongsto(project::class,'project_id');

    }

    public function user(){
        
        return $this->belongsto(User::class,'user_id');

    }

}
