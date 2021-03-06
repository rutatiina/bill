<?php

namespace Rutatiina\Bill\Services;

use Rutatiina\Bill\Models\RecurringBillProperty;

class RecurringBillPropertyService
{
    public static $errors = [];

    public function __construct()
    {
        //
    }

    public static function store($data)
    {
        $TxnRecurring = new RecurringBillProperty;
        $TxnRecurring->tenant_id = $data['tenant_id'];
        $TxnRecurring->recurring_bill_id = $data['id'];
        $TxnRecurring->status = $data['recurring']['status'];
        $TxnRecurring->frequency = $data['recurring']['frequency'];
        //$TxnRecurring->measurement = $data['recurring']['frequency']; //of no use
        $TxnRecurring->start_date = $data['recurring']['start_date'];
        $TxnRecurring->end_date = $data['recurring']['end_date'];
        $TxnRecurring->day_of_month = $data['recurring']['day_of_month'];
        $TxnRecurring->month = $data['recurring']['month'];
        $TxnRecurring->day_of_week = $data['recurring']['day_of_week'];
        $TxnRecurring->save();

    }

}
