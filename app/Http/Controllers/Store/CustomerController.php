<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\DebitSaleItem;
use App\Models\CashSaleItem;
use App\Models\CustomerLedgerEntry;
use App\Models\Receipt;
use App\Models\CompanySetting;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    /**
     * Quick-create a customer via AJAX (used in the debit-sale / POS forms).
     */
    public function quickStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $customer = Customer::create([
            'name'         => $request->name,
            'phone'        => $request->phone,
            'address'      => $request->address,
            'balance'      => 0,
            'credit_limit' => 0,
        ]);

        return response()->json([
            'success'  => true,
            'customer' => $customer,
        ]);
    }

    /**
     * Customer list with search + totals banner.
     */
    public function index()
    {
        $search          = request('search');
        $showDeactivated = request('show_deactivated') == '1';

        $customers = Customer::when(!$showDeactivated, function ($q) {
            $q->where('status', '!=', 'deactivated');
        })->when($search, function ($q) use ($search) {
            $q->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        })->latest()->paginate(15)->withQueryString();

        $totalCustomers   = Customer::where('status', '!=', 'deactivated')->count();
        // Exclude written off & deactivated customers from active receivable total
        $totalReceivable  = Customer::where('status', 'active')->where('balance', '>', 0)->sum('balance');
        $totalCreditLimit = Customer::where('status', '!=', 'deactivated')->sum('credit_limit');

        return view('store.customers.index', compact(
            'customers',
            'search',
            'showDeactivated',
            'totalCustomers',
            'totalReceivable',
            'totalCreditLimit'
        ));
    }

    /**
     * Customer profile page — KPIs + tabbed transaction histories.
     */
    public function show($id)
    {
        $customer = Customer::findOrFail($id);

        // ── DebitSales (credit invoices) ─────────────────────────────────────
        $debitSales = $customer->debitSales()
            ->withCount('items')
            ->orderBy('invoice_date', 'desc')
            ->get();

        $totalDebitCount  = $debitSales->count();
        $totalDebitAmount = $debitSales->sum('net_total');

        // ── CashSales (POS / cash invoices) ──────────────────────────────────
        $cashSales = $customer->cashSales()
            ->withCount('items')
            ->orderBy('sale_date', 'desc')
            ->get();

        $totalCashCount  = $cashSales->count();
        $totalCashAmount = $cashSales->sum('grand_total');

        // ── Refunds (Returns) ─────────────────────────────────────────────────
        $refunds = $customer->refunds()
            ->withCount('items')
            ->orderBy('refund_date', 'desc')
            ->get();

        $totalRefundCount  = $refunds->count();
        $totalRefundAmount = $refunds->sum('total_amount');

        // ── Grand total items sold (qty) ──────────────────────────────────────
        $totalItemsFromDebit = DebitSaleItem::whereHas('sale', function ($q) use ($id) {
            $q->where('customer_id', $id);
        })->sum('quantity');

        $totalItemsFromCash = CashSaleItem::whereHas('cashSale', function ($q) use ($id) {
            $q->where('customer_id', $id);
        })->sum('quantity');

        $totalItemsSold = $totalItemsFromDebit + $totalItemsFromCash;

        // ── Outstanding / Due ─────────────────────────────────────────────────
        $outstandingAmount = $customer->balance > 0 ? $customer->balance : 0.0;

        // ── Grand totals ──────────────────────────────────────────────────────
        $grandTotalSales    = $totalDebitAmount + $totalCashAmount;
        $netLifetimeValue   = max(0, $grandTotalSales - $totalRefundAmount);

        // ── Ledger Entries ───────────────────────────────────────────────────
        $ledgerEntries = $customer->ledgerEntries()
            ->with(['creator', 'reversal', 'receipt'])
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15, ['*'], 'ledger_page')
            ->withQueryString();

        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $isAdmin = $user && (in_array($role, ['owner', 'admin', 'store admin', 'super_owner', 'manager'])
            || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['owner', 'admin', 'Store Admin', 'Owner', 'manager'])));

        return view('store.customers.show', compact(
            'customer',
            'debitSales',
            'cashSales',
            'refunds',
            'ledgerEntries',
            'isAdmin',
            'totalItemsSold',
            'totalCashCount',
            'totalCashAmount',
            'totalDebitCount',
            'totalDebitAmount',
            'totalRefundCount',
            'totalRefundAmount',
            'outstandingAmount',
            'grandTotalSales',
            'netLifetimeValue'
        ));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('store.customers.create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:30',
            'address'      => 'nullable|string|max:500',
            'credit_limit' => 'nullable|numeric|min:0',
            'balance'      => 'nullable|numeric',
        ]);

        Customer::create([
            'name'         => $request->name,
            'phone'        => $request->phone,
            'address'      => $request->address,
            'credit_limit' => $request->credit_limit ?? 0,
            'balance'      => $request->balance ?? 0,
        ]);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Show the form for editing the given customer.
     */
    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('store.customers.edit', compact('customer'));
    }

    /**
     * Update the given customer.
     */
    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'name'         => 'required|string|max:255',
            'phone'        => 'nullable|string|max:30',
            'address'      => 'nullable|string|max:500',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer->update([
            'name'         => $request->name,
            'phone'        => $request->phone,
            'address'      => $request->address,
            'credit_limit' => $request->credit_limit ?? $customer->credit_limit,
        ]);

        return redirect()->route('customers.show', $customer->id)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Delete the given customer (only if they have no linked transactions).
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);

        $hasHistory = $customer->debitSales()->exists()
            || $customer->cashSales()->exists()
            || $customer->refunds()->exists()
            || $customer->ledgerEntries()->exists();

        $hasZeroBalance = ((float) $customer->balance == 0.0) && ((float) ($customer->store_credit ?? 0) == 0.0);

        if ($hasHistory || !$hasZeroBalance) {
            return redirect()->route('customers.index')
                ->with('error', 'Hard deletion is not allowed for customers with transaction history or non-zero balance. Please deactivate or write off instead.');
        }

        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'Customer record deleted permanently.');
    }

    /**
     * Download a sample Excel template for bulk customer import.
     */
    public function sampleExcel()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        $headers = ['Customer Name', 'Phone', 'Address', 'Credit Limit', 'Opening Balance'];

        foreach ($headers as $i => $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1) . '1';
            $sheet->setCellValue($cell, $header);
        }

        $examples = [
            ['Ali Ahmed',    '03001234567', 'Main Bazaar, Lahore',     '50000', '0'],
            ['Sara Traders', '03219876543', 'Saddar Road, Karachi',    '100000', '5000'],
        ];

        foreach ($examples as $ri => $row) {
            foreach ($row as $ci => $val) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1) . ($ri + 2);
                $sheet->setCellValue($cell, $val);
            }
        }

        foreach (range(1, count($headers)) as $ci) {
            $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci);
            $sheet->getColumnDimension($letter)->setAutoSize(true);
        }

        $sheet->getStyle('A1:E1')->getFont()->setBold(true);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="customers_sample_format.xlsx"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Bulk import customers from Excel (chunked, with duplicate-code detection).
     */
    public function import(Request $request)
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $request->validate([
            'excel_file' => 'required|file|mimes:xls,xlsx,csv,txt',
        ]);

        $file = $request->file('excel_file');

        try {
            $reader      = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getRealPath());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $worksheet   = $spreadsheet->getActiveSheet();

            $highestRow    = $worksheet->getHighestDataRow();
            $highestColumn = $worksheet->getHighestDataColumn();

            if ($highestRow <= 1) {
                return response()->json(['message' => 'The uploaded file does not contain any data rows.'], 422);
            }

            $rows = $worksheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, false, false);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to parse file: ' . $e->getMessage()], 422);
        }

        $headers = array_map(fn($h) => strtolower(trim((string) $h)), $rows[0]);

        $findHeader = function (array $options) use ($headers) {
            foreach ($options as $opt) {
                $idx = array_search(strtolower(trim($opt)), $headers);
                if ($idx !== false) return $idx;
            }
            return false;
        };

        $map = [
            'name'            => $findHeader(['customer name', 'customer_name', 'name', 'client name', 'client']),
            'phone'           => $findHeader(['phone', 'phone no', 'phone_no', 'contact', 'mobile', 'telephone']),
            'address'         => $findHeader(['address', 'location', 'street']),
            'credit_limit'    => $findHeader(['credit limit', 'credit_limit', 'limit']),
            'opening_balance' => $findHeader(['opening balance', 'opening_balance', 'balance', 'opening debt', 'debt']),
        ];

        if ($map['name'] === false) {
            return response()->json(['message' => 'Required column "Customer Name" not found in the sheet.'], 422);
        }

        $inserted     = 0;
        $skippedCount = 0;
        $failedCount  = 0;
        $errors       = [];

        // Load existing customer names (lowercased) to detect duplicates
        $existingNames = Customer::pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower(trim($name)) => $id];
        })->toArray();

        $rowsToInsert = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row       = $rows[$i];
            $rowNumber = $i + 1;

            $name = isset($row[$map['name']]) ? trim((string) $row[$map['name']]) : '';

            if ($name === '') continue;

            $nameKey = strtolower($name);
            if (isset($existingNames[$nameKey]) || isset($rowsToInsert[$nameKey])) {
                $errors[]  = "Row {$rowNumber}: Skipped — customer '{$name}' already exists.";
                $skippedCount++;
                continue;
            }

            $phone   = ($map['phone'] !== false && isset($row[$map['phone']])) ? trim((string) $row[$map['phone']]) : null;
            $address = ($map['address'] !== false && isset($row[$map['address']])) ? trim((string) $row[$map['address']]) : null;

            $creditLimitVal  = ($map['credit_limit'] !== false && isset($row[$map['credit_limit']]) && $row[$map['credit_limit']] !== '') ? $row[$map['credit_limit']] : 0;
            $openingBalVal   = ($map['opening_balance'] !== false && isset($row[$map['opening_balance']]) && $row[$map['opening_balance']] !== '') ? $row[$map['opening_balance']] : 0;

            if (!is_numeric($creditLimitVal)) {
                $errors[] = "Row {$rowNumber}: Credit Limit '{$creditLimitVal}' is not numeric.";
                $failedCount++;
                continue;
            }
            if (!is_numeric($openingBalVal)) {
                $errors[] = "Row {$rowNumber}: Opening Balance '{$openingBalVal}' is not numeric.";
                $failedCount++;
                continue;
            }

            $rowsToInsert[$nameKey] = [
                'name'            => $name,
                'phone'           => $phone,
                'address'         => $address,
                'credit_limit'    => floatval($creditLimitVal),
                'opening_balance' => floatval($openingBalVal),
                'row_number'      => $rowNumber,
            ];
        }

        $chunks = array_chunk(array_values($rowsToInsert), 200);

        foreach ($chunks as $chunk) {
            DB::beginTransaction();
            try {
                foreach ($chunk as $data) {
                    Customer::create([
                        'name'         => $data['name'],
                        'phone'        => $data['phone'],
                        'address'      => $data['address'],
                        'credit_limit' => $data['credit_limit'],
                        'balance'      => $data['opening_balance'],
                    ]);

                    $existingNames[strtolower($data['name'])] = true;
                    $inserted++;
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Customer import chunk failed: ' . $e->getMessage());
                foreach ($chunk as $data) {
                    $errors[] = "Row {$data['row_number']}: Failed to insert due to database error.";
                    $failedCount++;
                }
            }
        }

        return response()->json([
            'inserted'      => $inserted,
            'skipped_count' => $skippedCount,
            'failed_count'  => $failedCount,
            'skipped'       => $errors,
        ]);
    }

    /**
     * Helper to verify admin/owner access for financial balance operations.
     */
    private function checkAdminPermission()
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $role = strtolower($user->role ?? '');
        $isAdmin = in_array($role, ['owner', 'admin', 'store admin', 'super_owner', 'manager'])
            || (method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['owner', 'admin', 'Store Admin', 'Owner', 'manager']));

        if (!$isAdmin) {
            abort(403, 'Unauthorized action. Only admins can modify customer balances.');
        }
    }

    /**
     * Receive payment from customer (Reduces customer debt / balance).
     */
    public function receivePayment(Request $request, $id)
    {
        $this->checkAdminPermission();

        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'method' => 'required|string|in:cash,bank,easypaisa,jazzcash',
            'date'   => 'nullable|date',
            'note'   => 'nullable|string|max:1000',
        ]);

        $amount = (float) $request->amount;
        $customer = Customer::findOrFail($id);

        DB::transaction(function () use ($customer, $amount, $request) {
            if ($customer->balance >= $amount) {
                $customer->balance -= $amount;
            } else {
                $excess = $amount - $customer->balance;
                $customer->balance = 0;
                $customer->store_credit += $excess;
            }
            $customer->save();

            $ledgerEntry = CustomerLedgerEntry::create([
                'customer_id'   => $customer->id,
                'type'          => 'payment_received',
                'amount'        => -$amount, // Negative = customer owes less
                'balance_after' => $customer->balance,
                'method'        => $request->method,
                'note'          => $request->note ?: 'Payment Received (' . ucfirst($request->method) . ')',
                'created_by'    => auth()->id(),
                'created_at'    => $request->filled('date') ? $request->date . ' ' . now()->format('H:i:s') : now(),
            ]);

            $setting = CompanySetting::first();
            $storeName = $setting->business_name ?? config('app.name', 'Supermarket');

            $receiptNumber = Receipt::generateNextReceiptNumber();

            Receipt::create([
                'receipt_number'    => $receiptNumber,
                'customer_id'       => $customer->id,
                'ledger_entry_id'   => $ledgerEntry->id,
                'amount'            => $amount,
                'remaining_balance' => $customer->balance,
                'payment_method'    => $request->method,
                'received_by'       => auth()->id(),
                'store_name'        => $storeName,
            ]);
        });

        $customer->refresh();

        return response()->json([
            'success'      => true,
            'message'      => 'Payment of Rs. ' . number_format($amount, 2) . ' received successfully.',
            'balance'      => (float) $customer->balance,
            'store_credit' => (float) $customer->store_credit,
            'formatted_balance' => 'Rs. ' . number_format($customer->balance, 2),
            'formatted_store_credit' => 'Rs. ' . number_format($customer->store_credit, 2),
        ]);
    }

    /**
     * Pay customer (Payout from Store Credit).
     */
    public function payCustomer(Request $request, $id)
    {
        $this->checkAdminPermission();

        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'method' => 'required|string|in:cash,bank,easypaisa,jazzcash',
            'date'   => 'nullable|date',
            'note'   => 'nullable|string|max:1000',
        ]);

        $amount = (float) $request->amount;
        $customer = Customer::findOrFail($id);

        DB::transaction(function () use ($customer, $amount, $request) {
            if ($customer->store_credit >= $amount) {
                $customer->store_credit -= $amount;
            } else {
                $excess = $amount - $customer->store_credit;
                $customer->store_credit = 0;
                $customer->balance += $excess;
            }
            $customer->save();

            CustomerLedgerEntry::create([
                'customer_id'   => $customer->id,
                'type'          => 'payment_made',
                'amount'        => $amount, // Positive = net debt increases / store credit reduces
                'balance_after' => $customer->balance,
                'method'        => $request->method,
                'note'          => $request->note ?: 'Paid Customer Payout (' . ucfirst($request->method) . ')',
                'created_by'    => auth()->id(),
                'created_at'    => $request->filled('date') ? $request->date . ' ' . now()->format('H:i:s') : now(),
            ]);
        });

        $customer->refresh();

        return response()->json([
            'success'      => true,
            'message'      => 'Paid Rs. ' . number_format($amount, 2) . ' to customer successfully.',
            'balance'      => (float) $customer->balance,
            'store_credit' => (float) $customer->store_credit,
            'formatted_balance' => 'Rs. ' . number_format($customer->balance, 2),
            'formatted_store_credit' => 'Rs. ' . number_format($customer->store_credit, 2),
        ]);
    }

    /**
     * Manual adjustment of customer balance. Note is strictly required.
     */
    public function adjustBalance(Request $request, $id)
    {
        $this->checkAdminPermission();

        $request->validate([
            'action' => 'required|in:add_debt,reduce_debt,set_balance',
            'amount' => 'required|numeric|min:0',
            'note'   => 'required|string|min:3|max:1000',
        ]);

        $customer = Customer::findOrFail($id);
        $amount = (float) $request->amount;
        $action = $request->action;

        DB::transaction(function () use ($customer, $amount, $action, $request) {
            $oldBalance = (float) $customer->balance;
            $delta = 0;
            $newBalance = $oldBalance;

            if ($action === 'add_debt') {
                $delta = $amount;
                $newBalance = $oldBalance + $amount;
            } elseif ($action === 'reduce_debt') {
                $delta = -$amount;
                $newBalance = max(0, $oldBalance - $amount);
            } elseif ($action === 'set_balance') {
                $delta = $amount - $oldBalance;
                $newBalance = $amount;
            }

            $customer->balance = $newBalance;
            $customer->save();

            CustomerLedgerEntry::create([
                'customer_id'   => $customer->id,
                'type'          => 'manual_adjustment',
                'amount'        => $delta,
                'balance_after' => $newBalance,
                'note'          => $request->note,
                'created_by'    => auth()->id(),
            ]);

            AuditLog::record(
                'customer_adjustment',
                "Adjusted balance for customer {$customer->name} ({$action}, delta: Rs. " . number_format($delta, 2) . ") — Note: {$request->note}",
                'Customer',
                $customer->id,
                ['action' => $action, 'delta' => $delta, 'new_balance' => $newBalance]
            );
        });

        $customer->refresh();

        return response()->json([
            'success'      => true,
            'message'      => 'Customer balance adjusted successfully.',
            'balance'      => (float) $customer->balance,
            'store_credit' => (float) $customer->store_credit,
            'formatted_balance' => 'Rs. ' . number_format($customer->balance, 2),
            'formatted_store_credit' => 'Rs. ' . number_format($customer->store_credit, 2),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // WRITE OFF
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Write off a customer's outstanding balance.
     * Sets balance to 0, marks customer as written_off, logs ledger entry.
     */
    public function writeOffBalance(Request $request, $id)
    {
        $this->checkAdminPermission();

        $request->validate([
            'reason_category' => 'required|string|in:absconded,deceased,disputed,business_closed,other',
            'note'            => 'required|string|min:10|max:1000',
        ]);

        $customer = Customer::findOrFail($id);

        if ($customer->status === 'written_off') {
            return response()->json(['success' => false, 'message' => 'Customer balance has already been written off.'], 422);
        }

        if ((float) $customer->balance <= 0) {
            return response()->json(['success' => false, 'message' => 'Customer has no outstanding balance to write off.'], 422);
        }

        DB::transaction(function () use ($customer, $request) {
            $oldBalance = (float) $customer->balance;

            CustomerLedgerEntry::create([
                'customer_id'     => $customer->id,
                'type'            => 'write_off',
                'amount'          => -$oldBalance, // Negative: the debt is forgiven
                'balance_after'   => 0,
                'note'            => $request->note,
                'reason_category' => $request->reason_category,
                'created_by'      => auth()->id(),
            ]);

            $customer->balance       = 0;
            $customer->status        = 'written_off';
            $customer->written_off_at = now();
            $customer->written_off_by = auth()->id();
            $customer->save();

            AuditLog::record(
                'customer_write_off',
                "Wrote off balance of Rs. " . number_format($oldBalance, 2) . " for customer {$customer->name} ({$request->reason_category}) — Note: {$request->note}",
                'Customer',
                $customer->id,
                ['amount' => $oldBalance, 'reason' => $request->reason_category]
            );
        });

        $customer->refresh();

        return response()->json([
            'success'  => true,
            'message'  => 'Customer balance has been written off successfully.',
            'status'   => $customer->status,
            'balance'  => (float) $customer->balance,
            'formatted_balance' => 'Rs. ' . number_format($customer->balance, 2),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REINSTATE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Reinstate a written-off customer to active status.
     * Clears written_off fields and logs a write_off_reversal ledger entry.
     */
    public function reinstateCustomer(Request $request, $id)
    {
        $this->checkAdminPermission();

        $request->validate([
            'note' => 'required|string|min:3|max:1000',
        ]);

        $customer = Customer::findOrFail($id);

        if ($customer->status !== 'written_off') {
            return response()->json(['success' => false, 'message' => 'Customer is not in written-off status.'], 422);
        }

        DB::transaction(function () use ($customer, $request) {
            CustomerLedgerEntry::create([
                'customer_id'   => $customer->id,
                'type'          => 'write_off_reversal',
                'amount'        => 0, // Reinstatement only; no balance restored automatically
                'balance_after' => (float) $customer->balance,
                'note'          => $request->note,
                'created_by'    => auth()->id(),
            ]);

            $customer->status         = 'active';
            $customer->written_off_at = null;
            $customer->written_off_by = null;
            $customer->save();

            AuditLog::record(
                'customer_reinstate',
                "Reinstated customer {$customer->name} to active status — Note: {$request->note}",
                'Customer',
                $customer->id
            );
        });

        $customer->refresh();

        return response()->json([
            'success' => true,
            'message' => 'Customer has been reinstated to active status.',
            'status'  => $customer->status,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REVERSE LEDGER ENTRY
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Reverse a specific payment ledger entry (payment_received / payment_made).
     * Creates an offsetting payment_reversal entry and adjusts customer balance.
     */
    public function reverseLedgerEntry(Request $request, $id, $entryId)
    {
        $this->checkAdminPermission();

        $request->validate([
            'note' => 'required|string|min:3|max:1000',
        ]);

        $customer = Customer::findOrFail($id);
        $entry    = CustomerLedgerEntry::where('customer_id', $id)->findOrFail($entryId);

        // Only reversible types
        if (!in_array($entry->type, ['payment_received', 'payment_made'])) {
            return response()->json(['success' => false, 'message' => 'Only payment entries can be reversed.'], 422);
        }

        // Check if already reversed
        $alreadyReversed = CustomerLedgerEntry::where('reversed_entry_id', $entry->id)->exists();
        if ($alreadyReversed) {
            return response()->json(['success' => false, 'message' => 'This entry has already been reversed.'], 422);
        }

        DB::transaction(function () use ($customer, $entry, $request) {
            // The reversal amount is the opposite sign of the original entry
            $reversalAmount = -$entry->amount;

            // Adjust customer balance: reversal un-does the original effect
            $newBalance = (float) $customer->balance + $reversalAmount;
            $customer->balance = max(0, $newBalance);
            $customer->save();

            CustomerLedgerEntry::create([
                'customer_id'      => $customer->id,
                'type'             => 'payment_reversal',
                'amount'           => $reversalAmount,
                'balance_after'    => $customer->balance,
                'note'             => $request->note,
                'reversed_entry_id'=> $entry->id,
                'method'           => $entry->method,
                'created_by'       => auth()->id(),
            ]);

            AuditLog::record(
                'customer_reversal',
                "Reversed payment entry #{$entry->id} for customer {$customer->name} (amount: Rs. " . number_format(abs($reversalAmount), 2) . ") — Note: {$request->note}",
                'Customer',
                $customer->id,
                ['entry_id' => $entry->id, 'amount' => $reversalAmount]
            );
        });

        $customer->refresh();

        return response()->json([
            'success'  => true,
            'message'  => 'Entry reversed successfully.',
            'balance'  => (float) $customer->balance,
            'formatted_balance' => 'Rs. ' . number_format($customer->balance, 2),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DEACTIVATE / REACTIVATE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Toggle customer status between active and deactivated.
     * Cannot deactivate a customer who is currently written_off.
     */
    public function deactivateCustomer(Request $request, $id)
    {
        $this->checkAdminPermission();

        $customer = Customer::findOrFail($id);

        if ($customer->status === 'written_off') {
            return redirect()->route('customers.show', $id)
                ->with('error', 'Cannot deactivate a written-off customer. Reinstate them first.');
        }

        if ($customer->status === 'deactivated') {
            $customer->status = 'active';
            $customer->save();

            AuditLog::record(
                'customer_status_change',
                "Reactivated customer {$customer->name}",
                'Customer',
                $customer->id
            );

            return redirect()->route('customers.show', $id)
                ->with('success', 'Customer has been reactivated.');
        }

        $customer->status = 'deactivated';
        $customer->save();

        AuditLog::record(
            'customer_status_change',
            "Deactivated customer {$customer->name}",
            'Customer',
            $customer->id
        );

        return redirect()->route('customers.show', $id)
            ->with('success', 'Customer has been deactivated. They will be hidden from the active customer list.');
    }
}

