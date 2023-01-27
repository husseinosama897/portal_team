<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Auth;
use App\personal_overall;
use DatePeriod;
use DateInterval;
use DateTime;
use Carbon\Carbon;
class MyProfileController extends Controller
{
public function mytimesheet(){

    return view('user.timesheet');

}

    public function jsontimesheet(request $request)
    {
      $data = User::with(['contract'=>function($q){
  return $q->with('project');
      }])
      ->withSum(
        ['Attending_and_leaving' => function($q) use($request){
  
          if($request->from){
  
            $q->whereDate('attending_time','>=',$request->from);
             }
     
             if($request->to){
     
                $q->whereDate('attending_time','<=',$request->to);
                 }
     
              return $q;   
              
  
      }],
      'time_difference'
    )
  
    ->withSum(
      ['Attending_and_leaving' => function($q) use($request){
  
        if($request->from){
  
          $q->whereDate('attending_time','>=',$request->from);
           }
   
           if($request->to){
   
              $q->whereDate('attending_time','<=',$request->to);
               }
   
            return $q;   
            
  
    }],
    'min'
  )
  ;
      $data = $data->withCount(['Attending_and_leaving as Absence'=> function ($query) {
        return $query->where('absence','!=',null);
       }])->with('role')->find(Auth::id()) ;
      
     


      return response()->json(['data'=>$data]);
    }

    public function profile(){

   
      $User = User::withCount(['Attending_and_leaving as Absence'=> function ($query) {
   return $query->where('absence','!=',null);
  }])
  ->with(['user_daily_report','user_file','task','personal_overall','role'])
  
  ->withsum('Attending_and_leaving','time_difference')
  ->withsum('Attending_and_leaving','min')->with('contract')->find(auth()->user()->id);
  
  
  /*
  
      $users = User::get();
     
  
      $scaling = [];
      $number = 10;
      foreach($users as $user){
          
          for ($i=0; $i <=12 ; $i++) { 
  
  
        # We calculate the number of vacation days during the month 
  
        $start = new DateTime(Carbon::now()->subMonths($i)->startOfMonth());
        $end = new DateTime(Carbon::now()->subMonths($i)->lastOfMonth());
        
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
      $st1 = Carbon::now()->subMonths($i)->startOfMonth();
      $st2 = Carbon::now()->subMonths($i)->lastOfMonth();
  
  
        $diff = $st2->diffInDays(Carbon::parse($st1));
  
  #then we calculate wokring days 
  
  $working_days = ($diff - $weekends  );
  
  
  $number = 10; # this test  number of workers 
  
  
  $numbers_util_now = $number  * $working_days ; # total  attendance * working days 
  $rand = rand(100,120) ;
  $increment = ( $rand * 100 /  $numbers_util_now );
  $increment_point = ( $rand *10 * 100 /  $numbers_util_now * 10 );
  # actual attendance * total  attendance / 100 
  $time = rand(5,9);
  $points = ($increment * rand(7,9));
  
  
  
  
  
  
             $scaling[] = [
              
                  'date'=>$st1->format('Y-m-d'),
                  'percentage_performance'=>$increment_point,
                  'percentage_attendance'=>($increment ),
                  'num_of_performers'=>0,
                  'num_of_attendance'=>$rand ,
                  'point'=>rand(200,500),
                  'time'=>$time,
                  'user_id'=>$user->id
              
             ];
   }
  
   personal_overall::insert($scaling);
       }*/
  
       
     
  
      return view('user.profile')->with(['data'=>$User]);
  }
      
  
  
  }
  