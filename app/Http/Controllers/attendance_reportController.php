<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Attendance_report;
use App\Attending_and_leaving;
use DB;
class attendance_reportController extends Controller
{
    public function index(){

        return view('projectmanager.Attendance_report.index');

    }

    public function confirmed(Attendance_report $Attendance_report){


$this->validate($request,[
'rate'=>['required','numeric'],
'note'=>['string'],
 'status'=>['required','numeric'],
]);

$Attendance_report->update([
    'rate'=>$request->rate,
'note'=>$request->note,
 'status'=>$request->status,
]);

if($request->attendacedeleted){
    Attending_and_leaving::where(['date'=>$Attendance_report->date])
    ->WhereIn('id',$request->attendacedeleted)->delete();
}
$update_id = [];
$update_value = [];

foreach($request->updated_attendance as $Attendance_user_updated){

    $update_id[] = [
$Attendance_user_updated['id']
    ];
    $update_value[]=[
        $Attendance_user_updated['value']
    ];


}
if(!empty($update_id)){
    Attending_and_leaving::Wherein(['id'=>$update_id])->update($update_value);

}



$Attendance_report->Attendance_report_user()->detach();

$Attending_and_leaving = json_decode($request->Attending_and_leaving,true);

foreach($Attending_and_leavings as $Attending_and_leaving){

    $Attendance_report_user [] = [
   
       'Attendance_report_id'=>$Attendance_report['pivot']['id']  ?? null,
       'user_id'=>$Attending_and_leaving['pivot']['user_id'] ?? null,
       'hours'=>$Attending_and_leaving['pivot']['time_difference'] ?? null,
       'minute'=>$Attending_and_leaving['pivot']['minute'] ?? null,
    ];
   
       }
   
   
       DB::table('attendance_report_user')->insert($Attendance_report_user);

       

    }

    Public function confirmedpage(){
        
        return view('projectmanager.Attendance_report.confirmed');
    }


    public function json(request $request){


        if(auth()->user()->projectmanager()->count() > 1){
$data = auth()->user()->projectmanager()->where(['id',$request->project])->first();
        }elseif(auth()->user()->projectmanager()->count() == 1 ){
            $data = auth()->user()->projectmanager()->first();   
        }

$data = $data->Attendance_report;


if($request->date){
    $data = $data->where('date',$request->date);

}

if($request->ref){
    $data = $data->where('ref',$request->ref);
}

if($request->rate){
    $data = $data->where('rate',$request->rate);
}

$data = $data->paginate(10);

return response()->json(['data'=>$data]);



    }

}
