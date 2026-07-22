<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    /**
     * Search across Customers, Suppliers, Items, Sales/Invoices, and Expenses.
     */
    public function search(Request $request)
    {
        $query = trim($request->input('q', ''));

        if (strlen($query) < 2) {
            return response()->json([
                'customers' => [],
                'suppliers' => [],
                'items'     => [],
                'sales'     => [],
                'expenses'  => [],
                'total'     => 0,
            ]);
        }

        // 1. Customers
        $customers = Customer::where('name', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'phone', 'balance')
            ->limit(5)
            ->get()
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'title' => $c->name,
                    'subtitle' => 'Phone: ' . ($c->phone ?? 'N/A') . ' | Bal: Rs.' . number_format($c->balance ?? 0),
                    'url' => route('customers.index') . '?search=' . urlencode($c->name),
                    'icon' => 'fas fa-user',
                    'color' => 'text-blue-500',
                ];
            });

        // 2. Suppliers
        $suppliers = Supplier::where('name', 'LIKE', "%{$query}%")
            ->orWhere('code', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'code', 'current_balance')
            ->limit(5)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'title' => $s->name . ($s->code ? " ({$s->code})" : ''),
                    'subtitle' => 'Bal: Rs.' . number_format($s->current_balance ?? 0),
                    'url' => route('suppliers.show', $s->id),
                    'icon' => 'fas fa-truck',
                    'color' => 'text-purple-500',
                ];
            });

        // 3. Items / Products
        $items = Item::where('name', 'LIKE', "%{$query}%")
            ->orWhere('barcode', 'LIKE', "%{$query}%")
            ->orWhere('code', 'LIKE', "%{$query}%")
            ->select('id', 'name', 'barcode', 'code', 'sale_rate', 'on_hand')
            ->limit(5)
            ->get()
            ->map(function ($i) {
                return [
                    'id' => $i->id,
                    'title' => $i->name,
                    'subtitle' => 'Stock: ' . ($i->on_hand ?? 0) . ' | Rate: Rs.' . number_format($i->sale_rate ?? 0),
                    'url' => route('items.index') . '?search=' . urlencode($i->name),
                    'icon' => 'fas fa-box',
                    'color' => 'text-emerald-500',
                ];
            });

        // 4. Sales / Invoices
        $sales = Sale::where('invoice_no', 'LIKE', "%{$query}%")
            ->select('id', 'invoice_no', 'grand_total', 'payment_mode', 'created_at')
            ->limit(5)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'title' => 'Invoice ' . ($s->invoice_no ?? '#' . $s->id),
                    'subtitle' => ($s->payment_mode ?? 'Sale') . ' | Total: Rs.' . number_format($s->grand_total ?? 0) . ' (' . $s->created_at->format('d M Y') . ')',
                    'url' => route('sales.print', $s->id),
                    'icon' => 'fas fa-file-invoice-dollar',
                    'color' => 'text-amber-500',
                ];
            });

        // 5. Payments / Expenses
        $expenses = Payment::where('payment_no', 'LIKE', "%{$query}%")
            ->orWhere('paid_to_account', 'LIKE', "%{$query}%")
            ->orWhere('memo', 'LIKE', "%{$query}%")
            ->select('id', 'payment_no', 'paid_to_account', 'amount_paid', 'payment_date')
            ->limit(5)
            ->get()
            ->map(function ($e) {
                return [
                    'id' => $e->id,
                    'title' => $e->payment_no . ' — ' . $e->paid_to_account,
                    'subtitle' => 'Amount: Rs.' . number_format($e->amount_paid ?? 0) . ' (' . \Carbon\Carbon::parse($e->payment_date)->format('d M Y') . ')',
                    'url' => route('payments.index') . '?search=' . urlencode($e->payment_no),
                    'icon' => 'fas fa-receipt',
                    'color' => 'text-red-500',
                ];
            });

        $total = $customers->count() + $suppliers->count() + $items->count() + $sales->count() + $expenses->count();

        return response()->json([
            'customers' => $customers,
            'suppliers' => $suppliers,
            'items'     => $items,
            'sales'     => $sales,
            'expenses'  => $expenses,
            'total'     => $total,
        ]);
    }
}
