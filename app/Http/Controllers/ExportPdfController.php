<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\petty_cash;
use App\Purchase_order;
use App\subcontractor;
class ExportPdfController extends Controller
{

    public function subcontractor(){
    
        $subcontractor =      subcontractor::select(['ref','date','expected_amount','status','user_id','total','project_id'])
             ->with(['user','project'])->get()->chunk(100);
     
             return view('managers.export.subcontractor')->with(['subcontractor'=>$subcontractor]);

    }

    
    public function petty_cash(){
    
        $pett_cash =      petty_cash::select(['ref','date','expected_amount','status','user_id','total','project_id'])
             ->with(['user','project'])->get()->chunk(100);
     
             return view('managers.export.petty_cash')->with(['petty_cash'=>$pett_cash]);

    }

    public function purchase_order(){
    
        $purchase_order =      Purchase_order::select(['ref','date','status','user_id','total','project_id'])
             ->with(['user','project'])->get()->chunk(100);
     
             return view('managers.export.purchase_order')->with(['purchase_order'=>$purchase_order]);

    }

}
