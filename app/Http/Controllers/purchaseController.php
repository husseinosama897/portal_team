<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Purchase_order;
use Twilio\Rest\Client;

use App\purchase_order_product;
use Illuminate\Support\Facades\Validator;
use DB;
use App\workflow;
use Carbon\Carbon;

use App\notification;
use App\Jobs\sendcc;
use App\report;
use Illuminate\Support\Str;
use App\Jobs\rolecc;
use App\purchase_order_attachment;
use App\purchase_order_cycle;
use App\Exceptions\CustomException;
use App\payment_term as note;
use App\product;
use App\Events\NotificationEvent;
use App\Jobs\update_pervious_value;

class purchaseController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function prepurchasereturn(request  $request)
    {

        //   if(auth()->user()->role()->permission->where('name','preview po')->first()){
        return view('purchase.previewdef');

        //   }

    }




    public function createpurchaseorder()
    {
        // if(auth()->user()->role()->permission->where('name','create po')->first()){

        $data = Purchase_order::latest()->first();
        $explode = explode("-", $data->ref);
        return view('purchase.create')->with(['ref' => 'PO-' . '' . $explode[1] + 1]);


        // }

    }

    public function insarting_data(request $request)
    {
        $data =  $this->validate($request, [

            'project_id' => ['required', 'numeric'],
            'date' => ['required', 'date', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'material_avalibility' => ['string', 'max:255'],
            'transportation' => ['string', 'max:255'],
            'delivery_date' => ['required', 'date', 'max:255'],
            'cc' => ['string', 'max:255'],
            'ref' => ['string', 'max:255'],

            'supplier_id' => ['required', 'numeric'],

        ]);

        try {

            DB::transaction(function () use ($request, $data) {
                $cash = $request->cash == true ? 1 : 0;
                $on_vat = $request->no_vat == true ? 1 : 0;
                $Purchase_order = Purchase_order::create([

                    'project_id' => $request['project_id'],
                    'date' => $request['date'],
                    'subject' => $request['subject'],
                    'draft' => 0,
                    'user_id' => auth()->user()->id,
                    'transportation' => $request['transportation'],
                    'delivery_date' => $request['delivery_date'],
                    'status' => 0,
                    'on_vat' => $on_vat,
                    'cash' => $cash,
                    'total' => $request->overall,
                    'vat' => $request->vat,

                    'percentage_discount' => $request->percentage_discount,
                    'discount' => $request->discount,
                    'subtotal' => $request->total,
                    'ref' => $request->ref,
                    'supplier_id' => $request->supplier_id,

                    'order_for' => $request->order_for,
                ]);





                $attributes = json_decode($request->attr, true);
                $payment = json_decode($request->payment, true);
                $users = json_decode($request->users, true);
                $files = [];
                if ($request->count > 0) {
                    for ($counter = 0; $counter <= $request->count; $counter++) {

                        $img = 'files-' . $counter;

                        if ($request->$img) {
                            $image_tmp = $request->$img;
                            $fileName = Str::random(40) . '.' . $image_tmp->getClientOriginalExtension();

                            $extension = $image_tmp->getClientOriginalExtension();

                            $image_tmp->move('uploads/purchase_order', $fileName);

                            $files[] = [
                                'purchase_id' => $Purchase_order->id,
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
                            purchase_order_attachment::insert($chunk);
                        }
                    }
                }

                foreach ($attributes as $attr) {

                    $validator = Validator::make(
                        array('qty' => $attr['qty'] ?? null),
                        array('qty' => array('required', 'numeric')),
                        array('dis' => $attr['dis'] ?? null),
                        array('dis' => array('required|string')),

                        array('unit' => $attr['unit'] ?? null),
                        array('unit' => array('required|string|max:255')),

                        array('unit_price' => $attr['unit_price'] ?? null),
                        array('unit' => array('required', 'numeric'))

                    );



                    if ($validator->passes() == true) {

                        if (!empty($attr['id'])) {
                            $Purchase_order->attributes()->attach($attr['id'] ?? null, [
                                'dis' => $attr['dis'],
                                'qty' => $attr['qty'],
                                'unit' => $attr['unit'],
                                'unit_price' => $attr['unit_price'],
                                'total' => $attr['total'] ?? 0,
                                'purchase_order_id' => $Purchase_order->id,
                            ]);


                            $job = (new update_pervious_value(
                                $attr['unit_price'],
                                $attr['id'],
                                auth()->user()->id,
                                $attr['value']  ?? 0,
                                auth()->user()->role && auth()->user()->role->section_id !== null ? auth()->user()->role->section_id : null

                            ))->delay(Carbon::now()->addSeconds(90));
                            $this->dispatch($job);
                        } else {

                            $product = product::create([
                                'name' => $attr['dis'],
                            ]);






                            purchase_order_product::insert([
                                'dis' => $attr['dis'],
                                'qty' => $attr['qty'],
                                'product_id' => null,
                                'unit' => $attr['unit'],
                                'unit_price' => $attr['unit_price'],
                                'total' => $attr['total'] ?? 0,
                                'purchase_order_id' => $Purchase_order->id,
                            ]);
                        }
                    } else {

                        $errors  = $validator->errors()->toArray();
                        $data = json_encode($errors);

                        throw new CustomException($data);
                    }
                }

                $content   = 'user name:' . ' ' . auth()->user()->name ?? '' . 'Project Name:' . ' ' . $Purchase_order->project->name ?? '' . 'has been created:' . ' ' . $Purchase_order->ref . 'is waiting for review';
                if (!empty($users)) {
                    $rules = [


                        'id' => 'required|exists:users,id',

                    ];

                    foreach ($users as $user) {

                        $validator = Validator::make(
                            $user,

                            $rules

                        );
                        if ($validator->passes()) {
                            $Purchase_order->mention()->attach([
                                $user['id']
                            ]);

                            $managercontent = '';

                            $job = (new sendcc($user, $content, $managercontent))->delay(Carbon::now()->addSeconds(90));
                            $this->dispatch($job);
                            NotificationEvent::dispatch($user->id, $content);
                        } else {
                        }
                    }
                }


                $rules = [

                    'name' => "required|string",

                ];

                if (!empty($payment)  && $cash  == 1) {
                    foreach ($payment as $pay) {
                        $validator = Validator::make($pay, $rules);

                        if ($validator->passes()) {
                            note::insert([
                                'dis' => $pay['dis'] ?? null,
                                'purchase_order_id' => $Purchase_order->id,
                                'percentage' => $pay['percentage'] ?? null,
                                'name' => $pay['name'] ?? null,
                                'amount' => $pay['amount'] ?? null,
                                'date' => $pay['date'] ?? null,
                            ]);
                        } else {

                            $errors  = $validator->errors()->toArray();
                            $data = json_encode($errors);

                            throw new CustomException($data);
                        }
                    }
                }


                $workflow = workflow::where('name', 'purchase_order')->first()->flowworkStep()
                    ->first();

                foreach ($workflow->role->user as $flow) {

                    notification::create([

                        'type' => 4,
                        'read' => 1,
                        'name' => 'New purchase Request',
                        'user_id_to' => $flow->id,
                        'user_id_from' => auth()->user()->id,
                    ]);
                    $user = $flow;
                    $content = 'New purchase Request';
                    $managercontent = '';
                    $job = (new rolecc($user, $content, $managercontent))->delay(Carbon::now()->addSeconds(90));
                    $this->dispatch($job);
                    NotificationEvent::dispatch($user->id, $content);
                }

                /*
$account_sid = env('TWILIO_SID');
$account_token = env('TWILIO_TOKEN');
$number = env('TWILIO_FROM');

$client = new Client($account_sid,$account_token);
$client->messages->create('+966583850200',[
    'title'=>'hussein',
    'from'=>$number,
    'body'=>$content,
]);
*/

                purchase_order_cycle::create([
                    'step' => 1,
                    'status' => 0,
                    'flowwork_step_id' => $workflow->id,
                    'role_id' => $workflow->role_id,
                    'purchase_order_id' => $Purchase_order->id
                ]);
            });
        } catch (Exception $e) {
            return $e;
        }
    }




    public function update($Purchase_order)
    {
        //    if (is_numeric($Purchase_order) && auth()->user()->role()->permission->where('name','edit po')->first()  ){
        $data = Purchase_order::where('id', $Purchase_order)->with(['purchase_order_cycle' => function ($q) {
            return  $q->with(['comment_purchase_order_cycle' => function ($qu) {
                return $qu->with('attachment_purchase_order_cycle');
            }])->with('role');
        }])->with(['attributes2' => function ($q) {
            return $q->where('product_id', '=', null);
        }, 'purchase_order_attachment', 'attributes'])->with('note')->first();
        if (!empty($data)) {

            return view('purchase.update')->with(['data' => $data]);
        }
        // }

    }


    public function action(request $request, Purchase_order $Purchase_order)
    {


        $data =  $this->validate($request, [
            'quotation' => ['string', 'max:255'],
            'project_id' => ['required', 'numeric', 'max:255'],
            'date' => ['required', 'date', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],


            'ref' => ['string', 'max:255'],



        ]);

        try {

            DB::transaction(function () use ($request, $Purchase_order, $data) {
                $cash = $request->cash == true ? 1 : 0;
                $on_vat = $request->no_vat == true ? 1 : 0;
                if ($Purchase_order->total !== null) {


                    $Purchase_order->project->decrement('po_expenses', $Purchase_order->total);

                    $report =   report::where('date', $Purchase_order->date)->decrement('total_cash_out', $Purchase_order->total);
                }
                $Purchase_order->update([

                    'project_id' => $request['project_id'],
                    'date' => $request['date'],
                    'subject' => $request['subject'],
                    'material_avalibility' => $request['material_avalibility'],
                    'transportation' => $request['transportation'],
                    'delivery_date' => $request['delivery_date'],
                    'status' => 0,
                    'on_vat' => $on_vat,
                    'cash' => $cash,
                    'total' => $request->overall,
                    'vat' => $request->vat,
                    'percentage_discount' => $request->percentage_discount,
                    'discount' => $request->discount,
                    'subtotal' => $request->total,
                    'ref' => $request->ref,
                    'supplier_id' => $request->supplier_id,
                    'order_for' => $request->order_for,

                ]);





                if ($request->deletedfiles) {
                    purchase_order_attachment::find($request->deletedfiles)->delete();
                }


                $Purchase_order_cycle =  $Purchase_order->purchase_order_cycle()->delete();
                $workflow = workflow::where(['name' => 'purchase_order'])->first()->flowworkStep()
                    ->first();


                foreach ($Purchase_order->note as $note) {
                    $note->delete();
                }

                $attributes = json_decode($request->attr, true);
                $payment = json_decode($request->payment, true);

                $rules = [

                    'name' => "required|string",
                    'percentage' => "numeric",

                ];
                $payments = [];
                if (!empty($payment) && $cash  == 1) {
                    foreach ($payment as $pay) {
                        $validator = Validator::make($pay, $rules);

                        if ($validator->passes()) {
                            $payments[] =  [
                                'dis' => $pay['dis'] ?? null,
                                'purchase_order_id' => $Purchase_order->id,
                                'percentage' => $pay['percentage'] ?? null,
                                'name' => $pay['name'] ?? null,
                                'amount' => $pay['amount'] ?? null,
                                'date' => \Carbon\Carbon::parse($pay['date'])->format('Y-m-d') ?? null,
                            ];
                        } else {

                            $errors  = $validator->errors()->toArray();
                            $data = json_encode($errors);

                            throw new CustomException($data);
                        }
                    }
                }
                if (!empty($payments)) {
                    note::insert($payments);
                }
                $notification = [];

                foreach ($workflow->role->user as $flow) {

                    $notification[] = [
                        'type' => 4,
                        'read' => 1,
                        'name' => 'Po request has been modified' . '' . $Purchase_order->ref,
                        'user_id_to' => $flow->id,
                        'user_id_from' => auth()->user()->id,
                    ];
                    $user = $flow;
                    $content = 'Po request has been modified' . '' . $Purchase_order->ref;
                    $managercontent = '';
                    $job = (new rolecc($user, $content, $managercontent))->delay(Carbon::now()->addSeconds(90));
                    $this->dispatch($job);
                    NotificationEvent::dispatch($user->id, $content);
                }

                purchase_order_cycle::create([
                    'step' => 1,
                    'status' => 0,
                    'flowwork_step_id' => $workflow->id,
                    'role_id' => $workflow->role_id,
                    'purchase_order_id' => $Purchase_order->id
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

                            $image_tmp->move('uploads/purchase_order', $fileName);

                            $files[] = [
                                'purchase_id' => $Purchase_order->id,
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
                            purchase_order_attachment::insert($chunk);
                        }
                    }
                }

                if (!empty($Purchase_order->attributes2)) {
                    foreach ($Purchase_order->attributes2 as $Po) {
                        $Po->delete();
                    }
                }


                foreach ($attributes as $attr) {

                    $validator = Validator::make(
                        array('qty' => $attr['qty'] ?? $attr['pivot']['qty']),
                        array('qty' => array('required', 'numeric')),
                        array('dis' => $attr['dis'] ?? $attr['pivot']['dis']),
                        array('dis' => array('required|string')),

                        array('unit' => $attr['unit'] ?? $attr['pivot']['unit']),
                        array('unit' => array('string|max:255')),

                        array('unit_price' => $attr['unit_price'] ?? $attr['pivot']['unit_price']),
                        array('unit' => array('required', 'numeric'))

                    );



                    if ($validator->passes() == true) {


                        if (!empty($attr['pivot']['product_id'])) {
                            $Purchase_order->attributes()->attach($attr['pivot']['product_id'] ?? null, [
                                'dis' => $attr['dis'] ?? $attr['name'],
                                'qty' => $attr['pivot']['qty'],
                                'unit' => $attr['pivot']['unit'],
                                'unit_price' => $attr['pivot']['unit_price'],
                                'total' => $attr['pivot']['unit_price'] * $attr['pivot']['qty'] ?? 0,
                                'purchase_order_id' => $Purchase_order->id,
                            ]);
                            $job = (new update_pervious_value(
                                $attr['pivot']['unit_price'],
                                $attr['pivot']['product_id'],
                                auth()->user()->id,
                                $attr['value']
                            ))->delay(Carbon::now()->addSeconds(90));
                            $this->dispatch($job);
                        } else {
                            purchase_order_product::insert([
                                'dis' => $attr['dis'] ?? $attr['name'],
                                'qty' => $attr['qty'],
                                'product_id' => null,
                                'unit' => $attr['unit'],
                                'unit_price' => $attr['unit_price'],
                                'total' => $attr['unit_price'] * $attr['qty'] ?? 0,
                                'purchase_order_id' => $Purchase_order->id,
                            ]);
                        }
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

    public function purchasereturn($Purchase_order)
    {
        //  if (is_numeric($Purchase_order)  && auth()->user()->role()->permission->where('name','preview po')->first()){
        $data = Purchase_order::where('id', $Purchase_order)->with(['attributes', 'note'])
            ->with(['attributes2' => function ($q) {
                return $q->where('product_id', '=', null);
            }])
            ->with(['purchase_order_cycle' => function ($q) {
                return  $q->with(['comment_purchase_order_cycle' => function ($qu) {
                    return $qu->with('user');
                }])->with(['role' => function ($q) {
                }]);
            }])->with('project')->first();
        if (!empty($data)) {
            return view('purchase.preview')->with(['data' => $data]);
        }

        // }

    }


    public function index()
    {
        $purchase_orderworkflow =    workflow::where('name', 'purchase_order')->with(['flowworkStep' => function ($q) {
            return     $q->with('role');
        }])->first();

        return view('purchase.index')->with(['workflow' => $purchase_orderworkflow]);
    }

    public function returnasjson()
    {
        $purchase = auth()->user()->purchase()->with(['purchase_order_cycle' => function ($q) {
            return $q->with('role');
        }])->paginate(10);
        return response()->json(['data' => $purchase]);
    }

    public function delete(Purchase_order $Purchase_order)
    {
        if ($Purchase_order->user_id == auth()->user()->id) {
            $Purchase_order->delete();
        }
    }
}
