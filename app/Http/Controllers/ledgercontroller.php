<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\product;
use App\accountbank;
use App\supplier;
use App\entry_manual;
use App\easy_restriction;
use App\Purchase_order;
class ledgercontroller extends Controller
{


  public function homepage(){
    return view('managers.reports.homepage');
  }


  public function purchase_orderjson(request $request){
    $data = product::wherehas('purchase_order',function($q) use($request) {
if($request->from && empty($request->Period)){
  return $q->where('date', '=', $request->from);
}else{
 

  return $q->wherein('date', $request->Period);
}


     
    })->withcount(['purchase_order'=>function($q) use($request) {
      if($request->from && empty($request->Period)){
        return $q->where('date', '=', $request->from);
      }else{
        return $q->wherein('date', $request->Period)->orderBy('created_at', 'DESC');
      }
     }
     ] )->with(['purchase_order'=>function($q) use($request) {
      if($request->from && empty($request->Period)){
        return $q->where('date', '=', $request->from);
      }else{
        
        return $q->wherein('date', $request->Period)->orderBy('created_at', 'DESC');
      }
      
           
          }
     ] )->get();
  
    
    return response()->json(['data'=>$data]);
  }


  
/*
  public function productchartsjson(request $request){
    $data = auth()->user()->orderpackage()->with(['product'=>function($d) use($request){
   
    
 return  $d ->wherehas('offerpaid',function($q) use($request) {
if($request->from && empty($request->Period)){
  return $q->where('date', '=', $request->from);
}else{

  return $q->wherein('date', $request->Period);
}


     
    }
    

      

    )->withcount(['offerpaid'=>function($q) use($request) {
      if($request->from && empty($request->Period)){
        return $q->where('date', '=', $request->from);
      }else{
        
        return $q->wherein('date', $request->Period)->orderBy('created_at', 'DESC');
      }
      
           
          }
     ] )->with(['offerpaid'=>function($q) use($request) {
      if($request->from && empty($request->Period)){
        return $q->where('date', '=', $request->from);
      }else{
        
        return $q->wherein('date', $request->Period)->orderBy('created_at', 'DESC');
      }
      
           
          }
     ] );
  }
    
]);


    
    $data = $data->first();
    

    return response()->json(['data'=>$data]);
  }
  */

  public function purchase_orderchart(){
    return view('managers.reports.purchase_order');
  }

  
  public function SummaryAccount($account){
   
    return view('managers.reports.SummaryAccount');
  }

  public function productcharts(){
    return view('managers.reports.productcharts');
  }



  public function sales_invoices(){
    return view('managers.reports.sales_invoices');
  }


  public function ar_by_customers(){
    return view('managers.reports.ar_by_customers');
  }

  public function ap_by_vendors(){
    return view('managers.reports.ap_by_vendors');
  }

  public function taxreport(){
    return view('managers.reports.taxreport');
  }

  public function taxreportjson(request $request){
   $data = Purchase_order::where('created_at','>=',$request->from)
   ->where('created_at','<=',$request->to)->get();
   return response()->json(['data'=>$data]);
  }





  public function ap_by_vendorsjson(request $request){
    $data = supplier::wherehas('purchase_order')->orwherehas('entry_manual_account')
    ->with(['entry_manual_account'=> function($q)use($request){
     
      if($request->from){
        $q = $q->where('created_at','>=',$request->from)
        ;
        }
        
        if($request->to){
            $q = $q->where('created_at','<=',$request->to);
        }


return $q;
    }

     ]
     )
     ->with(['purchase_order'=> function($q) use($request){
  
        if($request->from){
          $q = $q->where('created_at','>=',$request->from)
          ;
          }
          
          if($request->to){
              $q = $q->where('created_at','<=',$request->to);
          }
  
          $q->wherehas('note')->with('note');
  
  return $q;
      }
      
    ]);
    
    
  $data = $data->get();

  return response()->json(['data'=>$data]);

  }



