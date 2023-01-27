<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Purchase_order;
use App\petty_cash;
use App\subcontractor;
use App\employee;
use App\rfq;
use App\matrial_request;
use Carbon\Carbon;
use App\invoice;
use Cache;
use App\report;
use App\workflow;
use App\salary;
use App\project;
use App\project_report;
use App\User;
use App\Report\timesheet_monthly_personal;
use App\Report\timesheet_monthly_project;
use App\Report\timesheet_daily_project;
use App\Report\timesheet_project_personal;
use App\Attending_and_leaving;
use App\contract;

use DatePeriod;
use DateInterval;
use DateTime;

use App\Jobs\hearing_process_attendance;
class projectManagerController extends Controller
{
    
// -----------------------  DCC  * * * ------------------------------------------------

     
      
      public function matrial_requestdata(request $request){
        //-------------- matrial_request -----------------------
      
        if(auth()->user()->projectmanager()->count() > 1){

          $this->validate($request,[
              'project_id'=>['numeric','required','exists:projects,id']
          ]);
  
          $matrial_request = auth()->user()->projectmanager->where('id',$request->project_id)->first();
  
          $matrial_request =  $matrial_request->matrial_request();
      }else{
          $matrial_request = auth()->user()->projectmanager()->first();
          $matrial_request = $matrial_request->matrial_request();
      }

          $matrial_request = $matrial_request->with(['user'=>function($q){
          return $q->select(['id','name']);
                }])->with(['matrial_request_cycle'=>function($q){
                  return $q->with('role');
                   }])->orderBy('created_at','DESC');
          
          
           
              $matrial_request =  $matrial_request->paginate(10);
          
              return response()->json(['matrial_request'=>$matrial_request]);
         
      }
      
      
      
        public function subcontractordata(request $request){
            //-------------- subcontractor -----------------------
            if(auth()->user()->projectmanager()->count() > 1){

              $this->validate($request,[
                  'project_id'=>['numeric','required','exists:projects,id']
              ]);
      
              $subcontractor = auth()->user()->projectmanager->where('id',$request->project_id)->first();
      
              $subcontractor =  $subcontractor->subcontractor();
          }else{
              $subcontractor = auth()->user()->projectmanager()->first();
              $subcontractor = $subcontractor->subcontractor();
          }
              
             
              
              $subcontractor = $subcontractor->with(['user'=>function($q){
              return $q->select(['id','name']);
                    }])->with(['subcontractor'=>function($q){
                      return $q->with('role');
                       }])->orderBy('created_at','DESC');
              
              
               
                  $subcontractor =  $subcontractor->paginate(10);
              
                  return response()->json(['subcontractor'=>$subcontractor]);
             
        }
        
        
      public function summary(request $request){


           if(auth()->user()->projectmanager()->count() > 1){

            $this->validate($request,[
                'project_id'=>['numeric','required','exists:projects,id']
            ]);
    
            $report = auth()->user()->projectmanager->where('id',$request->project_id)->first();
    
            $report =  $report->project_report()->get();
        }else{
            $report = auth()->user()->projectmanager()->first();
            $report = $report->project_report()->get();
        }
            

           return response()->json(['data'=>$report]);
      }
      

      
        public function podata(request $request){
      
      
          //----------------** Purchase_order ---------------------------
      
  
            
           if(auth()->user()->projectmanager()->count() > 1){

            $this->validate($request,[
                'project_id'=>['numeric','required','exists:projects,id']
            ]);
    
            $Purchase_order = auth()->user()->projectmanager->where('id',$request->project_id)->first();
    
            $Purchase_order =  $Purchase_order->purchase_order();
        }else{
            $Purchase_order = auth()->user()->projectmanager()->first();
            $Purchase_order = $Purchase_order->purchase_order();
        }


      
            $Purchase_order =   $Purchase_order->with(['user'=>function($q){
              return $q->select(['id','name']);
                    }])->with(['purchase_order_cycle'=>function($q){
                      return $q->with('role');
                       }]);
      
      
                $Purchase_order =  $Purchase_order->orderBy('created_at','DESC')->paginate(10);
      
      
      
      
      return response()->json(['Purchase_order'=>$Purchase_order,
      
      ]);
      
        }
        
        public function petty_cashdata(request $request){
      //------------------------**  petty cash  ---------------------------------
      
      
  
            if(auth()->user()->projectmanager()->count() > 1){

              $this->validate($request,[
                  'project_id'=>['numeric','required','exists:projects,id']
              ]);
      
              $petty_cash = auth()->user()->projectmanager->where('id',$request->project_id)->first();
      
              $petty_cash =  $petty_cash->petty_cash();
          }else{
              $petty_cash = auth()->user()->projectmanager()->first();
              $petty_cash = $petty_cash->petty_cash();
          }

          
           $petty_cash = $petty_cash-> with(['user'=>function($q){
              return $q->select(['id','name']);
                    }])->with(['petty_cash_cycle'=>function($q){
                      return $q->with('role');
                       }])->orderBy('created_at','DESC');
                 
      
                 $petty_cash =  $petty_cash->paginate(10);
      
                  
      
      return response()->json([
      'petty_cash'=>$petty_cash,
      
      ]);
      
        }
      
