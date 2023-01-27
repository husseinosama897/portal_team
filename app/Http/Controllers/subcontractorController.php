<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\subcontractor;
use App\subcontractor_attr;
use Illuminate\Support\Facades\Validator;
use DB;
use App\workflow;
use App\notification;
use Carbon\Carbon;
use App\subcontractor_request_cycle;
use App\contract_withsubcontractor;
use App\Jobs\rolecc;
use App\Jobs\sendcc;

use App\Exceptions\CustomException;
use App\Events\NotificationEvent;

class subcontractorController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    
    public function presubcontractorreturn(request  $request){
   
    
        return view('subcontractor.previewdef');
            
             
            
        
           }


           public function Archives(){
            return view('subcontractor.Archives');

           }


           
    public function updatepage( $subcontractor){
     
        if (is_numeric($subcontractor)    ){

            $data = subcontractor::where('id',$subcontractor)->with(['subcontractor'=>function($q){
                return  $q
                ->with(['comment_subcontractor_cycle'=> function($qu){
                    return $qu->with('attachment_subcontractor_cycle');
                }])->with('role');
                }])->with(['attributes'])->with(['contract_withsubcontractor'=>function($q){
                    return      $q->with(['invoice'=>function($q){
                        return $q->where('status',1);
                      }]);
                }])->with('files')->first();

            if(!empty($data) && $data->status == 2){
        return view('subcontractor.update')->with('data',$data);
            }
        }
    }

    

           
    public function updating(request $request,subcontractor $subcontractor){
     // if($subcontractor->status == 2 ){

      
        $data =  $this->validate($request,[
           'project_id'=>['required','numeric'],
           'date'=>['required','date','max:255'],
        'subject'=>['required','string','max:255'],
       
           'to'=>['string','max:255'],
       //    'total'=>['required','numeric','digits_between:1,99999999'],
         
            ]);
            try{
        
                DB::transaction(function () use ($request,$data,$subcontractor) {
       
            $subcontractor->update([
                'project_id'=>$request->project_id,
        
             
                'status'=>0,
        
              'total'=> ( $request->total + $request->vat ) ?? 0 ,
              
              'ref'=>$request->ref,
    
              'vat'=>$request->vat ?? 0,
                
               'date'=>$request->date,
            
                'subject'=>$request->subject,
            
                'to'=>$request->to,
            
            ]);
        
            if($request->deletedfiles){
                subcontractor_file::find($request->deletedfiles)->delete();
             }
            
         
    
            $subcontractor_cycle =  $subcontractor->subcontractor()->orderBy('id', 'DESC')->first();
           
            $subcontractor_cycle->update(['status'=>0]);

           

            $perv = workflow::where(['name'=>'subcontractor'])->first()->flowworkStep()->where(['step'=> $subcontractor_cycle->step])
            ->first();
        
          
        
             //

                 
                
foreach( $perv->role->user as $flow){

    notification::create([

        'type'=>3,
        'read'=>1,
        'name'=>'subcontractor request has been modified',
      'user_id_to'=>$flow->id,
         'user_id_from'=>auth()->user()->id,
         
    ]);
    $user = $flow;
    $content = 'subcontractor request has been modified';
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
                            
                    $image_tmp->move('uploads/subcontractor', $fileName);
           
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
                    subcontractor_attachment::insert($chunk);
                  }
                     }
                     
           }

       


           $rules = [
              
           
            "qty"  => "required|numeric",
        
           
            'name'=> "required|string",
           'unit'=> "string|max:255",
           
           'unit_price'=> "required|numeric",
           
        ];
    
        $attributes = json_decode($request->attr, true);
       
    foreach($subcontractor->attributes as $att){
        $att->delete();
    }

    foreach($attributes as $attr){
     
        $validator = Validator::make($attr,
    
            $rules
    
        );
        
        
        if ($validator->passes()) {
            subcontractor_attr::insert([
                'name'=>$attr['name'],
                'qty'=>$attr['qty'],
                 'unit'=>$attr['unit'],
                  'unit_price'=>$attr['unit_price'],
                  'currentqty'=>$attr['currentqty'],
                  'previous_qty'=>$attr['previous_qty'],
                  'expctedqty'=>$attr['expctedqty'],
                  'currentqty'=>$attr['currentqty'],
                 'total'=> $attr['total'] ?? 0,
                  'Excution_percentage'=>$attr['Excution_percentage'],
             'subcontractor_id'=>$subcontractor->id,
            ]);
        }else{
         
            $errors  = $validator->errors()->toArray();
            $data = json_encode($errors);
          
                    throw new CustomException ($data);
    
        }
    }

             
    
        });
        
            }
            catch (Exception $e) {
                return $e;
            }
        //  }
        }


           public function jsonArchives(){
            $data = auth()->user()->subcontractor()->orderBy('created_at','DESC')
            ->with(['subcontractor'=>function($q){
              return   $q->with('role');
             }])->paginate(10);
            return response()->json(['data'=>$data]);

           }


