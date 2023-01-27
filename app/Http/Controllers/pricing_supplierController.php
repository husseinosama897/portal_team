<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\pricing_supplier;
use App\product;
use App\Exceptions\CustomException;
use App\term;
use App\supplier;
use DB;
use App\payment_pricing;
use Validator;
use App\notification;
use Carbon\Carbon;
use App\pricing_supplier_cycle;
use App\pricing_supplier_attachment;
use App\Jobs\rolecc;
use App\pricing_supplier_product;
use App\Jobs\sendcc;
use App\workflow;
use App\Events\NotificationEvent;
class pricing_supplierController extends Controller
{

  public function __construct()
  {
      $this->middleware('auth');
  }
  

    public function index(){
      $pricing_supplierworkflow =    workflow::where('name','pricing_supplier')->with(['flowworkStep'=>function($q){
        return     $q->with('role');
         }])->first();

  
     return view('pricing_supplier.index')->with(['workflow'=>$pricing_supplierworkflow]);

    
      }




      public function editee($id){
     
  if(is_numeric($id)){
    $data =   pricing_supplier::with(['product','supplier','payment_pricing'])->find($id);
    if(!empty($data)){
      return view('pricing_supplier.update')->with('data',$data);
    }
    
  }
  
     
     
    }

public function getricingdetails( $pricing_supplier){
  
if(is_numeric($pricing_supplier)){

  $data = pricing_supplier::where('id',$pricing_supplier
  )->with(['attributes','attributes2','note'])->first();
  return response()->json(['data'=>$data]);

}
}

    public function pricing_suppliersAutoComplete(request $request){
$this->validate($request,[
  'ref'=>['required','string','max:255']
]);

$pricing_supplier = pricing_supplier::where('ref','LIKE','%'.$request->ref.'%')
->where('status',1)
->SELECT(['ref','id','status'])->get()->take(5);

return response()->json(['data'=>$pricing_supplier]);
    }

    public function preview2(request  $request){
   
    
      return view('pricing_supplier.previewdef');
          
           
          
      
         }
    

    public function preview( $pricing_supplier){
    
          $data = pricing_supplier::where('id',$pricing_supplier)->with(['attributes','note'])
         ->with(['attributes2'=>function($q){
              return $q->where('product_id','=',null);
          }])
          ->with(['pricing_supplier_cycle'=>function($q){
              return  $q->with(['pricing_supplier_comment_cycle'=> function($qu){
                  return $qu->with('user');
              }])->with(['role'=> function($q){
                 
  
              }]);
              
              }])->first();
          if(!empty($data)){
              return view('pricing_supplier.preview')->with(['data'=>$data]);
          }
       
      
  
     }
  
