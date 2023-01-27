<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\contractor;
use DB;
class contractor_employee extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

  
    public function CHunking_contractor_data(){
      $data = contractor::select(['id','comp','contractor_name'])->get()->chunk(30);
      return response()->json(['data'=>$data]);
    }


    public function contractor(request $request ){
  
     
        $this->validate($request,[
            'personal'=>['numeric','digits_between:1,2'],
           
            'country'=>['string','max:255'],
           
        
        'contractor_name'=>['string','max:255'],
            
        
        
           'postal_code'=>['string','max:255'],
           'building_num'=>['string','max:255'],
           'street_name'=>['string','max:255'],
        
        'country'=>['string','max:255'],
        
        'phone'=>['string','max:255'],
        'location'=>['string','max:255'],
        'city'=>['string','max:255'],
        
        'email'=>['string','max:255'],
        
        ]);
        
        
        if($request->personnal == 2){
          $this->validate($request,[
          'comp'=>['string','max:255'],
          'representative'=>['string','max:255'],
          'tax_number'=>['string','max:255'],
        
          ]);
        }
        
          $contractor =  contractor::create([
            'personal'=>$request->personal,
          'contractor_name'=>$request->contractor_name,
          'status'=>1,
          'comp'=>$request->comp,
        'country'=>$request->country,
            'postal_code'=>$request->postal_code,
            'building_num'=>$request->building_num,
            'street_name'=>$request->street_name,
        'tax_number'=>$request->tax_number,
        'phone'=>$request->phone,
        'location'=>$request->location,
        'city'=>$request->city,
        'email'=>$request->email,
        ]);
        
       
               
    
        
        
        return response()->json('done',200);
           
            }
            public function addcpage(){
            
           
                return view('contractor.addcontractor');
                
            }
        
          
        
            public function index(){
           
            
                    return view('contractor.index');
                
            }
        

            
public function getselectboxcontractor(request $request){

    $contractor =  contractor::query();
    
    
    $contractor =  $contractor->where('contractor_name', 'LIKE', '%' . $request->name . '%');
    
  
  $contractor = $contractor->orwhere('comp', 'LIKE', '%' . $request->name . '%');
    
    
  
    $contractor =  $contractor->get()->take(3);
  
    return response()->json(['data'=>$contractor]);
    
    
   }

   

            public function contractorjson(){
                
              $pr = contractor::paginate(10);
              return response()->json(['data'=>$pr]);
              
              
                
              }
        
        
              
            public function contractordebetor(){
            
            $pr = contractor::get()->chunk(10);
            return response()->json(['data'=>$pr]);
            
            
              
            }
        
        
              public function delete($ids){
           
                 contractor::whereIn('id',explode(",",$ids))->delete();
                
              }
              public function updatecontractorpage(contractor $contractor ){
        
                return view('contractor.update')->with('data',$contractor);
        
        
              }
        
              public function updatecontractor(request $request,contractor $contractor ){
          
                      $this->validate($request,[
                          
                          'status'=>['numeric','digits_between:1,2'],
                          'email'=>['string','max:255'],   
                          'country'=>['string','max:255'],
                        'contractor_name'=>['string','max:255'],
                        
                      ]);
                      
                      
                      $contractor->update([
                        'personal'=>$request->personal,
                        'contractor_name'=>$request->contractor_name,
                        'status'=>$request->status,
                        'comp'=>$request->comp,
                      'country'=>$request->country,
                          'postal_code'=>$request->postal_code,
                          'building_num'=>$request->building_num,
                          'street_name'=>$request->street_name,
                      'tax_number'=>$request->tax_number,
                      'phone'=>$request->phone,
                      'location'=>$request->location,
                      'city'=>$request->city,
                      'email'=>$request->email,
                      ]);
                      
                      return response()->json('done',200);
                    
                          }
        
              
        



}
