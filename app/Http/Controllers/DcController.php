<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Purchase_order;
use App\petty_cash;
use App\subcontractor;
use App\employee;
use App\rfq;
use App\matrial_request;
use Carbon\Carbon;
use App\invoice;
use Cache;
use App\report;
use App\workflow;
use App\salary;
class DcController extends Controller
{


  public function jsonSalaries(request $request){

 
 $salary =    salary::Query();



$salary = $salary->orderby('created_at','DESC')->paginate(10);

return response()->json(['data'=>$salary]);

}


public function matrial_requestdata(){
  //-------------- matrial_request -----------------------

    $matrial_request = matrial_request::query();
    
    $matrial_request = $matrial_request->with(['user'=>function($q){
    return $q->select(['id','name']);
          }])->with(['matrial_request_cycle'=>function($q){
            return $q->with('role');
             }])->orderBy('created_at','DESC');
    
    
     
        $matrial_request =  $matrial_request->paginate(10);
    
        return response()->json(['matrial_request'=>$matrial_request]);
   
}



  public function subcontractordata(){
      //-------------- subcontractor -----------------------

        $subcontractor = subcontractor::query();
        
        $subcontractor = $subcontractor->with(['user'=>function($q){
        return $q->select(['id','name']);
              }])->with(['subcontractor'=>function($q){
                return $q->with('role');
                 }])->orderBy('created_at','DESC');
        
        
         
            $subcontractor =  $subcontractor->paginate(10);
        
            return response()->json(['subcontractor'=>$subcontractor]);
       
  }
  
public function summary(){
     $report = report::select(['total_cash_in','total_cash_out','date'])->orderBy('date','ASC')->get();

     return response()->json(['data'=>$report]);
}

  public function podata(request $request){


    //----------------** Purchase_order ---------------------------

   
      $Purchase_order =  Purchase_order::query();

      $Purchase_order =   $Purchase_order->with(['user'=>function($q){
        return $q->select(['id','name']);
              }])->where('draft','!=',1)->with(['purchase_order_cycle'=>function($q){
                return $q->with('role');
                 }]);


          $Purchase_order =  $Purchase_order->orderBy('created_at','DESC')->paginate(10);




return response()->json(['Purchase_order'=>$Purchase_order,

]);

  }
  
  public function petty_cashdata(request $request){
//------------------------**  petty cash  ---------------------------------


      $petty_cash =  petty_cash::query();



     $petty_cash = $petty_cash-> with(['user'=>function($q){
        return $q->select(['id','name']);
              }])->with(['petty_cash_cycle'=>function($q){
                return $q->with('role');
                 }])->orderBy('created_at','DESC');
           

           $petty_cash =  $petty_cash->paginate(10);

            

return response()->json([
'petty_cash'=>$petty_cash,

]);

  }

    public function index(){

      $purchase_orderworkflow =    workflow::where('name','purchase_order')->with(['flowworkStep'=>function($q){
        return     $q->with('role');
         }])->first();

         $petty_cashworkflow =    workflow::where('name','petty_cash')->with(['flowworkStep'=>function($q){
          return     $q->with('role');
           }])->first();

           $matrial_requestworkflow =    workflow::where('name','matrial_request')->with(['flowworkStep'=>function($q){
            return     $q->with('role');
             }])->first();

             $subcontrctorworkflow =    workflow::where('name','subcontractor')->with(['flowworkStep'=>function($q){
              return     $q->with('role');
               }])->first();


      return view('dcc')->with(['purchase_orderworkflow'=>$purchase_orderworkflow,'subcontrctorworkflow'=>$subcontrctorworkflow,
    'matrial_requestworkflow'=>$matrial_requestworkflow,
    'petty_cashworkflow'=>$petty_cashworkflow,
    
    ]);
    }
}
