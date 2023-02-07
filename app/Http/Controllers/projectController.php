<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\project;
use App\Purchase_order;
use App\petty_cash;
use App\User;
use App\subcontractor;
use App\summaryreport;
class projectController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    


public function projectJson($project){
    if(is_numeric($project)){
        $data = project::where('id',$project)->with(['purchase_order'=>function($q){
    
    
                return $q->where('status',1);
    
    
                    }])->with(['petty_cash'=>function($q){
    
    
                        return $q->where('status',1);
    
    
                    }])->with(['subcontractor'=>function($q){
    
    
                        return $q->where('status',1);
    
    
                    }])->withsum('subcontractor','total')->with(['contract'=>function($q){
            
                        $q->with(['user'=>function($query){
 
                          

                         $query->withSum(
                            ['timesheet_project_personal' => function($q) {
                      
                     
                      /*
                      
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
                            
                           
                      
                            
                         
                                  return $query;   
                                  
                      */
                          }],
                          'time'
                        )
                      
                      ;

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
       
    
}

public function projectPage( ){

    
return view('project.report');

   

}

    public function delete(project $project){
$project->delete();
    }
public function index(){
    return view('project.index');
}

public function json(){
    $data = project::paginate(10);
    return response()->json(['data'=>$data]);
}
public function create(request $request){
$this->validate($request,[
'name'=>['required','string','max:255'],
]);

 $project =  project::create([
    'name'=>$request->name,
    'log'=>$request->log,
    'lat'=>$request->lat,
    'po_budget'=>$request->po_budget,
    'subcontractor_budget'=>$request->subcontractor_budget,
    'petty_cash_budget'=>$request->petty_cash_budget,
    'employee_budget'=>$request->employee_budget,
  'budget'=>$request->budget,
  "bid_value"=>$request->bid_value,
  'duration'=>$request->duration,
'projectmanager_id'=>$request->projectmanager_id,
'receive_date'=>$request->receive_date,
'initial_delivery_date'=>$request->initial_delivery_date,
'final_delivery_date'=>$request->final_delivery_date,
'customer_id'=>$request->customer_id,
]);

$summaryreport=  summaryreport::first();
  $summaryreport->project !== null ?  $summaryreport->incerment('project',1)
->incerment('bid_value_open',$request->bid_value)
: $summaryreport->update([
    'project'=>1,
    'bid_value_open'=>$request->bid_value,
]);



if($request->projectmanager_id){
  $user = User::find($request->projectmanager_id);
  $user->contract()->update([
    'project_id'=>$project->id
  ]);
}

return response()->json(['data'=>$project]);
}

public function update(request $request , project $project){
    $this->validate($request,[
        'name'=>['required','string','max:255'],
        ]);
        
      $project->update([
            'name'=>$request->name,
            'log'=>$request->log,
            'lat'=>$request->lat,
            'budget'=>$request->budget,
            'po_budget'=>$request->po_budget,
            'subcontractor_budget'=>$request->subcontractor_budget,
            'petty_cash_budget'=>$request->petty_cash_budget,
            'employee_budget'=>$request->employee_budget,
            'projectmanager_id'=>$request->projectmanager_id,
            "bid_value"=>$request->bid_value,
            'duration'=>$request->duration,
          'receive_date'=>$request->receive_date,
          'initial_delivery_date'=>$request->initial_delivery_date,
          'final_delivery_date'=>$request->final_delivery_date,
          'customer_id'=>$request->customer_id,
        ]);

        if($request->projectmanager_id){
            $user = User::find($request->projectmanager_id);
            $user->contract()->update([
              'project_id'=>$project->id
            ]);
          }
}

public function edit( project $project){
    return view('project.edit');
}


public function selectproject(){
    $data = project::get()->chunk(10);
    return response()->json(['data'=>$data]);
}
}
