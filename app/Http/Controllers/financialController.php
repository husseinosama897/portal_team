<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\petty_cash;
use App\Purchase_order;
use App\petty_cash_paid;
use App\purchase_paid;
use App\petty_cash_paid_attachment;
use App\financial_daily_report;
use App\daily_financial_report_item;
use DB;
use App\paid_attachment;
use Carbon\Carbon;
use App\subcontractor_paid_attachment;
use App\subcontractor_paid;
use App\subcontractor;
use Illuminate\Support\Str;
use App\User;
use App\Jobs\sendcc;
use App\daily_financial_report_item_attachment;

use Illuminate\Support\Facades\Validator;

use App\Exceptions\CustomException;

class financialController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }

  
    public function index(){
        return view('managers.report.daily.financial.index');
    }


    public function json(request $request){
        $data = financial_daily_report::query();

        if($request->ref){
            $data =  $data->where('ref', 'LIKE', '%' . $request->ref . '%');
            
                  }
            
           
            
                          if($request->user_id && $request->user_id !== ''){
            
                            $data =  $data->where('user_id',$request->user_id);
                            
                                  }
            
            
            
                                  
                          if($request->delivery_date){
            
                            $data =  $data->where('delivery_date',$request->delivery_date);
                            
                                  }
            
                                  if($request->from ){
            
                                    $data =  $data->wheredate('date','>=',$request->from);
                                    
                                          }
            
            
                                          if($request->to ){
            
                                            $data =  $data->wheredate('date','<=',$request->to);
                                            
                                                  }

                                                  $data = $data->paginate(10);

                                                  return response()->json(['data'=>$data]);

        
                                                  
    }

    public function update( $financial_daily_report ){
      if(is_numeric($financial_daily_report)){
$data = financial_daily_report::where('id',$financial_daily_report)->with('item',function($q){
  $q->with(['supplier'=>function($q){
    $q->select(['comp','customer_name','id']);
  },'purchase_order'=>function($q){
    $q->select(['id','paid','ref','total','project_id'])->with(['project'=>function($q){
      $q->select(['name','id']);
    }]);
  },'petty_cash'=>function($q){
    $q->select(['id','paid','ref','total','expected_amount','project_id'])->with(['project'=>function($q){
      $q->select(['name','id']);
    }]);
  },
  'subcontractor'=>function($q){
    $q->select(['id','ref','total','paid','project_id'])->with(['project'=>function($q){
      $q->select(['name','id']);
    }]);
  }
  ,'attachment']);
},)->first();

if($data){

$allowed = auth()->user()->role && auth()->user()->role->name   == 'General Manager' || 

auth()->user()->role && auth()->user()->role->name == 'Project Director'
?  1 : 0;

  return view('managers.report.daily.financial.update')->with(['data'=>$data,'allowed'=>$allowed]);
}
       
      }

        
    }


    public function preview( $financial_daily_report ){
      if(is_numeric($financial_daily_report)){
$data = financial_daily_report::where('id',$financial_daily_report)->with('item',function($q){
  $q->with(['supplier'=>function($q){
    $q->select(['comp','customer_name','id']);
  },'purchase_order'=>function($q){
    $q->select(['id','paid','ref','total','project_id'])->with(['project'=>function($q){
      $q->select(['name','id']);
    }]);
  },  'subcontractor'=>function($q){
    $q->select(['id','ref','total','paid'])->with(['project'=>function($q){
      $q->select(['name','id']);
    }]);
  },'petty_cash'=>function($q){
    $q->select(['id','paid','ref','total','expected_amount'])->with(['project'=>function($q){
      $q->select(['name','id']);
    }]);
  }]);
},)->first();

if($data){

  return view('managers.report.daily.financial.preview')->with(['data'=>$data]);
}
       
      }

        
    }

    public function insert(request $request){


    $financial_daily_report =   financial_daily_report::whereDate('created_at',DB::raw('CURDATE()'))->first();

  
if($financial_daily_report){

  daily_financial_report_item::create([
    'user_id'=>$request->user_id ?? null,
    'daily_financial_report_id'=>$financial_daily_report->id ?? null,
   'type'=>$request->type,
    'supplier_id'=>$request->supplier_id ??  null ,
    'purchase_order_id'=>$request->purchase_order_id ?? null,
    'petty_cash_id'=>$request->petty_cash_id ?? null,
'subcontractor_id'=>$request->subcontractor_id ?? null
  ]);


}else{

  $data = financial_daily_report::latest()->first();
  $explode = explode("-",$data->ref ?? 'FR-'.''.'0');

 

 $financial_daily_report = financial_daily_report::create([
    'ref'=>'FR-'.''.$explode[1] + 1,
    'date'=>Carbon::today(),
'status'=>3,
'user_id'=>auth()->user()->id
  ]);

  daily_financial_report_item::create([
    'user_id'=>$request->user_id ?? null,
    'daily_financial_report_id'=>$financial_daily_report->id ?? null,
    'subcontractor_id'=>$request->subcontractor_id ?? null,
   'type'=>$request->type,
    'supplier_id'=>$request->supplier_id ??  null ,
    'purchase_order_id'=>$request->purchase_order_id ?? null,
    'petty_cash_id'=>$request->petty_cash_id ?? null,

  ]);


}

 
}

