@extends('layouts.admin')

@section('title', 'Accounts Report')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Financial Accounts</h2>
            <p class="text-sm text-gray-500">Current balance of all accounts</p>
        </div>
        <button onclick="window.print()" class="text-gray-600 hover:text-gray-900 text-sm font-medium">
            <i class="fas fa-print mr-1"></i> Print
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($accounts as $account)
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex justify-between items-start mb-4">
                <div class="p-2 rounded-lg {{ $account->type == 'Cash' ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600' }}">
                    <i class="fas {{ $account->type == 'Cash' ? 'fa-wallet' : 'fa-university' }} text-xl"></i>
                </div>
                <span class="text-xs font-semibold px-2 py-1 rounded bg-gray-100 text-gray-600">{{ $account->account_number }}</span>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">{{ $account->name }}</p>
                <h3 class="text-2xl font-bold text-gray-800 mt-1">Rs. {{ number_format($account->current_balance, 2) }}</h3>
            </div>
            <div class="mt-4 pt-4 border-t text-xs text-gray-400 flex justify-between">
                <span>Updated recently</span>
                <span>{{ $account->bank_name }}</span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Transaction History Placeholder (Optional) -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mt-8">
        <h3 class="font-bold text-gray-700 mb-4">Account Summary</h3>
        <p class="text-gray-500 text-sm">Detailed transaction logic to be implemented.</p>
    </div>
</div>
@endsection