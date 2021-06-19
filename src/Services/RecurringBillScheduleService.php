<?php


namespace Rutatiina\Bill\Services;

use Illuminate\Support\Facades\Log;

class RecurringBillScheduleService
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

        $txnAttributes = RecurringBillService::copy($task->bill_recurring_id);

        $insert = RecurringBillService::store($txnAttributes);

        if ($insert == false)
        {
            Log::warning('Error: Recurring bill id:: #' . $task->recurring_invoice_id . ' failed @ ' . \Carbon\Carbon::now());
            Log::warning(implode("\n", RecurringBillService::$errors));
        }
        else
        {
            $task->update(['last_run' => now()]);
            Log::info('Success: Recurring bill id:: #' . $task->recurring_invoice_id . ' passed @ ' . \Carbon\Carbon::now());
        }
    }
}