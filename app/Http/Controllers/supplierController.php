<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\supplier;
class supplierController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }


  public function CHunking_supplier(){
    $data = User::select(['id','name'])->get()->chunk(30);
    return response()->json(['data'=>$data]);
  }

public function suppilercount(){

    $supplier = supplier::count();

return response()->json(['data'=>$supplier]);
    

  
}

public function getselectboxsupp(request $request){

  $supplier =  supplier::query();
  
  
  $supplier =  $supplier->where('customer_name', 'LIKE', '%' . $request->name . '%');
  

$supplier = $supplier->orwhere('comp', 'LIKE', '%' . $request->name . '%');
  
  

  $supplier =  $supplier->get()->take(3);

  return response()->json(['data'=>$supplier]);
  
  
 }

   public function createpage(){

      return view('managers.supplier.create');
    
   }


   public function createsupp(request $request ){
 
      $this->validate($request,[
          'personal'=>['numeric','digits_between:1,2'],
        'country'=>['string','max:255'],
       
       'comp'=>['string','max:255'],
   'customer_name'=>['string','max:255'],
        
     'tax_number'=>['string','max:255'],
      
   

       'postal_code'=>['string','max:255'],
       'building_num'=>['string','max:255'],
       'street_name'=>['string','max:255'],
   'tax_number'=>['string','max:255'],
   'country'=>['string','max:255'],
   'representative'=>['string','max:255'],
   'phone'=>['string','max:255'],
   'location'=>['string','max:255'],
   'city'=>['string','max:255'],
   
   'user_name' => ['required', 'string', 'max:255'],
   'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
   'password' => ['required', 'string', 'min:8', 'confirmed'],
      ]);
      

      
    $supplier =   supplier::create([
          'personal'=>$request->personal,
          
        'customer_name'=>$request->customer_name,
        'status'=>1,
        
      'comp'=>$request->comp,
          'postal_code'=>$request->postal_code,
          'building_num'=>$request->building_num,
          'street_name'=>$request->street_name,
      'tax_number'=>$request->tax_number,
      'country'=>$request->country,
      'representative'=>$request->representative,
      'phone'=>$request->phone,
      'location'=>$request->location,
      'city'=>$request->city,
   
      'vat'=>$request->vat,
      'email'=>$request->email,
      ]);

      
  $user =  User::create([
    'name' => $request->user_name,
    'email' => $request->email,
    'password' => Hash::make($request->password),
    'supplier_id'=>$supplier->id,

]);  
      
      return response()->json('done',200);
    
          }
          public function suppliertable(){
      
          return view('managers.supplier.table');
            
          }
          
          public function supplierjson(){
     
          $pr = supplier::paginate(10);
          return response()->json(['data'=>$pr]);
          
          }

          public function supplierselex(){
       
          $pr = supplier::get()->chunk(10);
          return response()->json(['data'=>$pr]);
          
          
            
          }
          
          
          public function delete($ids){
   
              auth()->user()->orderpackage->supplier()->whereIn('id',explode(",",$ids))->delete();
            
          }

          public function updatesupp(request $request,supplier $supplier ){
       
            
                  $this->validate($request,[
                      
                
                    
                  ]);
                  
                  
                  $supplier->update([
                    'customer_name'=>$request->customer_name,
                    'status'=>$request->status,
                    'country'=>$request->country,
                  'comp'=>$request->comp,
                
                  'email'=>$request->email,
                  ]);
                  
                  return response()->json('done',200);
                
                      }

}
