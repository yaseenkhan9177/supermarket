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
}
