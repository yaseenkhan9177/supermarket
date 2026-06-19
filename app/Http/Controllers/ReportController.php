<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;

class ReportController extends Controller
{
    public function index()
    {
        // Dynamic: Fetch root-level folders (or reports)
        // Dynamic: Fetch root-level folders (or reports)
        $query = \App\Models\Report::whereNull('parent_id')
            ->where('is_hidden_global', false);

        // Safety Check: Filter strictly if not owner
        // Assuming 'is_owner' is a property on User model. Ensure Auth check to avoid crash if guest.
        if (auth()->check() && !auth()->user()->is_owner) {
            $query->where('is_owner_only', false);
        }

        $categories = $query->with(['children' => function ($q) {
            $q->where('is_hidden_global', false);
            if (auth()->check() && !auth()->user()->is_owner) {
                $q->where('is_owner_only', false);
            }
            $q->orderBy('sort_order')->orderBy('name');
        }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('reports.index', compact('categories'));
    }

    public function view($id, Request $request)
    {
        // Dynamic: Fetch Report Name from DB
        $report = \App\Models\Report::find($id);

        $report_name = $report ? $report->name : 'Unknown Report';

        return view('reports.view', [
            'report_id' => $id,
            'report_name' => $report_name
        ]);
    }

    public function generate(Request $request)
    {
        // 1. Capture Inputs
        $fromDate = $request->date_from;
        $toDate = $request->date_to;
        $reportId = $request->report_id;

        // 2. Define Structure (Mock Data Logic)
        // In a real app, you would run SQL queries here based on $reportId

        $reportData = [
            'company' => [
                'name' => 'OwnStore Supermarket',
                'address' => '123 Main Street, Peshawar, Pakistan',
                'phone' => '+92-300-1234567',
            ],
            'meta' => [
                'title' => 'Sales Report', // Default
                'period' => "$fromDate to $toDate",
                'generated_by' => 'Administrator',
                'generated_at' => now()->format('d-M-Y h:i A'),
            ],
            'columns' => [], // Table Headers
            'rows' => [],    // Table Data
            'totals' => [],  // Footer Totals
        ];

        // 3. Mock Different Reports
        switch ($reportId) {
            case 1: // Daily Sales
                $reportData['meta']['title'] = 'Daily Sales Summary';
                $reportData['columns'] = ['Date', 'Invoice Count', 'Cash Sales', 'Credit Sales', 'Total Revenue'];
                $reportData['rows'] = [
                    ['2026-01-01', '150', '50,000', '10,000', '60,000'],
                    ['2026-01-02', '120', '40,000', '5,000', '45,000'],
                    ['2026-01-03', '180', '70,000', '20,000', '90,000'],
                ];
                $reportData['totals'] = ['Total', '450', '160,000', '35,000', '195,000'];
                break;

            case 5: // Supplier Ledger
                $reportData['meta']['title'] = 'Supplier Ledger (General)';
                $reportData['columns'] = ['Date', 'Ref #', 'Supplier Name', 'Bill Amount', 'Paid', 'Balance'];
                $reportData['rows'] = [
                    ['2026-01-05', 'PUR-001', 'Nestle Pakistan', '120,000', '0', '120,000'],
                    ['2026-01-06', 'PAY-998', 'Nestle Pakistan', '0', '50,000', '70,000'],
                ];
                $reportData['totals'] = ['Closing Balance', '', '', '120,000', '50,000', '70,000'];
                break;

            default: // Generic Fallback
                $reportData['meta']['title'] = 'General List';
                $reportData['columns'] = ['Item ID', 'Description', 'Qty', 'Rate', 'Amount'];
                $reportData['rows'] = [
                    ['101', 'Test Item A', '10', '500', '5000'],
                    ['102', 'Test Item B', '2', '1000', '2000'],
                ];
                $reportData['totals'] = ['Total', '', '12', '', '7000'];
        }

        // 4. Return the Universal View
        return view('reports.output', compact('reportData'));
    }

    public function accountReports()
    {
        $reportTree = [
            [
                'name' => 'Financial',
                'icon' => 'fas fa-landmark',
                'color' => 'indigo',
                'reports' => [
                    'Account Ledger WEB CR DR', 'Account Ledger WEB Details', 'Account Ledger WEB DR CR',
                    'Accounts By Vouchers', 'Accounts Ledger', 'Accounts Ledger Full Details 1',
                    'Accounts Ledger Full Details 2', 'Activities Report Details', 'Balance Sheet Details 1',
                    'Balance Sheet Details 2', 'Balance Sheet Details 3', 'Balance Sheet Summary',
                    'Balance Sheet Summary Live', 'Balance Sheet To Excel', 'Consolidated Trial Balance AC',
                    'Consolidated Trial Balance GL', 'General Ledger Accounts', 'General Ledger Accounts PDF',
                    'General Ledger Accounts WEB', 'Journal', 'Journal Entries',
                    'Month Wise Account Summary (1 Month)', 'Month Wise Account Summary (2 Months)',
                    'Month Wise Account Summary (3 Months)', 'Profit and Loss Accountwise',
                    'Profit and Loss Summary', 'Trial Balance AC Wise 1', 'Trial Balance AC Wise 2',
                    'Trial Balance AC Wise 3', 'Trial Balance GL Wise',
                ],
            ],
            [
                'name' => 'Items and Services',
                'icon' => 'fas fa-boxes',
                'color' => 'emerald',
                'reports' => [
                    'Artical Summary', 'Category Profitability Reports', 'Category Profitability Summary CATEGORY',
                    'Category Profitability Summary COUNTER', 'Category wise Profit Details CATEGORY',
                    'Category wise Profitability Details', 'Category/Item Profit Summary CATEGORY',
                    'Category/Item Profitability Summary', 'Class Profitability Summary',
                    'Class wise Propitability Details', 'Class/Item Profitability Summary',
                    'Final Stock Details By Class (ALL)', 'Final Stock Details By Class (Non-Zero)',
                    'Final Stock List by Category (ALL)', 'Final Stock List by Category (NON-ZERO)',
                    'Final Stock List By Class (ALL)', 'Final Stock List By Class (Non-Zero)',
                    'Final Stock List By Record (Non-Zero)', 'Final Stock List Details by Category',
                    'Final Stock Summary By Category', 'Final Stock Summary By Class',
                    'Inventory Position on Date',
                ],
            ],
            [
                'name' => 'Item Lists',
                'icon' => 'fas fa-list',
                'color' => 'sky',
                'reports' => [
                    'Item List', 'Item List with Batch', 'LIST OF SUSPECTS',
                    'MINIMUM LIMIT CROSSED CLASS', 'MINIMUM LIMIT CROSSED DEPART',
                    'Physical Inventory Worksheet',
                ],
            ],
            [
                'name' => 'Price List',
                'icon' => 'fas fa-tags',
                'color' => 'amber',
                'reports' => [
                    'Price List By Batch All Class', 'Price List By Batch Dept',
                    'Price List By Batch Existing Class', 'Price List By Batch Existing Dept',
                    'Price List By Existing Dept', 'Price List By Item All Class',
                    'Price List By Item All Dept', 'Price List By Item Existing Class',
                    'Profitability By SaleMan By G. Profit', 'Profitability By SaleMan On Sales',
                    'Sale List', 'Sale List WhatsApp', 'Stock Checking Histry', 'Stock Sheet',
                ],
            ],
            [
                'name' => 'Lists',
                'icon' => 'fas fa-th-list',
                'color' => 'violet',
                'reports' => [
                    'Accounts', 'Assets', 'Bank Accounts', 'Category wise All',
                    'Category wise No Almara', 'Category wise With Almara', 'Customers',
                    'Employees', 'Expences', 'General Leadgers', 'Income',
                    'Liabilities', 'Users', 'Vendors',
                ],
            ],
            [
                'name' => 'Lucky Garments Suggested Reports',
                'icon' => 'fas fa-star',
                'color' => 'rose',
                'reports' => [
                    'Category wise profit summay', 'Class wise profit Summary',
                    'Profit by Vendor Details', 'Profit by Vendor Summary',
                    'Profit by Vendor/Category Details', 'Saleman Summary',
                ],
            ],
            [
                'name' => 'Programmers',
                'icon' => 'fas fa-code',
                'color' => 'slate',
                'reports' => [
                    'Account Closing Balances Datewise', 'ACCOUNT EFFECTS DAYWISE',
                    'ACCOUNT EFFECTS MONTHWISE', 'ACCOUNT EFFECTS WEEKWISE',
                    'Adjustment Effect', 'Adjustments', 'Adjustments in Dates',
                    'Adjustments in Dates Excel', 'Analise Sessions', 'Duplicate Bills',
                    'Duplicate Payments', 'Duplicate Sales', 'Find Serial',
                ],
            ],
            [
                'name' => 'Maqbool Suggested',
                'icon' => 'fas fa-lightbulb',
                'color' => 'orange',
                'reports' => [
                    'Negative Class Order Name', 'Negative Department Order Code',
                    'Negative Department Order Name', 'Negative Department Order Vendor',
                    'Missing Items', 'Run External', 'Sold Not Enough Record',
                    'Sold Not Enough Recorded Report',
                ],
            ],
            [
                'name' => 'Purchase',
                'icon' => 'fas fa-shopping-cart',
                'color' => 'cyan',
                'reports' => [
                    'Bill List', 'Expences by Account Details', 'Expences by Account Summary',
                    'Payments List', 'Price List of vendor', 'Purchase by Item Details',
                    'Purchase by Item summary', 'Purchase by Vendor Summary',
                    'Purchase by Vendor Details', 'Sales by Vendor Itemwise ALI',
                    'Sales/Stock By Vendor Itemwise', 'Sales/Stock By Vendor SALE RATE',
                    'Short Expiry', 'Stock Bellow Minimum',
                ],
            ],
            [
                'name' => 'Sales Cash',
                'icon' => 'fas fa-cash-register',
                'color' => 'green',
                'reports' => [
                    'Closed Sessions Analisis', 'Closed Sessions Continued', 'Closed Sessions Daywise',
                    'Excel NON for', 'External Sales/Purchase', 'Member Discounted Sales',
                    'Oceans Saleman Commission', 'Operator Cash Sale List',
                    'Operator Cash Sale List with Totals', 'Operator/Saleman Sales Summary',
                    'Profit by Customer Details (Cash Sales)', 'Profit by Customer Summary (Cash Sales)',
                    'Saleman Commision', 'Sales By Client Total Disc', 'Sales by Customer',
                    'Sales by Item Details (Cash Sales)', 'Sales by Item Summary (Cash Sales)',
                    'Sales Total Summary', 'Sales Total Disc Service', 'Sales/Purchases Excel',
                ],
            ],
            [
                'name' => 'Sales Credit',
                'icon' => 'fas fa-credit-card',
                'color' => 'blue',
                'reports' => [
                    'DS By Customer', 'DS By Item', 'Profit by Customer Details (DB WEB)',
                    'Profit by Customer Details (DB)', 'Profit by Customer Summary (DB)',
                    'Saleman Commision', 'Sales by Item Details (Credit)',
                    'Sales by Item Summary (Credit)',
                ],
            ],
            [
                'name' => 'Sales Joined',
                'icon' => 'fas fa-link',
                'color' => 'purple',
                'reports' => [
                    'Altar Report', 'Embroidery Report', 'Gents Tailor',
                    'Income and Expenses Daywise', 'Income and Expenses Monthwise',
                    'Income and Expenses Spot Session Wise', 'Income and Expenses Weekwise',
                    'Profit by Customer Details (Joined)', 'Profit by Customer Summary (Joined)',
                    'Saleman Commision', 'Sales by Customer Details (Joined)',
                    'Sales by Customer Summary (Joined)', 'Sales by Item Details (Joined)',
                    'Sales by Item Summary (Joined)', 'Sales by Salesman Summary', 'Tailor Report',
                ],
            ],
            [
                'name' => 'Statements',
                'icon' => 'fas fa-file-invoice',
                'color' => 'teal',
                'reports' => [
                    'Cash Flow Counterwise', 'Customer Statement', 'Detail Ledger',
                    'Distribution Sheet', 'Group Ledger', 'Pack Reports', 'Recovery Report',
                    'Saleman Collection Areawise', 'Saleman Collection Sheet',
                    'Saleman Progress Actual', 'Saleman Progress Trade',
                    'Sales Purchase By Vendor Cost Rate', 'Sales Purchase By Vendor Sale Rate',
                    'SAS Reports', 'Vendor Statement', 'Week Wise Collection',
                ],
            ],
        ];

        return view('reports.account_reports', compact('reportTree'));
    }

    public function openReport(\Illuminate\Http\Request $request)
    {
        $reportName = $request->input('name', 'Report');
        $category   = $request->input('category', '');
        return view('reports.view', [
            'report_id'   => 0,
            'report_name' => $reportName,
        ]);
    }
}
