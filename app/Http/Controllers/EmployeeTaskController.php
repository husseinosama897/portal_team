<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\taskEmpAttachments;
use App\task;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Jobs\sendcc;
use App\Events\NotificationEvent;
class EmployeeTaskController extends Controller
{


    public function editTask(task $task){
        if($task->user()->where('task_id',$task->id)->count() > 0){

            $attachment_task_manager = $task->attachment_task_manager;
$taskEmpAttachments = $task->taskEmpAttachments;

            return view('task.update')->with(['data'=>$task]);
        
        }
       
        }


        public function mission_completed(task $task,request $request){

            $task->update([
                'status'=>1,
                'note'=>$request->note
            ]);

            $content   = 'Task :'.' '.$task->name .' has  been successfully' ;

            $job = (new sendcc($task->manager,$content,''))->delay(Carbon::now()->addSeconds(90));
            $this->dispatch($job);
            NotificationEvent::dispatch($task->manager->id,$content);

            $files = [];
            if($request->count > 0){
             for($counter = 0;  $counter <= $request->count;  $counter++){
              
                 $img = 'files-'.$counter;
                 
                   if($request->$img){
                     $image_tmp = $request->$img;
                     $fileName = Str::random(40).'.'.$image_tmp->getClientOriginalExtension();
               
                     $extension = $image_tmp->getClientOriginalExtension();
                             
                     $image_tmp->move('uploads/taskEmpAttachments', $fileName);
            

                     dd($request->$img);
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
                    taskEmpAttachments::insert($chunk);
                   }
                      }
                      
            }
        }



public function index(){
  
    return view('task.index');
}



public function JsonTaskData(request $request){

    $task = auth()->user()->task();

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



}