  public function ar_by_customersjson(request $request){
    $data = customer::wherehas('invoice')->orwherehas('entry_manual_account')->with(['entry_manual_account'=> function($q)use($request){
     
      if($request->from){
        $q = $q->where('created_at','>=',$request->from)
        ;
        }
        
        if($request->to){
            $q = $q->where('created_at','<=',$request->to);
        }


return $q;
    }

     ]
     )
     ->with(['invoice'=> function($q) use($request){
  
        if($request->from){
          $q = $q->where('created_at','>=',$request->from)
          ;
          }
          
          if($request->to){
              $q = $q->where('created_at','<=',$request->to);
          }
  
       
  return $q;
      }
      
    ]);
    
    
  


  $data = $data->get();

  return response()->json(['data'=>$data]);

  }


  public function sales_invoicesjson(request $request){
    $data = invoice::with('customer');
    
    if($request->from){
      $data = $data->where('created_at','>=',$request->from)
      ;
      }
      
      if($request->to){
          $data = $data->where('created_at','<=',$request->to);
      }
    
 $data  =   $data->get()->chunk(10);
    return response()->json(['data'=>$data]);
  }



public function ledgerpage(){

return view('managers.reports.ledger');
}

public function sales_and_purchasesreport(){

  return view('managers.reports.sales_and_purchases');
  }


public function tax(){
  return view('managers.reports.tax');
}
public function Journal_Reports(){
  return view('managers.reports.Journal_Reports');
}






public function sales_and_purchases(request $request){

$data = product::wherehas('invoice')
  
->orWhereHas('purchase_order',function($q)use($request){
    
    })->with([
      'purchase_order'=> function ($q) use($request){
    
    if($request->from){
      $q = $q->where('date','>=',$request->from)
      ;
      }
      
      if($request->to){
          $q = $q->where('date','<=',$request->to);
      }
      return $q;
      }
    
      
    ])->with([
      'invoice'=> function ($q) use($request){
    
    if($request->from){
      $q = $q->where('date','>=',$request->from)
      ;
      }
      
      if($request->to){
          $q = $q->where('date','<=',$request->to);
      }
      return $q;
      }
    
      
    ]);

  



$data = $data->get();


return response()->json(['data'=>$data]);

}

public function purchase_order(request $request){
  $data = Purchase_order::query();

  if($request->from){
    $data = $data->where('date','>=',$request->from)
    ;
    }
    
    if($request->to){
        $data = $data->where('date','<=',$request->to);
    }
    
$data = $data->where('status',1);
    $data = $data->where('on_vat',1);
    

 $data = $data->with('supplier')->get();

  return response()->json(['data'=>$data]);
}


public function trial_balances(){

  return view('managers.reports.trial_balances');
}

public function Balance_Sheet(){

  return view('managers.reports.Balance_Sheet');
}


public function entry_manualjson(request $request){

  $e =     entry_manual::query();

if($request->from){
$e = $e->where('date','>=',$request->from)
;
}

if($request->to){
    $e = $e->where('date','<=',$request->to);
}

if($request->code){
    $e = $e->where('code',$request->code);
}


if($request->to){
    $e = $e->where('dis', 'LIKE', '%' . $request->dis . '%');
}
        
    
$e = $e->orderBy('created_at', 'DESC')->get()->chunk(10);


    return response()->json(['data'=>$e]);



}



public function easy_restriction_json(request $request){

  $e =    easy_restriction::with([
    'creditor_sub_account3',

  
'debtor_sub_account3',


  'creditor_sub_account4',

'debtor_sub_account4'
  ]);



if($request->from){
$e = $e->where('date','>=',$request->from)
;
}

if($request->to){
    $e = $e->where('date','<=',$request->to);
}





        
    
$e = $e->orderBy('created_at', 'DESC')->get()->chunk(10);


    return response()->json(['data'=>$e]);



}

public function easy_entry_json(request $request){




  $e =    entry_manual::with('sub_account3')->with(
    ['entry_manual_account'=>function($q){
$q->with('sub_account3')
->with('creditor_sub_account3')
->with('creditor_sub_account4')
->with('sub_account4');
    }]
  );

  

if($request->from){
$e = $e->where('date','>=',$request->from)
;
}

if($request->to){
    $e = $e->where('date','<=',$request->to);
}

if($request->code){
    $e = $e->where('code',$request->code);
}


if($request->to){
    $e = $e->where('dis', 'LIKE', '%' . $request->dis . '%');
}
        
    
$e = $e->orderBy('created_at', 'DESC')->get()->chunk(10);


    return response()->json(['data'=>$e]);



}




