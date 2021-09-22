<?php

namespace Rutatiina\Bill;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Rutatiina\Bill\Traits\Recurring\Schedule as RecurringBillScheduleTrait;

class BillServiceProvider extends ServiceProvider
{
    use RecurringBillScheduleTrait;

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__.'/routes/routes.php';
        //include __DIR__.'/routes/api.php';

        $this->loadViewsFrom(__DIR__.'/resources/views', 'bill');
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');

        //register the scheduled tasks
        $this->app->booted(function () {
            $this->recurringBillSchedule(app(Schedule::class));
        });
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->make('Rutatiina\Bill\Http\Controllers\BillController');
    }
}
