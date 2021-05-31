<?php

namespace Rutatiina\Bill\Classes\Recurring;

use Illuminate\Support\Facades\Log;
use Rutatiina\Bill\Models\Bill;
use Rutatiina\Bill\Classes\Recurring\Copy as RecurringBillCopy;
use Rutatiina\Bill\Classes\Store as BillStore;

class Schedule
{
    public $task;

    function __construct($task)
    {
        $this->task = $task;
    }

    /**
     * Execute the console command.
     *
     * @return boolean
     */

    public function __invoke()
    {
        $task = $this->task;

        //get the last invoice number
        $txn = Bill::orderBy('id', 'desc')->first();
        //$settings = Setting::first();
        //$number = $settings->number_prefix.(str_pad((optional($txn)->number+1), $settings->minimum_number_length, "0", STR_PAD_LEFT)).$settings->number_postfix;

        $TxnCopy = new RecurringBillCopy();
        $txnAttributes = $TxnCopy->run($task->bill_recurring_id);
        $txnAttributes['number'] = (optional($txn)->number+1);
        //Log::info('doc number #'.$txnAttributes['number']);

        $TxnStore = new BillStore();
        $TxnStore->txnInsertData = $txnAttributes;
        $insert = $TxnStore->run();

        if ($insert == false)
        {
            Log::warning('Error: Recurring bill id:: #'.$task->recurring_invoice_id.' failed @ '.\Carbon\Carbon::now());
            Log::warning($TxnStore->errors);
        }
        else
        {
            $task->update(['last_run' => now()]);
            Log::info('Success: Recurring bill id:: #'.$task->recurring_invoice_id.' passed @ '.\Carbon\Carbon::now());
        }
    }
}