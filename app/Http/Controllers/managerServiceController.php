<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use DB;
use App\workflow;
use App\flowworkStep;
use App\notification;
use App\service_comment_cycle;
use App\service_cycle;
use App\report;
use App\Jobs\sendcc;
use App\service_attachment_cycle;
use App\service;

use App\service_attachment;
use App\service_attribute;
use App\Events\NotificationEvent;
class managerServiceController extends Controller
{
   public function __construct()
      {
          $this->middleware('auth');
      }

      
      public function index(){
          return view('managers.service.index');
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
          return view('managers.service.update')->with('data',$data);
              }
          }
      }
      
        public function action(request $request,service $service){
        
       
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
                
             'content'=>$request->content
                ]);
            
             
  
          $service_cycle =  $service->service_cycle()->orderBy('id', 'DESC')->first();
          if($service_cycle->status == 0){
          $service_cycle->status = $request->status;
          $service_cycle ->save();
         
          $perv = workflow::where(['name'=>'service'])->first()->flowworkStep()->where(['step'=> $service_cycle->step])
          ->first();
          
          if($request->status == 1){
      
              
           
  
              $workflow = workflow::where(['name'=>'service'])->first()->flowworkStep()->where(['step'=> $service_cycle->step+1])
              ->first();
          
              $content   = 'Your service' .$service->ref.'has been approved by'.$perv->role->name ?? ''.' and Under Review from '.$workflow->role->name ?? 'no one';
              if(!empty($workflow->role->user)){
  
              foreach($workflow->role->user as $user){
                
                  $job = (new sendcc(  $user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
                  $this->dispatch($job);
                  NotificationEvent::dispatch($user->id,$content);

                 }
  
  
              }
               
       
              $job = (new sendcc( $service->user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
              $this->dispatch($job);
              NotificationEvent::dispatch($service->user->id,$content);
       
              notification::create([
               'type'=>3,
               'read'=>1,
               'name'=>'Your service' .$service->ref.'has been approved ',
             'user_id_to'=>$service->user_id,
                'user_id_from'=>auth()->user()->id,  
           ]);
  
  
  
              if(!empty($workflow)){
                  service_cycle::insert([
                      'step'=>$service_cycle->step + 1,
                      'status'=>0,
                      'flowwork_step_id'=>$workflow->id,
                      'role_id'=>$workflow->role_id,
                      'service_id'=>$service->id
                  ]);
          
              }else  {
                  $service->update([
          'status'=>1,
               ]);
        
            $report =   report::where('date',$service->date)->increment('total_cash_out',$service->total);
 
     if(empty($report)){
      report::create([
        'date'=>$service->date,
        'total_cash_out'=>$service->total,
      ]);
     }  
 
 
  
           }
          }elseif($request->status == 2){
  
              $content   = 'Your service' .$service->ref.'has been rejected by'.$perv->role->name ;
  
             
              $job = (new sendcc( $service->user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
              $this->dispatch($job);
              NotificationEvent::dispatch($service->user->id,$content);
  
              
              $service->update([
                  'status'=>2,
                                  ]);
          
          }
      
         
          $service_comment_cycle = service_comment_cycle::create([
              'service_cycle_id'=>$service_cycle->id,
              'content'=>$request->contentmanager ?? 'No Comment',
              'user_id'=>auth()->user()->id,
          ]);
              
          
  
          $files = [];
        if(!empty($service_comment_cycle)){
            if($request->count > 0){
          for($counter = 0;  $counter <= $request->count;  $counter++){
          
              $img = 'files-'.$counter;
            
                if($request->$img){
                  $image_tmp = $request->$img;
                
                  
                  /*
                  $fileName = 'service_'.'_'.'code_'.'' .$service->id. Carbon::now().'_step_'.$service_cycle->step;
                  Storage::disk('google')->put($fileName
                   ,file_get_contents($image_tmp));
  */
  
                      $extension = $image_tmp->getClientOriginalExtension();
                      $fileName = rand(111,99999).'.'.$extension;
                      $image_tmp->move('uploads/service', $fileName);
              ++$counter;
              }else{
                $fileName = null;
              
              }
           
              $files[] = [
               'service_comment_cycle_id'=>$service_comment_cycle->id,
               'path'=>$fileName,
              ];
              
              }
          
        }
       
             
              $chunkfille = array_chunk($files, 3);
              if(!empty($chunkfille)){
                  foreach($chunkfille as $chunk){
                    service_attachment_cycle::insert($chunk);
                      
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
      
        public function preview( $service){
          if (is_numeric($service)){
          $data = service::where('id',$service)->with(['attributes','files'])->with(['service_cycle'=>function($q){
              return  $q
              ->with(['service_comment_cycle'=> function($qu){
                  return $qu->with('user');
              }]);
              }])->with(['employee','user'])->first();
          if(!empty($data)){
          return view('managers.service.preview')->with(['data'=>$data]);
          }
          }
           }
        
        
      
         
      
           public function json(request $request){
            $service = auth()->user()->role->service_cycle()->orderBy('created_at','DESC')->whereHas('service',function($q)use($request){
              if($request->ref){
                  $q->where('ref',$request->ref);
                  
                        }
  
                        if($request->date){
                          $q->where('date',$request->date);
                          
                                }
  
                  
                  
                                if($request->user_id && $request->user_id !== ''){
                  
                                  $q->where('user_id',$request->user_id);
                                  
                                        }
                                        return $q;
            })
            ->with(['service'=>function($q){
  return $q->with(['user','employee']);
  
            }])->paginate(10);
            return response()->json(['data'=>$service]);
           }
        
           public function delete(service $service){
               if($service->user_id == auth()->user()->id){
                  $service->delete();
  
               }
             
           }
      
      }
      