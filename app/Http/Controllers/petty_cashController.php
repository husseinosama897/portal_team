<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\petty_cash;
use App\petty_attr;
use App\notification;
use App\workflow;
use App\flowworkStep;
use Illuminate\Support\Str;
use App\Jobs\sendcc;
use Carbon\Carbon;
use App\Jobs\rolecc;
use App\petty_cash_attachment;
use App\petty_cash_cycle;
use Illuminate\Support\Facades\Validator;
use DB;
use App\Exceptions\CustomException;
use App\Events\NotificationEvent;
use App\project;
use Inertia\Inertia;

class petty_cashController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function edit($petty_cash)
    {

        if (is_numeric($petty_cash)) {
            $data = petty_cash::where('id', $petty_cash)->with(['petty_cash_cycle' => function ($q) {
                return  $q
                    ->with(['comment_petty_cash_cycle' => function ($qu) {
                        return $qu->with('attachment_petty_cash_cycle');
                    }])->with('role');
            }])->with(['attributes', 'petty_cash_attachment'])->first();
            if (!empty($data)) {
                return view('petty_cash.update')->with('data', $data);
            }
        }
    }

    public function update(request $request, petty_cash $petty_cash)
    {

        $data =  $this->validate($request, [
            'project_id' => ['required', 'numeric'],
            'date' => ['required', 'date', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],

            'to' => ['string', 'max:255'],
            'total' => ['required', 'numeric', 'digits_between:1,99999999'],

        ]);
        try {

            DB::transaction(function () use ($request, $data, $petty_cash) {

                $petty_cash->update([
                    'project_id' => $request->project_id,


                    'status' => 0,

                    'expected_amount' => ($request->total + $request->vat) ?? 0,

                    'ref' => $request->ref,

                    'vat' => $request->vat ?? 0,

                    'date' => $request->date,

                    'subject' => $request->subject,

                    'to' => $request->to,

                ]);



                if ($request->deletedfiles) {
                    petty_cash_attachment::find($request->deletedfiles)->delete();
                }



                $petty_cash_cycle =  $petty_cash->petty_cash_cycle()->orderBy('id', 'DESC')->first();
                if ($petty_cash_cycle) {
                    $petty_cash_cycle->update(['status' => 0]);



                    $perv = workflow::where(['name' => 'petty_cash'])->first()->flowworkStep()->where(['step' => $petty_cash_cycle->step])
                        ->first();



                    //



                    foreach ($perv->role->user as $flow) {

                        notification::create([

                            'type' => 3,
                            'read' => 1,
                            'name' => 'petty cash request has been modified',
                            'user_id_to' => $flow->id,
                            'user_id_from' => auth()->user()->id,

                        ]);
                        $user = $flow;
                        $content = 'petty cash request has been modified';
                        $managercontent = '';
                        $job = (new rolecc($user, $content, $managercontent))->delay(Carbon::now()->addSeconds(90));
                        $this->dispatch($job);
                        NotificationEvent::dispatch($user->id, $content);
                    }
                } else {
                    $workflow = workflow::where('name', 'petty_cash')->first()->flowworkStep()
                        ->first();


                    foreach ($workflow->role->user as $flow) {

                        notification::create([

                            'type' => 3,
                            'read' => 1,
                            'name' => 'New petty cash Request',
                            'user_id_to' => $flow->id,
                            'user_id_from' => auth()->user()->id,

                        ]);
                        $user = $flow;
                        $content = 'New petty cash Request';
                        $managercontent = '';
                        $job = (new rolecc($user, $content, $managercontent))->delay(Carbon::now()->addSeconds(90));
                        $this->dispatch($job);
                        //  NotificationEvent::dispatch($user->id,$content);

                    }

                    petty_cash_cycle::insert([
                        'step' => 1,
                        'status' => 0,
                        'flowwork_step_id' => $workflow->id,
                        'role_id' => $workflow->role_id,
                        'petty_cash_id' => $petty_cash->id
                    ]);
                }
                if ($request->count > 0) {
                    for ($counter = 0; $counter <= $request->count; $counter++) {

                        $img = 'files-' . $counter;

                        if ($request->$img) {
                            $image_tmp = $request->$img;
                            $fileName = Str::random(40) . '.' . $image_tmp->getClientOriginalExtension();

                            $extension = $image_tmp->getClientOriginalExtension();

                            $image_tmp->move('uploads/petty_cash', $fileName);

                            $files[] = [
                                'petty_id' => $petty_cash->id,
                                'path' => $fileName,
                            ];
                            ++$counter;
                        } else {
                            $fileName = null;
                        }
                    }

                    $chunkfille = array_chunk($files, 3);

                    if (!empty($chunkfille)) {
                        foreach ($chunkfille as $chunk) {
                            petty_cash_attachment::insert($chunk);
                        }
                    }
                }




                $rules = [


                    "qty"  => "required|numeric",


                    'name' => "required|string",
                    'unit' => "string|max:255",

                    'unit_price' => "required|numeric",

                ];

                $attributes = json_decode($request->attr, true);

                foreach ($petty_cash->attributes as $att) {
                    $att->delete();
                }

                foreach ($attributes as $attr) {

                    $validator = Validator::make(
                        $attr,

                        $rules

                    );


                    if ($validator->passes()) {
                        petty_attr::insert([
                            'name' => $attr['name'],
                            'qty' => $attr['qty'],
                            'unit' => $attr['unit'],
                            'unit_price' => $attr['unit_price'],

                            'total' => $attr['unit_price'] * $attr['qty'] ?? 0,

                            'petty_cash_id' => $petty_cash->id,

                        ]);
                    } else {

                        $errors  = $validator->errors()->toArray();
                        $data = json_encode($errors);

                        throw new CustomException($data);
                    }
                }
            });
        } catch (Exception $e) {
            return $e;
        }
    }

    public function index()
    {
        $petty_cashworkflow =    workflow::where('name', 'petty_cash')->with(['flowworkStep' => function ($q) {
            return     $q->with('role');
        }])->first();
        $petty_cash = auth()->user()->petty_cash()->orderBy('created_at', 'DESC')
            ->with(['petty_cash_cycle' => function ($q) {
                return   $q->with('role');
            }])->paginate(10);
        return Inertia::render('User/PettyCash/Index', [
            'data' => $petty_cash,
            'workflow' => $petty_cashworkflow
        ]);
        return view('petty_cash.index')->with(['data' => $petty_cash, 'workflow', $petty_cashworkflow]);
    }

    public function prepetty_cashreturn(request  $request)
    {


        return view('petty_cash.previewdef');
    }

    public function create()
    {
        $projects = project::all();
        $data = petty_cash::latest()->first();
        $explode = explode("-", $data->ref ?? 'PC-' . '' . '0');
        return Inertia::render('User/PettyCash/Create', ['reference' => 'PC-' . '' . $explode[1] + 1, 'projects' => $projects]);
    }

    public function insrting(request $request)
    {

        $data =  $this->validate($request, [
            'project_id' => ['required', 'numeric', 'max:255'],
            'date' => ['required', 'date', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],

            'to' => ['string', 'max:255'],
            'total' => ['required', 'numeric'],

        ]);
        try {

            DB::transaction(function () use ($request, $data) {

                $data = petty_cash::latest()->first();
                $explode = explode("-",$data->ref ?? 'R-'.''.'0');

                
                $subcon = petty_cash::create([
                    'project_id' => $request->project_id,

                    'user_id' => auth()->user()->id,

                    'status' => 0,

                    'expected_amount' => ($request->total + $request->vat),

                    'vat' => $request->vat,
                    'ref' => 'PC-'.''.$explode[1] + 1,
                    'date' => $request->date,
                    'content' => $request->content,
                    'subject' => $request->subject,

                    'to' => $request->to,

                ]);
                /*   if(!empty($subcon->cost_center)){
            $subcon->cost_center->expenses +=$subcon->total;
            $subcon->cost_center->save();
         }
            */

                if ($request->count > 0) {
                    for ($counter = 0; $counter <= $request->count; $counter++) {

                        $img = 'files-' . $counter;

                        if ($request->$img) {
                            $image_tmp = $request->$img;
                            $fileName = Str::random(40) . '.' . $image_tmp->getClientOriginalExtension();

                            $extension = $image_tmp->getClientOriginalExtension();

                            $image_tmp->move('uploads/petty_cash', $fileName);

                            $files[] = [
                                'petty_id' => $subcon->id,
                                'path' => $fileName,
                            ];
                            ++$counter;
                        } else {
                            $fileName = null;
                        }
                    }

                    $chunkfille = array_chunk($files, 3);

                    if (!empty($chunkfille)) {
                        foreach ($chunkfille as $chunk) {
                            petty_cash_attachment::insert($chunk);
                        }
                    }
                }



                $rules = [


                    "qty"  => "required|numeric",


                    'dis' => "required|string",
                    'unit' => "string|max:255",

                    'unit_price' => "required|numeric",

                ];

                $attributes = json_decode($request->attr, true);
                $users = json_decode($request->users, true);

                foreach ($attributes as $attr) {

                    $validator = Validator::make(
                        $attr,

                        $rules

                    );


                    if ($validator->passes()) {
                        petty_attr::insert([
                            'name' => $attr['dis'],
                            'qty' => $attr['qty'],
                            'unit' => $attr['unit'] ?? null,
                            'unit_price' => $attr['unit_price'],

                            'total' => $attr['unit_price'] * $attr['qty'] ?? 0,

                            'petty_cash_id' => $subcon->id,

                        ]);
                    } else {

                        $errors  = $validator->errors()->toArray();
                        $data = json_encode($errors);

                        throw new CustomException($data);
                    }
                }
                $rules = [


                    'id' => 'required|exists:users,id',

                ];
                $content   = 'user name:' . ' ' . auth()->user()->name ?? '' . 'Project Name:' . ' ' . $subcon->project->name ?? '' . 'has been created:' . ' ' . $subcon->ref . 'is waiting for review';
                if (!empty($users)) {
                    foreach ($users as $user) {




                        if ($validator->passes()) {
                            $subcon->mention()->attach([
                                $user['id']
                            ]);

                            $job = (new sendcc($user, $content))->delay(Carbon::now()->addSeconds(90));
                            $this->dispatch($job);
                        } else {
                            $errors  = $validator->errors()->toArray();
                            $data = json_encode($errors);

                            throw new CustomException($data);
                        }
                    }
                }
                $workflow = workflow::where('name', 'petty_cash')->first()->flowworkStep()
                    ->first();


                foreach ($workflow->role->user as $flow) {

                    notification::create([

                        'type' => 3,
                        'read' => 1,
                        'name' => 'New petty cash Request',
                        'user_id_to' => $flow->id,
                        'user_id_from' => auth()->user()->id,

                    ]);
                    $user = $flow;
                    $content = 'New petty cash Request';
                    $managercontent = '';
                    $job = (new rolecc($user, $content, $managercontent))->delay(Carbon::now()->addSeconds(90));
                    $this->dispatch($job);
                    //  NotificationEvent::dispatch($user->id,$content);

                }

                petty_cash_cycle::insert([
                    'step' => 1,
                    'status' => 0,
                    'flowwork_step_id' => $workflow->id,
                    'role_id' => $workflow->role_id,
                    'petty_cash_id' => $subcon->id
                ]);
            });
        } catch (Exception $e) {
            return $e;
        }
    }

    public function petty_cashreturn($petty_cash)
    {
        if (is_numeric($petty_cash)) {

            $data = petty_cash::where('id', $petty_cash)->with(['attributes'])->with(['petty_cash_cycle' => function ($q) {
                return  $q
                    ->with(['comment_petty_cash_cycle' => function ($qu) {
                        return $qu->with('user');
                    }]);
            }])->with('project')->first();

            if (!empty($data)) {
                return view('petty_cash.preview')->with(['data' => $data]);
            }
        }
    }





    public function returnasjson()
    {
        $petty_cash = auth()->user()->petty_cash()->orderBy('created_at', 'DESC')
            ->with(['petty_cash_cycle' => function ($q) {
                return   $q->with('role');
            }])->paginate(10);
        return response()->json(['data' => $petty_cash]);
    }


    public function delete(petty_cash $petty_cash)
    {
        if ($petty_cash->user_id == auth()->user()->id) {
            $petty_cash->delete();
        }
    }
}
