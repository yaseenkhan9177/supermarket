@extends('layouts.admin')

@section('navbar_subtitle', 'Debit Sales Management')

@section('navbar_actions')
<a href="{{ route('debit-sales.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
    <i class="fas fa-plus"></i>
    New Invoice
</a>
@endsection

@section('content')
<div class="space-y-6" x-data="{ searchQuery: '' }">

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center">
            <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-lg flex items-center justify-center mr-4">
                <i class="fas fa-file-invoice text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Invoices</p>
                <p class="text-lg font-bold text-gray-800">{{ $sales->total() }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center">
            <div class="w-12 h-12 bg-yellow-50 text-yellow-600 rounded-lg flex items-center justify-center mr-4">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Open</p>
                <p class="text-lg font-bold text-gray-800">{{ $sales->where('status', 'open')->count() }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center">
            <div class="w-12 h-12 bg-red-50 text-red-600 rounded-lg flex items-center justify-center mr-4">
                <i class="fas fa-exclamation-circle text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Overdue</p>
                <p class="text-lg font-bold text-gray-800">{{ $sales->where('status', 'overdue')->count() }}</p>
            </div>
        </div>
        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center">
            <div class="w-12 h-12 bg-green-50 text-green-600 rounded-lg flex items-center justify-center mr-4">
                <i class="fas fa-check-circle text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</p>
                <p class="text-lg font-bold text-gray-800">{{ $sales->where('status', 'paid')->count() }}</p>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <!-- Search & Filters -->
        <div class="p-4 md:p-6 border-b border-gray-100 bg-gray-50/50 flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="relative w-full md:w-96">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </span>
                <input type="text" x-model="searchQuery" placeholder="Search by Invoice # or Customer name..." class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
            </div>
            <div class="flex gap-2 w-full md:w-auto">
                <select class="w-full md:w-40 px-3 py-2 border border-gray-200 rounded-lg bg-white text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">All Statuses</option>
                    <option value="open">Open</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-[10px] font-bold text-gray-500 uppercase tracking-widest border-b border-gray-100">
                        <th class="px-6 py-4">Invoice Detail</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4 text-right">Net Amount</th>
                        <th class="px-6 py-4">Due Date</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50 transition group" x-show="searchQuery == '' || '{{ strtolower($sale->invoice_no) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower($sale->customer->name) }}'.includes(searchQuery.toLowerCase())">
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-900 text-sm tracking-tight">{{ $sale->invoice_no }}</span>
                                <span class="text-[10px] text-gray-500 flex items-center mt-1">
                                    <i class="far fa-calendar-alt mr-1 text-indigo-400"></i> {{ \Carbon\Carbon::parse($sale->invoice_date)->format('M d, Y') }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-xs mr-3">
                                    {{ strtoupper(substr($sale->customer->name, 0, 2)) }}
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-medium text-gray-800">{{ $sale->customer->name }}</span>
                                    <span class="text-[10px] text-gray-400 font-mono">{{ $sale->customer->phone ?? 'No phone' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-gray-900 text-sm">
                            $ {{ number_format($sale->net_total, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs {{ \Carbon\Carbon::parse($sale->due_date)->isPast() && $sale->status != 'paid' ? 'text-red-500 font-bold' : 'text-gray-600' }}">
                                {{ \Carbon\Carbon::parse($sale->due_date)->format('M d, Y') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($sale->status == 'open')
                            <span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-full text-[10px] font-bold uppercase tracking-wider">Open</span>
                            @elseif($sale->status == 'paid')
                            <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold uppercase tracking-wider">Paid</span>
                            @else
                            <span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-[10px] font-bold uppercase tracking-wider">Overdue</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition duration-200">
                                <a href="#" class="p-2 text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="View Details">
                                    <i class="far fa-eye"></i>
                                </a>
                                <a href="#" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition" title="Print Invoice">
                                    <i class="fas fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-file-invoice text-4xl mb-4 text-gray-200"></i>
                                <p class="text-sm">No debit sales found.</p>
                                <a href="{{ route('debit-sales.create') }}" class="mt-4 text-indigo-600 hover:underline font-bold text-xs uppercase">Create Your First Invoice</a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
        <div class="p-4 border-t border-gray-100">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</div>
@endsection