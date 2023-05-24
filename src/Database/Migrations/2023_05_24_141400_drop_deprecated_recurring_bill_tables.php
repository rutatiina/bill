<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropDeprecatedRecurringBillTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->dropIfExists('rg_recurring_bill_comments');
        Schema::connection('tenant')->dropIfExists('rg_recurring_bill_items');
        Schema::connection('tenant')->dropIfExists('rg_recurring_bill_ledgers');
        Schema::connection('tenant')->dropIfExists('rg_recurring_bill_recurrings');
        Schema::connection('tenant')->dropIfExists('rg_recurring_bill_settings');
        Schema::connection('tenant')->dropIfExists('rg_recurring_bills');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //do nothing
    }
}
