<?php

namespace App\Http\Controllers;

use App\Models\Customer;
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
    ];

    /** Maps 2-digit prefix to general_ledger_accounts.account_type value. */
    private const PREFIX_ACCOUNT_TYPE = [
        '01' => 'ASSETS',
        '02' => 'ASSETS',
        '03' => 'ASSETS',
        '04' => 'ASSETS',
        '07' => 'EQUITY',
        '08' => 'LIABILITIES',
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
     * Parse the uploaded CSV and return a JSON array of annotated rows.
     * Nothing is written to the database here.
     */
    public function parsePreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv,txt|max:20480',
        ]);

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

            // Extract customer names from upload to fetch full models at once
            $customerNames = [];
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
                $name = trim($this->cell($row, $colMap, 'name'));
                if ($name !== '') {
                    $customerNames[] = $name;
                }
            }

            $matchedCustomers = [];
            if (!empty($customerNames)) {
                $matchedCustomers = Customer::whereIn('name', $customerNames)
                    ->get()
                    ->keyBy(fn ($c) => strtolower(trim($c->name)))
                    ->toArray();
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

                // Derive prefix from first 2 characters of accountid
                $prefix   = $accountId !== '' ? substr($accountId, 0, 2) : '';
                $category = self::PREFIX_CATEGORY[$prefix] ?? 'Unmapped';
                $target   = self::PREFIX_TARGET[$prefix]   ?? 'unmapped';

                // Duplicate detection against the correct target table
                $isDuplicate    = false;
                           // Customer properties
                $isExisting     = false;
                $customerId     = null;
                $duplicateLabel = null;
                $phoneVal       = $this->cell($row, $colMap, 'phone');

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

                // Balance mapping logic (stbalance first, fallback to credit - debit)
                $stBalanceVal = $this->numericCell($row, $colMap, 'stbalance', 0);
                if ($stBalanceVal != 0) {
                    $balanceVal = $stBalanceVal;
                } else {
                    $cr = $this->numericCell($row, $colMap, 'credit', 0);
                    $dr = $this->numericCell($row, $colMap, 'debit', 0);
                    $balanceVal = $cr - $dr;
                }
                $storeCreditVal = 0.0;

                switch ($target) {
                    case 'gl':
                        $key = strtolower(trim($accountId));
                        if ($key !== '' && isset($existingGl[$key])) {
                            $isDuplicate    = true;
                            $duplicateLabel = "GL account '{$accountId}' already exists";
                        }
                        break;

                    case 'customer':
                        $key = strtolower(trim($name));
                        if ($key !== '' && isset($matchedCustomers[$key])) {
                            $isExisting   = true;
                            $custRecord   = $matchedCustomers[$key];
                            $customerId   = $custRecord['id'];
                            // Proposed updates are pre-populated with values FROM THE FILE, not old DB values
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

                $preview[] = [
                    'row'             => $i + 1,
                    'accountid'       => $accountId,
                    'ac'              => $ac,
                    'name'            => $name,
                    'phone'           => $phoneVal,
                    'address'         => $addressVal,
                    'credit_limit'    => (float) $creditLimitVal,
                    'balance'         => (float) $balanceVal,
                    'store_credit'    => (float) $storeCreditVal,
                    'prefix'          => $prefix,
                    'category'        => $category,
                    'target'          => $target,
                    'is_unmapped'     => ($target === 'unmapped'),
                    'is_duplicate'    => $isDuplicate,
                    'duplicate_label' => $duplicateLabel,
                    'is_existing'     => $isExisting,
                    'customer_id'     => $customerId,
                    'import_action'   => $isExisting ? 'update' : ($target === 'unmapped' ? 'skip' : 'create'),
                    'included'        => ($target !== 'unmapped'),
                ];
            }

            return response()->json([
                'rows'         => $preview,
                'prefix_map'   => self::PREFIX_CATEGORY,
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
            'rows'                 => 'required|array|min:1',
            'rows.*.row'           => 'required|integer',
            'rows.*.accountid'     => 'nullable|string',
            'rows.*.ac'            => 'nullable|string',
            'rows.*.name'          => 'nullable|string',
            'rows.*.phone'         => 'nullable|string',
            'rows.*.address'       => 'nullable|string',
            'rows.*.credit_limit'  => 'nullable|numeric',
            'rows.*.balance'       => 'nullable|numeric',
            'rows.*.store_credit'  => 'nullable|numeric',
            'rows.*.category'      => 'required|string',
            'rows.*.target'        => 'required|string',
            'rows.*.included'      => 'required|boolean',
            'rows.*.is_existing'   => 'required|boolean',
            'rows.*.customer_id'   => 'nullable|integer',
            'rows.*.import_action' => 'required|string|in:create,update,skip',
        ]);

        $rows = $request->input('rows');

        $summary = [
            'gl'       => ['inserted' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []],
            'customer' => ['inserted' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []],
            'supplier' => ['inserted' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []],
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
                continue;
            }

            try {
                switch ($target) {
                    case 'gl':
                        $this->importGlRow($rowNumber, $rowData, $existingGl, $summary);
                        break;

                    case 'customer':
                        if ($importAction === 'update') {
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
                // Determine which bucket to record the failure in
                $bucket = in_array($target, ['gl', 'customer', 'supplier']) ? $target : 'gl';
                $summary[$bucket]['failed']++;
                $summary[$bucket]['errors'][] = "Row {$rowNumber}: Unexpected error — " . $e->getMessage();
            }
        }

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
        $prefix      = substr($accountId, 0, 2);
        $accountType = self::PREFIX_ACCOUNT_TYPE[$prefix] ?? 'ASSETS';
        $glType      = $prefix; // Store bare 2-digit prefix, matching GeneralLedgerController queries

        DB::beginTransaction();
        try {
            GeneralLedgerAccount::create([
                'gl_code'         => $accountId,
                'gl_type'         => $glType,
                'name'            => $name,
                'account_type'    => $accountType,
                'opening_balance' => 0,
                'current_balance' => 0,
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
     * Import one row into the customers table.
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
        if (isset($existing[$key])) {
            $summary['customer']['errors'][] = "Row {$rowNumber}: Customer '{$name}' already exists — skipped.";
            $summary['customer']['skipped']++;
            return;
        }

        DB::beginTransaction();
        try {
            $customer = Customer::create([
                'name'         => $name,
                'phone'        => $rowData['phone'] ?: null,
                'address'      => $rowData['address'] ?: null,
                'credit_limit' => (float) ($rowData['credit_limit'] ?? 0),
                'balance'      => (float) ($rowData['balance'] ?? 0),
                'store_credit' => (float) ($rowData['store_credit'] ?? 0),
            ]);
            $existing[$key] = $customer->id;
            DB::commit();
            $summary['customer']['inserted']++;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing customer row in the customers table.
     */
    private function updateCustomerRow(
        int $rowNumber,
        array $rowData,
        array &$summary
    ): void {
        $customerId = $rowData['customer_id'] ?? null;
        if (!$customerId) {
            $summary['customer']['errors'][] = "Row {$rowNumber}: Missing existing Customer ID — skipped.";
            $summary['customer']['skipped']++;
            return;
        }

        $name = trim($rowData['name'] ?? '');
        if ($name === '') {
            $summary['customer']['errors'][] = "Row {$rowNumber}: Customer name is empty — skipped.";
            $summary['customer']['skipped']++;
            return;
        }

        DB::beginTransaction();
        try {
            $customer = Customer::find($customerId);
            if (!$customer) {
                $summary['customer']['errors'][] = "Row {$rowNumber}: Existing Customer ID {$customerId} not found — skipped.";
                $summary['customer']['skipped']++;
                DB::commit();
                return;
            }

            $customer->update([
                'name'         => $name,
                'phone'        => $rowData['phone'] ?: null,
                'address'      => $rowData['address'] ?: null,
                'credit_limit' => (float) ($rowData['credit_limit'] ?? 0),
                'balance'      => (float) ($rowData['balance'] ?? 0),
                'store_credit' => (float) ($rowData['store_credit'] ?? 0),
            ]);
            DB::commit();
            $summary['customer']['inserted']++;
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
            $supplier = Supplier::create([
                'name'            => $name,
                'code'            => $code,
                'account_code'    => $accountId,  // GL cross-reference
                'company_name'    => null,
                'phone'           => null,
                'address'         => null,
                'opening_balance' => 0,
                'current_balance' => 0,
                'category_id'     => null,
            ]);
            // opening_balance = 0 → no SupplierLedger entry (mirrors importSupplier logic)
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
            'name'         => $find(['name', 'account name', 'account_name', 'acname', 'title', 'description']),

            // ── Contact / address columns (prefix05 = Customer) ──────────────
            'phone'        => $find(['phone', 'phone no', 'phone_no', 'contact', 'mobile', 'fax']),
            'address1'     => $find(['address1', 'address 1', 'addr1', 'street1', 'address']),
            'address2'     => $find(['address2', 'address 2', 'addr2', 'street2', 'location']),

            // ── Financial columns (used by Customer rows) ─────────────────────
            // 'limit' maps the raw CSV column header "limit"
            'limit'        => $find(['limit', 'credit limit', 'credit_limit']),
            // 'stbalance' maps the raw CSV column header "stbalance"
            'stbalance'    => $find(['stbalance', 'st_balance', 'starting balance', 'start_balance']),
            // 'credit' and 'debit' for fallback balance calculation
            'credit'       => $find(['credit', 'cr', 'credit amount']),
            'debit'        => $find(['debit', 'dr', 'debit amount']),
            // legacy aliases kept for GL rows that use these keys
            'balance'      => $find(['balance', 'opening balance', 'opening_balance']),
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
