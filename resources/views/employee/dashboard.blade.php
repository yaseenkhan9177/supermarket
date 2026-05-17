@extends('layouts.employee')

@section('content')
<div class="flex-1 p-8 overflow-y-auto" x-data="{ priceCheckOpen: false }">

    <div class="flex justify-between items-end mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Welcome back, {{ auth('employee')->user()->name }}! 👋</h1>
            <p class="text-gray-500 mt-1">Here is your store performance for today.</p>
        </div>
        <div class="bg-white border border-gray-200 px-4 py-2 rounded-lg shadow-sm flex items-center gap-3">
            <div class="bg-blue-50 p-1.5 rounded text-blue-600">
                <i data-lucide="calendar" class="w-4 h-4"></i>
            </div>
            <div class="text-right">
                <div class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Current Time</div>
                <div class="text-sm font-bold text-gray-800 font-mono">
                    <span x-text="new Date().toLocaleTimeString()">11:24:05 AM</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 relative overflow-hidden group hover:border-blue-300 transition">
            <div class="absolute right-0 top-0 h-full w-1 bg-blue-500"></div>
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">My Sales Today</p>
                    <h3 class="text-2xl font-extrabold text-gray-800 mt-1">Rs. {{ number_format($stats['my_sales_today']) }}</h3>
                </div>
                <div class="bg-blue-50 p-2 rounded-lg text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                    <i data-lucide="wallet" class="w-5 h-5"></i>
                </div>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-1.5 mt-2">
                <div class="bg-blue-500 h-1.5 rounded-full" style="width: 45%"></div>
            </div>
            <p class="text-[10px] text-gray-400 mt-1">45% of daily goal (12k)</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 relative overflow-hidden group hover:border-green-300 transition">
            <div class="absolute right-0 top-0 h-full w-1 bg-green-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Cash In Hand</p>
                    <h3 class="text-2xl font-extrabold text-gray-800 mt-1">Rs. 12,250</h3>
                </div>
                <div class="bg-green-50 p-2 rounded-lg text-green-600 group-hover:bg-green-600 group-hover:text-white transition">
                    <i data-lucide="banknote" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-[10px] text-green-600 mt-2 font-medium flex items-center gap-1">
                <i data-lucide="arrow-up" class="w-3 h-3"></i> Includes Opening Float
            </p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 relative overflow-hidden group hover:border-purple-300 transition">
            <div class="absolute right-0 top-0 h-full w-1 bg-purple-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Invoices</p>
                    <h3 class="text-2xl font-extrabold text-gray-800 mt-1">{{ $stats['my_invoices_count'] }}</h3>
                </div>
                <div class="bg-purple-50 p-2 rounded-lg text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-[10px] text-gray-400 mt-2">Last: 12 mins ago</p>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 relative overflow-hidden group hover:border-orange-300 transition">
            <div class="absolute right-0 top-0 h-full w-1 bg-orange-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Pending Tasks</p>
                    <h3 class="text-2xl font-extrabold text-gray-800 mt-1">{{ $stats['pending_tasks'] }}</h3>
                </div>
                <div class="bg-orange-50 p-2 rounded-lg text-orange-600 group-hover:bg-orange-600 group-hover:text-white transition">
                    <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                </div>
            </div>
            <p class="text-[10px] text-orange-600 mt-2 font-medium cursor-pointer hover:underline">View List &rarr;</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <a href="{{ route('sales.pos') }}" class="col-span-1 md:col-span-1 bg-gradient-to-r from-blue-600 to-blue-500 text-white p-6 rounded-xl shadow-lg hover:shadow-xl hover:scale-[1.02] transition transform flex items-center justify-between group">
            <div class="text-left">
                <h3 class="text-lg font-bold">New Sale (POS)</h3>
                <p class="text-blue-100 text-xs mt-1">Start scanning items</p>
            </div>
            <div class="bg-white/20 p-3 rounded-full group-hover:bg-white/30 transition">
                <i data-lucide="shopping-cart" class="w-6 h-6 text-white"></i>
            </div>
        </a>

        <div class="col-span-1 md:col-span-2 grid grid-cols-3 gap-4">

            @if(auth('employee')->user()->hasPermission('customers.create'))
            <a href="{{ route('customers.index') }}" class="bg-white border border-gray-200 p-4 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition flex flex-col items-center justify-center gap-2 group">
                <div class="bg-purple-50 p-2 rounded-full text-purple-600 group-hover:scale-110 transition">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                </div>
                <span class="text-sm font-semibold text-gray-700">Add Customer</span>
            </a>
            @endif

            <button @click="priceCheckOpen = true" class="bg-white border border-gray-200 p-4 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition flex flex-col items-center justify-center gap-2 group">
                <div class="bg-yellow-50 p-2 rounded-full text-yellow-600 group-hover:scale-110 transition">
                    <i data-lucide="scan-barcode" class="w-5 h-5"></i>
                </div>
                <span class="text-sm font-semibold text-gray-700">Price Check</span>
            </button>

            <button class="bg-white border border-gray-200 p-4 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition flex flex-col items-center justify-center gap-2 group">
                <div class="bg-red-50 p-2 rounded-full text-red-600 group-hover:scale-110 transition">
                    <i data-lucide="rotate-ccw" class="w-5 h-5"></i>
                </div>
                <span class="text-sm font-semibold text-gray-700">Return Item</span>
            </button>

        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-800">Recent Transactions</h3>
            <a href="#" class="text-xs text-blue-600 font-semibold hover:underline">View All History</a>
        </div>
        <table class="w-full text-left text-sm text-gray-600">
            <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                <tr>
                    <th class="px-6 py-3">Receipt #</th>
                    <th class="px-6 py-3">Time</th>
                    <th class="px-6 py-3">Customer</th>
                    <th class="px-6 py-3 text-right">Amount</th>
                    <th class="px-6 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3 font-medium text-blue-600">#INV-2026-012</td>
                    <td class="px-6 py-3">11:15 AM</td>
                    <td class="px-6 py-3">Walk-in Customer</td>
                    <td class="px-6 py-3 text-right font-bold text-gray-800">Rs. 1,200</td>
                    <td class="px-6 py-3 text-center"><span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold">PAID</span></td>
                </tr>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-3 font-medium text-blue-600">#INV-2026-011</td>
                    <td class="px-6 py-3">10:48 AM</td>
                    <td class="px-6 py-3">Amos Stark</td>
                    <td class="px-6 py-3 text-right font-bold text-gray-800">Rs. 4,200</td>
                    <td class="px-6 py-3 text-center"><span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold">PAID</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Price Check Modal -->
    <div x-show="priceCheckOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="priceCheckOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="priceCheckOpen = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="priceCheckOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i data-lucide="scan-barcode" class="h-6 w-6 text-yellow-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Price Checker</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-4">Scan a barcode or enter SKU to check price and stock.</p>
                                <input type="text" class="w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Scan Barcode / Enter SKU">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto" @click="priceCheckOpen = false">Check</button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto" @click="priceCheckOpen = false">Cancel</button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection