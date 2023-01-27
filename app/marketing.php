<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class marketing extends Model
{
    use HasFactory;

    protected $fillable = [

        'ref',
        'delivery_date',
     'date',
        'subject',
       'content',
       'status',
       'user_id'
    ];

    public function user(){
        return $this->Belongsto(User::class,'user_id');
    }


    public function attachment(){
        return $this->HasMany(marketing_attachment::class,'marketing_id');

    }
    
    public function tender_comment(){
        return $this->Hasone(tender_comment::class,'marketing_id');

    }
}
