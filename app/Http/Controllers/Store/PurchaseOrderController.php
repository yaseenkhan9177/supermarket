<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Batch;
use App\Models\Item;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderExpense;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderReceipt;
use App\Models\PurchaseOrderReceiptItem;
use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    /**
     * List all Purchase Orders.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'items', 'expenses'])->latest();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'LIKE', "%{$search}%")->orWhere('code', 'LIKE', "%{$search}%");
                  });
            });
        }

        $purchaseOrders = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchase_orders.index', compact('purchaseOrders', 'suppliers'));
    }

    /**
     * Show form to create a new PO.
     */
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $items = Item::orderBy('description')->select('id', 'description', 'code', 'cost_rate', 'sale_rate')->get();

        return view('purchase_orders.create', compact('suppliers', 'items'));
    }

    /**
     * Store a newly created PO.
     */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'    => 'required|exists:suppliers,id',
            'expected_date'  => 'nullable|date',
            'note'           => 'nullable|string',
            'status'         => 'required|in:draft,sent',
            'items'          => 'required|array|min:1',
            'items.*.item_id'=> 'required|exists:items,id',
            'items.*.qty'    => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            // Generate PO Number (PO-YYYYMMDD-XXXX)
            $datePrefix = date('Ymd');
            $lastPo = PurchaseOrder::where('po_number', 'LIKE', "PO-{$datePrefix}-%")->latest()->first();
            $nextSeq = $lastPo ? ((int) substr($lastPo->po_number, -4) + 1) : 1;
            $poNumber = 'PO-' . $datePrefix . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);

            $po = PurchaseOrder::create([
                'po_number'     => $poNumber,
                'supplier_id'   => $request->supplier_id,
                'status'        => $request->status,
                'expected_date' => $request->expected_date,
                'note'          => $request->note,
                'created_by'    => auth()->id(),
            ]);

            foreach ($request->items as $row) {
                $qty = (float) $row['qty'];
                $cost = (float) $row['unit_cost'];
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'item_id'           => $row['item_id'],
                    'quantity_ordered'  => $qty,
                    'unit_cost'         => $cost,
                    'line_total'        => $qty * $cost,
                ]);
            }

            AuditLog::record(
                'po_created',
                "Created Purchase Order {$po->po_number} with status '{$po->status}'",
                'PurchaseOrder',
                $po->id
            );
        });

        return redirect()->route('purchase-orders.index')->with('success', 'Purchase Order created successfully!');
    }

    /**
     * Show PO Detail & Landed Cost Breakdown.
     */
    public function show($id)
    {
        $po = PurchaseOrder::with([
            'supplier',
            'creator',
            'items.item',
            'expenses.addedBy',
            'receipts.receiver',
            'receipts.items.item',
            'receipts.items.batch'
        ])->findOrFail($id);

        // Landed Cost Calculation Preview
        $totalExpenses = $po->total_expenses;
        $allocatedExpenses = $po->receipts()->sum('allocated_expense_amount');
        $unallocatedExpenses = max(0, $totalExpenses - $allocatedExpenses);

        // Calculate landed cost breakdown per item for pending lines
        $landedBreakdown = [];
        $subtotal = $po->subtotal;

        foreach ($po->items as $poItem) {
            $share = ($subtotal > 0) ? ($poItem->line_total / $subtotal) * $totalExpenses : 0;
            $landedUnitCost = $poItem->unit_cost + ($poItem->quantity_ordered > 0 ? ($share / $poItem->quantity_ordered) : 0);
            $landedBreakdown[$poItem->id] = [
                'expense_share'    => $share,
                'landed_unit_cost' => $landedUnitCost,
            ];
        }

        return view('purchase_orders.show', compact('po', 'totalExpenses', 'allocatedExpenses', 'unallocatedExpenses', 'landedBreakdown'));
    }

    /**
     * Update PO Status (Draft -> Sent, Cancelled).
     */
    public function updateStatus(Request $request, $id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $request->validate([
            'status' => 'required|in:sent,cancelled',
        ]);

        $po->status = $request->status;
        $po->save();

        AuditLog::record(
            'po_status_updated',
            "Updated PO {$po->po_number} status to '{$po->status}'",
            'PurchaseOrder',
            $po->id
        );

        return back()->with('success', "PO status updated to {$po->status}!");
    }

    /**
     * Add Landed Cost Expense to PO (Freight, Tax, Rent, Labor).
     */
    public function addExpense(Request $request, $id)
    {
        $po = PurchaseOrder::findOrFail($id);
        $request->validate([
            'expense_type' => 'required|string|max:100',
            'amount'       => 'required|numeric|min:0.01',
            'description'  => 'nullable|string',
        ]);

        DB::transaction(function () use ($po, $request) {
            $paymentId = null;

            // SPECIAL RULE: If PO is 100% received already, auto-create a Payment (Operating Expense)
            if ($po->status === 'received') {
                $payment = Payment::create([
                    'payment_no'        => 'EXP-PO-' . date('Ymd') . '-' . rand(100, 999),
                    'payment_date'      => now()->toDateString(),
                    'paid_to_account'   => 'Landed Cost / Freight Expense',
                    'paid_from_account' => 'Cash Drawer',
                    'amount_paid'       => $request->amount,
                    'memo'              => "Post-Receipt Landed Expense for closed PO #{$po->po_number}: {$request->description}",
                    'user_id'           => auth()->id() ?? 1,
                ]);
                $paymentId = $payment->id;
            }

            $expense = PurchaseOrderExpense::create([
                'purchase_order_id' => $po->id,
                'expense_type'      => $request->expense_type,
                'amount'            => $request->amount,
                'description'       => $request->description,
                'added_by'          => auth()->id() ?? 1,
                'payment_id'        => $paymentId,
            ]);

            AuditLog::record(
                'po_expense_added',
                "Added {$request->expense_type} expense of Rs.{$request->amount} to PO {$po->po_number}" . ($paymentId ? " (auto-logged as Payment #{$paymentId})" : ""),
                'PurchaseOrder',
                $po->id
            );
        });

        return back()->with('success', 'Landed cost expense added successfully!');
    }

    /**
     * Show Receive Stock Page/Modal.
     */
    public function receiveForm($id)
    {
        $po = PurchaseOrder::with(['supplier', 'items.item', 'expenses', 'receipts'])->findOrFail($id);

        if ($po->status === 'received' || $po->status === 'cancelled') {
            return redirect()->route('purchase-orders.show', $po->id)->with('error', 'This PO cannot accept further receipts.');
        }

        // Compute unallocated expenses
        $totalExpenses = $po->total_expenses;
        $allocatedExpenses = $po->receipts()->sum('allocated_expense_amount');
        $unallocatedExpenses = max(0, $totalExpenses - $allocatedExpenses);

        return view('purchase_orders.receive', compact('po', 'totalExpenses', 'allocatedExpenses', 'unallocatedExpenses'));
    }

    /**
     * Process Receive Action (Create Batches + Supplier Balance + Supplier Ledger Entry + Stock Update).
     */
    public function processReceive(Request $request, $id)
    {
        $po = PurchaseOrder::with(['supplier', 'items.item', 'expenses', 'receipts'])->findOrFail($id);

        $request->validate([
            'items'                 => 'required|array',
            'items.*.qty_received'  => 'required|numeric|min:0',
            'items.*.sale_price'    => 'required|numeric|min:0',
            'note'                  => 'nullable|string',
        ]);

        // Filter lines where qty_received > 0
        $receiveLines = [];
        $totalEventSupplierValue = 0;
        $totalEventQty = 0;

        foreach ($request->items as $poItemId => $data) {
            $qty = (float) $data['qty_received'];
            if ($qty <= 0) continue;

            $poItem = $po->items->where('id', $poItemId)->first();
            if (!$poItem) continue;

            // Cap at remaining pending quantity
            $maxAllowed = $poItem->pending_quantity;
            if ($qty > $maxAllowed) {
                $qty = $maxAllowed;
            }

            $lineSupplierValue = $qty * $poItem->unit_cost;
            $totalEventSupplierValue += $lineSupplierValue;
            $totalEventQty += $qty;

            $receiveLines[] = [
                'po_item'    => $poItem,
                'qty'        => $qty,
                'sale_price' => (float) $data['sale_price'],
                'line_val'   => $lineSupplierValue,
            ];
        }

        if (empty($receiveLines)) {
            return back()->with('error', 'Please enter a valid receiving quantity (> 0) for at least one line item.');
        }

        // Unallocated Expenses Pool
        $totalPOExpenses = $po->total_expenses;
        $previouslyAllocated = $po->receipts()->sum('allocated_expense_amount');
        $unallocatedExpenses = max(0, $totalPOExpenses - $previouslyAllocated);

        // Calculate total remaining unreceived PO value before this event
        $remainingUnreceivedPOValue = $po->items->sum(function ($item) {
            return $item->pending_quantity * $item->unit_cost;
        });

        // Zero-guard: if event value or remaining value is 0, skip expense allocation
        $eventAllocatedExpense = ($totalEventSupplierValue > 0 && $remainingUnreceivedPOValue > 0)
            ? min($unallocatedExpenses, round($unallocatedExpenses * ($totalEventSupplierValue / $remainingUnreceivedPOValue), 2))
            : 0;

        DB::transaction(function () use ($po, $receiveLines, $totalEventSupplierValue, $eventAllocatedExpense, $request) {
            $datePrefix = date('Ymd');
            $receiptNo = 'POR-' . $datePrefix . '-' . rand(1000, 9999);

            $supplierTotal = 0;
            $landedTotal = 0;

            // 1. Create Receipt Header
            $receipt = PurchaseOrderReceipt::create([
                'purchase_order_id'        => $po->id,
                'receipt_no'               => $receiptNo,
                'allocated_expense_amount' => $eventAllocatedExpense,
                'supplier_total_amount'    => 0,
                'landed_total_amount'      => 0,
                'note'                     => $request->note,
                'received_by'              => auth()->id() ?? 1,
            ]);

            foreach ($receiveLines as $line) {
                $poItem = $line['po_item'];
                $item = $poItem->item;
                $qty = $line['qty'];
                $unitSupplierCost = $poItem->unit_cost;

                // Landed Cost Allocation formula
                $lineExpenseShare = ($totalEventSupplierValue > 0)
                    ? ($line['line_val'] / $totalEventSupplierValue) * $eventAllocatedExpense
                    : 0;

                $unitLandedCost = $unitSupplierCost + ($qty > 0 ? ($lineExpenseShare / $qty) : 0);

                // 2. Create Batch
                $batchNo = 'PO-' . $po->po_number . '-' . date('ymd-His') . '-' . $item->id;
                $batch = Batch::create([
                    'item_id'            => $item->id,
                    'batch_no'           => $batchNo,
                    'quantity_available' => $qty,
                    'cost_price'         => $unitLandedCost,
                    'sale_price'         => $line['sale_price'],
                    'received_at'        => now(),
                ]);

                // 3. Update Item Stock on_hand
                $item->increment('on_hand', $qty);

                // 4. Record Receipt Item
                PurchaseOrderReceiptItem::create([
                    'receipt_id'         => $receipt->id,
                    'po_item_id'         => $poItem->id,
                    'item_id'            => $item->id,
                    'batch_id'           => $batch->id,
                    'quantity_received'  => $qty,
                    'unit_supplier_cost' => $unitSupplierCost,
                    'unit_landed_cost'   => $unitLandedCost,
                    'sale_price_set'     => $line['sale_price'],
                ]);

                // Update PO Item quantity_received
                $poItem->increment('quantity_received', $qty);

                $lineSupplierCost = $qty * $unitSupplierCost;
                $lineLandedCost = $qty * $unitLandedCost;

                $supplierTotal += $lineSupplierCost;
                $landedTotal += $lineLandedCost;

                // 5. Update Supplier Balance (Supplier unit cost ONLY, excludes landed expenses)
                $supplier = $po->supplier;
                $supplier->increment('current_balance', $lineSupplierCost);

                // 6. Create Supplier Ledger Entry
                SupplierLedgerEntry::create([
                    'supplier_id'   => $supplier->id,
                    'type'          => 'purchase',
                    'amount'        => $lineSupplierCost,
                    'balance_after' => $supplier->current_balance,
                    'method'        => 'PO System',
                    'note'          => "PO #{$po->po_number} - Received {$item->name} x {$qty} @ Rs.{$unitSupplierCost}",
                    'created_by'    => auth()->id(),
                ]);
            }

            // Update Receipt Totals
            $receipt->update([
                'supplier_total_amount' => $supplierTotal,
                'landed_total_amount'   => $landedTotal,
            ]);

            // 7. Update PO Status
            $po->refresh();
            $allReceived = true;
            foreach ($po->items as $checkItem) {
                if ($checkItem->pending_quantity > 0) {
                    $allReceived = false;
                    break;
                }
            }

            $po->status = $allReceived ? 'received' : 'partially_received';
            $po->save();

            AuditLog::record(
                'po_received',
                "Processed stock receipt {$receipt->receipt_no} for PO {$po->po_number} (Supplier Cost: Rs.{$supplierTotal}, Landed Cost: Rs.{$landedTotal})",
                'PurchaseOrder',
                $po->id
            );
        });

        return redirect()->route('purchase-orders.show', $po->id)->with('success', 'Stock received and inventory batches created successfully!');
    }
}
