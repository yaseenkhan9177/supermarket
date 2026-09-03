<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Item;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\User;
use App\Services\FifoStockService;
use App\Services\TaxService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DebitSalesController extends Controller
{
    protected TaxService $taxService;

    public function __construct(TaxService $taxService)
    {
        $this->taxService = $taxService;
    }

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
        $taxSettings = $this->taxService->getSettings();

        return view('debit-sales.create', compact('customers', 'salesmen', 'nextInvoice', 'taxSettings'));
    }

    // 2. Search API (Same standardized payload as Cash Sales)
    public function searchItems(Request $request)
    {
        $query = $request->get('q', $request->get('query', ''));

        // Standardized payload: on_hand (canonical), stock_qty, stock, item_type, name, code, barcode, price, etc.
        $items = Item::where('description', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->select(
                'id',
                'description',
                'description as name',
                'code',
                'code as barcode',
                'sale_rate',
                'sale_rate as price',
                'sale_rate as sale_price',
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
                $item->on_hand   = (float) ($item->on_hand ?? 0);
                $item->stock_qty = $item->on_hand;
                $item->stock     = $item->on_hand;
                $item->tax_rate  = $item->tax_rate !== null ? (float) $item->tax_rate : null;
                $item->item_type = $item->item_type ?? 'Inventory';
                return $item;
            });

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
            'rows'        => 'required|array|min:1',
            'grand_total' => 'required|numeric',
        ]);

        try {
            $fifo = new FifoStockService();

            $sale = DB::transaction(function () use ($request, $fifo) {

                $paid = (float) ($request->received_amount ?? 0);

                // A. Create Invoice Header with placeholder totals (recalculated below)
                $sale = Sale::create([
                    'invoice_no'   => $request->invoice_no,
                    'customer_id'  => $request->customer_id,
                    'user_id'      => $request->salesman_id ?? Auth::id(),
                    'sale_date'    => $request->date ?? now(),
                    'subtotal'     => 0,
                    'grand_total'  => 0,
                    'paid_amount'  => $paid,
                    'change_amount'=> 0,
                    'payment_mode' => 'Debit',
                    'status'       => 'completed',
                    'note'         => $request->note ?? $request->invoice_note,
                ]);

                $calculatedSubtotal = 0;
                $calculatedTaxTotal = 0;

                // B. Save Items & Deduct Stock via FIFO
                foreach ($request->rows as $row) {
                    $item = Item::where('id', $row['id'])->lockForUpdate()->first();
                    if (!$item) {
                        continue;
                    }

                    $qty = (float) $row['qty'];
                    $itemNote = $row['note'] ?? null;

                    // Use user-supplied per-row tax_rate if present; fall back to product's stored rate.
                    $effectiveTaxRate = array_key_exists('tax_rate', $row) && $row['tax_rate'] !== null
                        ? (float) $row['tax_rate']
                        : ($item->tax_rate !== null ? (float) $item->tax_rate : null);

                    if ($item->item_type === 'Service') {
                        // Service items: no stock deduction
                        $lineTotal = round($qty * (float) $row['price'], 2);
                        $lineTax   = $this->taxService->calculateLineTax($lineTotal, $effectiveTaxRate);

                        SaleItem::create([
                            'sale_id'    => $sale->id,
                            'item_id'    => $item->id,
                            'item_name'  => $item->description,
                            'batch_id'   => null,
                            'qty'        => $qty,
                            'rate'       => (float) $row['price'],
                            'total'      => $lineTotal,
                            'tax_rate'   => $lineTax['tax_rate'],
                            'tax_amount' => $lineTax['tax_amount'],
                            'note'       => $itemNote,
                        ]);
                        $calculatedSubtotal += $lineTotal;
                        $calculatedTaxTotal += $lineTax['tax_amount'];
                    } else {
                        // Stock item: FIFO deduction — may span multiple batches
                        $result = $fifo->deductStock($item->id, $qty, $sale->id, Auth::id());

                        foreach ($result['batches_used'] as $batchUsed) {
                            $lineTotal = round($batchUsed['quantity_deducted'] * $batchUsed['sale_price'], 2);
                            $lineTax   = $this->taxService->calculateLineTax($lineTotal, $effectiveTaxRate);

                            SaleItem::create([
                                'sale_id'    => $sale->id,
                                'item_id'    => $item->id,
                                'item_name'  => $item->description,
                                'batch_id'   => $batchUsed['batch_id'],
                                'qty'        => $batchUsed['quantity_deducted'],
                                'rate'       => $batchUsed['sale_price'],
                                'total'      => $lineTotal,
                                'tax_rate'   => $lineTax['tax_rate'],
                                'tax_amount' => $lineTax['tax_amount'],
                                'note'       => $itemNote,
                            ]);
                            $calculatedSubtotal += $lineTotal;
                            $calculatedTaxTotal += $lineTax['tax_amount'];
                        }
                    }
                }

                // C. Authoritative backend Grand Total calculation using per-row accumulated tax
                $grandTotal = round($calculatedSubtotal + $calculatedTaxTotal, 2);

                $sale->update([
                    'subtotal'    => $calculatedSubtotal,
                    'tax_rate'    => $calculatedSubtotal > 0 ? round(($calculatedTaxTotal / $calculatedSubtotal) * 100, 2) : 0,
                    'tax_total'   => $calculatedTaxTotal,
                    'grand_total' => $grandTotal,
                ]);

                // D. Update customer balance (amount still owed) and record ledger entry
                $customer = Customer::lockForUpdate()->find($request->customer_id);
                if ($customer) {
                    $due = max(0, $grandTotal - $paid);
                    $customer->increment('balance', $due);

                    CustomerLedgerEntry::create([
                        'customer_id'   => $customer->id,
                        'type'          => 'sale',
                        'amount'        => $due,
                        'balance_after' => $customer->balance,
                        'method'        => 'Debit',
                        'note'          => 'Debit Sale Invoice #' . ($sale->invoice_no ?? $sale->id) . ($paid > 0 ? ' (Paid: Rs. ' . number_format($paid, 2) . ')' : ''),
                        'created_by'    => Auth::id(),
                    ]);
                }

                return $sale;
            });

            // E. Receipt
            $receiptHtml = view('sales.receipt', compact('sale'))->render();
            $receiptHtml = str_replace(['window.print()', 'window.close()'], '', $receiptHtml);

            return response()->json([
                'success'      => true,
                'invoice_no'   => $sale->invoice_no,
                'receipt_html' => $receiptHtml,
                'sale_id'      => $sale->id,
                'print_url'    => route('debit-sales.show', $sale->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
