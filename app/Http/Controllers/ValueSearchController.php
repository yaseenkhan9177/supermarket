<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ValueSearchController extends Controller
{
    public function index()
    {
        return view('values.index');
    }

    public function search(Request $request)
    {
        // 1. Start Building Queries for each table

        // Cash Sales Query
        $cashSales = DB::table('cash_sales')
            ->select(
                'sale_date as date',
                'invoice_no as ref',
                DB::raw("'Cash Sale' as type"),
                'customer_name as description',
                'grand_total as amount',
                'id'
            );

        // Debit Sales Query (Join with customers for name)
        $debitSales = DB::table('debit_sales')
            ->join('customers', 'debit_sales.customer_id', '=', 'customers.id')
            ->select(
                'debit_sales.invoice_date as date',
                'debit_sales.invoice_no as ref',
                DB::raw("'Debit Sale' as type"),
                'customers.name as description',
                'debit_sales.net_total as amount',
                'debit_sales.id'
            );

        // Purchases Query
        $purchases = DB::table('purchases')
            ->select(
                'invoice_date as date',
                'invoice_no as ref',
                DB::raw("'Purchase' as type"),
                DB::raw("COALESCE(supplier_name, 'Supplier') as description"),
                'net_total as amount',
                'id'
            );

        // Payments Query
        $payments = DB::table('payments')
            ->select(
                'payment_date as date',
                'payment_no as ref',
                DB::raw("'Payment' as type"),
                'paid_to_account as description',
                'amount_paid as amount',
                'id'
            );

        // Receipts Query
        $receipts = DB::table('receipts')
            ->select(
                'receipt_date as date',
                'receipt_no as ref',
                DB::raw("'Receipt' as type"),
                'party_name as description',
                'amount as amount',
                'id'
            );

        // 2. Filter Logic (Apply to base queries to optimize before union if possible,
        // but for simplicity and consistency across different structures, we can filter the union results
        // or apply common where clauses if column names match)

        // Since we normalized column names (date, amount), we can apply filters to each query or use a subquery/union wrapping.
        // Applying filters to each instance is cleaner for SQL performance.

        if ($request->useDate && $request->dateFrom && $request->dateTo) {
            $cashSales->whereBetween('sale_date', [$request->dateFrom, $request->dateTo]);
            $debitSales->whereBetween('debit_sales.invoice_date', [$request->dateFrom, $request->dateTo]);
            $purchases->whereBetween('invoice_date', [$request->dateFrom, $request->dateTo]);
            $payments->whereBetween('payment_date', [$request->dateFrom, $request->dateTo]);
            $receipts->whereBetween('receipt_date', [$request->dateFrom, $request->dateTo]);
        }

        // 3. Combine Them
        $query = $cashSales
            ->union($debitSales)
            ->union($purchases)
            ->union($payments)
            ->union($receipts);

        // 4. Order By Date
        // Using a subquery approach or just get() and sort allow for easier handling, 
        // but let's try DB::query()->fromSub($query, 'union_table')->orderBy...
        // For simplicity and to avoid complex SQL grammar issues with unions in some drivers, 
        // we can fetch and filter in PHP if the dataset isn't huge, OR allow the union to run and order.

        // The most robust Laravel way with Unions:
        $results = $query->orderBy('date', 'desc')->get();

        // 5. Value Filter and Type Filter (Post-processing)
        // Doing this in SQL on the UNION'd result is ideal but requires wrapping in fromSub.
        // Given this is an "Audit" tool, strict filtering is key. Let's do it in PHP for maximum flexibility 
        // unless performance becomes an issue.

        $filtered = $results->filter(function ($row) use ($request) {
            // Type Filter
            if ($request->type && $request->type !== 'all') {
                // Map frontend types to database types
                // 'sales' matches 'Cash Sale' or 'Debit Sale'
                // 'purchases' matches 'Purchase'
                // 'payments' matches 'Payment'
                // 'receipts' matches 'Receipt'

                $rowType = strtolower($row->type);
                $reqType = strtolower($request->type);

                if ($reqType === 'sales' && !str_contains($rowType, 'sale')) return false;
                if ($reqType === 'purchases' && !str_contains($rowType, 'purchase')) return false;
                if ($reqType === 'payments' && !str_contains($rowType, 'payment')) return false;
                if ($reqType === 'receipts' && !str_contains($rowType, 'receipt')) return false;
            }

            // Value Filter
            if ($request->useValue) {
                $amt = (float) $row->amount;
                // Loose comparison for "contains"? Or range?
                // The prompt specified "Lower Limit" and "Upper Limit"

                if ($request->valLower !== null && $request->valLower !== '' && $amt < (float)$request->valLower) return false;
                if ($request->valUpper !== null && $request->valUpper !== '' && $amt > (float)$request->valUpper) return false;
            }

            return true;
        });

        // Re-index array for JSON
        return response()->json($filtered->values());
    }
}