          public function index(){
      
            $purchase_orderworkflow =    workflow::where('name','purchase_order')->with(['flowworkStep'=>function($q){
              return     $q->with('role');
               }])->first();
      
               $petty_cashworkflow =    workflow::where('name','petty_cash')->with(['flowworkStep'=>function($q){
                return     $q->with('role');
                 }])->first();
      
                 $matrial_requestworkflow =    workflow::where('name','matrial_request')->with(['flowworkStep'=>function($q){
                  return     $q->with('role');
                   }])->first();
      
                   $subcontrctorworkflow =    workflow::where('name','subcontractor')->with(['flowworkStep'=>function($q){
                    return     $q->with('role');
                     }])->first();
      
                     $projects = auth()->user()->projectmanager()->select(['id','projectmanager_id','name'])->get();
            

      
            return view('ProjectManager.dcc')->with(['purchase_orderworkflow'=>$purchase_orderworkflow,'subcontrctorworkflow'=>$subcontrctorworkflow,
          'matrial_requestworkflow'=>$matrial_requestworkflow,
          'petty_cashworkflow'=>$petty_cashworkflow,
          'projects'=>$projects
          
          ]);
          }


          /** ------------------------     end of dcc ------------------------------------------- */

          public function costcenterjson(request $request){
          

              if(auth()->user()->projectmanager()->count() > 1){

                $this->validate($request,[
                    'project_id'=>['numeric','required','exists:projects,id']
                ]);
        
                $data = auth()->user()->projectmanager->where('id',$request->project_id)->first();
        
             
            }else{
                $data = auth()->user()->projectmanager()->first();
               
            }
                
                $data =  $data->with(['purchase_order'=>function($q){
            
            
                        return $q->where('status',1);
            
            
                            }])->with('projectmanager')->with(['petty_cash'=>function($q){
            
            
                                return $q->where('status',1);
            
            
                            }])->with(['subcontractor'=>function($q){
            
            
                                return $q->where('status',1);
            
            
                            }])->withsum('subcontractor','total')->with(['contract'=>function($q)use($project){
            
                                   $q->with(['user'=>function($query)use($project){
            
                                      $query->withsum(['timesheet_project_personal as time'=>function($q)use($project){
                                        return $query->where('project_id',$project);
                                      }]);
            
        
                                                 $query = $query->withCount(['Attending_and_leaving as Absence'=> function ($query) {
                                                    return $query->where('absence','!=',null);
                                                   }]);
        
        
                                                   return $query;
                                   }]);                 
                       
            
            return $q;
            
                              }])->withsum('invoice','total')->with('invoice')
              ->first();
            
            
                
           return response()->json(['data'=>$data]);
            
               
            
        }
        
        public function costcenter( ){
        
          $projects = auth()->user()->projectmanager()->select(['id','projectmanager_id','name'])->get();
            
        return view('ProjectManager.costcenter')->with('projects',$projects);
        
           
        
        }
        



        // ----------------------------- * * * time sheet * * * -----------------------------------------

        public function jsontimesheet(request $request)
        {

          
     

          if(auth()->user()->projectmanager()->count() > 1){

            $this->validate($request,[
                'project_id'=>['numeric','required','exists:projects,id']
            ]);
    
            $project = auth()->user()->projectmanager->where('id',$request->project_id)->first();
    
         
        }else{

            $project = auth()->user()->projectmanager()->first();
           
        }

      

$data = $data = User::query();


        
$data = $data->WhereHas('contract',function($q)use($project){

$q->WhereHas('project',function($query)use($project){

  return $query->where('id',$project->id);

});

});

          $data = $data->with(['contract'=>function($q){



      return $q->with('project');


          }])->with('role')
          
        
    
      
        

      ->withSum(
            ['timesheet_project_personal' => function($q) use($request,$project){
      
      
              if($project->id){
                $q->where('project_id',$project->id);
              }
      
 
           
                  return $q;   
                  
      
          }],
          'time'
        )
      
      ;
          $data = $data->withCount(['Attending_and_leaving as Absence'=> function ($query) {
            return $query->where('absence','!=',null);
           }]);
          
        $data =   $data->paginate(10);
        
          return response()->json(['data'=>$data]);
        }

        
        public function timesheet(){
          $projects = auth()->user()->projectmanager()->select(['id','projectmanager_id','name'])->get();
            
          return view('ProjectManager.timesheet')->with('projects',$projects);
        }
          

        public function projectstimesheetPage(){
          $projects = auth()->user()->projectmanager()->select(['id','projectmanager_id','name'])->get();
            
       
          return view('ProjectManager.summary')->with('projects',$projects);
      }
      

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
 
  


});
$data = $data->with(['project_overall'=>function($q)use($request){
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

}]);

