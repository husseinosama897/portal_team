<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\subcontractor;
use App\subcontractor_inv;
use App\subcontractor_attr;
use Illuminate\Support\Facades\Validator;
use DB;
use App\workflow;
use Illuminate\Support\Str;
use App\project_overall;
use App\flowworkStep;
use Carbon\Carbon;
use App\subcontractor_request_cycle;
use App\comment_subcontractor_cycle;
use Illuminate\Support\Facades\Storage;
use App\attachment_subcontractor_cycle;
use App\Jobs\sendcc;
use App\notification;
use App\report;
use App\Exceptions\CustomException;
use App\Events\NotificationEvent;
class subcontractorManagerController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function inv(request $request,subcontractor $subcontractor){

        $subcontractor->update([
          'closed'=>1,
        ]);
        
          if($request->count > 0){
            for($counter = 0;  $counter <= $request->count;  $counter++){
             
                $img = 'files-'.$counter;
                
                  if($request->$img){
                    $image_tmp = $request->$img;
                    $fileName = Str::random(40).'.'.$image_tmp->getClientOriginalExtension();
              
                    $extension = $image_tmp->getClientOriginalExtension();
                            
                    $image_tmp->move('uploads/subcontractor/inv', $fileName);
           
              $files[] = [
                           'subcontractor_id'=>$subcontractor->id,
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
                    subcontractor_inv::insert($chunk);
                  }
              }
                     
           }
        
        }

    public function index(){
        return view('managers.subcontractor.index');
    }
    
    public function update( $subcontractor){
     
        if (is_numeric($subcontractor)){
            $data = subcontractor::where('id',$subcontractor)->with(['subcontractor'=>function($q){
                return  $q
                ->with(['comment_subcontractor_cycle'=> function($qu){
                    return $qu->with('attachment_subcontractor_cycle');
                }])->with('role');
                }])->with(['attributes'])->with(['contract_withsubcontractor'=>function($q){
              return      $q->with(['invoice'=>function($q){
                return $q->where('status',1);
              }]);
                }])->first();
                
           
            if(!empty($data)){
        return view('managers.subcontractor.update')->with('data',$data);
            }
        }
    }
    
