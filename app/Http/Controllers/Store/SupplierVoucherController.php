<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\SupplierPaymentVoucher;
use App\Models\CompanySetting;
use Illuminate\Http\Request;

class SupplierVoucherController extends Controller
{
    /**
     * Display printable Supplier Payment Voucher.
     */
    public function show($id)
    {
        $voucher = SupplierPaymentVoucher::with(['supplier', 'ledgerEntry.reversal', 'ledgerEntry.creator', 'paidBy'])->findOrFail($id);

        $companySetting = CompanySetting::first();
        $isReversed = false;

        if ($voucher->ledgerEntry && $voucher->ledgerEntry->reversal) {
            $isReversed = true;
        }

        return view('store.suppliers.voucher', compact('voucher', 'companySetting', 'isReversed'));
    }
}
