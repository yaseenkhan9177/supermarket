<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierCategory;
use App\Models\SupplierLedger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers   = Supplier::with('category')->latest()->paginate(10);
        $categories  = SupplierCategory::all();
        $totalPayable = Supplier::where('current_balance', '>', 0)->sum('current_balance');
        $totalAdvance = Supplier::where('current_balance', '<', 0)->sum('current_balance');

        return view('suppliers.index', compact('suppliers', 'categories', 'totalPayable', 'totalAdvance'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'code'            => 'required|string|max:50|unique:suppliers,code',
            'phone'           => 'nullable|string|max:20',
            'category_id'     => 'nullable|exists:supplier_categories,id',
            'opening_balance' => 'nullable|numeric',
        ]);

        Supplier::create([
            'name'            => $request->name,
            'company_name'    => $request->company_name,
            'code'            => strtoupper($request->code),
            'category_id'     => $request->category_id,
            'phone'           => $request->phone,
            'address'         => $request->address,
            'current_balance' => $request->opening_balance ?? 0,
            'opening_balance' => $request->opening_balance ?? 0,
        ]);

        return redirect()->back()->with('success', 'Supplier Added Successfully');
    }

    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:suppliers,code,' . $supplier->id,
            'category_id' => 'nullable|exists:supplier_categories,id',
        ]);

        $supplier->update([
            'name'         => $request->name,
            'company_name' => $request->company_name,
            'code'         => strtoupper($request->code),
            'category_id'  => $request->category_id,
            'phone'        => $request->phone,
            'address'      => $request->address,
        ]);

        return redirect()->back()->with('success', 'Supplier Updated');
    }

    public function destroy($id)
    {
        Supplier::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Supplier Deleted');
    }

    /**
     * Quick-create a supplier via AJAX (used in the purchase bill form).
     */
    public function quickStore(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $supplier = Supplier::create([
            'name'            => $request->name,
            'phone'           => $request->phone,
            'address'         => $request->address,
            'current_balance' => 0,
            'opening_balance' => 0,
        ]);

        return response()->json(['success' => true, 'supplier' => $supplier]);
    }

    /**
     * Show the full ledger (transaction history) for a single supplier.
     */
    public function ledger($id)
    {
        $supplier = Supplier::with('category')->findOrFail($id);
        $entries  = SupplierLedger::where('supplier_id', $id)
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        return view('suppliers.ledger', compact('supplier', 'entries'));
    }

    /**
     * API: Return the supplier's current credit amount.
     * Used by the purchase form to display the auto-apply credit banner.
     */
    public function getCredit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return response()->json([
            'has_credit'    => $supplier->has_credit,
            'credit_amount' => $supplier->return_credit,
            'balance'       => $supplier->current_balance,
        ]);
    }

    public function show($id)
    {
        $supplier = Supplier::with('category')->findOrFail($id);

        // Calculate KPIs
        $totalItemsSupplied = \App\Models\PurchaseItem::whereHas('purchase', function($q) use ($id) {
            $q->where('supplier_id', $id);
        })->sum('qty');

        $cashPurchases = $supplier->purchases()->where(function($q) {
            $q->where('payment_type', 'Cash')->orWhere('payment_type', 'cash');
        })->get();
        $totalCashCount = $cashPurchases->count();
        $totalCashAmount = $cashPurchases->sum('net_total');

        $creditPurchases = $supplier->purchases()->where(function($q) {
            $q->where('payment_type', 'Credit')->orWhere('payment_type', 'credit');
        })->get();
        $totalCreditCount = $creditPurchases->count();
        $totalCreditAmount = $creditPurchases->sum('net_total');

        $totalPayments = $supplier->payments()->sum('amount');
        $outstandingAmount = $supplier->opening_balance + $totalCreditAmount - $totalPayments;

        $grandTotalAmount = $supplier->purchases()->sum('net_total');

        $purchases = $supplier->purchases()
            ->withCount('items')
            ->orderBy('invoice_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Load recent payments for accordion
        $payments = $supplier->payments()->orderBy('payment_date', 'desc')->paginate(5);

        // Fetch low-stock items where this supplier is preferred
        $reorderItems = \App\Models\Item::where('preferred_supplier_id', $id)
            ->whereNotNull('min_stock_level')
            ->where('min_stock_level', '>', 0)
            ->whereColumn('on_hand', '<', 'min_stock_level')
            ->get();

        return view('suppliers.show', compact(
            'supplier',
            'totalItemsSupplied',
            'totalCashCount',
            'totalCashAmount',
            'totalCreditCount',
            'totalCreditAmount',
            'outstandingAmount',
            'grandTotalAmount',
            'purchases',
            'payments',
            'reorderItems'
        ));
    }

    // Store a payment for the supplier
    public function storePayment(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,bank_transfer,cheque,other',
            'reference_note' => 'nullable|string|max:255',
        ]);

        $supplier = Supplier::findOrFail($id);
        SupplierPayment::create([
            'supplier_id' => $supplier->id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'reference_note' => $request->reference_note,
        ]);

        // Adjust supplier balance (reduce payable)
        $supplier->current_balance -= $request->amount;
        $supplier->save();

        return redirect()->back()->with('success', 'Payment recorded successfully');
    }

    // List payments for a supplier
    public function paymentIndex($id)
    {
        $supplier = Supplier::with('payments')->findOrFail($id);
        $payments = $supplier->payments()->orderBy('payment_date', 'desc')->paginate(10);
        return view('suppliers.payments', compact('supplier', 'payments'));
    }

    public function sampleExcel()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Supplier Name', 
            'Unique Code', 
            'Category', 
            'Company Name', 
            'Phone', 
            'Address', 
            'Opening Balance'
        ];

        foreach ($headers as $colIdx => $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1) . '1';
            $sheet->setCellValue($cell, $header);
        }

        $examples = [
            [
                'ABC Distributors',
                'SUP-001',
                'Wholesale',
                'ABC Group of Companies',
                '03001234567',
                'Main Market Road, Plaza 4, Lahore',
                '15000'
            ],
            [
                'Zahid Local Vendor',
                'SUP-002',
                'Local Vendor',
                '',
                '03219876543',
                'Saddar Area, Karachi',
                '0'
            ]
        ];

        foreach ($examples as $rowIdx => $row) {
            foreach ($row as $colIdx => $value) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx + 1) . ($rowIdx + 2);
                $sheet->setCellValue($cell, $value);
            }
        }

        foreach (range(1, count($headers)) as $colIdx) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $sheet->getStyle('A1:G1')->getFont()->setBold(true);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="suppliers_sample_format.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    public function import(Request $request)
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $request->validate([
            'excel_file' => 'required|file|mimes:xls,xlsx,csv,txt',
        ]);

        $file = $request->file('excel_file');
        
        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getRealPath());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            
            $highestRow = $worksheet->getHighestDataRow();
            $highestColumn = $worksheet->getHighestDataColumn();
            
            if ($highestRow <= 1) {
                return response()->json(['message' => 'The uploaded file does not contain any data rows.'], 422);
            }
            
            $rows = $worksheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, false, false);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to parse file: ' . $e->getMessage()], 422);
        }

        $headers = array_map(function($h) {
            return strtolower(trim($h));
        }, $rows[0]);

        $findHeader = function(array $options) use ($headers) {
            foreach ($options as $opt) {
                $idx = array_search(strtolower(trim($opt)), $headers);
                if ($idx !== false) {
                    return $idx;
                }
            }
            return false;
        };

        $map = [
            'name'            => $findHeader(['supplier name', 'supplier_name', 'name', 'title', 'vendor name', 'vendor']),
            'code'            => $findHeader(['unique code', 'unique_code', 'code', 'supplier code', 'supplier_code', 'vendor code', 'vendor_code']),
            'category'        => $findHeader(['category', 'supplier category', 'supplier_category', 'type', 'group']),
            'company_name'    => $findHeader(['company name', 'company_name', 'company', 'organization']),
            'phone'           => $findHeader(['phone', 'phone no', 'phone_no', 'contact', 'mobile', 'telephone']),
            'address'         => $findHeader(['address', 'location', 'street']),
            'opening_balance' => $findHeader(['opening balance', 'opening_balance', 'balance', 'opening debt', 'debt']),
        ];

        if ($map['name'] === false) {
            return response()->json(['message' => 'Required column "Supplier Name" not found in the sheet.'], 422);
        }
        if ($map['code'] === false) {
            return response()->json(['message' => 'Required column "Unique Code" not found in the sheet.'], 422);
        }

        $inserted = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $errors = [];

        $categories = SupplierCategory::all()->pluck('id', 'name')->mapWithKeys(function ($id, $name) {
            return [strtolower(trim($name)) => $id];
        })->toArray();

        $existingCodes = Supplier::whereNotNull('code')->pluck('id', 'code')->mapWithKeys(function ($id, $code) {
            return [strtolower(trim($code)) => $id];
        })->toArray();

        $rowsToInsert = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $rowNumber = $i + 1;

            $name = isset($row[$map['name']]) ? trim($row[$map['name']]) : '';
            $code = isset($row[$map['code']]) ? strtoupper(trim($row[$map['code']])) : '';

            if ($name === '' && $code === '') {
                continue;
            }

            if ($name === '') {
                $errors[] = "Row {$rowNumber}: Supplier Name is required.";
                $failedCount++;
                continue;
            }

            if ($code === '') {
                $errors[] = "Row {$rowNumber}: Unique Code is required.";
                $failedCount++;
                continue;
            }

            $codeKey = strtolower($code);
            if (isset($existingCodes[$codeKey]) || isset($rowsToInsert[$codeKey])) {
                $errors[] = "Row {$rowNumber}: Skipped because Unique Code '{$code}' already exists.";
                $skippedCount++;
                continue;
            }

            $categoryVal = ($map['category'] !== false && isset($row[$map['category']]) && $row[$map['category']] !== '') ? trim($row[$map['category']]) : null;
            $companyName = ($map['company_name'] !== false && isset($row[$map['company_name']])) ? trim($row[$map['company_name']]) : null;
            $phone       = ($map['phone'] !== false && isset($row[$map['phone']])) ? trim($row[$map['phone']]) : null;
            $address     = ($map['address'] !== false && isset($row[$map['address']])) ? trim($row[$map['address']]) : null;
            
            $openingBalVal = ($map['opening_balance'] !== false && isset($row[$map['opening_balance']]) && $row[$map['opening_balance']] !== '') ? $row[$map['opening_balance']] : 0;
            if (!is_numeric($openingBalVal)) {
                $errors[] = "Row {$rowNumber}: Opening Balance '{$openingBalVal}' is not numeric.";
                $failedCount++;
                continue;
            }
            $openingBalance = floatval($openingBalVal);

            $rowsToInsert[$codeKey] = [
                'name'            => $name,
                'code'            => $code,
                'category_name'   => $categoryVal,
                'company_name'    => $companyName,
                'phone'           => $phone,
                'address'         => $address,
                'opening_balance' => $openingBalance,
                'row_number'      => $rowNumber
            ];
        }

        $chunks = array_chunk($rowsToInsert, 200);

        foreach ($chunks as $chunk) {
            \Illuminate\Support\Facades\DB::beginTransaction();
            try {
                foreach ($chunk as $data) {
                    $catId = null;
                    if (!empty($data['category_name'])) {
                        $catKey = strtolower($data['category_name']);
                        if (isset($categories[$catKey])) {
                            $catId = $categories[$catKey];
                        } else {
                            $catObj = SupplierCategory::create(['name' => $data['category_name']]);
                            $categories[$catKey] = $catObj->id;
                            $catId = $catObj->id;
                        }
                    }

                    $supplier = Supplier::create([
                        'name'            => $data['name'],
                        'company_name'    => $data['company_name'],
                        'code'            => $data['code'],
                        'category_id'     => $catId,
                        'phone'           => $data['phone'],
                        'address'         => $data['address'],
                        'current_balance' => $data['opening_balance'],
                        'opening_balance' => $data['opening_balance'],
                    ]);

                    if ($data['opening_balance'] != 0) {
                        SupplierLedger::create([
                            'supplier_id'    => $supplier->id,
                            'date'           => now()->toDateString(),
                            'reference_type' => 'opening',
                            'reference_id'   => $supplier->id,
                            'description'    => 'Opening Balance on Import',
                            'debit'          => 0,
                            'credit'         => $data['opening_balance'],
                            'balance'        => $data['opening_balance'],
                        ]);
                    }

                    $existingCodes[strtolower($data['code'])] = $supplier->id;
                    $inserted++;
                }
                \Illuminate\Support\Facades\DB::commit();
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\DB::rollBack();
                \Illuminate\Support\Facades\Log::error("Supplier import chunk failed: " . $e->getMessage());
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
}
