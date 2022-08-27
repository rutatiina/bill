<?php

namespace Rutatiina\Bill\Http\Controllers;

use Illuminate\Http\Request;
use Rutatiina\Bill\Models\Bill;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Rutatiina\Bill\Models\BillSetting;
use Rutatiina\Bill\Services\BillService;
use Illuminate\Support\Facades\Request as FacadesRequest;

class BillController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:bills.view');
        $this->middleware('permission:bills.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:bills.update', ['only' => ['edit', 'update']]);
        $this->middleware('permission:bills.delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {

        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $query = Bill::query();

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
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $settings = BillSetting::firstOrFail();

        $tenant = Auth::user()->tenant;

        $txnAttributes = (new Bill())->rgGetAttributes();

        $txnAttributes['number'] = BillService::nextNumber();
        $txnAttributes['status'] = 'approved';
        $txnAttributes['contact_id'] = '';
        $txnAttributes['contact'] = json_decode('{"currencies":[]}'); #required
        $txnAttributes['date'] = date('Y-m-d');
        $txnAttributes['base_currency'] = $tenant->base_currency;
        $txnAttributes['quote_currency'] = $tenant->base_currency;
        $txnAttributes['taxes'] = json_decode('{}');
        $txnAttributes['contact_notes'] = null;
        $txnAttributes['terms_and_conditions'] = null;
        $txnAttributes['debit_financial_account'] = '';
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
            'debit_financial_account_code' => $settings->debit_financial_account_code,
        ]];

        return [
            'pageTitle' => 'Create Bill', #required
            'pageAction' => 'Create', #required
            'txnUrlStore' => '/bills', #required
            'txnAttributes' => $txnAttributes, #required
        ];
    }

    public function store(Request $request)
    {
        //return $request->items;

        $storeService = BillService::store($request);

        if (!$storeService)
        {
            return [
                'status' => false,
                'messages' => BillService::$errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Bill saved'],
            'number' => 0,
            'callback' => route('bills.show', [$storeService->id], false)
        ];

    }

    public function show($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $txn = Bill::findOrFail($id);
        $txn->load('contact', 'items.taxes', 'ledgers');
        $txn->setAppends([
            'taxes',
            'number_string',
            'total_in_words',
        ]);

        return $txn->toArray();
    }

    public function edit($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $txnAttributes = BillService::edit($id);

        return [
            'pageTitle' => 'Edit Bill', #required
            'pageAction' => 'Edit', #required
            'txnUrlStore' => '/bills/' . $id, #required
            'txnAttributes' => $txnAttributes, #required
        ];
    }

    public function update(Request $request)
    {
        //print_r($request->all()); exit;

        $storeService = BillService::update($request);

        if ($storeService == false)
        {
            return [
                'status' => false,
                'messages' => BillService::$errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Bill updated'],
            'number' => 0,
            'callback' => route('bills.show', [$storeService->id], false)
        ];
    }

    public function destroy($id)
    {
        $destroy = BillService::destroy($id);

        if ($destroy)
        {
            return [
                'status' => true,
                'messages' => ['Bill deleted'],
                'callback' => route('bills.index', [], false)
            ];
        }
        else
        {
            return [
                'status' => false,
                'messages' => BillService::$errors
            ];
        }
    }

    #-----------------------------------------------------------------------------------

    public function approve($id)
    {
        $approve = BillService::approve($id);

        if ($approve == false)
        {
            return [
                'status' => false,
                'messages' => BillService::$errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Bill approved'],
        ];

    }

    public function copy($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('ui.limitless::layout_2-ltr-default.appVue');
        }

        $txnAttributes = BillService::copy($id);

        return [
            'pageTitle' => 'Copy Bill', #required
            'pageAction' => 'Copy', #required
            'txnUrlStore' => '/bills', #required
            'txnAttributes' => $txnAttributes, #required
        ];
    }

}
