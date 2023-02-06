<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class summaryreoprt extends Model
{
    use HasFactory;

    protected $fillable = [
 'bid_value',
    'cash_out',
'cash_in',
     'closed_project',
       'project',
        'employee',

      'matrial_request',

       'matrial_bending',

       'purchase_order',

      'purchase_order_bending',

'marketing',
     'marketing_bending',
        'petty_cash',
        'petty_cash_bending',
        'subcontractor_invoice',
        'subcontractor_invoice_bending',
        'contract',
    'contract_bending',
        'service',
  'service_bending',
        'attendance',
        'salaries',
     'bid_value_closed',
       'bid_value_open',
'percentage_attendance',
    'percentage_performance',
    ];
}
