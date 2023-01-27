<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\role;
use Illuminate\Support\Facades\Hash;
use App\user_file;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
class userController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    

public function employee_by_role(role $role){

return response()->json(['data'=>$role->user->chunk(30)]);

}

    public function CHunking_user(){
        $data = User::select(['id','name'])->get()->chunk(30);
        return response()->json(['data'=>$data]);
      }

    
    public function autocomplete(request $request){

        $validate = $this->validate($request,[
'name'=>['required','max:255','string']
        ]);
      
        $data = User::where('name', 'LIKE', '%' .$request->name . '%')
        ->select(['name','id','email'])->get()->take(4);
        return response()->json(['data'=>$data]);

    }


    public function jsonUser(request $request){

$this->validate($request,[
    'name'=>['string','max:255'],
    'role_id'=>['numeric'],
    'laborer'=>['numeric'],
    'contract_date' =>['date'],
 'project_id' =>['numeric'],
 'identity_date'=>['date'],
]);

        $data = User::query();
 
        if($request->name){
            $data = $data->where('name','LIKE', '%' .$request->name .'%');
      
        }

        if($request->role_id){
            
            $data = $data->where('role_id',$request->role_id);
        }

        
        if($request->laborer){
            
            $data = $data->where('laborer',$request->laborer);
        }

        if($request->contract_date || $request->project_id || $request->identity_date){
           
            $data = $data->whereHas('contract',function($q)use($request){
                if($request->contract_date){
                    $qr->where('contract_date',$request->contract_date);

                }


                if($request->project_id){
                    $q->whereHas('project',function($query)use($request){
                       
                     return   $query->where('id',$request->project_id);
        
                    });
                }
                

                if($request->identity_date){
                    $qr->where('identity_date','>=',$request->identity_date);
                }
        


            });

        }




   


        $data = $data->with(['contract'=>function($q)use($request){

          
    
    
        
            if($request->identity_date){
                $qr->where('identity_date','>=',$request->identity_date);
            }
    
            if($request->project_id){
            $q->whereHas('project',function($query)use($request){
               
             return   $query->where('id',$request->project_id);

            });
        }

    return $q->with(['project'=>function($qr)use($request){

        if($request->project_id){
            $qr->where('id',$request->project_id);
        }

     
        return $qr;

}]);
        }]);
        
        $data = $data->select(['name','role_id','email','admin','manager','id']);
        
        $data = $data->with('role')->orderBy('created_at','DESC')->paginate(10);

        return response()->json(['data'=>$data]);

    }


    public function role(){
        $role = role::get();
        return response()->json(['data'=>$role]);
    }

    public function rig(){
   return view('managers.adduser');     
    }


   
    
    public function edit(User $User){
        $data = $User->contract;

     
        return view('managers.edituser')->with('data',$User);     
         }

    public function usertable(){
        return view('managers.usertable');     
         }


    public function add(request $request){
        
     $this->validate($request, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

        ]);
        

        
            
        if($request->sign){
          $image_tmp = $request->sign;

              $extension = $image_tmp->getClientOriginalExtension();
              $fileName = rand(111,99999).'.'.$extension;
              $image_tmp->move('uploads/sign', $fileName);
     
      }else{
        $fileName = null;
      
      }


            
      if($request->image){
        $image_tmp = $request->image;

            $extension = $image_tmp->getClientOriginalExtension();
            $fileName_image = rand(111,99999).'.'.$extension;
            $image_tmp->move('uploads/images', $fileName_image);
   
    }else{
      $fileName_image = null;
    
    }





      $user =  User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id'=>$request->role_id,
            'laborer'=>$request->laborer,
            'sign'=>$fileName,
            'image'=>$fileName_image,
            'emp_on'=>Str::random(40),
        ]);  


        
  if($request->count > 0){
    for($counter = 0;  $counter <= $request->count;  $counter++){
     
        $img = 'files-'.$counter;
        
          if($request->$img){
            $image_tmp = $request->$img;
            $fileName = Str::random(40).'.'.$image_tmp->getClientOriginalExtension();
      
            $extension = $image_tmp->getClientOriginalExtension();
                    
            $image_tmp->move('uploads/user_file', $fileName);
   
      $files[] = [
                   'user_id'=>$User->id,
                   'path'=>$fileName,
                  ];
        ++$counter;
        }else{
          $fileName = null;
        
        }
   
   
      }
   
      $chunkfille = array_chunk($files, 3);

      if(!empty($chunkfille)){
          foreach($chunkfille as $chunk){
            user_file::insert($chunk);
          }
             }
             

            }
            

        \App\contract::insert([
 
            'user_id'=>$user->id,
          
           'project_id'=>$request->project_id,
         
       'first_name'=>$request->first_name,
       'Transportation_Allowance'=>$request->Transportation_Allowance,
                'Communication_Allowance'=>$request->Communication_Allowance,
                'Food_Allowance'=>$request->Food_Allowance,
                'Other_Allowance'=>$request->Other_Allowance,
'permit'=>$request->permit,
'salary_per_hour'=>$request->salary_per_hour,
'salary_per_month'=>$request->salary_per_month,
'fahther_name'=>$request->fahther_name,
'address'=>$request->address,
'type_of_identity'=>$request->type_of_identity,
'identity'=>$request->identity,
'identity_date'=>$request->identity_date,
'identity_source'=>$request->identity_source,
'identity_img'=>$request->identity_img,
'build_number'=>$request->build_number ,
'city'=>$request->city,
'street'=>$request->street,
'phone'=>$request->phone,

        ]);

        
    }
    public function update(request $request ,User $User){
        
        $this->validate($request, [
               'name' => ['required', 'string', 'max:255'],
               'email' => [ 'string', 'email', 'max:255', 'unique:users'],
               'password' => [ 'string', 'min:8', 'confirmed'],
   
           ]);
           
   
           
               
           if($request->sign){
             $image_tmp = $request->sign;
   
                 $extension = $image_tmp->getClientOriginalExtension();
                 $fileName = rand(111,99999).'.'.$extension;
                 $image_tmp->move('uploads/sign', $fileName);
      
         }else{
           $fileName = null;
         
         }
   

         if($request->image){
            $image_tmp = $request->image;
    
                $extension = $image_tmp->getClientOriginalExtension();
                $fileName_image = rand(111,99999).'.'.$extension;
                $image_tmp->move('uploads/images/users', $fileName_image);
       
        }else{
          $fileName_image = null;
        
        }
    
    

        
   
   
   if($request->name){
    $User-> name = $request->name;
   }
   if($request->laborer){
    $User->laborer=$request->laborer;
   
   }
            if($request->email){
           $User->email = $request->email;
            }
            
            if($request->password){
                $User->password = Hash::make($request->password);
                 }
                 if($request->role_id){
                    $User->role_id = $request->role_id;
                     }

                     if($fileName){
                    
                        $User->sign = $fileName;

                        
                         }

                         
                     if($fileName_image){
                    
                        $User->image = $fileName_image;

                        
                         }

                     
    
            $User->save();
 if(!empty($User->contract)){
    $User->contract->update([

              
 'first_name'=>$request->first_name,
        'Transportation_Allowance'=>$request->Transportation_Allowance,
                 'Communication_Allowance'=>$request->Communication_Allowance,
                 'Food_Allowance'=>$request->Food_Allowance,
                 'Other_Allowance'=>$request->Other_Allowance,
                 'permit'=>$request->permit,
        'project_id'=>$request->project_id,
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
 }else{
    $d = \App\contract::create([
 
        'user_id'=>$User->id,
      
       'project_id'=>$request->project_id,
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
         


       }


       public function adminornot(User $User){
           if($User->admin == 1){
            $User->admin = 0;
            $User->save();
           }elseif($User->admin == 0){
            $User->admin = 1;
            $User->save();
           }
       }

       
       public function managerornot(User $User){
        if($User->manager == 1){
         $User->manager = 0;
         $User->save();
        }elseif($User->manager == 0){
         $User->manager = 1;
         $User->save();
        }
    }

    public function delete(User $User){
        $User->delete();
    }
}