$data =   $data->first();



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
//----------------------- manule attendance -----------------------------------

public function manule_attendance(){
  $projects = auth()->user()->projectmanager()->select(['id','projectmanager_id','name'])->get();
            
       
  return view('ProjectManager.attendance')->with(['projects'=>$projects]);
}

public function attendance_absence_manule(request $request){

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
  
    $totalDuration =  $startTime->diffInMinutes($endTime);
  
   
  

  
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
  
//------------------------------------ laborers ---------------------------------------------

public function index_laborer(){
  $projects = auth()->user()->projectmanager()->select(['id','projectmanager_id','name'])->get();
            
  return view('ProjectManager.Laborers.index')->with('projects',$projects);
}

public function json_laborer(request $request){

  $User = User::query();
  if($request->name){
    $User = $User->where('name', 'LIKE', '%' . $request->name . '%');

  }

  $User = $User->where('id','!=',auth()->user()->id );

  if($request->project_id){

    $User = $User->WhereHas('contract',function($q) use($request){
return $q->where('project_id',$request->project_id);

    })->with(['contract'=>function($q) use($request){

    return $q->where('project_id',$request->project_id)->with('project');

    }]);
 
  }else{
    $array_id = [];

    $projects = auth()->user()->projectmanager()->get();
foreach($projects as $project){

  $array_id [] = [
    $project->id
  ];

}


    $User = $User->WhereHas('contract',function($q) use($request,$array_id){

      return $q->whereIn('project_id',$array_id);
      
          })->with(['contract'=>function($q) use($request){
      
          return $q->where('project_id',$request->project_id)->with('project');
      
          }]);

  }


 $User = $User->select(['id','name','laborer']);
 $User = $User->paginate(10);
  return response()->json(['data'=>$User]);
  
 }

  public function edit_laborer(User $User){
    $data = $User->contract;
    $projects = auth()->user()->projectmanager()->select(['id','projectmanager_id','name'])->get();
            
    return view('ProjectManager.Laborers.update')->with(['data'=>$User,'projects'=>$projects]);
}



    public function create_laborer(){
      $projects = auth()->user()->projectmanager()->select(['id','projectmanager_id','name'])->get();
        
        return view('ProjectManager.Laborers.create')->with(['projects'=>$projects]);
    }

    public function add_laborer(request $request){
        
        $this->validate($request, [
               'name' => ['required', 'string', 'max:255'],
               'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
               'password' => ['required', 'string', 'min:8', 'confirmed'],
   
           ]);
           
   
      $user =      User::create([
          'name' => $request->name,
               'email' => $request->email,
               'password' => Hash::make($request->password),
               'laborer'=>1
         
]);  

/*
contract::insert([
 
    'user_id'=>$user->id,
  'vacations'=>$request->vacations,
   'weekly_vacation'=>$request->weekly_vacation,
   'project_id'=>$request->project_id,
    'contract_date'=>$request->contract_date,
  'contract_ex'=>$request->contract_ex,

'working_hours'=>$request->contract_type,
    
'salary_per_hour'=>$request->salary_per_hour,
'salary_per_month'=>$request->salary_per_month,
'fahther_name'=>$request->fahther_name,
'address'=>$request->address,
'type_of_identity'=>$request->type_of_identity,
'identity'=>$request->identity,
'identity_date'=>$request->identity_date,
'identity_source'=>$request->identity_source,
'build_number'=>$request->build_number ,
'city'=>$request->city,
'street'=>$request->street,
'phone'=>$request->phone,
]);
*/

       }


       public function update_laborer( request $request , User $User ){


        $this->validate($request, [
          'name' => ['required', 'string', 'max:255'],
          'email' => [ 'string', 'email', 'max:255', 'unique:users'],
          'password' => [ 'string', 'min:8', 'confirmed'],

      ]);
      

        
      if($request->name){
        $User-> name = $request->name;
       }
             
                if($request->email){
               $User->email = $request->email;
                }
                
                if($request->password){
                    $User->password = Hash::make($request->password);
                     }

                     $User->save();
/*

$User->contract->update([

  'vacations'=>$request->vacations,
  'weekly_vacation'=>$request->weekly_vacation,
  'project_id'=>$request->project_id,
   'contract_date'=>$request->contract_date,
 'contract_ex'=>$request->contract_ex,

'working_hours'=>$request->contract_type,
   
'salary_per_hour'=>$request->salary_per_hour,
'salary_per_month'=>$request->salary_per_month,
'fahther_name'=>$request->fahther_name,
'address'=>$request->address,
'type_of_identity'=>$request->type_of_identity,
'identity'=>$request->identity,
'identity_date'=>$request->identity_date,
'identity_source'=>$request->identity_source,
'build_number'=>$request->build_number ,
'city'=>$request->city,
'street'=>$request->street,
'phone'=>$request->phone,
     
]);
*/


       }



       
}
