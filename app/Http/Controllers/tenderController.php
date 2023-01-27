<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\tender_comment;
use App\marketing;
use App\tender_attachment;
use Illuminate\Support\Str;
use App\notification;
use App\section;
use App\Jobs\rolecc;
use App\monthly_section;
use App\personal_overall;
use Carbon\Carbon;
class tenderController extends Controller
{
  public function __construct()
  {
      $this->middleware('auth');
  }

  
    public function index(){
        return view('tender.index');
    }


    public function json(request $request){

$data = marketing::query();


$data = $data->with('user');

if($request->ref){
    $data =    $data->where('ref', 'LIKE', '%' . $request->ref . '%');
    
          }

          
if($request->from || $request->to){
    $data =   $data->whereBetween('date',[$request->from,$request->to]);
    
          }


          if($request->user_id){
            $data =   $data->whereBetween('user_id',$request->user_id);
     
          }
     
          
    
          $data = $data->orderBy('created_at','DESC')->paginate(20);

          return response()->json(['data'=>$data]);
    }


    public function update(marketing $marketing){
        $attachment = $marketing->attachment  ?? '';
        $tender = $marketing->tender_comment  ?? '';
   
        $tender_attachment = !empty($tender->attachment) ?  $tender->attachment : '';
        return view('tender.edit')->with(['data'=>$marketing]);
    }



