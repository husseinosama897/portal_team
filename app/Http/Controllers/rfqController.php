<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\rfq;
use Illuminate\Support\Facades\Validator;
use DB;
use App\workflow;
use App\notification;
use App\rfq_cycle;
use App\Jobs\sendcc;
use App\Jobs\rolecc;

use App\Exceptions\CustomException;
use App\project;
use Inertia\Inertia;

class rfqController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }



    public function prefrqreturn(request  $request)
    {

        return view('frq.previewdef');
    }


    public function insrting(request $request)
    {
        $data =  $this->validate($request, [
            'project_id' => ['required', 'numeric', 'max:255'],
            'date' => ['required', 'date', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'ref' => ['string', 'max:255'],
            'to' => ['string', 'max:255'],

        ]);
        try {

            DB::transaction(function () use ($request, $data) {


                $rfq = rfq::create([

                    'project_id' => $data['project_id'],
                    'date' => $data['date'],
                    'subject' => $data['subject'],
                    'user_id' => auth()->user()->id,
                    'status' => 0,
                    'ref' => $request->ref,
                    'to' => $data['to'],

                    'content' => $request->content,
                ]);
                $rules = [


                    'id' => 'required|exists:Users,id',

                ];
                $users = json_decode($request->users, true);
                $content   = 'user name:' . ' ' . auth()->user()->name ?? '' . 'Project Name:' . ' ' . $rfq->project->name ?? '' . 'has been created:' . ' ' . $rfq->ref . 'is waiting for review';
                if (!empty($users)) {
                    foreach ($users as $user) {

                        $validator = Validator::make(
                            $user,

                            $rules

                        );

                        if ($validator->passes()) {
                            $rfq->mention()->attach([
                                $user['id']
                            ]);
                            dispatch_now(new sendcc($user, $content))->delay(now()->addMinutes(3));
                        } else {
                            /*
                $errors  = $validator->errors()->toArray();
                $data = json_encode($errors);

                        throw new CustomException ($data);
                        */
                        }
                    }
                }


                $workflow = workflow::where('name', 'rfq')->first()->flowworkStep()
                    ->first();

                foreach ($workflow->role->user as $flow) {

                    notification::create([

                        'type' => 6,
                        'read' => 1,
                        'name' => 'New RFQ Request',
                        'user_id_to' => $flow->id,
                        'user_id_from' => auth()->user()->id,

                    ]);

                    $user = $flow;
                    $content = 'New RFQ Request';
                    dispatch_now(new rolecc($user, $content));
                }

                rfq_cycle::insert([
                    'step' => 1,
                    'status' => 0,
                    'flowwork_step_id' => $workflow->id,
                    'role_id' => $workflow->role_id,
                    'rfq_id' => $rfq->id
                ]);
            });
        } catch (Exception $e) {
            return $e;
        }
    }

    public function frqreturn($frq)
    {
        if (is_numeric($frq)) {
            $data = rfq::where('id', $frq)->with('project')->with(['rfq_cycle' => function ($q) {
                return  $q
                    ->with(['comment_rfq_cycle' => function ($qu) {
                        return $qu->with('attachment_rfq_cycle');
                    }])->with('role');
            }])->with('project')->first();


            if (!empty($data)) {
                return view('frq.preview')->with(['data' => $data]);
            }
        }
    }


    public function index()
    {
        $rfq_workflow =    workflow::where('name', 'rfq')->with(['flowworkStep' => function ($q) {
            return     $q->with('role');
        }])->first();
        $rfq = auth()->user()->rfq()->orderBy('created_at', 'DESC')->with(['rfq_cycle' => function ($q) {
            return   $q->with('role');
        }])->paginate(10);
        return Inertia::render('User/Rfq/Index', [
            'data' => $rfq,
            'workflow' => $rfq_workflow
        ]);
        return view('frq.index');
    }

    public function create()
    {
        $projects = project::all();
        return Inertia::render('User/Rfq/Create', ['projects' => $projects]);
    }


    public function returnasjson()
    {
        $purchase = auth()->user()->rfq()->orderBy('created_at', 'DESC')->with(['rfq_cycle' => function ($q) {
            return   $q->with('role');
        }])->paginate(10);
        return response()->json(['data' => $purchase]);
    }

    public function delete(rfq $rfq)
    {

        if ($rfq->user_id == auth()->user()->id) {
            $rfq->delete();
        }
    }
}
