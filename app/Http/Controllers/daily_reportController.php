<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\dailyReport;
use App\daily_report_attachment;
use App\daily_productivity;
use Carbon\Carbon;
use Str;
use App\Jobs\performanceJob;
class daily_reportController extends Controller
{



public function index(){
    return view('daily_report.index');
}


public function create(){
  $data = dailyReport::latest()->first();
  $explode = explode("-",$data->ref ?? 'R-'.''.'0');

  return view('daily_report.create')->with(['ref'=>'R-'.''.$explode[1] + 1]);
}



public function delete(dailyReport $dailyReport){
$dailyReport->delete();
}

public function supervisor($project){
if(is_numeric($project)){

$data =  \App\User::select(['name','id'])->whereHas('contract',function($q) use($project){

  return $q->where('project_id',$project);

})->get()->chunk(10);


return response()->json(['data'=>$data]);

}

}


public function project_manager_daily_report(){

return view('daily_report.projectmanager');

  
}

public function json_project_manager_daily_Report(request $request){

    if(auth()->user()->projectmanager()->count() > 1){

        $this->validate($request,[
            'project_id'=>['numeric','required','exists:projects,id']
        ]);

        $dailyReport = auth()->user()->projectmanager->where('id',$request->project_id)->first();

        $dailyReport =  $dailyReport->dailyReport();
    }else{
        $dailyReport = auth()->user()->projectmanager()->first();
        $dailyReport = $dailyReport->dailyReport();
    }
    

  if($request->from){
      $dailyReport = $dailyReport->where('date','>=',$request->from);
  }
  if($request->to){
      $dailyReport = $dailyReport->where('date','=<',$request->to);
  }
  
  if($request->supervisor_id){
      $dailyReport = $dailyReport->where('supervisor_id',$request->supervisor_id);
  }
  
  $dailyReport = $dailyReport->with(['supervisor','project',])->paginate(10);
  
  return response()->json(['data'=>$dailyReport]);
  

  }
  

public function json_daily_Report(request $request){

    $dailyReport = auth()->user()->daily_Report();
  
  
  if($request->project_id){
      $dailyReport = $dailyReport->where('project_id',$request->project_id);
  }
  
  if($request->from){
      $dailyReport = $dailyReport->where('date','>=',$request->from);
  }
  if($request->to){
      $dailyReport = $dailyReport->where('date','=<',$request->to);
  }
  
  if($request->supervisor_id){
      $dailyReport = $dailyReport->where('supervisor_id',$request->supervisor_id);
  }
  
  $dailyReport = $dailyReport->with(['supervisor','project',])->paginate(10);
  
  return response()->json(['data'=>$dailyReport]);
  
  
  }
  
  

public function inserting(request $request){

$this->validate($request,[
    'project_id'=>['numeric'],
    'date'=>['required','date'],
    'The_scope_of_work'=>['required','string'],
'workplace'=>['required','string'],
  'number_of_staff'=>['required','numeric'],
]);


$dailyReport = dailyReport::create([
    'project_id'=>$request->project_id,
    'date'=>$request->date,
    'ref'=>$request->ref,
    'The_scope_of_work'=>$request->The_scope_of_work,
    'supervisor_id'=>auth()->user()->id,
'workplace'=>$request->workplace,
'note'=>$request->note,
'status'=>0,
  'number_of_staff'=>$request->number_of_staff,
]);
$content = 'New Employee Request';
$managercontent = '';
$job = (new rolecc($dailyReport->project->user,$content,$managercontent))->delay(Carbon::now()->addSeconds(90));
$this->dispatch($job);
 
$data =  json_decode($request->daily_productivities,true);

$users = json_decode($request->users,true);


$scaling = [];

foreach($data as $dat){
$scaling[] = [
    'daily_report_id'=>$dailyReport->id,
    'item'=>$dat['item'],
    'quantity'=>$dat['quantity'],
    'unit'=>$dat['unit'],
];



}

$array_chunk = array_chunk($scaling,10);

foreach($array_chunk as $array){
    daily_productivity::insert($array);
}


             // users scailing data  

             $users_scailing_data = [];
$user_performance = [];

foreach($users as $user){
  $users_scailing_data[] = [
    'user_id'=>$user['id'],
    'performance'=>$user['performance'],
    'commitment'=>$user['commitment'],
    'daily_report_id'=>$dailyReport->id,
    
  ];

 $jobuser =   $user['id'];
 $jobperformance=   $user['performance'];
  

  $job = (new performanceJob($jobuser,$jobperformance,$request->project_id,$request->section_id))->delay(Carbon::now()->addSeconds(90));
  $this->dispatch($job);

 }

 

// then we gonna cunking our data to be easier in storing 
 $array_chunk_users  =  array_chunk($users_scailing_data,100);



 foreach($array_chunk_users as $user){
  \DB::table('user_daily_report')->insert($user);
 }



if($request->count > 0){
    for($counter = 0;  $counter <= $request->count;  $counter++){
     
        $img = 'files-'.$counter;
        
          if($request->$img){
            $image_tmp = $request->$img;
            $fileName = Str::random(40).'.'.$image_tmp->getClientOriginalExtension();
      
            $extension = $image_tmp->getClientOriginalExtension();
                    
            $image_tmp->move('uploads/dailyReport', $fileName);
   
      $files[] = [
                   'daily_report_id'=>$dailyReport->id,
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
            daily_report_attachment::insert($chunk);
          }
             }


         
   }



}


public function edit(dailyReport $dailyReport){
    if(!empty(auth()->user()->projectmanager())){
$daily_productivity = $dailyReport->daily_productivity;
$attachments = $dailyReport->attachments;
$users = $dailyReport->users;
  
  return view('daily_report.update')->with('data',$dailyReport);
    }
}

public function updating(request $request,dailyReport $dailyReport){
  if(!empty(auth()->user()->projectmanager)){
  $this->validate($request,[
      'project_id'=>['required','numeric'],
      'date'=>['required','date'],
      'The_scope_of_work'=>['required','string'],
  'workplace'=>['required','string'],
    'number_of_staff'=>['required','numeric'],
  ]);
  
  
  $dailyReport->update([
      'project_id'=>$request->project_id,
      'date'=>$request->date,
      'ref'=>$request->ref,
      'The_scope_of_work'=>$request->The_scope_of_work,
  'workplace'=>$request->workplace,
  'note'=>$request->note,
  'status'=>$request->status,
    'number_of_staff'=>$request->number_of_staff,
  'contentmanager'=>$request->contentmanager,
  ]);

  /*////////////////////////////////////////////////////////////////////////
                                                                              /
                                                                              /
                                                                                                                                                          / 
  *                                                                          /
                                 
  *
  *
  * we gonna delete all belongs to many users 
  *
  *
  *
  *//////////////////////////////////////////////////////////////////////////

  $dailyReport->users()->detach();
  
  /*////////////////////////////////////////////////////////////////////////
  *
  *
  *
  * we gonna delete daily productivity thats
   table Includes tasks and what already implemented
  
  *
  *
  *
  *//////////////////////////////////////////////////////////////////////////

  daily_productivity::where('daily_report_id',$dailyReport->id)->delete();
   

  $data =  json_decode($request->daily_productivities,true);

  $users =  json_decode($request->users,true);
  
  $scaling = [];
  
  foreach($data as $dat){
  $scaling[] = [
      'daily_report_id'=>$dailyReport->id,
      'item'=>$dat['item'],
      'quantity'=>$dat['quantity'],
      'unit'=>$dat['unit'],
  ];
  
  
  
  }
  
  $array_chunk = array_chunk($scaling,10);
  
  foreach($array_chunk as $array){
      daily_productivity::insert($array);
  }
  
  
  if($request->count > 0){
      for($counter = 0;  $counter <= $request->count;  $counter++){
       
          $img = 'files-'.$counter;
          
            if($request->$img){
              $image_tmp = $request->$img;
              $fileName = Str::random(40).'.'.$image_tmp->getClientOriginalExtension();
        
              $extension = $image_tmp->getClientOriginalExtension();
                      
              $image_tmp->move('uploads/dailyReport', $fileName);
     
        $files[] = [
                     'daily_report_id'=>$dailyReport->id,
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
              daily_report_attachment::insert($chunk);
            }
               }
               
  
  

  
              }
  

              
                   // users scailing data  

             $users_scailing_data = [];


             foreach($users as $user){
              $users_scailing_data[] = [
                'user_id'=>$user['id'],
                'performance'=>$user['pivot']['performance'],
                'commitment'=>$user['pivot']['commitment'],
                'daily_report_id'=>$dailyReport->id,
                'time_difference'=>$Attending_and_leaving['pivot']['time_difference'] ?? null,
                'min'=>$Attending_and_leaving['pivot']['min'] ?? null,
              ];

             }

// then we gonna cunking our data to be easier in storing 
             $array_chunk_users  =  array_chunk($users_scailing_data,100);

           
             foreach($array_chunk_users as $user){
              \DB::table('attending_and_leavings')->insert($user);
             }


  

  
            }
  }
  public function preview($dailyReport){
if(is_numeric($dailyReport)){

  $data = dailyReport::where('id',$dailyReport)->with(['daily_productivity','users','supervisor','project'])->first();

  return view('daily_report.print')->with(['data'=>$data]);
}

  }
  

  
}
