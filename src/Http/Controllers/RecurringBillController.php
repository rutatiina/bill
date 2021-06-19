<?php

namespace Rutatiina\Bill\Http\Controllers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Rutatiina\Bill\Models\RecurringBill;
use Rutatiina\Bill\Services\RecurringBillService;
use Rutatiina\FinancialAccounting\Traits\RecurringTrait;

class RecurringBillController extends Controller
{
    use RecurringTrait;

    public function __construct()
    {
        $this->middleware('permission:recurring-bills.view');
        $this->middleware('permission:recurring-bills.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:recurring-bills.update', ['only' => ['edit', 'update']]);
        $this->middleware('permission:recurring-bills.delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('l-limitless-bs4.layout_2-ltr-default.appVue');
        }

        $query = RecurringBill::query();

        if ($request->contact)
        {
            $query->where(function ($q) use ($request)
            {
                $q->where('contact_id', $request->contact);
            });
        }

        $txns = $query->latest()->paginate($request->input('per_page', 20));

        return [
            'tableData' => $txns
        ];
    }

    public function create()
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('l-limitless-bs4.layout_2-ltr-default.appVue');
        }

        $tenant = Auth::user()->tenant;

        $txnAttributes = (new RecurringBill())->rgGetAttributes();

        $txnAttributes['status'] = 'active';
        $txnAttributes['contact'] = json_decode('{"currencies":[]}'); #required
        $txnAttributes['base_currency'] = $tenant->base_currency;
        $txnAttributes['quote_currency'] = $tenant->base_currency;
        $txnAttributes['taxes'] = json_decode('{}');
        $txnAttributes['contact_notes'] = null;
        $txnAttributes['terms_and_conditions'] = null;
        $txnAttributes['items'] = [[
            'selectedTaxes' => [], #required
            'selectedItem' => json_decode('{}'), #required
            'displayTotal' => 0,
            'name' => '',
            'description' => '',
            'rate' => 0,
            'quantity' => 1,
            'total' => 0,
            'taxes' => [],
            'item_id' => '',
            'contact_id' => '',
            'debit_financial_account_code' => 0,
        ]];

        return [
            'pageTitle' => 'Create Recurring Bill', #required
            'pageAction' => 'Create', #required
            'txnUrlStore' => '/recurring-bills', #required
            'txnAttributes' => $txnAttributes, #required
        ];
    }

    public function store(Request $request)
    {
        $storeService = RecurringBillService::store($request);

        if ($storeService == false)
        {
            return [
                'status' => false,
                'messages' => RecurringBillService::$errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Recurring Bill saved'],
            'number' => 0,
            'callback' => URL::route('recurring-bills.show', [$storeService->id], false)
        ];
    }

    public function show($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('l-limitless-bs4.layout_2-ltr-default.appVue');
        }

        $txn = RecurringBill::findOrFail($id);
        $txn->load('contact', 'items.taxes');
        $txn->setAppends([
            'taxes',
            'number_string',
            'total_in_words',
        ]);
        $response = $txn->toArray();
        $response['propertiesOptions'] = $this->propertiesOptions();

        return $response;
    }

    public function edit($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('l-limitless-bs4.layout_2-ltr-default.appVue');
        }

        $txnAttributes = RecurringBillService::edit($id);

        return [
            'pageTitle' => 'Edit Recurring Bill', #required
            'pageAction' => 'Edit', #required
            'txnUrlStore' => '/recurring-bills/' . $id, #required
            'txnAttributes' => $txnAttributes, #required
        ];
    }

    public function update(Request $request)
    {
        //print_r($request->all()); exit;

        $updateService = RecurringBillService::update($request);

        if ($updateService == false)
        {
            return [
                'status' => false,
                'messages' => RecurringBillService::$errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Recurring Bill updated'],
            'number' => 0,
            'callback' => URL::route('recurring-bills.show', [$updateService->id], false)
        ];
    }

    public function destroy($id)
    {
        $destroy = RecurringBillService::destroy($id);

        if ($destroy)
        {
            return [
                'status' => true,
                'messages' => 'Recurring Bill deleted',
            ];
        }
        else
        {
            return [
                'status' => false,
                'messages' => RecurringBillService::$errors
            ];
        }
    }

    #-----------------------------------------------------------------------------------

    public function activate($id)
    {
        $approve = RecurringBillService::activate($id);

        if ($approve == false)
        {
            return [
                'status' => false,
                'messages' => RecurringBillService::$errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Recurring Bill activated'],
        ];

    }

    public function copy($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('l-limitless-bs4.layout_2-ltr-default.appVue');
        }

        $txnAttributes = RecurringBillService::copy($id);


        $data = [
            'pageTitle' => 'Copy Recurring Bill', #required
            'pageAction' => 'Copy', #required
            'txnUrlStore' => '/recurring-bills', #required
            'txnAttributes' => $txnAttributes, #required
        ];

        if (FacadesRequest::wantsJson())
        {
            return $data;
        }
    }

}