      public function create(){
      
        $data = pricing_supplier::latest()->first();
        $explode = explode("-",$data->ref ?? 'PS-'.''.'0');

   
            return view('pricing_supplier.create')->with(['ref'=>'PS-'.''.$explode[1] + 1]);
            
            
                
      }

    
      public function insert(request $request){

        $data =  $this->validate($request,[

          'date'=>['required','date','max:255'],
      'subject'=>['required','string','max:255'],

          'ref'=>['string','max:255'],
      
          'supplier_id'=>['required','numeric'],
          
           ]);
    
    try{
    
        DB::transaction(function () use ($request,$data) {
        $cash = $request->cash == true ? 1 :0;
        $on_vat = $request->no_vat == true ? 1 :0;
    $pricing_supplier = pricing_supplier::create([
   
        'date'=>$request['date'],
    'subject'=>$request['subject'],
  
       'user_id'=>auth()->user()->id,

       'status'=>0,
       'on_vat'=>$on_vat,
       'cash'=>$cash ,
       'total'=>$request->overall,
       'vat'=>$request->vat,
    
       'percentage_discount'=>$request->percentage_discount,
       'discount'=>$request->discount,
    'subtotal'=>$request->total,
        'ref'=>$request->ref,
        'supplier_id'=>$request->supplier_id,
     
        'order_for'=>$request->order_for,
    ]);
      
    
    $payment = json_decode($request->payment, true);


    $rules = [
              
           
      "name"  => "string",

      "date"  => "date",
  
     
      'dis'=> "string",
     'percentage'=> "string|max:255",
     
     'amount'=> "required|numeric",
     
  ];


    if(!empty($payment)  && $cash  == 1 ){
      foreach($payment as $pay){
          $validator = Validator::make($pay, $rules);
          
          if ($validator->passes()) {
              payment_pricing::insert([
              'dis'=>$pay['dis'] ?? null,
              'pricing_supplier_id'=>$pricing_supplier->id,
                'percentage'=>$pay['percentage'] ?? null,
            'name'=>$pay['name']?? null,
  'amount'=>$pay['amount'] ?? null,
        'date'=>$pay['date'] ?? null,
              ]);
          }else{
           
           $errors  = $validator->errors()->toArray();
      $data = json_encode($errors);
      
              throw new CustomException ($data);
          }
      }
  }


    $attributes = json_decode($request->attr, true);

    
foreach($attributes as $attr){

  $validator = Validator::make(
      array('qty' => $attr['qty']),
      array('qty' => array('required','numeric')),
      array('dis' => $attr['dis']),
      array('dis' => array('required|string')),

      array('unit' => $attr['unit']),
      array('unit' => array('string|max:255')),
      
      array('unit_price' => $attr['unit_price']),
      array('unit' => array('required','numeric'))

  );


  
  if ($validator->passes() == true) {
if(!empty($attr['id'])){
  $pricing_supplier->attributes()->attach($attr['id'] ?? null,[
      'dis'=>$attr['dis'],
        'qty'=>$attr['qty'],
         'unit'=>$attr['unit'],
          'unit_price'=>$attr['unit_price'],
         'total'=>$attr['unit_price'] * $attr['qty'] ?? 0,
       'pricing_supplier_id'=>$pricing_supplier->id, 
      ]);
      
}else{
  pricing_supplier_product::insert([
      'dis'=>$attr['dis'],
      'qty'=>$attr['qty'],
      'product_id'=>null,
       'unit'=>$attr['unit'],
        'unit_price'=>$attr['unit_price'],
       'total'=>$attr['unit_price'] * $attr['qty'] ?? 0,
     'pricing_supplier_id'=>$pricing_supplier->id, 
  ]);
  
              
}



  }
}
    
  $workflow = workflow::where('name','pricing_supplier')->first()->flowworkStep()
  ->first();
  
  
  foreach( $workflow->role->user as $flow){
  
      notification::create([
  
          'type'=>3,
          'read'=>1,
          'name'=>'pricing supplier Request',
        'user_id_to'=>$flow->id,
           'user_id_from'=>auth()->user()->id,
           
      ]);
      $user = $flow;
      $content = 'pricing supplier Request';
      $managercontent = '';
      $job = (new rolecc($user,$content,$managercontent))->delay(Carbon::now()->addSeconds(90));
     $this->dispatch($job);
  //   NotificationEvent::dispatch($user->id,$content);
   
   }
  
   pricing_supplier_cycle::create([
   'step'=>1,
   'status'=>0,
   'flowwork_step_id'=>$workflow->id,
   'role_id'=>$workflow->role_id,
   'pricing_supplier_id'=>$pricing_supplier->id
  ]);

  

});
 


}
catch (Exception $e) {
    return $e;

}
      }
      


