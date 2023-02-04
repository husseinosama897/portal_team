<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Str;
use \Carbon\Carbon;
use Storage;
use App\Attending_and_leaving;
use DB;
use App\project;

use App\timesheet_monthly_personal;
use App\timesheet_monthly_project;
use App\monthly_section;
use App\timesheet_daily_role;
use App\timesheet_monthly_role;
use App\timesheet_daily_section;
use App\timesheet_daily_project;
use App\timesheet_project_personal;
use DatePeriod;
use DateInterval;
use DateTime;
use App\project_overall;
use App\personal_overall;
use App\Jobs\hearing_process_attendance;
class laborerattendingController extends Controller
{

  public function __construct()
  {
      $this->middleware('auth');
  }

  
public function sortUsersByProject(project $User){
  
  $User = $User->contract()->select(['user_id','id','project_id'])->with(['user'=>function($q){

    return $q->select(['name','id']);

  }])->get()->chunk(10);

  return response()->json(['data'=>$User]);

}


public function attendance_absence(request $request){

$this->validate($request,[
  'from'=>['required'],
  'type'=>['required','numeric','digits_between:1,2'],
  'project_id'=>['required','numeric']
]);

if($request->type== 1){
  $this->validate($request,[
    'to'=>['required'],
  ]);
}


  $data = json_decode($request->data, true);

  $startTime = Carbon::createFromFormat('Y-m-d H:i:s',$request->from);

  $endTime = Carbon::createFromFormat('Y-m-d H:i:s',$request->to);

  $totalDuration =  $startTime->diffInHours($endTime);

  $totalMin =  $startTime->diffInMinutes($endTime)  - 60 * $totalDuration;


if($totalMin >= 55){
$totalDuration += 1;
$totalMin = 0;
}else{
  $totalDuration += $totalMin / 100;
}



$attendance = [];

  foreach($data as $da){
    
    $attendance[] = [
'user_id'=>$da['id'],
'attending_time'=> $request->type == 1 ? $request->from : null,
'attending_leaving'=> $request->type == 1 ? $request->to : null,
'absence' => $request->type == 2 ? Carbon::parse($request->from)->format('d/m/Y') : null   ,
'time_difference'=> $request->type == 1 ? $totalDuration : null,

'project_id'=>$request['project_id']
    ];
  }



  $array_chunk = array_chunk($attendance,100);


  foreach($array_chunk as $chunk){
    Attending_and_leaving::insert($chunk);
  
  }


  foreach($attendance as $chunk){
  
    $job = (new hearing_process_attendance($attendance))->delay(Carbon::now()->addSeconds(90));

    $this->dispatch($job);

  }
 


}



public function attendance_Manule(){
return view('laborer.attendance');
}

public function attendance(request $request,User $User){
  
  $startTime = Carbon::createFromFormat('Y-m-d H:i:s',$request->attending_time);

  $endTime = Carbon::createFromFormat('Y-m-d H:i:s',$request->attending_leaving);
 
  $endTime = Carbon::createFromFormat('Y-m-d H:i:s',$endTime)->timezone('Asia/Riyadh');
  $totalDuration =  $startTime->diffInHours($endTime);

  $totalMin =  $startTime->diffInMinutes($endTime)  - 60 * $totalDuration;


if($totalMin >= 55){
$totalDuration += 1;
$totalMin = 0;
}

  Attending_and_leaving::create([
      'attending_leaving'=> $request->attending_leaving,
      'time_difference'=> $totalDuration,
   'attending_time'=>$request->attending_time,
 // 'min'=>$totalMin,
  'user_id'=>$get->id->id,
  ]);

}

    public function start_day(){
if(!empty(auth()->user()->contract)){
  $data = auth()->user()->contract;

  $User =  $data->project;
  
          return view('laborer.homepage')->with('data',$data);
  
}else{
  return redirect()->back();
}


    }


