<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\product;
use App\personal_overall;
use Carbon\Carbon;
use App\monthly_section;
class update_pervious_value implements ShouldQueue
{
public $value , $id , $user , $value_p,$section;

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($value , $id, $user, $value_p,$section)
    {
        $this->id = $id;
        $this->value = $value;

        $this->user = $user;

        $this->value_p = $value_p;
$this->section = $section;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
     
        product::find($this->id)->update(['value'=>$this->value]);
     

     

   

       //-------------------- section --------------------------------

if(  $this->value < $this->value_p ){
     $personal_overall =  personal_overall::where(['date'=>Carbon::now()->startOfMonth(),'user_id'=>$this->user])->first();  
     if($personal_overall){
      $personal_overall->increment('cost_reduction'
      ,$this->value_p - $this->value ) ;
      $personal_overall->increment('percentage_section'
      ,5) ;

     }else{
     personal_overall::create([
      'user_id'=>$this->user,
      'date'=>Carbon::now()->startOfMonth()
    ,
      'time'=>0,
      'marketing_project'=>0,
      'num_of_performers'=>0,
      'num_of_attendance'=>0,
      'percentage_performance'=>0,
      'percentage_attendance'=>0,
  'percentage_section'=>5,
  'cost_reduction'=>$this->value_p - $this->value,
  'marketing'=>0,
     ]);

     }
  



     
  
       if($this->section !== null  &&  $this->value < $this->value_p){
        $monthly_section =  monthly_section::where(['date'=>Carbon::now()->startOfMonth(),'section_id'=>
        $this->section
        ])->first(); 
        
        if($monthly_section){
          $monthly_section->increment('cost_reduction'
          ,$this->value_p  - $this->value);
          
        
          $monthly_section->increment('saving_percentage'
          ,5);
 
          
        }else{

          monthly_section::create([
            'date'=>Carbon::now()->startOfMonth(),
            'point'=> 0,
            'section_id'=>$this->section,
            'percentage_attendance'=>0,
            'num_of_attendance'=>0,

            'marketing_project'=>0,
            'percentage_marketing_project'=>0,
            'percentage_deal'=>0,
            'num_deal'=>0,

            'percentage_performance'=>0,
            'saving_percentage'=>5,
            'cost_reduction'=> $this->value_p - $this->value ,
            'num_of_performers'=>0,
                  'time'=>0
           ]);

        }
        
  
    
       }

      }
    }
}
