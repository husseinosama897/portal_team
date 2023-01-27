<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\employee;
use Illuminate\Support\Facades\Validator;
use DB;
use App\workflow;
use App\employee_cycle;
use Carbon\Carbon;
use App\notification;
use App\Jobs\sendcc;
use App\Jobs\rolecc;
use App\Events\NotificationEvent;
class employeeController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    
    public function insrting(request $request){
        $data =  $this->validate($request,[
            'project_id'=>['required','numeric'],
            'date'=>['required','date'],
         'subject'=>['required','string','max:255'],
         'ref'=>['string','max:255'],
            'to'=>['string','max:255'],
         'loan_value'=>['numeric'],
         'loan_option'=>['string'],
             ]);
             try{

                DB::transaction(function () use ($request,$data) {
        
$loan_option = $request->loan_option == true ? 1 :0; 

             $employee = employee::create([
                'project_id'=>$request['project_id'],
                'date'=>$request['date'],
            'subject'=>$request['subject'],
            'loan_value'=>$request->loan_value,
            'loan_option'=>$loan_option,
               'user_id'=>  $request->user_id ?? auth()->user()->id,
               'status'=>0,
                'ref'=>$request->ref,
                'to'=>$request['to'],
            'content'=>$request->content,
            ]);

            $workflow = workflow::where('name','employee')->first()->flowworkStep()
            ->first();
           
           
           employee_cycle::insert([
             'step'=>1,
             'status'=>0,
             'flowwork_step_id'=>$workflow->id,
             'role_id'=>$workflow->role_id,
             'employee_id'=>$employee->id
           ]);

           
     foreach( $workflow->role->user as $flow){

        notification::create([

            'type'=>1,
            'read'=>1,
            'name'=>'New Employee Request',
          'user_id_to'=>$flow->id,
             'user_id_from'=>auth()->user()->id,
             
        ]);

        
        $user = $flow;
        $content = 'New Employee Request';
      $managercontent = '';
      $job = (new rolecc($user,$content,$managercontent))->delay(Carbon::now()->addSeconds(90));
     $this->dispatch($job);
     NotificationEvent::dispatch($user->id,$content);

     }
        

            $rules = [
          
       
                'id' => 'required|exists:users,id',
            
            ];
            $users = json_decode($request->users, true);

            if(!empty($users)){
                foreach($users as $user){
             
                    $validator = Validator::make($user,[
                
                        $rules
                
                    ] );
                    if ($validator->passes()) {
                    $employee->mention()->attach([
                $user['id']
                    ]);
                }
                else{
                    $errors  = $validator->errors()->toArray();
                    $data = json_encode($errors);
                  
                      //      throw new CustomException ($data);  
                }
                }  
            }
      

        });

    }
    catch (Exception $e) {
        return $e;
    }
    }


    public function preemployeereturn(request  $request){
   
    
    return view('employee.previewdef');
        
         
        
    
       }

    public function employeereturn( $employee){
        if (is_numeric($employee)){
            $data = employee::where('id',$employee)->with(['employee_cycle'=>function($q){
                return  $q
                ->with(['comment_employee_cycle'=> function($qu){
                    return $qu->with('user');
                }]);
                }])->with('project')->first();
            if(!empty($data)){
             
                return view('employee.preview')->with(['data'=>$data]);
            }
         
        }
    
       }


       public function index(){
 
        return view('employee.index');
    }
 
    public function create(){
        return view('employee.create');
    }


    public function returnasjson(){
     $employee = auth()->user()->employee()->orderBy('created_at','DESC')->with(['employee_cycle'=>function($q){
        return   $q->with('role');
       }])->paginate(10);
     return response()->json(['data'=>$employee]);
    }
 
    public function delete(employee $employee){
        
     if($employee->user_id == auth()->user()->id){
        $employee->delete();
     }
    }


}
