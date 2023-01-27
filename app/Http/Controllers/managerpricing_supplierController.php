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
use App\comment_purchase_cycle;
class managerpricing_supplierController extends Controller
{


    public function __construct()
  {
      $this->middleware('auth');
  }


  public function index(){
      return view('managers.pricing_supplier.index');
  }

  public function json(request $request){
   $pricing_supplier = auth()->user()->role
   ->pricing_supplier_cycle()->orderBy('updated_at','DESC')->WhereHas('pricing_supplier',function($q) use( $request){
    if($request->ref){
$q->where('ref',$request->ref);

    }

    if($request->project_id && $request->project_id !== ''){

      $q->where('project_id',$request->project_id);
      
            }

            if($request->user_id && $request->user_id !== ''){

              $q->where('user_id',$request->user_id);
              
                    }

                    if($request->supplier_id && $request->supplier_id !== ''){

                      $q->where('supplier_id',$request->supplier_id);
                      
                            }


                    
            if($request->delivery_date){

              $q->where('delivery_date',$request->delivery_date);
              
                    }

                    if($request->date){

                      $q->where('date',$request->date);
                      
                            }



                            return $q;

   })->with(['pricing_supplier'=>function($query) {
     return  $query->with('user');
   }])->paginate(10);



   return response()->json(['data'=>$pricing_supplier]);
  }

  

  public function preview( $pricing_supplier){
   if (is_numeric($pricing_supplier)){
       $data = pricing_supplier::where('id',$pricing_supplier)->with(['attributes','note'])->with(['pricing_supplier_cycle'=>function($q){
          return  $q->with(['pricing_supplier_comment_cycle'=> function($qu){
              return $qu->with('user');
          }])->with(['role'=> function($q){
             
          }]);
          }])
          ->with(['attributes2'=>function($q){
            return $q->where('product_id','=',null);
        }])
          ->first();
       if(!empty($data)){
           return view('managers.pricing_supplier.preview')->with(['data'=>$data]);
       }
    
   }

  }

  public function update( $pricing_supplier){
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
           
       
           return view('managers.pricing_supplier.update')->with(['data'=>$data]);
       }
    
   }

  }

public function action(request $request,pricing_supplier $pricing_supplier){


   $data =  $this->validate($request,[
 
      'date'=>['required','date','max:255'],
  'subject'=>['required','string','max:255'],
 
  'status'=>['required','numeric','between:1,2'],
      'ref'=>['string','max:255'],
 
     

       ]);

try{

    DB::transaction(function () use ($request,$pricing_supplier,$data) {
      $cash = $request->cash == true ? 1 :0;
  $on_vat = $request->no_vat == true ? 1 :0;

$pricing_supplier->update([

    'date'=>$request['date'],
'subject'=>$request['subject'],
'supplier_id'=>$request->supplier_id,
   'ref'=>$request['ref'],
  'order_for'=>$request->order_for,

    

]);


$pricing_supplier_cycle =  $pricing_supplier->pricing_supplier_cycle()->orderBy('id', 'DESC')->first();
if($pricing_supplier_cycle->status == 0){

  $pricing_supplier_cycle->status = $request->status;
  $pricing_supplier_cycle ->save();
  $perv = workflow::where(['name'=>'pricing_supplier'])->first()->flowworkStep()->where(['step'=> $pricing_supplier_cycle->step])
  ->first();

     $workflow = workflow::where(['name'=>'pricing_supplier'])->first()->flowworkStep()->where(['step'=> $pricing_supplier_cycle->step+1])
     ->first();
 if($request->status == 1){

     $content   = 'Your  pricing supplier ' .$pricing_supplier->ref.'has been approved by'.$perv->role->name ?? ''.' and Under Review from '.$workflow->role->name ?? 'no one';

     if(!empty($workflow->role->user)){
      foreach($workflow->role->user as $user){
          
          $job = (new sendcc($user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
          $this->dispatch($job);
  
          NotificationEvent::dispatch($user->id,$content);
         }
     }
    



     $job = (new sendcc($pricing_supplier->user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
     $this->dispatch($job);
     NotificationEvent::dispatch($pricing_supplier->user->id,$content);

     notification::create([
      'type'=>4,
      'read'=>1,
      'name'=>'Your pricing supplier' .$pricing_supplier->ref.'has been approved ',
    'user_id_to'=>$pricing_supplier->user_id,
       'user_id_from'=>auth()->user()->id,  
  ]);



     if(!empty($workflow)  ){
 pricing_supplier_cycle::insert([
             'step'=>$pricing_supplier_cycle->step + 1,
             'status'=>0,
             'flowwork_step_id'=>$workflow->id,
             'role_id'=>$workflow->role_id,
             'pricing_supplier_id'=>$pricing_supplier->id
         ]);
 
     }else  {
      $pricing_supplier->update([
'status'=>1,
   ]);




}
 }elseif($request->status == 2){
  $content   = 'Your' .$pricing_supplier->ref.'has been rejected by'.$perv->role->name ;


  $job = (new sendcc($pricing_supplier->user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
  $this->dispatch($job);

  NotificationEvent::dispatch($pricing_supplier->user->id,$content);

  $pricing_supplier->update([
      'status'=>2,
                      ]);


                      notification::create([

                          'type'=>4,
                          'read'=>1,
                          'name'=>'Your' .$pricing_supplier->ref.'has been rejected by',
                        'user_id_to'=>$pricing_supplier->user_id,
                           'user_id_from'=>auth()->user()->id,  
                      ]);

                      
                      
} 
 
    
     $comment_purchase_cycle = comment_purchase_cycle::create([
         'pricing_supplier_cycle_id'=>$pricing_supplier_cycle->id,
         'content'=>$request->contentmanager ?? 'No Comment',
         'user_id'=>auth()->user()->id,
     ]);
     

     
        $files = [];
       if($request->count > 0){
        for($counter = 0;  $counter <= $request->count;  $counter++){
          $img = 'files-'.$counter;

   


     
              if($request->$img){

                $image_tmp = $request->$img;
                
             $fileName = 'pricing_supplier_'.'_'.'code_'.'' .$pricing_supplier->id. Carbon::now().'_step_'.$pricing_supplier_cycle->step;
        Storage::disk('google')->put($fileName
        ,file_get_contents($image_tmp));

          ++$counter;
         
        
            ++$counter;
            }else{
            
            
            }

          

       
      }


      }
        

}

    });


}
catch (Exception $e) {
    return $e;
}

}
  

}

