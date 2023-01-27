<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invoice extends Model
{
    use HasFactory;

    protected $fillable = [
      'project_id',
      'subtotal',
       'vat',
       'total',
        'path',
       'date',
      'description',
      'code'
    ];

    public function project(){
    return  $this->belongsto(project::class,'project_id');
    }


   
}
