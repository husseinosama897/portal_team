<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\workflow;
use App\role;
use App\purchase_order;
use App\purchase_order_cycle;
use App\flowworkStep;
use App\petty_cash_cycle;
use App\service_cycle;
use App\joboffer_cycle;
use App\matrial_request_cycle;
use App\petty_cash;
use App\subcontractor_request_cycle;
use App\subcontractor;
use App\contract_withsubcontractorcycle;
use App\employee_cycle;
use App\pricing_supplier_cycle;

class customizeWorkflowController extends Controller
{

            
// ----------- * * * matrial_request * * * ----------------------------

public function workflowmatrial_request(){
    $workflow = workflow::where('name','matrial_request')->with(['flowworkStep'=>function($q){
   return     $q->with('role');
    }])->first();
  
    

$role = role::get();

return view('managers.workflow.matrial_request')->with(['workflow'=>$workflow,'role'=>$role]);
}


public function updatematrial_requestWorkflow(request $request){

    $workflow = json_decode($request->Workflow, true);
   
$step = 1;
$matrial_request =    workflow::where('name','matrial_request')->with(['flowworkStep'=>function($q){
    return     $q->with('role');
     }])->first();


     foreach($matrial_request->flowworkStep as $pworkflow){
$pworkflow->delete();
     }

     
    foreach($workflow as $work){

$flowworkStep = flowworkStep::create([
'role_id'=>$work['id'],
'step'=>$step,
'work_flow_id'=>$service->id,
]);


matrial_request_cycle::where('step',$step)->update(['role_id'=>$work['id']
,
'flowwork_step_id'=>$flowworkStep->id

]);
$step ++;
    }

}
        
// ----------- * * * employee * * * ----------------------------

public function workflowemployee(){
    $workflow = workflow::where('name','employee')->with(['flowworkStep'=>function($q){
   return     $q->with('role');
    }])->first();
  
    

$role = role::get();

return view('managers.workflow.employee')->with(['workflow'=>$workflow,'role'=>$role]);
}


public function updateemployeeWorkflow(request $request){

    $workflow = json_decode($request->Workflow, true);
   
$step = 1;
$employee =    workflow::where('name','employee')->with(['flowworkStep'=>function($q){
    return     $q->with('role');
     }])->first();


     foreach($employee->flowworkStep as $pworkflow){
$pworkflow->delete();
     }

     
    foreach($workflow as $work){

$flowworkStep = flowworkStep::create([
'role_id'=>$work['id'],
'step'=>$step,
'work_flow_id'=>$service->id,
]);


employee_cycle::where('step',$step)->update(['role_id'=>$work['id']
,
'flowwork_step_id'=>$flowworkStep->id

]);
$step ++;
    }

}



    
// ----------- * * * pricing_supplier * * * ----------------------------

public function workflowpricing_supplier(){
    $workflow = workflow::where('name','pricing_supplier')->with(['flowworkStep'=>function($q){
   return     $q->with('role');
    }])->first();
  
    

$role = role::get();

return view('managers.workflow.pricing_supplier')->with(['workflow'=>$workflow,'role'=>$role]);
}


public function updatepricing_supplierWorkflow(request $request){

    $workflow = json_decode($request->Workflow, true);
   
$step = 1;
$pricing_supplier =    workflow::where('name','pricing_supplier')->with(['flowworkStep'=>function($q){
    return     $q->with('role');
     }])->first();


     foreach($pricing_supplier->flowworkStep as $pworkflow){
$pworkflow->delete();
     }

     
    foreach($workflow as $work){

$flowworkStep = flowworkStep::create([
'role_id'=>$work['id'],
'step'=>$step,
'work_flow_id'=>$service->id,
]);


pricing_suppliercycle::where('step',$step)->update(['role_id'=>$work['id']
,
'flowwork_step_id'=>$flowworkStep->id

]);
$step ++;
    }

}


    
// ----------- * * * cws * * * ----------------------------

public function workflowcws(){
    $workflow = workflow::where('name','cws')->with(['flowworkStep'=>function($q){
   return     $q->with('role');
    }])->first();
  
    

$role = role::get();

return view('managers.workflow.cws')->with(['workflow'=>$workflow,'role'=>$role]);
}


public function updatecwsWorkflow(request $request){

    $workflow = json_decode($request->Workflow, true);
   
$step = 1;
$cws =    workflow::where('name','cws')->with(['flowworkStep'=>function($q){
    return     $q->with('role');
     }])->first();


     foreach($cws->flowworkStep as $pworkflow){
$pworkflow->delete();
     }

     
    foreach($workflow as $work){

$flowworkStep = flowworkStep::create([
'role_id'=>$work['id'],
'step'=>$step,
'work_flow_id'=>$cws->id,
]);


contract_withsubcontractorcycle::where('step',$step)->update(['role_id'=>$work['id']
,
'flowwork_step_id'=>$flowworkStep->id

]);
$step ++;
    }

}



// ----------- * * * service * * * ----------------------------

public function workflowservice(){
    $workflow = workflow::where('name','service')->with(['flowworkStep'=>function($q){
   return     $q->with('role');
    }])->first();
  
    

$role = role::get();

return view('managers.workflow.service')->with(['workflow'=>$workflow,'role'=>$role]);
}


public function updateserviceWorkflow(request $request){

    $workflow = json_decode($request->Workflow, true);
   
$step = 1;
$service =    workflow::where('name','service')->with(['flowworkStep'=>function($q){
    return     $q->with('role');
     }])->first();


     foreach($service->flowworkStep as $pworkflow){
$pworkflow->delete();
     }

     
    foreach($workflow as $work){

$flowworkStep = flowworkStep::create([
'role_id'=>$work['id'],
'step'=>$step,
'work_flow_id'=>$service->id,
]);


service_cycle::where('step',$step)->update(['role_id'=>$work['id']
,
'flowwork_step_id'=>$flowworkStep->id

]);
$step ++;
    }

}





// ----------- * * * joboffer * * * ----------------------------

public function workflowjoboffer(){
    $workflow = workflow::where('name','joboffer')->with(['flowworkStep'=>function($q){
   return     $q->with('role');
    }])->first();
  
    

$role = role::get();

return view('managers.workflow.joboffer')->with(['workflow'=>$workflow,'role'=>$role]);
}


public function updatejobofferWorkflow(request $request){

    $workflow = json_decode($request->Workflow, true);
   
$step = 1;
$joboffer =    workflow::where('name','joboffer')->with(['flowworkStep'=>function($q){
    return     $q->with('role');
     }])->first();


     foreach($joboffer->flowworkStep as $pworkflow){
$pworkflow->delete();
     }

     
    foreach($workflow as $work){

$flowworkStep = flowworkStep::create([
'role_id'=>$work['id'],
'step'=>$step,
'work_flow_id'=>$joboffer->id,
]);


joboffer_cycle::where('step',$step)->update(['role_id'=>$work['id']
,
'flowwork_step_id'=>$flowworkStep->id

]);
$step ++;
    }

}





//-------------------------------       Purchase     -----------------------------------------------



public function updateWorkflow(request $request){

    $workflow = json_decode($request->Workflow, true);
   
$step = 1;
$purchase_order =    workflow::where('name','purchase_order')->with(['flowworkStep'=>function($q){
    return     $q->with('role');
     }])->first();


     foreach($purchase_order->flowworkStep as $pworkflow){
$pworkflow->delete();
     }

     
    foreach($workflow as $work){

$flowworkStep = flowworkStep::create([
'role_id'=>$work['id'],
'step'=>$step,
'work_flow_id'=>$purchase_order->id,
]);


purchase_order_cycle::where('step',$step)->update(['role_id'=>$work['id']
,
'flowwork_step_id'=>$flowworkStep->id

]);
$step ++;
    }

}


