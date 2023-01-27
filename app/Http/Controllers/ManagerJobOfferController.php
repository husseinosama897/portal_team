<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\joboffer;
use App\joboffer_attachment;
use App\joboffer_comment_cycle;
use App\joboffer_cycle_attachment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use DB;
use App\notification;
use App\workflow;
use App\Jobs\sendcc;
use App\Events\NotificationEvent;
class ManagerJobOfferController extends Controller
{
    public function index(){
        return view('managers.joboffer.index');
    }


    
public function updating(request $request,joboffer $joboffer){

    $this->validate($request,[
        'date'=>['date','required'],
      
     'subject'=>['required','max:255','string'],
     'content'=>['required','string'],
    'contract_type'=>['string'],
       'work_location'=>['string'],
       'salary'=>['string','numeric'],
    ]);
    
        $benefit_check = $request->benefit_check == true ?  1 : 0;
    
        $joboffer->update([
       'date'=>$request->date,
       'ref'=>$request->ref,
       'benefit_check'=>$benefit_check,
     'subject'=>$request->subject,
   
     'content'=>$request->content,
    'contract_type'=>$request->contract_type,
       'work_location'=>$request->work_location,
       'salary'=>$request->salary,
        ]);
    

        

        $joboffer_cycle =  $joboffer->joboffer_cycle()->orderBy('id', 'DESC')->first();
        if($joboffer_cycle->status == 0){
        $joboffer_cycle->status = $request->status;
        $joboffer_cycle ->save();
       
        $perv = workflow::where(['name'=>'joboffer'])->first()->flowworkStep()->where(['step'=> $joboffer_cycle->step])
        ->first();
        
        if($request->status == 1){
    
            
         

            $workflow = workflow::where(['name'=>'joboffer'])->first()->flowworkStep()->where(['step'=> $joboffer_cycle->step+1])
            ->first();
        
            $content   = 'Your  joboffer' .$joboffer->ref.'has been approved by'.$perv->role->name ?? ''.' and Under Review from '.$workflow->role->name ?? 'no one';
            if(!empty($workflow->role->user)){

            foreach($workflow->role->user as $user){
              
                $job = (new sendcc(  $user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
                $this->dispatch($job);
                NotificationEvent::dispatch($user->id,$content);

               }


            }
             
     
            $job = (new sendcc( $joboffer->user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
            $this->dispatch($job);
            NotificationEvent::dispatch($joboffer->user->id,$content);
     
            notification::create([
             'type'=>3,
             'read'=>1,
             'name'=>'Your  joboffer' .$joboffer->ref.'has been approved ',
           'user_id_to'=>$joboffer->user_id,
              'user_id_from'=>auth()->user()->id,  
         ]);



            if(!empty($workflow)){
                joboffer_cycle::insert([
                    'step'=>$joboffer_cycle->step + 1,
                    'status'=>0,
                    'flowwork_step_id'=>$workflow->id,
                    'role_id'=>$workflow->role_id,
                    'joboffer_id'=>$joboffer->id
                ]);
        
            }else  {
                $joboffer->update([
        'status'=>1,
             ]);
      

             $job = (new sendcc( $joboffer->user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
             $this->dispatch($job);
             
         }
        }elseif($request->status == 2){

            $content   = 'Your joboffer' .$joboffer->ref.'has been rejected by'.$perv->role->name ;

           
            $job = (new sendcc( $joboffer->user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
            $this->dispatch($job);
    
            NotificationEvent::dispatch($joboffer->user->id,$content);
     
            $joboffer->update([
                'status'=>2,
                                ]);
        
        }
    
       
        $joboffer_comment_cycle = joboffer_comment_cycle::create([
            'joboffer_cycle_id'=>$joboffer_cycle->id,
            'content'=>$request->contentmanager ?? 'No Comment',
            'user_id'=>auth()->user()->id,
        ]);
            
        

        $files = [];
      if(!empty($joboffer_comment_cycle)){
          if($request->count > 0){
        for($counter = 0;  $counter <= $request->count;  $counter++){
        
            $img = 'files-'.$counter;
          
              if($request->$img){
                $image_tmp = $request->$img;
              
                    $fileName = rand(111,99999).'.'.$extension;
                    $image_tmp->move('uploads/joboffer', $fileName);
            ++$counter;
            }else{
              $fileName = null;
            
            }
         
            $files[] = [
             'joboffer_comment_cycle_id'=>$joboffer_comment_cycle->id,
             'path'=>$fileName,
            ];
            
            }
        
      }
     
           
            $chunkfille = array_chunk($files, 3);
            if(!empty($chunkfille)){
                foreach($chunkfille as $chunk){
                    joboffer_cycle_attachment::insert($chunk);
                    
                   }
            }
        }
    }
         

    

    
    }

    

    public function json(request $request){
       
        $joboffer = auth()->user()->role->joboffer_cycle()->orderBy('created_at','DESC')->whereHas('joboffer',function($q)use($request){


        if($request->date){

            $joboffer = $joboffer->where('date','>=',$request->date);

        }


        if($request->subject){

            $joboffer = $joboffer->where('subject', 'LIKE', '%' . $request->subject . '%');

        }

        if($request->work_place){

            $joboffer = $joboffer->where('work_place',$request->work_place);

        }

        if($request->ref){
            $joboffer = $joboffer->where('ref','>=',$request->ref);
        }

    });

$joboffer = $joboffer->with('joboffer');

$joboffer = $joboffer->paginate(10);

return response()->json(['data'=>$joboffer]);

    }


    
public function preview($joboffer){
    if(is_numeric($joboffer)){
    
        $data = joboffer::where('id',$joboffer)->with(['condition','benefits'])->first();
    
        if($data){
    
    return view('managers.joboffer.preview')->with(['data'=>$data]);
    
        }
    
    }


}


public function update($joboffer){
  
    if(is_numeric($joboffer)){
    
        $data = joboffer::where('id',$joboffer)->with(['joboffer_cycle'=>function($q){
            return  $q
            ->with(['joboffer_comment_cycle'=> function($qu){
                return $qu->with('files');
       }])->with('role');
            }])->with(['condition','benefits','files'])->first();
    
        if($data){
    
    return view('managers.joboffer.update')->with(['data'=>$data]);
    
        }
    
    }


}

}
