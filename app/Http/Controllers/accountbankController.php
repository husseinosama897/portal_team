<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\accountbank;
use App\subaccount1;
use App\sub_account3;
use App\sub_account4;

class accountbankController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function accountbankpage(){
 
        return view('accountbank.create');
        
    }



public function editaccount3( $sub_account3){

$data = sub_account3::where('id',$sub_account3)->with(['sub_account4'])->with(['subaccount1'=> function($q){
  return $q->with('sub_account3');
}])->first();


return view('accountbank.edit')->with(['data'=>$data]);


  
}

public function editaccount4( $sub_account4){

    $data = sub_account4::where('id',$sub_account4)->with('sub_account3')->first();
    
    return view('accountbank.edit')->with(['data'=>$data]);
    

  
}

public function create(request $request){

if($request->level ==  2){
  $sub_account3 = sub_account3::create([
    'name'=>$request->name,
    'ename'=>$request->ename,
    'dis'=>$request->dis,
    'code'=>$request->code,
    'sub_account1_id'=>$request->sub_account1_id,
  ]);
}

elseif($request->level == 3){
  $sub_account3 = sub_account4::create([
    'name'=>$request->name,
    'ename'=>$request->ename,
    'dis'=>$request->dis,
    'code'=>$request->code,
    'sub_account3_id'=>$request->sub_account1_id,
  ]);
}


}


public function update3(request $request,sub_account3 $sub_account3){


 $sub_account3->update([
    'name'=>$request->name,
    'ename'=>$request->ename,
    'dis'=>$request->dis,
    'code'=>$request->code,

  ]);




}

public function update4(request $request,sub_account4 $sub_account4){



  $sub_account4->update([
    'name'=>$request->name,
    'ename'=>$request->ename,
    'dis'=>$request->dis,
    'code'=>$request->code,
 
  ]);



}

public function accountbanktable(){

  return view('accountbank.table');
    
  }
  
  public function accountbankjson(){
   
  $pr = accountbank::with(['subaccount1'=> function($q){
    
    $q->with(['sub_account3'=>function($r){
      return $r->with('sub_account4');
      }]);
    }
    
    
    ])->paginate(10);
  return response()->json(['data'=>$pr]);
  
  
    
  }

  public function accountbanksele(){
 
  $pr = accountbank::with(['subaccount1'=> function($q){
    
   $q->with(['sub_account3'=>function($r){
    return $r->with('sub_account4');
    }]);
  }
  
  
  ])->get()->chunk(10);
  return response()->json(['data'=>$pr]);
  
  
    
  }
  
  
  public function delete1(accountbank $accountbank){
 
      auth()->user()->orderpackage->accountbank()->whereIn('id',explode(",",$ids))->delete();
    
  }

    public function delete2($subaccount1){
  

        auth()->user()->orderpackage->accountbank()->subaccount1()->whereIn('id',explode(",",$subaccount1))->delete();
      

  
  }

  public function delete3(sub_account3 $sub_account3){


   $sub_account3->delete();
    
  }

    public function delete4(sub_account4 $sub_account4){

    
     $sub_account4->delete();
      


}


  public function updateacc(accountbank $accountbank,request $request){
 
        $this->validate($request,[
           
            'name'=>['string','max:255','required'],
             'type'=>['string','max:255','required'],
            'number'=>['string','max:255','required'],
            'initial_balance'=>['required','numeric'],
        ]);

        $accountbank->update([
              
            'name'=>$request->name,
             'type'=>$request->type,
            'number'=>$request->number,
            'initial_balance'=>$request->initial_balance,
        ]);

}
}