      public function action(request $request,subcontractor $subcontractor){
      
    $data =  $this->validate($request,[
       'project_id'=>['required','numeric'],
       'date'=>['required','date','max:255'],
    'subject'=>['required','string','max:255'],
    'status'=>['required','numeric','between:1,2'],
       'to'=>['string','max:255'],
       
        ]);
        try{
    
            DB::transaction(function () use ($request,$data,$subcontractor) {
    
        $subcontractor->update([
            'project_id'=>$request->project_id,
    
         'ref'=>$request->ref,
            'status'=>0,
    
         
          'contract_no'=>$request->contract_no,
         
            
           'date'=>$request->date,
        
            'subject'=>$request->subject,
        
            'to'=>$request->to,
            'invoice_ON'=>$request->invoice_ON,
       //     'payment_no'=>$request->payment_no,
            
        ]);
    
     

        $subcontractor_request_cycle =  $subcontractor->subcontractor()->orderBy('id', 'DESC')->first();
      

        

        if($subcontractor_request_cycle->status == 0){
        $subcontractor_request_cycle->status = $request->status;
        $subcontractor_request_cycle ->save();
       
        $perv = workflow::where(['name'=>'subcontractor'])->first()->flowworkStep()->where(['step'=> $subcontractor_request_cycle->step])
        ->first();
    

        if($request->status == 1){
    
            $workflow = workflow::where(['name'=>'subcontractor'])->first()->flowworkStep()->where(['step'=> $subcontractor_request_cycle->step+1])
            ->first();
        


            $content   = 'Your subcontractor' .$subcontractor->ref.'has been approved by'.$perv->role->name ?? ''.' and Under Review from '.$workflow->role->name ?? 'no one';
if(!empty($workflow->role)){
    foreach($workflow->role->user as $user){
       
        $job = (new sendcc( $user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
        $this->dispatch($job);
        NotificationEvent::dispatch($user->id,$content);
       }
}
           


$job = (new sendcc($subcontractor->user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
$this->dispatch($job);
NotificationEvent::dispatch($subcontractor->user->id,$content);
            notification::create([
                'type'=>8,
                'read'=>1,
                'name'=>$content,
              'user_id_to'=>$subcontractor->user_id,
                 'user_id_from'=>auth()->user()->id,  
            ]);


            


            if(!empty($workflow)){
                subcontractor_request_cycle::insert([
                    'step'=>$subcontractor_request_cycle->step + 1,
                    'status'=>0,
                    'flowwork_step_id'=>$workflow->id,
                    'role_id'=>$workflow->role_id,
                    'subcontractor_id'=>$subcontractor->id
                ]);
        
            }else{
                $subcontractor->update([
'status'=>1,
                ]);
              
                // -------------------------  project expenses -------------------------------------

              $subcontractor->project->subcontractor_expenses !== null ?   $subcontractor->project->increment('subcontractor_expenses',$subcontractor->total)

              : $subcontractor->project->update(['subcontractor_expenses'=>$subcontractor->total]);
//-------------------------------------------- general report ----------------------------------------

                
                $report =   report::where('date',$subcontractor->date)->increment('total_cash_out',$subcontractor->total);

                if(empty($report)){
                 report::create([
                   'date'=>$subcontractor->date,
                   'total_cash_out'=>$subcontractor->total,
                 ]);
                }  
// -------------------------------------------------------------------------------------------------------


//---------------------------------- project report -------------------------------------------------

$project_overall = project_overall::where(['date'=>Carbon::now()->startOfMonth(),'project_id'=>$subcontractor->project_id])->first();

  if($project_overall){

          $project_overall->increment('cash_out',$subcontractor->total);
    
  }else{
 
        project_overall::create([
            'date'=>Carbon::now()->startOfMonth(),
            'percentage_performance'=>0,
            'cash_out'=>$subcontractor->total,
            'percentage_attendance'=>0,
            'cash_in'=>0,
            'num_of_performers'=>0,
            'num_of_attendance'=>0,
            'performance_point'=>0,
            'time_attendance'=>0,
            'project_id'=>$subcontractor->project_id
        ]);
    
    
    

  
  
  }
  //------------------------------------------------------------------------------------------

 $data =   \App\attributes_contract::where('contract_withsubcontractor_id',$subcontractor->contract_withsubcontractor_id)->get();

foreach($data as $dat){
    $dat->delete();
    
}


$scaling = [];


foreach($subcontractor->attributes as $attr){

$scaling [] = [
  'contract_withsubcontractor_id'=>$subcontractor->contract_withsubcontractor_id,
  'name'=>$attr['name'],
  'unit'=>$attr['unit'],
  'unit_price'=>$attr['unit_price'],
  'expctedqty'=>$attr['expctedqty'],
   'Excution_percentage'=>$attr['Excution_percentage'],
 'previous_qty'=>($attr['previous_qty'] + $attr['currentqty']),
];




}

$array_chunk = array_chunk($scaling,10);
foreach($array_chunk as $chunk){
    \App\attributes_contract::insert(
        $chunk
    );
}


            }
        }elseif($request->status == 2){

            $subcontractor->update([
                'status'=>2,
                                ]);



                                $content   = 'Your subcontractor request' .$subcontractor->ref.'has been rejected by'.$perv->role->name ;

                                


  
        $job = (new sendcc($subcontractor->user,$content,$request->contentmanager))->delay(Carbon::now()->addSeconds(90));
        $this->dispatch($job);
        NotificationEvent::dispatch($subcontractor->user->id,$content);


                                notification::create([
                                    'type'=>8,
                                    'read'=>1,
                                    'name'=>$content,
                                  'user_id_to'=>$subcontractor->user_id,
                                     'user_id_from'=>auth()->user()->id,  
                                ]);         

        }
    
   
            $comment_subcontractor_cycle = comment_subcontractor_cycle::create([
                'subcontractor_request_cycle_id'=>$subcontractor_request_cycle->id,
                'content'=>$request->contentmanager ?? 'No Comment',
                'user_id'=>auth()->user()->id,
            ]);
            
        

        $files = [];
      if(!empty($comment_subcontractor_cycle)){
          if($request->count > 0){
        for($counter = 0;  $counter <= $request->count;  $counter++){
        
            $img = 'files-'.$counter;
            
              if($request->$img){
                $image_tmp = $request->$img;
                
                         
                $fileName = 'subcontractor'.'_'.'code_'.'' .$subcontractor->id. Carbon::now().'_step_'.$subcontractor_request_cycle->step;
                Storage::disk('google')->put($fileName
                 ,file_get_contents($image_tmp));

                    $extension = $image_tmp->getClientOriginalExtension();
                    $fileName = rand(111,99999).'.'.$extension;
                    $image_tmp->move('uploads/subcontractor', $fileName);
            ++$counter;
            }else{
              $fileName = null;
            
            }
         
            $files[] = [
             'comment_subcontractor_cycle_id'=>$comment_subcontractor_cycle->id,
             'path'=>$fileName,
            ];
            
            }
      }
     
           
            $chunkfille = array_chunk($files, 3);
            if(!empty($chunkfille)){
                foreach($chunkfille as $chunk){
                    attachment_subcontractor_cycle::insert($chunk);
                    
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
    
      public function subcontractorreturn( $subcontractor){
        if (is_numeric($subcontractor)){
        $data = subcontractor::where('id',$subcontractor)->with(['attributes'])->with(['subcontractor'=>function($q){
            return  $q
            ->with(['comment_subcontractor_cycle'=> function($qu){
                return $qu->with('user');
            }])->with('role');
            }])->with('project')->first();
        if(!empty($data)){
        return view('managers.subcontractor.preview')->with(['data'=>$data]);
        }
        }
         }
      
      
    
       
    
         public function returnasjson(){
          $purchase = auth()->user()->role->subcontractor()
         ->with(['subcontractor_real'=>function($q){
          $q->with(['contract_withsubcontractor'=>function($query){
            $query->select(['id','contractor_id'])->with(['contractor'=>function($contractor){
              $contractor->select(['contractor_name','id']);
            }]);
          }]);
        }]) ->paginate(10);
          return response()->json(['data'=>$purchase]);
         }
      
         public function delete(subcontractor $subcontractor){
             if($subcontractor->user_id == auth()->user()->id){
                $subcontractor->delete();
             }
           
         }
    
    }
    