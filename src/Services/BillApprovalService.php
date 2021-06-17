<?php

namespace Rutatiina\Bill\Services;

use Rutatiina\FinancialAccounting\Services\AccountBalanceUpdateService;
use Rutatiina\FinancialAccounting\Services\ContactBalanceUpdateService;

trait BillApprovalService
{
    public static function run($data)
    {
        if ($data['status'] != 'approved')
        {
            //can only update balances if status is approved
            return false;
        }

        if (isset($data['balances_where_updated']) && $data['balances_where_updated'])
        {
            //cannot update balances for task already completed
            return false;
        }

        //inventory checks and inventory balance update if needed
        //$this->inventory(); //currently inventory update for estimates is disabled

        //Update the account balances
        AccountBalanceUpdateService::doubleEntry($data['ledgers']);

        //Update the contact balances
        ContactBalanceUpdateService::doubleEntry($data['ledgers']);

        return true;
    }

}
