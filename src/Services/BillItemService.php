<?php

namespace Rutatiina\Bill\Services;

use Rutatiina\Bill\Models\BillItem;
use Rutatiina\Bill\Models\BillItemTax;

class BillItemService
{
    public static $errors = [];

    public function __construct()
    {
        //
    }

    public static function store($data)
    {
        //print_r($data['items']); exit;

        //Save the items >> $data['items']
        foreach ($data['items'] as &$item)
        {
            $item['bill_id'] = $data['id'];

            $itemTaxes = (is_array($item['taxes'])) ? $item['taxes'] : [] ;
            unset($item['taxes']);

            $itemModel = BillItem::create($item);

            foreach ($itemTaxes as $tax)
            {
                //save the taxes attached to the item
                $itemTax = new BillItemTax;
                $itemTax->tenant_id = $item['tenant_id'];
                $itemTax->bill_id = $item['bill_id'];
                $itemTax->bill_item_id = $itemModel->id;
                $itemTax->tax_code = $tax['code'];
                $itemTax->amount = $tax['total'];
                $itemTax->inclusive = $tax['inclusive'];
                $itemTax->exclusive = $tax['exclusive'];
                $itemTax->save();
            }
            unset($tax);
        }
        unset($item);

    }

}