    public function ledger(request $request){
      
    
     $data =  accountbank::whereHas('subaccount1'
      ,function($query)use($request){
        
    // level 1
    
$query->whereHas('sub_account3',function($q)use($request){

  return $q->with(['creditor_sub_account3'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
}

  }])
  ->orwhereHas(
    'debtor_sub_account3', function ( $query2) use($request) {

      if($request->from){
        $query2->where('date','>=',$request->from)
        ;
        }
        
        
        if($request->to){
          $query2->where('date','<=',$request->to);
        }

    }



  )->with(['debtor_sub_account3'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
}

  }])
  
  ->orwhereHas('manule_creditor_sub_account3', function ( $query2) use($request){

    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
}

  })->with(['manule_creditor_sub_account3'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
}

  }])

  
  ->orwhereHas('manule_creditor', function ( $query2)  use($request){


    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}
  })->with(['manule_creditor'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
}

  }])
  
  

  


  ->orwhereHas('manule_debtor_sub_account3', function ( $query2) use($request) {

    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}

  })->with(['manule_debtor_sub_account3'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
}

  }])


  
  
  ->orwherehas('sub_account4', function($queryr) use($request){


 
      
 
  
  
    $queryr
    ->whereHas('creditor_sub_account4', function ( $query2) use($request){
    
        if($request->from){
     $query2->where('date','>=',$request->from)
     ;
     }
     
     
    if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
}

    
    });

    $queryr ->orwhereHas('manule_creditor_sub_account4', function ( $query2) use($request){
    
        if($request->from){
     $query2->where('date','>=',$request->from)
     ;
     }
     
     
    if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
}

    
   });


       $queryr->orwhereHas('manule_debtor_sub_account4', function ( $query2) use($request){
    
        if($request->from){
     $query2->where('date','>=',$request->from)
     ;
     }
     
     
    if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
}

    
   })
 ;
 
  
  
  
  
    $queryr->orwhereHas('debtor_sub_account4', function ( $query2) use($request){
    
        if($request->from){
     $query2->where('date','>=',$request->from)
     ;
     }
     
     
    if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
}
    
    })  ->orwhereHas('manule_creditor_sub_account4', function ( $query2) use($request){
    
    
     if($request->from){
  $query2->where('date','>=',$request->from)
  ;
  }
  
  
 if($request->to){
  $query2 =  $query2->where('date','<=',$request->to);
 }
 
 })
 
 ->orwhereHas('manule_debtor_sub_account4', function ( $query2) use($request){
 
   if($request->from){
  $query2->where('date','>=',$request->from)
  ;
  }
  
  
 if($request->to){
  $query2 =  $query2->where('date','<=',$request->to);
 }
 })->with(['manule_debtor_sub_account4' =>function($query2) use($request){
  if($request->from){
    $query2->where('date','>=',$request->from)
    ;
    }
    
    
   if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
}

}])->with(['manule_creditor_sub_account4' =>function($query2) use($request){
  if($request->from){
    $query2->where('date','>=',$request->from)
    ;
    }
    
    
   if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
}

}]);
 




   
   })->orwhereHas('debtor_sub_account3', function ( $query2) use($request){
   

    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}
   
   
   });
 
 
 

})->with(['sub_account3' =>function($query2) use($request){
 
  

}]);
 

        

})


