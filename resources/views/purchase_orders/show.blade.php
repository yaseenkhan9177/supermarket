@extends('layouts.admin')

@section('title', "PO {$po->po_number}")

@section('content')
<div class="max-w-6xl mx-auto pb-16" x-data="{ showExpenseModal: false }">

    {{-- Header & Action Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-200 flex items-center gap-1">
                    <i class="fas fa-home text-xs"></i> Dashboard
                </a>
                <span>/</span>
                <a href="{{ route('purchase-orders.index') }}" class="hover:text-slate-200 flex items-center gap-1">
                    Purchase Orders
                </a>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">{{ $po->po_number }}</h1>
                @php
                    $badgeStyle = match($po->status) {
                        'draft' => 'bg-slate-100 text-slate-700 border-slate-200',
                        'sent' => 'bg-blue-100 text-blue-800 border-blue-200',
                        'partially_received' => 'bg-amber-100 text-amber-800 border-amber-200',
                        'received' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                        'cancelled' => 'bg-red-100 text-red-800 border-red-200',
                        default => 'bg-slate-100 text-slate-700'
                    };
                @endphp
                <span class="px-3 py-1 rounded-full text-xs font-bold border uppercase tracking-wider {{ $badgeStyle }}">
                    {{ str_replace('_', ' ', $po->status) }}
                </span>
            </div>
            <p class="text-slate-500 dark:text-slate-400 text-xs mt-1">
                Created by {{ $po->creator->name ?? 'System' }} on {{ $po->created_at->format('d M Y, h:i A') }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-transform hover:scale-105">
                <i class="fas fa-home"></i> Dashboard
            </a>

            @if($po->status === 'draft')
            <form method="POST" action="{{ route('purchase-orders.status', $po->id) }}">
                @csrf
                <input type="hidden" name="status" value="sent">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors">
                    <i class="fas fa-paper-plane"></i> Mark Sent to Supplier
                </button>
            </form>
            @endif

            @if(in_array($po->status, ['sent', 'partially_received']))
            <a href="{{ route('purchase-orders.receive', $po->id) }}" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 shadow-lg shadow-emerald-500/20 transition-transform hover:scale-105">
                <i class="fas fa-box-open"></i> Receive Stock
            </a>
            @endif

            <button @click="showExpenseModal = true" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors">
                <i class="fas fa-truck-loading"></i> Add Landed Expense
            </button>
        </div>
    </div>

    {{-- Supplier & Financial Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
        {{-- Supplier Info --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Supplier Details</span>
            <h3 class="text-lg font-bold text-slate-800 dark:text-white mt-1">{{ $po->supplier->name ?? 'N/A' }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">Code: {{ $po->supplier->code ?? 'N/A' }} | Phone: {{ $po->supplier->phone ?? 'N/A' }}</p>
            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 text-xs">
                <a href="{{ route('suppliers.show', $po->supplier_id) }}" class="text-indigo-600 dark:text-indigo-400 font-bold hover:underline">
                    View Supplier Ledger & Profile &rarr;
                </a>
            </div>
        </div>

        {{-- PO Subtotal & Expenses --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Financial Summary</span>
            <div class="mt-2 space-y-1.5 text-xs">
                <div class="flex justify-between">
                    <span class="text-slate-500">Items Subtotal:</span>
                    <span class="font-bold text-slate-800 dark:text-white">Rs. {{ number_format($po->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-amber-600 font-medium">Landed Expenses:</span>
                    <span class="font-bold text-amber-600">Rs. {{ number_format($totalExpenses, 2) }}</span>
                </div>
                <div class="flex justify-between pt-1.5 border-t border-slate-100 dark:border-slate-700 text-sm font-black">
                    <span class="text-slate-800 dark:text-white">Grand Total:</span>
                    <span class="text-indigo-600 dark:text-indigo-400">Rs. {{ number_format($po->grand_total, 2) }}</span>
                </div>
            </div>
        </div>

        {{-- Receiving Progress --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-5 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <span class="text-[11px] font-bold text-slate-400 uppercase">Receiving Status</span>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-1">{{ $po->receiving_progress }}% Completed</h3>
            <div class="w-full bg-slate-200 dark:bg-slate-700 h-2.5 rounded-full overflow-hidden mt-3">
                <div class="bg-indigo-600 h-full transition-all" style="width: {{ $po->receiving_progress }}%"></div>
            </div>
            <p class="text-xs text-slate-500 mt-2">
                {{ number_format($po->items->sum('quantity_received')) }} / {{ number_format($po->items->sum('quantity_ordered')) }} total units received
            </p>
        </div>
    </div>

    {{-- ORDERED ITEMS TABLE --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden mb-8">
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 dark:text-white text-base">Ordered Line Items</h3>
            <span class="text-xs text-slate-400">{{ $po->items->count() }} line items</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-400 uppercase">
                        <th class="py-3.5 px-5">Item / Product</th>
                        <th class="py-3.5 px-5 text-center">Ordered</th>
                        <th class="py-3.5 px-5 text-center">Received</th>
                        <th class="py-3.5 px-5 text-center">Pending</th>
                        <th class="py-3.5 px-5 text-right">PO Unit Cost</th>
                        <th class="py-3.5 px-5 text-right">Line Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-sm">
                    @foreach($po->items as $item)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="py-3.5 px-5">
                            <div class="font-bold text-slate-800 dark:text-white">{{ $item->item->description ?? $item->item->name ?? 'Unknown' }}</div>
                            <div class="text-[11px] text-slate-400">Code: {{ $item->item->code ?? '—' }}</div>
                        </td>
                        <td class="py-3.5 px-5 text-center font-semibold text-slate-700 dark:text-slate-300">
                            {{ number_format($item->quantity_ordered, 2) }}
                        </td>
                        <td class="py-3.5 px-5 text-center font-semibold text-emerald-600">
                            {{ number_format($item->quantity_received, 2) }}
                        </td>
                        <td class="py-3.5 px-5 text-center font-bold text-amber-600">
                            {{ number_format($item->pending_quantity, 2) }}
                        </td>
                        <td class="py-3.5 px-5 text-right font-semibold text-slate-800 dark:text-slate-200">
                            Rs. {{ number_format($item->unit_cost, 2) }}
                        </td>
                        <td class="py-3.5 px-5 text-right font-bold text-slate-900 dark:text-white">
                            Rs. {{ number_format($item->line_total, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- LANDED COST EXPENSES & WORKED EXAMPLE ALLOCATION MATH --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        {{-- Expenses List --}}
        <div class="lg:col-span-2 bg-white dark:bg-slate-800/90 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 dark:text-white text-base">Landed Expenses (Freight, Rent, Tax, Labor)</h3>
                <button @click="showExpenseModal = true" class="text-xs font-bold text-amber-600 hover:underline">+ Add Expense</button>
            </div>

            @if($po->expenses->isEmpty())
                <div class="p-8 text-center text-slate-400 text-xs">
                    No extra landed expenses added to this PO yet.
                </div>
            @else
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/40 text-slate-400 font-bold border-b border-slate-200 dark:border-slate-700 uppercase">
                            <th class="py-2.5 px-4">Type</th>
                            <th class="py-2.5 px-4">Description</th>
                            <th class="py-2.5 px-4">Added By</th>
                            <th class="py-2.5 px-4 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        @foreach($po->expenses as $exp)
                        <tr>
                            <td class="py-2.5 px-4 font-bold text-slate-800 dark:text-slate-200 uppercase">{{ $exp->expense_type }}</td>
                            <td class="py-2.5 px-4 text-slate-600 dark:text-slate-400">{{ $exp->description ?: '—' }}</td>
                            <td class="py-2.5 px-4 text-slate-500">{{ $exp->addedBy->name ?? 'Staff' }}</td>
                            <td class="py-2.5 px-4 text-right font-bold text-amber-600">Rs. {{ number_format($exp->amount, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Landed Cost Worked Example Formula Card --}}
        <div class="bg-gradient-to-br from-indigo-900 to-slate-900 text-white rounded-2xl p-5 shadow-xl flex flex-col justify-between">
            <div>
                <h4 class="font-bold text-xs uppercase tracking-wider text-indigo-300 mb-2">Landed Cost Allocation Formula</h4>
                <p class="text-xs leading-relaxed text-slate-300 mb-3">
                    Each item's true cost = PO Unit Cost + Proportional Expense Share.
                </p>

                <div class="bg-white/10 p-3 rounded-xl font-mono text-[11px] space-y-1 mb-3">
                    <div>Share = (Line Value / Subtotal) &times; Total Expenses</div>
                    <div>Landed Unit = Cost + (Share / Qty)</div>
                </div>

                <div class="text-[11px] text-slate-300 space-y-1">
                    <div>• <strong>Unallocated Expenses:</strong> Rs. {{ number_format($unallocatedExpenses, 2) }}</div>
                    <div>• <strong>Allocated in Past Receipts:</strong> Rs. {{ number_format($allocatedExpenses, 2) }}</div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-white/10 text-[10px] text-slate-400">
                <i class="fas fa-lock text-amber-400 mr-1"></i> Received batch costs are permanently locked upon receipt.
            </div>
        </div>
    </div>

    {{-- LANDED COST BREAKDOWN PER ITEM PREVIEW --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden mb-8">
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700">
            <h3 class="font-bold text-slate-800 dark:text-white text-base">Landed Cost Unit Preview (If Received Fully Now)</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-900/40 text-slate-400 font-bold border-b border-slate-200 dark:border-slate-700 uppercase">
                        <th class="py-3 px-5">Item</th>
                        <th class="py-3 px-5 text-right">Supplier Cost</th>
                        <th class="py-3 px-5 text-right">Value Share %</th>
                        <th class="py-3 px-5 text-right">Absorbed Expense Share</th>
                        <th class="py-3 px-5 text-right">Landed Cost Per Unit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                    @foreach($po->items as $item)
                    @php
                        $share = $landedBreakdown[$item->id]['expense_share'] ?? 0;
                        $landedUnit = $landedBreakdown[$item->id]['landed_unit_cost'] ?? $item->unit_cost;
                        $valPercent = ($po->subtotal > 0) ? ($item->line_total / $po->subtotal) * 100 : 0;
                    @endphp
                    <tr>
                        <td class="py-3 px-5 font-semibold text-slate-800 dark:text-white">{{ $item->item->description ?? $item->item->name ?? 'Item' }}</td>
                        <td class="py-3 px-5 text-right text-slate-600 dark:text-slate-300">Rs. {{ number_format($item->unit_cost, 2) }}</td>
                        <td class="py-3 px-5 text-right text-slate-500">{{ number_format($valPercent, 1) }}%</td>
                        <td class="py-3 px-5 text-right font-semibold text-amber-600">Rs. {{ number_format($share, 2) }}</td>
                        <td class="py-3 px-5 text-right font-black text-indigo-600 dark:text-indigo-400 text-sm">
                            Rs. {{ number_format($landedUnit, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- RECEIVING HISTORY --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700">
            <h3 class="font-bold text-slate-800 dark:text-white text-base">Receiving History Log</h3>
        </div>

        @if($po->receipts->isEmpty())
            <div class="p-8 text-center text-slate-400 text-xs">
                No stock has been received for this PO yet.
            </div>
        @else
            <div class="divide-y divide-slate-100 dark:divide-slate-700/60">
                @foreach($po->receipts as $receipt)
                <div class="p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <span class="font-mono font-bold text-indigo-600 text-sm">{{ $receipt->receipt_no }}</span>
                            <span class="text-xs text-slate-400 ml-2">Received on {{ $receipt->created_at->format('d M Y, h:i A') }} by {{ $receipt->receiver->name ?? 'Staff' }}</span>
                        </div>
                        <div class="text-xs font-bold text-slate-700 dark:text-slate-200">
                            Supplier Cost: Rs. {{ number_format($receipt->supplier_total_amount, 2) }} | Allocated Expense: Rs. {{ number_format($receipt->allocated_expense_amount, 2) }}
                        </div>
                    </div>

                    <table class="w-full text-left border-collapse text-xs bg-slate-50 dark:bg-slate-900/40 rounded-xl overflow-hidden">
                        <thead>
                            <tr class="text-slate-400 font-bold border-b border-slate-200 dark:border-slate-700">
                                <th class="p-2.5">Item Received</th>
                                <th class="p-2.5 text-center">Qty</th>
                                <th class="p-2.5 text-right">Supplier Unit Cost</th>
                                <th class="p-2.5 text-right">Landed Unit Cost</th>
                                <th class="p-2.5 text-right">Sale Price Set</th>
                                <th class="p-2.5">Batch Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receipt->items as $rItem)
                            <tr class="border-b border-slate-100 dark:border-slate-800">
                                <td class="p-2.5 font-semibold text-slate-800 dark:text-white">{{ $rItem->item->description ?? $rItem->item->name ?? 'Item' }}</td>
                                <td class="p-2.5 text-center font-bold text-emerald-600">{{ number_format($rItem->quantity_received, 2) }}</td>
                                <td class="p-2.5 text-right text-slate-600">Rs. {{ number_format($rItem->unit_supplier_cost, 2) }}</td>
                                <td class="p-2.5 text-right font-bold text-indigo-600">Rs. {{ number_format($rItem->unit_landed_cost, 2) }}</td>
                                <td class="p-2.5 text-right font-bold text-emerald-600">Rs. {{ number_format($rItem->sale_price_set, 2) }}</td>
                                <td class="p-2.5 font-mono text-[10px] text-slate-500">{{ $rItem->batch->batch_no ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ADD EXPENSE MODAL --}}
    <div x-show="showExpenseModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 max-w-md w-full shadow-2xl border border-slate-200 dark:border-slate-700">
            <h3 class="font-bold text-slate-800 dark:text-white text-lg mb-4">Add Landed Expense to PO</h3>
            <form method="POST" action="{{ route('purchase-orders.expenses', $po->id) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Expense Type</label>
                    <select name="expense_type" required class="w-full text-xs font-semibold p-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white">
                        <option value="freight">Freight / Transportation</option>
                        <option value="rent">Rent / Warehouse Share</option>
                        <option value="tax">Customs / Duty / Tax</option>
                        <option value="labor">Labor / Offloading</option>
                        <option value="other">Other Expense</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Amount (Rs.) <span class="text-red-500">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" required class="w-full text-sm font-bold p-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Description / Memo</label>
                    <input type="text" name="description" placeholder="Truck #, receipt reference..." class="w-full text-xs p-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white">
                </div>

                @if($po->status === 'received')
                <div class="p-3 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 text-xs rounded-xl border border-amber-200">
                    <i class="fas fa-info-circle mr-1"></i> Notice: Since this PO is already 100% received, this expense will be automatically logged as a direct Operating Expense in Bills/Expenses.
                </div>
                @endif

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showExpenseModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-xl">Cancel</button>
                    <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold rounded-xl shadow-md">Add Expense</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
