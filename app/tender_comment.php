<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tender_comment extends Model
{
    use HasFactory;

protected $fillable = [
    'marketing_id',
    'content',
];

    public function attachment(){
        return $this->hasMany(tender_attachment::class,'tender_comment_id');
    }
}
