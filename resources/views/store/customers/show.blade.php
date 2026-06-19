@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- Breadcrumb / Back Navigation --}}
    <div class="flex items-center justify-between mb-8">
        <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white font-semibold transition text-sm">
            <i class="fas fa-arrow-left"></i> Back to Customers
        </a>
        <div class="flex items-center gap-2">
            <span class="px-3 py-1 text-xs font-bold uppercase rounded-full {{ $customer->balance > 0 ? 'bg-emerald-100 text-emerald-800' : ($customer->balance < 0 ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                {{ $customer->balance > 0 ? 'Creditor' : ($customer->balance < 0 ? 'Debtor' : 'Settled') }}
            </span>
        </div>
    </div>

    {{-- Profile Header Card --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 md:p-8 shadow-sm mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
        <div class="absolute top-0 left-0 h-full w-2 bg-indigo-600"></div>
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white">{{ $customer->name }}</h1>
                @if($customer->phone)
                <span class="px-2.5 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full">{{ $customer->phone }}</span>
                @endif
            </div>
            <p class="text-slate-500 text-sm mt-1">
                <i class="fas fa-map-marker-alt mr-1"></i> {{ $customer->address ?: 'No address provided' }}
            </p>
        </div>
        <div class="flex flex-col text-left md:text-right">
            <p class="text-slate-400 text-xs font-bold uppercase tracking-wider">Current Balance</p>
            @if($customer->balance > 0)
                <h2 class="text-3xl font-black text-emerald-600 mt-1">Rs. {{ number_format($customer->balance, 2) }}</h2>
                <span class="text-xs font-bold text-emerald-500/80 bg-emerald-50 dark:bg-emerald-950/20 px-2 py-0.5 rounded-full inline-block mt-1 self-start md:self-end">Creditor (We owe them)</span>
            @elseif($customer->balance < 0)
                <h2 class="text-3xl font-black text-red-600 mt-1">Rs. {{ number_format(abs($customer->balance), 2) }}</h2>
                <span class="text-xs font-bold text-red-500/80 bg-red-50 dark:bg-red-950/20 px-2 py-0.5 rounded-full inline-block mt-1 self-start md:self-end">Debtor (They owe us)</span>
            @else
                <h2 class="text-3xl font-black text-slate-400 mt-1">Settled</h2>
                <span class="text-xs font-bold text-slate-400 bg-slate-50 dark:bg-slate-950 px-2 py-0.5 rounded-full inline-block mt-1 self-start md:self-end">No Balance Outstanding</span>
            @endif
        </div>
    </div>

    {{-- KPI Dashboard --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-8">
        {{-- Total Items Sold --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Total Items Sold</div>
            <div class="flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-slate-800 dark:text-white">{{ number_format($totalItemsSold) }}</span>
                <span class="text-slate-300 dark:text-slate-700"><i class="fas fa-boxes text-lg"></i></span>
            </div>
            <div class="text-slate-400 text-[10px] mt-2">Sum of quantity sold across all sales</div>
        </div>
        {{-- Cash Sales --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Cash Sales</div>
            <div>
                <div class="flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-slate-800 dark:text-white">Rs. {{ number_format($totalCashAmount) }}</span>
                    <span class="text-slate-300 dark:text-slate-700"><i class="fas fa-wallet text-lg"></i></span>
                </div>
                <div class="text-slate-400 text-[10px] mt-2">{{ $totalCashCount }} cash {{ Str::plural('bill', $totalCashCount) }} recorded</div>
            </div>
        </div>
        {{-- Credit Sales --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Credit Sales</div>
            <div>
                <div class="flex items-baseline justify-between">
                    <span class="text-2xl font-extrabold text-slate-800 dark:text-white">Rs. {{ number_format($totalDebitAmount) }}</span>
                    <span class="text-slate-300 dark:text-slate-700"><i class="fas fa-file-invoice-dollar text-lg"></i></span>
                </div>
                <div class="text-slate-400 text-[10px] mt-2">{{ $totalDebitCount }} credit {{ Str::plural('bill', $totalDebitCount) }} recorded</div>
            </div>
        </div>
        {{-- Outstanding Due --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between relative overflow-hidden">
            @if($outstandingAmount > 0)
                <div class="absolute top-0 left-0 w-full h-1 bg-red-500"></div>
            @endif
            <div class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Outstanding Due</div>
            <div class="flex items-baseline justify-between">
                <span class="text-2xl font-extrabold {{ $outstandingAmount > 0 ? 'text-red-600' : 'text-slate-800 dark:text-slate-200' }}">Rs. {{ number_format($outstandingAmount) }}</span>
                <span class="text-slate-300 dark:text-slate-700"><i class="fas fa-exclamation-circle text-lg"></i></span>
            </div>
            <div class="text-slate-400 text-[10px] mt-2">Unpaid credit + opening balance</div>
        </div>
        {{-- Grand Total Sales --}}
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div class="text-slate-400 dark:text-slate-500 text-xs font-bold uppercase tracking-wider mb-2">Grand Total Sales</div>
            <div class="flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400">Rs. {{ number_format($grandTotalSales) }}</span>
                <span class="text-slate-300 dark:text-slate-700"><i class="fas fa-chart-line text-lg"></i></span>
            </div>
            <div class="text-slate-400 text-[10px] mt-2">Cash + credit sales total</div>
        </div>
    </div>

    {{-- Recent Bills Table --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-12">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <i class="fas fa-file-invoice text-indigo-500"></i> Recent Sales
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                            <tr>
                                <th class="p-4">Bill No</th>
                                <th class="p-4">Date</th>
                                <th class="p-4 text-center">Items</th>
                                <th class="p-4 text-right">Net Amount</th>
                                <th class="p-4">Type</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse($bills as $bill)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                <td class="p-4 font-mono font-bold text-slate-800 dark:text-white">{{ $bill->bill_no ?: '—' }}</td>
                                <td class="p-4 text-slate-600 dark:text-slate-350">{{ \Carbon\Carbon::parse($bill->date)->format('d-M-Y') }}</td>
                                <td class="p-4 text-center font-bold text-slate-700 dark:text-slate-350">{{ $bill->items_count }}</td>
                                <td class="p-4 text-right font-black text-slate-800 dark:text-white">Rs. {{ number_format($bill->amount, 2) }}</td>
                                <td class="p-4">
                                    <span class="px-2.5 py-0.5 rounded text-xs font-bold border {{ strtolower($bill->type) === 'credit' ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/20 dark:text-amber-400 dark:border-amber-900' : 'bg-green-50 text-green-700 border-green-200 dark:bg-green-950/20 dark:text-green-400 dark:border-green-900' }}">
                                        {{ $bill->type }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full {{ $bill->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : ($bill->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ $bill->status }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    @if($bill->print_route)
                                    <a href="{{ $bill->print_route }}" target="_blank" class="inline-flex items-center gap-1.5 text-indigo-500 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg text-xs font-bold transition">
                                        <i class="fas fa-print"></i> Print
                                    </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="p-8 text-center text-slate-450 dark:text-slate-500 font-medium">No sales transactions recorded for this customer.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($bills->hasPages())
                <div class="p-4 border-t border-slate-100 dark:border-slate-800">
                    {{ $bills->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
