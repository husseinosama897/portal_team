<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\easy_restriction;
use App\ledger;
use App\accountbank;
class easy_restrictionController extends Controller
{
 
    public function pagesay(){
     
        return view('easy_restriction.home');

        
    }

    public function select(){
     
        return view('easy_restriction.select');

        
    }


    public function moneymovements(){

      
            return view('easy_restriction.movements');
    
            
    }

    
    public function moneymovementsedit($easy_restriction){
      
 $data = easy_restriction::where('id',$easy_restriction)->with([

    'creditor_sub_account3',
    'debtor_sub_account3',
    'creditor_sub_account4',
    'debtor_sub_account4',

 ])->first();


 if($data->type == 1){
    return view('easy_restriction.movementsedit')->with(['data'=>$data]);
 }
            
    
            elseif($data->type == 2){
                
                return view('easy_restriction.fixedassetedit')->with(['data'=>$data]);
            }

            elseif($data->type == 3){
                
                return view('easy_restriction.withdrawedit')->with(['data'=>$data]);
            }

            elseif($data->type == 4){
                
                return view('easy_restriction.capitaledit')->with(['data'=>$data]);
            }
            elseif($data->type == 6){
                
                return view('easy_restriction.payrollaccountingedit')->with(['data'=>$data]);
            }

            elseif($data->type == 5){
                
                return view('easy_restriction.profitsedit')->with(['data'=>$data]);
            }



            
            
            
    }



    public function moneycapital(){

      
            return view('easy_restriction.capital');
    
            
    }




    

    public function withdraw(){
 
            return view('easy_restriction.withdraw');
    
            
    }


    public function Fixedasset(){


       
            return view('easy_restriction.fixedasset');
    
            
    }



    public function profits(){


      
            return view('easy_restriction.profits');
    
            
    }




public function moneymovementsaccount(){

        $da =  accountbank::where('code',1)->with(['subaccount1'=>
        function($d){
            $d->where('code',11)->with(['sub_account3'=> function($r) {
         return   $r->whereNotIn('code',[1103])->with('sub_account4');
           }]);
        }])->first();
           
        return response()->json(['data'=>$da]);
    
}


public function moneycapitalsaccount(){
   
        $da =  accountbank::where('code',3)->with(['subaccount1'=>
        function($d){
            $d->with(['sub_account3'=> function($r) {
         return   $r->whereNotIn('code',[3402])->with('sub_account4');
         
           }]);
        }])->first();
           
        return response()->json(['data'=>$da]);
    
}



public function distributeprofits(){
  
        $da =  accountbank::where('code',3)->with(['subaccount1'=>
        function($d){
            $d->where('code',34)->with(['sub_account3'=> function($r) {
         return   $r->where('code',3402)->with('sub_account4');
           }]);
        }])->first();
           
        return response()->json(['data'=>$da]);
    
}



public function pageasy(){

      
        return view('easy_restriction.home');

    
}


public function homejson(request $request){

  $e =     easy_restriction::with(['creditor_sub_account3','debtor_sub_account3','creditor_sub_account4',

  'debtor_sub_account4'
  ]);



if($request->from){
$e = $e->where('date','>=',$request->from)
;
}


if($request->to){
    $e = $e->where('date','<=',$request->to);
}
        
    
$e = $e->paginate(10);






    return response()->json(['data'=>$e]);
}





public function payrollaccountinga(){
 
        $da =  accountbank::where('code',5)->with(['subaccount1'=>
        function($d){
            $d->wherein('code',[52,53,51])->with(['sub_account3'=> function($r) {
         return   $r->with('sub_account4');
           }]);
        }])->first();
           
        return response()->json(['data'=>$da]);
    
}



