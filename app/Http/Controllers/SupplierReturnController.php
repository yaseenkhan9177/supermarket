<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\SupplierReturn;
use App\Models\SupplierReturnItem;
use App\Models\SupplierLedger;
use App\Models\Account;
use App\Models\Item;
use App\Models\Batch;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SupplierReturnController extends Controller
{
    /**
     * Expiry Dashboard — shows batches expiring within 3 days.
     * This is the landing page where the user sees what needs to go back.
     */
    public function index(Request $request)
    {
        $days    = $request->input('days', 3); // configurable window, default = 3 days
        $cutoff  = Carbon::now()->addDays((int) $days)->endOfDay();
        $now     = Carbon::now()->startOfDay();

        // Get batches expiring within the window (exclude already-zero quantity)
        $expiringBatches = Batch::with('item')
            ->where('quantity_available', '>', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '>=', $now)
            ->where('expires_at', '<=', $cutoff)
            ->orderBy('expires_at', 'asc')
            ->get();

        // Also get already expired (quantity still in stock)
        $expiredBatches = Batch::with('item')
            ->where('quantity_available', '>', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->orderBy('expires_at', 'asc')
            ->get();

        $recentReturns = SupplierReturn::with('supplier')
            ->latest()
            ->take(5)
            ->get();

        return view('supplier_returns.index', compact(
            'expiringBatches', 'expiredBatches', 'recentReturns', 'days'
        ));
    }

    /**
     * Show the form to initiate a return for specific batches.
     * Batch IDs are passed as a comma-separated query param or POST body.
     */
    public function create(Request $request)
    {
        $batchIds = array_filter(explode(',', $request->input('batches', '')));

        if (empty($batchIds)) {
            return redirect()->route('supplier-returns.index')
                ->with('error', 'Please select at least one batch to return.');
        }

        $batches   = Batch::with('item')->whereIn('id', $batchIds)->get();
        $suppliers = Supplier::orderBy('name')->get();
        $accounts  = Account::where('type', 'Asset')->orderBy('name')->get();

        return view('supplier_returns.create', compact('batches', 'suppliers', 'accounts'));
    }

    /**
     * Process the return.
     *
     * Scenario A — cash_refund:   Increment account balance, debit supplier ledger
     * Scenario B — store_credit:  Decrement supplier.current_balance (goes/stays negative)
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'return_date' => 'required|date',
            'resolution'  => 'required|in:cash_refund,store_credit',
            'account_id'  => 'required_if:resolution,cash_refund|nullable|exists:accounts,id',
            'items'       => 'required|array|min:1',
            'items.*.batch_id'      => 'required|exists:batches,id',
            'items.*.item_id'       => 'required|exists:items,id',
            'items.*.qty_returned'  => 'required|integer|min:1',
            'items.*.cost_rate'     => 'required|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request) {

                $totalValue  = 0;
                $returnLines = [];

                // ── 1. Validate quantities & build line data ──────────────────────
                foreach ($request->items as $line) {
                    $batch = Batch::findOrFail($line['batch_id']);

                    if ($line['qty_returned'] > $batch->quantity_available) {
                        $itemName = $batch->item->description ?? 'item';
                        throw new \Exception(
                            "Cannot return {$line['qty_returned']} units of '{$itemName}'. " .
                            "Only {$batch->quantity_available} available in this batch."
                        );
                    }

                    $lineTotal    = $line['qty_returned'] * $line['cost_rate'];
                    $totalValue  += $lineTotal;

                    $returnLines[] = [
                        'batch'        => $batch,
                        'line'         => $line,
                        'lineTotal'    => $lineTotal,
                    ];
                }

                // ── 2. Create Return Header ───────────────────────────────────────
                $returnNo = 'SR-' . date('Y') . '-' . str_pad(
                    (SupplierReturn::whereYear('created_at', date('Y'))->count() + 1),
                    4, '0', STR_PAD_LEFT
                );

                $supplierReturn = SupplierReturn::create([
                    'return_no'   => $returnNo,
                    'supplier_id' => $request->supplier_id,
                    'return_date' => $request->return_date,
                    'total_value' => $totalValue,
                    'resolution'  => $request->resolution,
                    'account_id'  => ($request->resolution === 'cash_refund') ? $request->account_id : null,
                    'notes'       => $request->notes,
                    'user_id'     => auth()->id(),
                ]);

                // ── 3. Save Line Items & Decrement Stock ──────────────────────────
                foreach ($returnLines as $data) {
                    ['batch' => $batch, 'line' => $line, 'lineTotal' => $lineTotal] = $data;

                    SupplierReturnItem::create([
                        'supplier_return_id' => $supplierReturn->id,
                        'item_id'            => $line['item_id'],
                        'batch_id'           => $batch->id,
                        'batch_no'           => $batch->batch_no,
                        'expiry_date'        => $batch->expires_at?->toDateString(),
                        'qty_returned'       => $line['qty_returned'],
                        'cost_rate'          => $line['cost_rate'],
                        'total'              => $lineTotal,
                    ]);

                    // Decrement batch quantity
                    $batch->decrement('quantity_available', $line['qty_returned']);

                    // Decrement item on_hand
                    $item = Item::find($line['item_id']);
                    if ($item) {
                        $item->decrement('on_hand', $line['qty_returned']);
                    }
                }

                // ── 4. Apply Financial Resolution ─────────────────────────────────
                $supplier = Supplier::findOrFail($request->supplier_id);

                if ($request->resolution === 'cash_refund') {
                    // Scenario A: Supplier pays cash → credit the designated account
                    $account = Account::findOrFail($request->account_id);
                    $account->increment('current_balance', $totalValue);

                    SupplierLedger::create([
                        'supplier_id'    => $supplier->id,
                        'date'           => $request->return_date,
                        'reference_type' => 'ReturnCash',
                        'reference_id'   => $supplierReturn->id,
                        'description'    => 'Cash Refund for Return #' . $returnNo . ' → Account: ' . $account->name,
                        'debit'          => $totalValue,
                        'credit'         => 0,
                        'balance'        => $supplier->fresh()->current_balance,
                    ]);

                } else {
                    // Scenario B: Store Credit → reduce supplier balance (into negative)
                    $supplier->decrement('current_balance', $totalValue);

                    SupplierLedger::create([
                        'supplier_id'    => $supplier->id,
                        'date'           => $request->return_date,
                        'reference_type' => 'ReturnCredit',
                        'reference_id'   => $supplierReturn->id,
                        'description'    => 'Store Credit for Return #' . $returnNo .
                                           ' (Rs. ' . number_format($totalValue, 2) . ' to be applied on next bill)',
                        'debit'          => $totalValue,
                        'credit'         => 0,
                        'balance'        => $supplier->fresh()->current_balance,
                    ]);
                }
            });

            return redirect()->route('supplier-returns.index')
                ->with('success', 'Return processed successfully! Stock and ledger have been updated.');

        } catch (\Exception $e) {
            \Log::error('Supplier Return Error: ' . $e->getMessage());
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }
}
