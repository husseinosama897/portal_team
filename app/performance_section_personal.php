<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class performance_section_personal extends Model
{
    use HasFactory;
    protected $fillable = [
        'date',
        'point',
        'section_id',
        'user_id'
    ];
}
