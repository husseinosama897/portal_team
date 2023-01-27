<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\joboffer;
use Str;
use App\joboffer_attachment;
use App\Benefit_salary;
use App\Condition;
use App\notification;
use App\Jobs\sendcc;
use App\workflow;
use Carbon\Carbon;
use App\Jobs\rolecc;
use App\joboffer_cycle;
use App\Events\NotificationEvent;

class JobOfferController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }



    public function preview2(request  $request){
   
        return view('joboffer.previewdef');
            
        
        
           }



    public function index(){

        $jobofferworkflow =    workflow::where('name','joboffer')->with(['flowworkStep'=>function($q){
            return     $q->with('role');
             }])->first();

             
        return view('joboffer.index')->with('workflow',$jobofferworkflow);
    }


public function json(){

   $data =  auth()->user()->joboffer()->paginate(10);

   return response()->json(['data'=>$data]);


}


public function create(){

    $data = joboffer::latest()->first();
 
        $explode = explode("-",$data->ref ?? 'Jo-'.''.'0');

    return view('joboffer.create')->with(['ref'=>'Jo-'.''.$explode[1] + 1]);
}


public function insert(request $request){

$this->validate($request,[
    'date'=>['date','required'],
 'subject'=>['required','max:255','string'],
 'content'=>['required','string'],
'contract_type'=>['string'],
   'work_location'=>['string'],
   'salary'=>['string','numeric'],
   'email'=>['email'],
   'name'=>['required','string','max:255']
]);

    $benefit_check = $request->benefit_check == true ?  1 : 0;

    $joboffer = joboffer::create([
   'date'=>$request->date,
   'ref'=>$request->ref,
   'name'=>$request->name,
   'email'=>$request->email,
   'benefit_check'=>$benefit_check,
 'subject'=>$request->subject,
 'content'=>$request->content,
'contract_type'=>$request->contract_type,
'user_id'=>auth()->user()->id,
   'work_location'=>$request->work_location,
   'salary'=>$request->salary,
    ]);



$benefit_salary  = json_decode($request->benefit_salary,true);
$condition = json_decode($request->condition,true);

$benefit_salary_scailing = [];
$condition_scailing = [];

if($request->count > 0){
    for($counter = 0;  $counter <= $request->count;  $counter++){
     
        $img = 'files-'.$counter;
        
          if($request->$img){
            $image_tmp = $request->$img;
            $fileName = Str::random(40).'.'.$image_tmp->getClientOriginalExtension();
      
          
            $image_tmp->move('uploads/joboffer', $fileName);
   
      $files[] = [
                   'joboffer_id'=>$joboffer->id,
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
            joboffer_attachment::insert($chunk);
          }
             }
             
   }




# scailing salary and benefits 
if($benefit_check == 1){
    foreach($benefit_salary as $benefit)
    {
    
        $benefit_salary_scailing []= [
    'joboffer_id'=>$joboffer->id,
            'item'=>$benefit['item'],
            'value'=>$benefit['value'] ?? null,
    
        ];
    
    }
}



# chunking array 

$chunking = array_chunk($benefit_salary_scailing,10);
if($benefit_check == 1){
    foreach($chunking as $chunk){

        Benefit_salary::insert($chunk);
    
    }
}




# scailing conditions 

foreach($condition as $con){
    $condition_scailing[] = [
        'joboffer_id'=>$joboffer->id,
        'item'=>$con['item'],
    ];
}


# chunk condition array 

$array_condition_chunk = array_chunk($condition_scailing,10);

foreach($array_condition_chunk as $chunk){

    Condition::insert($chunk);

}



$workflow = workflow::where('name','joboffer')->first()->flowworkStep()
->first();

foreach( $workflow->role->user as $flow){

   notification::create([

       'type'=>4,
       'read'=>1,
       'name'=>'New joboffer Request',
     'user_id_to'=>$flow->id,
        'user_id_from'=>auth()->user()->id,  
   ]);
   $user = $flow;
   $content = 'New joboffer Request';
 $managercontent = '';
 $job = (new rolecc($user,$content,$managercontent))->delay(Carbon::now()->addSeconds(90));
$this->dispatch($job);
NotificationEvent::dispatch($user->id,$content);

}

/*
$account_sid = env('TWILIO_SID');
$account_token = env('TWILIO_TOKEN');
$number = env('TWILIO_FROM');

$client = new Client($account_sid,$account_token);
$client->messages->create('+966583850200',[
   'title'=>'hussein',
   'from'=>$number,
   'body'=>$content,
]);
*/

joboffer_cycle::insert([
   'step'=>1,
   'status'=>0,
   'flowwork_step_id'=>$workflow->id,
   'role_id'=>$workflow->role_id,
   'joboffer_id'=>$joboffer->id
 ]);
   






}

public function preview($joboffer){
if(is_numeric($joboffer)){

    $data = auth()->user()->joboffer()->where('id',$joboffer)->with(['condition','benefits'])->first();

    if($data){

return view('joboffer.preview')->with(['data'=>$data]);

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

   public function action(request $request,joboffer $joboffer){
 
    
 
    $data =  $this->validate($request,[
        'quotation'=>['string','max:255'],
       'project_id'=>['required','numeric','max:255'],
       'date'=>['required','date','max:255'],
   'subject'=>['required','string','max:255'],
  

       'ref'=>['string','max:255'],
       'to'=>['string','max:255'],
      
 
        ]);
 
 try{
 
    
     DB::transaction(function () use ($request,$joboffer,$data) {
        $benefit_check = $request->benefit_check == true ?  1 : 0;

 $joboffer->update([
    'date'=>$request->date,
    'ref'=>$request->ref,
    'name'=>$request->name,
    'email'=>$request->email,
    'benefit_check'=>$benefit_check,
  'subject'=>$request->subject,
  'content'=>$request->content,
 'contract_type'=>$request->contract_type,
 'user_id'=>auth()->user()->id,
    'work_location'=>$request->work_location,
    'salary'=>$request->salary,
 ]);


 if($request->deletedfiles){
    joboffer_attachment::find($request->deletedfiles)->delete();
 }

 


 $joboffer_cycle =  $joboffer->joboffer_cycle()->orderBy('id', 'DESC')->first();
$joboffer_cycle->update(['status'=>0]);

 $perv = workflow::where(['name'=>'joboffer'])->first()->flowworkStep()
 ->where(['step'=> $joboffer_cycle->step])
 ->first();


if(!empty($joboffer->benefits())){
    $joboffer->benefits->delete();
}
   


	if(!empty($joboffer->condition())){
        $joboffer->condition()->delete();
    }


    

$benefit_salary  = json_decode($request->benefit_salary,true);
$condition = json_decode($request->condition,true);

$benefit_salary_scailing = [];
$condition_scailing = [];

if($request->count > 0){
    for($counter = 0;  $counter <= $request->count;  $counter++){
     
        $img = 'files-'.$counter;
        
          if($request->$img){
            $image_tmp = $request->$img;
            $fileName = Str::random(40).'.'.$image_tmp->getClientOriginalExtension();
      
          
            $image_tmp->move('uploads/joboffer', $fileName);
   
      $files[] = [
                   'joboffer_id'=>$joboffer->id,
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
            joboffer_attachment::insert($chunk);
          }
             }
             
   }




# scailing salary and benefits 
if($benefit_check == 1){
    foreach($benefit_salary as $benefit)
    {
    
        $benefit_salary_scailing []= [
    'joboffer_id'=>$joboffer->id,
            'item'=>$benefit['item'],
            'value'=>$benefit['value'] ?? null,
    
        ];
    
    }
}



# chunking array 

$chunking = array_chunk($benefit_salary_scailing,10);
if($benefit_check == 1){
    foreach($chunking as $chunk){

        Benefit_salary::insert($chunk);
    
    }
}




# scailing conditions 

foreach($condition as $con){
    $condition_scailing[] = [
        'joboffer_id'=>$joboffer->id,
        'item'=>$con['item'],
    ];
}


# chunk condition array 

$array_condition_chunk = array_chunk($condition_scailing,10);

foreach($array_condition_chunk as $chunk){

    Condition::insert($chunk);

}



$workflow = workflow::where('name','joboffer')->first()->flowworkStep()
->first();

foreach( $workflow->role->user as $flow){

   notification::create([

       'type'=>4,
       'read'=>1,
       'name'=>'New joboffer request has been modified',
     'user_id_to'=>$flow->id,
        'user_id_from'=>auth()->user()->id,  
   ]);
   $user = $flow;
   $content = 'New joboffer request has been modified';
 $managercontent = '';
 $job = (new rolecc($user,$content,$managercontent))->delay(Carbon::now()->addSeconds(90));
$this->dispatch($job);
NotificationEvent::dispatch($user->id,$content);
}



});
 }
catch (Exception $e) {
    return $e;
}

}

}

