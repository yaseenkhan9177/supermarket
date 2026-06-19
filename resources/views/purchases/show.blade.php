@extends('layouts.admin')

@section('title', 'Purchase Bill Details')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-6">

    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 border-b border-gray-200 dark:border-slate-700 pb-5">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Purchase Bill Details
                </h1>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300">
                    {{ ucfirst($purchase->status ?? 'Received') }}
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $purchase->payment_type === 'Credit' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300' }}">
                    {{ $purchase->payment_type }}
                </span>
            </div>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">
                Bill #: <span class="font-mono font-bold text-gray-800 dark:text-slate-200">{{ $purchase->purchase_no }}</span> 
                @if($purchase->vendor_bill_no)
                    | Ref: <span class="font-mono">{{ $purchase->vendor_bill_no }}</span>
                @endif
                | Date: <span class="font-mono">{{ \Carbon\Carbon::parse($purchase->invoice_date)->format('d M Y') }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('reports.purchases') }}" class="bg-white text-black border border-gray-300 hover:bg-gray-100 font-bold px-4 py-2 rounded-lg inline-flex items-center gap-2 shadow-sm text-sm transition duration-150">
                <i class="fas fa-arrow-left"></i> Back to Purchases
            </a>
            <a href="{{ route('purchases.print', $purchase->id) }}" class="bg-white text-black border border-gray-300 hover:bg-gray-100 font-bold px-4 py-2 rounded-lg inline-flex items-center gap-2 shadow-sm text-sm transition duration-150">
                <i class="fas fa-print"></i> Print Bill
            </a>
        </div>
    </div>

    {{-- Grid Layout: Main info and Sidebar summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left 2 Columns: Supplier Info & Items list --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Supplier Card --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm p-5">
                <div class="flex items-center gap-2 mb-4 border-b border-gray-100 dark:border-slate-700 pb-3">
                    <i class="fas fa-user-tie text-indigo-600 dark:text-indigo-400 text-lg"></i>
                    <h3 class="font-bold text-gray-900 dark:text-white">Supplier Information</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="block text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wide">Company / Supplier</span>
                        <span class="font-bold text-gray-800 dark:text-slate-200 text-base block mt-0.5">
                            {{ $purchase->supplier->name ?? 'N/A' }}
                        </span>
                        @if($purchase->supplier->company_name)
                            <span class="text-xs text-gray-500 dark:text-slate-400">({{ $purchase->supplier->company_name }})</span>
                        @endif
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wide">Contact Details</span>
                        <span class="block text-gray-800 dark:text-slate-200 mt-0.5 font-semibold">
                            <i class="fas fa-phone-alt mr-1 text-gray-400"></i>{{ $purchase->supplier->phone ?? '—' }}
                        </span>
                        @if($purchase->supplier->email)
                            <span class="block text-gray-500 dark:text-slate-400 text-xs mt-0.5">
                                <i class="fas fa-envelope mr-1 text-gray-400"></i>{{ $purchase->supplier->email }}
                            </span>
                        @endif
                    </div>
                    @if($purchase->supplier->address)
                    <div class="md:col-span-2">
                        <span class="block text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wide">Address</span>
                        <span class="block text-gray-700 dark:text-slate-300 mt-0.5">
                            {{ $purchase->supplier->address }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Items List --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-4 bg-gray-50 dark:bg-slate-700/50 border-b border-gray-100 dark:border-slate-700">
                    <h3 class="font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-boxes text-indigo-600 dark:text-indigo-400"></i> Items Received
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-slate-700/30 border-b border-gray-200 dark:border-slate-700 text-gray-500 dark:text-slate-400 uppercase text-[10px] font-bold tracking-wider">
                                <th class="p-3 w-10 text-center">#</th>
                                <th class="p-3">Item Description</th>
                                <th class="p-3 text-center">Qty</th>
                                <th class="p-3 text-right">Cost Rate</th>
                                <th class="p-3 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @foreach($purchase->items as $idx => $item)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-700/20 transition duration-100">
                                <td class="p-3 text-center text-gray-400 font-mono text-xs">{{ $idx + 1 }}</td>
                                <td class="p-3">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $item->item->name ?? 'Unknown Item' }}</div>
                                    <div class="text-xs text-gray-400 font-mono mt-0.5">Code: {{ $item->item->code ?? '-' }}</div>
                                </td>
                                <td class="p-3 text-center font-bold font-mono text-gray-800 dark:text-slate-200">{{ $item->qty }}</td>
                                <td class="p-3 text-right font-mono text-gray-700 dark:text-slate-300">Rs. {{ number_format($item->cost_rate, 2) }}</td>
                                <td class="p-3 text-right font-mono font-bold text-gray-950 dark:text-white">Rs. {{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Import & Clearing Charges --}}
            @if($purchase->charges && $purchase->charges->count() > 0)
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-4 bg-amber-50/50 dark:bg-amber-950/10 border-b border-amber-100 dark:border-amber-900/30">
                    <h3 class="font-bold text-amber-900 dark:text-amber-400 flex items-center gap-2">
                        <i class="fas fa-file-invoice-dollar"></i> Import & Clearing Charges
                    </h3>
                </div>
                <div class="p-4">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @foreach($purchase->charges as $charge)
                            <tr>
                                <td class="py-2.5 text-gray-700 dark:text-slate-300">{{ $charge->taxChargeType->name ?? 'Unknown Charge' }}</td>
                                <td class="py-2.5 text-right font-mono font-bold text-gray-900 dark:text-white">Rs. {{ number_format($charge->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Payment Splits --}}
            @if($purchase->paymentSplits && $purchase->paymentSplits->count() > 0)
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
                <div class="p-4 bg-emerald-50/50 dark:bg-emerald-950/10 border-b border-emerald-100 dark:border-emerald-900/30">
                    <h3 class="font-bold text-emerald-950 dark:text-emerald-400 flex items-center gap-2">
                        <i class="fas fa-wallet"></i> Payment Allocations
                    </h3>
                </div>
                <div class="p-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-[10px] text-gray-400 uppercase font-bold tracking-wider border-b pb-2">
                                <th class="text-left pb-2">Source / Method</th>
                                <th class="text-left pb-2">Linked Account</th>
                                <th class="text-left pb-2">Reference</th>
                                <th class="text-right pb-2">Amount Paid</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @foreach($purchase->paymentSplits as $split)
                            <tr>
                                <td class="py-2.5 font-medium text-gray-900 dark:text-white">{{ $split->payment_method }}</td>
                                <td class="py-2.5 text-gray-600 dark:text-slate-300">{{ $split->account->name ?? '—' }}</td>
                                <td class="py-2.5 font-mono text-xs text-gray-500 dark:text-slate-400">{{ $split->reference_no ?? '—' }}</td>
                                <td class="py-2.5 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($split->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

        </div>

        {{-- Right 1 Column: Summary Card --}}
        <div class="space-y-6">

            {{-- Totals Summary --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm p-6 space-y-4">
                <h3 class="font-bold text-gray-950 dark:text-white border-b border-gray-100 dark:border-slate-700 pb-3 text-lg">
                    Bill Summary
                </h3>
                <div class="space-y-2.5 text-sm text-gray-600 dark:text-slate-400">
                    <div class="flex justify-between">
                        <span>Items Subtotal</span>
                        <span class="font-mono font-semibold text-gray-900 dark:text-slate-200">Rs. {{ number_format($purchase->gross_total, 2) }}</span>
                    </div>

                    @if($purchase->charges && $purchase->charges->sum('amount') > 0)
                    <div class="flex justify-between text-amber-700 dark:text-amber-400">
                        <span>Clearing Charges / Taxes</span>
                        <span class="font-mono font-semibold">Rs. {{ number_format($purchase->charges->sum('amount'), 2) }}</span>
                    </div>
                    @endif

                    @if($purchase->discount > 0)
                    <div class="flex justify-between text-emerald-600 dark:text-emerald-400">
                        <span>Discount</span>
                        <span class="font-mono font-semibold">-Rs. {{ number_format($purchase->discount, 2) }}</span>
                    </div>
                    @endif

                    <div class="h-px bg-gray-100 dark:bg-slate-700 my-2"></div>

                    <div class="flex justify-between text-gray-950 dark:text-white font-bold text-base">
                        <span>Grand Total</span>
                        <span class="font-mono text-indigo-600 dark:text-indigo-400">Rs. {{ number_format($purchase->net_total, 2) }}</span>
                    </div>

                    <div class="flex justify-between text-emerald-600 dark:text-emerald-400 font-semibold">
                        <span>Amount Paid</span>
                        <span class="font-mono">Rs. {{ number_format($purchase->total_paid, 2) }}</span>
                    </div>

                    @php
                        $balanceDue = max(0, $purchase->net_total - $purchase->total_paid);
                    @endphp
                    <div class="flex justify-between font-bold {{ $balanceDue > 0.5 ? 'text-red-600 dark:text-red-400' : 'text-gray-400' }}">
                        <span>Balance Due</span>
                        <span class="font-mono">Rs. {{ number_format($balanceDue, 2) }}</span>
                    </div>
                </div>

                @if($purchase->notes)
                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-slate-700 text-xs">
                    <span class="block font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-1">Remarks / Memo</span>
                    <p class="text-gray-700 dark:text-slate-300 italic bg-gray-50 dark:bg-slate-900/50 p-2.5 rounded-lg border border-gray-100 dark:border-slate-800">
                        {{ $purchase->notes }}
                    </p>
                </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
