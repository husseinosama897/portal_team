<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\service;
use App\service_cycle;
use App\service_attachment;
use App\service_attribute;
use App\workflow;
use DB;
use App\notification;
use Validator;
use App\Jobs\rolecc;
use App\Jobs\sendcc;
use Carbon\Carbon;
use App\Events\NotificationEvent;
class serviceController extends Controller
{
   
    public function __construct()
    {
        $this->middleware('auth');
    }
    

    public function edit( $service){
     
        if (is_numeric($service)){
            $data = service::where('id',$service)->with(['service_cycle'=>function($q){
                return  $q
                ->with(['service_comment_cycle'=> function($qu){
                    return $qu->with('files');
                }])->with('role');
                }])->with(['attributes','files'])->first();
            if(!empty($data)){
        return view('service.update')->with('data',$data);
            }
        }
    }

    public function update(request $request,service $service){
      
        $data =  $this->validate($request,[
            'date'=>['required','string','max:255'],
           'date'=>['required','date'],
        'subject'=>['required','string','max:255'],
           'total'=>['required','numeric','digits_between:1,99999999'],
         
            ]);
            try{
        
                DB::transaction(function () use ($request,$data,$service) {
       
            $service->update([
      
                'status'=>0,
        
              'total'=> ( $request->total  ) ?? 0 ,
              
      
'date'=>$request->date,
            
                'subject'=>$request->subject,
            
         'content'=>$request->content,
         'role_id'=>$request->role_id
            ]);
        
         
    
            $service_cycle =  $service->service_cycle()->orderBy('id', 'DESC')->first();
           

            $service_cycle->update(['status'=>0]);

       

            $perv = workflow::where(['name'=>'service'])->first()->flowworkStep()->where(['step'=> $service_cycle->step])
            ->first();
        
          
        
             //

                 
                
foreach( $perv->role->user as $flow){

    notification::create([

        'type'=>3,
        'read'=>1,
        'name'=>'service request has been modified',
      'user_id_to'=>$flow->id,
         'user_id_from'=>auth()->user()->id,
         
    ]);
    $user = $flow;
    $content = 'service request has been modified';
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
                            
                    $image_tmp->move('uploads/service', $fileName);
           
              $files[] = [
                           'service_id'=>$service->id,
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
                    service_attachment::insert($chunk);
                  }
                     }
                     
           }

       


           $rules = [
            
 'item'=> "required|string",
           'amount'=>"required|numeric",
  'employee_cost'=> "required|numeric",
           
        ];
    
        $attributes = json_decode($request->attr, true);
       $data = [];
    foreach($service->attributes as $att){
        $att->delete();
    }

    foreach($attributes as $attr){
     


        $validator = Validator::make($attr,
    
            $rules
    
        );
        
        
        if ($validator->passes()) {
        $data [] = [
            'service_id'=>$service->id,
            'item'=>$attr['item'],
           'amount'=>$attr['amount'],
              'percentage'=>$attr['percentage'],
      'employee_cost'=>$attr['employee_cost']
        ];
        }else{
         
            $errors  = $validator->errors()->toArray();
            $data = json_encode($errors);
          
                    throw new CustomException ($data);
    
        }


    }


$array = array_chunk($data,10);

foreach($array as $arr){
    service_attribute::insert($arr);
}
    
             
    
        });
        
            }
            catch (Exception $e) {
                return $e;
            }
          }

    public function index(){
        $serviceworkflow =    workflow::where('name','service')->with(['flowworkStep'=>function($q){
            return     $q->with('role');
             }])->first();

        return view('service.index')->with('workflow',$serviceworkflow);
    }

    public function preview2(request  $request){
   
    
        return view('service.previewdef');
            
             
            
        
           }

    public function create(){
        $data = service::latest()->first();
        $explode = explode("-",$data->ref ?? 'HR-'.''.'0');

        return view('service.create')->with(['ref'=>'HR-'.''.$explode[1] + 1]);
    }
    
      public function insrting(request $request){
      
        $data =  $this->validate($request,[
            'content'=>['required','string','max:255'],
           'date'=>['required','date'],
        'subject'=>['required','string','max:255'],
           'total'=>['required','numeric','digits_between:1,99999999'],
         
            ]);

        try{
    
            DB::transaction(function () use ($request,$data) {
    
        $service = service::create([
            
            'status'=>0,
        
            'total'=> ( $request->total  ) ?? 0 ,
            
            'ref'=>$request->ref,
'date'=>$request->date,
          
'role_id'=>$request->role_id,

'subject'=>$request->subject,

  'content'=>$request->content,

       'employee_id'=>$request->employee_id,

            'user_id'=>auth()->user()->id,
        
            

        ]);


# CALLING attributes request it json type 

$attributes = json_decode($request->attr,true)
;

        if($request->count > 0){
            for($counter = 0;  $counter <= $request->count;  $counter++){
             
                $img = 'files-'.$counter;
                
                  if($request->$img){
                    $image_tmp = $request->$img;
                    $fileName = Str::random(40).'.'.$image_tmp->getClientOriginalExtension();
              
                    $extension = $image_tmp->getClientOriginalExtension();
                            
                    $image_tmp->move('uploads/service', $fileName);
           
              $files[] = [
                           'service_id'=>$service->id,
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
                    service_attachment::insert($chunk);
                  }
                     }
                     
           }


           
           $rules = [
            
            'item'=> "required|string",
                      'amount'=>"required|numeric",
             'employee_cost'=> "required|numeric",
                      
                   ];

                   $sciling = [];
    
           foreach($attributes as $attr){
     


            $validator = Validator::make($attr,
        
                $rules
        
            );
            
            
            if ($validator->passes()) {
            $sciling [] = [
                'service_id'=>$service->id,
                'item'=>$attr['item'],
               'amount'=>$attr['amount'],
                  'percentage'=>$attr['percentage'],
          'employee_cost'=>$attr['employee_cost']
            ];
            }else{
             
                $errors  = $validator->errors()->toArray();
                $data = json_encode($errors);
              
                        throw new CustomException ($data);
        
            }
    
    
        }
    
    
    $array = array_chunk($sciling,10);
    
    foreach($array as $arr){
        service_attribute::insert($arr);
    }
        
    


  


    
    
$workflow = workflow::where('name','service')->first()->flowworkStep()
->first();


foreach( $workflow->role->user as $flow){

    notification::create([

        'type'=>3,
        'read'=>1,
        'name'=>'New serivce Request',
      'user_id_to'=>$flow->id,
         'user_id_from'=>auth()->user()->id,
         
    ]);
    $user = $flow;
    $content = 'New serivce Request';
    $managercontent = '';
    $job = (new rolecc($user,$content,$managercontent))->delay(Carbon::now()->addSeconds(90));
   $this->dispatch($job);
   NotificationEvent::dispatch($user->id,$content);
   
 }

 service_cycle::insert([
 'step'=>1,
 'status'=>0,
 'flowwork_step_id'=>$workflow->id,
 'role_id'=>$workflow->role_id,
 'service_id'=>$service->id
]);

    
    });
    
        }
        catch (Exception $e) {
            return $e;
        }
      }
    
      public function servicereturn( $service){
        if (is_numeric($service)){
            
            $data = service::where('id',$service)->with(['attributes'])->with(['service_cycle'=>function($q){
                return  $q
                ->with(['comment_service_cycle'=> function($qu){
                    return $qu->with('user');
                }]);
                }])->with('project')->first();

        if(!empty($data)){
        return view('service.preview')->with(['data'=>$data]);
        }
        }
         }
      
      
    
       
    
         public function json(){
          $service = auth()->user()->service()->orderBy('created_at','DESC')
          ->with(['service_cycle'=>function($q){
            return   $q->with('role');
           }])->paginate(10);
          return response()->json(['data'=>$service]);
         }
      
         
         public function delete(service $service){
             if($service->user_id == auth()->user()->id){
                $service->delete();
             }
           
         }
    
    }
    
