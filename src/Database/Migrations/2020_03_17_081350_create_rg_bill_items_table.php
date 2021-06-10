<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRgBillItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('tenant')->create('rg_bill_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();

            //>> default columns
            $table->softDeletes();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            //<< default columns

            //>> table columns
            $table->unsignedBigInteger('project_id')->nullable();
            $table->unsignedBigInteger('bill_id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->unsignedBigInteger('debit_financial_account_code')->nullable();
            $table->unsignedBigInteger('contact_id')->nullable();
            $table->string('name', 100);
            $table->string('description', 250)->nullable();
            $table->unsignedInteger('quantity');
            $table->unsignedDecimal('rate', 20,5);
            $table->unsignedDecimal('taxable_amount', 20, 5);
            $table->unsignedDecimal('total', 20, 5);
            $table->unsignedDecimal('discount_amount', 20, 5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('tenant')->dropIfExists('rg_bill_items');
    }
}
