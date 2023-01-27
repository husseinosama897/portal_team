<?php

namespace App\Http\Controllers;
use Validator;
use Illuminate\Http\Request;
use App\supplierorder;
use DB;
use App\Exceptions\CustomException;
class supplierorderController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function supplierordercreate(){
  
return view('managers.supplierorder.create');

        
    }



    public function supplierorderindex(){
      
            return view('managers.supplierorder.index');


        
    }


    public function insert(request $request){
    
            $this->validate($request,[
                'code'=>['string','required','max:255'],
             //   'supplier_id'=>['required','numeric'],
            //  'fq'=>['string'],
            ]);

            try{
 
                DB::transaction(function () use ($request) {

            if($request->isedit == true){

 $supplierorder =     supplierorder::where('id',$request->s_id)->first();

 $supplierorder->update([
    'code'=>$request->code,
    'supplier_id'=>$request->supplier_id,
'fq'=>$request->fq,
'orderpackage_id'=>auth()->user()->orderpackage->id,
]);


            }
     else{


 $supplierorder   =    supplierorder::create([
            'code'=>$request->code,
         
        ]);

    
    }
$supplierorder->product()->detach();

$rules = [
    "quantity"  => "required|numeric",
   'id'=> 'required','numeric','exists:products,id',

   
];
        $product = json_decode($request->product, true);
        foreach($product as $v){
      
            $array = [
                "quantity"  => $v['quantity'] ?? null ,
               'id'=> $v['id'] ?? null,
       
               
            ];
              $validator = Validator::make(
          $array,$rules
          
            );
          if ($validator->passes() ) {

            $supplierorder->product()->attach($v['id'], [
               
                'quantity' => $v['quantity'],
            ]);
        }else{
     
            $errors  = $validator->errors()->toArray();
            $data = json_encode($errors);
          
                    throw new CustomException ($data);
    
        }

    }
 
    });
 


}
catch (Exception $e) {
    return $e;
}


    
    }


    public function supplierorderjson(){
      
return response()->json(['data'=>supplierorder::orderBy('created_at', 'DESC')->with('supplier')->paginate(10)]);

   
    }


     
    public function delete($ids){
   
          supplierorder::whereIn('id',explode(",",$ids))->delete();
        
      }



      public function getselectsupplierorder(request $request){
       
        $supplierorder = supplierorder::where('code', 'LIKE', '%' . $request->code . '%')->get()->take(5);
        return response()->json(['data'=>$supplierorder]);
        
       }


       public function supplierorderselectright($id){
     
            $data =     supplierorder::where('id',$id)->with(['product','supplier'])->first();
     
            return response()->json(['data'=>$data]);
 
       }
       public function supplierorderselectright_edit($id){
     
            $data =    supplierorder::where('id',$id)->with(['product','supplier'])->first();
       
            return view('supplierorder.create')->with(['editdata'=>$data]);
       
       
    }

      public function updatesupplierorder(request $request,supplierorder $supplierorder ){
 
  $supplierorder->update([
    
    'supplier_id'=>$request->supplier_id,

]);
              
              return response()->json('done',200);
        
    }


                  // Supplier order Print   -Index-

    public function sp_print($id){

            $data = supplierorder::where('code',$id)->with(['product','Supplier'])->first();
         

            return view('print.supplierrequest')->with(['data'=>$data]);


    }
    public function supplierorder_count(){

          return response()->json(['data'=>count(auth()->user()->orderpackage->supplierorder()->get())]);
     
    }
    

}
