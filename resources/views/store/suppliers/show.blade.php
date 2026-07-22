@extends('layouts.admin')

@section('title', 'Supplier Profile - ' . $supplier->name)

@section('content')
<div x-data="{
    activeTab: 'ledger',
    showPayModal: false,
    showAdjustModal: false,
    showReverseModal: false,
    reverseEntryId: null,
    
    // Pay Supplier form
    payForm: {
        amount: '',
        method: 'cash',
        date: '{{ date('Y-m-d') }}',
        note: ''
    },
    
    // Adjust Balance form
    adjustForm: {
        action: 'add_payable',
        amount: '',
        note: ''
    },
    
    // Reverse form
    reverseForm: {
        note: ''
    },

    openReverseModal(id) {
        this.reverseEntryId = id;
        this.reverseForm.note = '';
        this.showReverseModal = true;
    },

    async submitPaySupplier() {
        if (!this.payForm.amount || parseFloat(this.payForm.amount) <= 0) {
            alert('Please enter a valid payment amount.');
            return;
        }

        try {
            const res = await fetch('{{ route('suppliers.pay', $supplier->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.payForm)
            });

            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error recording payment.');
            }
        } catch (e) {
            alert('Server error processing payment.');
        }
    },

    async submitAdjustBalance() {
        if (!this.adjustForm.amount || parseFloat(this.adjustForm.amount) < 0) {
            alert('Please enter a valid amount.');
            return;
        }
        if (!this.adjustForm.note || this.adjustForm.note.length < 10) {
            alert('Reason / Note is required and must be at least 10 characters long.');
            return;
        }

        try {
            const res = await fetch('{{ route('suppliers.adjust-balance', $supplier->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.adjustForm)
            });

            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error adjusting balance.');
            }
        } catch (e) {
            alert('Server error adjusting balance.');
        }
    },

    async submitReverseEntry() {
        if (!this.reverseForm.note || this.reverseForm.note.length < 3) {
            alert('Please enter a note explaining the reversal.');
            return;
        }

        try {
            const res = await fetch(`/suppliers/{{ $supplier->id }}/ledger/${this.reverseEntryId}/reverse`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.reverseForm)
            });

            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Error reversing entry.');
            }
        } catch (e) {
            alert('Server error reversing entry.');
        }
    }
}" class="space-y-6">

    {{-- ── HEADER & BREADCRUMB ─────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 mb-1">
                <a href="{{ route('suppliers.index') }}" class="hover:text-slate-600 dark:hover:text-slate-200">Suppliers</a>
                <i class="fas fa-chevron-right text-[10px]"></i>
                <span class="text-slate-600 dark:text-slate-300">Supplier Profile</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                    {{ $supplier->name }}
                </h1>
                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full font-mono bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    {{ $supplier->code ?: 'SUP-' . str_pad($supplier->id, 4, '0', STR_PAD_LEFT) }}
                </span>
            </div>
            @if($supplier->company_name)
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    <i class="fas fa-building mr-1"></i> {{ $supplier->company_name }}
                </p>
            @endif
        </div>

        {{-- Action Buttons (Admin Only) --}}
        @if($isAdmin)
        <div class="flex items-center gap-3 flex-wrap">
            <button @click="showAdjustModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl shadow-sm transition">
                <i class="fas fa-sliders"></i> Adjust Balance
            </button>
            <button @click="showPayModal = true" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg transition">
                <i class="fas fa-hand-holding-dollar"></i> Pay Supplier
            </button>
        </div>
        @endif
    </div>

    {{-- ── TOP KPI BANNER CARDS ───────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        {{-- PAYABLE BALANCE CARD --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm relative overflow-hidden">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Outstanding Payable
            </span>
            <div class="mt-2 flex items-baseline gap-2">
                <span class="text-2xl md:text-3xl font-black font-mono tracking-tight {{ $payableBalance > 0 ? 'text-red-600 dark:text-red-400' : ($payableBalance < 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-800 dark:text-white') }}">
                    Rs. {{ number_format(abs($payableBalance), 2) }}
                </span>
            </div>
            <p class="text-[11px] font-semibold mt-1.5 {{ $payableBalance > 0 ? 'text-red-500' : ($payableBalance < 0 ? 'text-emerald-500' : 'text-slate-400') }}">
                @if($payableBalance > 0)
                    <i class="fas fa-triangle-exclamation mr-1"></i> Store Owes Supplier (Debt)
                @elseif($payableBalance < 0)
                    <i class="fas fa-circle-check mr-1"></i> Advance / Credit with Supplier
                @else
                    <i class="fas fa-check-double mr-1"></i> Account Settled
                @endif
            </p>
        </div>

        {{-- TOTAL PURCHASES CARD --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Total Purchases
            </span>
            <div class="mt-2">
                <span class="text-2xl font-black font-mono text-slate-800 dark:text-white">
                    Rs. {{ number_format($totalPurchasesAmount, 2) }}
                </span>
            </div>
            <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-1.5">
                {{ $totalPurchasesCount }} Total Purchase Bills
            </p>
        </div>

        {{-- TOTAL PAID CARD --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Total Paid To Supplier
            </span>
            <div class="mt-2">
                <span class="text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400">
                    Rs. {{ number_format($totalPaidAmount, 2) }}
                </span>
            </div>
            <p class="text-[11px] font-medium text-slate-500 dark:text-slate-400 mt-1.5">
                Lifetime Outgoing Payments
            </p>
        </div>

        {{-- CONTACT & INFO CARD --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm space-y-1">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                Contact Info
            </span>
            <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 mt-1">
                <i class="fas fa-phone text-slate-400 w-4"></i> {{ $supplier->phone ?: 'No phone' }}
            </div>
            <div class="text-xs font-semibold text-slate-700 dark:text-slate-300 truncate">
                <i class="fas fa-location-dot text-slate-400 w-4"></i> {{ $supplier->address ?: 'No address' }}
            </div>
            <div class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                <i class="fas fa-tag text-slate-400 w-4"></i> {{ $supplier->category->name ?? 'General Supplier' }}
            </div>
        </div>

    </div>

    {{-- ── TAB NAVIGATION ─────────────────────────────────────────────── --}}
    <div class="border-b border-slate-200 dark:border-slate-800 flex items-center gap-4 overflow-x-auto">
        <button @click="activeTab = 'ledger'"
                :class="activeTab === 'ledger' ? 'border-purple-600 text-purple-600 dark:text-purple-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-medium'"
                class="py-3 px-1 border-b-2 text-sm whitespace-nowrap transition">
            <i class="fas fa-book-open mr-1"></i> Supplier Ledger (Audit Trail)
        </button>
        <button @click="activeTab = 'purchases'"
                :class="activeTab === 'purchases' ? 'border-purple-600 text-purple-600 dark:text-purple-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-medium'"
                class="py-3 px-1 border-b-2 text-sm whitespace-nowrap transition">
            <i class="fas fa-boxes-packing mr-1"></i> Purchases History ({{ $totalPurchasesCount }})
        </button>
        <button @click="activeTab = 'payments'"
                :class="activeTab === 'payments' ? 'border-purple-600 text-purple-600 dark:text-purple-400 font-bold' : 'border-transparent text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-medium'"
                class="py-3 px-1 border-b-2 text-sm whitespace-nowrap transition">
            <i class="fas fa-receipt mr-1"></i> Payments Made
        </button>
    </div>

    {{-- ── TAB CONTENT ────────────────────────────────────────────────── --}}
    
    {{-- ── TAB 1: LEDGER (AUDIT TRAIL) ────────────────────────────────── --}}
    <div x-show="activeTab === 'ledger'">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-3">
                <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-book-open text-purple-600"></i> Supplier Ledger Audit Trail
                </h3>
                <span class="text-xs font-medium text-slate-400">
                    Single Source of Truth for Payable Balance History
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                        <tr>
                            <th class="p-4">Date & Time</th>
                            <th class="p-4">Type</th>
                            <th class="p-4 text-right">Amount</th>
                            <th class="p-4 text-right">Running Payable</th>
                            <th class="p-4">Method</th>
                            <th class="p-4">Note / Reference</th>
                            <th class="p-4">Performed By</th>
                            <th class="p-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($ledgerEntries as $entry)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 text-slate-600 dark:text-slate-400 text-xs font-medium whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($entry->created_at)->format('d M Y, h:i A') }}
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                @php
                                    $typeBadges = [
                                        'purchase'           => ['label' => 'Purchase',           'class' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/40 dark:text-blue-300'],
                                        'return_to_supplier' => ['label' => 'Supplier Return',   'class' => 'bg-orange-100 text-orange-800 dark:bg-orange-950/40 dark:text-orange-300'],
                                        'payment_made'       => ['label' => 'Payment Made',       'class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-300'],
                                        'payment_reversal'   => ['label' => 'Reversed',           'class' => 'bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-400'],
                                        'manual_adjustment'  => ['label' => 'Adjustment',         'class' => 'bg-purple-100 text-purple-800 dark:bg-purple-950/40 dark:text-purple-300'],
                                    ];
                                    $badge = $typeBadges[$entry->type] ?? ['label' => $entry->type, 'class' => 'bg-slate-100 text-slate-700'];
                                    $hasReversal = $entry->reversal !== null;
                                @endphp
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full {{ $badge['class'] }}">
                                    {{ $badge['label'] }}
                                </span>
                                @if($hasReversal)
                                    <span class="ml-1 px-2 py-0.5 text-[10px] font-bold rounded-full bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-500">reversed</span>
                                @endif
                            </td>
                            <td class="p-4 text-right font-black whitespace-nowrap
                                {{ $entry->amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                                {{ $entry->amount > 0 ? '+ Rs. ' . number_format($entry->amount, 2) : '- Rs. ' . number_format(abs($entry->amount), 2) }}
                            </td>
                            <td class="p-4 text-right font-mono font-bold text-slate-800 dark:text-white whitespace-nowrap">
                                Rs. {{ number_format($entry->balance_after, 2) }}
                            </td>
                            <td class="p-4 text-slate-600 dark:text-slate-400 text-xs font-semibold capitalize whitespace-nowrap">
                                {{ $entry->method ? str_replace('_', ' ', $entry->method) : '—' }}
                            </td>
                            <td class="p-4 text-slate-700 dark:text-slate-300 text-xs max-w-xs truncate">
                                {{ $entry->note ?: '—' }}
                            </td>
                            <td class="p-4 text-slate-500 dark:text-slate-400 text-xs whitespace-nowrap">
                                <i class="fas fa-user-circle text-slate-400 mr-1"></i>
                                {{ $entry->creator->name ?? 'System' }}
                            </td>
                            <td class="p-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1 justify-center">
                                    @if($entry->type === 'payment_made' && $entry->voucher)
                                        <a href="{{ route('supplier.vouchers.show', $entry->voucher->id) }}" target="_blank"
                                           class="inline-flex items-center gap-1 text-[10px] px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-600 hover:text-blue-800 dark:bg-blue-950/40 dark:hover:bg-blue-950/60 dark:text-blue-400 font-bold rounded-lg transition"
                                           title="View / Print Payment Voucher">
                                            <i class="fas fa-file-invoice"></i> View Voucher
                                        </a>
                                    @endif

                                    @if($isAdmin && $entry->type === 'payment_made' && !$hasReversal)
                                        <button @click="openReverseModal({{ $entry->id }})" title="Reverse this payment entry"
                                                class="inline-flex items-center gap-1 text-[10px] px-2.5 py-1 bg-gray-100 hover:bg-red-100 text-gray-500 hover:text-red-700 dark:bg-slate-800 dark:hover:bg-red-950/40 dark:text-slate-400 dark:hover:text-red-400 font-bold rounded-lg transition">
                                            <i class="fas fa-undo"></i> Reverse
                                        </button>
                                    @elseif(!($entry->type === 'payment_made' && $entry->voucher))
                                        <span class="text-slate-300 dark:text-slate-700 text-xs">—</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="p-10 text-center text-slate-400 dark:text-slate-500">
                                <i class="fas fa-book-open text-3xl mb-2 block opacity-30"></i>
                                No ledger entries recorded for this supplier yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ledgerEntries->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $ledgerEntries->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ── TAB 2: PURCHASES ────────────────────────────────────────────── --}}
    <div x-show="activeTab === 'purchases'">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-800 dark:text-white">
                    Purchase Bills
                </h3>
                <span class="text-sm font-bold text-blue-600 dark:text-blue-400">
                    Total: Rs. {{ number_format($totalPurchasesAmount, 2) }}
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                        <tr>
                            <th class="p-4">Bill No</th>
                            <th class="p-4">Date</th>
                            <th class="p-4 text-center">Items</th>
                            <th class="p-4 text-right">Net Total</th>
                            <th class="p-4">Payment Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($purchases as $purchase)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 font-mono font-bold text-slate-800 dark:text-white text-sm">
                                {{ $purchase->purchase_no ?: '—' }}
                            </td>
                            <td class="p-4 text-slate-600 dark:text-slate-400">
                                {{ \Carbon\Carbon::parse($purchase->invoice_date)->format('d M Y') }}
                            </td>
                            <td class="p-4 text-center font-semibold text-slate-700 dark:text-slate-300">
                                {{ $purchase->items_count }}
                            </td>
                            <td class="p-4 text-right font-black text-slate-800 dark:text-white">
                                Rs. {{ number_format($purchase->net_total, 2) }}
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                    {{ ucfirst($purchase->payment_type ?: 'Credit') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-10 text-center text-slate-400 dark:text-slate-500">
                                <i class="fas fa-boxes-packing text-3xl mb-2 block opacity-30"></i>
                                No purchase bills recorded for this supplier.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($purchases->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ── TAB 3: PAYMENTS MADE ────────────────────────────────────────── --}}
    <div x-show="activeTab === 'payments'">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                <h3 class="text-base font-bold text-slate-800 dark:text-white">
                    Payments Made to Supplier
                </h3>
                <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                    Total Paid: Rs. {{ number_format($totalPaidAmount, 2) }}
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                        <tr>
                            <th class="p-4">Date</th>
                            <th class="p-4">Voucher No</th>
                            <th class="p-4 text-right">Amount</th>
                            <th class="p-4">Payment Method</th>
                            <th class="p-4">Note</th>
                            <th class="p-4 text-center">Voucher Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse($payments as $pmt)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                            <td class="p-4 text-slate-600 dark:text-slate-400 text-xs">
                                {{ \Carbon\Carbon::parse($pmt->created_at)->format('d M Y, h:i A') }}
                            </td>
                            <td class="p-4 font-mono font-bold text-slate-800 dark:text-white text-xs">
                                {{ $pmt->voucher ? $pmt->voucher->voucher_number : '—' }}
                            </td>
                            <td class="p-4 text-right font-black text-emerald-600 dark:text-emerald-400">
                                Rs. {{ number_format(abs($pmt->amount), 2) }}
                            </td>
                            <td class="p-4 text-slate-700 dark:text-slate-300 capitalize text-xs">
                                {{ str_replace('_', ' ', $pmt->method ?: 'Cash') }}
                            </td>
                            <td class="p-4 text-slate-600 dark:text-slate-400 text-xs">
                                {{ $pmt->note ?: '—' }}
                            </td>
                            <td class="p-4 text-center">
                                @if($pmt->voucher)
                                    <a href="{{ route('supplier.vouchers.show', $pmt->voucher->id) }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-xs text-blue-600 hover:text-blue-800 font-bold bg-blue-50 dark:bg-blue-950/30 px-3 py-1.5 rounded-lg transition">
                                        <i class="fas fa-print"></i> Print Voucher
                                    </a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="p-10 text-center text-slate-400 dark:text-slate-500">
                                <i class="fas fa-hand-holding-dollar text-3xl mb-2 block opacity-30"></i>
                                No payments recorded for this supplier.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($payments->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ── MODAL 1: PAY SUPPLIER ─────────────────────────────────────── --}}
    @if($isAdmin)
    <div x-show="showPayModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showPayModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 md:p-8 max-w-md w-full shadow-2xl space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-hand-holding-dollar text-emerald-600"></i> Pay Supplier
                    </h3>
                    <button @click="showPayModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                            Amount (Rs.) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" min="0.01" x-model="payForm.amount" placeholder="0.00"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono text-base font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                            Payment Method <span class="text-red-500">*</span>
                        </label>
                        <select x-model="payForm.method"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none">
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="easypaisa">EasyPaisa</option>
                            <option value="jazzcash">JazzCash</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                            Payment Date
                        </label>
                        <input type="date" x-model="payForm.date"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                            Note / Reference
                        </label>
                        <textarea x-model="payForm.note" rows="2" placeholder="Optional reference note..."
                                  class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-emerald-500 outline-none"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button @click="showPayModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">
                        Cancel
                    </button>
                    <button @click="submitPaySupplier()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                        Confirm Payment
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── MODAL 2: ADJUST BALANCE ───────────────────────────────────── --}}
    <div x-show="showAdjustModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showAdjustModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 md:p-8 max-w-md w-full shadow-2xl space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-sliders text-purple-600"></i> Adjust Supplier Balance
                    </h3>
                    <button @click="showAdjustModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                            Action <span class="text-red-500">*</span>
                        </label>
                        <select x-model="adjustForm.action"
                                class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-purple-500 outline-none">
                            <option value="add_payable">Add Payable (+ Store owes more)</option>
                            <option value="reduce_payable">Reduce Payable (- Store owes less)</option>
                            <option value="set_balance">Set Balance Directly</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                            Amount (Rs.) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" step="0.01" min="0" x-model="adjustForm.amount" placeholder="0.00"
                               class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl font-mono text-base font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-purple-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                            Reason / Note (Min 10 chars) <span class="text-red-500">*</span>
                        </label>
                        <textarea x-model="adjustForm.note" rows="3" placeholder="Provide a detailed audit explanation for this adjustment..."
                                  class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-purple-500 outline-none"></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Must be at least 10 characters for audit compliance.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button @click="showAdjustModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">
                        Cancel
                    </button>
                    <button @click="submitAdjustBalance()" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                        Save Adjustment
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ── MODAL 3: REVERSE ENTRY ────────────────────────────────────── --}}
    <div x-show="showReverseModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showReverseModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 md:p-8 max-w-md w-full shadow-2xl space-y-5">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <h3 class="text-lg font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <i class="fas fa-undo text-red-600"></i> Reverse Payment Entry
                    </h3>
                    <button @click="showReverseModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>

                <p class="text-xs text-slate-600 dark:text-slate-400">
                    Reversing this payment entry will restore the paid amount back into the supplier's payable balance. The original voucher record will remain intact with a visual <strong>REVERSED</strong> stamp.
                </p>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-1">
                        Reason for Reversal <span class="text-red-500">*</span>
                    </label>
                    <textarea x-model="reverseForm.note" rows="3" placeholder="Reason for reversing this payment..."
                              class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-red-500 outline-none"></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <button @click="showReverseModal = false" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200">
                        Cancel
                    </button>
                    <button @click="submitReverseEntry()" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-md transition">
                        Confirm Reversal
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
@endsection
