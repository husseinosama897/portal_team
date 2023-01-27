<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class notification extends Model
{
    protected $fillable = [
  'type',
     'read',
     'name',
   'user_id_to',
      'user_id_from',
    ];
}
