<?php

namespace Rutatiina\Bill\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Rutatiina\Bill\Models\RecurringBill;
use Rutatiina\Tax\Models\Tax;

class RecurringBillService
{
    public static $errors = [];

    public function __construct()
    {
        //
    }

    public static function edit($id)
    {
        $taxes = Tax::all()->keyBy('code');

        $txn = RecurringBill::findOrFail($id);
        $txn->load('contact', 'items.taxes');
        $txn->setAppends(['taxes', 'date_range', 'is_recurring']);

        $attributes = $txn->toArray();

        //print_r($attributes); exit;

        $attributes['_method'] = 'PATCH';

        $attributes['contact']['currency'] = $attributes['contact']['currency_and_exchange_rate'];
        $attributes['contact']['currencies'] = $attributes['contact']['currencies_and_exchange_rates'];

        $attributes['taxes'] = json_decode('{}');

        foreach ($attributes['items'] as $key => &$item)
        {
            $selectedItem = [
                'id' => $item['item_id'],
                'name' => $item['name'],
                'description' => $item['description'],
                'rate' => $item['rate'],
                'tax_method' => 'inclusive',
                'account_type' => null,
            ];

            $item['selectedItem'] = $selectedItem; #required
            $item['selectedTaxes'] = []; #required
            $item['displayTotal'] = 0; #required

            foreach ($item['taxes'] as $itemTax)
            {
                $item['selectedTaxes'][] = $taxes[$itemTax['tax_code']];
            }

            $item['rate'] = floatval($item['rate']);
            $item['quantity'] = floatval($item['quantity']);
            $item['total'] = floatval($item['total']);
            $item['displayTotal'] = $item['total']; #required
        };
        unset($item);

        return $attributes;
    }

    public static function store($requestInstance)
    {
        $data = RecurringBillValidateService::run($requestInstance);
        //print_r($data); exit;
        if ($data === false)
        {
            self::$errors = RecurringBillValidateService::$errors;
            return false;
        }

        //start database transaction
        DB::connection('tenant')->beginTransaction();

        try
        {
            $Txn = new RecurringBill;
            $Txn->tenant_id = $data['tenant_id'];
            $Txn->created_by = Auth::id();
            $Txn->profile_name = $data['profile_name'];
            $Txn->contact_id = $data['contact_id'];
            $Txn->contact_name = $data['contact_name'];
            $Txn->contact_address = $data['contact_address'];
            $Txn->reference = $data['reference'];
            $Txn->base_currency = $data['base_currency'];
            $Txn->quote_currency = $data['quote_currency'];
            $Txn->exchange_rate = $data['exchange_rate'];
            $Txn->taxable_amount = $data['taxable_amount'];
            $Txn->total = $data['total'];
            $Txn->branch_id = $data['branch_id'];
            $Txn->store_id = $data['store_id'];
            $Txn->payment_terms = $data['payment_terms'];

            $Txn->status = $data['status'];
            $Txn->frequency = $data['frequency'];
            $Txn->start_date = $data['start_date'];
            $Txn->end_date = $data['end_date'];
            $Txn->cron_day_of_month = $data['cron_day_of_month'];
            $Txn->cron_month = $data['cron_month'];
            $Txn->cron_day_of_week = $data['cron_day_of_week'];

            $Txn->save();

            $data['id'] = $Txn->id;

            //print_r($data['items']); exit;

            //Save the items >> $data['items']
            RecurringBillItemService::store($data);

            DB::connection('tenant')->commit();

            return $Txn;

        }
        catch (\Throwable $e)
        {
            DB::connection('tenant')->rollBack();

            Log::critical('Fatal Internal Error: Failed to save recurring bill to database');
            Log::critical($e);

            //print_r($e); exit;
            if (App::environment('local'))
            {
                self::$errors[] = 'Error: Failed to save recurring bill to database.';
                self::$errors[] = 'File: ' . $e->getFile();
                self::$errors[] = 'Line: ' . $e->getLine();
                self::$errors[] = 'Message: ' . $e->getMessage();
            }
            else
            {
                self::$errors[] = 'Fatal Internal Error: Failed to save recurring bill to database. Please contact Admin';
            }

            return false;
        }
        //*/

    }

