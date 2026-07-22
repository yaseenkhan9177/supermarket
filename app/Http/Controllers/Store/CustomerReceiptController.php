<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Models\CompanySetting;
use Illuminate\Http\Request;

class CustomerReceiptController extends Controller
{
    /**
     * Display printable Customer Payment Receipt.
     */
    public function show($id)
    {
        $receipt = Receipt::with(['customer', 'ledgerEntry.reversal', 'ledgerEntry.creator', 'receivedBy'])->findOrFail($id);

        $companySetting = CompanySetting::first();
        $isReversed = false;

        if ($receipt->ledgerEntry && $receipt->ledgerEntry->reversal) {
            $isReversed = true;
        }

        return view('store.customers.receipt', compact('receipt', 'companySetting', 'isReversed'));
    }
}