->with(['subaccount1'=>function($query)use($request){

// level 2

  $query ->whereHas('sub_account3',function($q) use($request){

    return $q->whereHas('creditor_sub_account3', function ( $query2) use($request) {
  
      if($request->from){
        $query2->where('date','>=',$request->from)
        ;
        }
        
        
        if($request->to){
          $query2->where('date','<=',$request->to);
        }
  
    })->with('creditor_sub_account3')
    ->orwhereHas(
      'debtor_sub_account3', function ( $query2) use($request){
  
        if($request->from){
          $query2->where('date','>=',$request->from)
          ;
          }
          
          
          if($request->to){
            $query2->where('date','<=',$request->to);
          }

  
      }
  
  
  
    )->with(['debtor_sub_account3' =>function($query2) use($request){
      if($request->from){
        $query2->where('date','>=',$request->from)
        ;
        }
        
        
       if($request->to){
        $query2 =  $query2->where('date','<=',$request->to);
    }
    
    }])
       
    
    
    
    ->orwhereHas('manule_creditor', function ( $query2) use($request){

    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}


    })->with(['manule_creditor' =>function($query2) use($request){
      if($request->from){
        $query2->where('date','>=',$request->from)
        ;
        }
        
        
       if($request->to){
        $query2 =  $query2->where('date','<=',$request->to);
    }
    
    }])
     
    
    
    
    
    
    ->orwhereHas('manule_creditor_sub_account3', function ( $query2) use($request){

    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}



    })->with(['manule_creditor_sub_account3' =>function($query2) use($request){
      if($request->from){
        $query2->where('date','>=',$request->from)
        ;
        }
        
        
       if($request->to){
        $query2 =  $query2->where('date','<=',$request->to);
    }
    
    }])
     
    
  
  
  
  
    ->orwhereHas('manule_debtor_sub_account3', function ( $query2) use($request){
  
      if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}

  
    })->with(['manule_debtor_sub_account3' =>function($query2) use($request){
      if($request->from){
        $query2->where('date','>=',$request->from)
        ;
        }
        
        
       if($request->to){
        $query2 =  $query2->where('date','<=',$request->to);
    }
    
    }])
  

    
    
    ->orwherehas('sub_account4', function($queryr) use($request){
  
  
      $queryr
     ->whereHas('creditor_sub_account4', function ( $query2) use($request){
     
         if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}

     
     });

     $queryr ->orwhereHas('manule_creditor_sub_account4', function ( $query2) use($request){
     
         if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}

     
    });


        $queryr->orwhereHas('manule_debtor_sub_account4', function ( $query2) use($request){
     
         if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}

     
    })
  ;
  
   
   
   
   
     $queryr->orwhereHas('debtor_sub_account4', function ( $query2) use($request){
     
         if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}
     
     })  ->orwhereHas('manule_creditor_sub_account4', function ( $query2) use($request){
     
     
      if($request->from){
   $query2->where('date','>=',$request->from)
   ;
   }
   
   
  if($request->to){
   $query2 =  $query2->where('date','<=',$request->to);
  }
  
  })
  
  ->orwhereHas('manule_debtor_sub_account4', function ( $query2) use($request){
  
    if($request->from){
   $query2->where('date','>=',$request->from)
   ;
   }
   
   
  if($request->to){
   $query2 =  $query2->where('date','<=',$request->to);
  }
  })->with(['manule_debtor_sub_account4','manule_creditor_sub_account4']);
   
 
     
     
     });
   
   
   
  
  })
