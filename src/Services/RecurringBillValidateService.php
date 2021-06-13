<?php

namespace Rutatiina\Bill\Services;

use Illuminate\Support\Facades\Validator;
use Rutatiina\Contact\Models\Contact;
use Rutatiina\Bill\Models\RecurringBillSetting;

class RecurringBillValidateService
{
    public static $errors = [];

    public static function run($requestInstance)
    {
        //$request = request(); //used for the flash when validation fails
        $user = auth()->user();


        // >> data validation >>------------------------------------------------------------

        //validate the data
        $customMessages = [
            'con_day_of_month.required_if' => "The day of month to recurr is required",
            'con_month.required_if' => "The month to recurr is required",
            'con_day_of_week.required_if' => "The day of week to recurr is required",

            'items.*.debit_financial_account_code.required' => "The item account is required",
            'items.*.debit_financial_account_code.numeric' => "The item account must be numeric",
            'items.*.debit_financial_account_code.gt' => "The item account is required",
            'items.*.taxes.*.code.required' => "Tax code is required",
            'items.*.taxes.*.total.required' => "Tax total is required",
        ];

        $rules = [
            'profile_name' => 'required|string|max:250',
            'contact_id' => 'required|numeric',
            'base_currency' => 'required',

            'frequency' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'con_day_of_month' => 'required_if:frequency,custom|string',
            'con_month' => 'required_if:frequency,custom|string',
            'con_day_of_week' => 'required_if:frequency,custom|string',

            'items' => 'bail|required|array',
            'items.*.name' => 'bail|required',
            'items.*.rate' => 'bail|required|numeric',
            'items.*.quantity' => 'bail|required|numeric|gt:0',
            //'items.*.total' => 'bail|required|numeric|in:' . $itemTotal, //todo custom validator to check this
            'items.*.units' => 'bail|numeric|nullable',
            'items.*.debit_financial_account_code' => 'bail|required|numeric|gt:0',
            'items.*.taxes' => 'bail|array|nullable',

            'items.*.taxes.*.code' => 'bail|required',
            'items.*.taxes.*.total' => 'bail|required|numeric',
            //'items.*.taxes.*.exclusive' => 'required|numeric',
        ];

        $validator = Validator::make($requestInstance->all(), $rules, $customMessages);

        if ($validator->fails())
        {
            self::$errors = $validator->errors()->all();
            return false;
        }

        // << data validation <<------------------------------------------------------------

        $contact = Contact::findOrFail($requestInstance->contact_id);

        $data['id'] = $requestInstance->input('id', null); //for updating the id will always be posted
        $data['user_id'] = $user->id;
        $data['tenant_id'] = $user->tenant->id;
        $data['created_by'] = $user->name;
        $data['app'] = 'web';
        $data['profile_name'] = $requestInstance->input('profile_name');
        $data['contact_id'] = $requestInstance->contact_id;
        $data['contact_name'] = $contact->name;
        $data['contact_address'] = trim($contact->shipping_address_street1 . ' ' . $contact->shipping_address_street2);
        $data['reference'] = $requestInstance->input('reference', null);
        $data['base_currency'] =  $requestInstance->input('base_currency');
        $data['quote_currency'] =  $requestInstance->input('quote_currency', $data['base_currency']);
        $data['exchange_rate'] = $requestInstance->input('exchange_rate', 1);
        $data['branch_id'] = $requestInstance->input('branch_id', null);
        $data['store_id'] = $requestInstance->input('store_id', null);
        $data['due_date'] = $requestInstance->input('due_date', null);
        $data['payment_terms'] = $requestInstance->input('payment_terms', null);

        $data['status'] = $requestInstance->input('status', null);
        $data['frequency'] = $requestInstance->input('frequency', null);
        $data['start_date'] = $requestInstance->input('start_date', null);
        $data['end_date'] = $requestInstance->input('end_date', null);
        $data['cron_day_of_month'] = $requestInstance->input('cron_day_of_month', null);
        $data['cron_month'] = $requestInstance->input('cron_month', null);
        $data['cron_day_of_week'] = $requestInstance->input('cron_day_of_week', null);


        //set the transaction total to zero
        $txnTotal = 0;
        $taxableAmount = 0;

        //Formulate the DB ready items array
        $data['items'] = [];
        foreach ($requestInstance->items as $key => $item)
        {
            $itemTaxes = $requestInstance->input('items.'.$key.'.taxes', []);

            $txnTotal           += ($item['rate']*$item['quantity']);
            $taxableAmount      += ($item['rate']*$item['quantity']);
            $itemTaxableAmount   = ($item['rate']*$item['quantity']); //calculate the item taxable amount

            foreach ($itemTaxes as $itemTax)
            {
                $txnTotal           += $itemTax['exclusive'];
                $taxableAmount      -= $itemTax['inclusive'];
                $itemTaxableAmount  -= $itemTax['inclusive']; //calculate the item taxable amount more by removing the inclusive amount
            }

            $data['items'][] = [
                'tenant_id' => $data['tenant_id'],
                'created_by' => $data['created_by'],
                'contact_id' => $item['contact_id'],
                'item_id' => $item['item_id'],
                'debit_financial_account_code' => $item['debit_financial_account_code'],
                'name' => $item['name'],
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'rate' => $item['rate'],
                'total' => $item['total'],
                'taxable_amount' => $itemTaxableAmount,
                'taxes' => $itemTaxes,
            ];
        }

        $data['taxable_amount'] = $taxableAmount;
        $data['total'] = $txnTotal;


        //print_r($data); exit;

        return $data;

    }

}
