<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class attributes_contract extends Model
{
    use HasFactory;
    protected $fillable = [
   'contract_withsubcontractor_id',
     'name',
'unit',
 'unit_price',
 'expctedqty',
    'previous_qty',
    ];
}