->with(['sub_account3' => function($q) use($request){

  
    return $q->

    
    
whereHas('sub_account4',function($queryr) use($request){


  $queryr
  ->whereHas('creditor_sub_account4', function ( $query2) use($request){
  
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}
  
  })
  ->orwhereHas('manule_creditor_sub_account4', function ( $query2) use($request){
     
     
    if($request->from){
 $query2->where('date','>=',$request->from)
 ;
 }
 
 
if($request->to){
 $query2 =  $query2->where('date','<=',$request->to);
}

})

->orwhereHas('manule_debtor_sub_account4', function ( $query2) use($request){

  if($request->from){
 $query2->where('date','>=',$request->from)
 ;
 }
 
 
if($request->to){
 $query2 =  $query2->where('date','<=',$request->to);
}
})
  ->with(['creditor_sub_account4' =>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])->with(['manule_debtor_sub_account4' =>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
  ->with(['manule_creditor_sub_account4' =>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }]);




  $queryr->orwhereHas('debtor_sub_account4', function ( $query2) use($request){
  
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}

  
  });
 


  
}) ->orwhereHas('manule_creditor_sub_account3', function ( $query2) use($request){

    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}


})->with(['manule_creditor_sub_account3' =>function($query2) use($request){
  if($request->from){
    $query2->where('date','>=',$request->from)
    ;
    }
    
    
   if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
}

}])



->orwhereHas('manule_creditor', function ( $query2) use($request){


  if($request->from){
    $query2->where('date','>=',$request->from)
    ;
    }
    
    
    if($request->to){
      $query2->where('date','<=',$request->to);
    }


})->with(['manule_creditor' =>function($query2) use($request){
  if($request->from){
    $query2->where('date','>=',$request->from)
    ;
    }
    
    
   if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
}

}])






->orwhereHas('manule_debtor_sub_account3', function ( $query2) use($request) {


  if($request->from){
    $query2->where('date','>=',$request->from)
    ;
    }
    
    
    if($request->to){
      $query2->where('date','<=',$request->to);
    }


})->with(['manule_debtor_sub_account3' =>function($query2) use($request){
  if($request->from){
    $query2->where('date','>=',$request->from)
    ;
    }
    
    
   if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
}

}])


->orwhereHas('manule_creditor', function ( $query2) use($request){

    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}


})->with(['manule_creditor' =>function($query2) use($request){
  if($request->from){
    $query2->where('date','>=',$request->from)
    ;
    }
    
    
   if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
}

}])






->orwhereHas('debtor_sub_account3', function ( $query2) use($request){
  
      if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}

  
})->orwhereHas('creditor_sub_account3', function ( $query2) use($request){
  
      if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}

  
})->orwhereHas('sub_account4')