public function confirm(financial_daily_report $financial_daily_report,request $request){
  $this->validate($request,[
    'total'=>['numeric'],
    'status'=>'numeric|between:0,3'
   ]);
try{
  
  DB::transaction(function () use ($financial_daily_report,$request) {
       

  if($request->deletedfiles){
    daily_financial_report_item::find($request->deletedfiles)->delete();
  }


  $financial_daily_report->increment('amount',$request->total);

  $financial_daily_report->update([
    'status'=>1
  ]);



  $attributes = json_decode($request['attributes'], true);


  $rules = [
              
           
    "id"  => "required|numeric|exists:daily_financial_report_items",

   
    'pay'=> "required|numeric",

];



$files= [ ];
$files_petty_cash = [];
$files_po = [];
$update = [];

foreach($attributes as $att){
  
  $validator = Validator::make($att,
    
  $rules

);
if ($validator->passes()) {
  $update [] = [
   
    'pay'=>$att['pay'],
'id'=>$att['id'],

    'daily_financial_report_id'=>$financial_daily_report->id ?? null,
   
  ];



  if($att['type'] == 'PO'){

  $Purchase_order =  Purchase_order::where('id',$att['purchase_order_id'])->first();

  $Purchase_order->paid !== null ? $Purchase_order->increment('paid',$att['pay']) : 
  $Purchase_order->update([
  'paid' => $att['pay']
]);

  $purchase_paid =  purchase_paid::create([
    'total'=>$att['pay'],
    'purchase_order_id'=>$Purchase_order->id
  ]);

  $number = $Purchase_order->paids()->count() + 1;


}


elseif($att['type'] == 'PC'){

  $petty_cash =  petty_cash::where('id',$att['petty_cash_id'])->first();

  $petty_cash->paid !== null ? $petty_cash->increment('paid',$att['pay']) : 
$petty_cash->update([
  'paid' => $att['pay']
]);

  $petty_cash_paid =  petty_cash_paid::create([
    'total'=>$att['pay'],
    'petty_cash_id'=>$petty_cash->id
  ]);

  $number = $petty_cash->paids()->count() + 1;


}


elseif($att['type'] == 'SI'){

  $subcontractor =  subcontractor::where('id',$att['subcontractor_id'])->first();

  $subcontractor->paid !== null ? $subcontractor->increment('paid',$att['pay']) : 
$subcontractor->update([
  'paid' => $att['pay']
]);

  $subcontractor_paid =  subcontractor_paid::create([
    'total'=>$att['pay'],
    'subcontractor_id'=>$subcontractor->id
  ]);

  $number = $subcontractor->paids()->count() + 1;


}



$counter = 0;
  if($att['files'] > 0){
    for($counter = 0;  $counter <= $att['files'];  $counter++){
     

        $img = 'files-'.$att['id'].'-'.$counter;
        $image_tmp = $request->$img;
        
          if($request->$img){
        
            $fileName = Str::random(4).$financial_daily_report->ref.'_'.$counter.'.'.$image_tmp->getClientOriginalExtension();
      
            $extension = $image_tmp->getClientOriginalExtension();
                    
            $image_tmp->move('uploads/financial_report/'.$financial_daily_report->ref.'/'.$att['id'], $fileName);
   
            
      $files[] = [
                   'financial_report_item_id'=>$att['id'],
                   'path'=>$fileName,
                  ];
 
        }

/*
      report files is  ended  we gonna storaging PO FILES 

      AND Petty cash  

      */


      
if($att['type'] == 'PO'){

  $fileName = Str::random(4).'_'.$att['purchase_order']['ref'].'invoice_'.$number.'.'.$image_tmp->getClientOriginalExtension();
      
  $extension = $image_tmp->getClientOriginalExtension();
          
  $image_tmp->move('uploads/purchase_order/'.$Purchase_order->ref.'/invoice', $fileName);

$files_po[] = [
         'purchase_paid_id'=>$purchase_paid->id,
         'path'=>$fileName,
        ];


}



if($att['type'] == 'PC'){

  $fileName = Str::random(4).'_'.$att['petty_cash']['ref'].'invoice_'.$number.'.'.$image_tmp->getClientOriginalExtension();
      
  $extension = $image_tmp->getClientOriginalExtension();
          
  $image_tmp->move('uploads/petty_cash/'.$petty_cash->ref.'/invoice', $fileName);

$files_petty_cash[] = [
         'petty_cash_id'=>$petty_cash_paid->id,
         'path'=>$fileName,
        ];


}




if($att['type'] == 'SI'){

  $fileName = Str::random(4).'_'.$att['subcontrctor']['ref'].'invoice_'.$number.'.'.$image_tmp->getClientOriginalExtension();
      
  $extension = $image_tmp->getClientOriginalExtension();
          
  $image_tmp->move('uploads/subcontrctor/'.$subcontractor->ref.'/invoice', $fileName);

$files_subcontractor[] = [
         'subcontractor_id'=>$subcontractor_paid->id,
         'path'=>$fileName,
        ];


}
        
   
++$counter;
   
      }
   
      $chunkfille = array_chunk($files, 3);
      if(!empty($chunkfille)){
          foreach($chunkfille as $chunk){
            daily_financial_report_item_attachment::insert($chunk);
          }
             }


             $chunkfille = array_chunk($files_po, 3);
             if(!empty($chunkfille)){
                 foreach($chunkfille as $chunk){
                  paid_attachment::insert($chunk);
                 }
                    }


                    $chunkfille = array_chunk($files_petty_cash, 3);
                    if(!empty($chunkfille)){
                        foreach($chunkfille as $chunk){
                          petty_cash_paid_attachment::insert($chunk);
                        }
                           }


                           $chunkfille = array_chunk($files_subcontractor, 3);
                           if(!empty($chunkfille)){
                               foreach($chunkfille as $chunk){
                                subcontractor_paid_attachment::insert($chunk);
                               }
                                  }



                    
                          }
             
   }else{
         
    $errors  = $validator->errors()->toArray();
    $data = json_encode($errors);
  
            throw new CustomException ($data);

}



}

daily_financial_report_item::upsert($update,[   
  'id',
  'daily_financial_report_id',
],
['pay']);

});
    

$users = User::whereHas('role',function($q){
  $q->where('name','General Manager')->orWhere('name','Project Director');
})->select(['id','name','email'])->get();

$content = 'financial daily report ready to review';
foreach($users as $user){

  $job = (new sendcc($user,$content))->delay(Carbon::now()->addSeconds(90));
$this->dispatch($job);


}





}
catch (Exception $e) {
    return $e;
}

    }


}