    public function workflowPurchase(){
        $workflow = workflow::where('name','purchase_order')->with(['flowworkStep'=>function($q){
       return     $q->with('role');
        }])->first();
      
        

$role = role::get();

return view('managers.workflow.purchase')->with(['workflow'=>$workflow,'role'=>$role]);
    }



//------------------------ petty cash -----------------------------------------------------
    public function workflowpetty_cash(){
        $workflow = workflow::where('name','petty_cash')->with(['flowworkStep'=>function($q){
       return     $q->with('role');
        }])->first();
      
        

$role = role::get();

return view('managers.workflow.petty_cash')->with(['workflow'=>$workflow,'role'=>$role]);
    }



    public function updateWorkflowPetty_cash(request $request){

        $workflow = json_decode($request->Workflow, true);
       
    $step = 1;
    $petty_cash =    workflow::where('name','petty_cash')->with(['flowworkStep'=>function($q){
        return     $q->with('role');
         }])->first();
    
    
         foreach($petty_cash->flowworkStep as $pworkflow){
    $pworkflow->delete();
         }
    
         
        foreach($workflow as $work){
    
    $flowworkStep = flowworkStep::create([
    'role_id'=>$work['id'],
    'step'=>$step,
    'work_flow_id'=>$petty_cash->id,
    ]);
    
    /*
    petty_cash_cycle::where('step',$step)->update(['role_id'=>$work['id']
    ,
    'flowwork_step_id'=>$flowworkStep->id
    
    ]);
    */
    $step ++;
        }
    
    }


    
//------------------------ ** subcontractor **  -----------------------------------------------------
public function workflowsubcontractor(){
    $workflow = workflow::where('name','subcontractor')->with(['flowworkStep'=>function($q){
   return     $q->with('role');
    }])->first();
  
    

$role = role::get();

return view('managers.workflow.subcontractor')->with(['workflow'=>$workflow,'role'=>$role]);
}



public function updateWorkflowsubcontractor(request $request){

    $workflow = json_decode($request->Workflow, true);
   
$step = 1;
$subcontractor =    workflow::where('name','subcontractor')->with(['flowworkStep'=>function($q){
    return     $q->with('role');
     }])->first();


     foreach($subcontractor->flowworkStep as $pworkflow){
$pworkflow->delete();
     }

     
    foreach($workflow as $work){

$flowworkStep = flowworkStep::create([
'role_id'=>$work['id'],
'step'=>$step,
'work_flow_id'=>$subcontractor->id,
]);


subcontractor_request_cycle::where('step',$step)->update(['role_id'=>$work['id']
,
'flowwork_step_id'=>$flowworkStep->id

]);
$step ++;
    }

}


}
