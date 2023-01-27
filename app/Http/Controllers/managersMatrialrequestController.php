<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\workflow;
use Carbon\Carbon;
use App\notification;
use App\flowworkStep;
use App\matrial_request_cycle;
use App\comment_matrial_cycle;
use App\attachment_matrial_cycle;
use App\matrial_request;
use Illuminate\Support\Facades\Storage;
use DB;
use App\Exceptions\CustomException;
use App\matrial_condition as note;
use App\Jobs\sendcc;
use App\Purchase_order;
use App\petty_cash;
use App\petty_attr;
use App\purchase_order_product;

class managersMatrialrequestController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    

   public function index(){
       return view('managers.matrial_request.index');
   }

   public function returnjsonmatrial(){
    $matrial_request = auth()->user()->role
    ->matrial_request_cycle()->orderBy('created_at','DESC')->with(['matrial_request'=>function($q){

    return    $q->with(['user','project']);

    }])->paginate(10);

    return response()->json(['data'=>$matrial_request]);
   }

   

   public function matrial_requestreturn( $matrial_request){
    if (is_numeric($matrial_request)){
        $data = matrial_request::where('id',$matrial_request)->with(['attributes','note'])->with(['matrial_request_cycle'=>function($q){
            return  $q->with(['comment_matrial_cycle'=> function($qu){
                return $qu->with('user');
            }]);
            }])->with('project')->first();
        if(!empty($data)){
            return view('managers.matrial_request.preview')->with(['data'=>$data]);
        }
     
    }

   }

   public function updatematrial_requestreturn( $matrial_request){
    if (is_numeric($matrial_request)){
    
        $data = matrial_request::where('id',$matrial_request)->with(['matrial_request_cycle'=>function($q){
            return  $q->with(['comment_matrial_cycle'=> function($qu){
                return $qu->with('attachment_matrial_cycle');
            }])->with('role');
            }])->with('attributes')->with('files')->with('note')->first();
        if(!empty($data)){
            return view('managers.matrial_request.update')->with(['data'=>$data]);
        }
     
    }

   }

public function forwardToPo(matrial_request $matrial_request){
if($matrial_request->status == 1){


    $matrial_request_cycle =  $matrial_request->matrial_request_cycle()->orderBy('id', 'ASC')->first();


    $data = Purchase_order::latest()->first();
    $explode = explode("-",$data->ref ?? 'Po-'.''.'0');

    $Purchase_order = Purchase_order::create([
        'project_id'=>$matrial_request['project_id'],
        'date'=>$matrial_request['date'],
    'subject'=>$matrial_request['subject'],
       'user_id'=>$matrial_request_cycle->comment_matrial_cycle->user_id,
       'status'=>3,
       'draft'=>1,
        'ref'=>'PO-'.''.$explode[1] + 1,
     'matrial_request_id'=>$matrial_request->id,
        'to'=>$matrial_request['to'],
        'order_for'=>$matrial_request->content,
    ]);
    
    $matrial = [];
foreach($matrial_request->attributes as $attr){

$matrial[] =  [
        'dis'=>$attr['name'],
          'qty'=>$attr['qty'],
           'unit'=>$attr['unit'],
         'purchase_order_id'=>$Purchase_order->id,
             ];


}

$chunk_matrial = array_chunk($matrial,10);
foreach($chunk_matrial as $chunk){
    purchase_order_product::insert($chunk);
}


foreach($matrial_request->files as $file){
    $src = "/uploads/matrial_request/".$file->path;  // source folder or file
$dest = "/uploads/purchase_order/".$file->path;   // destination folder or file        

shell_exec("cp -r $src $dest");
}
$content = 'general manager transformed  material request to PO'.' '.$Purchase_order->ref;

dispatch_now(new sendcc($matrial_request_cycle->comment_matrial_cycle->user,$content,''));

}
  

}


public function forwardToPetty_cash(matrial_request $matrial_request){
    if($matrial_request->status == 1){
    
    
        $matrial_request_cycle =  $matrial_request->matrial_request_cycle()->orderBy('id', 'ASC')->first();
    
    
        $data = petty_cash::latest()->first();
        $explode = explode("-",$data->ref ?? 'PC-'.''.'0');
    
        $petty_cash = petty_cash::create([
            'project_id'=>$matrial_request['project_id'],
            'date'=>$matrial_request['date'],
        'subject'=>$matrial_request['subject'],
           'user_id'=>$matrial_request_cycle->comment_matrial_cycle->user_id,
           'status'=>3,
           'draft'=>1,
            'ref'=>'PC-'.''.$explode[1] + 1,
         'matrial_request_id'=>$matrial_request->id,
            'to'=>$matrial_request['to'],
            'order_for'=>$matrial_request->content,
        ]);
        
        $matrial = [];
    foreach($matrial_request->attributes as $attr){
    
    $matrial[] =  [
            'name'=>$attr['name'],
              'qty'=>$attr['qty'],
               'unit'=>$attr['unit'],
             'petty_cash_id'=>$petty_cash->id,
                 ];
    
    
    }
    
    $chunk_matrial = array_chunk($matrial,10);
    foreach($chunk_matrial as $chunk){
        petty_attr::insert($chunk);
    }
    
    
    foreach($matrial_request->files as $file){
        $src = "/uploads/matrial_request/".$file->path;  // source folder or file
    $dest = "/uploads/petty_cash/".$file->path;   // destination folder or file        
    
    shell_exec("cp -r $src $dest");
    }
        
    $content = 'general manager transformed  material request to PC'.' '.$petty_cash->ref;

    dispatch_now(new sendcc($matrial_request_cycle->comment_matrial_cycle->user,$content,''));

    
    }
      
    
    }

    

