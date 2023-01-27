<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\petty_cash;
use App\petty_cash_attr;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use DB;
use App\workflow;
use App\flowworkStep;
use App\notification;
use App\comment_petty_cash_cycle;
use App\petty_cash_cycle;
use App\report;
use App\petty_inv;
use App\Jobs\sendcc;
use App\attachment_petty_cash_cycle;
use App\Events\NotificationEvent;
class managers_petty_cash_Controller extends Controller

{
    

      public function __construct()
    {
        $this->middleware('auth');
    }
    
    
public function updatetotal(petty_cash $petty_cash,request $request){

$petty_cash->update([
  'total'=>$request->total,
  'vat'=>$request->vat,
]);

}

public function inv(request $request,petty_cash $petty_cash){

    $petty_cash->update([
      
      'closed'=>1,
    
    ]);
    
      if($request->count > 0){
        for($counter = 0;  $counter <= $request->count;  $counter++){
         
            $img = 'files-'.$counter;
            
              if($request->$img){
                $image_tmp = $request->$img;
                $fileName = Str::random(40).'.'.$image_tmp->getClientOriginalExtension();
          
                $extension = $image_tmp->getClientOriginalExtension();
                        
                $image_tmp->move('uploads/petty_cash/inv', $fileName);
       
          $files[] = [
                       'petty_cash_id'=>$petty_cash->id,
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
                petty_inv::insert($chunk);
              }
                 }
                 
       }
    
    }

    

    public function index(){
        return view('managers.petty_cash.index');
    }
    
    public function update( $petty_cash){
     
        if (is_numeric($petty_cash)){
            $data = petty_cash::where('id',$petty_cash)->with(['petty_cash_cycle'=>function($q){
                return  $q
                ->with(['comment_petty_cash_cycle'=> function($qu){
                    return $qu->with('attachment_petty_cash_cycle');
                }])->with('role');
                }])->with(['attributes','petty_cash_attachment'])->first();
            if(!empty($data)){
        return view('managers.petty_cash.update')->with('data',$data);
            }
        }
    }
    
      public function action(request $request,petty_cash $petty_cash){
      
    $data =  $this->validate($request,[
       'project_id'=>['required','numeric'],
       'date'=>['required','date','max:255'],
    'subject'=>['required','string','max:255'],
    'status'=>['required','numeric','between:1,2'],
       'to'=>['string','max:255'],
       
        ]);
        try{
    
            DB::transaction(function () use ($request,$data,$petty_cash) {
   
        $petty_cash->update([
            'project_id'=>$request->project_id,
            'status'=>0,
          'ref'=>$request->ref,

       
           'date'=>$request->date,
        
            'subject'=>$request->subject,
        
            'to'=>$request->to,
        
        ]);
    
     

        $petty_cash_cycle =  $petty_cash->petty_cash_cycle()->orderBy('id', 'DESC')->first();
        if($petty_cash_cycle->status == 0){
        $petty_cash_cycle->status = $request->status;
        $petty_cash_cycle ->save();
       
        $perv = workflow::where(['name'=>'petty_cash'])->first()->flowworkStep()->where(['step'=> $petty_cash_cycle->step])
        ->first();
        
        if($request->status == 1){
    
            
         

            $workflow = workflow::where(['name'=>'petty_cash'])->first()->flowworkStep()->where(['step'=> $petty_cash_cycle->step+1])
            ->first();
        
            $content   = 'Your petty cash' .$petty_cash->ref.'has been approved by'.$perv->role->name ?? ''.' and Under Review from '.$workflow->role->name ?? 'no one';
            if(!empty($workflow->role->user)){

            foreach($workflow->role->user as $user){
              
                $job = (new sendcc(  $user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
                $this->dispatch($job);
                NotificationEvent::dispatch($user->id,$content);

               }


            }
             
     
            $job = (new sendcc( $petty_cash->user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
            $this->dispatch($job);
    
            NotificationEvent::dispatch($petty_cash->user->id,$content);

            notification::create([
             'type'=>3,
             'read'=>1,
             'name'=>'Your petty cash' .$petty_cash->ref.'has been approved ',
           'user_id_to'=>$petty_cash->user_id,
              'user_id_from'=>auth()->user()->id,  
         ]);



            if(!empty($workflow)){
                petty_cash_cycle::insert([
                    'step'=>$petty_cash_cycle->step + 1,
                    'status'=>0,
                    'flowwork_step_id'=>$workflow->id,
                    'role_id'=>$workflow->role_id,
                    'petty_cash_id'=>$petty_cash->id
                ]);
        
            }else  {
                $petty_cash->update([
        'status'=>1,
             ]);
      
         //    $report =   report::where('date',$petty_cash->date)->increment('total_cash_out',0);
/*
   if(empty($report)){
    report::create([
      'date'=>$petty_cash->date,
      'total_cash_out'=>$petty_cash->total,
    ]);
   }  
*/
//   $petty_cash->project->decrement('petty_cash_expenses',0);


         }
        }elseif($request->status == 2){

            $content   = 'Your petty cash' .$petty_cash->ref.'has been rejected by'.$perv->role->name ;

           
            $job = (new sendcc( $petty_cash->user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
            $this->dispatch($job);
    
            NotificationEvent::dispatch($petty_cash->user->id,$content);


            
            $petty_cash->update([
                'status'=>2,
                                ]);
        
        }
    
       
        $comment_petty_cash_cycle = comment_petty_cash_cycle::create([
            'petty_cash_cycle_id'=>$petty_cash_cycle->id,
            'content'=>$request->contentmanager ?? 'No Comment',
            'user_id'=>auth()->user()->id,
        ]);
            
        

        $files = [];
      if(!empty($comment_petty_cash_cycle)){
          if($request->count > 0){
        for($counter = 0;  $counter <= $request->count;  $counter++){
        
            $img = 'files-'.$counter;
          
              if($request->$img){
                $image_tmp = $request->$img;
              
                
                /*
                $fileName = 'petty_cash_'.'_'.'code_'.'' .$petty_cash->id. Carbon::now().'_step_'.$petty_cash_cycle->step;
                Storage::disk('google')->put($fileName
                 ,file_get_contents($image_tmp));
*/

                    $extension = $image_tmp->getClientOriginalExtension();
                    $fileName = rand(111,99999).'.'.$extension;
                    $image_tmp->move('uploads/petty_cash', $fileName);
            ++$counter;
            }else{
              $fileName = null;
            
            }
         
            $files[] = [
             'comment_petty_cash_cycle_id'=>$comment_petty_cash_cycle->id,
             'path'=>$fileName,
            ];
            
            }
        
      }
     
           
            $chunkfille = array_chunk($files, 3);
            if(!empty($chunkfille)){
                foreach($chunkfille as $chunk){
                    attachment_petty_cash_cycle::insert($chunk);
                    
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
    
      public function petty_cashreturn( $petty_cash){
        if (is_numeric($petty_cash)){
        $data = petty_cash::where('id',$petty_cash)->with(['attributes','petty_cash_attachment'])->with(['petty_cash_cycle'=>function($q){
            return  $q
            ->with(['comment_petty_cash_cycle'=> function($qu){
                return $qu->with('user');
            }]);
            }])->with('project')->first();
        if(!empty($data)){
        return view('managers.petty_cash.preview')->with(['data'=>$data]);
        }
        }
         }
      
      
    
       
    
         public function returnasjson(request $request){
          $purchase = auth()->user()->role->petty_cash_cycle()->orderBy('created_at','DESC')->whereHas('petty_cash',function($q)use($request){
            if($request->ref){
              $q->where('ref', 'LIKE', '%' . $request->ref . '%');
              
                    }

                      if($request->date){
                        $q->where('date',$request->date);
                        
                              }

                
                      if($request->project_id && $request->project_id !== ''){
                
                        $q->where('project_id',$request->project_id);
                        
                              }
                
                              if($request->user_id && $request->user_id !== ''){
                
                                $q->where('user_id',$request->user_id);
                                
                                      }
                                      return $q;
          })
          ->with(['petty_cash'=>function($q){
return $q->with('user');
          }])->paginate(10);
          return response()->json(['data'=>$purchase]);
         }
      
         public function delete(petty_cash $petty_cash){
             if($petty_cash->user_id == auth()->user()->id){
                $petty_cash->delete();

             }
           
         }
    
    }
    