<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\GeneralLedgerAccount;
use App\Models\Supplier;
use App\Models\SupplierLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ChartOfAccountsImportController extends Controller
{
    // =========================================================================
    // PREFIX → CATEGORY / TARGET MAP  (authoritative business logic)
    // =========================================================================

    /** Maps 2-digit prefix to human-readable category label. */
    private const PREFIX_CATEGORY = [
        '01' => 'Banks',
        '02' => 'Inventory',
        '03' => 'Other Assets',
        '04' => 'Fixed Assets',
        '05' => 'Customers',
        '06' => 'Suppliers',
        '07' => 'Equity',
        '08' => 'Liabilities',
        '09' => 'Sales Income',
        '10' => 'Services',
        '11' => 'Other Income',
        '12' => 'Cost of Sales',
        '13' => 'Expenses',
        '14' => 'Employees',
    ];

    /** Maps 2-digit prefix to destination target. */
    private const PREFIX_TARGET = [
        '01' => 'gl',
        '02' => 'gl',
        '03' => 'gl',
        '04' => 'gl',
        '05' => 'customer',
        '06' => 'supplier',
        '07' => 'gl',
        '08' => 'gl',
        '09' => 'gl',
        '10' => 'gl',
        '11' => 'gl',
        '12' => 'gl',
        '13' => 'gl',
        '14' => 'gl',
    ];

    /** Maps 2-digit prefix to general_ledger_accounts.account_type value. */
    private const PREFIX_ACCOUNT_TYPE = [
        '01' => 'ASSETS',
        '02' => 'ASSETS',
        '03' => 'ASSETS',
        '04' => 'ASSETS',
        '07' => 'EQUITY',
        '08' => 'LIABILITIES',
        '09' => 'INCOME',
        '10' => 'INCOME',
        '11' => 'INCOME',
        '12' => 'EXPENSE',
        '13' => 'EXPENSE',
        '14' => 'EXPENSE',
    ];

    // =========================================================================
    // PUBLIC ACTIONS
    // =========================================================================

    /**
     * GET /accounts/import
     * Render the upload page.
     */
    public function showUpload()
    {
        return view('accounts.import');
    }

    /**
     * POST /accounts/import/preview
     *
     * Parse the uploaded CSV/Excel and return a JSON array of annotated rows.
     * Nothing is written to the database here.
     */
    public function parsePreview(Request $request)
    {
        $request->validate([
            'file'              => 'required|file|mimes:xls,xlsx,csv,txt|max:20480',
            'balance_treatment' => 'nullable|string|in:customer_owes,store_owes',
        ]);

        $balanceTreatment = $request->input('balance_treatment', 'customer_owes');
        // We do NOT use a simple multiplier here.
        // The abs()-based rule below is applied per customer row.

        try {
            $rows    = $this->readFile($request->file('file'));
            $headers = $this->normaliseHeaders($rows[0] ?? []);
            $colMap  = $this->buildColumnMap($headers);

            // ── Pre-load existing records for duplicate detection ────────────
            $existingGl        = GeneralLedgerAccount::pluck('id', 'gl_code')
                ->mapWithKeys(fn ($id, $c) => [strtolower(trim($c)) => $id])
                ->toArray();
            $existingSuppliers = Supplier::whereNotNull('code')
                ->pluck('id', 'code')
                ->mapWithKeys(fn ($id, $c) => [strtolower(trim($c)) => $id])
                ->toArray();

            // Extract customer names & phones from upload to fetch full models
            $customerNames  = [];
            $customerPhones = [];
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $allEmpty = true;
                foreach ($row as $cell) {
                    if (trim((string) $cell) !== '') {
                        $allEmpty = false;
                        break;
                    }
                }
                if ($allEmpty) {
                    continue;
                }
                $name  = trim($this->cell($row, $colMap, 'name'));
                $phone = trim($this->cell($row, $colMap, 'phone'));
                if ($name !== '') {
                    $customerNames[] = $name;
                }
                if ($phone !== '') {
                    $customerPhones[] = $phone;
                }
            }

            $matchedCustomersByName  = [];
            $matchedCustomersByPhone = [];
            if (!empty($customerNames) || !empty($customerPhones)) {
                $matchedQuery = Customer::query();
                if (!empty($customerNames)) {
                    $matchedQuery->whereIn('name', $customerNames);
                    $lowerNames = array_map(fn ($n) => strtolower(trim($n)), $customerNames);
                    $matchedQuery->orWhere(function ($q) use ($lowerNames) {
                        $q->whereIn(DB::raw('LOWER(TRIM(name))'), $lowerNames);
                    });
                }
                if (!empty($customerPhones)) {
                    $matchedQuery->orWhereIn('phone', $customerPhones);
                }
                $allMatched = $matchedQuery->get();
                foreach ($allMatched as $c) {
                    $nameKey = strtolower(trim($c->name));
                    if (!isset($matchedCustomersByName[$nameKey])) {
                        $matchedCustomersByName[$nameKey] = $c;
                    }
                    if (!empty($c->phone)) {
                        $phoneKey = trim($c->phone);
                        $matchedCustomersByPhone[$phoneKey] = $c;
                    }
                }
            }

            $preview = [];

            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                // Skip blank rows
                $allEmpty = true;
                foreach ($row as $cell) {
                    if (trim((string) $cell) !== '') {
                        $allEmpty = false;
                        break;
                    }
                }
                if ($allEmpty) {
                    continue;
                }

                $accountId = $this->cell($row, $colMap, 'accountid');
                $ac        = $this->cell($row, $colMap, 'ac');
                $name      = $this->cell($row, $colMap, 'name');
                $phoneVal  = $this->cell($row, $colMap, 'phone');
                $emailVal  = $this->cell($row, $colMap, 'email');

                // Derive prefix from first 2 characters of accountid, parsing as an integer 1–14
                $prefixChar = $accountId !== '' ? substr($accountId, 0, 2) : '';
                $prefixVal  = is_numeric($prefixChar) ? (int)$prefixChar : 0;
                if ($prefixVal >= 1 && $prefixVal <= 14) {
                    $prefix   = str_pad($prefixVal, 2, '0', STR_PAD_LEFT);
                    $category = self::PREFIX_CATEGORY[$prefix] ?? 'Unmapped';
                    $target   = self::PREFIX_TARGET[$prefix]   ?? 'unmapped';
                } elseif ($accountId === '' && $name !== '') {
                    // If accountid is missing but name is provided in an accounts/customer import file,
                    // route row to Customers category ('05')
                    $prefix   = '05';
                    $category = 'Customers';
                    $target   = 'customer';
                } else {
                    $prefix   = $prefixChar;
                    $category = 'Unmapped';
                    $target   = 'unmapped';
                }

                // Duplicate detection against the correct target table
                $isDuplicate        = false;
                $duplicateLabel     = null;
                $isExisting         = false;
                $customerId         = null;
                $existingBalance    = null;
                $existingPhone      = null;
                $existingEmail      = null;

                // Address concatenation (address1 + address2, space separated if both exist)
                $addr1 = trim($this->cell($row, $colMap, 'address1'));
                $addr2 = trim($this->cell($row, $colMap, 'address2'));
                $addressVal = '';
                if ($addr1 !== '' && $addr2 !== '') {
                    $addressVal = $addr1 . ' ' . $addr2;
                } elseif ($addr1 !== '') {
                    $addressVal = $addr1;
                } else {
                    $addressVal = $addr2;
                }

                // Limit / Credit limit
                $creditLimitVal = $this->numericCell($row, $colMap, 'limit', 0);

                // Balance mapping logic (stbalance first, then balance, fallback to credit - debit)
                $stBalanceVal = $this->numericCell($row, $colMap, 'stbalance', 0);
                $balColVal    = $this->numericCell($row, $colMap, 'balance', 0);
                if ($stBalanceVal != 0) {
                    $rawBalance = $stBalanceVal;
                } elseif ($balColVal != 0) {
                    $rawBalance = $balColVal;
                } else {
                    $cr = $this->numericCell($row, $colMap, 'credit', 0);
                    $dr = $this->numericCell($row, $colMap, 'debit', 0);
                    $rawBalance = $cr - $dr;
                }
                $storeCreditVal = 0.0;

                // Apply abs()-based balance treatment:
                //   customer_owes  → final = +abs(raw)   (positive = red = owes store)
                //   store_owes     → final = -abs(raw)   (negative = green = store owes customer)
                // The sign in the uploaded file is INTENTIONALLY ignored; only the user's
                // selected meaning controls which direction the balance is stored.
                if ($target === 'customer') {
                    $absRaw = abs($rawBalance);
                    $importedBalance = ($balanceTreatment === 'store_owes') ? -$absRaw : $absRaw;
                } else {
                    $importedBalance = $rawBalance;
                }

                switch ($target) {
                    case 'gl':
                        $key = strtolower(trim($accountId));
                        if ($key !== '' && isset($existingGl[$key])) {
                            $isDuplicate    = true;
                            $duplicateLabel = "GL account '{$accountId}' already exists";
                        }
                        break;

                    case 'customer':
                        $nameKey  = strtolower(trim($name));
                        $phoneKey = trim($phoneVal);
                        $custRecord = null;
                        if ($nameKey !== '' && isset($matchedCustomersByName[$nameKey])) {
                            $custRecord = $matchedCustomersByName[$nameKey];
                        } elseif ($phoneKey !== '' && isset($matchedCustomersByPhone[$phoneKey])) {
                            $custRecord = $matchedCustomersByPhone[$phoneKey];
                        }

                        if ($custRecord) {
                            $isExisting      = true;
                            $customerId      = $custRecord->id;
                            $existingBalance = (float) $custRecord->balance;
                            $existingPhone   = $custRecord->phone;
                            $existingEmail   = $custRecord->email;
                            $duplicateLabel  = "Customer '{$custRecord->name}' already exists (Existing Balance: Rs. " . number_format($existingBalance, 2) . ")";
                        }
                        break;

                    case 'supplier':
                        $key = strtolower(strtoupper(trim($accountId)));
                        if ($key !== '' && isset($existingSuppliers[$key])) {
                            $isDuplicate    = true;
                            $duplicateLabel = "Supplier code '{$accountId}' already exists";
                        }
                        break;
                }

                $finalBalanceAdd    = $isExisting ? round($existingBalance + $importedBalance, 2) : (float) $importedBalance;
                $finalBalanceNotAdd = $isExisting ? $existingBalance : 0.0;

                $preview[] = [
                    'row'                   => $i + 1,
                    'accountid'             => $accountId,
                    'ac'                    => $ac,
                    'name'                  => $name,
                    'phone'                 => $phoneVal,
                    'email'                 => $emailVal,
                    'address'               => $addressVal,
                    'credit_limit'          => (float) $creditLimitVal,
                    'balance'               => (float) $importedBalance,
                    'raw_balance'           => (float) $rawBalance,
                    'store_credit'          => (float) $storeCreditVal,
                    'prefix'                => $prefix,
                    'category'              => $category,
                    'target'                => $target,
                    'is_unmapped'           => ($target === 'unmapped'),
                    'is_duplicate'          => $isDuplicate,
                    'duplicate_label'       => $duplicateLabel,
                    'is_existing'           => $isExisting,
                    'customer_id'           => $customerId,
                    'existing_balance'      => $existingBalance,
                    'existing_phone'        => $existingPhone,
                    'existing_email'        => $existingEmail,
                    'final_balance_add'     => $finalBalanceAdd,
                    'final_balance_not_add' => $finalBalanceNotAdd,
                    'duplicate_action'      => $isExisting ? 'add' : null,
                    'import_action'         => $isExisting ? 'update' : ($target === 'unmapped' ? 'skip' : 'create'),
                    'included'              => ($target !== 'unmapped'),
                ];
            }

            return response()->json([
                'rows'              => $preview,
                'prefix_map'        => self::PREFIX_CATEGORY,
                'balance_treatment' => $balanceTreatment,
            ]);
        } catch (\Exception $e) {
            Log::error('ChartOfAccountsImport parsePreview error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to parse file: ' . $e->getMessage()], 422);
        }
    }

    /**
     * POST /accounts/import/commit
     *
     * Receives the user-confirmed row array and writes records to the
     * appropriate tables. Returns a JSON summary.
     */
    public function commit(Request $request)
    {
        $request->validate([
            'rows'                    => 'required|array|min:1',
            'rows.*.row'              => 'required|integer',
            'rows.*.accountid'        => 'nullable|string',
            'rows.*.ac'               => 'nullable|string',
            'rows.*.name'             => 'nullable|string',
            'rows.*.phone'            => 'nullable|string',
            'rows.*.email'            => 'nullable|string',
            'rows.*.address'          => 'nullable|string',
            'rows.*.credit_limit'     => 'nullable|numeric',
            'rows.*.balance'          => 'nullable|numeric',
            'rows.*.raw_balance'      => 'nullable|numeric',
            'rows.*.store_credit'     => 'nullable|numeric',
            'rows.*.category'         => 'required|string',
            'rows.*.target'           => 'required|string',
            'rows.*.included'         => 'required|boolean',
            'rows.*.is_existing'      => 'required|boolean',
            'rows.*.customer_id'      => 'nullable|integer',
            'rows.*.duplicate_action' => 'nullable|string|in:add,not_add',
            'rows.*.import_action'    => 'required|string|in:create,update,skip',
            'balance_treatment'       => 'nullable|string|in:customer_owes,store_owes',
        ]);

        $rows = $request->input('rows');

        $summary = [
            'gl'       => ['inserted' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []],
            'customer' => [
                'inserted'           => 0,
                'new_customers'      => 0,
                'existing_customers' => 0,
                'added_to_existing'  => 0,
                'not_added'          => 0,
                'skipped'            => 0,
                'failed'             => 0,
                'errors'             => [],
            ],
            'supplier' => ['inserted' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []],
            'total_imported' => 0,
            'skipped'  => 0,
        ];

        // Pre-load existing records (same pattern as UnifiedImportController::commit)
        $existingGl = GeneralLedgerAccount::pluck('id', 'gl_code')
            ->mapWithKeys(fn ($id, $c) => [strtolower(trim($c)) => $id])
            ->toArray();
        $existingCustomers = Customer::pluck('id', 'name')
            ->mapWithKeys(fn ($id, $n) => [strtolower(trim($n)) => $id])
            ->toArray();
        $existingSuppliers = Supplier::whereNotNull('code')
            ->pluck('id', 'code')
            ->mapWithKeys(fn ($id, $c) => [strtolower(trim($c)) => $id])
            ->toArray();

        foreach ($rows as $rowData) {
            $rowNumber    = $rowData['row'];
            $target       = $rowData['target'] ?? 'unmapped';
            $included     = (bool) $rowData['included'];
            $category     = $rowData['category'] ?? 'Unmapped';
            $importAction = $rowData['import_action'] ?? 'skip';

            // Safety net: skip unchecked or unmapped rows
            if (! $included || $target === 'unmapped' || $category === 'Unmapped' || $importAction === 'skip') {
                $summary['skipped']++;
                if ($target === 'customer') {
                    $summary['customer']['skipped']++;
                }
                continue;
            }

            try {
                switch ($target) {
                    case 'gl':
                        $this->importGlRow($rowNumber, $rowData, $existingGl, $summary);
                        break;

                    case 'customer':
                        if ($rowData['is_existing'] || $importAction === 'update') {
                            $this->updateCustomerRow($rowNumber, $rowData, $summary);
                        } else {
                            $this->importCustomerRow($rowNumber, $rowData, $existingCustomers, $summary);
                        }
                        break;

                    case 'supplier':
                        $this->importSupplierRow($rowNumber, $rowData, $existingSuppliers, $summary);
                        break;

                    default:
                        $summary['skipped']++;
                        break;
                }
            } catch (\Exception $e) {
                Log::error("ChartOfAccountsImport commit row {$rowNumber} error: " . $e->getMessage());
                $bucket = in_array($target, ['gl', 'customer', 'supplier']) ? $target : 'gl';
                $summary[$bucket]['failed']++;
                $summary[$bucket]['errors'][] = "Row {$rowNumber}: Unexpected error — " . $e->getMessage();
            }
        }

        $summary['total_imported'] = $summary['gl']['inserted'] + $summary['customer']['inserted'] + $summary['supplier']['inserted'];

        return response()->json(['summary' => $summary]);
    }

    // =========================================================================
    // PRIVATE: TABLE-SPECIFIC IMPORT METHODS
    // =========================================================================

    /**
     * Import one row into general_ledger_accounts.
     */
    private function importGlRow(
        int $rowNumber,
        array $rowData,
        array &$existing,
        array &$summary
    ): void {
        $accountId = trim($rowData['accountid'] ?? '');
        $name      = trim($rowData['name'] ?? '');

        if ($accountId === '') {
            $summary['gl']['errors'][] = "Row {$rowNumber}: Account ID is empty — skipped.";
            $summary['gl']['skipped']++;
            return;
        }
        if ($name === '') {
            $summary['gl']['errors'][] = "Row {$rowNumber}: Account name is empty — skipped.";
            $summary['gl']['skipped']++;
            return;
        }

        $key = strtolower($accountId);
        if (isset($existing[$key])) {
            $summary['gl']['errors'][] = "Row {$rowNumber}: GL account '{$accountId}' already exists — skipped.";
            $summary['gl']['skipped']++;
            return;
        }

        // Derive prefix from accountId for account_type
        $prefixChar  = substr($accountId, 0, 2);
        $prefixVal   = is_numeric($prefixChar) ? (int)$prefixChar : 0;
        $prefix      = str_pad($prefixVal, 2, '0', STR_PAD_LEFT);
        $accountType = self::PREFIX_ACCOUNT_TYPE[$prefix] ?? 'ASSETS';
        $glType      = $prefix; // Store bare 2-digit prefix, matching GeneralLedgerController queries

        $balance     = (float) ($rowData['balance'] ?? 0);

        DB::beginTransaction();
        try {
            GeneralLedgerAccount::create([
                'gl_code'         => $accountId,
                'gl_type'         => $glType,
                'name'            => $name,
                'account_type'    => $accountType,
                'opening_balance' => $balance,
                'current_balance' => $balance,
            ]);
            $existing[$key] = true;
            DB::commit();
            $summary['gl']['inserted']++;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Import one new customer row into the customers table.
     */
    private function importCustomerRow(
        int $rowNumber,
        array $rowData,
        array &$existing,
        array &$summary
    ): void {
        $name = trim($rowData['name'] ?? '');
        if ($name === '') {
            $summary['customer']['errors'][] = "Row {$rowNumber}: Customer name is empty — skipped.";
            $summary['customer']['skipped']++;
            return;
        }

        $key = strtolower($name);
        // If customer unexpectedly exists in DB, route to safe update logic
        if (isset($existing[$key]) || Customer::where(DB::raw('LOWER(TRIM(name))'), $key)->exists()) {
            $existingCustomer = Customer::where(DB::raw('LOWER(TRIM(name))'), $key)->first();
            if ($existingCustomer) {
                $rowData['customer_id'] = $existingCustomer->id;
                $this->updateCustomerRow($rowNumber, $rowData, $summary);
                return;
            }
        }

        $balance = (float) ($rowData['balance'] ?? 0);

        DB::beginTransaction();
        try {
            $customer = Customer::create([
                'name'         => $name,
                'phone'        => !empty($rowData['phone']) ? $rowData['phone'] : null,
                'email'        => !empty($rowData['email']) ? $rowData['email'] : null,
                'address'      => !empty($rowData['address']) ? $rowData['address'] : null,
                'credit_limit' => (float) ($rowData['credit_limit'] ?? 0),
                'balance'      => $balance,
                'store_credit' => (float) ($rowData['store_credit'] ?? 0),
                'status'       => 'active',
            ]);

            // If non-zero opening balance, create a ledger entry for audit trail
            if ($balance != 0) {
                CustomerLedgerEntry::create([
                    'customer_id'   => $customer->id,
                    'type'          => 'manual_adjustment',
                    'amount'        => $balance,
                    'balance_after' => $balance,
                    'note'          => "Opening balance imported from Account Import",
                    'created_by'    => auth()->id(),
                ]);
            }

            $existing[$key] = $customer->id;
            DB::commit();
            $summary['customer']['new_customers']++;
            $summary['customer']['inserted']++;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle an existing customer row: ADD imported balance to existing balance,
     * or NOT ADD (keep existing customer balance completely unchanged).
     */
    private function updateCustomerRow(
        int $rowNumber,
        array $rowData,
        array &$summary
    ): void {
        $customerId = $rowData['customer_id'] ?? null;
        $name       = trim($rowData['name'] ?? '');

        if ($name === '') {
            $summary['customer']['errors'][] = "Row {$rowNumber}: Customer name is empty — skipped.";
            $summary['customer']['skipped']++;
            return;
        }

        DB::beginTransaction();
        try {
            $customer = null;
            if ($customerId) {
                $customer = Customer::find($customerId);
            }
            if (!$customer) {
                $customer = Customer::where(DB::raw('LOWER(TRIM(name))'), strtolower($name))->first();
            }

            if (!$customer) {
                $summary['customer']['errors'][] = "Row {$rowNumber}: Existing customer '{$name}' could not be found — skipped.";
                $summary['customer']['skipped']++;
                DB::commit();
                return;
            }

            $duplicateAction = $rowData['duplicate_action'] ?? 'add';
            $importedBalance = (float) ($rowData['balance'] ?? 0);
            $oldBalance      = (float) $customer->balance;

            $summary['customer']['existing_customers']++;

            if ($duplicateAction === 'add') {
                // ADD: Add imported balance to existing balance
                $newBalance = round($oldBalance + $importedBalance, 2);
                $customer->balance = $newBalance;

                // Backfill customer contact details if currently empty
                if (!empty($rowData['phone']) && empty($customer->phone)) {
                    $customer->phone = $rowData['phone'];
                }
                if (!empty($rowData['address']) && empty($customer->address)) {
                    $customer->address = $rowData['address'];
                }
                if (!empty($rowData['email']) && empty($customer->email)) {
                    $customer->email = $rowData['email'];
                }
                if (isset($rowData['credit_limit']) && (float)$rowData['credit_limit'] > 0 && (float)$customer->credit_limit == 0) {
                    $customer->credit_limit = (float) $rowData['credit_limit'];
                }
                $customer->save();

                if ($importedBalance != 0) {
                    CustomerLedgerEntry::create([
                        'customer_id'   => $customer->id,
                        'type'          => 'manual_adjustment',
                        'amount'        => $importedBalance,
                        'balance_after' => $newBalance,
                        'note'          => "Imported balance added from Account Import (Previous: {$oldBalance}, Added: {$importedBalance})",
                        'created_by'    => auth()->id(),
                    ]);
                }

                $summary['customer']['added_to_existing']++;
                $summary['customer']['inserted']++;
            } else {
                // NOT ADD: Do not add imported balance. Keep existing customer balance unchanged.
                // $customer->balance is NEVER modified.
                if (!empty($rowData['phone']) && empty($customer->phone)) {
                    $customer->phone = $rowData['phone'];
                }
                if (!empty($rowData['address']) && empty($customer->address)) {
                    $customer->address = $rowData['address'];
                }
                if (!empty($rowData['email']) && empty($customer->email)) {
                    $customer->email = $rowData['email'];
                }
                $customer->save();

                $summary['customer']['not_added']++;
                $summary['customer']['inserted']++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Import one row into the suppliers table.
     */
    private function importSupplierRow(
        int $rowNumber,
        array $rowData,
        array &$existing,
        array &$summary
    ): void {
        $accountId = trim($rowData['accountid'] ?? '');
        $name      = trim($rowData['name'] ?? '');

        if ($name === '') {
            $summary['supplier']['errors'][] = "Row {$rowNumber}: Supplier name is empty — skipped.";
            $summary['supplier']['skipped']++;
            return;
        }

        // accountid (e.g. "060010") becomes the unique supplier code
        $code    = $accountId !== '' ? strtoupper($accountId) : ('IMP-' . now()->format('YmdHis') . '-' . $rowNumber);
        $codeKey = strtolower($code);

        if (isset($existing[$codeKey])) {
            $summary['supplier']['errors'][] = "Row {$rowNumber}: Supplier code '{$code}' already exists — skipped.";
            $summary['supplier']['skipped']++;
            return;
        }

        DB::beginTransaction();
        try {
            $balance  = (float) ($rowData['balance'] ?? 0);
            $phone    = trim($rowData['phone']   ?? '') ?: null;
            $address  = trim($rowData['address'] ?? '') ?: null;

            $supplier = Supplier::create([
                'name'            => $name,
                'code'            => $code,
                'account_code'    => $accountId,  // GL cross-reference
                'company_name'    => null,
                'phone'           => $phone,
                'address'         => $address,
                'opening_balance' => $balance,
                'current_balance' => $balance,
                'category_id'     => null,
            ]);

            // If there is an opening balance, record it as a supplier_ledger_entries row
            // so the Supplier Profile ledger is accurate from day one.
            if ($balance != 0) {
                \App\Models\SupplierLedgerEntry::create([
                    'supplier_id'   => $supplier->id,
                    'type'          => 'manual_adjustment',
                    'amount'        => $balance,
                    'balance_after' => $balance,
                    'note'          => "Opening balance imported from Chart of Accounts (code: {$code})",
                    'created_by'    => auth()->id(),
                ]);
            }

            $existing[$codeKey] = $supplier->id;
            DB::commit();
            $summary['supplier']['inserted']++;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // =========================================================================
    // PRIVATE: FILE PARSING HELPERS  (mirrors UnifiedImportController)
    // =========================================================================

    /**
     * Read the uploaded file into a 2D array via PhpSpreadsheet.
     * Identical to UnifiedImportController::readFile().
     */
    private function readFile($uploadedFile): array
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $reader      = IOFactory::createReaderForFile($uploadedFile->getRealPath());
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($uploadedFile->getRealPath());
        $worksheet   = $spreadsheet->getActiveSheet();
        $highestRow  = $worksheet->getHighestDataRow();
        $highestCol  = $worksheet->getHighestDataColumn();

        if ($highestRow < 2) {
            throw new \RuntimeException('The uploaded file does not contain any data rows.');
        }

        return $worksheet->rangeToArray('A1:' . $highestCol . $highestRow, null, true, false, false);
    }

    /**
     * Normalise header row: lowercase + trim.
     */
    private function normaliseHeaders(array $headerRow): array
    {
        return array_map(fn ($h) => strtolower(trim((string) $h)), $headerRow);
    }

    /**
     * Build a semantic column-index map for the Chart of Accounts CSV format.
     *
     * IMPORTANT: every key listed here must exactly match what parsePreview passes
     * to cell() and numericCell() — there is no fallback for missing keys.
     */
    private function buildColumnMap(array $headers): array
    {
        $find = function (array $options) use ($headers): int {
            foreach ($options as $opt) {
                $idx = array_search(strtolower(trim($opt)), $headers);
                if ($idx !== false) {
                    return (int) $idx;
                }
            }
            return -1;
        };

        return [
            // ── Core identifier columns ──────────────────────────────────────
            'accountid'    => $find(['accountid', 'account_id', 'account id', 'acno', 'gl_code', 'gl code']),
            'ac'           => $find(['ac', 'ac_no', 'sub_code', 'sub code', 'subcode']),
            'name'         => $find(['name', 'account name', 'account_name', 'acname', 'title', 'description', 'customer name', 'customer_name', 'client name', 'client']),

            // ── Contact / address columns (prefix05 = Customer) ──────────────
            'phone'        => $find(['phone', 'phone no', 'phone_no', 'contact', 'mobile', 'fax', 'telephone']),
            'email'        => $find(['email', 'e-mail', 'mail', 'email address', 'email_address']),
            'address1'     => $find(['address1', 'address 1', 'addr1', 'street1', 'address', 'location', 'street']),
            'address2'     => $find(['address2', 'address 2', 'addr2', 'street2']),

            // ── Financial columns (used by Customer rows) ─────────────────────
            // 'limit' maps the raw CSV column header "limit"
            'limit'        => $find(['limit', 'credit limit', 'credit_limit']),
            // 'stbalance' maps the raw CSV column header "stbalance"
            'stbalance'    => $find(['stbalance', 'st_balance', 'starting balance', 'start_balance']),
            // 'credit' and 'debit' for fallback balance calculation
            'credit'       => $find(['credit', 'cr', 'credit amount']),
            'debit'        => $find(['debit', 'dr', 'debit amount']),
            // legacy aliases kept for GL rows that use these keys
            'balance'      => $find(['balance', 'opening balance', 'opening_balance', 'opening debt', 'debt']),
            'credit_limit' => $find(['credit limit', 'credit_limit', 'limit']),
        ];
    }

    /**
     * Get a string cell value by field name; returns '' if absent.
     */
    private function cell(array $row, array $colMap, string $field): string
    {
        $idx = $colMap[$field] ?? -1;
        if ($idx < 0 || ! isset($row[$idx])) {
            return '';
        }
        return trim((string) $row[$idx]);
    }

    /**
     * Get a numeric cell value; returns $default if absent or non-numeric.
     */
    private function numericCell(array $row, array $colMap, string $field, float $default = 0.0): float
    {
        $val = $this->cell($row, $colMap, $field);
        if ($val === '' || !is_numeric($val)) return $default;
        return (float) $val;
    }
}