//last 4
->with(['sub_account4'=> function($queryr) use($request){


   $queryr
  ->whereHas('creditor_sub_account4', function ( $query2) use($request){
  
      if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}

  
  })->with(['creditor_sub_account4'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
  
  ->orwhereHas('manule_creditor_sub_account4', function ( $query2) use($request){
     
     
         if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}

  })

->orwhereHas('manule_debtor_sub_account4', function ( $query2) use($request){
   
       if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
}

   
  })
->orwhereHas('debtor_sub_account4', function ( $query2) use($request){
  

  if($request->from){
    $query2->where('date','>=',$request->from)
    ;
    }
    
    
    if($request->to){
      $query2->where('date','<=',$request->to);
    }

  
  
  })->with(['debtor_sub_account4' =>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }]) ->with(['manule_creditor_sub_account4' =>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
  ->with(['manule_debtor_sub_account4' =>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }]);

  }])->with(['creditor_sub_account3' =>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
 
  ->with(['debtor_sub_account3' =>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }]);



  }])->whereHas('sub_account3', function($q){


    
  }) ;
  
  


}])
        
        
        ->get();


        return response()->json(['data'=>$data]);
         
    }
    



    public function ledger2(request $request){
      
    
      $data =    auth()->user()->orderpackage()->WhereHas('accountbank',function($Q) use($request){

     

if($request->level == 1){
  $Q->where('id',$request->account);
}

      
     $Q->whereHas('subaccount1'
       ,function($query)use($request){
         
     // level 1
     

     if($request->level == 2){
      return $q->where('id',$request->account);
         }

 $query->whereHas('sub_account3',function($q)use($request){
 
   
   
   if($request->level == 3){
return $q->where('id',$request->account);
   }
   
   return  $q->whereHas('creditor_sub_account3', function ( $query2)   use($request) {
 
 
     if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
       $query2 =  $query2->where('date','<=',$request->to);
 }
 
   })->with(['creditor_sub_account3' =>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
   ->orwhereHas(
     'debtor_sub_account3', function ( $query2) use($request) {
 
       if($request->from){
         $query2->where('date','>=',$request->from)
         ;
         }
         
         
         if($request->to){
           $query2->where('date','<=',$request->to);
         }
 
     }
 
 
 
   )->with(['debtor_sub_account3' =>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
   
   ->orwhereHas('manule_creditor_sub_account3', function ( $query2) use($request){
 
     if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
       $query2 =  $query2->where('date','<=',$request->to);
 }
 
   })->with(['manule_creditor_sub_account3' =>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
 
   
   ->orwhereHas('manule_creditor', function ( $query2)  use($request){
 
 
     if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
   })->with(['manule_creditor' =>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
   
   
 
   
 
 
   ->orwhereHas('manule_debtor_sub_account3', function ( $query2) use($request) {
 
     if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
 
   })->with(['manule_debtor_sub_account3' =>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
 
 
   
   
   ->orwherehas('sub_account4', function($queryr) use($request){
 
 
  
       if($request->level == 4){
         $queryr->where('id',$request->account);
       }
  
   
   
     $queryr
     ->whereHas('creditor_sub_account4', function ( $query2) use($request){
     
         if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
 }
 
     
     });
 
     $queryr ->orwhereHas('manule_creditor_sub_account4', function ( $query2) use($request){
     
         if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
 }
 
     
    });
 
 
        $queryr->orwhereHas('manule_debtor_sub_account4', function ( $query2) use($request){
     
         if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
 }
 
     
    })
  ;
  
   
   
   
   
     $queryr->orwhereHas('debtor_sub_account4', function ( $query2) use($request){
     
         if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
     $query2 =  $query2->where('date','<=',$request->to);
 }
     
     })  ->orwhereHas('manule_creditor_sub_account4', function ( $query2) use($request){
     
     
      if($request->from){
   $query2->where('date','>=',$request->from)
   ;
   }
   
   
  if($request->to){
   $query2 =  $query2->where('date','<=',$request->to);
  }
  
  })
  
  ->orwhereHas('manule_debtor_sub_account4', function ( $query2) use($request){
  
    if($request->from){
   $query2->where('date','>=',$request->from)
   ;
   }
   
   
  if($request->to){
   $query2 =  $query2->where('date','<=',$request->to);
  }
  })->with(['manule_debtor_sub_account4' =>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
  ->with(['manule_creditor_sub_account4' =>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }]);
 
 
    
    })->orwhereHas('debtor_sub_account3', function ( $query2) use($request){
    
 
     if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
    
    
    });
  
  
  
 
 })->with(['sub_account3'=> function($q)use($request){
 
 
 if($request->level == 3){
   $q->where('id',$request->account);
 }
 
 

 


 
 }]);
 
 
 return $query;
 
 
         
 
 });
 
})

->with(['accountbank'=>function($q) use($request){


  if($request->level == 1){
    $q->where('id',$request->account);
  }

$q ->with(['subaccount1'=>function($query)use($request){
 
 // level 2
if($request->level == 2){
  $query->where('id',$request->account);
}

 
   $query ->whereHas('sub_account3',function($q) use($request){
 


    if($request->level == 3){
      $q->where('id',$request->level);
    }
     return $q->whereHas('creditor_sub_account3', function ( $query2) use($request) {
   
       if($request->from){
         $query2->where('date','>=',$request->from)
         ;
         }
         
         
         if($request->to){
           $query2->where('date','<=',$request->to);
         }
   
     })->with(['creditor_sub_account3'=>function($query2) use($request){
      if($request->from){
        $query2->where('date','>=',$request->from)
        ;
        }
        
        
       if($request->to){
        $query2 =  $query2->where('date','<=',$request->to);
    }
    
    }])
   
     ->orwhereHas(
       'debtor_sub_account3', function ( $query2) use($request){
   
         if($request->from){
           $query2->where('date','>=',$request->from)
           ;
           }
           
           
           if($request->to){
             $query2->where('date','<=',$request->to);
           }
 
   
       }
   
   
   
     )->with(['debtor_sub_account3'=>function($query2) use($request){
      if($request->from){
        $query2->where('date','>=',$request->from)
        ;
        }
        
        
       if($request->to){
        $query2 =  $query2->where('date','<=',$request->to);
    }
    
    }])
     ->orwhereHas('manule_creditor', function ( $query2) use($request){
 
     if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
 
 
     })->with(['manule_creditor'=>function($query2) use($request){
      if($request->from){
        $query2->where('date','>=',$request->from)
        ;
        }
        
        
       if($request->to){
        $query2 =  $query2->where('date','<=',$request->to);
    }
    
    }])
   
     
     
     
     
     
     ->orwhereHas('manule_creditor_sub_account3', function ( $query2) use($request){
 
     if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
 
 
 
     })->with(['manule_creditor_sub_account3'=>function($query2) use($request){
      if($request->from){
        $query2->where('date','>=',$request->from)
        ;
        }
        
        
       if($request->to){
        $query2 =  $query2->where('date','<=',$request->to);
    }
    
    }])
   
     
   
   
   
   
     ->orwhereHas('manule_debtor_sub_account3', function ( $query2) use($request){
   
       if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
 
   
     })->with(['manule_debtor_sub_account3'=>function($query2) use($request){
      if($request->from){
        $query2->where('date','>=',$request->from)
        ;
        }
        
        
       if($request->to){
        $query2 =  $query2->where('date','<=',$request->to);
    }
    
    }])
   
   
 
     
     
     ->orwherehas('sub_account4', function($queryr) use($request){
   
   

      if($request->level == 4){
        $queryr->where('id',$account);
      }
       $queryr
      ->whereHas('creditor_sub_account4', function ( $query2) use($request){
      
          if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
 
      
      });
 
      $queryr ->orwhereHas('manule_creditor_sub_account4', function ( $query2) use($request){
      
          if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
 
      
     });
 
 
         $queryr->orwhereHas('manule_debtor_sub_account4', function ( $query2) use($request){
      
          if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
 
      
     })
   ;
   
    
    
    
    
      $queryr->orwhereHas('debtor_sub_account4', function ( $query2) use($request){
      
          if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
      
      })  ->orwhereHas('manule_creditor_sub_account4', function ( $query2) use($request){
      
      
       if($request->from){
    $query2->where('date','>=',$request->from)
    ;
    }
    
    
   if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
   }
   
   })
   
   ->orwhereHas('manule_debtor_sub_account4', function ( $query2) use($request){
   
     if($request->from){
    $query2->where('date','>=',$request->from)
    ;
    }
    
    
   if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
   }
   })->with(['manule_debtor_sub_account4'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])->with(['manule_creditor_sub_account4'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }]);
 

      
      
      });
    
    
    
   
   })
 ->with(['sub_account3' => function($q) use($request){
 
   
if($request->level == 3){
  $q->where('id',$request->account);
}


     return $q->
 
     
     
 whereHas('sub_account4',function($queryr) use($request){
 
 
   $queryr
   ->whereHas('creditor_sub_account4', function ( $query2) use($request){
   
     if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
   
   })
   ->orwhereHas('manule_creditor_sub_account4', function ( $query2) use($request){
      
      
     if($request->from){
  $query2->where('date','>=',$request->from)
  ;
  }
  
  
 if($request->to){
  $query2 =  $query2->where('date','<=',$request->to);
 }
 
 })
 
 ->orwhereHas('manule_debtor_sub_account4', function ( $query2) use($request){
 
   if($request->from){
  $query2->where('date','>=',$request->from)
  ;
  }
  
  
 if($request->to){
  $query2 =  $query2->where('date','<=',$request->to);
 }
 })
   ->with(['creditor_sub_account4'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
  ->with(['manule_debtor_sub_account4'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
   ->with(['manule_creditor_sub_account4'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }]);
 
 
 
 
   $queryr->orwhereHas('debtor_sub_account4', function ( $query2) use($request){
   
     if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
 
   
   });
  
 
 
   
 }) ->orwhereHas('manule_creditor_sub_account3', function ( $query2) use($request){
 
     if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
 
 
 })->with(['manule_creditor_sub_account3'=>function($query2) use($request){
  if($request->from){
    $query2->where('date','>=',$request->from)
    ;
    }
    
    
   if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
}

}])  ->orwhereHas('manule_creditor', function ( $query2) use($request){
 
 
   if($request->from){
     $query2->where('date','>=',$request->from)
     ;
     }
     
     
     if($request->to){
       $query2->where('date','<=',$request->to);
     }
 

 })->with(['manule_creditor'=>function($query2) use($request){
  if($request->from){
    $query2->where('date','>=',$request->from)
    ;
    }
    
    
   if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
}

}])
 
 
 
 
 
 
 ->orwhereHas('manule_debtor_sub_account3', function ( $query2) use($request) {
 
 
   if($request->from){
     $query2->where('date','>=',$request->from)
     ;
     }
     
     
     if($request->to){
       $query2->where('date','<=',$request->to);
     }
 
 
 })->with(['manule_debtor_sub_account3'=>function($query2) use($request){
  if($request->from){
    $query2->where('date','>=',$request->from)
    ;
    }
    
    
   if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
}

}])  ->orwhereHas('manule_creditor', function ( $query2) use($request){
 
     if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
 
 
 })->with(['manule_creditor'=>function($query2) use($request){
  if($request->from){
    $query2->where('date','>=',$request->from)
    ;
    }
    
    
   if($request->to){
    $query2 =  $query2->where('date','<=',$request->to);
}

}])
 
 
 
 
 
 
 ->orwhereHas('debtor_sub_account3', function ( $query2) use($request){
   
       if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
 
   
 })->orwhereHas('creditor_sub_account3', function ( $query2) use($request){
   
       if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
 
   
 })->orwhereHas('sub_account4')
 
 
 //last 4
 ->with(['sub_account4'=> function($queryr) use($request){
 
 if($request->level == 4){
   $queryr->where('id',$request->account);
 }
    $queryr
   ->whereHas('creditor_sub_account4', function ( $query2) use($request){
   
       if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
 
   
   })->with(['creditor_sub_account4'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
   
   ->orwhereHas('manule_creditor_sub_account4', function ( $query2) use($request){
      
      
          if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
 
   })
 
 ->orwhereHas('manule_debtor_sub_account4', function ( $query2) use($request){
    
        if($request->from){
       $query2->where('date','>=',$request->from)
       ;
       }
       
       
      if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
 }
 
    
   })
 ->orwhereHas('debtor_sub_account4', function ( $query2) use($request){
   
 
   if($request->from){
     $query2->where('date','>=',$request->from)
     ;
     }
     
     
     if($request->to){
       $query2->where('date','<=',$request->to);
     }
 
   
   
   })->with(['debtor_sub_account4'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
   
   
   
   ->with(['manule_creditor_sub_account4'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
   ->with(['manule_debtor_sub_account4'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }]);
 
   }])->with(['creditor_sub_account3'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }])
  
   ->with(['debtor_sub_account3'=>function($query2) use($request){
    if($request->from){
      $query2->where('date','>=',$request->from)
      ;
      }
      
      
     if($request->to){
      $query2 =  $query2->where('date','<=',$request->to);
  }
  
  }]);
 
 
 
   }])->whereHas('sub_account3', function($q){
 
 
     
   }) ;
   
   
 
 
 }]);

}])       
         
         ->first();
 
 
         return response()->json(['data'=>$data]);
          
     }
}
