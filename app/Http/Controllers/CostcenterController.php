<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\cost_center;
class CostcenterController extends Controller
{
    public function index(){
        return view('managers.costcenter.index');

    }

    public function create(){
        return view('managers.costcenter.create');

    }
    public function edit(cost_center $cost_center){
        return view('managers.costcenter.edit');
        
    }

    public function record(request $request){

$this->validate($request,[
'project_id'=>['required','numeric'],
'budget'=>['required','numeric'],
'name'=>['required','string'],
]);

$cost_center = cost_center::create([
    'project_id'=>$request->project_id,
    'budget'=>$request->budget,
    'name'=>$request->name,
]);

$cost_center->project->budget += $request->budget;
$cost_center->project->save();




    }
    public function update(request $request,cost_center $cost_center){

        $this->validate($request,[
        'project_id'=>['required','numeric'],
        'budget'=>['required','numeric'],
        'name'=>['required','string'],
        ]);
        $cost_center->project->budget -= $cost_center->budget;
$cost_center->project->save();
        
      $cost_center->update([
            'project_id'=>$request->project_id,
            'budget'=>$request->budget,
            'name'=>$request->name,
        ]);
        
        
        $cost_center->project->budget += $request->budget;
$cost_center->project->save();

            }

            public function jsoncostcenter(){
                $cost = cost_center::with('project')->paginate(10);
                return response()->json(['data'=>$cost]);
            }
            public function getCostCenter(request $request ){

                $cost_center = cost_center::where('project_id',$request->project_id)->get();

                return response()->json(['data'=>$cost_center]);

            }


            public function getCostCenterSelectBoxwithoutprameter(request $request ){

                $cost_center = cost_center::get()->chunk(10);

                return response()->json(['data'=>$cost_center]);

            }
}


