<?php

namespace Rutatiina\Bill\Traits\Recurring;

use Rutatiina\Bill\Models\BillRecurringProperties;

trait Schedule
{
    /**
     * Execute the console command.
     *
     * @param \Rutatiina\RecurringBill\Traits\Schedule $schedule
     * @return boolean
     */
    public function recurringBillSchedule($schedule)
    {
        //return true;

        config(['app.scheduled_process' => true]);

        //$schedule->call(function () {
        //    Log::info('recurringInvoiceSchedule via trait has been called #updated');
        //})->everyMinute()->runInBackground();

        //the script to process recurring requests

        $tasks = BillRecurringProperties::withoutGlobalScopes()
            ->where('status', 'active')
            ->get();

        //Log::info('number of tasks: '.$tasks->count());

        $this->recurringSchedule($schedule, $tasks);

        return true;
    }
}
