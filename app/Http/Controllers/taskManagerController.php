<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\task;
use App\User;
use App\attachment_task_manager;
use Illuminate\Support\Str;
use App\Jobs\sendcc;
use Carbon\Carbon;
use Validator;
use App\Jobs\rolecc;
use App\Events\NotificationEvent;
class taskManagerController extends Controller
{

public function editTask(task $task){
$user = $task->user;
$attachment_task_manager = $task->attachment_task_manager;
$taskEmpAttachments = $task->taskEmpAttachments;
return view('managers.task.update')->with(['data'=>$task]);

}

    public function create(){

       return view('managers.task.create');

    }

    public function UpdatePostTask(task $task ,request $request  ){

        $this->validate($request,[
'name'=>['required','string','max:255'],
'dis'=>['required','string'],
'start_at'=>['required','date'],
'expires_in'=>['required','date'],

        ]);

    $task->update([
            'name'=>$request->name,
'dis'=>$request->dis,

'start_at'=>$request->start_at,
'status'=>$request->status,
'expires_in'=>$request->expires_in,
'point'=>$request->point,
'noteManager'=>$request->noteManager
    ]);
    $users = json_decode($request->users, true);
    
    $files = [];
if($request->count > 0){
 for($counter = 0;  $counter <= $request->count;  $counter++){
  
     $img = 'files-'.$counter;
     
       if($request->$img){
         $image_tmp = $request->$img;
         $fileName = Str::random(40).'.'.$image_tmp->getClientOriginalExtension();
   
         $extension = $image_tmp->getClientOriginalExtension();
                 
         $image_tmp->move('uploads/attachment_task_manager', $fileName);

   $files[] = [
                'task_id'=>$task->id,
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
        attachment_task_manager::insert($chunk);
       }
          }
          
}
$content   = 'Task Name:'.' '.$task->name .'got updated you should to check up' ;
if(!empty($users)){
    $rules = [
              
           
        'id' => 'required|exists:users,id',
    
    ];

    $newData = [];
    foreach($users as $user){
      
        $newData [] = 
         $user['id'];
        
        
       
        $managercontent = '';
       
        $job = (new sendcc($user,$content , $managercontent))->delay(Carbon::now()->addSeconds(90));
        $this->dispatch($job);
        NotificationEvent::dispatch($user->id,$content);
   
   
                
    
    }

  
    $task->user()->sync($newData);


    
}



    }

    public function PostTask(request $request  ){

        $this->validate($request,[
'name'=>['required','string','max:255'],
'dis'=>['required','string'],
'start_at'=>['required','date'],
'expires_in'=>['required','date'],

        ]);

    $task =     task::create([
            'name'=>$request->name,
'dis'=>$request->dis,
'user_id'=>auth()->user()->id,
'start_at'=>$request->start_at,
'expires_in'=>$request->expires_in,
'point'=>$request->point
    ]);
    $users = json_decode($request->users, true);
    
    $files = [];
if($request->count > 0){
 for($counter = 0;  $counter <= $request->count;  $counter++){
  
     $img = 'files-'.$counter;
     
       if($request->$img){
         $image_tmp = $request->$img;
         $fileName = Str::random(40).'.'.$image_tmp->getClientOriginalExtension();
   
         $extension = $image_tmp->getClientOriginalExtension();
                 
         $image_tmp->move('uploads/attachment_task_manager', $fileName);

   $files[] = [
                'task_id'=>$task->id,
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
        attachment_task_manager::insert($chunk);
       }
          }
          
}
$content   = 'Task Name:'.' '.$task->name ;
if(!empty($users)){
    $rules = [
              
           
        'id' => 'required|exists:users,id',
    
    ];

    foreach($users as $user){
        $validator = Validator::make($user,
            $rules
         );
        if ($validator->passes()) {
        $task->user()->attach([
    $user['id']
        ]);
        $managercontent = '';
       
        $job = (new sendcc($user,$content ,$managercontent))->delay(Carbon::now()->addSeconds(90));
        $this->dispatch($job);
        NotificationEvent::dispatch($user->id,$content);
    }
    else{
   
                
    }
    }
    
}



    }

    public function index(){
        return view('managers.task.index');
    }


    public function JsonTaskData(request $request){

        $task = task::query();

        if($request->name){
            $task = $task->where('name',$request->name);
        }

        if($request->start_at){
            $task = $task->where('start_at',$request->start_at);
        }

        if($request->expires_in){
            $task = $task->where('expires_in',$request->expires_in);
        }

        if($request->point){
            $task = $task->where('point',$request->point);
        }

        
        if($request->dis){
            $task = $task->where('dis',$request->dis);
        }

        $task = $task->orderBy('created_at','DESC')->paginate(10);

return response()->json(['data'=>$task]);

    }

public function delete(task  $task ){
    $task->delete();
}
}
