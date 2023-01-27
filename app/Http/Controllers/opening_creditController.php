<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\entry_manual;
use App\accountbank;
use App\entry_manual_account;
class opening_creditController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    
   public function index(){

        return view('entry_manual.index');

    
   }

   public function select(){

        return view('entry_manual.select');

    
   }

//customer

   public function createpage(){
   
        return view('entry_manual.create');
    
   }


//productpage


public function createproduct(){
  
        return view('entry_manual.product');
    
   }

   public function createindex(){
  
        return view('entry_manual.index');
    
   }

   public function homejson(request $request){
    
  $e =     entry_manual::query();

  $e = $e->where('hide','!==',1);


if($request->from){
$e = $e->where('date','>=',$request->from)
;
}


if($request->to){
    $e = $e->where('date','>=',$request->to);
}

if($request->code){
    $e = $e->where('code',$request->code);
}


if($request->to){
    $e = $e->where('dis', 'LIKE', '%' . $request->dis . '%');
}
        
    
$e = $e->orderBy('created_at', 'DESC')->paginate(10);






    return response()->json(['data'=>$e]);
}



   public function createaccounting(){
  
        return view('entry_manual.accounting');
    
   }


   public function createsuppiler(){
   
        return view('entry_manual.suppiler');
    
   }





   public function insarting(request $request){
  
        $this->validate($request,[
          
            'date'=>['required','date'],
            'dis'=>['required','string','max:255'],
    
        ]);

        if($request->type !== 5){
            $this->validate($request,[
                'creditor_id'=>['required','numeric'],
            ]);
      
        }
$code = entry_manual::count() + 1;
        $entry_manual = entry_manual::create([
   
     'date'=>$request->date,
           'creditor_id'=>$request->creditor_id ?? null, 
            'value'=>$request->value,
        'dis'=>$request->dis,
        'line'=>$request->line,
        'hide'=>0,
'type'=>$request->type,
      'code'=>$code
            
        ]);
if( $entry_manual->sub_account3()->count() > 0){
    $entry_manual->sub_account3()->increment('initial_balance',$request->value);
    
    $entry_manual->sub_account3->subaccount1()->increment('initial_balance',$request->value);
    $entry_manual->sub_account3->subaccount1->accountbank->increment('initial_balance',$request->value);
}
      

        $accounts = json_decode($request->account, true);
        if($request->type == 1 || $request->type == 4){
        $da =  accountbank::where('code',1)->with(['subaccount1'=>
     function($d){
         $d->where('code',11)->with(['sub_account3'=> function($r) {
      return   

      $r->where('code',1103);
        }]);
     }]);
     $da = $da->first();
     $d = $da['subaccount1'][0]['sub_account3'][0]['id'];
  
    
    }elseif($request->type == 2){
        $da = accountbank::where('code',1)->with(['subaccount1'=>
        function($d){
            $d->where('code',11)->with(['sub_account3'=> function($r) {
         return   
   
         $r->where('code',1106);
           }]);
        }]);
        $da = $da->first();
        $d = $da['subaccount1'][0]['sub_account3'][0]['id'];
   
    
    }
  
  
    

        $inserting =  [];
foreach($accounts as $acc){
    if($request->type  == 2  || $request->type  == 1  || $request->type  == 4  ){
      $e =  entry_manual_account::create( [
            'debtor_account3_id'=>$d,
       'entry_manual_id'=>$entry_manual->id   ,
       'value'=>$acc['value'],
       'supplier_id'=>$acc['supplier_id'] ?? null,
        'customer_id'=>$acc['customer_id'] ?? null,
        'product_id'=>$acc['product_id'] ?? null,
        'project_id'=>$acc['project_id']?? null,
        'date'=>$request->date,
         ]);

    $e->sub_account3()->increment('initial_balance',$acc['value']);
    }

    elseif($request->type == 5){
        if(  !empty($acc['account']) && 3 == $acc['account']['level'] ){
            if(!empty($acc['account'])){
              $e =  entry_manual_account::create( [
                  'creditor_sub_account3_id'=>$acc['account']['id'],
                   'creditor_sub_account4_id'=>null,
             'entry_manual_id'=>$entry_manual->id,
             'value'=>$acc['value'],
             'date'=>$request->date,
          
              ]);
       
           

              $e->creditor_sub_account3()->increment('initial_balance',$acc['value']);
              $e->creditor_sub_account3->subaccount1()->increment('initial_balance',$acc['value']);
$e->creditor_sub_account3->subaccount1->accountbank()->increment('initial_balance',$acc['value']);

            }
         
          }elseif( !empty($acc['account']) && 4 == $acc['account']['level']){
              if(!empty($acc['account'])){
      
              
      
                  $e =  entry_manual_account::create([
                  'creditor_sub_account3_id'=>null,
                   'creditor_sub_account4_id'=>$acc['account']['id'],
             'entry_manual_id'=>$entry_manual->id,
          'value'=>$acc['value'],
          'date'=>$request->date,
               ]);
               $e->creditor_sub_account4()->increment('initial_balance',$acc['value']);
               $e->creditor_sub_account4->sub_account3()->decrement('initial_balance',$acc['value']);
               $e->creditor_sub_account4->sub_account3->subaccount1()->decrement('initial_balance',$acc['value']);
           $e->creditor_sub_account4->sub_account3->subaccount1->accountbank->decrement('initial_balance',$acc['value']);
              }
      
          }

    }



    elseif(3 == $acc['account']['level']){
      if(!empty($acc['account'])){
        $e =  entry_manual_account::create( [
            'debtor_account3_id'=>$acc['account']['id'],
             'debtor_sub_account4_id'=>null,
       'entry_manual_id'=>$entry_manual->id,
       'value'=>$acc['value'],
       'date'=>$request->date,
        ]);
 
        $e->sub_account3()->increment('initial_balance',$acc['value']);
       
        $e->sub_account3->subaccount1()->increment('initial_balance',$acc['value']);
        $e->sub_account3->subaccount1->accountbank->increment('initial_balance',$acc['value']);
      }
   
    }elseif(4 == $acc['account']['level']){
        if(!empty($acc['account'])){

        

            $e =  entry_manual_account::create([
            'debtor_account3_id'=>null,
             'debtor_sub_account4_id'=>$acc['account']['id'],
       'entry_manual_id'=>$entry_manual->id,
    'value'=>$acc['value'],
    'date'=>$request->date,
         ]);
         $e->sub_account4()->increment('initial_balance',$acc['value']);
  
         $e->sub_account4->sub_account3()->increment('initial_balance',$acc['value']);
         $e->sub_account4->sub_account3->subaccount1()->increment('initial_balance',$acc['value']);
         $e->sub_account4->sub_account3->subaccount1->accountbank->increment('initial_balance',$acc['value']);
        }

    }

    



}
     

if($request->type == 5){
    foreach($accounts as $acc){
      
    
        if($request->type == 5){
            if(3 == $acc['account']['level']){
                if(!empty($acc['account'])){
                  $e =  entry_manual_account::create( [
                      'debtor_account3_id'=>$acc['account']['id'],
                       'debtor_sub_account4_id'=>null,
                 'entry_manual_id'=>$entry_manual->id,
                 'value'=>$acc['value'],
                 'date'=>$request->date,
                  ]);
                  $e->sub_account3()->increment('initial_balance',$acc['value']);
                  $e->sub_account3->subaccount1()->increment('initial_balance',$acc['value']);
                  $e->sub_account3->subaccount1->accountbank->increment('initial_balance',$acc['value']);

                }
             
              }elseif(4 == $acc['account']['level']){
                  if(!empty($acc['account'])){
          
                  
          
                      $e =  entry_manual_account::create([
                      'debtor_account3_id'=>null,
                       'debtor_sub_account4_id'=>$acc['account']['id'],
                 'entry_manual_id'=>$entry_manual->id,
              'value'=>$acc['value'],
              'date'=>$request->date,
                   ]);
                   $e->sub_account4()->increment('initial_balance',$acc['value']);
  
                   $e->sub_account4->sub_account3()->increment('initial_balance',$acc['value']);
                   $e->sub_account4->sub_account3->subaccount1()->increment('initial_balance',$acc['value']);
                   $e->sub_account4->sub_account3->subaccount1->accountbank->increment('initial_balance',$acc['value']);

                  }
          
              }
    
        }
    
    }
}



        


    
   }

   public function allaccount(){
    $da =  accountbank::with(['subaccount1'=>
    function($d){
        $d->with(['sub_account3'=> function($r) {
     return   

     $r->whereNotIn('code',[1103,1104])->with('sub_account4');
       }]);
    }]);

    $da = $da->get();
    return response()->json(['data'=>$da]);
   }



   public function editpage($entry_manual){
  
  
      $a =       entry_manual::where('id',$entry_manual)->with(['entry_manual_account'=> function($q){

       return $q->with(['sub_account3'=> function(){


        }])->with('sub_account4')->with('creditor_sub_account3')->with('creditor_sub_account4');
      }])->with('sub_account3')->first();

if($a->type == 1){
    return view('entry_manual.customeredit')->with('data',$a);
}

if($a->type == 4){
    return view('entry_manual.supplieredit')->with('data',$a);
}

if($a->type == 2){
    return view('entry_manual.productedit')->with('data',$a);
}
if($a->type == 3){
    return view('entry_manual.accountingedit')->with('data',$a);
}


if($a->type == 5){
    return view('entry_manual.editmanual')->with('data',$a);
}




  
   

}


