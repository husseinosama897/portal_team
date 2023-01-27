<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance_report extends Model
{
    use HasFactory;

    protected $fillable = [
       'ref',
        'project_id',
      'date',
    'rate',
   'note',
     'status',
     
    ];
}
