<?php

namespace Rutatiina\Bill\Traits\Recurring;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Rutatiina\Bill\Models\RecurringBill;
use Rutatiina\FinancialAccounting\Traits\Schedule as FinancialAccountingScheduleTrait;

trait Schedule
{
    use FinancialAccountingScheduleTrait;

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

        try
        {
            DB::connection('tenant')->getDatabaseName();
            Schema::hasTable((new RecurringBill)->getTable());
        }
        catch (\Throwable $e)
        {
            return false;
        }

        //$schedule->call(function () {
        //    Log::info('recurringInvoiceSchedule via trait has been called #updated');
        //})->everyMinute()->runInBackground();

        //the script to process recurring requests

        $tasks = RecurringBill::withoutGlobalScopes()
            ->where('status', 'active')
            ->get();

        //Log::info('number of tasks: '.$tasks->count());

        $this->recurringSchedule($schedule, $tasks);

        return true;
    }
}
