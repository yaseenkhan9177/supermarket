<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Item;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Wallet;
use App\Models\SaleVersion;
use App\Services\FifoStockService;
use App\Services\InvoiceEditService;
use App\Services\TaxService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    protected TaxService $taxService;

    public function __construct(TaxService $taxService)
    {
        $this->taxService = $taxService;
    }

    public function index()
    {
        return $this->pos();
    }

    public function pos()
    {
        // Get all items for POS display with canonical on_hand and backward-compatible aliases
        $items = Item::select(
                'id',
                'description',
                'description as name',
                'code',
                'code as barcode',
                'sale_rate as price',
                'sale_rate as sale_price',
                'sale_rate as rate',
                'on_hand',
                'on_hand as stock',
                'on_hand as stock_qty',
                'tax_rate',
                'item_type',
                'item_type as category',
                'image_path'
            )
            ->get()
            ->map(function ($item) {
                $item->on_hand   = (float) ($item->on_hand ?? 0);
                $item->stock     = $item->on_hand;
                $item->stock_qty = $item->on_hand;
                $item->tax_rate  = $item->tax_rate !== null ? (float) $item->tax_rate : null;
                $item->item_type = $item->item_type ?? 'Inventory';
                return $item;
            });

        $taxSettings      = $this->taxService->getSettings();
        $wallets          = \App\Models\Wallet::all();
        $availableCharges = \App\Models\AdditionalCharge::where('is_enabled', true)->get();

        return view('sales.pos', compact('items', 'taxSettings', 'wallets', 'availableCharges'));
    }

    // 1️⃣ Step 1 & 2: Product Fetch & Form Logic
    // This API endpoint feeds your Alpine.js frontend search & Smart Product Search
    public function searchProducts(Request $request)
    {
        $query = $request->get('q', $request->get('query', $request->get('barcode', '')));

        $products = Item::where('description', 'LIKE', "%{$query}%")
            ->orWhere('code', 'LIKE', "%{$query}%")
            ->select(
                'id',
                'description',
                'description as name',
                'code',
                'code as barcode',
                'sale_rate',
                'sale_rate as sale_price',
                'sale_rate as price',
                'sale_rate as rate',
                'on_hand',
                'on_hand as stock_qty',
                'on_hand as stock',
                'tax_rate',
                'item_type',
                'item_type as category',
                'image_path'
            )
            ->limit(50)
            ->get()
            ->map(function ($item) {
                $item->on_hand     = (float) ($item->on_hand ?? 0);
                $item->stock_qty   = $item->on_hand;
                $item->stock       = $item->on_hand;
                $item->tax_rate    = $item->tax_rate !== null ? (float) $item->tax_rate : null;
                $item->item_type   = $item->item_type ?? 'Inventory';
                return $item;
            });

        return response()->json($products);
    }

    // 5️⃣ Step 5: Save Sale Transaction (The Heavy Lifting)
    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array|min:1',
            'amount_received' => 'required|numeric|min:0'
        ]);

        try {
            $fifo = new FifoStockService();

            return DB::transaction(function () use ($request, $fifo) {

                $returnAdj = (float) $request->input('return_adjustment', 0);
                $discount  = (float) $request->input('discount_total', 0);
                $paymentMode = $request->input('payment_mode', 'Cash');
                $walletId    = $request->input('wallet_id', $request->input('account_id'));

                // Placeholder totals — recalculated below
                $sale = Sale::create([
                    'invoice_no'        => 'INV-' . time(),
                    'user_id'           => auth()->id(),
                    'customer_id'       => $request->input('customer_id'),
                    'wallet_id'         => $walletId,
                    'subtotal'          => 0,
                    'discount_total'    => $discount,
                    'return_adjustment' => $returnAdj,
                    'grand_total'       => 0,
                    'paid_amount'       => $request->amount_received,
                    'change_amount'     => 0,
                    'payment_mode'      => $paymentMode,
                    'status'            => 'completed',
                    'sale_date'         => now(),
                ]);

                $calculatedSubtotal = 0;
                $calculatedTaxTotal = 0;

                // Save Items & Deduct Stock via FIFO
                foreach ($request->cart as $cartItem) {
                    $product = Item::lockForUpdate()->find($cartItem['id']);
                    if (!$product) {
                        continue;
                    }

                    $qty = (float) $cartItem['qty'];
                    $saleRate = (float) ($cartItem['price'] ?? $cartItem['rate'] ?? $product->sale_rate ?? $product->price);

                    // Use user-supplied per-item tax_rate if provided; otherwise fall back to product's stored rate.
                    $effectiveTaxRate = array_key_exists('tax_rate', $cartItem) && $cartItem['tax_rate'] !== null
                        ? (float) $cartItem['tax_rate']
                        : $product->tax_rate;

                    if ($product->item_type === 'Service') {
                        // Service items: no stock deduction
                        $lineTotal = round($qty * $saleRate, 2);
                        $lineTax   = $this->taxService->calculateLineTax($lineTotal, $effectiveTaxRate);

                        SaleItem::create([
                            'sale_id'   => $sale->id,
                            'item_id'   => $product->id,
                            'item_name' => $product->description,
                            'batch_id'  => null,
                            'qty'       => $qty,
                            'rate'      => $saleRate,
                            'total'     => $lineTotal,
                            'tax_rate'  => $lineTax['tax_rate'],
                            'tax_amount'=> $lineTax['tax_amount'],
                        ]);
                        $calculatedSubtotal += $lineTotal;
                        $calculatedTaxTotal += $lineTax['tax_amount'];
                    } else {
                        // Stock item: FIFO deduction — may span multiple batches
                        $result = $fifo->deductStock($product->id, $qty, $sale->id, auth()->id());

                        foreach ($result['batches_used'] as $batchUsed) {
                            $lineTotal = round($batchUsed['quantity_deducted'] * $batchUsed['sale_price'], 2);
                            $lineTax   = $this->taxService->calculateLineTax($lineTotal, $effectiveTaxRate);

                            SaleItem::create([
                                'sale_id'   => $sale->id,
                                'item_id'   => $product->id,
                                'item_name' => $product->description,
                                'batch_id'  => $batchUsed['batch_id'],
                                'qty'       => $batchUsed['quantity_deducted'],
                                'rate'      => $batchUsed['sale_price'],
                                'total'     => $lineTotal,
                                'tax_rate'  => $lineTax['tax_rate'],
                                'tax_amount'=> $lineTax['tax_amount'],
                            ]);
                            $calculatedSubtotal += $lineTotal;
                            $calculatedTaxTotal += $lineTax['tax_amount'];
                        }
                    }
                }

                // Process Additional Charges
                $additionalChargesTotal = 0;
                $chargeIds = $request->input('additional_charges', []);
                if (is_array($chargeIds)) {
                    foreach ($chargeIds as $chargeId) {
                        $charge = \App\Models\AdditionalCharge::find($chargeId);
                        if ($charge && $charge->is_enabled) {
                            $chargeAmount = $charge->type === 'percentage'
                                ? round($calculatedSubtotal * ($charge->value / 100), 2)
                                : round($charge->value, 2);

                            \App\Models\SaleAdditionalCharge::create([
                                'sale_id'              => $sale->id,
                                'additional_charge_id' => $charge->id,
                                'name'                 => $charge->name,
                                'type'                 => $charge->type,
                                'value'                => $charge->value,
                                'amount'               => $chargeAmount,
                            ]);
                            $additionalChargesTotal += $chargeAmount;
                        }
                    }
                }

                // Authoritative Grand Total calculation
                $effectiveTaxable = max(0.00, round($calculatedSubtotal - $discount, 2));
                $grandTotal       = max(0.00, round($effectiveTaxable + $calculatedTaxTotal + $additionalChargesTotal - $returnAdj, 2));

                $sale->update([
                    'subtotal'                 => $calculatedSubtotal,
                    'tax_rate'                 => $calculatedTaxTotal > 0 ? round(($calculatedTaxTotal / max(1, $effectiveTaxable)) * 100, 2) : 0,
                    'tax_total'                => $calculatedTaxTotal,
                    'additional_charges_total' => $additionalChargesTotal,
                    'grand_total'              => $grandTotal,
                    'change_amount'            => max(0, $request->amount_received - $grandTotal),
                ]);

                // Record Financial Transactions, Wallet Adjustment, Customer Receivable & GL Entries
                $accountingService = new \App\Services\AccountingService();
                $accountingService->recordSale($sale, $walletId, auth()->id());

                return response()->json([
                    'success'    => true,
                    'message'    => 'Sale Recorded!',
                    'invoice_no' => $sale->invoice_no,
                    'print_url'  => route('sales.print', $sale->id),
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // New Method: Print Receipt
    public function print($id)
    {
        $sale = Sale::with(['items', 'user'])->findOrFail($id);
        // Use a dedicated thermal-friendly view
        return view('sales.receipt', compact('sale'));
    }

    // Helper for Step 7
    private function postAccountingEntries($sale)
    {
        // Debit Cash
        GLEntry::create([
            'account_id' => 101, // Cash on Hand
            'debit' => $sale->grand_total,
            'credit' => 0,
            'description' => "Sale #{$sale->invoice_no}",
            'date' => now(),
        ]);

        // Credit Sales
        GLEntry::create([
            'account_id' => 401, // Sales Revenue
            'debit' => 0,
            'credit' => $sale->grand_total,
            'description' => "Revenue Sale #{$sale->invoice_no}",
            'date' => now(),
        ]);
    }

    public function apiCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        return response()->json([
            'id' => $customer->id,
            'address' => $customer->address,
            'phone' => $customer->phone,
            'credit_limit' => number_format($customer->credit_limit, 2, '.', ''),
            'balance' => number_format($customer->balance, 2, '.', ''),
        ]);
    }

    public function apiProduct(Request $request)
    {
        $barcode = $request->query('barcode');
        $item = Item::where('code', $barcode)->first();

        if ($item) {
            return response()->json([
                'id' => $item->id,
                'description' => $item->description,
                'sale_rate' => $item->sale_rate,
                'on_hand' => $item->on_hand,
            ]);
        }

        return response()->json(['error' => 'Product not found'], 404);
    }
    public function history(Request $request)
    {
        $statsQuery = Sale::query();
        if ($request->filled('from_date')) {
            $statsQuery->whereDate('sale_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $statsQuery->whereDate('sale_date', '<=', $request->to_date);
        }

        // 1. Calculate Stats for Cards (Filtered or Global Totals)
        $stats = [
            'all_count' => (clone $statsQuery)->count(),
            'all_total' => (clone $statsQuery)->sum('grand_total'),

            'cash_count' => (clone $statsQuery)->where('payment_mode', 'Cash')->count(),
            'cash_total' => (clone $statsQuery)->where('payment_mode', 'Cash')->sum('grand_total'),

            'debit_count' => (clone $statsQuery)->where('payment_mode', 'Debit')->count(),
            'debit_total' => (clone $statsQuery)->where('payment_mode', 'Debit')->sum('grand_total'),
        ];

        // 2. Filter Logic
        $query = Sale::with(['user', 'customer', 'refundItems'])->latest();

        if ($request->filled('from_date')) {
            $query->whereDate('sale_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('sale_date', '<=', $request->to_date);
        }
        if ($request->has('type') && $request->type != 'all') {
            $query->where('payment_mode', $request->type);
        }

        $sales = $query->paginate(15)->withQueryString();

        return view('sales.history', compact('sales', 'stats'));
    }

    public function todaysSales(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date'   => 'nullable|date_format:Y-m-d|after_or_equal:from_date',
        ], [
            'from_date.date_format'   => 'The From Date must be a valid date (YYYY-MM-DD).',
            'to_date.date_format'     => 'The To Date must be a valid date (YYYY-MM-DD).',
            'to_date.after_or_equal' => 'The From Date cannot be after the To Date.',
        ]);

        if ($validator->fails()) {
            return redirect()->route('sales.today')
                ->withErrors($validator)
                ->withInput();
        }

        $preset = $request->input('preset', 'today');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        if ($request->filled('from_date') && $request->filled('to_date')) {
            // Use explicitly provided dates
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
                case 'today':
                default:
                    $fromDate = today()->toDateString();
                    $toDate = today()->toDateString();
                    $preset = 'today';
                    break;
            }
        } else {
            // Default initial state: Today
            $fromDate = today()->toDateString();
            $toDate = today()->toDateString();
            $preset = 'today';
        }

        // Complete day boundaries to include all timestamps on start and end dates
        $start = \Carbon\Carbon::parse($fromDate)->startOfDay();
        $end = \Carbon\Carbon::parse($toDate)->endOfDay();

        $query = Sale::whereBetween('sale_date', [$start, $end]);

        // Calculate KPI totals at query level
        $totalTransactions = (clone $query)->count();
        $totalRevenue = (clone $query)->sum('grand_total');
        $cashTotal = (clone $query)->where('payment_mode', 'Cash')->sum('grand_total');
        $debitTotal = (clone $query)->where('payment_mode', 'Debit')->sum('grand_total');
        $totalItemsCount = SaleItem::whereIn('sale_id', (clone $query)->select('id'))->count();

        // Paginate results with query string preservation
        $todaySales = $query->with(['customer:id,name', 'user:id,name'])
            ->withCount('items')
            ->latest('sale_date')
            ->paginate(25)
            ->withQueryString();

        return view('sales.today', compact(
            'todaySales',
            'totalRevenue',
            'totalTransactions',
            'cashTotal',
            'debitTotal',
            'totalItemsCount',
            'fromDate',
            'toDate',
            'preset'
        ));
    }

    /**
     * Show interactive invoice editing screen.
     */
    public function edit($id)
    {
        $sale = Sale::with(['items.item', 'customer', 'wallet', 'user', 'versions.user'])->findOrFail($id);

        if ($sale->status === 'cancelled') {
            return redirect()->route('sales.today')
                ->with('error', "Invoice #{$sale->invoice_no} is cancelled and cannot be edited.");
        }

        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone', 'balance']);
        $wallets   = Wallet::where('is_active', true)->orderBy('name')->get();
        $users     = \App\Models\User::orderBy('name')->get(['id', 'name']);
        $taxSettings = $this->taxService->getSettings();

        $originalItems = $sale->items->map(function ($i) {
            return [
                'item_id'   => $i->item_id,
                'item_name' => $i->item_name,
                'item_code' => $i->item?->code,
                'item_type' => $i->item?->item_type ?? 'Inventory',
                'qty'       => (float) $i->qty,
                'rate'      => (float) $i->rate,
                'tax_rate'  => (float) ($i->tax_rate ?? 0),
            ];
        })->values()->toArray();

        $items = $sale->items->map(function ($i) {
            return [
                'uid'       => uniqid(),
                'item_id'   => $i->item_id,
                'item_name' => $i->item_name,
                'item_code' => $i->item?->code,
                'item_type' => $i->item?->item_type ?? 'Inventory',
                'batch_id'  => $i->batch_id,
                'qty'       => (float) $i->qty,
                'rate'      => (float) $i->rate,
                'tax_rate'  => (float) ($i->tax_rate ?? 0),
            ];
        })->values()->toArray();

        return view('sales.edit', compact('sale', 'customers', 'wallets', 'users', 'originalItems', 'items', 'taxSettings'));
    }

    /**
     * Process invoice update with stock delta synchronization and versioning.
     */
    public function update(Request $request, $id, InvoiceEditService $invoiceEditService)
    {
        if (Auth::guard('employee')->check()) {
            $authUser = Auth::guard('employee')->user();
            $userId   = $authUser->id;
            $userName = $authUser->full_name;
        } else {
            $authUser = Auth::user();
            $userId   = $authUser?->id ?? 1;
            $userName = $authUser?->name ?? 'Store Admin';
        }

        $reason = trim($request->input('reason', ''));
        if (empty($reason)) {
            $reason = 'Invoice modified by ' . $userName;
            $request->merge(['reason' => $reason]);
        }

        $request->validate([
            'customer_id'          => 'nullable|exists:customers,id',
            'payment_mode'         => 'required|in:Cash,Debit,Card,Online',
            'wallet_id'            => 'nullable|exists:wallets,id',
            'discount_total'       => 'nullable|numeric|min:0',
            'tax_total'            => 'nullable|numeric|min:0',
            'paid_amount'          => 'nullable|numeric|min:0',
            'reason'               => 'nullable|string|max:1000',
            'change_reason'        => 'nullable|string|max:1000',
            'original_updated_at'  => 'required|string',
            'items'                => 'required|array|min:1',
            'items.*.item_id'      => 'required|exists:items,id',
            'items.*.qty'          => 'required|numeric|min:0.01',
            'items.*.rate'         => 'required|numeric|min:0',
        ], [
            'items.required'       => 'The invoice must contain at least one item.',
            'items.*.qty.min'      => 'Item quantity must be greater than zero.',
        ]);

        try {
            $updatedSale = $invoiceEditService->updateInvoice(
                (int) $id,
                $request->all(),
                $userId,
                $userName,
                $request->input('change_reason') ?? $request->input('reason'),
                $request->ip(),
                $request->input('original_updated_at')
            );

            return redirect()->route('sales.versions', $updatedSale->id)
                ->with('success', "Invoice #{$updatedSale->invoice_no} updated successfully. Version recorded.");
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * View complete historical version timeline of an invoice.
     */
    public function historyVersions($id)
    {
        $sale = Sale::with(['versions.user', 'customer', 'wallet', 'user'])->findOrFail($id);
        $versions = $sale->versions;

        return view('sales.versions', compact('sale', 'versions'));
    }

    /**
     * View read-only snapshot of a specific historical version.
     */
    public function showVersion($id, $versionNumber)
    {
        $sale = Sale::findOrFail($id);
        $version = SaleVersion::where('sale_id', $sale->id)
            ->where('version_number', $versionNumber)
            ->firstOrFail();

        return view('sales.version_receipt', compact('sale', 'version'));
    }

    /**
     * Cancel an invoice safely, returning all items to stock.
     */
    public function cancelInvoice(Request $request, $id, InvoiceEditService $invoiceEditService)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ], [
            'reason.required' => 'Please provide a cancellation reason for auditing purposes.',
        ]);

        if (Auth::guard('employee')->check()) {
            $userId = Auth::guard('employee')->id();
        } else {
            $userId = Auth::id() ?? 1;
        }

        try {
            $sale = $invoiceEditService->cancelInvoice(
                (int) $id,
                $userId,
                $request->input('reason'),
                $request->ip()
            );

            return redirect()->route('sales.versions', $sale->id)
                ->with('success', "Invoice #{$sale->invoice_no} has been cancelled and its inventory restored to stock.");
        } catch (\Exception $e) {
            return back()->with('error', 'Cancellation error: ' . $e->getMessage());
        }
    }
}


