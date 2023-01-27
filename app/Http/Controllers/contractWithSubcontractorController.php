<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\contract_withsubcontractor;
use App\contract_condition;
use DB;
use App\contract_withsubcontractorcycle;
use App\Jobs\sendcc;
use App\workflow;
use App\notification;
use Carbon\Carbon;
use App\Jobs\rolecc;
use App\attributes_contract;
use App\contract_withsubcontractor_attachment;
use Validator;
use App\Events\NotificationEvent;
use Illuminate\Support\Str;
use App\Exceptions\CustomException;
class contractWithSubcontractorController extends Controller
{
  
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function preview( $contract_withsubcontractor){
     
      if (is_numeric($contract_withsubcontractor)){
          $data = contract_withsubcontractor::where('id',$contract_withsubcontractor)->with(['contract_withsubcontractor_cycle'=>function($q){
              return  $q->with(['comment_contract_withsubcontractor_cycle'=>function($q){
                return $q->with('user');
              }]);
              }])->with(['condition','attributes','contractor'])->first();
          if(!empty($data)){
      return view('contract_withsubcontractor.preview')->with('data',$data);
    }
  }
      }



    public function updatepage( $contract_withsubcontractor){
     
      if (is_numeric($contract_withsubcontractor)){
          $data = contract_withsubcontractor::where('id',$contract_withsubcontractor)->with(['contract_withsubcontractor_cycle'=>function($q){
              return  $q
              ->with(['comment_contract_withsubcontractor_cycle'=> function($qu){
                  return $qu->with('attachment_contract_withsubcontractor_cycle');
              }])->with('role');
              }])->with(['condition','contract_withsubcontractor_attachment','attributes','contractor'])->first();
          if(!empty($data)){
      return view('contract_withsubcontractor.update')->with('data',$data);
       
    
    }
  }
      }


    public function updating(request $request,contract_withsubcontractor $contract_withsubcontractor){
      
      $data =  $this->validate($request,[
   
          ]);
          try{
      
              DB::transaction(function () use ($request,$data,$contract_withsubcontractor) {
     
          $contract_withsubcontractor->update([
              'project_id'=>$request->project_id,
    
              'status'=>0,
      
            'total'=> ( $request->total + $request->vat ) ?? 0 ,
             'date'=>$request->date, 
          ]);
      
       

          if($request->deletedfiles){
            contract_withsubcontractor_attachment::find($request->deletedfiles)->delete();
         }

  
          $contract_withsubcontractor_cycle =  $contract_withsubcontractor->contract_withsubcontractor_cycle()->orderBy('id', 'DESC')->first();
         
          $contract_withsubcontractor_cycle->update(['status'=>0]);

         

          $perv = workflow::where(['name'=>'cws'])->first()->flowworkStep()->where(['step'=> $contract_withsubcontractor_cycle->step])
          ->first();
      
        
      
           //

               
              
foreach( $perv->role->user as $flow){

  notification::create([

      'type'=>3,
      'read'=>1,
      'name'=>'contract_withsubcontractor request has been modified',
    'user_id_to'=>$flow->id,
       'user_id_from'=>auth()->user()->id,
       
  ]);
  $user = $flow;
  $content = 'contract_withsubcontractor request has been modified';
  $managercontent = '';
  $job = (new rolecc($user,$content,$managercontent))->delay(Carbon::now()->addSeconds(90));
 $this->dispatch($job);
 

 NotificationEvent::dispatch($user->id,$content);

 
}
     
       if($request->count > 0){
          for($counter = 0;  $counter <= $request->count;  $counter++){
           
              $img = 'files-'.$counter;
              
                if($request->$img){
                  $image_tmp = $request->$img;
                  $fileName = Str::random(40).'.'.$image_tmp->getClientOriginalExtension();
            
                  $extension = $image_tmp->getClientOriginalExtension();
                          
                  $image_tmp->move('uploads/contract_withsubcontractor', $fileName);
         
            $files[] = [
                         'contract_withsubcontractor_id'=>$contract_withsubcontractor->id,
                         'path'=>$fileName,
                        ];
              ++$counter;
              }else{
                $fileName = null;
              
              }
         
         
            }
         
            $chunkfille = array_chunk($files, 3);
   
            if(!empty($chunkfille)){
                foreach($chunkfille as $chunk){
                  contract_withsubcontractor_attachment::insert($chunk);
                }
                   }
                   
         }

     
         $rules = [
            
         
          "qty"  => "required|numeric",
      
         
          'name'=> "required|string",
         'unit'=> "string|max:255",
         
         'unit_price'=> "required|numeric",
         
      ];
  
      $attributes = json_decode($request->attribute, true);
      $data =    json_decode($request->condition,true);





  foreach($contract_withsubcontractor->attributes as $att){
      $att->delete();
  }
  foreach($contract_withsubcontractor->condition as $att){

    $att->delete();

}


foreach($data as $d){
 
  $scling[] = [
     'withsubcontractor_id'=>$contract_withsubcontractor->id,
      'condition'=> $d['condition']
  ];
   }
  
  $chunk = array_chunk($scling,3);
  
  foreach($chunk as $ch)
  {
      contract_condition::insert($ch);
  
  }

  $scling = [];

// scaling data

foreach($attributes as $attr){
$scling []= [
  'contract_withsubcontractor_id'=>$contract_withsubcontractor->id,
  'name'=>$attr['name'],
  'unit'=>$attr['unit'],
  'unit_price'=>$attr['unit_price'],
  'expctedqty'=>$attr['expctedqty'],
 
];
 
}


$chunkdata = array_chunk($scling,10);
foreach($chunkdata as $data){
  attributes_contract::insert($data);
}




      
  

           
  
      });
      



          }
          catch (Exception $e) {
              return $e;
          }
        }


    
 
public function chunkcws(request $request){
   
  $this->validate($request,[
    'name'=>['required','max:255','string'],

  ]);

  $data = contract_withsubcontractor::where('ref','LIKE','%'.$request->name.'%')->select(['ref','id'])->get()->take(5);

return response()->json(['data'=>$data]);

}

public function index(){
  $cwsworkflow =    workflow::where('name','cws')->with(['flowworkStep'=>function($q){
    return     $q->with('role');
     }])->first();

    return view('contract_withsubcontractor.index')->with('workflow',$cwsworkflow);
}

public function create(){
    $data = contract_withsubcontractor::latest()->first();
 
        $explode = explode("-",$data->ref ?? 'C-'.''.'0');
    
    return view('contract_withsubcontractor.create')->with(['ref'=>'C-'.''.$explode[1] + 1]);
}

