<?php

namespace Rutatiina\Bill\Services;

use Rutatiina\Bill\Models\BillLedger;

class BillLedgersService
{
    public static $errors = [];

    public function __construct()
    {
        //
    }

    public static function store($data)
    {
        foreach ($data['ledgers'] as &$ledger)
        {
            $ledger['bill_id'] = $data['id'];
            BillLedger::create($ledger);
        }
        unset($ledger);

    }

}
