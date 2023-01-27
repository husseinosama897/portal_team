<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Jobs\qremail;
use Carbon\Carbon;
use App\project;

class qrController extends Controller
{

    

public function find($project){
    if(is_string($project)){
        $project = project::Where('qr',$project)->first();

        return view('printqr')->with(['project'=>$project]);

    }


}

    public function qr(){
        $projects = project::select(['id','projectmanager_id','name','qr'])->with('projectmanager')->get();



foreach($projects as $project){

    $project->update([
    'qr'=>Str::random(40),
]);

    $job = (new qremail($project->projectmanager,$project->qr))->delay(Carbon::now()->addSeconds(90));
    $this->dispatch($job);
}
 

    }
}