      public function update(request $request , $id){

        $data =  $this->validate($request,[

          'date'=>['required','date','max:255'],
      'subject'=>['required','string','max:255'],

          'ref'=>['string','max:255'],
      
          'supplier_id'=>['required','numeric'],
          
           ]);
     $update =  pricing_supplier::where('id',$id)->first();





     if($request->deletedfiles){
      pricing_supplier_attachment::find($request->deletedfiles)->delete();
   }

   

    $update->update([
        'supplier_id'=>$request->supplier_id,
        'fq'=>$request->fq,
        'tax'=>$request->tax,
       
        'total_amount'=>$request->total_amount,
        'discount'=>$request->discount,
        'overall'=>$request->overall,
        'pre_discount'=>$request->pre_discount,
        'tax_number'=>$request->tax_number,
    ]);
    
    
    $payment = json_decode($request->payment, true);

$a = [];

if(!empty($update->note())){
foreach($update->note as $pay){
  $pay->delete();
}
}
if(!empty($payment)){
  foreach($payment as $p){
    $rules = [
          
       
      "percentage"  => "required|numeric",

     
      'name'=> "required|string|max:255",
     'payment_pricing'=> "string|max:255",
     
     'amount'=> "required|numeric",
     
  ];
  $validator = Validator::make($p, $rules);
 
 
  if ($validator->passes() ) {
      $a[] = [
          'pricing_supplier_id'=>$update->id,
          'percentage'=>$p['percentage'],
       'name'=>$p['name'],
      'payment_pricing'=>$p['payment_pricing'] ?? null,
      
      'amount'=>$p['amount'],
      
      ];
    }else{
      $errors  = $validator->errors()->toArray();
      $data = json_encode($errors);
    
              throw new CustomException ($data);
    }
    
      
  }

}
 
  payment_pricing::insert($a);

    $product = json_decode($request->product, true);
if(!empty($update->attributes())){
  $update->attributes()->detach();

}

if(!empty($update->attributes2())){
  $update->attributes2()->delete();

}


$attributes = json_decode($request->attr, true);

    
foreach($attributes as $attr){

  $validator = Validator::make(
      array('qty' => $attr['qty']),
      array('qty' => array('required','numeric')),
      array('dis' => $attr['dis']),
      array('dis' => array('required|string')),

      array('unit' => $attr['unit']),
      array('unit' => array('string|max:255')),
      
      array('unit_price' => $attr['unit_price']),
      array('unit' => array('required','numeric'))

  );


  
  if ($validator->passes() == true) {
if(!empty($attr['id'])){
  $pricing_supplier->attributes()->attach($attr['id'] ?? null,[
      'dis'=>$attr['dis'],
        'qty'=>$attr['qty'],
         'unit'=>$attr['unit'],
          'unit_price'=>$attr['unit_price'],
         'total'=>$attr['unit_price'] * $attr['qty'] ?? 0,
       'pricing_supplier_id'=>$update->id, 
      ]);
      
}else{

  pricing_supplier_product::insert([
      'dis'=>$attr['dis'],
      'qty'=>$attr['qty'],
      'product_id'=>null,
       'unit'=>$attr['unit'],
        'unit_price'=>$attr['unit_price'],
       'total'=>$attr['unit_price'] * $attr['qty'] ?? 0,
     'pricing_supplier_id'=>$update->id, 
  ]);
  
              
}
  }
}


    
  

      }
      



      public function pricing_supplierjson(){
    
     $pricing_supplier=  auth()->user()->pricing_supplier()->orderBy('created_at', 'DESC')
        ->with('supplier')->with(['pricing_supplier_cycle'=>function
        ($q){
          return $q->with('role');
        }])->paginate(10);

return response()->json(['data'=>$pricing_supplier]);

        
    }
      


         
    public function delete($ids){
pricing_supplier::whereIn('id',explode(",",$ids))->delete();
        
      }

      public function updatesupplierorder(request $request,pricing_supplier $pricing_supplier ){
        
  $pricing_supplier->update([
    
    'supplier_id'=>$request->supplier_id,

]);
              
              return response()->json('done',200);
            
                  }



 public function pricing_supplierselect(request $request){

                $data =   pricing_supplier::where('code',$request->code)
                
               ->get()->take(6);

                
                return response()->json(['data'=>$data]);

    
 }



 public function edit( $pricing_supplier){
  if (is_numeric($pricing_supplier)){
  $data = pricing_supplier::where('id',$pricing_supplier)->with(['pricing_supplier_cycle'=>function($q){
          return  $q->with(['pricing_supplier_comment_cycle'=> function($qu){
              return $qu->with('files');
          }])->with('role');
          }])
          ->with('files')
          ->with(['attributes']) ->with(['attributes2'=>function($q){
           return $q->where('product_id','=',null);
       }])->with('note')->first();
      if(!empty($data)){
          
      
          return view('pricing_supplier.update')->with(['data'=>$data]);
      }
   
  }

 }

                  
}