  public function contract_withsubcontractorinsrting(request $request){
  
$data =  $this->validate($request,[
   
   'contractor_id'=>['required','numeric'],
   'project_id'=>['required','numeric'],
   
   'date'=>['required','date','max:255'],


   
    ]);
   
    try{

        DB::transaction(function () use ($request) {


            
    $contract_withsubcontractor = contract_withsubcontractor::create([
'contractor_id'=>$request->contractor_id,
        'user_id'=>auth()->user()->id,
    'ref'=>$request->ref,
       'date'=>$request->date,
       'project_id'=>$request->project_id,
'status'=>0,
'total'=>$request->total,
    ]);

 $data =    json_decode($request->condition,true);

 $attributes = json_decode($request->attribute,true);
 $scling = [];
 if(!empty($data)){
    foreach($data as $d){


    
$scling[] = [
   'withsubcontractor_id'=>$contract_withsubcontractor->id,
    'condition'=> $d['dis'] ?? null,
];
 }
  
 }
 
$chunk = array_chunk($scling,3);

foreach($chunk as $ch)
{
    contract_condition::insert($ch);

}

// scaling data 
$scling = [];

foreach($attributes as $attr){
$scling []= [
  'contract_withsubcontractor_id'=>$contract_withsubcontractor->id,
  'name'=>$attr['dis'],
  'unit'=>$attr['unit'],
  'unit_price'=>$attr['unit_price'],
  'expctedqty'=>$attr['expctedqty'],
 
  
];
 
}

$chunkdata = array_chunk($scling,10);
foreach($chunkdata as $data){
  attributes_contract::insert($data);
}



if($request->count > 0){
    for($counter = 0;  $counter <= $request->count;  $counter++){
     
        $img = 'files-'.$counter;
        
          if($request->$img){
            $image_tmp = $request->$img;
            $fileName = Str::random(40).'.'.$image_tmp->getClientOriginalExtension();
      
            $extension = $image_tmp->getClientOriginalExtension();
                    
            $image_tmp->move('uploads/withcontract_withsubcontractor', $fileName);
   
      $files[] = [
                   'contract_withsubcontractor_id'=>$contract_withsubcontractor->id,
                   'path'=>$fileName,
                  ];
        ++$counter;
        }else{
          $fileName = null;
        
        }
   
   
      }
   
      $chunkfille = array_chunk($files, 3);

      if(!empty($chunkfille)){
          foreach($chunkfille as $chunk){
            contract_withsubcontractor_attachment::insert($chunk);
          }
             }
             
   }


$workflow = workflow::where('name','cws')->first()->flowworkStep()
->first();


foreach( $workflow->role->user as $flow){

    notification::create([

        'type'=>3,
        'read'=>1,
        'name'=>'New Contract With contract_withsubcontractor Request',
      'user_id_to'=>$flow->id,
         'user_id_from'=>auth()->user()->id,
         
    ]);
    $user = $flow;
    $content = 'New Contract With contract_withsubcontractor Request';
    $managercontent = '';
    $job = (new rolecc($user,$content,$managercontent))->delay(Carbon::now()->addSeconds(90));
   $this->dispatch($job);
   NotificationEvent::dispatch($user->id,$content);

 }

 contract_withsubcontractorcycle::insert([
 'step'=>1,
 'status'=>0,
 'flowwork_step_id'=>$workflow->id,
 'role_id'=>$workflow->role_id,
 'contract_withsubcontractor_id'=>$contract_withsubcontractor->id
]);



});

    }
    catch (Exception $e) {
        return $e;
    }
  }

  

     public function returnasjson(){
   $data =  contract_withsubcontractor::with(['contract_withsubcontractor_cycle'=>function($q){
    return $q->with('role');
   }])->with(['contractor','user'])->paginate(10);
      return response()->json(['data'=>$data]);
     }
  
     public function delete(contract_withsubcontractor $contract_withsubcontractor){
            $contract_withsubcontractor->delete();
}

}
