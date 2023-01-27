<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

class hearing_process_attendance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

     public $attendance;

    public function __construct($attendance)
    {
        $this->attendance = $attendance;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

      $user = User::find($this->attendance->user_id);

        $startTime = Carbon::createFromFormat('Y-m-d H:i:s',$Attending_and_leaving->attending_time);
        $endTime = Carbon::now()->timezone('Asia/Riyadh');
       
        $endTime = Carbon::createFromFormat('Y-m-d H:i:s',$endTime)->timezone('Asia/Riyadh');
        $totalDuration =  $startTime->diffInMinutes($endTime);
      
     //   $totalMin =  $startTime->diffInMinutes($endTime)  - 60 * $totalDuration;
    
     
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


if($totalMin >= 55){
$totalDuration += 1;
$totalMin = 0;
}

        $Attending_and_leaving->update([
            'attending_leaving'=>Carbon::now()->timezone('Asia/Riyadh'),
            'time_difference'=> $totalDuration,
       //     'leaving_image'=>$imageName,
      //  'min'=>$totalMin


        ]);
$time =$totalDuration;
// ----------------------------- * * * MONTHLY time sheet Personal project * * * ------------------------

if($this->attendance->project_id !== null){

  $timesheet_project_personal =     timesheet_project_personal::where(['project_id'=>$this->attendance->project_id,'user_id'=>auth()->user()->id,'date'=>Carbon::now()->startOfMonth()])->first();

  if($timesheet_project_personal){
    $timesheet_project_personal->increment(
      'time',$this->attendance->time_difference
    );
  }else{
    $timesheet_project_personal =     timesheet_project_personal::create([
      'project_id'=>$this->attendance->project_id,
      'user_id'=>$this->attendance->user_id,
    'date'=>Carbon::now()->startOfMonth(),
  
    'time'=>$this->attendance->time_difference
    
    ]
    
    );
  
  }

  
}


// ----------------------------- * * * end of MONTHLY time sheet Personal project * * * ------------------------




// ---------------------------- * * * daily time sheet project * * * -------------------------------------
  
if($this->attendance->project_id !== null){

$timesheet_daily_project =     timesheet_daily_project::where(['project_id'=>$this->attendance->project_id,'date'=>Carbon::now()->format('Y-m-d')])->first();

if($timesheet_daily_project){
  $timesheet_daily_project->increment(
    'time',$this->attendance->time_difference
  );
}else{
  $timesheet_daily_project =     timesheet_daily_project::create([
    'project_id'=>$this->attendance->project_id,
  'date'=>Carbon::now()->format('Y-m-d'),
  'time'=>$this->attendance->time_difference
  
  ]
  
  );

}


}
// ------------------------------ * * * end of daily time sheet  project * * * ----------------------------------








// ---------------------------- * * * MONTHLY time sheet project * * * -------------------------------------
  
if($this->attendance->project_id !== null){



$number = project::find($this->attendance->project_id)->user()->count(); # this test  number of workers 


$project_overall = project_overall::where(['date'=>Carbon::now()->startOfMonth(),'project_id'=>$this->attendance->project_id])->first();

$numbers_util_now = $number  * $working_days;
$increment = 1  *  100  / $numbers_util_now;

  if($project_overall){
 
$old =  $project_overall->num_of_attendance * $numbers_util_now / 100 ;

        $project_overall->update([
    
    
            'percentage_attendance'=>($old   + $increment ),
          
      
          ]);
    
          $project_overall->increment('time_attendance',$this->attendance->time_difference);
    
    

    

  
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
            'time_attendance'=>$this->attendance->time_difference,
         'cost_reduction'=>0,
            'project_id'=>$this->attendance->project_id ?? null
        ]);
    
    
    

  
  
  }
  
  
  }
  // ------------------------------ * * * end of MONTHLY time sheet  project * * * ----------------------------------
  







  // ---------------------------- * * * daily time sheet section * * * -------------------------------------
  
