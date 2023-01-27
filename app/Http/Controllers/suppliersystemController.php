<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Purchase_order;
class suppliersystemController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index(){
        return view('suppliersystem.index');
    }

    public function json(request $request){
        $this->validate($request,[
            'from'=>['date'],
            'to'=>['date'],
            'ref'=>['string','max:255']
        ]);

        $data = auth()->user()->suppliersystem()->purchase_order();

        if($request->ref){
        $data =     $data->where('ref', 'LIKE', '%' . $request->ref . '%');
            
                  }

        if($request->from || $request->to){

            $data = $data->whereBetween('date',[$request->from,$request->to]);
        }


$data = $data->paginate(10);

return response()->json(['data'=>$data]);

    }


    public function print( $Purchase_order){
        //  if (is_numeric($Purchase_order)  && auth()->user()->role()->permission->where('name','preview po')->first()){
              $data = Purchase_order::where('id',$Purchase_order)->with(['attributes','note'])
             ->with(['attributes2'=>function($q){
                  return $q->where('product_id','=',null);
              }])
             ->with('project')->first();
              if(!empty($data)   && $data->supplier_id == auth()->user()->supplier_id){
                  return view('suppliersystem.preview')->with(['data'=>$data]);
              }
           
         // }
      
         }
      
      
    
           
   public function update( $Purchase_order){
    //    if (is_numeric($Purchase_order) && auth()->user()->role()->permission->where('name','edit po')->first()  ){
        $data = Purchase_order::where('id',$Purchase_order)
  ->with(['attributes'])->with(['attributes2'=>function($q){
                    return $q->where('product_id','=',null);
                }])->with('note')->first();
            if(!empty($data)    && $data->supplier_id == auth()->user()->supplier_id ){
               
                return view('suppliersystem.update')->with(['data'=>$data]);
            }
       // }
    
       }
      
  
      
}