    public function moneycapital2saccount(){
     
            $da =  accountbank::where('code',1)->with(['subaccount1'=>
            function($d){
                $d->wherein('code',[11,12])->with(['sub_account3'=> function($r) {
             return   $r->where('code','!=',1203)
->with('sub_account4');
               }]);
            }])->first();
               
            return response()->json(['data'=>$da]);
        

}


public function fixedsaccount(){
 
        $da =  accountbank::where('code',1)->with(['subaccount1'=>
        function($d){
            $d->where('code',[12,51,52])->with(['sub_account3'=> function($r) {
         return   $r->with('sub_account4');
           }]);
        }])->first();
           
        return response()->json(['data'=>$da]);
    

}

public function spiceasy( $easy_restriction){
 
$data = easy_restriction::where('id',$easy_restriction)->with([
'creditor_sub_account3',

'creditor_sub_account4',
'debtor_sub_account3',
'debtor_sub_account4',

])->first();
        return view('easy_restriction.spic')->with(['data'=>$data]);
  



    }

public function payrollaccounting(){

return view('easy_restriction.payrollaccounting');
    
}
public function fixed0saccount(){
 
        $da =  accountbank::where('code',5)->with(['subaccount1'=>
        function($d){
            $d->where('code',52)->with(['sub_account3'=> function($r) {
         return   

         $r->where('code',52015)->with('sub_account4');
           }]);
        }])->first();
           
        return response()->json(['data'=>$da]);
    

}

public function productfixed0saccount(){
 
        $da =  accountbank::whereIn('code',[1,5])->with(['subaccount1'=>
     function($d){
         $d->whereNotIn('code',[11,53])->with(['sub_account3'=> function($r) {
      return   

      $r->whereNotIn('code',[5201,5205,52015,52016,52017,52018])->with('sub_account4');
        }]);
     }]);
  
     $da = $da->get();
        
   
        return response()->json(['data'=>$da]);
    }



public function producteleingsaccount(){

        $da =  accountbank::where('code',4)->with(['subaccount1'=>
     function($d){
         $d->whereIn('code',[42,41])->with(['sub_account3'=> function($r) {
      return   

      $r->with('sub_account4');
        }]);
     }]);
  
     $da = $da->get();
        
   
        return response()->json(['data'=>$da]);
    
}


public function easymovement(request $request){
 
        $this->validate($request,[
          
            'date'=>['required','date'],
            'value'=>['required','numeric'],
    
           'debtor_id'=>['numeric'],
     'creditor_id'=>['numeric'],
 
        ]);
    $code =   easy_restriction::count();
  $easy = new  easy_restriction;

  if($request->creditor_level == 3){
      $easy->creditor_sub_account3_id = $request->creditor_id;
  }
  if($request->creditor_level == 4){
    $easy->creditor_sub_account4_id = $request->creditor_id;
}
$easy->code = $code;
$easy->date = $request->date;
$easy->value = $request->value;
$easy->dis = $request->dis;  
$easy->type = $request->type;
  if($request->debtor_level == 3){
    $easy->debtor_account3_id = $request->debtor_id;
}
if($request->debtor_level == 4){
  $easy->debtor_sub_account4_id = $request->debtor_id;
}

$easy->save();

if($request->creditor_level &&   $request->creditor_level == 3){
    $easy->creditor_sub_account3()->decrement('initial_balance',$request->value);
$easy->creditor_sub_account3->subaccount1()->decrement('initial_balance',$request->value);
$easy->creditor_sub_account3->subaccount1->accountbank()->decrement('initial_balance',$request->value);












}

if( $request->creditor_level &&  $request->creditor_level == 4){
    $easy->creditor_sub_account4()->decrement('initial_balance',$request->value);
    $easy->creditor_sub_account4->sub_account3()->decrement('initial_balance',$request->value);
    $easy->creditor_sub_account4->sub_account3->subaccount1()->decrement('initial_balance',$request->value);
$easy->creditor_sub_account4->sub_account3->subaccount1->accountbank->decrement('initial_balance',$request->value);












    }

    if($request->debtor_level && $request->debtor_level == 3){

        $easy->debtor_sub_account3()->increment('initial_balance',$request->value);
        $easy->debtor_sub_account3->subaccount1()->increment('initial_balance',$request->value);
        $easy->debtor_sub_account3->subaccount1->accountbank()->increment('initial_balance',$request->value);

      
        
 
        

          }
        
        if( $request->debtor_level && $request->debtor_level == 4){
            $easy->debtor_sub_account4()->increment('initial_balance',$request->value);
            $easy->debtor_sub_account4->sub_account3()->increment('initial_balance',$request->value);
            $easy->debtor_sub_account4->sub_account3->subaccount1()->increment('initial_balance',$request->value);
            $easy->debtor_sub_account4->sub_account3->subaccount1->accountbank->increment('initial_balance',$request->value);
           
        
        
        

     
      
            
            
         
      



        }
    
}


public function editeasymovement(request $request,$easy_restriction){

        $data = easy_restriction::where('id',$easy_restriction)->with([

            'creditor_sub_account3',
            'debtor_sub_account3',
            'creditor_sub_account4',
            'debtor_sub_account4',
        
         ])->first();

 
        $this->validate($request,[
          
            'date'=>['required','date'],
            'value'=>['required','numeric'],
    
           'debtor_id'=>['numeric'],
     'creditor_id'=>['numeric'],
 
        ]);

        if(!empty($data->creditor_sub_account3)){
            $data->creditor_sub_account3()->increment('initial_balance',$data->value);
            if( $data->creditor_sub_account3->subaccount1 )
{
    $data->creditor_sub_account3->subaccount1->increment('initial_balance',$data->value);
}      
if($data->creditor_sub_account3->subaccount1->accountbank){
    $data->creditor_sub_account3->subaccount1->accountbank()->increment('initial_balance',$data->value);
        
}
    
        
        if( $data->creditor_sub_account3->subaccount1 && $data->creditor_sub_account3->subaccount1->ledger()->count() > 0 ){
           
            $easy->creditor_sub_account3->subaccount1->ledger()->decrement('creditor_value',$data->value);
        }
        
        
     
        
        }
        
        if(  !empty($data->creditor_sub_account4 )){
            $data->creditor_sub_account4()->increment('initial_balance',$data->value);
            $data->creditor_sub_account4->sub_account3->subaccount1()->increment('initial_balance',$data->value);
        $data->creditor_sub_account4->sub_account3->subaccount1->accountbank->increment('initial_balance',$data->value);
    

     
        
        
      
            }
        
            if(!empty($data->debtor_sub_account3)){
                $data->debtor_sub_account3()->decrement('initial_balance',$data->value);
                $data->debtor_sub_account3->subaccount1()->decrement('initial_balance',$data->value);
                $data->debtor_sub_account3->subaccount1->accountbank()->decrement('initial_balance',$data->value);





                  }
                
                if( !empty($data->debtor_sub_account4)){
                    $data->debtor_sub_account4()->decrement('initial_balance',$data->value);
                    $data->debtor_sub_account4->sub_account3->subaccount1()->decrement('initial_balance',$data->value);
                    $data->debtor_sub_account4->sub_account3->subaccount1->accountbank->decrement('initial_balance',$data->value);
                    
    
                }

                    $data ->creditor_sub_account3_id = null;
                    $data->creditor_sub_account4_id = null;

                    $data->debtor_account3_id = null;

                    $data ->debtor_sub_account4_id = null;

  if($request->creditor_level == 3){
 $data ->creditor_sub_account3_id = $request->creditor_id;
  }
  if($request->creditor_level == 4){
    $data->creditor_sub_account4_id = $request->creditor_id;
}
$data->date = $request->date;
$data->value = $request->value;
$data->dis = $request->dis;  

  if($request->debtor_level == 3){
    $data->debtor_account3_id = $request->debtor_id;
}
if($request->debtor_level == 4){
    $data ->debtor_sub_account4_id = $request->debtor_id;
}

$data->save();

if($request->creditor_level &&   $request->creditor_level == 3){
    $data->creditor_sub_account3()->decrement('initial_balance',$request->value);
    $data->creditor_sub_account3->subaccount1->decrement('initial_balance',$request->value);
    $data->creditor_sub_account3->subaccount1->accountbank()->decrement('initial_balance',$request->value);





}

if( $request->creditor_level &&  $request->creditor_level == 4){
    $data->creditor_sub_account4()->decrement('initial_balance',$request->value);
    $data->creditor_sub_account4->sub_account3->subaccount1()->decrement('initial_balance',$request->value);
    $data->creditor_sub_account4->sub_account3->subaccount1->accountbank->decrement('initial_balance',$request->value);
   







}

    if($request->debtor_level && $request->debtor_level == 3){
        $data->debtor_sub_account3()->increment('initial_balance',$request->value);
        $data->debtor_sub_account3->subaccount1()->increment('initial_balance',$request->value);
        $data->debtor_sub_account3->subaccount1->accountbank()->increment('initial_balance',$request->value);
        
    
    
    


    }
        
        if( $request->debtor_level && $request->debtor_level == 4){
            $data->debtor_sub_account4()->increment('initial_balance',$request->value);
            $data->debtor_sub_account4->sub_account3->subaccount1()->increment('initial_balance',$request->value);
            $data->debtor_sub_account4->sub_account3->subaccount1->accountbank->increment('initial_balance',$request->value);
           
        
        
        
            



        }
    
}

public function delete($easy_restriction){
 
        $data = easy_restriction::where('id',$easy_restriction)->first();

        if(!empty($data->creditor_sub_account3)){
            $data->creditor_sub_account3()->increment('initial_balance',$data->value);
        $data->creditor_sub_account3->subaccount1->increment('initial_balance',$data->value);
        $data->creditor_sub_account3->subaccount1->accountbank()->increment('initial_balance',$data->value);
        
        }
        
        if(  !empty($data->creditor_sub_account4 )){
            $data->creditor_sub_account4()->increment('initial_balance',$data->value);
            $data->creditor_sub_account4->sub_account3->subaccount1()->increment('initial_balance',$data->value);
        $data->creditor_sub_account4->sub_account3->subaccount1->accountbank->increment('initial_balance',$data->value);
            }
        
            if(!empty($data->debtor_sub_account3)){
                $data->debtor_sub_account3()->decrement('initial_balance',$data->value);
                $data->debtor_sub_account3->subaccount1()->decrement('initial_balance',$data->value);
                $data->debtor_sub_account3->subaccount1->accountbank()->decrement('initial_balance',$data->value);
                  }
                
                if( !empty($data->debtor_sub_account4)){
                    $data->debtor_sub_account4()->decrement('initial_balance',$data->value);
                    $data->debtor_sub_account4->sub_account3->subaccount1()->decrement('initial_balance',$data->value);
                    $data->debtor_sub_account4->sub_account3->subaccount1->accountbank->decrement('initial_balance',$data->value);
                    }

                    $data->delete();

    
}
}
