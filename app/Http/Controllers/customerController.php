<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\customer;

class customerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

  
    public function CHunking_customer_data(){
      $data = customer::select(['id','comp','customer_name'])->get()->chunk(30);
      return response()->json(['data'=>$data]);
    }

    public function customer(request $request ){
  
     
$this->validate($request,[
    'personal'=>['numeric','digits_between:1,2'],
   
    'country'=>['string','max:255'],
   

'customer_name'=>['string','max:255'],
    


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

  $customer =  customer::create([
    'personal'=>$request->personal,
  'customer_name'=>$request->customer_name,
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

$project = json_decode($request->project, true);
$data = [];
if(!empty($project)){
  foreach($proj as $project){
     
    $data[] = [
      'name'=>$proj['name'],
      "bid_value"=>$proj['bid_value'],
      'duration'=>$proj['duration'],
'receive_date'=>$proj['receive_date'],
'initial_delivery_date'=>$proj['initial_delivery_date'],
'final_delivery_date'=>$proj['final_delivery_date'],
'budget'=>$proj['budget'],
'po_budget'=>$proj['po_budget'],
'subcontractor_budget'=>$proj['subcontractor_budget'],
'petty_cash_budget'=>$proj['petty_cash_budget'],
'employee_budget'=>$proj['employee_budget'],

'customer_id'=>$customer->id,
    ];

}

}

$chunkfille = array_chunk($data, 3);
     
if(!empty($chunkfille)){
    foreach($chunkfille as $chunk){
      project::insert($chunk);
    }
       }



return response()->json('done',200);
   
    }
    public function addcpage(){
    
   
        return view('managers.customer.addcustomer');
        
    }

  

    public function index(){
   
   
            return view('managers.customer.index');
        
    }

    public function customerjson(){
        
      $pr = customer::paginate(10);
      return response()->json(['data'=>$pr]);
      
      
        
      }


      
    public function customerdebetor(){
    
    $pr = customer::get()->chunk(10);
    return response()->json(['data'=>$pr]);
    
    
      
    }


      public function delete($ids){
   
         customer::whereIn('id',explode(",",$ids))->delete();
        
      }
      public function updatecustomerpage(customer $customer ){

        return view('managers.customer.update')->with('data',$customer);


      }

      public function updatecustomer(request $request,customer $customer ){
  
              $this->validate($request,[
                  
                  'status'=>['numeric','digits_between:1,2'],
                  'email'=>['string','max:255'],   
                  'country'=>['string','max:255'],
                'customer_name'=>['string','max:255'],
                
              ]);
              
              
              $customer->update([
                'personal'=>$request->personal,
                'customer_name'=>$request->customer_name,
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
