<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\workflow;
use App\User;


use App\Attending_and_leaving;

use App\project;
use DB;
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
use Carbon\Carbon;
use App\project_overall;
use App\personal_overall;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
       
        
    /*
    $Attending_and_leaving = Attending_and_leaving::whereDate('attending_time', DB::raw('CURDATE()'))->get();

    foreach($Attending_and_leaving as $att)
{

    if(!empty($att)){ 
        $startTime = Carbon::createFromFormat('Y-m-d H:i:s',$att->attending_time);
        $endTime = Carbon::now()->timezone('Asia/Riyadh');
    
      
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



        
        

// ----------------------------- * * * MONTHLY time sheet Personal project * * * ------------------------

if( !empty($att->user->contract->project) &&  $att->user->contract->project->id !== null){

  $timesheet_project_personal =     timesheet_project_personal::where(['project_id'=>$att->user->contract->project->id,'user_id'=>$att->user_id,'date'=>Carbon::now()->startOfMonth()])->first();

  if($timesheet_project_personal){
    $timesheet_project_personal->increment(
      'time',$att->time_difference ??0
    );
  }else{
    $timesheet_project_personal =     timesheet_project_personal::create([
      'project_id'=>$att->user->contract->project->id,
      'user_id'=>$att->user_id,
    'date'=>Carbon::now()->startOfMonth(),
  
    'time'=>$att->time_difference ??0
    
    ]
    
    );
  
  }

  
}


// ----------------------------- * * * end of MONTHLY time sheet Personal project * * * ------------------------




// ---------------------------- * * * daily time sheet project * * * -------------------------------------
  
if(!empty($att->user->contract->project) && $att->user->contract->project->id !== null){

$timesheet_daily_project =     timesheet_daily_project::where(['project_id'=>$att->user->contract->project->id,'date'=>Carbon::now()->format('Y-m-d')])->first();

if($timesheet_daily_project){
  $timesheet_daily_project->increment(
    'time',$att->time_difference ??0
  );
}else{
  $timesheet_daily_project =     timesheet_daily_project::create([
    'project_id'=>$att->user->contract->project->id,
  'date'=>Carbon::now()->format('Y-m-d'),
  'time'=>$att->time_difference ??0
  
  ]
  
  );

}


}
// ------------------------------ * * * end of daily time sheet  project * * * ----------------------------------








// ---------------------------- * * * MONTHLY time sheet project * * * -------------------------------------
  
if(!empty($att->user->contract->project) && $att->user->contract->project->id !== null){

$number = $att->user->contract->project()->count(); # this test  number of workers 


$User_overall = project_overall::where(['date'=>Carbon::now()->startOfMonth(),'project_id'=>$att->user->contract->project->id])->first();

$numbers_util_now = $number  * $working_days;
$increment = 1  *  100  / $numbers_util_now;

  if($User_overall){
 
$old =  $User_overall->num_of_attendance * $numbers_util_now / 100 ;

        $User_overall->update([
    
    
            'percentage_attendance'=>($old   + $increment ),
          
      
          ]);
    
          $User_overall->increment('time_attendance',$att->time_difference ??0);
    

          $User_overall->increment('num_of_attendance',1);
    
    

    

  
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
            'time_attendance'=>$att->time_difference ??0,
         
            'project_id'=>$att->user->contract->project->id
        ]);
    
    
    

  
  
  }
  
  
  }
  // ------------------------------ * * * end of MONTHLY time sheet  project * * * ----------------------------------
  







  // ---------------------------- * * * daily time sheet section * * * -------------------------------------
  
if(!empty($get->role) ){

  $timesheet_daily_section =     timesheet_daily_section::where(['section_id'=>$get->role->section_id,'date'=>Carbon::now()->format('Y-m-d')])->first();
  
  if($timesheet_daily_section){
    $timesheet_daily_section->increment(
      'time',$att->time_difference ??0
    );
  }else{
    $timesheet_daily_section =     timesheet_daily_section::create([
      'section_id'=>$get->role->section_id,
    'date'=>Carbon::now()->format('Y-m-d'),
    'time'=>$att->time_difference ??0
    
    ]
    
    );
  
  }
  
  
  }
  // ------------------------------ * * * end of daily time sheet  project * * * ----------------------------------
  
  
  
  
  
  
  
  
  // ---------------------------- * * * MONTHLY time sheet section * * * -------------------------------------
    
  if(!empty($get->role) && $get->role->section_id !== null){
  

    $number = $get->role->section()->count(); # this  number of workers 


$numbers_util_now = $number  * $working_days;
$increment = 1  *  100  / $numbers_util_now;


    $monthly_section =     monthly_section::where(['section_id'=>$get->role->section_id,'date'=>Carbon::now()->startOfMonth(),])->first();
    
    if($monthly_section){

      $old =  $monthly_section->num_of_attendance * $numbers_util_now / 100 ;

      $monthly_section->increment(
        'time',$att->time_difference ??0
      );

      $monthly_section->increment(
        'percentage_attendance',($increment + $old)
      );

      $monthly_section->increment(
        'num_of_attendance',1
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
      'time'=>$att->time_difference ??0,

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
      'time',$att->time_difference ??0
    );
  }else{
    $timesheet_daily_role =     timesheet_daily_role::create([
      'role_id'=>$get->role->id,
    'date'=>Carbon::now()->format('Y-m-d'),
    'time'=>$att->time_difference ??0
    
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
        'time',$att->time_difference ??0
      );
    }else{
      $timesheet_daily_role =     timesheet_daily_role::create([
        'role_id'=>$get->role->id,
      'date'=>Carbon::now()->startOfMonth()
    ,
      'time'=>$att->time_difference ??0
      
      ]
      
      );
    
    }
    
    
    }
    // ------------------------------ * * * end of MONTHLY time sheet  section * * * ----------------------------------
    








 // ---------------------------- * * * MONTHLY time sheet personal * * * -------------------------------------
    

  $timesheet_monthly_personal =     personal_overall::where(['user_id'=>$att->user_id,'date'=>Carbon::now()->startOfMonth(),])->first();
  $numbers_util_now = 1  * $working_days;
$increment = 1  *  100  / $numbers_util_now;

  if($timesheet_monthly_personal){
 
$old =  $timesheet_monthly_personal->num_of_attendance * $numbers_util_now / 100 ;

$timesheet_monthly_personal->update([
    
    
  'percentage_attendance'=>($old   + $increment ),


]);


    $timesheet_monthly_personal->increment(
      'time',$att->time_difference ??0
    );
    $timesheet_monthly_personal->increment(
      'num_of_attendance',1
    );
  }else{
    $timesheet_monthly_personal =     personal_overall::create([
      'user_id'=>$att->user_id,
    'date'=>Carbon::now()->startOfMonth()
  ,
    'time'=>$att->time_difference ??0,
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
  


}
}
 
   */
        
        return view('home');
    }
}
