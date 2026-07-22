<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DebitSale;
use App\Models\DebitSaleItem;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// Assuming Item Model exists
use App\Models\Item;

class DebitSaleController extends Controller
{
    public function index()
    {
        $sales = DebitSale::with('customer')->latest()->paginate(10);
        return view('store.debit_sales.index', compact('sales'));
    }

    public function create()
    {
        $customers = Customer::all();
        $employees = Employee::all();

        // Generate Invoice ID
        $latest = DebitSale::latest()->first();
        $nextId = $latest ? $latest->id + 1 : 1;
        $invoiceNo = 'DS-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        return view('store.debit_sales.create', compact('customers', 'employees', 'invoiceNo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'salesman_id' => 'required|exists:employees,id',
            'invoice_no'  => 'required|unique:debit_sales,invoice_no',
            'due_date'    => 'required|date|after_or_equal:today',
            'items'       => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:items,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.rate'       => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $customer = Customer::lockForUpdate()->findOrFail($request->customer_id);

            // 1. Create Invoice Header
            $sale = DebitSale::create([
                'invoice_no'    => $request->invoice_no,
                'customer_id'   => $request->customer_id,
                'salesman_id'   => $request->salesman_id,
                'invoice_date'  => $request->invoice_date ?? now()->toDateString(),
                'due_date'      => $request->due_date,
                'pricing_type'  => $request->pricing_type ?? 'Retail',
                'gross_total'   => 0, // Calculated below
                'discount'      => $request->discount ?? 0,
                'net_total'     => 0, // Calculated below
                'status'        => 'open',
            ]);

            $totalGross = 0;
            $totalNetBody = 0;

            // 2. Process Items
            foreach ($request->items as $row) {
                $item = Item::findOrFail($row['product_id']);

                $total = $row['quantity'] * $row['rate'];
                $discountPerc = $row['discount_percent'] ?? 0;
                $discountAmt = $total * ($discountPerc / 100);
                $net = $total - $discountAmt;

                DebitSaleItem::create([
                    'debit_sale_id' => $sale->id,
                    'product_id'    => $item->id,
                    'item_name'     => $item->description,
                    'quantity'      => $row['quantity'],
                    'rate'          => $row['rate'],
                    'total'         => $total,
                    'discount_percent' => $discountPerc,
                    'discount_amount'  => $discountAmt,
                    'net_amount'    => $net,
                ]);

                // Update Stock
                $item->decrement('on_hand', $row['quantity']);

                $totalGross += $total;
                $totalNetBody += $net;
            }

            // 3. Update Invoice Totals
            $finalNet = $totalNetBody - ($request->discount ?? 0);
            $sale->update([
                'gross_total' => $totalGross,
                'net_total'   => $finalNet
            ]);

            // 4. Update Customer Balance (Increase Debt)
            $customer->increment('balance', $finalNet);
            \App\Models\CustomerLedgerEntry::create([
                'customer_id'   => $customer->id,
                'type'          => 'sale',
                'amount'        => $finalNet,
                'balance_after' => $customer->fresh()->balance,
                'note'          => 'Debit Sale Invoice #' . ($sale->invoice_no ?? $sale->id),
                'created_by'    => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('debit-sales.index')->with('success', 'Debit Invoice created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving invoice: ' . $e->getMessage())->withInput();
        }
    }
}
