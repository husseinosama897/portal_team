<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class joboffer extends Model
{
    use HasFactory;

    protected $fillable = [
       'date',
       'name',
       'email',
'subject',
'content',
 'contract_type',
       'work_location',
       'status',
       'user_id',
       'benefit_check',
    'salary',
        'ref',
        
    ];

    public function condition(){
        return $this->hasMany(Condition::class,'joboffer_id');
    }

    public function benefits(){
        return $this->hasMany(Benefit_salary::class,'joboffer_id');
    }

public function files(){
    return $this->hasMany(joboffer_attachment::class,'joboffer_id');
}

public function joboffer_cycle(){
    return $this->HasMany(joboffer_cycle::class,'joboffer_id');
 }


}