public function action(request $request,matrial_request $matrial_request){


    $data =  $this->validate($request,[
        'quotation'=>['string','max:255'],
       'project_id'=>['required','numeric','max:255'],
       'date'=>['required','date','max:255'],
   'subject'=>['required','string','max:255'],
  
    
       'ref'=>['string','max:255'],
       'to'=>['string','max:255'],
     
 
        ]);
 
 try{
 
     DB::transaction(function () use ($request,$matrial_request,$data) {
 $matrial_request->update([
    
     'project_id'=>$request['project_id'],
     'date'=>$request['date'],
 'subject'=>$request['subject'],


    'ref'=>$request['ref'],
    'status'=>$request->status,
    
     'to'=>$request['to'],
     
     
 
 ]);
 $matrial_request_cycle =  $matrial_request->matrial_request_cycle()->orderBy('id', 'DESC')->first();


 if($matrial_request_cycle->status == 0){
 $matrial_request_cycle->status = $request->status;
 $matrial_request_cycle ->save();

 $perv = workflow::where(['name'=>'matrial_request'])->first()->flowworkStep()->where(['step'=> $matrial_request_cycle->step])
 ->first();

 
if($request->status == 1){
   
    $workflow = workflow::where(['name'=>'matrial_request'])->first()->flowworkStep()->where(['step'=> $matrial_request_cycle->step+1])
    ->first();

  
    $content   = 'Your matrial request' .$matrial_request->ref.'has been approved by'.$perv->role->name ?? ''.' and Under Review from '.$workflow->role->name ?? 'no one';

    if(!empty($workflow->role->user)){
    foreach($workflow->role->user as $user){
        dispatch_now(new sendcc($user,$content,$request->contentmanager));
       }
    }

    
    if(!empty($matrial_request->mention)){

        foreach($matrial_request->mention as $user){
            dispatch_now(new sendcc($user,$content,$request->contentmanager));
         
        }
        

        }
  

 
       
    dispatch_now(new sendcc($matrial_request->user,$content,$request->contentmanager));



    if(!empty($workflow)){
matrial_request_cycle::insert([
            'step'=>$matrial_request_cycle->step + 1,
            'status'=>0,
            'flowwork_step_id'=>$workflow->id,
            'role_id'=>$workflow->role_id,
            'matrial_request_id'=>$matrial_request->id
        ]);

            
        notification::create([
            'type'=>2,
            'read'=>1,
            'name'=>'Your matrial request' .$matrial_request->ref.'has been approved ',
          'user_id_to'=>$matrial_request->user_id,
             'user_id_from'=>auth()->user()->id,  
        ]);




    } else  {
        $matrial_request->update([
'status'=>1,
     ]);
 }
}elseif($request->status == 2){

    $matrial_request->update([
        'status'=>2,
                        ]);

                        $content   = 'Your matrial request' .$matrial_request->ref.'has been rejected by'.$perv->role->name ;

                        dispatch_now(new sendcc($matrial_request->user,$content,$request->contentmanager));
            
                        notification::create([
                            'type'=>2,
                            'read'=>1,
                            'name'=>$content,
                          'user_id_to'=>$matrial_request->user_id,
                             'user_id_from'=>auth()->user()->id,  
                        ]);

}


    $comment_matrial_cycle = comment_matrial_cycle::create([
        'matrial_request_cycle_id'=>$matrial_request_cycle->id,
        'content'=>$request->contentmanager ?? 'No Comment',
        'user_id'=>auth()->user()->id,
    ]);
    


  


       $files = [];
      
       if($request->count > 0){
        for($counter = 0;  $counter <= $request->count;  $counter++){
        
            $img = 'files-'.$counter;
            
              if($request->$img){
                $image_tmp = $request->$img;
                
                        
                $fileName = 'matrial_request_'.'_'.'code_'.'' .$matrial_request->id. Carbon::now().'_step_'.$matrial_request_cycle->step;
                Storage::disk('google')->put($fileName
                 ,file_get_contents($image_tmp));

                 

                    $extension = $image_tmp->getClientOriginalExtension();
                    $fileName = rand(111,99999).'.'.$extension;
                    $image_tmp->move('uploads/matrial_request', $fileName);
            ++$counter;
            }else{
              $fileName = null;
            
            }
            $files[] = [
             'comment_matrial_cycle_id'=>$comment_matrial_cycle->id,
             'path'=>$fileName,
            ];
            
            }
           
            $chunkfille = array_chunk($files, 3);
           foreach($chunkfille as $chunk){
            attachment_matrial_cycle::insert($chunk);
            
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
