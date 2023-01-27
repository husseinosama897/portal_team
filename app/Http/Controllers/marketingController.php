<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\marketing;
use App\marketing_attachment;
use DB;
use Illuminate\Support\Str;
use App\notification;
use App\section;
use App\Jobs\rolecc;
use App\monthly_section;
use App\personal_overall;
use Carbon\Carbon;
class marketingController extends Controller
{
    public function add(request $request){

        $this->validate($request,[
            'delivery_date'=>['required','date'],
            'subject'=>['required','string','max:255'],
            'content'=>['string'],
            'date'=>['required','date'],
        ]);

        
    $marketing =    marketing::create([
        'ref'=>$request->ref,
           'delivery_date'=>$request->delivery_date,
        'date'=>$request->date,
           'subject'=>$request->subject,
          'content'=>$request->content,
          'status'=>0,
          'user_id'=>auth()->user()->id,
        ]);

        $update = auth()->user()->personal_overall()->whereDate('date',Carbon::now()->startOfMonth())->first();

      $user=   auth()->user();
if($update !== null  && !empty($user->role) && !empty($user->role->section)  && 
$user->role->section->name == 'Marketing'){


  $update->marketing_project !== null ? $update->increment('marketing_project',1)  : $update->update(['marketing_project'=>1]);

   $percentage =  $update->marketing_project / 20 * 100 ;

   $update->update(['percentage_section'=>$percentage]);

   
  }elseif($update == null  && !empty($user->role) && !empty($user->role->section)  && 
  $user->role->section->name == 'Marketing'){
   

    personal_overall::create([
      'user_id'=>auth()->user()->id,
      'date'=>Carbon::now()->startOfMonth()
    ,
      'time'=>0,
      'num_of_performers'=>0,
      'num_of_attendance'=>0,
      'percentage_performance'=>0,
      'percentage_attendance'=>0,
  'percentage_section'=>1 / 20 *100,
  'marketing_project'=>1,
  'cost_reduction'=>0,
  'marketing'=>0
    ]);


}
 
   
//-------------------------------  department ---------------------------------------

if(!empty($user->role) && !empty($user->role->section)  && 
  $user->role->section->name == 'Marketing'){
$monthly_section = monthly_section::where(['date'=>Carbon::now()->startofmonth(), 'section_id'=>$user->role->section->id])->first();

if($monthly_section !== null){


 $monthly_section->num_marketing_project !== null ? $monthly_section->increment('num_marketing_project',1)  : 
 $monthly_section->update(['num_marketing_project'=>1]);

 $percentage =  $monthly_section->num_marketing_project / 20 * 100 ;

 $monthly_section->update(['percentage_marketing_project'=>$percentage]);

 
}else{
 monthly_section::create([
   'section_id'=>$user->role->section->id,
   'date'=>Carbon::now()->startOfMonth(),
   'cost_reduction'=>0,
   'time'=>0,
'num_marketing_project'=>1,
'percentage_marketing_project'=>1 / 20 * 100,
'percentage_deal'=>0,
'num_deal'=>0,
'num_of_attendance'=>0,
'num_of_performers'=>0,
'percentage_attendance'=>0,
'percentage_performance'=>0,
   'saving_percentage'=>0,
   'cost_reduction'=>0,
 ]);
}
  }
        $files = [];
if($request->count > 0){
 for($counter = 0;  $counter <= $request->count;  $counter++){
  
     $img = 'files-'.$counter;
     
       if($request->$img){
         $image_tmp = $request->$img;
         $fileName = Str::random(4).'_'.$marketing->subject.'.'.$image_tmp->getClientOriginalExtension();
   
         $extension = $image_tmp->getClientOriginalExtension();
                 
         $image_tmp->move('uploads/marketing/'.$marketing->ref, $fileName);

   $files[] = [
                'marketing_id'=>$marketing->id,
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
        marketing_attachment::insert($chunk);
       }
          }
          
}

$section = section::where(['name'=>'tender'])->with(['role'=>function($q){
return $q->with('user');
}])->first();
$notification = [];

$content = 'New project Request';
foreach($section->role as $role){

  foreach($role->use as $user){

    $notification [] =[
      'type'=>null,
      'read'=>1,
      'name'=>'New project Request',
    'user_id_to'=>$user->id,
       'user_id_from'=>auth()->user()->id,  
    ];
  
    $job = (new rolecc($user,$content,''))->delay(Carbon::now()->addSeconds(90));
  $this->dispatch($job);

  
  }


}

$array_chunk = array_chunk($notification,3);

foreach($array_chunk as $chunk){
  notification::insert($chunk);
}


 



    }


    public function index(){
        return view('marketing.index');
    }

    public function create(){
      $data = marketing::latest()->first();
      $explode = explode("-",$data->ref ?? 'M-'.''.'0');
    

        return view('marketing.create')->with(['ref'=>'M-'.''.$explode[1] + 1]);
    }

    public function edit( $marketing){
      
if(is_numeric($marketing)){


  $data = marketing::where('id',$marketing)->with([
    'attachment','tender_comment'=>function($q){
      return $q->with('attachment');
    }
  ])->first();

 
if($data){
  return view('marketing.edit')->with(['data'=>$data]);

}
 
}
    
    }


    public function json(request $request){
        $data =auth()->user()->marketing();

        $data = $data->with('user');

        $data = $data->orderBy('created_at','DESC')->paginate(20);

      return response()->json(['data'=>$data]);

    }

    public function update(request $request,marketing $marketing){


        $this->validate($request,[
            'delivery_date'=>['required','date'],
            'subject'=>['required','string','max:255'],
            'content'=>['string'],
            'date'=>['required','date'],
        ]);

    $marketing->update([
'delivery_date'=>$request->delivery_date,
        'date'=>$request->date,
           'subject'=>$request->subject,
          'content'=>$request->content,
          'status'=>0
        ]);


        if($request->deletedfiles){
            marketing_attachment::find($request->deletedfiles)->delete();
         }
 
         

        $files = [];
if($request->count > 0){
 for($counter = 0;  $counter <= $request->count;  $counter++){
  
     $img = 'files-'.$counter;
     
       if($request->$img){
         $image_tmp = $request->$img;
         $fileName = Str::random(4).'_'.$marketing->subject.'.'.$image_tmp->getClientOriginalExtension();
   
         $image_tmp->move('uploads/marketing/'.$marketing->ref, $fileName);

   $files[] = [
                'marketing_id'=>$marketing->id,
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
        marketing_attachment::insert($chunk);
       }
          }
          
}





    }


    public function delete(marketing $marketing){
      if($marketing->user_id == auth()->user()->id){
        $marketing->delete();


        $update = auth()->user()->personal_overall()->whereDate('date',Carbon::now()->startOfMonth())->first();

        $user=   auth()->user();
  if($update !== null  && !empty($user->role) && !empty($user->role->section)  && 
  $user->role->section->name == 'Marketing'){
  
  
    $update->marketing_project !== null ? $update->decrement('marketing_project',1)  : 
    $update->update(['marketing_project'=>-1]);
  
     $percentage =  $update->marketing_project / 20 * 100 ;
  
     $update->update(['percentage_section'=>$percentage]);
  
     
    }elseif($update == null  && !empty($user->role) && !empty($user->role->section)  && 
    $user->role->section->name == 'Marketing'){
     
  
      personal_overall::create([
        'user_id'=>auth()->user()->id,
        'date'=>Carbon::now()->startOfMonth()
      ,
        'time'=>0,
        'num_of_performers'=>0,
        'num_of_attendance'=>0,
        'percentage_performance'=>0,
        'percentage_attendance'=>0,
    'percentage_section'=>0 ,
    'marketing_project'=>0,
    'deal'=>0,
    'percentage_deal'=>0,
    'cost_reduction'=>0,
    'marketing'=>0
      ]);
  
  
  }
   
     
  //-------------------------------  department ---------------------------------------
  
  if(!empty($user->role) && !empty($user->role->section)  && 
    $user->role->section->name == 'Marketing'){
  $monthly_section = monthly_section::where(['date'=>Carbon::now()->startofmonth(), 'section_id'=>$user->role->section->id])->first();
  
  if($monthly_section !== null){
  
  
   $monthly_section->num_marketing_project !== null ? $monthly_section->decrement('num_marketing_project',1)  :
    $monthly_section->update(['num_marketing_project'=>-1]);
  
   $percentage =  $monthly_section->num_marketing_project / 20 * 100 ;
  
   $monthly_section->update(['percentage_marketing_project'=>$percentage]);
  
   
  }else{
   monthly_section::create([
     'section_id'=>$user->role->section->id,
     'date'=>Carbon::now()->startOfMonth(),
     
    
     
     'cost_reduction'=>0,
     'time'=>0,
  
  'percentage_section'=>0,
  'marketing_project'=>0,
  'percentage_marketing_project'=>0,
  'percentage_deal'=>0,
  'num_deal'=>0,
  'num_of_attendance'=>0,
  'num_of_performers'=>0,
  'percentage_attendance'=>0,
  'percentage_performance'=>0,
     'saving_percentage'=>0,
     'cost_reduction'=>0,
   ]);
  }
    }

      }
    }

}
