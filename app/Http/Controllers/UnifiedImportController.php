<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Employee;
use App\Models\GeneralLedgerAccount;
use App\Models\Supplier;
use App\Models\SupplierCategory;
use App\Models\SupplierLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UnifiedImportController extends Controller
{
    // =========================================================================
    // PUBLIC ROUTES
    // =========================================================================

    /**
     * GET /import  - show the upload form.
     */
    public function showUpload()
    {
        return view('import.unified');
    }

    /**
     * POST /import/preview
     *
     * Parses the uploaded file and returns a JSON array of rows, each annotated
     * with a suggested record type and a duplicate-detection flag.
     * NO database writes happen here.
     */
    public function parsePreview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx,csv,txt|max:20480',
        ]);

        try {
            $rows    = $this->readFile($request->file('file'));
            $headers = $this->normaliseHeaders($rows[0]);
            $colMap  = $this->buildColumnMap($headers);
            $suggestedType = $this->suggestFileType($headers, $colMap);

            // Pre-load existing records for duplicate detection
            $existingCustomers  = Customer::pluck('id', 'name')
                ->mapWithKeys(fn($id, $n) => [strtolower(trim($n)) => $id])->toArray();
            $existingSuppliers  = Supplier::whereNotNull('code')
                ->pluck('id', 'code')
                ->mapWithKeys(fn($id, $c) => [strtolower(trim($c)) => $id])->toArray();
            $existingEmployees  = Employee::whereNotNull('phone')
                ->pluck('id', 'phone')
                ->mapWithKeys(fn($id, $p) => [trim($p) => $id])->toArray();
            $existingGlAccounts = GeneralLedgerAccount::pluck('id', 'gl_code')
                ->mapWithKeys(fn($id, $c) => [strtolower(trim($c)) => $id])->toArray();

            $preview = [];

            for ($i = 1; $i < count($rows); $i++) {
                $row       = $rows[$i];
                $rowNumber = $i + 1;

                // Skip completely empty rows
                $allEmpty = true;
                foreach ($row as $cell) {
                    if (trim((string) $cell) !== '') { $allEmpty = false; break; }
                }
                if ($allEmpty) continue;

                // Extract readable fields for preview display
                $name  = $this->cell($row, $colMap, 'name');
                $phone = $this->cell($row, $colMap, 'phone');
                $code  = $this->cell($row, $colMap, 'code');

                // Per-row type detection
                $type = $this->detectRowType($row, $colMap, $suggestedType);

                // Duplicate detection for the suggested type
                $isDuplicate    = false;
                $duplicateLabel = null;

                switch ($type) {
                    case 'customer':
                        $key = strtolower(trim($name));
                        if ($key !== '' && isset($existingCustomers[$key])) {
                            $isDuplicate    = true;
                            $duplicateLabel = "Customer '{$name}' already exists";
                        }
                        break;

                    case 'supplier':
                        $codeKey = strtolower(trim($code ?: $name));
                        if ($codeKey !== '' && isset($existingSuppliers[$codeKey])) {
                            $isDuplicate    = true;
                            $duplicateLabel = "Supplier code '{$code}' already exists";
                        }
                        break;

                    case 'staff':
                        $phoneKey = trim($phone);
                        if ($phoneKey !== '' && isset($existingEmployees[$phoneKey])) {
                            $isDuplicate    = true;
                            $duplicateLabel = "Employee with phone '{$phone}' already exists";
                        }
                        break;

                    case 'gl_account':
                        $glCode = strtolower(trim($this->cell($row, $colMap, 'gl_code') ?: $code));
                        if ($glCode !== '' && isset($existingGlAccounts[$glCode])) {
                            $isDuplicate    = true;
                            $duplicateLabel = "GL account '{$glCode}' already exists";
                        }
                        break;
                }

                // Build extra-fields summary for preview display
                $extras = $this->buildExtraFields($row, $colMap, $headers);

                $preview[] = [
                    'row'             => $rowNumber,
                    'name'            => $name,
                    'phone'           => $phone,
                    'code'            => $code,
                    'extras'          => $extras,
                    'suggested_type'  => $type,
                    'is_duplicate'    => $isDuplicate,
                    'duplicate_label' => $duplicateLabel,
                    // Raw row data passed back so commit can use it without re-parsing
                    '_raw'            => $row,
                ];
            }

            return response()->json([
                'rows'    => $preview,
                'col_map' => $colMap,
                'headers' => $headers,
            ]);
        } catch (\Exception $e) {
            Log::error('UnifiedImport parsePreview error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to parse file: ' . $e->getMessage()], 422);
        }
    }

    /**
     * POST /import/commit
     *
     * Receives the user-confirmed row array (with final `type` per row)
     * and writes records to the appropriate tables.
     */
    public function commit(Request $request)
    {
        $request->validate([
            'rows'          => 'required|array|min:1',
            'rows.*.row'    => 'required|integer',
            'rows.*.type'   => 'required|in:customer,supplier,staff,gl_account,skip',
            'rows.*.name'   => 'nullable|string',
            'rows.*.phone'  => 'nullable|string',
            'rows.*.code'   => 'nullable|string',
            'rows.*._raw'   => 'nullable|array',
            'col_map'       => 'required|array',
        ]);

        $colMap = $request->input('col_map');
        $rows   = $request->input('rows');

        $summary = [
            'customer'   => ['inserted' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []],
            'supplier'   => ['inserted' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []],
            'staff'      => ['inserted' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []],
            'gl_account' => ['inserted' => 0, 'skipped' => 0, 'failed' => 0, 'errors' => []],
            'skipped'    => 0,
        ];

        // Pre-load existing records for duplicate detection during commit
        $existingCustomers  = Customer::pluck('id', 'name')
            ->mapWithKeys(fn($id, $n) => [strtolower(trim($n)) => $id])->toArray();
        $existingSuppliers  = Supplier::whereNotNull('code')
            ->pluck('id', 'code')
            ->mapWithKeys(fn($id, $c) => [strtolower(trim($c)) => $id])->toArray();
        $existingEmployees  = Employee::whereNotNull('phone')
            ->pluck('id', 'phone')
            ->mapWithKeys(fn($id, $p) => [trim($p) => $id])->toArray();
        $existingGlAccounts = GeneralLedgerAccount::pluck('id', 'gl_code')
            ->mapWithKeys(fn($id, $c) => [strtolower(trim($c)) => $id])->toArray();

        $categories = SupplierCategory::all()->pluck('id', 'name')
            ->mapWithKeys(fn($id, $n) => [strtolower(trim($n)) => $id])->toArray();

        foreach ($rows as $rowData) {
            $type      = $rowData['type'];
            $rowNumber = $rowData['row'];
            $raw       = $rowData['_raw'] ?? [];

            if ($type === 'skip') {
                $summary['skipped']++;
                continue;
            }

            try {
                switch ($type) {
                    case 'customer':
                        $this->importCustomer(
                            $raw, $colMap, $rowNumber,
                            $existingCustomers, $summary
                        );
                        break;

                    case 'supplier':
                        $this->importSupplier(
                            $raw, $colMap, $rowNumber,
                            $existingSuppliers, $categories, $summary
                        );
                        break;

                    case 'staff':
                        $this->importEmployee(
                            $raw, $colMap, $rowNumber,
                            $existingEmployees, $summary
                        );
                        break;

                    case 'gl_account':
                        $this->importGlAccount(
                            $raw, $colMap, $rowNumber,
                            $existingGlAccounts, $summary
                        );
                        break;
                }
            } catch (\Exception $e) {
                Log::error("UnifiedImport commit row {$rowNumber} error: " . $e->getMessage());
                $summary[$type]['failed']++;
                $summary[$type]['errors'][] = "Row {$rowNumber}: Unexpected error - " . $e->getMessage();
            }
        }

        return response()->json(['summary' => $summary]);
    }

    /**
     * GET /import/sample  - download a sample multi-type XLSX template.
     */
    public function downloadSample()
    {
        $spreadsheet = new Spreadsheet();

        // Sheet 1: Customers
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Customers');
        $this->writeSheetData($sheet, [
            ['Customer Name', 'Phone', 'Address', 'Credit Limit', 'Opening Balance'],
            ['Ali Ahmed', '03001234567', 'Main Bazaar, Lahore', '50000', '0'],
            ['Sara Traders', '03219876543', 'Saddar Road, Karachi', '100000', '5000'],
        ]);

        // Sheet 2: Suppliers
        $supplierSheet = $spreadsheet->createSheet();
        $supplierSheet->setTitle('Suppliers');
        $this->writeSheetData($supplierSheet, [
            ['Supplier Name', 'Unique Code', 'Category', 'Company Name', 'Phone', 'Address', 'Opening Balance'],
            ['ABC Distributors', 'SUP-001', 'Wholesale', 'ABC Group', '03001234567', 'Main Market, Lahore', '15000'],
            ['Zahid Vendor', 'SUP-002', 'Local', '', '03219876543', 'Saddar, Karachi', '0'],
        ]);

        // Sheet 3: Staff / Employees
        $staffSheet = $spreadsheet->createSheet();
        $staffSheet->setTitle('Staff');
        $this->writeSheetData($staffSheet, [
            ['Full Name', 'Phone', 'Email', 'Designation', 'Address', 'City', 'Employee Code', 'Commission Rate'],
            ['Ahmed Khan', '03001111111', 'ahmed@example.com', 'Salesman', 'Main St', 'Lahore', 'EMP-001', '2.5'],
            ['Fatima Noor', '03002222222', '', 'Cashier', '', 'Karachi', 'EMP-002', '0'],
        ]);

        // Sheet 4: Chart of Accounts
        $glSheet = $spreadsheet->createSheet();
        $glSheet->setTitle('Chart of Accounts');
        $this->writeSheetData($glSheet, [
            ['GL Code', 'GL Type', 'Account Name', 'Account Type', 'Opening Balance'],
            ['01-001', '01: CASH/BANKS', 'Cash Drawer', 'ASSETS', '10000'],
            ['02-001', '02: INVENTORY', 'Stock Inventory', 'ASSETS', '0'],
            ['05-001', '05: RECEIVABLES', 'Trade Receivables', 'ASSETS', '0'],
        ]);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="unified_import_sample.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    // =========================================================================
    // PRIVATE: TYPE-SPECIFIC IMPORT METHODS
    // =========================================================================

    private function importCustomer(
        array $raw, array $colMap, int $rowNumber,
        array &$existing, array &$summary
    ): void {
        $name = $this->cell($raw, $colMap, 'name');
        if ($name === '') {
            $summary['customer']['errors'][] = "Row {$rowNumber}: Customer name is empty - skipped.";
            $summary['customer']['skipped']++;
            return;
        }

        $key = strtolower($name);
        if (isset($existing[$key])) {
            $summary['customer']['errors'][] = "Row {$rowNumber}: Customer '{$name}' already exists - skipped.";
            $summary['customer']['skipped']++;
            return;
        }

        $creditLimit    = $this->numericCell($raw, $colMap, 'credit_limit', 0);
        $openingBalance = $this->numericCell($raw, $colMap, 'opening_balance', 0);
        $phone          = $this->cell($raw, $colMap, 'phone');
        $address        = $this->cell($raw, $colMap, 'address');

        DB::beginTransaction();
        try {
            $customer = Customer::create([
                'name'         => $name,
                'phone'        => $phone ?: null,
                'address'      => $address ?: null,
                'credit_limit' => $creditLimit,
                'balance'      => $openingBalance,   // Customer uses `balance` column
            ]);
            $existing[$key] = $customer->id;
            DB::commit();
            $summary['customer']['inserted']++;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function importSupplier(
        array $raw, array $colMap, int $rowNumber,
        array &$existing, array &$categories, array &$summary
    ): void {
        $name = $this->cell($raw, $colMap, 'name');
        if ($name === '') {
            $summary['supplier']['errors'][] = "Row {$rowNumber}: Supplier name is empty - skipped.";
            $summary['supplier']['skipped']++;
            return;
        }

        // Auto-generate code if not provided
        $code = $this->cell($raw, $colMap, 'code');
        if ($code === '') {
            $code = 'IMP-' . now()->format('YmdHis') . '-' . $rowNumber;
        }
        $code = strtoupper($code);

        $codeKey = strtolower($code);
        if (isset($existing[$codeKey])) {
            $summary['supplier']['errors'][] = "Row {$rowNumber}: Supplier code '{$code}' already exists - skipped.";
            $summary['supplier']['skipped']++;
            return;
        }

        $categoryName   = $this->cell($raw, $colMap, 'category');
        $companyName    = $this->cell($raw, $colMap, 'company_name');
        $phone          = $this->cell($raw, $colMap, 'phone');
        $address        = $this->cell($raw, $colMap, 'address');
        $openingBalance = $this->numericCell($raw, $colMap, 'opening_balance', 0);

        DB::beginTransaction();
        try {
            // Resolve or create category
            $catId = null;
            if ($categoryName !== '') {
                $catKey = strtolower($categoryName);
                if (isset($categories[$catKey])) {
                    $catId = $categories[$catKey];
                } else {
                    $catObj = SupplierCategory::create(['name' => $categoryName]);
                    $categories[$catKey] = $catObj->id;
                    $catId = $catObj->id;
                }
            }

            $supplier = Supplier::create([
                'name'            => $name,
                'company_name'    => $companyName ?: null,
                'code'            => $code,
                'category_id'     => $catId,
                'phone'           => $phone ?: null,
                'address'         => $address ?: null,
                'opening_balance' => $openingBalance,
                'current_balance' => $openingBalance, // matches existing import pattern
            ]);

            if ($openingBalance != 0) {
                SupplierLedger::create([
                    'supplier_id'    => $supplier->id,
                    'date'           => now()->toDateString(),
                    'reference_type' => 'opening',
                    'reference_id'   => $supplier->id,
                    'description'    => 'Opening Balance on Import',
                    'debit'          => 0,
                    'credit'         => $openingBalance,
                    'balance'        => $openingBalance,
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

    /**
     * SAFETY GUARANTEE: Creates an Employee profile record ONLY.
     *   password = null        -> login via auth:employee guard is impossible
     *   is_active = false      -> record is flagged inactive
     *   No User record created -> no auth:web guard access
     *   No Spatie role assigned -> Employee model has no HasRoles trait
     *   No pin set             -> PIN-based access also blocked
     */
    private function importEmployee(
        array $raw, array $colMap, int $rowNumber,
        array &$existing, array &$summary
    ): void {
        $name = $this->cell($raw, $colMap, 'name');
        if ($name === '') {
            $summary['staff']['errors'][] = "Row {$rowNumber}: Staff name is empty - skipped.";
            $summary['staff']['skipped']++;
            return;
        }

        $phone = $this->cell($raw, $colMap, 'phone');

        // Phone-based duplicate check
        if ($phone !== '' && isset($existing[trim($phone)])) {
            $summary['staff']['errors'][] = "Row {$rowNumber}: Employee with phone '{$phone}' already exists - skipped.";
            $summary['staff']['skipped']++;
            return;
        }

        // Name-based fallback duplicate check (phone may be absent)
        if ($phone === '') {
            $nameDupe = Employee::where('full_name', $name)->exists();
            if ($nameDupe) {
                $summary['staff']['errors'][] = "Row {$rowNumber}: Employee '{$name}' already exists - skipped.";
                $summary['staff']['skipped']++;
                return;
            }
        }

        $email          = $this->cell($raw, $colMap, 'email');
        $designation    = $this->cell($raw, $colMap, 'designation') ?: 'Imported';
        $address        = $this->cell($raw, $colMap, 'address');
        $city           = $this->cell($raw, $colMap, 'city');
        $employeeCode   = $this->cell($raw, $colMap, 'employee_code');
        $commissionRate = $this->numericCell($raw, $colMap, 'commission_rate', 0);

        // Validate email uniqueness (employees.email is unique nullable)
        if ($email !== '') {
            if (Employee::where('email', $email)->exists()) {
                $summary['staff']['errors'][] = "Row {$rowNumber}: Email '{$email}' already exists - skipped.";
                $summary['staff']['skipped']++;
                return;
            }
        }

        DB::beginTransaction();
        try {
            $employee = Employee::create([
                'full_name'       => $name,
                'email'           => $email ?: null,
                'phone'           => $phone ?: null,
                'designation'     => $designation,
                'address'         => $address ?: null,
                'city'            => $city ?: null,
                'employee_code'   => $employeeCode ?: null,
                'commission_rate' => $commissionRate,
                // SAFETY: null password -> auth:employee login impossible
                //         is_active=false -> record is inert/inactive
                'password'        => null,
                'is_active'       => false,
            ]);

            if ($phone !== '') {
                $existing[trim($phone)] = $employee->id;
            }
            DB::commit();
            $summary['staff']['inserted']++;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function importGlAccount(
        array $raw, array $colMap, int $rowNumber,
        array &$existing, array &$summary
    ): void {
        // GL code may be in 'gl_code' or 'code' columns
        $glCode = $this->cell($raw, $colMap, 'gl_code')
            ?: $this->cell($raw, $colMap, 'code');

        if ($glCode === '') {
            $summary['gl_account']['errors'][] = "Row {$rowNumber}: GL Code is empty - skipped.";
            $summary['gl_account']['skipped']++;
            return;
        }

        $glKey = strtolower($glCode);
        if (isset($existing[$glKey])) {
            $summary['gl_account']['errors'][] = "Row {$rowNumber}: GL account '{$glCode}' already exists - skipped.";
            $summary['gl_account']['skipped']++;
            return;
        }

        $name           = $this->cell($raw, $colMap, 'name');
        $glType         = $this->cell($raw, $colMap, 'gl_type');
        // account_type is optional; default to 'General' if not provided
        $accountType    = $this->cell($raw, $colMap, 'account_type') ?: 'General';
        $openingBalance = $this->numericCell($raw, $colMap, 'opening_balance', 0);

        if ($name === '') {
            $summary['gl_account']['errors'][] = "Row {$rowNumber}: Account name is empty - skipped.";
            $summary['gl_account']['skipped']++;
            return;
        }

        // GeneralLedgerAccount uses the default DB connection.
        // Stancl Tenancy's BootstrapTenancy listener switches this to the
        // tenant DB automatically when a tenant session is active - same
        // pattern as GeneralLedgerController::store() which uses the model directly.
        DB::beginTransaction();
        try {
            GeneralLedgerAccount::create([
                'gl_code'         => $glCode,
                'gl_type'         => $glType ?: $glCode,
                'name'            => $name,
                'account_type'    => $accountType,
                'opening_balance' => $openingBalance,
                'current_balance' => $openingBalance,
            ]);
            $existing[$glKey] = true;
            DB::commit();
            $summary['gl_account']['inserted']++;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // =========================================================================
    // PRIVATE: DETECTION & PARSING HELPERS
    // =========================================================================

    /**
     * Suggest a file-level record type based on the column headers present.
     * Per-row prefix signals can still override this per row.
     */
    private function suggestFileType(array $headers, array $colMap): string
    {
        // Staff signals: role/position/designation columns
        if (
            in_array('role', $headers) ||
            in_array('position', $headers) ||
            in_array('designation', $headers) ||
            in_array('full name', $headers) ||
            in_array('full_name', $headers) ||
            ($colMap['designation'] ?? -1) >= 0   // -1 means "not found in file"
        ) {
            return 'staff';
        }

        // GL Account signals
        if (
            in_array('gl_code', $headers) ||
            in_array('gl code', $headers) ||
            in_array('gl_type', $headers) ||
            in_array('gl type', $headers) ||
            in_array('account_type', $headers) ||
            in_array('account type', $headers)
        ) {
            return 'gl_account';
        }

        // Supplier signals: unique code column present alongside supplier-name signal
        if (
            in_array('unique code', $headers) ||
            in_array('unique_code', $headers) ||
            in_array('supplier code', $headers) ||
            in_array('supplier_code', $headers) ||
            (isset($colMap['code']) && (
                in_array('supplier name', $headers) ||
                in_array('supplier_name', $headers) ||
                in_array('vendor', $headers) ||
                in_array('vendor name', $headers)
            ))
        ) {
            return 'supplier';
        }

        // Default -> customer
        return 'customer';
    }

    /**
     * Per-row type detection. Applies legacy account-code prefix signals
     * (05xxxx = Customer, 06xxxx = Supplier) on top of the file-level suggestion.
     */
    private function detectRowType(array $row, array $colMap, string $fileTypeSuggestion): string
    {
        $code = $this->cell($row, $colMap, 'code')
            ?: $this->cell($row, $colMap, 'gl_code');

        // Legacy prefix signals (narrow, safe - only for this specific pattern)
        if ($code !== '') {
            if (preg_match('/^05\d{4}/', $code)) return 'customer';
            if (preg_match('/^06\d{4}/', $code)) return 'supplier';
        }

        return $fileTypeSuggestion;
    }

    /**
     * Read the uploaded file into a 2D array (row x column).
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
        return array_map(fn($h) => strtolower(trim((string) $h)), $headerRow);
    }

    /**
     * Build a semantic column-index map from the normalised headers.
     * Returns an associative array of field -> column-index (-1 if absent).
     */
    private function buildColumnMap(array $headers): array
    {
        $find = function (array $options) use ($headers): int {
            foreach ($options as $opt) {
                $idx = array_search(strtolower(trim($opt)), $headers);
                if ($idx !== false) return (int) $idx;
            }
            return -1;
        };

        return [
            'name'            => $find(['customer name', 'supplier name', 'full name', 'full_name', 'name', 'staff name', 'employee name', 'account name', 'client', 'vendor']),
            'phone'           => $find(['phone', 'phone no', 'phone_no', 'contact', 'mobile', 'telephone']),
            'code'            => $find(['unique code', 'unique_code', 'code', 'supplier code', 'supplier_code', 'vendor code', 'vendor_code', 'gl code', 'gl_code']),
            'gl_code'         => $find(['gl code', 'gl_code']),
            'gl_type'         => $find(['gl type', 'gl_type', 'type']),
            'account_type'    => $find(['account type', 'account_type']),
            'category'        => $find(['category', 'supplier category', 'supplier_category', 'group']),
            'company_name'    => $find(['company name', 'company_name', 'company', 'organization']),
            'address'         => $find(['address', 'location', 'street']),
            'city'            => $find(['city', 'town']),
            'email'           => $find(['email', 'email address', 'e-mail']),
            'designation'     => $find(['designation', 'role', 'position', 'job title', 'job_title']),
            'employee_code'   => $find(['employee code', 'employee_code', 'emp code', 'emp_code']),
            'commission_rate' => $find(['commission rate', 'commission_rate', 'commission', 'rate']),
            'credit_limit'    => $find(['credit limit', 'credit_limit', 'limit']),
            'opening_balance' => $find(['opening balance', 'opening_balance', 'balance', 'opening debt', 'debt']),
        ];
    }

    /**
     * Get a string cell value by semantic field name; returns '' if absent.
     */
    private function cell(array $row, array $colMap, string $field): string
    {
        $idx = $colMap[$field] ?? -1;
        if ($idx < 0 || !isset($row[$idx])) return '';
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

    /**
     * Build a compact extra-fields string for preview display.
     * Excludes name, phone, code (shown in dedicated table columns).
     */
    private function buildExtraFields(array $row, array $colMap, array $headers): string
    {
        $skipFields = ['name', 'phone', 'code', 'gl_code'];
        $skipIdx    = [];

        foreach ($skipFields as $field) {
            $idx = $colMap[$field] ?? -1;
            if ($idx >= 0) $skipIdx[] = $idx;
        }

        $parts = [];
        foreach ($headers as $idx => $header) {
            if (in_array($idx, $skipIdx, true)) continue;
            $val = isset($row[$idx]) ? trim((string) $row[$idx]) : '';
            if ($val === '') continue;
            $parts[] = ucwords(str_replace('_', ' ', $header)) . ': ' . $val;
        }

        return implode(' | ', array_slice($parts, 0, 4)); // Cap for display
    }

    /**
     * Helper: write rows + bold header to a worksheet.
     */
    private function writeSheetData($sheet, array $rows): void
    {
        foreach ($rows as $ri => $row) {
            foreach ($row as $ci => $val) {
                $cell = Coordinate::stringFromColumnIndex($ci + 1) . ($ri + 1);
                $sheet->setCellValue($cell, $val);
            }
        }
        $lastCol = Coordinate::stringFromColumnIndex(count($rows[0]));
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        foreach (range(1, count($rows[0])) as $ci) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($ci))->setAutoSize(true);
        }
    }
}