if(!empty($user->role) && $user->role->section_id !== null){


  $timesheet_daily_section =     timesheet_daily_section::where(['section_id'=>$user->role->section_id,'date'=>Carbon::now()->format('Y-m-d')])->first();
  
  if($timesheet_daily_section){
    $timesheet_daily_section->increment(
      'time',$this->attendance->time_difference
    );
  }else{
    $timesheet_daily_section =     timesheet_daily_section::create([
      'section_id'=>$user->role->section_id,
    'date'=>Carbon::now()->format('Y-m-d'),
    'time'=>$this->attendance->time_difference
    
    ]
    
    );
  
  }
  
  
  
  // ------------------------------ * * * end of daily time sheet  project * * * ----------------------------------
  
  
  
  
  
  
  
  
  // ---------------------------- * * * MONTHLY time sheet section * * * -------------------------------------
    
  $number = $user->role->section()->count();

  $numbers_util_now = $number  * $working_days;
$increment = 1  *  100  / $numbers_util_now;



  
    $monthly_section =     monthly_section::where(['section_id'=>$user->role->section_id,'date'=>Carbon::now()->startOfMonth(),])->first();
    
    if($monthly_section){
      $old =  $monthly_section->num_of_attendance * $numbers_util_now / 100 ;

      $monthly_section->increment(
        'time',$this->attendance->time_difference
      );

      $monthly_section
      ->increment(
        'percentage_attendance',($old+$increment)
      );


      $monthly_section
      ->increment(
        'num_of_attendance',1
      );



    }else{
      $monthly_section =     monthly_section::create([
        'section_id'=>$user->role->section_id,
      'date'=>Carbon::now()->startOfMonth(),
    'percentage_attendance'=>$increment,
'num_of_attendance'=>1,
'percentage_performance'=>0,
'saving_percentage'=>0,

'marketing_project'=>0,
'percentage_marketing_project'=>0,
'percentage_deal'=>0,
'num_deal'=>0,

'cost_reduction'=>0,
'num_of_performers'=>0,
      'time'=>$this->attendance->time_difference
      
      ]
      
      );
    
    }
    
  }
    
    // ------------------------------ * * * end of MONTHLY time sheet  section * * * ----------------------------------
    
  
  
  
  
  
  
    



      // ---------------------------- * * * daily time sheet role * * * -------------------------------------
  

  $timesheet_daily_role =     timesheet_daily_role::where(['role_id'=>$user->role->id,'date'=>Carbon::now()->format('Y-m-d')])->first();
  
  if($timesheet_daily_role){
    $timesheet_daily_role->increment(
      'time',$this->attendance->time_difference
    );
  }else{
    $timesheet_daily_role =     timesheet_daily_role::create([
      'role_id'=>$user->role->id,
    'date'=>Carbon::now()->format('Y-m-d'),
    'time'=>$this->attendance->time_difference
    
    ]
    
    );
  
  }
  
  
  
  // ------------------------------ * * * end of daily time sheet  role * * * ----------------------------------
  
  
  
  
  
  
  
  
  // ---------------------------- * * * MONTHLY time sheet role * * * -------------------------------------
    
 if(!empty($user->role)){

  $timesheet_monthly_role =     timesheet_monthly_role::where(['role_id'=>$user->role->id,'date'=>Carbon::now()->startOfMonth(),])->first();
    
  if($timesheet_monthly_role){
    $timesheet_monthly_role->increment(
      'time',$this->attendance->time_difference
    );
  }else{
    $timesheet_daily_role =     timesheet_daily_role::create([
      'role_id'=>$user->role->id,
    'date'=>Carbon::now()->startOfMonth()
  ,
    'time'=>$this->attendance->time_difference
    
    ]
    
    );
  
  }

 }

    
    
    
    // ------------------------------ * * * end of MONTHLY time sheet  section * * * ----------------------------------
    








 // ---------------------------- * * * MONTHLY time sheet personal * * * -------------------------------------
    

  $timesheet_monthly_personal =     timesheet_monthly_personal::where(['user_id'=>$this->attendance->user_id,'date'=>Carbon::now()->startOfMonth(),])->first();
  
  if($timesheet_monthly_personal){
    $timesheet_monthly_personal->increment(
      'time',$this->attendance->time_difference,
    );
  }else{
    $timesheet_monthly_personal =     timesheet_monthly_personal::create([
      'user_id'=>$this->attendance->user_id,
    'date'=>Carbon::now()->startOfMonth()
  ,
    'time'=>$this->attendance->time_difference
    
    ]
    
    );
  
  }
  
  
  
  // ------------------------------ * * * end of MONTHLY time sheet  section * * * ----------------------------------
  




    }
}
