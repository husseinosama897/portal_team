<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\contractor;
use App\contractor_attachment;
use DB;
class contractorController extends Controller
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
        
       
               
    
        if($request->count > 0){
          for($counter = 0;  $counter <= $request->count;  $counter++){
           
              $img = 'files-'.$counter;
              
                if($request->$img){
                  $image_tmp = $request->$img;
                  $fileName = Str::random(40).'.'.$image_tmp->getClientOriginalExtension();
            
                  $extension = $image_tmp->getClientOriginalExtension();
                          
                  $image_tmp->move('uploads/contractor', $fileName);
         
            $files[] = [
                         'contractor_id'=>$contractor->id,
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
                  contractor_attachment::insert($chunk);
                }
                   }
                   
         }

     

        
        return response()->json('done',200);
           
            }
            public function addcpage(){
            
           
                return view('managers.contractor.addcontractor');
                
            }
        
          
        
            public function index(){
           
            
                    return view('managers.contractor.index');
                
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
        
                return view('managers.contractor.update')->with('data',$contractor);
        
        
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
        
              
        
                          public function profile( $contractor)
{


  $contractor = contractor::where('id',$contractor)->with(
    'file','cws',
  )->first();



  return view('managers.contractor.profile')->with(['data'=>$contractor]);

}


}
