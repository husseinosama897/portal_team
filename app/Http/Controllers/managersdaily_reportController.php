<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\dailyReport;
use App\daily_report_attachment;
use App\daily_productivity;
use Str;

class managersdaily_reportController extends Controller

{

  

public function preview($dailyReport){
if(is_numeric($dailyReport)){

$data = dailyReport::where('id',$dailyReport)->with(['daily_productivity','users','supervisor','project'])->first();

return view('daily_report.print')->with(['data'=>$data]);

}
}



public function index(){
    return view('managers.daily_report.index');
}




public function delete(dailyReport $dailyReport){
$dailyReport->delete();
}





public function json_daily_Report(request $request){
if(empty(auth()->user()->projectmanager)){

  
  $dailyReport = dailyReport::query();
$dailyReport = $dailyReport->where('status',1);

if($request->project_id){
    $dailyReport = $dailyReport->where('project_id',$request->project_id);
}

if($request->from){
    $dailyReport = $dailyReport->where('date','>=',$request->from);
}
if($request->to){
    $dailyReport = $dailyReport->where('date','=<',$request->to);
}

if($request->supervisor_id){
    $dailyReport = $dailyReport->where('supervisor_id',$request->supervisor_id);
}

$dailyReport = $dailyReport->paginate(10);

return response()->json(['data'=>$dailyReport]);


}

}


public function edit(dailyReport $dailyReport){
  
$daily_productivity = $dailyReport->daily_productivity;
$attachments = $dailyReport->attachments;
$users = $dailyReport->users;  

  return view('managers.daily_report.update')->with('data',$dailyReport);

}

public function updating(request $request,dailyReport $dailyReport){

  $this->validate($request,[
      'project_id'=>['required','numeric'],
      'date'=>['required','date'],
      'The_scope_of_work'=>['required','string'],
  'workplace'=>['required','numeric'],
    'number_of_staff'=>['required','numeric'],
  ]);
  
  
  $dailyReport->update([
      'project_id'=>$request->project_id,
      'date'=>$request->date,
      'ref'=>$request->ref,
      'The_scope_of_work'=>$request->The_scope_of_work,
  'workplace'=>$request->workplace,
  'note'=>$request->note,
  'status'=>$request->status,
    'number_of_staff'=>$request->number_of_staff,
  'contentmanager'=>$request->contentmanager,
  ]);
  

  daily_productivity::where('daily_report_id',$dailyReport->id)->delete();
   

  $data =  json_decode($request->daily_productivities,true);
  
  $scaling = [];
  
  foreach($data as $dat){
  $scaling[] = [
      'daily_report_id'=>$dailyReport->id,
      'item'=>$dat['item'],
      'quantity'=>$dat['quantity'],
      'unit'=>$dat['unit'],
  ];
  
  
  
  }
  
  $array_chunk = array_chunk($scaling,10);
  
  foreach($array_chunk as $array){
      daily_productivity::insert($array);
  }
  
  
  if($request->count > 0){
      for($counter = 0;  $counter <= $request->count;  $counter++){
       
          $img = 'files-'.$counter;
          
            if($request->$img){
              $image_tmp = $request->$img;
              $fileName = Str::random(40).'.'.$image_tmp->getClientOriginalExtension();
        
              $extension = $image_tmp->getClientOriginalExtension();
                      
              $image_tmp->move('uploads/dailyReport', $fileName);
     
        $files[] = [
                     'daily_report_id'=>$dailyReport->id,
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
              daily_report_attachment::insert($chunk);
            }
               }
               
     }
  
  
  
  }
  
  

  
}
