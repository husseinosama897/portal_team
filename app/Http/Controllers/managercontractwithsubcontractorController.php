<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\contract_withsubcontractor;
use DB;
use Carbon\Carbon;
use App\workflow;
use App\Jobs\sendcc;
use App\notification;
use App\contract_withsubcontractorcycle;
use App\comment_contract_withsubcontractor_cycle;
use App\Events\NotificationEvent;
class managercontractwithsubcontractorController extends Controller
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
        return view('managers.contract_withsubcontractor.preview')->with('data',$data);
      }
    }
        }
    

    public function index(){
    

        return view('managers.contract_withsubcontractor.index');
    }
    
    public function update( $contract_withsubcontractor){
     
        if (is_numeric($contract_withsubcontractor)){
            $data = contract_withsubcontractor::where('id',$contract_withsubcontractor)->with(['contract_withsubcontractor_cycle'=>function($q){
                return  $q
                ->with(['comment_contract_withsubcontractor_cycle'=> function($qu){
                    return $qu->with('attachment_contract_withsubcontractor_cycle');
           }])->with('role');
                }])->with(['condition','contract_withsubcontractor_attachment','attributes','contractor'])->first();
            if(!empty($data)){
        return view('managers.contract_withsubcontractor.update')->with('data',$data);
            }
        }
    }
    
      public function action(request $request,contract_withsubcontractor $contract_withsubcontractor){
      
    $data =  $this->validate($request,[
  
       'date'=>['required','date','max:255'],

    'status'=>['required','numeric','between:1,2'],
    'project_id'=>['required','numeric'],

       
        ]);
        try{
    
            DB::transaction(function () use ($request,$data,$contract_withsubcontractor) {
   
        $contract_withsubcontractor->update([
           'contractor_id'=>$request->contractor_id,
           'date'=>$request->date,
    'status'=>0,
    'project_id'=>$request->project_id,
        
        ]);
    
     

        $contract_withsubcontractor_cycle =  $contract_withsubcontractor->contract_withsubcontractor_cycle()->orderBy('id', 'DESC')->first();
        if($contract_withsubcontractor_cycle->status == 0){
        $contract_withsubcontractor_cycle->status = $request->status;
        $contract_withsubcontractor_cycle ->save();
       
        $perv = workflow::where(['name'=>'cws'])->first()->flowworkStep()->where(['step'=>$contract_withsubcontractor_cycle->step])
        ->first();

        if($request->status == 1){
    
            
         

            $workflow = workflow::where(['name'=>'cws'])->first()->flowworkStep()->where(['step'=> $contract_withsubcontractor_cycle->step+1])
            ->first();
        
            $content   = 'Your cws' .$contract_withsubcontractor->ref.'has been approved by'.$perv->role->name ?? ''.' and Under Review from '.$workflow->role->name ?? 'no one';
            if(!empty($workflow->role->user)){

            foreach($workflow->role->user as $user){
              
                $job = (new sendcc(  $user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
                $this->dispatch($job);
                NotificationEvent::dispatch($user->id,$content);
               }


            }
             
     
            $job = (new sendcc( $contract_withsubcontractor->user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
            $this->dispatch($job);
            NotificationEvent::dispatch($contract_withsubcontractor->user->id,$content);
     
            notification::create([
             'type'=>3,
             'read'=>1,
             'name'=>'Your cws' .$contract_withsubcontractor->ref.'has been approved ',
           'user_id_to'=>$contract_withsubcontractor->user_id,
              'user_id_from'=>auth()->user()->id,  
         ]);



            if(!empty($workflow)){
                contract_withsubcontractorcycle::insert([
                    'step'=>$contract_withsubcontractor_cycle->step + 1,
                    'status'=>0,
                    'flowwork_step_id'=>$workflow->id,
                    'role_id'=>$workflow->role_id,
                    'contract_withsubcontractor_id'=>$contract_withsubcontractor->id
                ]);
        
            }else  {
                $contract_withsubcontractor->update([
        'status'=>1,
             ]);
      
            


         }
        }elseif($request->status == 2){

            $content   = 'Your cws' .$contract_withsubcontractor->ref.'has been rejected by'.$perv->role->name ;

           
            $job = (new sendcc( $contract_withsubcontractor->user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
            $this->dispatch($job);
    

            NotificationEvent::dispatch($contract_withsubcontractor->user->id,$content);

            $contract_withsubcontractor->update([
                'status'=>2,
                                ]);
        
        }
    
       
        $comment_contract_withsubcontractor_cycle = comment_contract_withsubcontractor_cycle::create([
            'contract_withsubcontractor_cycle_id'=>$contract_withsubcontractor_cycle->id,
            'content'=>$request->contentmanager ?? 'No Comment',
            'user_id'=>auth()->user()->id,
        ]);
            
        

        $files = [];
      if(!empty($comment_contract_withsubcontractor_cycle)){
          if($request->count > 0){
        for($counter = 0;  $counter <= $request->count;  $counter++){
        
            $img = 'files-'.$counter;
          
              if($request->$img){
                $image_tmp = $request->$img;
              
                
                /*
                $fileName = 'contract_withsubcontractor_'.'_'.'code_'.'' .$contract_withsubcontractor->id. Carbon::now().'_step_'.$contract_withsubcontractor_cycle->step;
                Storage::disk('google')->put($fileName
                 ,file_get_contents($image_tmp));
*/

                    $extension = $image_tmp->getClientOriginalExtension();
                    $fileName = rand(111,99999).'.'.$extension;
                    $image_tmp->move('uploads/contract_withsubcontractor/managers', $fileName);
            ++$counter;
            }else{
              $fileName = null;
            
            }
         
            $files[] = [
             'comment_contract_withsubcontractor_cycle_id'=>$comment_contract_withsubcontractor_cycle->id,
             'path'=>$fileName,
            ];
            
            }
        
      }
     
           
            $chunkfille = array_chunk($files, 3);
            if(!empty($chunkfille)){
                foreach($chunkfille as $chunk){
                    attachment_contract_withsubcontractor_cycle::insert($chunk);
                    
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
    
      public function contract_withsubcontractorreturn( $contract_withsubcontractor){
        if (is_numeric($contract_withsubcontractor)){
        $data = contract_withsubcontractor::where('id',$contract_withsubcontractor)->with(['condition','contract_withsubcontractor_attachment'])->with(['contract_withsubcontractor_cycle'=>function($q){
            return  $q
            ->with(['comment_contract_withsubcontractor_cycle'=> function($qu){
                return $qu->with('user');
            }]);
            }])->first();
        if(!empty($data)){
        return view('managers.contract_withsubcontractor.preview')->with(['data'=>$data]);
        }
        }
         }
      
      
    
       
    
         public function returnasjson(request $request){
          $purchase = auth()->user()->role->contract_withsubcontractor_cycle()->orderBy('created_at','DESC')->whereHas('contract_withsubcontractor',function($q)use($request){
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
          ->with(['contract_withsubcontractor'=>function($q){
return $q->with(['user','contractor']);
          }])->paginate(10);
          return response()->json(['data'=>$purchase]);
         }
      
         public function delete(contract_withsubcontractor $contract_withsubcontractor){
             if($contract_withsubcontractor->user_id == auth()->user()->id){
                $contract_withsubcontractor->delete();

             }
           
         }
    
    }
    