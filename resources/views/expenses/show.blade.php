@extends('layouts.admin')

@section('title', 'Expense — ' . $expense->expense_no)

@section('content')
<div class="max-w-3xl mx-auto pb-16">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('expenses.index') }}" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">{{ $expense->expense_no }}</h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">{{ $expense->expense_date->format('d F Y') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('expenses.print', $expense->id) }}" target="_blank"
               class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl flex items-center gap-2 transition-colors">
                <i class="fas fa-print"></i> Print Voucher
            </a>
            @can('expenses.edit')
            <a href="{{ route('expenses.edit', $expense->id) }}"
               class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl flex items-center gap-2 transition-colors shadow-sm">
                <i class="fas fa-pencil"></i> Edit
            </a>
            @endcan
        </div>
    </div>

    {{-- Detail Card --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden">

        {{-- Amount Hero --}}
        <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-8 text-white text-center">
            <p class="text-sm font-semibold text-red-100 mb-1">Total Amount Paid</p>
            <p class="text-4xl font-black tracking-tight">Rs. {{ number_format($expense->amount, 2) }}</p>
            <p class="text-red-100 text-sm mt-2">{{ $expense->category_name }}</p>
        </div>

        <div class="p-6 space-y-4">
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4">
                <div>
                    <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Expense #</dt>
                    <dd class="text-sm font-bold text-slate-800 dark:text-white font-mono">{{ $expense->expense_no }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Date</dt>
                    <dd class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $expense->expense_date->format('d M Y, D') }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Category</dt>
                    <dd>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">
                            {{ $expense->category_name }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Payment Method</dt>
                    <dd class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ $expense->payment_method }}</dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Description</dt>
                    <dd class="text-sm text-slate-700 dark:text-slate-300">{{ $expense->description }}</dd>
                </div>
                @if($expense->wallet)
                <div>
                    <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Paid From</dt>
                    <dd class="text-sm text-slate-700 dark:text-slate-300">{{ $expense->wallet->name }}</dd>
                </div>
                @endif
                @if($expense->reference_no)
                <div>
                    <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Reference #</dt>
                    <dd class="text-sm font-mono text-slate-700 dark:text-slate-300">{{ $expense->reference_no }}</dd>
                </div>
                @endif
                @if($expense->notes)
                <div class="col-span-2">
                    <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Notes</dt>
                    <dd class="text-sm text-slate-600 dark:text-slate-400 italic">{{ $expense->notes }}</dd>
                </div>
                @endif
                <div>
                    <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Recorded By</dt>
                    <dd class="text-sm text-slate-700 dark:text-slate-300">{{ $expense->user->name ?? 'Staff' }}</dd>
                </div>
                <div>
                    <dt class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Recorded At</dt>
                    <dd class="text-sm text-slate-700 dark:text-slate-300">{{ $expense->created_at->format('d M Y, h:i A') }}</dd>
                </div>
            </dl>

            @if($expense->attachment_path)
            <div class="border-t border-slate-100 dark:border-slate-700/60 pt-4 mt-4">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Attachment</p>
                @php $ext = pathinfo($expense->attachment_path, PATHINFO_EXTENSION); @endphp
                @if(in_array(strtolower($ext), ['jpg','jpeg','png','webp']))
                <img src="{{ Storage::url($expense->attachment_path) }}" alt="Expense attachment"
                     class="max-h-64 rounded-xl border border-slate-200 dark:border-slate-700 object-contain">
                @else
                <a href="{{ Storage::url($expense->attachment_path) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition-colors">
                    <i class="fas fa-file-pdf text-red-500"></i> View Attachment
                </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
