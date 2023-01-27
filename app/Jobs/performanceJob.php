<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\performance_daily_personal;
use App\performance_daily_project;
use App\performance_daily_section;
use App\performance_monthly_personal;
use App\performance_monthly_project;
use App\monthly_section;
use App\performance_project_personal;
use App\performance_section_personal;
use App\project_overall;
use Carbon\Carbon;
class performanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $user,$performance,$project,$section;
    public function __construct($user,$performance,$project,$section)
    {
      $this->user = $user;
    $this->performance=  $performance;
   $this->project =  $project;
    $this->section = $section;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $user = User::find($this->user)->with('contract');
        
      

        
        $start = new DateTime(Carbon::now()->startOfMonth());
        $end = new DateTime(Carbon::now()->format('Y-m-d'));
        
        $interval = new DateInterval('P1D');
        $daterange = new DatePeriod($start, $interval ,$end);
        
        $saturdays = 0;
        foreach($daterange as $date){
            $days = $date->format('D');
            if ($days == 'Fri') {
                $saturdays++;
            }
        }

      $st1 = Carbon::now()->startOfMonth();
      $st2 = Carbon::now();
  

        $diff = $st2->diffInDays(Carbon::parse($st1));

if($this->project){
    $number =  $user->contract->project()->count();

}elseif($this->section){
$number =   $user->role->section()->count();
}else{
    $number = 0;
}
      
      
        
   

$days_without_weekends = ($diff - $saturdays  );
   

$numbers_util_now = 1  * $days_without_weekends * 10;
   
$increment = $this->performance  * 100  / $numbers_util_now ;



     if($this->project == null){

    
        $project_overall = project_overall::where(['date'=>Carbon::now()->startOfMonth(),'project_id'=>$this->project])->first();

if($project_overall){
    $old =  $performance_monthly_project->num_of_performers * $numbers_util_now / 100 ;
            
    $project_overall->update([


        'percentage_performance'=>($old   + $increment ),
      
  
      ]);
      $project_overall->increment(
        'num_of_performers',1
           );
           $project_overall->increment(
            'performance_point',$this->point
               );

           

}else{
    project_overall::create([
        'date'=>Carbon::now()->startOfMonth(),
        'percentage_performance'=>( $increment ),
        'cash_out'=>0,
        'cash_in'=>0,
        'date'=>Carbon::now()->startOfMonth(),
        'project_id'=>$this->project,
        'num_of_performers'=>1,
        'num_of_attendance'=>0,
'time_attendance'=>0,

        'percentage_attendance'=>0
    ]);
}


      
        
     }






     if($this->user == null){
        $performance_monthly_personal = personal_overall::where(['date'=>Carbon::now()->startOfMonth(),'user_id'=>$this->user])->first();



        if($performance_monthly_personal){

            $old =  $performance_monthly_personal->num_of_performers * $numbers_util_now / 100 ;
            
            $performance_monthly_personal->update([
        
        
                'percentage_performance'=>($old   + $increment ),
              
          
              ]);

            $performance_monthly_personal->increment('point',$this->performance);

            $performance_monthly_personal->increment('num_of_performers',1);

        }else{
            personal_overall::create([
                'date'=>Carbon::now()->startOfMonth(),
                'point'=>$this->performance ?? 0,
                'user_id'=>$this->user,
                'num_of_performers'=>1,
                'num_of_attendance'=>0,
                'time'=>0,
                'marketing_project'=>0,
                'cost_reduction'=>0,
                'percentage_performance'=>$increment,
                'percentage_attendance'=>0,
                'percentage_section'=>0,
                'marketing'=>0
            ]);
        }
        
     }


     if($this->section == null){
        $monthly_section = monthly_section::where(['date'=>Carbon::now()->startOfMonth(),'section_id'=>$this->section])->first();

        
        if($monthly_section){
            $old =  $monthly_section->num_of_performers * $numbers_util_now / 100 ;
            
            $monthly_section->increment('point',$this->performance);
           
            $monthly_section->increment('percentage_performance',($old + $increment));
           
            $monthly_section->increment('num_of_performers',1);
           

        }else{
            monthly_section::create([
                'date'=>Carbon::now()->startOfMonth(),
                'point'=>$this->performance ?? 0,
                'section_id'=>$this->section,
                'percentage_attendance'=>0,
                'num_of_attendance'=>0,
                'percentage_performance'=>$increment,
                'saving_percentage'=>0,


                'marketing_project'=>0,
                'percentage_marketing_project'=>0,
                'percentage_deal'=>0,
                'num_deal'=>0,
                

                'cost_reduction'=>0,
                'num_of_performers'=>1,
                      'time'=>0
            ]);
        }
        
     }


     if($this->section == null && $this->user){

        $performance_section_personal = performance_section_personal::where(['date'=>Carbon::now()->startOfMonth(),'section_id'=>$this->section,'user_id'=>$this->user])->first();

        if($performance_section_personal){
            $performance_section_personal->increment('point',$this->performance);
        }else{
            performance_section_personal::create([
                'date'=>Carbon::now()->startOfMonth(),
                'point'=>$this->performance ?? 0,
                'section_id'=>$this->section,
                'user_id'=>$this->user
            ]);
        }
        
     }


     if($this->project == null && $this->user){
        $performance_project_personal = performance_project_personal::where(['date'=>Carbon::now()->startOfMonth(),'project_id'=>$this->project,'user_id'=>$this->user])->first();

        if($performance_project_personal){
            $performance_project_personal->increment('point',$this->performance);
        }else{
            performance_project_personal::create([
                'date'=>Carbon::now()->startOfMonth(),
                'point'=>$this->performance ?? 0,
                'project_id'=>$this->project,
                'user_id'=>$this->user
            ]);
        }
        
     }





    }
}