public function printentry($entry_manual){
    
  $a =   entry_manual::where('id',$entry_manual)->with(['entry_manual_account'=> function($q){

   return $q->with(['sub_account3'=> function(){


    }])->with('sub_account4')->with('creditor_sub_account3')->with('creditor_sub_account4');



  }])->with('sub_account3')->first();


return view('entry_manual.entry_print')->with('data',$a);



    

}



   public function update(request $request,$entry_manual){
 
        $this->validate($request,[
          
            'date'=>['required','date'],
            'dis'=>['required','string','max:255'],
    
     
 
        ]);


        
        if($request->type !== 5){
            $this->validate($request,[
                'creditor_id'=>['required','numeric'],
            ]);
      
        }


   $update = entry_manual::where('id',$entry_manual)->first();
   if($update->sub_account3()->count() > 0){
    $update->sub_account3()->decrement('initial_balance',$update->value);
    $update->sub_account3->subaccount1()->decrement('initial_balance',$update->value);
    $update->sub_account3->subaccount1->accountbank->decrement('initial_balance',$update->value);


   }

   
        $update->update([
    
            
     'date'=>$request->date,
           'creditor_id'=>$request->creditor_id, 
            'value'=>$request->value,
        'dis'=>$request->dis,
      

        ]);

        foreach($update->entry_manual_account as $entry_manual_account){
         


            $dc = $entry_manual_account->value ?? $entry_manual_account->value;
if($entry_manual_account->sub_account3()->count()){
    $entry_manual_account->sub_account3()->decrement('initial_balance',$dc);

    $entry_manual_account->sub_account3->subaccount1()->decrement('initial_balance',$dc);
    $entry_manual_account->sub_account3->subaccount1->accountbank->decrement('initial_balance',$dc);

   

}

if($entry_manual_account->sub_account4()->count()){

    $entry_manual_account->sub_account4()->decrement('initial_balance',$dc);
  
    $entry_manual_account->sub_account4->sub_account3()->decrement('initial_balance',$dc);
    $entry_manual_account->sub_account4->sub_account3->subaccount1()->decrement('initial_balance',$dc);
    $entry_manual_account->sub_account4->sub_account3->subaccount1->accountbank->decrement('initial_balance',$dc);

}

if($entry_manual_account->creditor_sub_account4()->count()){

    $entry_manual_account->creditor_sub_account4()->decrement('initial_balance',$dc);
    $entry_manual_account->creditor_sub_account4->sub_account3()->decrement('initial_balance',$dc);
    $entry_manual_account->creditor_sub_account4->sub_account3->subaccount1()->decrement('initial_balance',$dc);
$entry_manual_account->creditor_sub_account4->sub_account3->subaccount1->accountbank->decrement('initial_balance',$dc);

}


if($entry_manual_account->creditor_sub_account3()->count()){

    
    $entry_manual_account->creditor_sub_account3()->decrement('initial_balance',$dc);
    $entry_manual_account->creditor_sub_account3->subaccount1()->decrement('initial_balance',$dc);
$entry_manual_account->creditor_sub_account3->subaccount1->accountbank()->decrement('initial_balance',$dc);

}

$entry_manual_account->delete();
          
        }


       



        if($update->sub_account3()->count() > 0){

        $update->sub_account3()->increment('initial_balance',$request->value);
        $update->sub_account3->subaccount1()->increment('initial_balance',$request->value);
        $update->sub_account3->subaccount1->accountbank->increment('initial_balance',$request->value);
    
        }
        $accounts = json_decode($request->account, true);
        if($request->type == 1 || $request->type == 4){
        $da = accountbank::where('code',1)->with(['subaccount1'=>
     function($d){
         $d->where('code',11)->with(['sub_account3'=> function($r) {
      return   

      $r->where('code',1103);
        }]);
     }]);
     $da = $da->first();
     $d = $da['subaccount1'][0]['sub_account3'][0]['id'];
  
    
    }elseif($request->type == 2){
        $da = accountbank::where('code',1)->with(['subaccount1'=>
        function($d){
            $d->where('code',11)->with(['sub_account3'=> function($r) {
         return   
   
         $r->where('code',1106);
           }]);
        }]);
        $da = $da->first();
        $d = $da['subaccount1'][0]['sub_account3'][0]['id'];
   
    
    }
  
  
    

        $inserting =  [];
        foreach($accounts as $acc){
            if($request->type  == 2  || $request->type  == 1  || $request->type  == 4  ){
              $e =  entry_manual_account::create( [
                    'debtor_account3_id'=>$d,
               'entry_manual_id'=>$update->id   ,
               'value'=>$acc['value'],
               'supplier_id'=>$acc['supplier_id'] ?? null,
                'customer_id'=>$acc['customer_id'] ?? null,
                'product_id'=>$acc['product_id'] ?? null,
                'project_id'=>$acc['project_id']?? null,
                 ]);
        
            $e->sub_account3()->increment('initial_balance',$acc['value']);
            $e->sub_account3->subaccount1()->increment('initial_balance',$acc['value']);
            $e->sub_account3->subaccount1->accountbank->increment('initial_balance',$acc['value']);

            }
        
            elseif($request->type == 5){
                if(  !empty($acc['sub_account3']) && 3 == $acc['sub_account3']['level'] ){
                    if(!empty($acc['sub_account3'])){
                      $e =  entry_manual_account::create( [
                          'creditor_sub_account3_id'=>$acc['sub_account3']['id'],
                           'creditor_sub_account4_id'=>null,
                     'entry_manual_id'=>$update->id,
                     'value'=>$acc['value'],
                  
                      ]);
               
                   
        
                      $e->creditor_sub_account3()->increment('initial_balance',$acc['value']);
                      $e->creditor_sub_account3->subaccount1()->increment('initial_balance',$acc['value']);
        $e->creditor_sub_account3->subaccount1->accountbank()->increment('initial_balance',$acc['value']);
        
                    }
                 
                  }elseif( !empty($acc['sub_account4']) && 4 == $acc['sub_account4']['level']){
                      if(!empty($acc['sub_account4'])){
              
                      
              
                          $e =  entry_manual_account::create([
                          'creditor_sub_account3_id'=>null,
                           'creditor_sub_account4_id'=>$acc['sub_account4']['id'],
                     'entry_manual_id'=>$update->id,
                  'value'=>$acc['value'],
                       ]);
                       $e->creditor_sub_account4()->increment('initial_balance',$acc['value']);
                       $e->creditor_sub_account4->sub_account3()->decrement('initial_balance',$acc['value']);
                       $e->creditor_sub_account4->sub_account3->subaccount1()->decrement('initial_balance',$acc['value']);
                   $e->creditor_sub_account4->sub_account3->subaccount1->accountbank->decrement('initial_balance',$acc['value']);
                      }
              
                  }
        
            }
        
        
        
            elseif(!empty($acc['sub_account3']) && 3 == $acc['sub_account3']['level']){
              if(!empty($acc['sub_account3'])){
                $e =  entry_manual_account::create( [
                    'debtor_account3_id'=>$acc['sub_account3']['id'],
                     'debtor_sub_account4_id'=>null,
               'entry_manual_id'=>$update->id,
               'value'=>$acc['value'],
            
                ]);
         
                $e->sub_account3()->increment('initial_balance',$acc['value']);
  
                $e->sub_account3->subaccount1()->increment('initial_balance',$acc['value']);
                $e->sub_account3->subaccount1->accountbank->increment('initial_balance',$acc['value']);
              }
           
            }elseif(!empty($acc['sub_account4']) && 4 == $acc['sub_account4']['level']){
                if(!empty($acc['sub_account4'])){
        
                
        
                    $e =  entry_manual_account::create([
                    'debtor_account3_id'=>null,
                     'debtor_sub_account4_id'=>$acc['sub_account4']['id'],
               'entry_manual_id'=>$update->id,
            'value'=>$acc['value'],
                 ]);
                 $e->sub_account4()->increment('initial_balance',$acc['value']);
              
                 $e->sub_account4->sub_account3()->increment('initial_balance',$acc['value']);
                 $e->sub_account4->sub_account3->subaccount1()->increment('initial_balance',$acc['value']);
                 $e->sub_account4->sub_account3->subaccount1->accountbank->increment('initial_balance',$acc['value']);
                }
        
            }
        
            
        
        
        
        }
             
        
        if($request->type == 5){
            foreach($accounts as $acc){
              
            
                if($request->type == 5){
                    if(!empty($acc['sub_account3']) && 3 == $acc['sub_account3']['level']){
                        if(!empty($acc['sub_account3'])){
                          $e =  entry_manual_account::create( [
                              'debtor_account3_id'=>$acc['sub_account3']['id'],
                               'debtor_sub_account4_id'=>null,
                         'entry_manual_id'=>$update->id,
                         'value'=>$acc['value'],
                      
                          ]);
                          $e->sub_account3()->increment('initial_balance',$acc['value']);
  
                          $e->sub_account3->subaccount1()->increment('initial_balance',$acc['value']);
                          $e->sub_account3->subaccount1->accountbank->increment('initial_balance',$acc['value']);
                        }
                     
                      }elseif(4 == $acc['sub_account4']['level']){
                          if(!empty($acc['sub_account4'])){
                  
                          
                  
                              $e =  entry_manual_account::create([
                              'debtor_account3_id'=>null,
                               'debtor_sub_account4_id'=>$acc['sub_account4']['id'],
                         'entry_manual_id'=>$update->id,
                      'value'=>$acc['value'],
                           ]);
                           $e->sub_account4()->increment('initial_balance',$acc['value']);
              
                           $e->sub_account4->sub_account3()->increment('initial_balance',$acc['value']);
                           $e->sub_account4->sub_account3->subaccount1()->increment('initial_balance',$acc['value']);
                           $e->sub_account4->sub_account3->subaccount1->accountbank->increment('initial_balance',$acc['value']);
                          }
                  
                      }
            
                }
            
            }
        }
        
        

    
   }

   public function delete($entry_manual){
   
    $update =   entry_manual::where('id',$entry_manual)->first();
    foreach($update->entry_manual_account as $entry_manual_account){
        $d =  $entry_manual_account->value ?? 0;
        if(!empty($entry_manual_account->sub_account3())){
         
            $update->sub_account3()->decrement('initial_balance',$d);
            }
            if(!empty($entry_manual_account->sub_account4())){
                $entry_manual_account->sub_account4()->decrement('initial_balance',$d);
              }

    }
    if(!empty($update->sub_account3)){
    foreach($update->sub_account3() as $deletes){
        $deletes->delete();
    }
}
    if(!empty($update->sub_account4)){
    foreach($update->sub_account4() as $deletes){
        $deletes->delete();
    }
}
    if(!empty($update->creditor_sub_account3)){
        foreach($update->creditor_sub_account3() as $deletes){
            $deletes->delete();
        }
    }
  
    if(!empty($update->creditor_sub_account4)){
        foreach($update->creditor_sub_account4() as $deletes){
            $deletes->delete();
        }
    }
   
 

    $update->delete();

    
    
   }
}
