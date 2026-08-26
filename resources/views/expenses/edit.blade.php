@extends('layouts.admin')

@section('title', 'Edit Expense — ' . $expense->expense_no)

@section('content')
<div class="max-w-3xl mx-auto pb-16">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('expenses.index') }}" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 dark:text-slate-400 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Edit Expense</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5 font-mono">{{ $expense->expense_no }}</p>
        </div>
    </div>

    @if($errors->any())
    <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800/60 rounded-2xl">
        <div class="flex items-center gap-2 mb-2">
            <i class="fas fa-exclamation-triangle text-red-500"></i>
            <p class="text-sm font-bold text-red-700 dark:text-red-400">Please fix the following errors:</p>
        </div>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li class="text-xs text-red-600 dark:text-red-400">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Warning about wallet adjustment --}}
    <div class="mb-6 p-4 bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800/60 rounded-2xl flex items-start gap-3">
        <i class="fas fa-info-circle text-amber-500 text-base shrink-0 mt-0.5"></i>
        <div class="text-xs text-amber-800 dark:text-amber-300">
            <strong>Balance Adjustment Notice:</strong> Editing this expense will automatically reverse the original wallet balance adjustment and apply the new one. The difference will be settled in the linked cash/bank account.
        </div>
    </div>

    <form method="POST" action="{{ route('expenses.update', $expense->id) }}" enctype="multipart/form-data"
          x-data="expenseForm()" class="space-y-6">
        @csrf @method('PUT')

        <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-700/30">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                    <i class="fas fa-edit text-amber-500"></i> Edit Expense Details
                </h3>
            </div>
            <div class="p-6 space-y-5">

                {{-- Row 1: Date + Category --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                            Expense Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="expense_date"
                               value="{{ old('expense_date', $expense->expense_date->toDateString()) }}" required
                               class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <select name="expense_category_id" required
                                class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('expense_category_id', $expense->expense_category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="description"
                           value="{{ old('description', $expense->description) }}" required
                           class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                {{-- Row 2: Amount + Payment Method --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                            Amount (Rs.) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="amount" step="0.01" min="0.01"
                               value="{{ old('amount', $expense->amount) }}" required
                               class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                            Payment Method <span class="text-red-500">*</span>
                        </label>
                        <select name="payment_method" x-model="paymentMethod" required
                                class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            @foreach(['Cash','Bank','Cheque','Card','Other'] as $m)
                            <option value="{{ $m }}"
                                {{ old('payment_method', $expense->payment_method) == $m ? 'selected' : '' }}>
                                {{ $m }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Wallet/Bank selector --}}
                <div x-show="paymentMethod === 'Cash' || paymentMethod === 'Bank' || paymentMethod === 'Cheque'">
                    <div x-show="paymentMethod === 'Cash'">
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">Cash Wallet</label>
                        <select name="wallet_id"
                                class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="">-- Default Wallet --</option>
                            @foreach($wallets as $wallet)
                            <option value="{{ $wallet->id }}"
                                {{ old('wallet_id', $expense->wallet_id) == $wallet->id ? 'selected' : '' }}>
                                {{ $wallet->name }} (Rs. {{ number_format($wallet->balance ?? 0, 2) }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="paymentMethod === 'Bank' || paymentMethod === 'Cheque'">
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">Bank Account</label>
                        <select name="bank_account_id"
                                class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="">-- Select Bank Account --</option>
                            @foreach($bankAccounts as $bank)
                            <option value="{{ $bank->id }}"
                                {{ old('bank_account_id', $expense->bank_account_id) == $bank->id ? 'selected' : '' }}>
                                {{ $bank->bank_name }} - {{ $bank->account_number ?? '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Reference + Notes --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">Reference / Cheque No.</label>
                        <input type="text" name="reference_no"
                               value="{{ old('reference_no', $expense->reference_no) }}"
                               class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">Notes</label>
                        <input type="text" name="notes"
                               value="{{ old('notes', $expense->notes) }}"
                               class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                {{-- Attachment --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                        Attachment
                        @if($expense->attachment_path)
                        <span class="font-normal text-slate-400">(current attachment will be replaced if you upload a new file)</span>
                        @endif
                    </label>
                    @if($expense->attachment_path)
                    <div class="mb-2 flex items-center gap-2 text-xs text-slate-600 dark:text-slate-400">
                        <i class="fas fa-paperclip text-indigo-400"></i>
                        <a href="{{ Storage::url($expense->attachment_path) }}" target="_blank"
                           class="text-indigo-600 dark:text-indigo-400 hover:underline">
                            View current attachment
                        </a>
                    </div>
                    @endif
                    <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf"
                           class="w-full text-sm text-slate-600 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-100 cursor-pointer">
                </div>

            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('expenses.index') }}"
               class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                <i class="fas fa-save"></i> Update Expense
            </button>
        </div>
    </form>
</div>

<script>
function expenseForm() {
    return {
        paymentMethod: '{{ old('payment_method', $expense->payment_method) }}',
    };
}
</script>
@endsection
