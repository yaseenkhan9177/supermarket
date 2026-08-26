<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Payment;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        ExpenseCategory::seedDefaultsIfEmpty();

        $validator = Validator::make($request->all(), [
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date'   => 'nullable|date_format:Y-m-d|after_or_equal:from_date',
        ], [
            'from_date.date_format'  => 'The From Date must be in YYYY-MM-DD format.',
            'to_date.date_format'    => 'The To Date must be in YYYY-MM-DD format.',
            'to_date.after_or_equal' => 'The From Date cannot be after the To Date.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('expenses.index')
                ->withErrors($validator)
                ->withInput();
        }

        $preset = $request->input('preset', 'today');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $fromDate = $request->input('from_date');
            $toDate = $request->input('to_date');
        } elseif ($request->filled('preset')) {
            switch ($preset) {
                case 'yesterday':
                    $fromDate = today()->subDay()->toDateString();
                    $toDate = today()->subDay()->toDateString();
                    break;
                case 'last_7_days':
                    $fromDate = today()->subDays(6)->toDateString();
                    $toDate = today()->toDateString();
                    break;
                case 'this_week':
                    $fromDate = now()->startOfWeek()->toDateString();
                    $toDate = now()->endOfWeek()->toDateString();
                    break;
                case 'this_month':
                    $fromDate = now()->startOfMonth()->toDateString();
                    $toDate = now()->endOfMonth()->toDateString();
                    break;
                case 'last_month':
                    $fromDate = now()->subMonth()->startOfMonth()->toDateString();
                    $toDate = now()->subMonth()->endOfMonth()->toDateString();
                    break;
                case 'all_time':
                    $fromDate = null;
                    $toDate = null;
                    break;
                case 'today':
                default:
                    $fromDate = today()->toDateString();
                    $toDate = today()->toDateString();
                    $preset = 'today';
                    break;
            }
        } else {
            // Default load: Today
            $fromDate = today()->toDateString();
            $toDate = today()->toDateString();
            $preset = 'today';
        }

        // Build base query
        $query = Expense::query();

        if ($fromDate && $toDate) {
            $start = Carbon::parse($fromDate)->startOfDay();
            $end = Carbon::parse($toDate)->endOfDay();
            $query->whereBetween('expense_date', [$start, $end]);
        } elseif ($fromDate) {
            $query->whereDate('expense_date', '>=', $fromDate);
        } elseif ($toDate) {
            $query->whereDate('expense_date', '<=', $toDate);
        }

        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('expense_category_id', $request->category_id);
        }

        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('wallet_id') && $request->wallet_id !== 'all') {
            $query->where('wallet_id', $request->wallet_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('expense_no', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('category_name', 'LIKE', "%{$search}%")
                  ->orWhere('reference_no', 'LIKE', "%{$search}%")
                  ->orWhere('notes', 'LIKE', "%{$search}%");
            });
        }

        // KPI Summaries calculated on cloned query
        $summaryQuery = clone $query;
        $totalExpenses = (float) $summaryQuery->sum('amount');
        $expenseCount  = (int) $summaryQuery->count();
        $avgExpense    = $expenseCount > 0 ? round($totalExpenses / $expenseCount, 2) : 0;
        $maxExpense    = (float) ($summaryQuery->max('amount') ?? 0);
        $cashTotal     = (float) (clone $query)->where('payment_method', 'Cash')->sum('amount');
        $bankTotal     = (float) (clone $query)->where('payment_method', '!=', 'Cash')->sum('amount');

        // Analytics: Category Breakdown
        $categoryBreakdown = (clone $query)
            ->select('category_name', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_name')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Analytics: Payment Method Breakdown
        $paymentBreakdown = (clone $query)
            ->select('payment_method', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderBy('total_amount', 'desc')
            ->get();

        // Analytics: Daily Trend (for the filtered period)
        $dailyTrend = (clone $query)
            ->select('expense_date', DB::raw('SUM(amount) as daily_amount'))
            ->groupBy('expense_date')
            ->orderBy('expense_date', 'asc')
            ->get();

        // Paginated list
        $expenses = $query->with(['category', 'wallet', 'user'])
            ->latest('expense_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $categories = ExpenseCategory::active()->orderBy('name')->get();
        $wallets    = Wallet::where('is_active', true)->get();
        $bankAccounts = BankAccount::orderBy('bank_name')->get();

        return view('expenses.index', compact(
            'expenses',
            'categories',
            'wallets',
            'bankAccounts',
            'fromDate',
            'toDate',
            'preset',
            'totalExpenses',
            'expenseCount',
            'avgExpense',
            'maxExpense',
            'cashTotal',
            'bankTotal',
            'categoryBreakdown',
            'paymentBreakdown',
            'dailyTrend'
        ));
    }

    public function create()
    {
        ExpenseCategory::seedDefaultsIfEmpty();
        $categories   = ExpenseCategory::active()->orderBy('name')->get();
        $wallets      = Wallet::where('is_active', true)->get();
        $bankAccounts = BankAccount::orderBy('bank_name')->get();

        return view('expenses.create', compact('categories', 'wallets', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'expense_date'        => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description'         => 'required|string|max:255',
            'amount'              => 'required|numeric|min:0.01',
            'payment_method'      => 'required|string|in:Cash,Bank,Cheque,Card,Other',
            'wallet_id'           => 'nullable|exists:wallets,id',
            'bank_account_id'     => 'nullable|exists:bank_accounts,id',
            'reference_no'        => 'nullable|string|max:100',
            'notes'               => 'nullable|string|max:1000',
            'attachment'          => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:5120', // 5MB max
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $category = ExpenseCategory::findOrFail($request->expense_category_id);
                $amount = (float) $request->amount;

                // 1. Handle file upload safely
                $attachmentPath = null;
                if ($request->hasFile('attachment')) {
                    $file = $request->file('attachment');
                    $filename = 'expense_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $attachmentPath = $file->storeAs('attachments/expenses', $filename, 'public');
                }

                // 2. Generate Unique Expense Voucher Number
                $dateCode = Carbon::parse($request->expense_date)->format('Ymd');
                $countToday = Expense::whereDate('expense_date', $request->expense_date)->count() + 1;
                $expenseNo = 'EXP-' . $dateCode . '-' . str_pad($countToday, 3, '0', STR_PAD_LEFT);

                // Ensure absolute uniqueness
                while (Expense::where('expense_no', $expenseNo)->exists()) {
                    $expenseNo = 'EXP-' . $dateCode . '-' . rand(100, 999);
                }

                // 3. Resolve Wallet & adjust balance
                $wallet = null;
                if ($request->filled('wallet_id')) {
                    $wallet = Wallet::lockForUpdate()->find($request->wallet_id);
                } elseif ($request->payment_method === 'Cash') {
                    // Prefer active counter/cash wallet; fall back to any active wallet
                    $wallet = Wallet::where('is_active', true)
                        ->whereIn('type', ['counter', 'wallet'])
                        ->first()
                        ?? Wallet::where('is_active', true)->first();
                }

                if ($wallet) {
                    $wallet->adjustBalance(-abs($amount));
                }

                // 4. Create Synced Accounting Payment record
                $payment = Payment::create([
                    'payment_no'        => 'PAY-' . $expenseNo,
                    'payment_date'      => $request->expense_date,
                    'paid_to_account'   => $category->name,
                    'paid_from_account' => $wallet ? $wallet->name : $request->payment_method,
                    'wallet_id'         => $wallet ? $wallet->id : null,
                    'bank_account_id'   => $request->bank_account_id,
                    'amount_paid'       => $amount,
                    'payment_method'    => in_array($request->payment_method, ['Cash', 'Bank', 'Cheque']) ? $request->payment_method : 'Cash',
                    'expense_type'      => $category->name,
                    'memo'              => $request->description . ($request->notes ? " | {$request->notes}" : ''),
                    'user_id'           => auth()->id() ?? 1,
                    'is_locked'         => true,
                ]);

                // 5. Create Expense Record
                $expense = Expense::create([
                    'expense_no'          => $expenseNo,
                    'expense_date'        => $request->expense_date,
                    'expense_category_id' => $category->id,
                    'category_name'       => $category->name,
                    'description'         => $request->description,
                    'amount'              => $amount,
                    'payment_method'      => $request->payment_method,
                    'wallet_id'           => $wallet ? $wallet->id : null,
                    'bank_account_id'     => $request->bank_account_id,
                    'payment_id'          => $payment->id,
                    'reference_no'        => $request->reference_no,
                    'notes'               => $request->notes,
                    'attachment_path'     => $attachmentPath,
                    'user_id'             => auth()->id(),
                ]);

                // 6. Record Audit Log
                AuditLog::record(
                    'expense_created',
                    "Created expense {$expense->expense_no} of Rs. " . number_format($expense->amount, 2) . " for {$category->name} paid via {$expense->payment_method}",
                    'Expense',
                    $expense->id,
                    [
                        'amount'         => $amount,
                        'category'       => $category->name,
                        'payment_method' => $expense->payment_method,
                        'wallet_id'      => $wallet ? $wallet->id : null,
                    ]
                );

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success'    => true,
                        'message'    => 'Expense recorded successfully!',
                        'expense'    => $expense,
                        'print_url'  => route('expenses.print', $expense->id),
                    ]);
                }

                return redirect()->route('expenses.index')->with('success', "Expense {$expense->expense_no} of Rs. " . number_format($expense->amount, 2) . " recorded successfully.");
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to record expense: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $expense = Expense::with(['category', 'wallet', 'bankAccount', 'user', 'payment'])->findOrFail($id);
        return view('expenses.show', compact('expense'));
    }

    public function edit($id)
    {
        $expense      = Expense::with(['category', 'wallet'])->findOrFail($id);
        $categories   = ExpenseCategory::active()->orderBy('name')->get();
        $wallets      = Wallet::where('is_active', true)->get();
        $bankAccounts = BankAccount::orderBy('bank_name')->get();

        return view('expenses.edit', compact('expense', 'categories', 'wallets', 'bankAccounts'));
    }

    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $request->validate([
            'expense_date'        => 'required|date',
            'expense_category_id' => 'required|exists:expense_categories,id',
            'description'         => 'required|string|max:255',
            'amount'              => 'required|numeric|min:0.01',
            'payment_method'      => 'required|string|in:Cash,Bank,Cheque,Card,Other',
            'wallet_id'           => 'nullable|exists:wallets,id',
            'bank_account_id'     => 'nullable|exists:bank_accounts,id',
            'reference_no'        => 'nullable|string|max:100',
            'notes'               => 'nullable|string|max:1000',
            'attachment'          => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
        ]);

        try {
            return DB::transaction(function () use ($request, $expense) {
                $category = ExpenseCategory::findOrFail($request->expense_category_id);
                $oldAmount = (float) $expense->amount;
                $newAmount = (float) $request->amount;
                $oldWalletId = $expense->wallet_id;
                $newWalletId = $request->wallet_id;

                // 1. Revert previous wallet adjustment
                if ($oldWalletId) {
                    $oldWallet = Wallet::lockForUpdate()->find($oldWalletId);
                    if ($oldWallet) {
                        $oldWallet->adjustBalance(+abs($oldAmount));
                    }
                }

                // 2. Apply new wallet adjustment
                $newWallet = null;
                if ($newWalletId) {
                    $newWallet = Wallet::lockForUpdate()->find($newWalletId);
                    if ($newWallet) {
                        $newWallet->adjustBalance(-abs($newAmount));
                    }
                }

                // 3. Handle attachment replacement
                $attachmentPath = $expense->attachment_path;
                if ($request->hasFile('attachment')) {
                    if ($attachmentPath && Storage::disk('public')->exists($attachmentPath)) {
                        Storage::disk('public')->delete($attachmentPath);
                    }
                    $file = $request->file('attachment');
                    $filename = 'expense_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $attachmentPath = $file->storeAs('attachments/expenses', $filename, 'public');
                }

                // 4. Update linked Payment record if existing
                if ($expense->payment_id) {
                    $payment = Payment::find($expense->payment_id);
                    if ($payment) {
                        $payment->update([
                            'payment_date'      => $request->expense_date,
                            'paid_to_account'   => $category->name,
                            'paid_from_account' => $newWallet ? $newWallet->name : $request->payment_method,
                            'wallet_id'         => $newWallet ? $newWallet->id : null,
                            'bank_account_id'   => $request->bank_account_id,
                            'amount_paid'       => $newAmount,
                            'payment_method'    => in_array($request->payment_method, ['Cash', 'Bank', 'Cheque']) ? $request->payment_method : 'Cash',
                            'expense_type'      => $category->name,
                            'memo'              => $request->description . ($request->notes ? " | {$request->notes}" : ''),
                        ]);
                    }
                }

                // 5. Update Expense Record
                $expense->update([
                    'expense_date'        => $request->expense_date,
                    'expense_category_id' => $category->id,
                    'category_name'       => $category->name,
                    'description'         => $request->description,
                    'amount'              => $newAmount,
                    'payment_method'      => $request->payment_method,
                    'wallet_id'           => $newWallet ? $newWallet->id : null,
                    'bank_account_id'     => $request->bank_account_id,
                    'reference_no'        => $request->reference_no,
                    'notes'               => $request->notes,
                    'attachment_path'     => $attachmentPath,
                ]);

                // 6. Record Audit Log
                AuditLog::record(
                    'expense_updated',
                    "Updated expense {$expense->expense_no} to Rs. " . number_format($expense->amount, 2) . " ({$category->name})",
                    'Expense',
                    $expense->id,
                    [
                        'old_amount' => $oldAmount,
                        'new_amount' => $newAmount,
                        'category'   => $category->name,
                    ]
                );

                return redirect()->route('expenses.index')->with('success', "Expense {$expense->expense_no} updated successfully.");
            });
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update expense: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);

        try {
            DB::transaction(function () use ($expense) {
                $amount = (float) $expense->amount;

                // 1. Revert wallet adjustment
                if ($expense->wallet_id) {
                    $wallet = Wallet::lockForUpdate()->find($expense->wallet_id);
                    if ($wallet) {
                        $wallet->adjustBalance(+abs($amount));
                    }
                }

                // 2. Delete linked Payment record
                if ($expense->payment_id) {
                    $payment = Payment::find($expense->payment_id);
                    if ($payment) {
                        $payment->delete();
                    }
                }

                // 3. Delete attachment if exists
                if ($expense->attachment_path && Storage::disk('public')->exists($expense->attachment_path)) {
                    Storage::disk('public')->delete($expense->attachment_path);
                }

                $expenseNo = $expense->expense_no;
                $expense->delete();

                // 4. Record Audit Log
                AuditLog::record(
                    'expense_deleted',
                    "Deleted expense {$expenseNo} of Rs. " . number_format($amount, 2) . " and restored wallet balance",
                    'Expense',
                    $expense->id
                );
            });

            return redirect()->route('expenses.index')->with('success', "Expense {$expense->expense_no} deleted and wallet balance restored.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete expense: ' . $e->getMessage());
        }
    }

    public function printVoucher($id)
    {
        $expense = Expense::with(['category', 'wallet', 'bankAccount', 'user'])->findOrFail($id);
        return view('expenses.voucher', compact('expense'));
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'xlsx');

        // Apply same filters as index
        $query = Expense::with(['category', 'wallet', 'user']);

        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }
        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('expense_category_id', $request->category_id);
        }
        if ($request->filled('payment_method') && $request->payment_method !== 'all') {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('wallet_id') && $request->wallet_id !== 'all') {
            $query->where('wallet_id', $request->wallet_id);
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('expense_no', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('category_name', 'LIKE', "%{$search}%")
                  ->orWhere('reference_no', 'LIKE', "%{$search}%");
            });
        }

        $expenses = $query->latest('expense_date')->get();

        if ($format === 'csv') {
            $filename = 'expenses_' . date('Y_m_d_His') . '.csv';
            $headers = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($expenses) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Expense No', 'Date', 'Category', 'Description', 'Payment Method', 'Account/Wallet', 'Reference No', 'Amount (Rs.)', 'Created By', 'Notes']);

                foreach ($expenses as $exp) {
                    fputcsv($file, [
                        $exp->expense_no,
                        $exp->expense_date->format('Y-m-d'),
                        $exp->category_name,
                        $exp->description,
                        $exp->payment_method,
                        $exp->wallet->name ?? '—',
                        $exp->reference_no ?? '—',
                        number_format($exp->amount, 2, '.', ''),
                        $exp->user->name ?? 'Staff',
                        $exp->notes ?? '',
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        // Export as Excel XLSX
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Expenses Report');

        // Headers
        $headers = ['A1' => 'Expense #', 'B1' => 'Date', 'C1' => 'Category', 'D1' => 'Description', 'E1' => 'Payment Method', 'F1' => 'Account / Wallet', 'G1' => 'Reference #', 'H1' => 'Amount (Rs.)', 'I1' => 'Recorded By', 'J1' => 'Notes'];
        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
            $sheet->getStyle($cell)->getFont()->setBold(true);
        }

        $row = 2;
        foreach ($expenses as $exp) {
            $sheet->setCellValue("A{$row}", $exp->expense_no);
            $sheet->setCellValue("B{$row}", $exp->expense_date->format('d-M-Y'));
            $sheet->setCellValue("C{$row}", $exp->category_name);
            $sheet->setCellValue("D{$row}", $exp->description);
            $sheet->setCellValue("E{$row}", $exp->payment_method);
            $sheet->setCellValue("F{$row}", $exp->wallet->name ?? '—');
            $sheet->setCellValue("G{$row}", $exp->reference_no ?? '—');
            $sheet->setCellValue("H{$row}", (float) $exp->amount);
            $sheet->setCellValue("I{$row}", $exp->user->name ?? 'Staff');
            $sheet->setCellValue("J{$row}", $exp->notes ?? '');
            $row++;
        }

        // Total Row
        $sheet->setCellValue("G{$row}", 'TOTAL:');
        $sheet->setCellValue("H{$row}", "=SUM(H2:H" . ($row - 1) . ")");
        $sheet->getStyle("G{$row}:H{$row}")->getFont()->setBold(true);

        // Auto-fit columns
        foreach (range(1, 10) as $colIdx) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'expenses_' . date('Y_m_d_His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function report(Request $request)
    {
        ExpenseCategory::seedDefaultsIfEmpty();

        $fromDate = $request->input('from_date', now()->startOfMonth()->toDateString());
        $toDate   = $request->input('to_date', now()->endOfMonth()->toDateString());
        $preset   = $request->input('preset', 'this_month');

        $query = Expense::whereBetween('expense_date', [
            Carbon::parse($fromDate)->startOfDay(),
            Carbon::parse($toDate)->endOfDay()
        ]);

        $totalExpenses = (float) (clone $query)->sum('amount');
        $totalCount    = (int) (clone $query)->count();

        $byCategory = (clone $query)
            ->select('category_name', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_name')
            ->orderBy('total_amount', 'desc')
            ->get();

        $byPaymentMethod = (clone $query)
            ->select('payment_method', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderBy('total_amount', 'desc')
            ->get();

        $byMonth = Expense::select(
                DB::raw("DATE_FORMAT(expense_date, '%Y-%m') as month_year"),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('month_year')
            ->orderBy('month_year', 'desc')
            ->limit(12)
            ->get();

        return view('reports.expenses', compact(
            'fromDate',
            'toDate',
            'preset',
            'totalExpenses',
            'totalCount',
            'byCategory',
            'byPaymentMethod',
            'byMonth'
        ));
    }
}