    public static function update($requestInstance)
    {
        $data = RecurringBillValidateService::run($requestInstance);
        //print_r($data); exit;
        if ($data === false)
        {
            self::$errors = RecurringBillValidateService::$errors;
            return false;
        }

        //start database transaction
        DB::connection('tenant')->beginTransaction();

        try
        {
            $Txn = RecurringBill::with('items')->findOrFail($data['id']);

            if ($Txn->status == 'active')
            {
                self::$errors[] = 'Active transaction cannot be not be edited';
                return false;
            }

            //Delete affected relations
            $Txn->items()->delete();
            $Txn->item_taxes()->delete();
            $Txn->delete();

            $txnStore = self::store($requestInstance);

            DB::connection('tenant')->commit();

            return $txnStore;

        }
        catch (\Throwable $e)
        {
            DB::connection('tenant')->rollBack();

            Log::critical('Fatal Internal Error: Failed to update recurring bill in database');
            Log::critical($e);

            //print_r($e); exit;
            if (App::environment('local'))
            {
                self::$errors[] = 'Error: Failed to update recurring bill in database.';
                self::$errors[] = 'File: ' . $e->getFile();
                self::$errors[] = 'Line: ' . $e->getLine();
                self::$errors[] = 'Message: ' . $e->getMessage();
            }
            else
            {
                self::$errors[] = 'Fatal Internal Error: Failed to update recurring bill in database. Please contact Admin';
            }

            return false;
        }

    }

    public static function destroy($id)
    {
        //start database transaction
        DB::connection('tenant')->beginTransaction();

        try
        {
            $Txn = RecurringBill::findOrFail($id);

            if ($Txn->status == 'active')
            {
                self::$errors[] = 'Active transaction cannot be not be deleted';
                return false;
            }

            //Delete affected relations
            $Txn->items()->delete();
            $Txn->item_taxes()->delete();
            $Txn->delete();

            DB::connection('tenant')->commit();

            return true;

        }
        catch (\Throwable $e)
        {
            DB::connection('tenant')->rollBack();

            Log::critical('Fatal Internal Error: Failed to delete recurrinf bill from database');
            Log::critical($e);

            //print_r($e); exit;
            if (App::environment('local'))
            {
                self::$errors[] = 'Error: Failed to delete recurring bill from database.';
                self::$errors[] = 'File: ' . $e->getFile();
                self::$errors[] = 'Line: ' . $e->getLine();
                self::$errors[] = 'Message: ' . $e->getMessage();
            }
            else
            {
                self::$errors[] = 'Fatal Internal Error: Failed to delete recurring bill from database. Please contact Admin';
            }

            return false;
        }
    }

    public static function copy($id)
    {
        $taxes = Tax::all()->keyBy('code');

        $txn = RecurringBill::findOrFail($id);
        $txn->load('contact', 'items.taxes');
        $txn->setAppends(['taxes', 'date_range', 'is_recurring']);

        $attributes = $txn->toArray();

        $attributes['isRecurring'] = true;

        #reset some values
        $attributes['date'] = date('Y-m-d');
        $attributes['due_date'] = '';
        $attributes['profile_name'] = '';
        #reset some values

        $attributes['contact']['currency'] = $attributes['contact']['currency_and_exchange_rate'];
        $attributes['contact']['currencies'] = $attributes['contact']['currencies_and_exchange_rates'];

        $attributes['taxes'] = json_decode('{}');

        foreach ($attributes['items'] as $key => &$item)
        {
            $selectedItem = [
                'id' => $item['item_id'],
                'name' => $item['name'],
                'description' => $item['description'],
                'rate' => $item['rate'],
                'tax_method' => 'inclusive',
                'account_type' => null,
            ];

            $item['selectedItem'] = $selectedItem; #required
            $item['selectedTaxes'] = []; #required
            $item['displayTotal'] = 0; #required
            $item['rate'] = floatval($item['rate']);
            $item['quantity'] = floatval($item['quantity']);
            $item['total'] = floatval($item['total']);
            $item['displayTotal'] = $item['total']; #required

            foreach ($item['taxes'] as $itemTax)
            {
                $item['selectedTaxes'][] = $taxes[$itemTax['tax_code']];
            }
        };
        unset($item);

        return $attributes;
    }

    public static function activate($id)
    {
        $Txn = RecurringBill::findOrFail($id);

        if (strtolower($Txn->status) != 'draft')
        {
            self::$errors[] = $Txn->status . ' recurring bill cannot be activated';
            return false;
        }

        //start database transaction
        DB::connection('tenant')->beginTransaction();

        try
        {
            //update the status of the txn
            $Txn->status = 'active';
            $Txn->save();

            DB::connection('tenant')->commit();

            return true;

        }
        catch (\Exception $e)
        {
            DB::connection('tenant')->rollBack();
            //print_r($e); exit;
            if (App::environment('local'))
            {
                self::$errors[] = 'DB Error: Failed to approve recurring bill.';
                self::$errors[] = 'File: ' . $e->getFile();
                self::$errors[] = 'Line: ' . $e->getLine();
                self::$errors[] = 'Message: ' . $e->getMessage();
            }
            else
            {
                self::$errors[] = 'Fatal Internal Error: Failed to approve recurring bill. Please contact Admin';
            }

            return false;
        }
    }

}
