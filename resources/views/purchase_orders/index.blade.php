@extends('layouts.admin')

@section('title', 'Purchase Orders')

@section('content')
<div class="max-w-7xl mx-auto pb-12">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="/dashboard" class="text-slate-400 hover:text-slate-200 transition-colors text-sm flex items-center gap-1.5">
                    <i class="fas fa-arrow-left text-xs"></i> Dashboard
                </a>
            </div>
            <h1 class="text-3xl font-bold text-slate-800 dark:text-white tracking-tight">Purchase Orders (PO)</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
                Manage stock purchases, landed cost tracking, and supplier receipts
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-sm rounded-xl flex items-center gap-2 transition-transform hover:scale-105">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="{{ route('purchase-orders.create') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-lg shadow-indigo-500/20 flex items-center gap-2 transition-transform hover:scale-105">
                <i class="fas fa-plus"></i> Create Purchase Order
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('purchase-orders.index') }}" class="bg-white dark:bg-slate-800/90 p-4 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700/60 mb-6 flex flex-wrap items-center gap-3">
        {{-- Status Filter --}}
        <div class="w-40">
            <select name="status" class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200">
                <option value="all">All Statuses</option>
                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                <option value="partially_received" {{ request('status') == 'partially_received' ? 'selected' : '' }}>Partially Received</option>
                <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        {{-- Supplier Filter --}}
        <div class="w-56">
            <select name="supplier_id" class="w-full text-xs font-semibold px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $sup)
                <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Search Input --}}
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search PO # or supplier name..." class="w-full text-xs font-medium px-3.5 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-colors">
            <i class="fas fa-filter mr-1"></i> Filter
        </button>

        @if(request()->hasAny(['status', 'supplier_id', 'search']))
        <a href="{{ route('purchase-orders.index') }}" class="px-3 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-xl text-xs font-semibold">Reset</a>
        @endif
    </form>

    {{-- PO Table --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700/60 overflow-hidden">
        @if($purchaseOrders->isEmpty())
            <div class="p-12 text-center text-slate-400 dark:text-slate-500">
                <i class="fas fa-file-invoice text-5xl mb-4 opacity-30 block"></i>
                <p class="text-base font-semibold">No purchase orders found matching your filters.</p>
                <a href="{{ route('purchase-orders.create') }}" class="mt-4 inline-block px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold">Create First PO</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                            <th class="py-3.5 px-5">PO Number</th>
                            <th class="py-3.5 px-5">Supplier</th>
                            <th class="py-3.5 px-5">Status</th>
                            <th class="py-3.5 px-5">Receiving Progress</th>
                            <th class="py-3.5 px-5 text-right">Items Subtotal</th>
                            <th class="py-3.5 px-5 text-right">Landed Expenses</th>
                            <th class="py-3.5 px-5 text-right">Grand Total</th>
                            <th class="py-3.5 px-5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-sm">
                        @foreach($purchaseOrders as $po)
                        <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="py-3.5 px-5 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                <a href="{{ route('purchase-orders.show', $po->id) }}" class="hover:underline">
                                    {{ $po->po_number }}
                                </a>
                                <div class="text-[11px] font-normal text-slate-400">{{ $po->created_at->format('d M Y') }}</div>
                            </td>
                            <td class="py-3.5 px-5">
                                <div class="font-bold text-slate-800 dark:text-white">{{ $po->supplier->name ?? 'Unknown' }}</div>
                                <div class="text-[11px] text-slate-400">{{ $po->supplier->code ?? '' }}</div>
                            </td>
                            <td class="py-3.5 px-5">
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
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border uppercase tracking-wider {{ $badgeStyle }}">
                                    {{ str_replace('_', ' ', $po->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-5">
                                <div class="w-32">
                                    <div class="flex justify-between text-[10px] font-bold text-slate-500 mb-1">
                                        <span>{{ $po->receiving_progress }}%</span>
                                        <span>{{ number_format($po->items->sum('quantity_received')) }} / {{ number_format($po->items->sum('quantity_ordered')) }}</span>
                                    </div>
                                    <div class="w-full bg-slate-200 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                                        <div class="bg-indigo-600 h-full transition-all" style="width: {{ $po->receiving_progress }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-5 text-right font-semibold text-slate-700 dark:text-slate-300">
                                Rs. {{ number_format($po->subtotal, 2) }}
                            </td>
                            <td class="py-3.5 px-5 text-right font-semibold text-amber-600">
                                Rs. {{ number_format($po->total_expenses, 2) }}
                            </td>
                            <td class="py-3.5 px-5 text-right font-black text-slate-900 dark:text-white">
                                Rs. {{ number_format($po->grand_total, 2) }}
                            </td>
                            <td class="py-3.5 px-5 text-center">
                                <a href="{{ route('purchase-orders.show', $po->id) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-bold transition-colors">
                                    View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-700/60">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