    public function responses(request $request,marketing $marketing){

        $this->validate($request,[
            'status'=>['required','numeric'],
            'response'=>['string','required'],
            
        ]);


        $marketing->update([
            'status'=>$request->status,
            
        ]);


        
 if($request->deletedfiles){
  tender_attachment::find($request->deletedfiles)->delete();
}


if(!empty($marketing->tender_comment)){
   $marketing->tender_comment->update([
        'content'=>$request->response
    ]);

    $tender = $marketing->tender_comment;
}else{
    $tender =     tender_comment::create([

        'marketing_id'=>$marketing->id,
        'content'=>$request->response,

    ]);
}




        

        $files = [];
        if($request->count > 0){
         for($counter = 0;  $counter <= $request->count;  $counter++){
          
             $img = 'files-'.$counter;
             
               if($request->$img){
                 $image_tmp = $request->$img;
                 $fileName = Str::random(4).'_'.$marketing->subject.'.'.$image_tmp->getClientOriginalExtension();
   
                 $extension = $image_tmp->getClientOriginalExtension();
                         
                 $image_tmp->move('uploads/tender/'.$marketing->ref, $fileName);
        
           $files[] = [
                        'tender_comment_id'=>$tender->id,
                        'path'=>$fileName,
                       ];
             ++$counter;
             }else{
               $fileName = null;
             
             }
        
        
           }
          }
           $chunkfille = array_chunk($files, 3);
           if(!empty($chunkfille)){
               foreach($chunkfille as $chunk){
                tender_attachment::insert($chunk);
               }
                  }



                  $section = section::where(['name'=>'marketing'])->with(['role'=>function($q){
                    return $q->with('user');
                    }])->first();
                    $notification = [];
                    
                    $content = 'tender department response';
                    if(!empty($section->role)){
                      foreach($section->role as $role){
                    if(!empty($role->user)){
                      foreach($role->user as $user){
                      
                        $notification [] =[
                          'type'=>null,
                          'read'=>1,
                          'name'=>'tender department response',
                        'user_id_to'=>$user->id,
                           'user_id_from'=>auth()->user()->id,  
                        ];
                      
                        $job = (new rolecc($user,$content,''))->delay(Carbon::now()->addSeconds(90));
                      $this->dispatch($job);
                    
                      
                      }
                    }
                      
                      
                      
                      }
                      
                    }
                 
                    $array_chunk = array_chunk($notification,3);
                    
                    foreach($array_chunk as $chunk){
                      notification::insert($chunk);
                    }
                    

                    
                    $user=   auth()->user();
                    $update = $user->personal_overall()->whereDate('date',Carbon::now()->startOfMonth())->first();
                 

                    // checking up if this user working at section 
                 
                
                 $section =  section::where('name','Marketing')->select(['name','id'])->first();
                  $monthly_section =     $section->monthly_section()->select(['id','num_marketing_project',
                  'section_id'
                  ])
                  ->where(['date'=>Carbon::now()->startofmonth()])->first();
                    



                    if($update !== null  && !empty($user->role) && !empty($user->role->section)  && 
                    $user->role->section->name == 'tender'){
                    
                     

                      $update->project_pricing !== null ? $update->increment('project_pricing',1)  : $update->update(['project_pricing'=>1]);
                    


                      #     number of projects this user priced / number of projects created by marketing department 
                       $percentage =  $update->project_pricing / $monthly_section->num_marketing_project * 100 ;
                    
                       $update->update(['percentage_section'=>$percentage]);
              
                    
                    }elseif($update == null  && !empty($user->role) && !empty($user->role->section)  && 
                    $user->role->section->name == 'tender'){
                  personal_overall::create([
                    'user_id'=>auth()->user()->id,
                    'date'=>Carbon::now()->startOfMonth()
                  ,
                    'time'=>0,
                    'num_of_performers'=>0,
                    'num_of_attendance'=>0,
                    'percentage_performance'=>0,
                    'percentage_attendance'=>0,
                'percentage_section'=> 1 / $monthly_section->num_marketing_project * 100  ,
                'marketing_project'=>0,
                'cost_reduction'=>0,
                'marketing'=>0,
              'percentage_deal'=>0,
              'deal'=>0,
                  ]);
              
              
                }
              
              
                 // -------------------------- department -------------------------------
              
              
                    if( !empty($user->role) && !empty($user->role->section)  && 
                    $user->role->section->name == 'tender'){
              
                      
                  $monthly_section_st2 = monthly_section::where(['date'=>Carbon::now()->startofmonth(), 'section_id'=>$user->role->section->id])->first();
              
                  if($monthly_section !== null){
               
                                
                   $monthly_section_st2->project_pricing !== null ? $monthly_section_st2->increment('project_pricing',1)  : $monthly_section_st2->update(['project_pricing'=>1]);
               
                     #     number of projects tender department priced / number of projects created by marketing department 

                   $percentage =  $monthly_section_st2->project_pricing / $monthly_section->num_marketing_project * 100 ;

                   $monthly_section_st2->update(['percentage_pricing'=>$percentage]);
               
                   
                  }else{
                   monthly_section::create([
                     'section_id'=>$user->role->section->id,
                     'date'=>Carbon::now()->startOfMonth(),
                     'num_marketing_project'=>0,
                     'percentage_marketing_project'=>0,  
                     'cost_reduction'=>0,
                     'time'=>0,
               'percentage_section'=>0,
               'percentage_marketing_project'=>0,
               'percentage_deal'=>0,
               'percentage_pricing'=>  1 / $monthly_section->num_marketing_project * 100 ,
               'num_deal_marketing'=>0,
               'project_pricing'=>1,
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

    public function deal_done(marketing $marketing){

      $user=   $marketing->user;
      $update = $user->personal_overall()->whereDate('date',Carbon::now()->startOfMonth())->first();

      if($update !== null  && !empty($user->role) && !empty($user->role->section)  && 
      $user->role->section->name == 'Marketing'){
      
        $update->deal !== null ? $update->increment('deal',1)  : $update->update(['deal'=>1]);
      
          #    number of projects deal done

         $percentage =  $update->deal / 2 * 100 ;
      
         $update->update(['percentage_deal'=>$percentage]);

      
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
  'percentage_section'=>0,
  'marketing_project'=>0,
  'cost_reduction'=>0,
  'marketing'=>0,
'percentage_deal'=>1 / 2 * 100,
'deal'=>1,
    ]);


  }


   // -------------------------- department -------------------------------


      if( !empty($user->role) && !empty($user->role->section)  && 
      $user->role->section->name == 'Marketing'){

        
    $monthly_section = monthly_section::where(['date'=>Carbon::now()->startofmonth(), 'section_id'=>$user->role->section->id])->first();

    if($monthly_section !== null){
 
 
     $monthly_section->num_deal !== null ? $monthly_section->increment('num_deal',1)  : $monthly_section->update(['num_deal'=>1]);
 
     $percentage =  $monthly_section->num_deal / 2 * 100 ;
  
     $monthly_section->update(['percentage_deal'=>$percentage]);
 
     
    }else{
     monthly_section::create([
       'section_id'=>$user->role->section->id,
       'date'=>Carbon::now()->startOfMonth(),
       'num_marketing_project'=>0,
       'percentage_marketing_project'=>0,  
       'cost_reduction'=>0,
       'time'=>0,
 'percentage_section'=>0,
 'percentage_marketing_project'=>0,
 'percentage_deal'=>1 / 2 * 100,
 'num_deal'=>1,
 'num_of_attendance'=>0,
 'num_of_performers'=>0,
 'percentage_attendance'=>0,
 'percentage_performance'=>0,
       'saving_percentage'=>0,
       'cost_reduction'=>0,
     ]);
    }




      $this->tenderDeal($marketing);


    }

  }


protected function tenderDeal($marketing){
    $user=   $marketing->priced_by;
                    $update = $user->personal_overall()->whereDate('date',Carbon::now()->startOfMonth())->first();
                 

                    // checking up if this user working at section 
                 
                
                 $section =  section::where('name','Marketing')->select(['name','id'])->first();
                  $monthly_section =     $section->monthly_section()->select(['id','num_deal',
                  'section_id'
                  ])
                  ->where(['date'=>Carbon::now()->startofmonth()])->first();
                    



                    if($update !== null  && !empty($user->role) && !empty($user->role->section)  && 
                    $user->role->section->name == 'tender'){
                    
                     

                      $update->project_pricing !== null ? $update->increment('deal',1)  : $update->update(['deal'=>1]);
                      #     number of projects tender department priced / number of projects created by marketing department 
                       $percentage =  $update->deal / $monthly_section->num_deal * 100 ;
                    
                       $update->update(['deal_percentage'=>$percentage]);
              
                    
                    }elseif($update == null  && !empty($user->role) && !empty($user->role->section)  && 
                    $user->role->section->name == 'tender'){
                  personal_overall::create([
                    'user_id'=>$user->id,
                    'date'=>Carbon::now()->startOfMonth()
                  ,
                    'time'=>0,
                    'num_of_performers'=>0,
                    'num_of_attendance'=>0,
                    'percentage_performance'=>0,
                    'percentage_attendance'=>0,
                'deal_percentage'=> 1 / $monthly_section->num_deal * 100  ,
                'deal'=>1,
                'marketing_project'=>0,
                'cost_reduction'=>0,
                'marketing'=>0,
              'percentage_deal'=>0,
              'deal'=>0,
                  ]);
              
              
                }
              
              
                 // -------------------------- department -------------------------------
              
              
                    if( !empty($user->role) && !empty($user->role->section)  && 
                    $user->role->section->name == 'tender'){
              
                      
                  $monthly_section_st2 = monthly_section::where(['date'=>Carbon::now()->startofmonth(), 'section_id'=>$user->role->section->id])->first();
              
                  if($monthly_section_st2 !== null){
               
               
                   $monthly_section_st2->num_deal !== null ? $monthly_section_st2->increment('num_deal',1)  : $monthly_section_st2->update(['num_deal'=>1]);
           
                  }else{
                   monthly_section::create([
                     'section_id'=>$user->role->section->id,
                     'date'=>Carbon::now()->startOfMonth(),
                     'num_marketing_project'=>0,
                     'percentage_marketing_project'=>0,  
                     'cost_reduction'=>0,
                     'time'=>0,
               'percentage_section'=>0,
               'percentage_marketing_project'=>0,
               'num_deal'=>1,
               'project_pricing'=>1,
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