    public function Attending_and_leaving(request $request,$User){
        
  if(is_string($User)){

    $get = User::where('emp_on',$User)->first();
  $check = $get->contract;


  

        $Attending_and_leaving = $get->Attending_and_leaving()->whereDate('attending_time', DB::raw('CURDATE()'))->first();
    //    $image = $request->v;
     //   $image = str_replace('data:image/jpeg;base64,', '', $image);
     //   $image = str_replace(' ', '+', $image);
      //  $imageName = Str::random(20).'.'.'jpeg';
      
      
        
   //     Storage::disk('public')->put($imageName, base64_decode($image));

  

      if(!empty($Attending_and_leaving)){ 
        $startTime = Carbon::createFromFormat('Y-m-d H:i:s',$Attending_and_leaving->attending_time);
        $endTime = Carbon::now()->timezone('Asia/Riyadh');
       
        $endTime = Carbon::createFromFormat('Y-m-d H:i:s',$endTime)->timezone('Asia/Riyadh');
        $totalDuration =  $startTime->diffInMinutes($endTime);
      
    //    $totalMin =  $startTime->diffInMinutes($endTime)  - 60 * $totalDuration;
    
     
        # We calculate the number of vacation days during the month 

        $start = new DateTime(Carbon::now()->startOfMonth());
        $end = new DateTime(Carbon::now()->format('Y-m-d'));
        
        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($start, $interval ,$end);
        
        $weekends = 0;
        foreach($daterange as $date){
            $days = $date->format('D');
            if ($days == 'Fri') { # we set friday
                $weekends++;
            }
        }

        # here we calculate the difference between the start of month and now
      $st1 = Carbon::now()->startOfMonth();
      $st2 = Carbon::now();
  

        $diff = $st2->diffInDays(Carbon::parse($st1));

#then we calculate wokring days 

$working_days = ($diff - $weekends  );



   $time = (  $totalDuration );
 


        $Attending_and_leaving->update([
            'attending_leaving'=>Carbon::now()->timezone('Asia/Riyadh'),
            'time_difference'=> $time,
       //     'leaving_image'=>$imageName,
       // 'min'=>$totalMin


        ]);
        
        

// ----------------------------- * * * MONTHLY time sheet Personal project * * * ------------------------

if( !empty($check->project) &&  $check->project->id !== null){

  $timesheet_project_personal =     timesheet_project_personal::where(['project_id'=>$check->project->id,'user_id'=>$get->id,'date'=>Carbon::now()->startOfMonth()])->first();

  if($timesheet_project_personal){
    $timesheet_project_personal->increment(
      'time',$time
    );
  }else{
    $timesheet_project_personal =     timesheet_project_personal::create([
      'project_id'=>$check->project->id,
      'user_id'=>$get->id,
    'date'=>Carbon::now()->startOfMonth(),
  
    'time'=>$time
    
    ]
    
    );
  
  }

  
}


// ----------------------------- * * * end of MONTHLY time sheet Personal project * * * ------------------------




// ---------------------------- * * * daily time sheet project * * * -------------------------------------
  
if(!empty($check->project) && $check->project->id !== null){

$timesheet_daily_project =     timesheet_daily_project::where(['project_id'=>$check->project->id,'date'=>Carbon::now()->format('Y-m-d')])->first();

if($timesheet_daily_project){
  $timesheet_daily_project->increment(
    'time',$time
  );
}else{
  $timesheet_daily_project =     timesheet_daily_project::create([
    'project_id'=>$check->project->id,
  'date'=>Carbon::now()->format('Y-m-d'),
  'time'=>$time
  
  ]
  
  );

}


}
// ------------------------------ * * * end of daily time sheet  project * * * ----------------------------------








// ---------------------------- * * * MONTHLY time sheet project * * * -------------------------------------
  
if(!empty($check->project) && $check->project->id !== null){

$number = $check->project->contract()->count(); # this test  number of workers 


$User_overall = project_overall::where(['date'=>Carbon::now()->startOfMonth(),'project_id'=>$check->project->id])->first();

$numbers_util_now = $number  * $working_days;
$numbers_util_now =$numbers_util_now > 0 ? $numbers_util_now  : 1 * 100;
$increment = 1  / $numbers_util_now ;

  if($User_overall){
    $User_overall->increment('num_of_attendance',1);
$old =  $User_overall->num_of_attendance / $numbers_util_now * 100 ;

        $User_overall->update([
    
    
            'percentage_attendance'=>($old    ),
          
      
          ]);
    
          $User_overall->increment('time_attendance',$time);
       
    
    

    

  
  }else{
 
        project_overall::create([
            'date'=>Carbon::now()->startOfMonth(),
            'percentage_performance'=>0,
            'cash_out'=>0,
            'percentage_attendance'=>($increment ),
            'cash_in'=>0,
            'num_of_performers'=>0,
            'num_of_attendance'=>1,
            'performance_point'=>0,
            'time_attendance'=>$time,
         
            'project_id'=>$check->project->id
        ]);
    
    
    

  
  
  }
  
  
  }
  // ------------------------------ * * * end of MONTHLY time sheet  project * * * ----------------------------------
  







  // ---------------------------- * * * daily time sheet section * * * -------------------------------------
  
if(!empty($get->role) && !empty($get->role->section) ){

  $timesheet_daily_section =     timesheet_daily_section::where(['section_id'=>$get->role->section_id,'date'=>Carbon::now()->format('Y-m-d')])->first();
  
  if($timesheet_daily_section){
    $timesheet_daily_section->increment(
      'time',$time
    );
  }else{
    $timesheet_daily_section =     timesheet_daily_section::create([
      'section_id'=>$get->role->section_id,
    'date'=>Carbon::now()->format('Y-m-d'),
    'time'=>$time
    
    ]
    
    );
  
  }
  
  
  }
  // ------------------------------ * * * end of daily time sheet  project * * * ----------------------------------
  
  
  
  
  
  
  
  
  // ---------------------------- * * * MONTHLY time sheet section * * * -------------------------------------
    
  if(!empty($get->role) && !empty($get->role->section) &&   $get->role->section_id !== null){
  

    $number = $get->role->section()->count(); # this  number of workers 


$numbers_util_now = $number  * $working_days;
$numbers_util_now =$numbers_util_now > 0 ? $numbers_util_now  : 1 * 100;
$increment = 1  / $numbers_util_now;


    $monthly_section =     monthly_section::where(['section_id'=>$get->role->section_id,'date'=>Carbon::now()->startOfMonth(),])->first();
    
    if($monthly_section){

      $monthly_section->increment(
        'num_of_attendance',1
      );

      $old =  $monthly_section->num_of_attendance / $numbers_util_now * 100 ;

      $monthly_section->increment(
        'time',$time
      );

      $monthly_section->increment(
        'percentage_attendance',( $old)
      );

     

    }else{
      $monthly_section =     monthly_section::create([
        'section_id'=>$get->role->section_id,
      'date'=>Carbon::now()->startOfMonth(),
      'saving_percentage'=>0,
      'marketing_project'=>0,
'percentage_marketing_project'=>0,
'percentage_deal'=>0,
'num_deal'=>0,
      'cost_reduction'=>0,
      'time'=>$time,

'num_of_attendance'=>1,
'num_of_performers'=>0,
'percentage_attendance'=>$increment,
'percentage_performance'=>0,
      'saving_percentage'=>0,
      'cost_reduction'=>0,
      ]
      
      );
    
    }
    
    
    }
    // ------------------------------ * * * end of MONTHLY time sheet  section * * * ----------------------------------
    
  
  
  
  
  
  
    



      // ---------------------------- * * * daily time sheet role * * * -------------------------------------
  
if(!empty($get->role) ){

  $timesheet_daily_role =     timesheet_daily_role::where(['role_id'=>$get->role->id,'date'=>Carbon::now()->format('Y-m-d')])->first();
  
  if($timesheet_daily_role){
    $timesheet_daily_role->increment(
      'time',$time
    );
  }else{
    $timesheet_daily_role =     timesheet_daily_role::create([
      'role_id'=>$get->role->id,
    'date'=>Carbon::now()->format('Y-m-d'),
    'time'=>$time
    
    ]
    
    );
  
  }
  
  
  }
  // ------------------------------ * * * end of daily time sheet  role * * * ----------------------------------
  
  
  
  
  
  
  
  
  // ---------------------------- * * * MONTHLY time sheet role * * * -------------------------------------
    
  if(!empty($get->role) ){
  
    $timesheet_monthly_role =     timesheet_monthly_role::where(['role_id'=>$get->role->id,'date'=>Carbon::now()->startOfMonth(),])->first();
    
    if($timesheet_monthly_role){
      $timesheet_monthly_role->increment(
        'time',$time
      );
    }else{
      $timesheet_daily_role =     timesheet_daily_role::create([
        'role_id'=>$get->role->id,
      'date'=>Carbon::now()->startOfMonth()
    ,
      'time'=>$time
      
      ]
      
      );
    
    }
    
    
    }
    // ------------------------------ * * * end of MONTHLY time sheet  section * * * ----------------------------------
    








 // ---------------------------- * * * MONTHLY time sheet personal * * * -------------------------------------
    

  $timesheet_monthly_personal =     personal_overall::where(['user_id'=>$get->id,'date'=>Carbon::now()->startOfMonth(),])->first();
  $numbers_util_now =   1  * $working_days  ;
  $numbers_util_now =$numbers_util_now > 0 ? $numbers_util_now  : 1 * 100;
  $increment = 1  / $numbers_util_now ;

  if($timesheet_monthly_personal){
    $timesheet_monthly_personal->increment(
      'num_of_attendance',1
    );

$old =  $timesheet_monthly_personal->num_of_attendance / $numbers_util_now * 100 ;

$timesheet_monthly_personal->update([
    

  'percentage_attendance'=>($old   ),


]);


    $timesheet_monthly_personal->increment(
      'time',$time
    );
  
  }else{
    $timesheet_monthly_personal =     personal_overall::create([
      'user_id'=>$get->id,
    'date'=>Carbon::now()->startOfMonth()
  ,
    'time'=>$time,
    'num_of_performers'=>0,
    'num_of_attendance'=>1,
    'marketing_project'=>0,
    'percentage_performance'=>0,
    'percentage_attendance'=>$increment,
'percentage_section'=>0,
'cost_reduction'=>0,
'marketing'=>0
    ]
    
    );
  
  }
  
  
  
  // ------------------------------ * * * end of MONTHLY time sheet  section * * * ----------------------------------
  



      }else{
       
       
        Attending_and_leaving::create([
         'user_id'=>$get->id,
        'project_id'=>$check->project->id ?? null,
        'section_id'=>$check->role->section_id ?? null,
        'attending_time'=>Carbon::now()->timezone('Asia/Riyadh'),
  //        'out_of_place'=>$request->out_of_place,
           'status'=>1,
           'scanned_by'=>auth()->user()->id
         //  'attending_image'=>$imageName,
        ]);

       

      }

      return view('succes');
 
    }
     
    
    
    }
}
