<?php



namespace App;



use Illuminate\Database\Eloquent\Model;



class entry_manual extends Model

{

    protected $fillable = [

        'orderpackage_id',

       

            

        'date',

        'line',

        'code',
        'hide',

        'type',

              'creditor_id', 

            'value',   

           'dis',

    ];



    public function sub_account3(){

        return $this->belongsto(sub_account3::class,'creditor_id');

    }





    public function entry_manual_account(){

        return $this->hasMany(entry_manual_account::class,'entry_manual_id');

    }

}

