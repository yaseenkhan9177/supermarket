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

class CashSalesController extends Controller
{
    protected TaxService $taxService;

    public function __construct(TaxService $taxService)
    {
        $this->taxService = $taxService;
    }

    // 1. Show the Form
    public function create()
    {
        $customers = Customer::all();
        $salesmen = User::all();

        // Generate Invoice No (e.g., CS-2026-0001)
        $nextInvoice = 'CS-' . date('Y') . '-' . str_pad(Sale::count() + 1, 4, '0', STR_PAD_LEFT);

        $wallets = \App\Models\Wallet::where('is_active', true)->get();
        $activeWallet = \App\Models\Wallet::where('is_active', true)->first();
        $defaultWalletId = $activeWallet ? $activeWallet->id : null;
        $activeWalletName = $activeWallet ? $activeWallet->name : 'Shop Counter';
        $taxSettings = $this->taxService->getSettings();

        return view('cash-sales.create', compact('customers', 'salesmen', 'nextInvoice', 'activeWalletName', 'wallets', 'defaultWalletId', 'taxSettings'));
    }

    // 2. Search Items API
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

    // 3. Store Sale (The Main Engine)
    public function store(Request $request)
    {
        $request->validate([
            'rows'        => 'required|array|min:1',
            'grand_total' => 'required|numeric',
            'wallet_id'   => 'required|exists:wallets,id',
        ]);

        try {
            $fifo = new FifoStockService();

            $sale = DB::transaction(function () use ($request, $fifo) {
                $returnAdj = $request->input('return_adjustment', 0);

                // A. Create Invoice Header with placeholder totals (recalculated below)
                $sale = Sale::create([
                    'invoice_no'        => $request->invoice_no,
                    'customer_id'       => $request->customer_id,
                    'wallet_id'         => $request->wallet_id,
                    'user_id'           => $request->salesman_id ?? Auth::id(),
                    'sale_date'         => $request->date ?? now(),
                    'subtotal'          => 0,
                    'return_adjustment' => $returnAdj,
                    'grand_total'       => 0,
                    'paid_amount'       => $request->received_amount ?? 0,
                    'change_amount'     => 0,
                    'payment_mode'      => 'Cash',
                    'status'            => 'completed',
                    'note'              => $request->note ?? $request->invoice_note,
                ]);

                $calculatedSubtotal = 0;

                // B. Save Items & Deduct Stock via FIFO
                foreach ($request->rows as $row) {
                    $item = Item::where('id', $row['id'])->lockForUpdate()->first();
                    if (!$item) {
                        continue;
                    }

                    $qty = (float) $row['qty'];
                    $itemNote = $row['note'] ?? null;

                    if ($item->item_type === 'Service') {
                        // Service items: no stock, use the rate the cashier entered
                        $lineTotal = $qty * $row['price'];
                        SaleItem::create([
                            'sale_id'   => $sale->id,
                            'item_id'   => $item->id,
                            'item_name' => $item->description,
                            'batch_id'  => null,
                            'qty'       => $qty,
                            'rate'      => $row['price'],
                            'total'     => $lineTotal,
                            'note'      => $itemNote,
                        ]);
                        $calculatedSubtotal += $lineTotal;
                    } else {
                        // Stock item: FIFO deduction — may span multiple batches
                        $result = $fifo->deductStock($item->id, $qty, $sale->id, Auth::id());

                        foreach ($result['batches_used'] as $batchUsed) {
                            $lineTotal = $batchUsed['quantity_deducted'] * $batchUsed['sale_price'];
                            SaleItem::create([
                                'sale_id'   => $sale->id,
                                'item_id'   => $item->id,
                                'item_name' => $item->description,
                                'batch_id'  => $batchUsed['batch_id'],
                                'qty'       => $batchUsed['quantity_deducted'],
                                'rate'      => $batchUsed['sale_price'],
                                'total'     => $lineTotal,
                                'note'      => $itemNote,
                            ]);
                            $calculatedSubtotal += $lineTotal;
                        }
                    }
                }

                // C. Authoritative backend Tax & Grand Total calculation
                $taxResult = $this->taxService->calculate($calculatedSubtotal, 0, $returnAdj);
                $grandTotal = $taxResult['grand_total'];
                $taxTotal   = $taxResult['tax_amount'];
                $taxRate    = $taxResult['tax_rate'];

                $sale->update([
                    'subtotal'      => $calculatedSubtotal,
                    'tax_rate'      => $taxRate,
                    'tax_total'     => $taxTotal,
                    'grand_total'   => $grandTotal,
                    'change_amount' => ($request->received_amount ?? 0) - $grandTotal,
                ]);

                // D. Adjust Active Wallet Balance
                $wallet = \App\Models\Wallet::findOrFail($request->wallet_id);
                $wallet->adjustBalance($grandTotal);

                // E. Write Customer Ledger Entry (only if a real customer is attached)
                //    Cash sales are paid immediately — do NOT increment customer balance.
                //    This entry records the purchase in their history without creating debt.
                if (!empty($sale->customer_id)) {
                    $customer = Customer::lockForUpdate()->find($sale->customer_id);
                    if ($customer) {
                        CustomerLedgerEntry::create([
                            'customer_id'   => $customer->id,
                            'type'          => 'sale',
                            'amount'        => $grandTotal,
                            'balance_after' => $customer->balance, // unchanged — cash paid in full
                            'method'        => 'Cash',
                            'note'          => 'Cash Sale Invoice #' . ($sale->invoice_no ?? $sale->id),
                            'created_by'    => Auth::id(),
                        ]);
                    }
                }

                return $sale;
            });

            // E. Generate Receipt View
            $receiptHtml = view('sales.receipt', compact('sale'))->render();
            $receiptHtml = str_replace(['window.print()', 'window.close()'], '', $receiptHtml);

            // F. Send Response
            return response()->json([
                'success'     => true,
                'invoice_no'  => $sale->invoice_no,
                'receipt_html'=> $receiptHtml,
                'sale_id'     => $sale->id,
                'print_url'   => route('cash-sales.show', $sale->id),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
