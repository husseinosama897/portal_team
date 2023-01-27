<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\site;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;
use App\notification;
use App\workflow;
use Illuminate\Support\Facades\Storage;
use App\site_cycle;
use App\Exceptions\CustomException;
use App\comment_site_cycle;
use App\Jobs\sendcc;


use App\attachment_site_cycle;
class sitemanagerController extends Controller


{

    public function __construct()
    {
        $this->middleware('auth');
    }
    
    

    public function index(){
        return view('managers.site.index');
    }
    
    public function update( $site){
     
        if (is_numeric($site)){
            $data = site::where('id',$site)->with(['site_cycle'=>function($q){
                return  $q
                ->with(['comment_site_cycle'=> function($qu){
                    return $qu->with('attachment_site_cycle');
                }])->with('role');
                }])->first();
            if(!empty($data)){
        return view('managers.site.update')->with('data',$data);
            }
        }
    }
    
      public function action(request $request,site $site){
      
    $data =  $this->validate($request,[
       'project_id'=>['required','numeric'],
       'date'=>['required','date','max:255'],
    'subject'=>['required','string','max:255'],
    'status'=>['required','numeric','between:1,2'],
       'to'=>['string','max:255'],
  
        ]);
        try{
    
            DB::transaction(function () use ($request,$data,$site) {
    
        $site->update([
            'project_id'=>$request->project_id,
    
        
            'status'=>0,
  
            'content'=>$request->content,
           'date'=>$request->date,
        
            'subject'=>$request->subject,
        
'ref'=>$request->ref,

            'to'=>$request->to,
    
        ]);
    
     

        $site_cycle =  $site->site_cycle()->orderBy('id', 'DESC')->first();
        if($site_cycle->status == 0){
        $site_cycle->status = $request->status;
        $site_cycle ->save();
        $perv = workflow::where(['name'=>'site_request'])->first()->flowworkStep()->where(['step'=> $site_cycle->step])
        ->first();

        if($request->status == 1){
      

            $workflow = workflow::where(['name'=>'site_request'])->first()->flowworkStep()->where(['step'=> $site_cycle->step+1])
            ->first();

            $content   = 'Your site' .$site->ref.'has been approved by'.$perv->role->name ?? ''.' and Under Review from '.$workflow->role->name ?? 'no one';

            foreach($workflow->role->user as $user){
                dispatch_now(new sendcc($user,$content,$request->contentmanager));
               }
        
             
            dispatch_now(new sendcc($site->user,$content,$request->contentmanager));
           
            notification::create([
                'type'=>7,
                'read'=>1,
                'name'=>$content,
              'user_id_to'=>$site->user_id,
                 'user_id_from'=>auth()->user()->id,  
            ]);


        
   
            if(!empty($workflow)){
                site_cycle::insert([
                    'step'=>$site_cycle->step + 1,
                    'status'=>0,
                    'flowwork_step_id'=>$workflow->id,
                    'role_id'=>$workflow->role_id,
                    'site_id'=>$site->id
                ]);
        
            }
            elseif(empty($workflow)  ){
                $site->update([
'status'=>1,
                ]);
            }

        }elseif($request->status == 2){

            $site->update([
                'status'=>2,
                                ]);


                                $content   = 'Your site request' .$site->ref.'has been rejected by'.$perv->role->name ;

                                dispatch_now(new sendcc($site->user,$content,$request->contentmanager));
                    
                                notification::create([
                                    'type'=>7,
                                    'read'=>1,
                                    'name'=>$content,
                                  'user_id_to'=>$site->user_id,
                                     'user_id_from'=>auth()->user()->id,  
                                ]);         

        }
    
        if($request->contentmanager){
            $comment_site_cycle = comment_site_cycle::create([
                'site_cycle_id'=>$site_cycle->id,
                'content'=>$request->contentmanager ?? 'No Comment',
                'user_id'=>auth()->user()->id,
            ]);
            
        }

        $files = [];

      if(!empty($comment_site_cycle)){
          if($request->count > 0){
        for($counter = 0;  $counter <= $request->count;  $counter++){
        
            $img = 'files-'.$counter;
            
              if($request->$img){
                $image_tmp = $request->$img;
                
                $fileName = 'site_'.'_'.'code_'.'' .$site->id. Carbon::now().'_step_'.$site_cycle->step;
                Storage::disk('google')->put($fileName
                 ,file_get_contents($image_tmp));


                    $extension = $image_tmp->getClientOriginalExtension();
                    $fileName = rand(111,99999).'.'.$extension;
                    $image_tmp->move('uploads/site', $fileName);
            ++$counter;
            }else{
              $fileName = null;
            
            }
         
            $files[] = [
             'comment_site_cycle_id'=>$comment_site_cycle->id,
             'path'=>$fileName,
            ];
            
            }
      }
     
            $chunkfille = array_chunk($files, 3);
            if(!empty($chunkfille)){
                foreach($chunkfille as $chunk){
                    attachment_site_cycle::insert($chunk);
                    
                   }
            }
        }
    }
         

    });
    
        }
        catch (Exception $e) {
            return $e;
        }
      }
    
      public function sitereturn( $site){
        if (is_numeric($site)){
        $data = site::where('id',$site)->with(['site_cycle'=>function($q){
            return  $q
            ->with(['comment_site_cycle'=> function($qu){
                return $qu->with('user');
            }]);
            }])->with('project')->first();
        if(!empty($data)){
        return view('managers.site.preview')->with(['data'=>$data]);
        }
        }
         }
      
      
    
       
    
         public function returnasjson(){
          $purchase = auth()->user()->role->site_cycle()->with('site')->orderBy('created_at','DESC')->paginate(10);
          return response()->json(['data'=>$purchase]);
         }
      
         public function delete(site $site){
             if($site->user_id == auth()->user()->id){
                $site->delete();
             }
           
         }
    
    }
    
