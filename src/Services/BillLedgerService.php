<?php

namespace Rutatiina\Bill\Services;

use Rutatiina\Bill\Models\BillLedger;

class BillLedgerService
{
    public static $errors = [];

    public function __construct()
    {
        //
    }

    public static function store($data)
    {
        //print_r($data['ledgers']); exit;

        //Save the items >> $data['items']
        foreach ($data['ledgers'] as &$ledger)
        {
            $ledger['bill_id'] = $data['id'];
            BillLedger::create($ledger);
        }
        unset($ledger);

    }

}