public function index(contract_withsubcontractor $contract_withsubcontractor){

    if(is_numeric($contract_withsubcontractor->id) && $contract_withsubcontractor->status == 1 ){

    return view('subcontractor.index')->with(['id'=>$contract_withsubcontractor->id]);
    }
}

public function create(contract_withsubcontractor $contract_withsubcontractor){
    if(is_numeric($contract_withsubcontractor->id) && $contract_withsubcontractor->status == 1 ){

        $data = subcontractor::latest()->select('id','ref')->first();
        $explode = explode("-",$data->ref);

if(!empty($contract_withsubcontractor->invoice)){
    $invoices = $contract_withsubcontractor->invoice()->where('status',1)->get() ;
    $invoice_num = $contract_withsubcontractor->invoice()->count() + 1;
}else{
    $invoices =null;
    $invoice_num = 1;
}
       
    
       
$explode2 = explode("-",$contract_withsubcontractor->ref);

$explode2[1] = $explode2[1];
$num = 'C-'.''.$explode2[1] .'/'. $invoice_num;
       
    $attributes = $contract_withsubcontractor->attributes;

 
   
        return view('subcontractor.create')->with(['ref'=>'SI-'.''.$explode[1]+1,'num'=>$num,'contract_total'=>$contract_withsubcontractor->total,'id'=>$contract_withsubcontractor->id,'project_id'=>$contract_withsubcontractor->project_id,'invoice'=>$invoices,'attributes'=>$attributes]);

    }
 
}

  public function subcontractorinsrting(request $request,$contract_withsubcontractor){
  
$data =  $this->validate($request,[
   'project_id'=>['required','numeric'],
 
   
   'date'=>['required','date','max:255'],
'subject'=>['required','string','max:255'],
'total'=>['required','numeric'],
   'to'=>['string','max:255'],
   
    ]);
    try{

        DB::transaction(function () use ($request,$data,$contract_withsubcontractor) {


            
    $subcon = subcontractor::create([
        'project_id'=>$request->project_id,

        'user_id'=>auth()->user()->id,
    'ref'=>$request->ref,
    'cost_center_id'=>$request->cost_center_id,
        'status'=>0,
'contract_withsubcontractor_id'=>$contract_withsubcontractor,
        'total'=>($request->total + $request->vat),
        
      'vat'=>$request->vat,
        
       'date'=>$request->date,
    
        'subject'=>$request->subject,
    
        'to'=>$request->to,
'contract_no'=>$request->contract_on,
'invoice_ON'=>$request->invoice_ON,
//'payment_no'=>$request->payment_no,

    ]);

 
    

    $rules = [
        "qty"  => "required|numeric",
        'name'=> "required|string",
       'unit'=> "string|max:255",  
       'unit_price'=> "required|numeric",
    ];

    $attributes = json_decode($request->attr, true);
    $users = json_decode($request->users, true);

foreach($attributes as $attr){
 
    $validator = Validator::make($attr,

        $rules

     );
    
    
    if ($validator->passes()) {
        subcontractor_attr::insert([
        'name'=>$attr['name'],
          'qty'=>$attr['qty'],
           'unit'=>$attr['unit'],
            'unit_price'=>$attr['unit_price'],
            'currentqty'=>$attr['currentqty'],
            'previous_qty'=>$attr['previous_qty'],
            'expctedqty'=>$attr['expctedqty'],
            'currentqty'=>$attr['currentqty'],
           'total'=> $attr['total'] ?? 0,
            'Excution_percentage'=>$attr['Excution_percentage'],
         'subcontractor_id'=>$subcon->id,
            
        ]);
    }else{
     
        $errors  = $validator->errors()->toArray();
        $data = json_encode($errors);
      
                throw new CustomException ($data);

    }
}
$rules = [
          
       
    'id' => 'required|exists:users,id',

];
if(!empty($users)){
    
    $content   = 'user name:'.' '.auth()->user()->name  ?? ''.'Project Name:'.' '.$subcon->project->name ?? ''.'has been created:' .' '. $subcon->ref . 'is waiting for review';
    foreach($users as $user){
 
        $validator = Validator::make($user,[
    
            $rules
    
        ] );
        if ($validator->passes()) {
        $subcon->mention()->attach([
    $user['id']
        ]);
              
        $managercontent = '';

        $job = (new sendcc($user,$content,$managercontent))->delay(Carbon::now()->addSeconds(90));
        $this->dispatch($job);
       NotificationEvent::dispatch($user->id,$content);
    }
    else{
        /*
        $errors  = $validator->errors()->toArray();
        $data = json_encode($errors);
      
                throw new CustomException ($data);  
                */
    }
    }
}


$workflow = workflow::where('name','subcontractor')->first()->flowworkStep()
->first();



foreach( $workflow->role->user as $flow){

    notification::create([

        'type'=>8,
        'read'=>1,
        'name'=>'New Subcontractor Request',
      'user_id_to'=>$flow->id,
         'user_id_from'=>auth()->user()->id,
         
    ]);

    $user = $flow;
    $content = 'New Subcontractor Request';

    $managercontent = '';
    $job = (new rolecc($user,$content,$managercontent))->delay(Carbon::now()->addSeconds(90));
   $this->dispatch($job);
   NotificationEvent::dispatch($user->id,$content);

 }


subcontractor_request_cycle::insert([
 'step'=>1,
 'status'=>0,
 'flowwork_step_id'=>$workflow->id,
 'role_id'=>$workflow->role_id,
 'subcontractor_id'=>$subcon->id
]);

});

    }
    catch (Exception $e) {
        return $e;
    }
  }

  public function subcontractorreturn( $subcontractor){
    if (is_numeric($subcontractor) ){
        
        $data = subcontractor::where('id',$subcontractor)->with(['attributes'])->with(['subcontractor'=>function($q){
            return  $q
            ->with(['comment_subcontractor_cycle'=> function($qu){
                return $qu->with('user');
            }])->with('role');
            }])->with('project')->first();

    if(!empty($data)){
    return view('subcontractor.preview')->with(['data'=>$data]);
    }
    }
     }
  
  

   

     public function returnasjson(contract_withsubcontractor $contract_withsubcontractor){
        if(is_numeric($contract_withsubcontractor->id) && $contract_withsubcontractor->status == 1 ){
      $data = auth()->user()->subcontractor()->where('contract_withsubcontractor_id',$contract_withsubcontractor->id)->orderBy('created_at','DESC')
      ->with(['subcontractor'=>function($q){
        return   $q->with('role');
       }])->paginate(10);
      return response()->json(['data'=>$data]);
    }

     }
  
     public function delete(subcontractor $subcontractor){
         if($subcontractor->user_id == auth()->user()->id){
            $subcontractor->delete();
         }
       
     }

}
