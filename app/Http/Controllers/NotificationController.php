<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\notification;
class NotificationController extends Controller
{
    public function readnot(notification $notification){

        $notification->update([
            'read'=>2,
        ]);

        if($notification->type == 1){
return redirect()->route('managers.index_employee_request');

        }
        elseif($notification->type == 2){
            return redirect()->route('managers.index_matrial_request');
            
                    }
                    elseif($notification->type == 3){
                        return redirect()->route('managers.index_petty_cash_request');
                        
                                }
                                elseif($notification->type == 4){
                                    return redirect()->route('managers.index_purchase_order_request');
                                    
                                            }
                                            elseif($notification->type == 6){
                                                return redirect()->route('managers.index_rfq_request');
                                                
                                                        }
                                                        elseif($notification->type == 7){
                                                            return redirect()->route('managers.index_site_request');
                                                            
                                                                    }

                                      elseif($notification->type == 8){
                                         return redirect()->route('managers.index_subcontractor_request');
                                                                      
                                                      }


    }
}
