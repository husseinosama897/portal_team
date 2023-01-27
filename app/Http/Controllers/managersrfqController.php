<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\rfq;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Exceptions\CustomException;
use App\notification;
use App\workflow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\flowworkStep;
use App\comment_rfq_cycle;
use App\rfq_cycle;
use App\attachment_rfq_cycle;
use App\Jobs\sendcc;

class managersrfqController extends Controller

{
    

    public function __construct()
    {
        $this->middleware('auth');
    }

    

    public function index(){
        return view('managers.rfq.index');
    }
    
    public function update( $rfq){
     
        if (is_numeric($rfq)){
            $data = rfq::where('id',$rfq)->with(['rfq_cycle'=>function($q){
                return  $q
                ->with(['comment_rfq_cycle'=> function($qu){
                    return $qu->with('attachment_rfq_cycle');
                }])->with('role');
                }])->first();
            if(!empty($data)){
        return view('managers.rfq.update')->with('data',$data);
            }
        }
    }
    
      public function action(request $request,rfq $rfq){
      
    $data =  $this->validate($request,[
       'project_id'=>['required','numeric'],
       'date'=>['required','date','max:255'],
    'subject'=>['required','string','max:255'],
    'status'=>['required','numeric','between:1,2'],
       'to'=>['string','max:255'],
   
        ]);
        try{
    
            DB::transaction(function () use ($request,$data,$rfq) {
    
        $rfq->update([
            'project_id'=>$request->project_id,
    
            'ref'=>$request->ref,
            'status'=>0,
    
          'total'=>$request->total,
            'content'=>$request->content,
          'vat'=>$request->vat,
            
           'date'=>$request->date,
        
            'subject'=>$request->subject,
        
            'to'=>$request->to,
     
        ]);
    
     

        $rfq_cycle =  $rfq->rfq_cycle()->orderBy('id', 'DESC')->first();
        if($rfq_cycle->status == 0){
        $rfq_cycle->status = $request->status;
        $rfq_cycle ->save();
        $perv = workflow::where(['name'=>'rfq'])->first()->flowworkStep()->where(['step'=> $rfq_cycle->step])
            ->first();
        if($request->status == 1){
    
           

            $workflow = workflow::where(['name'=>'rfq'])->first()->flowworkStep()->where(['step'=> $rfq_cycle->step+1])
            ->first();
        

            $content   = 'Your RFQ' .$rfq->ref.'has been approved by'.$perv->role->name ?? ''.' and Under Review from '.$workflow->role->name ?? 'no one';
            if(!empty($workflow->role->user)){
            foreach($workflow->role->user as $user){
                dispatch_now(new sendcc($user,$content,$request->contentmanager));
               }

            }
               
            dispatch_now(new sendcc($rfq->user,$content,$request->contentmanager));
           
            notification::create([
                'type'=>6,
                'read'=>1,
                'name'=>$content,
              'user_id_to'=>$rfq->user_id,
                 'user_id_from'=>auth()->user()->id,  
            ]);

            

            if(!empty($workflow)){
                rfq_cycle::insert([
                    'step'=>$rfq_cycle->step + 1,
                    'status'=>0,
                    'flowwork_step_id'=>$workflow->id,
                    'role_id'=>$workflow->role_id,
                    'rfq_id'=>$rfq->id
                ]);
        
            } else  {
                   $rfq->update([
'status'=>1,
                ]);
            }

        }elseif($request->status == 2){

            $rfq->update([
                'status'=>2,
                                ]);

                                $content   = 'Your matrial request' .$rfq->ref.'has been rejected by'.$perv->role->name ;

                                dispatch_now(new sendcc($rfq->user,$content,$request->contentmanager));
                    
                                notification::create([
                                    'type'=>6,
                                    'read'=>1,
                                    'name'=>$content,
                                  'user_id_to'=>$rfq->user_id,
                                     'user_id_from'=>auth()->user()->id,  
                                ]);


        }
    
        if($request->contentmanager){
            $comment_rfq_cycle = comment_rfq_cycle::create([
                'rfq_cycle_id'=>$rfq_cycle->id,
                'content'=>$request->contentmanager ?? 'No Comment',
                'user_id'=>auth()->user()->id,
            ]);
            
        }

        $files = [];

      if(!empty($comment_rfq_cycle)){
          if($request->count > 0){
        for($counter = 0;  $counter <= $request->count;  $counter++){
        
            $img = 'files-'.$counter;
            
              if($request->$img){
                $image_tmp = $request->$img;
                
                     
                $fileName = 'rfq_'.'_'.'code_'.'' .$rfq->id. Carbon::now().'_step_'.$rfq_cycle->step;
                Storage::disk('google')->put($fileName
                 ,file_get_contents($image_tmp));


                    $extension = $image_tmp->getClientOriginalExtension();
                    $fileName = rand(111,99999).'.'.$extension;
                    $image_tmp->move('uploads/rfq', $fileName);
            ++$counter;
            }else{
              $fileName = null;
            
            }
         
            $files[] = [
             'comment_rfq_cycle_id'=>$comment_rfq_cycle->id,
             'path'=>$fileName,
            ];
            
            }
      }
     
            $chunkfille = array_chunk($files, 3);
            if(!empty($chunkfille)){
                foreach($chunkfille as $chunk){
                    attachment_rfq_cycle::insert($chunk);
                    
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
    
      public function rfqreturn( $rfq){
        if (is_numeric($rfq)){
        $data = rfq::where('id',$rfq)->with(['rfq_cycle'=>function($q){
            return  $q
            ->with(['comment_rfq_cycle'=> function($qu){
                return $qu->with('user');
            }]);
            }])->with('project')->first();
        if(!empty($data)){
        return view('managers.rfq.preview')->with(['data'=>$data]);
        }
        }
         }
      
      
    
       
    
         public function returnasjson(){
          $purchase = auth()->user()->role->rfq_cycle()->with('rfq')->orderBy('created_at','DESC')->paginate(10);
          return response()->json(['data'=>$purchase]);
         }
      
         public function delete(rfq $rfq){
             if($rfq->user_id == auth()->user()->id){
                $rfq->delete();
             }
           
         }
    
    }
    
