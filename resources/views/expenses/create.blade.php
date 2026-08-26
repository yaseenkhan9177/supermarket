@extends('layouts.admin')

@section('title', 'Record New Expense')

@section('content')
<div class="max-w-3xl mx-auto pb-16">

    {{-- Header --}}
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('expenses.index') }}" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 dark:text-slate-400 transition-colors">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight">Record New Expense</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Fill in the details below to record an operational expense</p>
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

    <form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data"
          x-data="expenseForm()" class="space-y-6">
        @csrf

        {{-- Main Card --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 bg-slate-50/50 dark:bg-slate-700/30">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                    <i class="fas fa-info-circle text-indigo-500"></i> Expense Details
                </h3>
            </div>
            <div class="p-6 space-y-5">

                {{-- Row 1: Date + Category --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                            Expense Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="expense_date" value="{{ old('expense_date', today()->toDateString()) }}" required
                               class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                            Category <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-2">
                            <select name="expense_category_id" id="category-select" required
                                    class="flex-1 text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('expense_category_id') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                        Description <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="description" value="{{ old('description') }}" required
                           placeholder="e.g. Monthly rent for main store, January electricity bill..."
                           class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                {{-- Row 2: Amount + Payment Method --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                            Amount (Rs.) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="amount" step="0.01" min="0.01" value="{{ old('amount') }}" required
                               placeholder="0.00"
                               class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                            Payment Method <span class="text-red-500">*</span>
                        </label>
                        <select name="payment_method" x-model="paymentMethod" required
                                class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            @foreach(['Cash','Bank','Cheque','Card','Other'] as $m)
                            <option value="{{ $m }}" {{ old('payment_method','Cash') == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Wallet/Bank selector (conditional) --}}
                <div x-show="paymentMethod === 'Cash' || paymentMethod === 'Bank' || paymentMethod === 'Cheque'">
                    <div x-show="paymentMethod === 'Cash'">
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                            Cash Wallet / Drawer
                        </label>
                        <select name="wallet_id"
                                class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="">-- Default Wallet --</option>
                            @foreach($wallets as $wallet)
                            <option value="{{ $wallet->id }}" {{ old('wallet_id') == $wallet->id ? 'selected' : '' }}>
                                {{ $wallet->name }} (Rs. {{ number_format($wallet->balance ?? 0, 2) }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div x-show="paymentMethod === 'Bank' || paymentMethod === 'Cheque'">
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                            Bank Account
                        </label>
                        <select name="bank_account_id"
                                class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <option value="">-- Select Bank Account --</option>
                            @foreach($bankAccounts as $bank)
                            <option value="{{ $bank->id }}" {{ old('bank_account_id') == $bank->id ? 'selected' : '' }}>
                                {{ $bank->bank_name }} - {{ $bank->account_number ?? '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Reference No + Notes --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                            Reference / Cheque No.
                        </label>
                        <input type="text" name="reference_no" value="{{ old('reference_no') }}"
                               placeholder="Invoice #, receipt #, cheque #..."
                               class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                            Notes
                        </label>
                        <input type="text" name="notes" value="{{ old('notes') }}"
                               placeholder="Additional remarks (optional)..."
                               class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                {{-- Attachment --}}
                <div>
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">
                        Attachment <span class="text-slate-400 font-normal">(Receipt / Invoice / Bill — max 5MB)</span>
                    </label>
                    <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf"
                           class="w-full text-sm text-slate-600 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Accepted: JPG, PNG, WEBP, PDF</p>
                </div>

            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('expenses.index') }}"
               class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition-colors">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                <i class="fas fa-save"></i> Record Expense
            </button>
        </div>
    </form>
</div>

<script>
function expenseForm() {
    return {
        paymentMethod: '{{ old('payment_method', 'Cash') }}',
    };
}
</script>
@endsection
