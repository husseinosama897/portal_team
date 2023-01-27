<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\role;
use App\permission;
use App\section;
class RoleController extends Controller
{

    public function rolechunk(){
$data = role::get()->chunk(10);
        return response()->json(['data'=>$data]);
    }

    public function create(){
$permission = permission::get();
$section = section::select(['name','id'])->get();
return view('managers.role.create')->with(['data'=>$permission,'section'=>$section]);
    }

    public function edit(role $role){
        $permission = permission::get();

        $section = section::select(['name','id'])->get();
$permissionrole = $role->permission;

 return view('managers.role.update')->with(['data'=>$permission,'role'=>$role,'permissionrole'=>$permissionrole,'section'=>$section]);

    }

    public function insert(request $request){

        $this->validate($request,[
            'name'=>['required','max:255','string'],

        ]);

     $role =    role::create([

        'name'=>$request->name
        ,'section_id'=>$request->section_id
        ]);


       $json = json_decode($request->permission,true);
        $role->permission()->attach($json);

    }



    public function update(request $request,role $role){

        $this->validate($request,[
            'name'=>['required','max:255','string'],
            'section_id'=>['required','max:255','numeric'],

        ]);

     $role->update([

        'name'=>$request->name
        ,'section_id'=>$request->section_id
        ]);


       $json = json_decode($request->permission,true);
        $role->permission()->sync($json);

    }



    public function index(){
        return view('managers.role.index');
    }

    public function json(){
        $data = role::with('section')->paginate(10);
        return response()->json(['data'=>$data]);
    }
}
