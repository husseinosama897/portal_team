<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\project;
use App\User;
use Carbon\Carbon;
use DatePeriod;
use DateInterval;
use DateTime;
use App\project_overall;
use App\summaryreoprt;
use App\section;

class reportController extends Controller
{
    public function stockPage(){
        return view('managers.report.stock');
    }

    public function analysis_HR_page(){
        return view('managers.report.analysis_HR');
    }

    public function analysis_HR_JSON(request $request){


/*
        $date = \Carbon\Carbon::now();

 $salary =  \App\contract::where('salary_per_month',null)->get();

foreach($salary as $sal){
    $sal->update([
        'salary_per_month'=>rand(2000,5000),
    ]);
}

 $data = [];

        foreach(User::get() as $user){

    $totalDuration =  $date->diffInMonths($user->contract->contract_date);

for ($i=0; $i <= 10 ; $i++) { 

$data[]= [ 
    'user_id'=>$user->id,
    'Amount'=>$user->contract->salary_per_month,
'month'=>$date->subMonths($i)->format('M'),
];


}

 }

 $array_chunk = array_chunk($data,100);
   foreach($array_chunk as  $chunk){
    \App\salary::create(
        $chunk
         );
     
   }
*/

/*
        foreach(User::get() as $user){

            $user->update([
                'role_id'=>rand(50,63),
            ]);

        }
        */

/*
        foreach(\App\Attending_and_leaving::get() as $Attending_and_leaving){
            $Attending_and_leaving->delete();
        }

        $users = User::get();
        $scaling = [];
        foreach($users as $user){
            for ($i=0; $i <=30 ; $i++) { 
               $scaling[] = [
                'time_difference'=>rand(5,9),
                'user_id'=>$user->id,
               ];


            }
        }

        \App\Attending_and_leaving::insert($scaling);

*/
       /*
$users =   \App\contract::where('country',null)->get();
$scaling = [];
foreach($users as $user){
    $randomcountry = rand(1,6);

        

        if($randomcountry == 1){

       $user->update([
        'country'=>'netherland',
       ]);
        }

        if($randomcountry == 2){
            $user->update([
                'country'=>'egypt',
               ]);
        
        }

        if($randomcountry == 3){

     
            $user->update([
                'country'=>'ksa',
               ]);

        }

        
        if($randomcountry == 4){

            $user->update([
                'country'=>'india',
               ]);

        }

        if($randomcountry == 5){

            $user->update([
                'country'=>'serbia',
               ]);

        }

        if($randomcountry == 6){

        
            $user->update([
                'country'=>'iraq',
               ]);

        }

    }
*/



/*
 $data =      \App\contract::where('age',null)->get();
       
foreach($data as $dat){
    $randage = rand(20,60);
  $newDateTime = \Carbon\Carbon::today()->subYears($randage);

    $dat->update([
        'age'=>$newDateTime,
    ]);

}

*/
        $data = User::query();

      $data =  $data->withsum('Attending_and_leaving','time_difference')->withsum('Attending_and_leaving','min');
    $data =    $data->with(['role'=>function($q){

        $q->select(['name','id']);

    }]);

    $data = $data->with(['salary'=>function($q)use($request){
if($request->from !== null || $request->to !== null ){
    $q->whereBetween('month',[$request->from,$request->to]);
 
}


    }]);



    $data = $data->with(['contract'=>function($q){

$q->select(['age','country','contract_date','salary_per_month','id','user_id']);

    }]);

    $data = $data->with(['task'=>function($q){
        $q->select(['start_at','expires_in','point','task_id']);
    }]);


    $data = $data->get()->chunk(10);

    return response()->json(['data'=>$data]);

    }


public function stock($project,request $request){
    if(is_numeric($project)){
        $data = project::query();

        $data = $data->with(['purchase_order'=>function($q)use($request){
            if($request->from  || $request->to){
                $q->whereBtween('date',[$request->from,$request->to]);
                
                                }
                                
$q->select('id','date','ref','project_id','delivery_date');

            $q->with(['attributes'=>function($q)use($request){
              




            }]);

            $q->with(['attributes2'=>function($q)use($request){
              

return $q->where('product_id','=',null);


            }]);


        }]);
        
        $data = $data->where('id',$project)->first();


        return response()->json(['data'=>$data]);
    }


}

public function positiontimesheetPage(){
    return view('managers.report.timesheet.position');
}

public function projectstimesheetPage(){
    return view('managers.report.timesheet.project');
}


//----------------------- * * * Performance project report * * * ---------------------------------------------


public function project_search(request $request){

    $this->validate($request,[
        'project_name'=>['required','string','max:255'],
    ]);
    $data = project::query();

    if($request->project_name){

    $data =   $data->where('name', 'LIKE', '%' . $request->project_name . '%');
      
            }

            $data = $data->select(['id','name'])->get()->take(5);

            return response()->json(['data'=>$data]);
}


//-----------------------------------------------------------------
public function jsonprojectReport(request $request)
{
    $this->validate($request,[
        'project_id'=>['required','numeric','exists:projects,id'],
    ]);
/*
    $users = project::get();
   
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


$numbers_util_now = $number  * $working_days * 10; # total  attendance * working days 
$rand = rand(100,120) * 10;
$increment = ( $rand * 100 /  $numbers_util_now ); # actual attendance * total  attendance / 100 

$points = ($increment * rand(7,9));

*/



/*
           $scaling[] = [
            
                'date'=>$st1->format('Y-m-d'),
                'percentage_performance'=>0,
                'cash_out'=>0,
                'percentage_attendance'=>($increment ),
                'cash_in'=>0,
                'num_of_performers'=>0,
                'num_of_attendance'=>$rand ,
                'performance_point'=>0,
                'time_attendance'=>$time,
                'project_id'=>$user->id
            
           ];
 }
     }
*/
       

  


  
$data = project::query();
if($request->project_id){
    $data = $data->where('id',$request->project_id);
}



$data = $data->whereHas('project_overall',function($q)use($request){
    $from ='';
    $to ='';
  if($request->from){
    $from = date('m', strtotime($request->from));
  }

    if($request->to){
        $to = date('m', strtotime($request->to));
      }
       
      
        if($from){
            $q->whereMonth('date','>=',$from);
        }
      
        if($to){
          $q->whereMonth('date','<=',$to);
      }
      
   
            return $q;   
        


});
$data = $data->with('project_overall');

$data =   $data->first();

  return response()->json(['data'=>$data]);
}



public function jsonpositionReport(request $request)
{
  $data = User::query();

  $data = $data->select(['id','role_id','name','laborer']);

  $data = $data->WhereHas('contract',function($q){


    });

  $data = $data->with(['contract'=>function($q){

  }])->with('role');
  

 $data=  $data->withSum(
    ['personal_overall' => function($q) use($request){


        $from ='';
        $to ='';
      if($request->from){
        $from = date('m', strtotime($request->from));
      }

        if($request->to){
            $to = date('m', strtotime($request->to));
          }
           
          
            if($from){
                $q->whereMonth('date','>=',$from);
            }
          
            if($to){
              $q->whereMonth('date','<=',$to);
          }
          
       
                return $q;   
            

  }],
  'time'
)

;
      $data = $data->withCount(['Attending_and_leaving as Absence'=> function ($query) {
    return $query->where('absence','!=',null);
   }]);
  


$data =   $data->get();



if($request->from){
    $start =  Carbon::createFromFormat('Y-m-d',$request->from)->startOfMonth();
  
  }else{
    $start = new DateTime(Carbon::now()->startOfMonth());
  
    
  }
  
  if($request->to){
    $end =  Carbon::createFromFormat('Y-m-d',$request->to);
  }else{
    $end = new DateTime(Carbon::now()->format('Y-m-d'));
  }
  
  $interval = new DateInterval('P1D');
  $daterange = new DatePeriod($start, $interval ,$end);
  
  $weekends = 0;
  
  foreach($daterange as $date){
      $days = $date->format('D');
      if ($days == 'Fri') { # we set friday
          $weekends++;
      }
  }


  return response()->json(['data'=>$data,'weekends'=>$weekends]);
}


// ------------------------------- construction report -------------------------------------------

public function construction(){
    $bid_value = summaryreoprt::select(['id','bid_value_open'])->first();


return view('managers.report.section.construction')->with(['bid_value'=>$bid_value]);
}


public function tenderpage(){
 
return view('managers.report.section.tender');
}



public function tenderjson(request $request){
    $section = section::where('name','tender')->with(['monthly_section'=>function($q)use($request){

        $from ='';
        $to ='';
      if($request->from){
        $from = date('m', strtotime($request->from));
      }

        if($request->to){
            $to = date('m', strtotime($request->to));
          }
           
          
            if($from){
                $q->whereMonth('date','>=',$from);
            }
          
            if($to){
              $q->whereMonth('date','<=',$to);
          }
          
       
                return $q;   
            
                

    }])->first();

    return response()->json(['data'=>$section]);

}

public function jsonconstruction(request $request){

/*
$petty_cashs = \App\subcontractor::where('total','!=',null)->where(['status'=>1])->get();
$array = [];
foreach($petty_cashs as $petty_cash){


$project_overall = project_overall::whereDate('date',Carbon::createFromFormat('Y-m-d', $petty_cash->date)->firstOfMonth()

)->where(['project_id'=>$petty_cash->project_id])->first();

    if(!$project_overall ){
    project_overall::create([
    'date'=>Carbon::createFromFormat('Y-m-d', $petty_cash->date)->firstOfMonth(),
    'percentage_performance'=>0,
    'cash_out'=>$petty_cash->total,
    'percentage_attendance'=>0,
    'cash_in'=>0,
    'num_of_performers'=>0,
    'num_of_attendance'=>0,
    'performance_point'=>0,
    'time_attendance'=>0,
 
    'project_id'=>$petty_cash->project_id
]);
    }else{
        $project_overall->increment('cash_out',$petty_cash->total);
    }
}
    
*/

    $data = project::query();




    $data = $data->select(['id','bid_value','name']);

$data = $data->whereHas('project_overall',function($q)use($request){
    if($request->from){
        $q->whereMonth('date','>=',$request->from);
    }


    if($request->to){
        $q->whereMonth('date','=<',$request->to);
    }


});
$data = $data->with(['project_overall'=>function($q){
return $q->select(['date','id','cash_in','cash_out',
'percentage_performance','percentage_attendance'
,'project_id']);
}]);

$data =   $data->get();



  return response()->json(['data'=>$data]);


}


public function procurementjson(request $request){
    $section = section::where('name','procurement')->with(['monthly_section'=>function($q)use($request){

        $from ='';
        $to ='';
      if($request->from){
        $from = date('m', strtotime($request->from));
      }

        if($request->to){
            $to = date('m', strtotime($request->to));
          }
           
          
            if($from){
                $q->whereMonth('date','>=',$from);
            }
          
            if($to){
              $q->whereMonth('date','<=',$to);
          }
          
       
                return $q;   
            
                

    }])->first();

    return response()->json(['data'=>$section]);

}

public function procurementpage(){
return view('managers.report.section.procurement');
}





public function marketingjson(request $request){
    $section = section::where('name','Marketing')->with(['monthly_section'=>function($q)use($request){

        $from ='';
        $to ='';
      if($request->from){
        $from = date('m', strtotime($request->from));
      }

        if($request->to){
            $to = date('m', strtotime($request->to));
          }
           
          
            if($from){
                $q->whereMonth('date','>=',$from);
            }
          
            if($to){
              $q->whereMonth('date','<=',$to);
          }
          
       
                return $q;   
            
                

    }])->first();

    return response()->json(['data'=>$section]);

}

public function marketingpage(){
return view('managers.report.section.marketing');
}


}
