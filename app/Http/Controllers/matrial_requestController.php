<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\matrial_request;
use App\matrial_attr;
use Illuminate\Support\Facades\Validator;
use DB;
use Carbon\Carbon;
use App\workflow;
use App\notification;
use App\flowworkStep;
use App\matrial_request_cycle;
use App\attachment_matrial_cycle;
use App\Exceptions\CustomException;
use App\Jobs\sendcc;
use App\matrial_request_attachment;
use App\Jobs\rolecc;
use App\matrial_condition as note;
use App\Events\NotificationEvent;
use App\project;
use Exception;
use Inertia\Inertia;
use App\summaryreport;
class matrial_requestController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function matrial_requestprereturn(request  $request)
    {

        $data  =
            [
                'ref' => $request->def,
                'to' => $request->to,
                'date' => $request->date,
                'subject' => $request->subject,
                'content' => $request->content,
                'attributes' => json_decode($request->attr, true),
                'note' => json_decode($request->condition, true),
            ];


        return view('matrial_request.previewdef')->with(['data' => $data]);
    }

    public function create()
    {
        $projects = project::all();
        $data = matrial_request::latest()->first();
        $explode = explode("-", $data->ref ?? 'MR-' . '' . '0');
        return Inertia::render('User/MatrialRequest/Create', ['reference' => 'MR-' . '' . $explode[1] + 1, 'projects' => $projects]);
    }

    public function insarting(request $request)
    {
        $data =  $this->validate($request, [
            'quotation' => ['string', 'max:255'],
            'project_id' => ['required', 'numeric', 'max:255'],
            'date' => ['required', 'date', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],


            'ref' => ['string', 'max:255'],
            'to' => ['string', 'max:255'],

        ]);

        try {

            DB::transaction(function () use ($request, $data) {
                $matrial_request = matrial_request::create([

                    'project_id' => $request['project_id'],
                    'date' => $request['date'],
                    'subject' => $request['subject'],
                    'content' => $request->content,
                    'user_id' => auth()->user()->id,
                    'ref' => $request['ref'],
                    'status' => 0,
                    'to' => $request['to']
                ]);


                $summaryreport=  summaryreport::first();
                $summaryreport =  $summaryreport->matrial_bending !== null ?  $summaryreport->incerment('matrial_bending',1)
                : $summaryreport->update([
                    'matrial_bending'=>1
                ]);

                $rules = [


                    "qty"  => "required|numeric",


                    'dis' => "required|string",
                    'unit' => "string|max:255",

                    'unit_price' => "required|numeric",

                ];

                $attributes = json_decode($request->attr, true);
                $users = json_decode($request->users, true);

                if ($request->count > 0) {
                    for ($counter = 0; $counter <= $request->count; $counter++) {

                        $img = 'files-' . $counter;

                        if ($request->$img) {
                            $image_tmp = $request->$img;
                            $fileName = Str::random(40) . '.' . $image_tmp->getClientOriginalExtension();

                            $extension = $image_tmp->getClientOriginalExtension();

                            $image_tmp->move('uploads/matrial_request', $fileName);

                            $files[] = [
                                'matrial_request_id' => $matrial_request->id,
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
                            matrial_request_attachment::insert($chunk);
                        }
                    }
                }


                foreach ($attributes as $attr) {
                    $validator = Validator::make(
                        array('qty' => $attr['qty']),
                        array('qty' => array('required', 'numeric')),
                        array('dis' => $attr['dis']),
                        array('dis' => array('string')),

                        array('unit' => $attr['unit']),
                        array('unit' => array('string|max:255')),

                    );

                    if ($validator->passes()) {
                        matrial_attr::insert([
                            'name' => $attr['dis'],
                            'qty' => $attr['qty'],
                            'unit' => $attr['unit'],
                            'matrial_request_id' => $matrial_request->id,
                        ]);
                    } else {

                        $errors  = $validator->errors()->toArray();
                        $data = json_encode($errors);

                        throw new CustomException($data);
                    }
                }
                if (!empty($users)) {
                    foreach ($users as $user) {
                        $rules = [


                            'id' => 'required|exists:users,id',

                        ];
                        $validator = Validator::make(
                            $user,

                            $rules

                        );
                        if ($validator->passes()) {
                            $matrial_request->mention()->attach([
                                $user['id']
                            ]);

                            $managercontent = '';
                            $content = 'New Matrial Request' . $matrial_request->ref . '';
                            $job = (new rolecc($user, $content, $managercontent))->delay(Carbon::now()->addSeconds(90));
                            $this->dispatch($job);
                            NotificationEvent::dispatch($user->id, $content);
                        } else {
                            $errors  = $validator->errors()->toArray();
                            $data = json_encode($errors);

                            throw new CustomException($data);
                        }
                    }
                }




                $workflow = workflow::where('name', 'matrial_request')->first()->flowworkStep()
                    ->first();

                foreach ($workflow->role->user as $flow) {

                    notification::create([

                        'type' => 2,
                        'read' => 1,
                        'name' => 'New Matrial Request',
                        'user_id_to' => $flow->id,
                        'user_id_from' => auth()->user()->id,

                    ]);
                }


                matrial_request_cycle::insert([
                    'step' => 1,
                    'status' => 0,
                    'flowwork_step_id' => $workflow->id,
                    'role_id' => $workflow->role_id,
                    'matrial_request_id' => $matrial_request->id
                ]);
            });
        } catch (Exception $e) {
            return $e;
        }
    }

    public function matrial_requestreturn($matrial_request)
    {
        if (is_numeric($matrial_request)) {

            $data = matrial_request::where('id', $matrial_request)->with(['attributes', 'note'])->with(['matrial_request_cycle' => function ($q) {
                return  $q->with(['comment_matrial_cycle' => function ($qu) {
                    return $qu->with('user');
                }]);
            }])->with('project')->first();

            if (!empty($data)) {
                return view('matrial_request.preview')->with(['data' => $data]);
            }
        }
    }


    public function index()
    {

        $mattrial_requestworkflow =    workflow::where('name', 'matrial_request')->with(['flowworkStep' => function ($q) {
            return     $q->with('role');
        }])->first();
        $matrial_request = auth()->user()->matrial_request()->orderBy('created_at', 'DESC')
            ->with(['matrial_request_cycle' => function ($q) {
                return   $q->with('role');
            }])->paginate(10);
        return Inertia::render('User/MatrialRequest/Index', [
            'data' => $matrial_request,
            'workflow' => $mattrial_requestworkflow
        ]);
    }

    public function returnasjson()
    {
        $matrial_request = auth()->user()->matrial_request()->orderBy('created_at', 'DESC')
            ->with(['matrial_request_cycle' => function ($q) {
                return   $q->with('role');
            }])->paginate(10);
        return response()->json(['data' => $matrial_request]);
    }

    public function delete(matrial_request $matrial_request)
    {
        if ($matrial_request->user_id == auth()->user()->id) {
            $matrial_request->delete();
        }
    }




    public function edit($matrial_request)
    {
        if (is_numeric($matrial_request)) {
            $projects = project::all();
            $data = matrial_request::where('id', $matrial_request)->with(['matrial_request_cycle' => function ($q) {
                return  $q->with(['comment_matrial_cycle' => function ($qu) {
                    return $qu->with('attachment_matrial_cycle');
                }])->with('role');
            }])->with('attributes')->with('files')->with('note')->first();
            if (!empty($data)) {
                return Inertia::render('User/MatrialRequest/Edit', [
                    'data' => $data,
                    'projects' => $projects
                ]);
            }
        }
    }

    public function action(request $request, matrial_request $matrial_request)
    {


        $data =  $this->validate($request, [
            'quotation' => ['string', 'max:255'],
            'project_id' => ['required', 'numeric', 'max:255'],
            'date' => ['required', 'date', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],


            'ref' => ['string', 'max:255'],
            'to' => ['string', 'max:255'],


        ]);

        try {

            DB::transaction(function () use ($request, $matrial_request, $data) {
                $matrial_request->update([

                    'project_id' => $data['project_id'],
                    'date' => $data['date'],
                    'subject' => $data['subject'],
                    'ref' => $data['ref'],
                    'status' => 0,
                    'to' => $data['to'],



                ]);

                if ($request->deletedfiles) {
                    matrial_request_attachment::find($request->deletedfiles)->delete();
                }

                $matrial_request_cycle =  $matrial_request->matrial_request_cycle()->delete();

                $workflow = workflow::where(['name' => 'matrial_request'])->first()->flowworkStep()
                    ->first();


                $attributes = json_decode($request->attr, true);




                $notification = [];

                foreach ($workflow->role->user as $flow) {

                    $notification[] = [
                        'type' => 4,
                        'read' => 1,
                        'name' => 'matrial_request request has been modified' . '' . $matrial_request->ref,
                        'user_id_to' => $flow->id,
                        'user_id_from' => auth()->user()->id,
                    ];
                    $user = $flow;
                    $content = 'matrial_request request has been modified' . '' . $matrial_request->ref;
                    $managercontent = '';
                    $job = (new rolecc($user, $content, $managercontent))->delay(Carbon::now()->addSeconds(90));
                    $this->dispatch($job);
                    NotificationEvent::dispatch($user->id, $content);
                }

                matrial_request_cycle::create([
                    'step' => 1,
                    'status' => 0,
                    'flowwork_step_id' => $workflow->id,
                    'role_id' => $workflow->role_id,
                    'matrial_request_id' => $matrial_request->id
                ]);



                $chunk_notification = array_chunk($notification, 10);

                foreach ($chunk_notification as $noti) {
                    notification::insert($noti);
                }

                if ($request->count > 0) {
                    for ($counter = 0; $counter <= $request->count; $counter++) {

                        $img = 'files-' . $counter;

                        if ($request->$img) {
                            $image_tmp = $request->$img;
                            $fileName = Str::random(40) . '.' . $image_tmp->getClientOriginalExtension();

                            $extension = $image_tmp->getClientOriginalExtension();

                            $image_tmp->move('uploads/matrial_request', $fileName);

                            $files[] = [
                                'matrial_request_id' => $matrial_request->id,
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
                            matrial_request_attachment::insert($chunk);
                        }
                    }
                }



                if (!empty($matrial_request->attributes)) {
                    foreach ($matrial_request->attributes as $matrial) {
                        $matrial->delete();
                    }
                }


                foreach ($attributes as $attr) {
                    $validator = Validator::make(
                        array('qty' => $attr['qty']),
                        array('qty' => array('required', 'numeric')),
                        array('name' => $attr['name']),
                        array('name' => array('string')),

                        array('unit' => $attr['unit']),
                        array('unit' => array('string|max:255')),

                    );

                    if ($validator->passes()) {
                        matrial_attr::insert([
                            'name' => $attr['name'],
                            'qty' => $attr['qty'],
                            'unit' => $attr['unit'],
                            'matrial_request_id' => $matrial_request->id,
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
}
