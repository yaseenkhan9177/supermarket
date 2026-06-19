<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\DebitSaleItem;
use App\Models\CashSaleItem;
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
        $search    = request('search');
        $customers = Customer::when($search, function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        })->latest()->paginate(15)->withQueryString();

        $totalCustomers    = Customer::count();
        $totalReceivable   = Customer::where('balance', '>', 0)->sum('balance');
        $totalCreditLimit  = Customer::sum('credit_limit');

        return view('store.customers.index', compact(
            'customers',
            'search',
            'totalCustomers',
            'totalReceivable',
            'totalCreditLimit'
        ));
    }

    /**
     * Customer profile page — KPIs + recent bills.
     */
    public function show($id)
    {
        $customer = Customer::findOrFail($id);

        // ── DebitSales (credit invoices) ─────────────────────────
        $debitSales = $customer->debitSales()->withCount('items')->orderBy('invoice_date', 'desc')->get();

        $totalDebitCount  = $debitSales->count();
        $totalDebitAmount = $debitSales->sum('net_total');

        // ── CashSales (POS / cash invoices) ──────────────────────
        $cashSales = $customer->cashSales()->withCount('items')->orderBy('sale_date', 'desc')->get();

        $totalCashCount  = $cashSales->count();
        $totalCashAmount = $cashSales->sum('grand_total');

        // ── Grand total items sold (qty) ──────────────────────────
        // From debit sales
        $totalItemsFromDebit = DebitSaleItem::whereHas('sale', function ($q) use ($id) {
            $q->where('customer_id', $id);
        })->sum('quantity');

        // From cash sales
        $totalItemsFromCash = CashSaleItem::whereHas('cashSale', function ($q) use ($id) {
            $q->where('customer_id', $id);
        })->sum('qty');

        $totalItemsSold = $totalItemsFromDebit + $totalItemsFromCash;

        // ── Outstanding / Due ─────────────────────────────────────
        // customer->balance stores the current outstanding receivable
        $outstandingAmount = $customer->balance > 0 ? $customer->balance : 0.0;

        // ── Grand totals ──────────────────────────────────────────
        $grandTotalSales = $totalDebitAmount + $totalCashAmount;

        // ── Recent bills table (combine debit + cash, paginate in PHP) ─
        // Collect debit sales
        $debitRows = $debitSales->map(function ($s) {
            return (object)[
                'bill_no'      => $s->invoice_no,
                'date'         => $s->invoice_date,
                'items_count'  => $s->items_count,
                'amount'       => $s->net_total,
                'type'         => 'Credit',
                'status'       => $s->status ?? 'issued',
                'print_route'  => route('debit-sales.show', $s->id),
            ];
        });

        // Collect cash sales
        $cashRows = $cashSales->map(function ($s) {
            return (object)[
                'bill_no'      => $s->invoice_no,
                'date'         => $s->sale_date,
                'items_count'  => $s->items_count,
                'amount'       => $s->grand_total,
                'type'         => 'Cash',
                'status'       => 'completed',
                'print_route'  => null,
            ];
        });

        $allBills = $debitRows->merge($cashRows)->sortByDesc('date')->values();

        // Manual pagination
        $page        = request('page', 1);
        $perPage     = 10;
        $offset      = ($page - 1) * $perPage;
        $paginated   = $allBills->slice($offset, $perPage)->values();
        $bills       = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginated,
            $allBills->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('store.customers.show', compact(
            'customer',
            'totalItemsSold',
            'totalCashCount',
            'totalCashAmount',
            'totalDebitCount',
            'totalDebitAmount',
            'outstandingAmount',
            'grandTotalSales',
            'bills'
        ));
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
}
