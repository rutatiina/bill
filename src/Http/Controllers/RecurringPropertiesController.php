<?php

namespace Rutatiina\Bill\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as FacadesRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Rutatiina\RecurringBill\Models\RecurringBillRecurring;
use Rutatiina\RecurringBill\Models\Setting;
use Rutatiina\FinancialAccounting\Traits\FinancialAccountingTrait;
use Rutatiina\Item\Traits\ItemsVueSearchSelect;
use Yajra\DataTables\Facades\DataTables;
use Rutatiina\FinancialAccounting\Models\Account;

class RecurringPropertiesController extends Controller
{
    use FinancialAccountingTrait;
    use ItemsVueSearchSelect;

    private  $txnEntreeSlug = 'offer';

    public function __construct()
    {
		//$this->middleware('permission:estimates.view');
		//$this->middleware('permission:estimates.create', ['only' => ['create','store']]);
		//$this->middleware('permission:estimates.update', ['only' => ['edit','update']]);
		//$this->middleware('permission:estimates.delete', ['only' => ['destroy']]);
	}

    public function index()
	{
	    //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
	{
        //
    }

    public function show($id)
	{
	    //
    }

    public function edit($id)
	{
	    //
    }

    public function update(Request $request)
	{
	    //update the properties of the recurring record

        $properties = RecurringBillRecurring::find($request->id);
        $properties->status = $request->status;
        $properties->frequency = $request->frequency;
        $properties->save();

        return [
            'status'    => true,
            'messages'  => ['Recurring properties updated'],
            'recurringProperties'  => $properties,
        ];
	}

    public function destroy($id)
	{
	    //
	}

	#-----------------------------------------------------------------------------------
}
