<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class report extends Model
{
    use HasFactory;

    protected $fillable = [
     'total_cash_in',
'total_cash_out',
'date',
    ];
}
