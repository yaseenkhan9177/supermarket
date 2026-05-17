@extends('layouts.admin')

@section('title', 'Sales Report')

@section('content')
<div class="space-y-6">
    <!-- Header & Filter -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Sales Report</h2>
            <p class="text-sm text-gray-500">Overview of debit sales and collections</p>
        </div>
        <form method="GET" action="{{ route('reports.sales') }}" class="flex flex-col sm:flex-row gap-2 bg-white p-2 rounded-lg shadow-sm border">
            <input type="date" name="start_date" value="{{ $startDate }}" class="border rounded px-2 py-1 text-sm focus:ring-indigo-500">
            <span class="text-gray-400 self-center hidden sm:block">-</span>
            <input type="date" name="end_date" value="{{ $endDate }}" class="border rounded px-2 py-1 text-sm focus:ring-indigo-500">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-1 rounded text-sm hover:bg-indigo-700 font-medium">Filter</button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl shadow-sm border border-indigo-100">
            <div class="text-gray-500 text-sm font-medium">Total Sales</div>
            <div class="text-2xl font-bold text-indigo-700 mt-1">Rs. {{ number_format($totalSales, 2) }}</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-green-100">
            <div class="text-gray-500 text-sm font-medium">Total Received</div>
            <div class="text-2xl font-bold text-green-700 mt-1">Rs. {{ number_format($totalPaid, 2) }}</div>
        </div>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-red-100">
            <div class="text-gray-500 text-sm font-medium">Total Due</div>
            <div class="text-2xl font-bold text-red-700 mt-1">Rs. {{ number_format($totalDue, 2) }}</div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Invoice</th>
                        <th class="px-6 py-3">Customer</th>
                        <th class="px-6 py-3 text-right">Net Total</th>
                        <th class="px-6 py-3 text-right">Paid</th>
                        <th class="px-6 py-3 text-right">Balance</th>
                        <th class="px-6 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr class="bg-white border-b hover:bg-gray-50">
                        <td class="px-6 py-4">{{ Carbon\Carbon::parse($sale->invoice_date)->format('d M, Y') }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $sale->invoice_no }}</td>
                        <td class="px-6 py-4">{{ $sale->customer->name ?? 'Walk-in' }}</td>
                        <td class="px-6 py-4 text-right font-medium">Rs. {{ number_format($sale->net_total, 2) }}</td>
                        <td class="px-6 py-4 text-right text-green-600">Rs. {{ number_format($sale->paid_amount, 2) }}</td>
                        <td class="px-6 py-4 text-right text-red-600">Rs. {{ number_format($sale->balance ?? ($sale->net_total - $sale->paid_amount), 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 rounded-full text-xs font-bold 
                                {{ $sale->status == 'paid' ? 'bg-green-100 text-green-800' : ($sale->status == 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst($sale->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">No sales records found for this period.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection