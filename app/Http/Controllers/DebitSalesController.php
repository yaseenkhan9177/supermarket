<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Item;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DebitSalesController extends Controller
{
    // 0. Index (Redirects to Create)
    public function index()
    {
        return redirect()->route('debit-sales.create');
    }

    // 1. Show Page
    public function create()
    {
        // Fetch only registered customers (Debit requires a known person)
        $customers = Customer::all();
        $salesmen = User::all();

        // Generate Invoice No (DS = Debit Sale)
        $nextInvoice = 'DS-' . date('Y') . '-' . str_pad(Sale::count() + 1, 4, '0', STR_PAD_LEFT);

        return view('debit-sales.create', compact('customers', 'salesmen', 'nextInvoice'));
    }

    // 2. Search API (Same as Cash Sales)
    public function searchItems(Request $request)
    {
        $query = $request->get('q');
        // Schema Mapping: description -> name, sale_rate -> price
        $items = Item::where('description', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->select('id', 'description as name', 'code', 'sale_rate as price', 'on_hand as stock_qty', 'item_type')
            ->limit(50)
            ->get();
        return response()->json($items);
    }

    public function show($id)
    {
        $sale = Sale::with(['items', 'user'])->findOrFail($id);
        return view('sales.receipt', compact('sale'));
    }

    // 3. Store Debit Sale
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id', // MANDATORY FOR DEBIT
            'rows' => 'required|array|min:1',
            'grand_total' => 'required|numeric',
        ]);

        try {
            $sale = DB::transaction(function () use ($request) {

                // Calculate Paid vs Due
                $paid = $request->received_amount ?? 0;

                // A. Create Invoice Header
                $sale = Sale::create([
                    'invoice_no' => $request->invoice_no,
                    'customer_id' => $request->customer_id,
                    'user_id' => $request->salesman_id ?? Auth::id(),
                    'sale_date' => $request->date ?? now(), // Schema: sale_date
                    'subtotal' => $request->grand_total,
                    'grand_total' => $request->grand_total,
                    'paid_amount' => $paid,
                    'change_amount' => 0, // No change given in debit usually
                    'payment_mode' => 'Debit', // MARKED AS DEBIT
                    'status' => 'completed',
                ]);

                // B. Save Items & Deduct Stock
                foreach ($request->rows as $row) {
                    $item = Item::where('id', $row['id'])->lockForUpdate()->first();

                    if ($item) {
                        SaleItem::create([
                            'sale_id' => $sale->id,
                            'item_id' => $item->id,
                            'item_name' => $item->description, // Schema: item_name -> description
                            'qty' => $row['qty'],
                            'rate' => $row['price'], // Schema: rate
                            'total' => $row['qty'] * $row['price'],
                        ]);

                        // 🔥 STOCK DEDUCTION (Same reliable logic)
                        $currentStock = $item->on_hand;
                        $newStock = $currentStock - $row['qty'];
                        // Attempt double update but prioritize on_hand which is known to exist
                        $item->update([
                            'on_hand' => $newStock,
                            // 'stock' => $newStock // Safely commented out unless user confirms 'stock' column exists to avoid crash
                        ]);
                    }
                }

                return $sale;
            });

            // C. Receipt
            $receiptHtml = view('sales.receipt', compact('sale'))->render();
            $receiptHtml = str_replace(['window.print()', 'window.close()'], '', $receiptHtml);

            return response()->json([
                'success' => true,
                'invoice_no' => $sale->invoice_no,
                'receipt_html' => $receiptHtml,
                'sale_id' => $sale->id,
                'print_url' => route('debit-sales.show', $sale->id) // Route for receipt
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
