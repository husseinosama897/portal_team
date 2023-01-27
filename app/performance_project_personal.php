<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class performance_project_personal extends Model
{
    use HasFactory;
    protected $fillable = [
        'date',
             'point',
             'project_id',
             'user_id'
 ];
}
