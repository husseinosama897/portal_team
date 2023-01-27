<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class purchase_paid extends Model
{
    use HasFactory;

    protected $fillable = [
        'total',
        'purchase_order_id'
    ];
    public function attachment(){
return $this->HasMany(paid_attachment::class,'purchase_paid_id');
    }
}
