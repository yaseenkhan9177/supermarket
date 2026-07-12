@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- Breadcrumbs & Header --}}
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('customers.index') }}"
           class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white font-semibold transition text-sm">
            <i class="fas fa-arrow-left"></i> Back to Customers
        </a>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">New Customer</h1>
    </div>

    {{-- Form Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm">
        <form method="POST" action="{{ route('customers.store') }}">
            @csrf

            <div class="space-y-6">
                {{-- Name --}}
                <div>
                    <label for="name" class="block text-sm font-bold text-slate-700 dark:text-slate-350 mb-2">Customer Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" required value="{{ old('name') }}"
                           class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-transparent text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition"
                           placeholder="Enter customer name">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-sm font-bold text-slate-700 dark:text-slate-350 mb-2">Phone Number</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-transparent text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition"
                           placeholder="e.g. 03001234567">
                    @error('phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Address --}}
                <div>
                    <label for="address" class="block text-sm font-bold text-slate-700 dark:text-slate-350 mb-2">Address</label>
                    <textarea name="address" id="address" rows="3"
                              class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-transparent text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition"
                              placeholder="Enter address details">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Credit Limit --}}
                    <div>
                        <label for="credit_limit" class="block text-sm font-bold text-slate-700 dark:text-slate-350 mb-2">Credit Limit (Rs.)</label>
                        <input type="number" step="0.01" name="credit_limit" id="credit_limit" value="{{ old('credit_limit', 0) }}"
                               class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-transparent text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition"
                               placeholder="0.00">
                        @error('credit_limit')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Opening Balance --}}
                    <div>
                        <label for="balance" class="block text-sm font-bold text-slate-700 dark:text-slate-350 mb-2">Opening Balance (Rs.)</label>
                        <input type="number" step="0.01" name="balance" id="balance" value="{{ old('balance', 0) }}"
                               class="w-full px-4 py-2.5 border border-slate-300 dark:border-slate-700 rounded-xl bg-transparent text-slate-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600 transition"
                               placeholder="0.00">
                        <p class="text-xs text-slate-400 mt-1">Use positive numbers if they owe you, negative if you owe them.</p>
                        @error('balance')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-800">
                    <a href="{{ route('customers.index') }}"
                       class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-750 text-slate-800 dark:text-white text-sm font-bold rounded-xl transition">
                        Cancel
                    </a>
                    <button type="submit"
                            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition">
                        Save Customer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
