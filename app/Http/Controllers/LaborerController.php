<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\contract;
use Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use DatePeriod;
use DateInterval;
use DateTime;
use Carbon\Carbon;
class LaborerController extends Controller
{

  public function SearchLaborer(request $request){

    $User = User::query();
    if($request->name){
      $User = $User->where('name', 'LIKE', '%' . $request->name . '%');

    }


    if($request->project_id){

      $User = $User->WhereHas('contract',function($q) use($request){
return $q->where('project_id',$request->project_id);

      })->with(['contract'=>function($q) use($request){

      return $q->where('project_id',$request->project_id)->with('project');

      }]);
   
    }

  
      $User = $User->with(['Attending_and_leaving'=>function($q) use($request){
        if($request->from){

       $q->whereDate('attending_time','>=',$request->from);
        }

        if($request->to){

           $q->whereDate('attending_time','<=',$request->to);
            }

         return $q;   

      }]);
   
    
$User = $User->withCount(['Attending_and_leaving as Absence'=> function ($query) {
 return $query->where('absence','!=',null);
}]);

   $User = $User->select(['id','name','laborer'])->withsum('Attending_and_leaving','time_difference')->withsum('Attending_and_leaving','min');
   $User = $User->paginate(10);
    return response()->json(['data'=>$User]);
    
   }

  public function performance(User $User){
    $data = $User->Attending_and_leaving;
    return view('managers.Laborers.performance')->with('user',$User);
  }
    
    public function index(){
        return view('managers.Laborers.index');
    }
  
  public function jsonlaborer(request $request)
  {
    $data = User::query();
    $data = $data->WhereHas('contract',function($q){

      
      });

    $data = $data->with(['contract'=>function($q){
return $q->with('project');
    }])->with('role');
    
  if($request->project_id){
    $data=  $data->withSum(
      ['timesheet_project_personal' => function($q) use($request){


        if($request->project_id){
          $q->where('project_id',$request->project_id);
        }


   
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

  }else{
$data = $data->whereHas('personal_overall',function($q)use( $request){
  
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

      
   
            return $q;   
            

    }],
    'time'
  )

;

$data=  $data->withSum(
  ['personal_overall' => function($q) use($request){


    if($request->project_id){
      $q->where('project_id',$request->project_id);
    }


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

  

        return $q;   
        

}],
'num_of_attendance'
)

;
  }
        $data = $data->withCount(['Attending_and_leaving as Absence'=> function ($query) {
      return $query->where('absence','!=',null);
     }]);
    
if($request->name){
  $data =   $data->where('name', 'LIKE', '%' . $request->name . '%');

}
    

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


  $data =   $data->paginate(10);
  
    return response()->json(['data'=>$data,'weekends'=>$weekends]);
  }




  public function edit(User $User){
    $data = $User->contract;

    return view('managers.Laborers.update')->with('data',$User);
}



    public function create(){
        return view('managers.Laborers.create');
    }

    public function add(request $request){
        
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


       }


       public function update( request $request , User $User ){


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



       }

       public function delete(User $User){
        $User->delete();
       }


       
}
