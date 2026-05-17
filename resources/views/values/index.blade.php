<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Search | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800" x-data="valueSearch()">

    <nav class="bg-indigo-900 border-b border-indigo-800 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8 text-white">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-indigo-700 flex items-center justify-center text-white shadow-md border border-indigo-600">
                    <i class="fas fa-search-dollar text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-white leading-none tracking-tight">
                        OwnStore <span class="text-indigo-400">PRO</span>
                    </h1>
                    <span class="text-xs text-indigo-300 font-medium mt-0.5">Numbers in Transactions (Value Search)</span>
                </div>
            </div>
            <div>
                <a href="/dashboard" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105 border border-indigo-700">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1400px] pb-32">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-4 space-y-6">
                <!-- Search Form -->
                <form @submit.prevent="performSearch" class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-indigo-50 px-6 py-4 border-b border-indigo-100 flex items-center gap-2">
                        <i class="fas fa-filter text-indigo-600"></i>
                        <h3 class="font-bold text-indigo-900">Search Criteria</h3>
                    </div>

                    <div class="p-6 space-y-6">

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-3">Transaction Type</label>
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                                <label class="flex items-center gap-3 p-2 rounded hover:bg-gray-50 cursor-pointer border border-transparent hover:border-gray-200">
                                    <input type="radio" name="type" value="all" x-model="filters.type" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm font-medium">X. All Above (Global Search)</span>
                                </label>
                                <label class="flex items-center gap-3 p-2 rounded hover:bg-gray-50 cursor-pointer border border-transparent hover:border-gray-200">
                                    <input type="radio" name="type" value="sales" x-model="filters.type" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm">1. Sales (Cash & Credit)</span>
                                </label>
                                <label class="flex items-center gap-3 p-2 rounded hover:bg-gray-50 cursor-pointer border border-transparent hover:border-gray-200">
                                    <input type="radio" name="type" value="purchases" x-model="filters.type" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm">4. Purchases (Bills)</span>
                                </label>
                                <label class="flex items-center gap-3 p-2 rounded hover:bg-gray-50 cursor-pointer border border-transparent hover:border-gray-200">
                                    <input type="radio" name="type" value="payments" x-model="filters.type" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm">9. Payment (Expenses)</span>
                                </label>
                                <label class="flex items-center gap-3 p-2 rounded hover:bg-gray-50 cursor-pointer border border-transparent hover:border-gray-200">
                                    <input type="radio" name="type" value="receipts" x-model="filters.type" class="text-indigo-600 focus:ring-indigo-500">
                                    <span class="text-sm">8. Receipt (Income)</span>
                                </label>
                            </div>
                        </div>

                        <hr class="border-dashed border-gray-200">

                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <input type="checkbox" id="useDate" x-model="filters.useDate" class="rounded text-indigo-600">
                                <label for="useDate" class="text-xs font-bold text-gray-500 uppercase">Date Range</label>
                            </div>
                            <div class="grid grid-cols-2 gap-3" x-show="filters.useDate" x-transition>
                                <div>
                                    <label class="text-[10px] text-gray-400 font-bold block mb-1">From</label>
                                    <input type="date" x-model="filters.dateFrom" class="w-full border border-gray-300 rounded p-2 text-sm">
                                </div>
                                <div>
                                    <label class="text-[10px] text-gray-400 font-bold block mb-1">To</label>
                                    <input type="date" x-model="filters.dateTo" class="w-full border border-gray-300 rounded p-2 text-sm">
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <input type="checkbox" id="useValue" x-model="filters.useValue" class="rounded text-indigo-600">
                                <label for="useValue" class="text-xs font-bold text-gray-500 uppercase">Value / Amount Range</label>
                            </div>
                            <div class="grid grid-cols-2 gap-3" x-show="filters.useValue" x-transition>
                                <div>
                                    <label class="text-[10px] text-gray-400 font-bold block mb-1">Lower Limit</label>
                                    <input type="number" step="0.01" x-model="filters.valLower" placeholder="Min" class="w-full border border-gray-300 rounded p-2 text-sm">
                                </div>
                                <div>
                                    <label class="text-[10px] text-gray-400 font-bold block mb-1">Upper Limit</label>
                                    <input type="number" step="0.01" x-model="filters.valUpper" placeholder="Max" class="w-full border border-gray-300 rounded p-2 text-sm">
                                </div>
                            </div>
                        </div>

                        <button type="submit" :disabled="loading" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg shadow transition transform hover:-translate-y-0.5 flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-search" x-show="!loading"></i>
                            <i class="fas fa-spinner fa-spin" x-show="loading" style="display: none;"></i>
                            <span x-text="loading ? 'Searching...' : 'Get List'"></span>
                        </button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-8">
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden min-h-[500px] flex flex-col">

                    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-list text-gray-400"></i>
                            <h3 class="font-bold text-gray-700">Search Results</h3>
                        </div>
                        <span class="bg-indigo-100 text-indigo-800 text-xs font-bold px-2 py-1 rounded-full" x-text="results.length + ' Records Found'"></span>
                    </div>

                    <div class="flex-1 overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-gray-100 text-xs font-bold text-gray-500 uppercase sticky top-0">
                                <tr>
                                    <th class="p-4 w-24">Date</th>
                                    <th class="p-4 w-32">Ref #</th>
                                    <th class="p-4 w-24">Type</th>
                                    <th class="p-4">Description / Account</th>
                                    <th class="p-4 w-32 text-right">Amount</th>
                                    <th class="p-4 w-20 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <template x-for="row in results" :key="row.ref + row.id">
                                    <tr class="hover:bg-indigo-50 transition group">
                                        <td class="p-4 text-sm text-gray-600 font-mono" x-text="formatDate(row.date)"></td>

                                        <td class="p-4 text-sm font-bold text-indigo-600 hover:underline cursor-pointer">
                                            <span x-text="row.ref"></span>
                                        </td>

                                        <td class="p-4">
                                            <span class="text-[10px] font-bold px-2 py-1 rounded uppercase"
                                                :class="{
                                                    'bg-green-100 text-green-700': row.type.includes('Sale') || row.type === 'Receipt',
                                                    'bg-red-100 text-red-700': row.type === 'Purchase' || row.type === 'Payment'
                                                }"
                                                x-text="row.type">
                                            </span>
                                        </td>

                                        <td class="p-4 text-sm text-gray-700" x-text="row.description"></td>

                                        <td class="p-4 text-right font-mono font-bold text-gray-800" x-text="'Rs. ' + Number(row.amount).toLocaleString('en-US', {minimumFractionDigits: 2})"></td>

                                        <td class="p-4 text-center">
                                            <button class="text-gray-400 hover:text-indigo-600 transition" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>

                                <tr x-show="!hasSearched">
                                    <td colspan="6" class="p-10 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-search-dollar text-4xl mb-3 opacity-20"></i>
                                            <p>Enter criteria and click "Get List" to search.</p>
                                        </div>
                                    </td>
                                </tr>

                                <tr x-show="hasSearched && results.length === 0" style="display: none;">
                                    <td colspan="6" class="p-10 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-ghost text-4xl mb-3 opacity-20"></i>
                                            <p>No transactions found matching your criteria.</p>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-gray-50 p-4 border-t border-gray-200 flex justify-end gap-6">
                        <div class="text-right">
                            <span class="block text-[10px] font-bold text-gray-400 uppercase">Sum of Transactions</span>
                            <span class="block text-xl font-bold text-indigo-900" x-text="'Rs. ' + totalSum"></span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script>
        function valueSearch() {
            return {
                loading: false,
                hasSearched: false,
                filters: {
                    type: 'all',
                    useDate: false,
                    dateFrom: new Date().toISOString().slice(0, 10),
                    dateTo: new Date().toISOString().slice(0, 10),
                    useValue: false,
                    valLower: '',
                    valUpper: ''
                },
                results: [],

                get totalSum() {
                    return this.results.reduce((sum, row) => sum + parseFloat(row.amount || 0), 0).toFixed(2);
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    return dateStr.substring(0, 10);
                },

                async performSearch() {
                    this.loading = true;
                    this.hasSearched = true;
                    this.results = [];

                    try {
                        const response = await fetch('/values/search', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.filters)
                        });

                        if (!response.ok) throw new Error('Search failed');

                        this.results = await response.json();
                    } catch (error) {
                        console.error(error);
                        alert('Error fetching results. Please try again.');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</body>

</html>