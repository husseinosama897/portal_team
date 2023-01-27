<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\contract;
use Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class attendingUserController extends Controller
{


  public function performance(User $User){
    $data = $User->Attending_and_leaving;
    return view('managers.performance')->with('user',$User);
  }
    
    public function index(){
        return view('managers.attending');
    }
  
  public function jsonlaborer(request $request)
  {
    $data = User::query();
    $data = $data->where('laborer','!==',1);
   $data =  $data->with(['Attending_and_leaving'=>function($q)  use($request) {
  
    if($request->from){
      $q = $q->where('attending_time','>=',$request->from);
    }
  
  if($request->to){
    $q = $q->where('attending_leaving','>=',$request->to);
  }
  
  return $q;
  
   }])->withsum('Attending_and_leaving','time_difference')->withsum('Attending_and_leaving','min');
   
    
  $data =   $data->paginate(10);
  
    return response()->json(['data'=>$data]);
  }
}
