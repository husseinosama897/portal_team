<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\product;
use Validator;
class productController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }
  public function autoCompleteProduct(request $request){

    $product = product::where('name', 'LIKE', '%' . $request->name . '%')->select(['id','name','unit','value'])->get()->take(3);
    return response()->json(['data'=>$product]);
    
   }

  public function edit(product $product){
  
  
          return view('product.edit')->with(['data'=>$product]);   
             
    }

    public function product(){
     
  
   
        return view('product.product');   
      
    }

public function addproduct(request $request){
  

   




if($request->p_account3_id == null && $request->p_account4_id == null ){
  $this->validate($request,[
  
//    'p_account4_id'=>['required','numeric'],
 //   'p_account3_id'=>['required','numeric'],
  ])   ;
  
}

/*

 if($request->img){
  $image_tmp = $request->img;
      // Upload Images after Resize
      $extension = $image_tmp->getClientOriginalExtension();
      $fileName = rand(111,99999).'.'.$extension;
      $image_tmp->move('uploads/product', $fileName);

}else{
  $fileName = null;
}
*/

/*
   $product->inventorym()->attach($request->loaction_inventory,[
  'quantity'=>$request->quantity,
]);
*/

$data = json_decode($request->product,true);



$rules = [
              
           
  "type"  => "required|numeric",
  'name'=> "required|string",

];

$scaling = [];
foreach($data as $dat){
     
  $validator = Validator::make($dat,

      $rules

  );
  
$scaling [] = [
  'name'=>$dat['name'],
  'type'=>$dat['type'],
];


}


product::insert($scaling);

return response()->json('done',200);

}


public function updateproduct(request $request,product $product){
  




if($request->p_account3_id == null && $request->p_account4_id == null && $request->type == 1){
  $this->validate($request,[
  
    'p_account4_id'=>['required','numeric'],
    'p_account3_id'=>['required','numeric'],
  ])   ;
  
}


 if($request->img){
  $image_tmp = $request->img;
      // Upload Images after Resize
      $extension = $image_tmp->getClientOriginalExtension();
      $fileName = rand(111,99999).'.'.$extension;
      $image_tmp->move('uploads/product', $fileName);

}else{
  $fileName = null;
}

 $product->update([
  'name'=>$request->name,
'unit'=>$request->measuring,
  'barcode'=>$request->barcode,
 'dis'=>$request->dis,
 'buy_price'=>$request->buy_price,
 'p_account3_id'=>$request->p_account3_id,
 'p_account4_id'=>$request->p_account4_id,

]);
if($fileName){
  $product->update([
    'img'=>$fileName,
    ]);
}


$pro->inventorym()->sync($request->loaction_inventory,[
  'quantity'=>$request->quantity  ?? 0,
]);
return response()->json('done',200);
  
}


public function editproduct(request $request,product $product){

 $this->validate($request,[
  
  'type'=>['required','numeric','digits_between:1,2'],
    'name'=>['required','string','max:255'],

   'group'=>['required','string','max:255'],

    'barcode'=>['required','string','max:255'],
 
 ])   ;


 
$product->type = $request->type;
$product->name =$request->name;
$product->group=$request->group;
$product->barcode=$request->barcode;
$product->selling = $request->selling;
$product->buy_price = $request->buy_price;

$product->save();

return response()->json('done',200);
  
}


public function producttable(){
 
return view('product.table');
  
}

public function productjson(){
  
$pr = product::orderBy('created_at', 'DESC')->orderBy('created_at','DESC')->paginate(10);
return response()->json(['data'=>$pr]);

  
}

public function productselect(){

$pr = product::orderBy('created_at', 'DESC')->get()->chunk(10);
return response()->json(['data'=>$pr]);

  
}


public function delete($ids){

    $product  = product::whereIn('id',explode(",",$ids))->get();
    foreach($product as $pro){
      if(!empty($pro->inventorym())){
        $pro->inventorym()->detach();
     //   $pro->offerpaid()->detach();
      //  $pro->purchase_order()->detach();
//$pro->showprice()->detach();
//$pro->pricing_supplier()->detach();
//$pro->supplierorder()->detach();
// $pro->supplierorder()->detach();
      
  
    }
   

 $product  =  product::whereIn('id',explode(",",$ids))->delete();
  }
}

}
