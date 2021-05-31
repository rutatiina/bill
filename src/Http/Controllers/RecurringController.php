<?php

namespace Rutatiina\Bill\Http\Controllers;

use URL;
use PDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Support\Facades\View;
use Rutatiina\Bill\Models\BillRecurring;
use Rutatiina\Bill\Models\Setting;
use Rutatiina\FinancialAccounting\Traits\FinancialAccountingTrait;
use Rutatiina\Contact\Traits\ContactTrait;
use Yajra\DataTables\Facades\DataTables;
use Rutatiina\FinancialAccounting\Traits\Recurring as FinancialAccountingRecurringTrait;

use Rutatiina\Bill\Classes\Recurring\Store as TxnStore;
use Rutatiina\Bill\Classes\Recurring\Approve as TxnApprove;
use Rutatiina\Bill\Classes\Recurring\Read as TxnRead;
use Rutatiina\Bill\Classes\Recurring\Copy as TxnCopy;
use Rutatiina\Bill\Classes\Recurring\Number as TxnNumber;
use Rutatiina\Bill\Traits\Recurring\Item as TxnItem;
use Rutatiina\Bill\Classes\Recurring\Edit as TxnEdit;
use Rutatiina\Bill\Classes\Recurring\Update as TxnUpdate;

class RecurringController extends Controller
{
    use FinancialAccountingTrait;
    use ContactTrait;
    use TxnItem;

    // >> get the item attributes template << !!important

    use FinancialAccountingRecurringTrait;

    private $txnEntreeSlug = 'recurring-bill';

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

        $query = BillRecurring::query();

        if ($request->contact)
        {
            $query->where(function ($q) use ($request)
            {
                $q->where('debit_contact_id', $request->contact);
                $q->orWhere('credit_contact_id', $request->contact);
            });
        }

        $txns = $query->latest()->paginate($request->input('per_page', 20));

        return [
            'tableData' => $txns
        ];
    }

    private function nextNumber()
    {
        $txn = BillRecurring::latest()->first();
        $settings = Setting::first();

        return $settings->number_prefix . (str_pad((optional($txn)->number + 1), $settings->minimum_number_length, "0", STR_PAD_LEFT)) . $settings->number_postfix;
    }

    public function create()
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('l-limitless-bs4.layout_2-ltr-default.appVue');
        }

        $tenant = Auth::user()->tenant;

        $txnAttributes = (new BillRecurring())->rgGetAttributes();

        $txnAttributes['number'] = $this->nextNumber();

        $txnAttributes['status'] = 'approved';
        $txnAttributes['contact_id'] = '';
        $txnAttributes['contact'] = json_decode('{"currencies":[]}'); #required
        $txnAttributes['date'] = date('Y-m-d');
        $txnAttributes['base_currency'] = $tenant->base_currency;
        $txnAttributes['quote_currency'] = $tenant->base_currency;
        $txnAttributes['taxes'] = json_decode('{}');
        $txnAttributes['isRecurring'] = true;
        $txnAttributes['recurring'] = [
            'status' => 'active',
            'frequency' => 'monthly',
            'date_range' => [], //used by vue
            'start_date' => '',
            'end_date' => '',
            'day_of_month' => '*',
            'month' => '*',
            'day_of_week' => '*',
        ];
        $txnAttributes['contact_notes'] = null;
        $txnAttributes['terms_and_conditions'] = null;
        $txnAttributes['items'] = [$this->itemCreate()];

        unset($txnAttributes['txn_entree_id']); //!important
        unset($txnAttributes['txn_type_id']); //!important
        unset($txnAttributes['debit_contact_id']); //!important
        unset($txnAttributes['credit_contact_id']); //!important

        $data = [
            'pageTitle' => 'Create Recurring Bill', #required
            'pageAction' => 'Create', #required
            'txnUrlStore' => '/recurring-bills', #required
            'txnAttributes' => $txnAttributes, #required
        ];

        return $data;

    }

    public function store(Request $request)
    {
        $TxnStore = new TxnStore();
        $TxnStore->txnEntreeSlug = $this->txnEntreeSlug;
        $TxnStore->txnInsertData = $request->all();
        $insert = $TxnStore->run();

        if ($insert == false)
        {
            return [
                'status' => false,
                'messages' => $TxnStore->errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Recurring Bill saved'],
            'number' => 0,
            'callback' => URL::route('recurring-bills.show', [$insert->id], false)
        ];
    }

    public function show($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('l-limitless-bs4.layout_2-ltr-default.appVue');
        }

        if (FacadesRequest::wantsJson())
        {
            $TxnRead = new TxnRead();
            $data = $TxnRead->run($id);
            $data['recurringOptions'] = $this->recurringOptions();
            return $data;
        }
    }

    public function edit($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('l-limitless-bs4.layout_2-ltr-default.appVue');
        }

        $TxnEdit = new TxnEdit();
        $txnAttributes = $TxnEdit->run($id);

        $data = [
            'pageTitle' => 'Edit Recurring Bill', #required
            'pageAction' => 'Edit', #required
            'txnUrlStore' => '/recurring-bills/' . $id, #required
            'txnAttributes' => $txnAttributes, #required
        ];

        if (FacadesRequest::wantsJson())
        {
            return $data;
        }
    }

    public function update(Request $request)
    {
        //print_r($request->all()); exit;

        $TxnStore = new TxnUpdate();
        $TxnStore->txnInsertData = $request->all();
        $insert = $TxnStore->run();

        if ($insert == false)
        {
            return [
                'status' => false,
                'messages' => $TxnStore->errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Recurring Bill updated'],
            'number' => 0,
            'callback' => URL::route('recurring-bills.show', [$insert->id], false)
        ];
    }

    public function destroy()
    {
    }

    #-----------------------------------------------------------------------------------

    public function approve($id)
    {
        $TxnApprove = new TxnApprove();
        $approve = $TxnApprove->run($id);

        if ($approve == false)
        {
            return [
                'status' => false,
                'messages' => $TxnApprove->errors
            ];
        }

        return [
            'status' => true,
            'messages' => ['Recurring Bill approved'],
        ];

    }

    public function copy($id)
    {
        //load the vue version of the app
        if (!FacadesRequest::wantsJson())
        {
            return view('l-limitless-bs4.layout_2-ltr-default.appVue');
        }

        $TxnCopy = new TxnCopy();
        $txnAttributes = $TxnCopy->run($id);

        $TxnNumber = new TxnNumber();
        $txnAttributes['number'] = $TxnNumber->run($this->txnEntreeSlug);


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

    public function exportToExcel(Request $request)
    {

        $txns = collect([]);

        $txns->push([
            'DATE',
            'REFERENCE',
            'SUPPLIER / VENDOR',
            'EXPIRY DATE',
            'TOTAL',
            ' ', //Currency
        ]);

        foreach (array_reverse($request->ids) as $id)
        {
            $txn = Transaction::transaction($id);

            $txns->push([
                $txn->date,
                $txn->reference,
                $txn->contact_name,
                $txn->expiry_date,
                $txn->total,
                $txn->base_currency,
            ]);
        }

        $export = $txns->downloadExcel(
            'maccounts-recurring-bills-export-' . date('Y-m-d-H-m-s') . '.xlsx',
            null,
            false
        );

        //$books->load('author', 'publisher'); //of no use

        return $export;
    }

}
