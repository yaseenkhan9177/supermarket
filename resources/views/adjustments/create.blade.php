<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Adjustment | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-900 font-sans text-gray-200" x-data="adjustmentForm()">

    <nav class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-cyan-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-clipboard-check text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-gray-900 leading-none tracking-tight">
                        OwnStore <span class="text-cyan-600">PRO</span>
                    </h1>
                    <span class="text-xs text-gray-500 font-medium mt-0.5">Stock Adjustment & Audit</span>
                </div>
            </div>
            <div>
                <a href="/dashboard" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1400px] pb-32">

        {{-- Alerts --}}
        @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
            <p class="font-bold">Success</p>
            <p>{{ session('success') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
            <p class="font-bold">Error</p>
            <p>{{ session('error') }}</p>
        </div>
        @endif

        <form action="/adjustments/store" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

                <div class="lg:col-span-1 bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
                    <div class="flex items-center gap-2 mb-4 border-b border-gray-700 pb-2">
                        <i class="fas fa-info-circle text-cyan-400"></i>
                        <h3 class="text-white font-bold">Audit Details</h3>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Adjustment #</label>
                            <input type="text" name="adjustment_no" value="ADJ-{{ date('Ymd') }}-{{ rand(10,99) }}" readonly class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm font-mono text-cyan-400">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Date</label>
                                <input type="date" name="adjustment_date" value="{{ date('Y-m-d') }}" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-gray-200">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Type</label>
                                <select name="type" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-gray-200 focus:ring-2 focus:ring-cyan-500 outline-none">
                                    <option>Correction</option>
                                    <option>Opening Stock</option>
                                    <option>Damage / Loss</option>
                                    <option>Theft</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Description / Reason</label>
                            <textarea name="description" placeholder="e.g. Monthly Stock Take" rows="2" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-gray-200"></textarea>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white p-6 rounded-xl text-gray-800 shadow-lg">
                    <div class="flex items-center justify-between mb-4 border-b pb-2">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-barcode text-cyan-600"></i>
                            <h3 class="font-bold text-gray-900">Inventory Items</h3>
                        </div>
                        <button type="button" @click="addRow()" class="bg-cyan-100 text-cyan-700 hover:bg-cyan-200 px-4 py-2 rounded text-sm font-bold transition">
                            <i class="fas fa-plus mr-1"></i> Add Manual Row
                        </button>
                    </div>

                    <div class="overflow-x-auto min-h-[300px]">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-xs font-bold text-gray-500 uppercase bg-gray-50 border-b">
                                    <th class="p-3 w-10 text-center">#</th>
                                    <th class="p-3 w-40">Barcode/Code</th>
                                    <th class="p-3">Item Name</th>
                                    <th class="p-3 w-24 text-center">System Qty</th>
                                    <th class="p-3 w-32 text-center text-cyan-700 bg-cyan-50">Physical Qty</th>
                                    <th class="p-3 w-24 text-center">Diff</th>
                                    <th class="p-3 w-10 text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(row, index) in rows" :key="index">
                                    <tr class="border-b hover:bg-gray-50 transition group">
                                        <td class="p-3 text-center text-gray-400" x-text="index + 1"></td>

                                        <td class="p-3">
                                            <input type="text" x-model="row.barcode" @keydown.enter.prevent="fetchProduct(index)" class="w-full p-2 border rounded bg-gray-50 focus:bg-white focus:ring-2 focus:ring-cyan-500 text-sm" placeholder="Scan...">
                                        </td>

                                        <td class="p-3">
                                            <input type="text" x-model="row.name" class="w-full p-2 border rounded bg-gray-50 text-sm" readonly tabindex="-1">
                                            <input type="hidden" :name="`items[${index}][product_id]`" x-model="row.product_id">
                                            <input type="hidden" :name="`items[${index}][name]`" x-model="row.name">
                                        </td>

                                        <td class="p-3 text-center">
                                            <input type="text" :name="`items[${index}][system_stock]`" x-model="row.system_stock" class="w-full p-2 border-0 bg-transparent text-center text-sm font-medium text-gray-500" readonly tabindex="-1">
                                        </td>

                                        <td class="p-3 bg-cyan-50">
                                            <input type="number" :name="`items[${index}][physical_stock]`" x-model="row.physical_stock" class="w-full p-2 border border-cyan-300 rounded text-center text-lg font-bold text-gray-900 focus:ring-2 focus:ring-cyan-500">
                                        </td>

                                        <td class="p-3 text-center font-bold"
                                            :class="calculateDiff(row) < 0 ? 'text-red-500' : (calculateDiff(row) > 0 ? 'text-green-500' : 'text-gray-300')">
                                            <span x-text="calculateDiff(row) > 0 ? '+' + calculateDiff(row) : calculateDiff(row)"></span>
                                        </td>

                                        <td class="p-3 text-center">
                                            <button type="button" @click="removeRow(index)" class="text-gray-300 hover:text-red-500 transition">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>

                        <div x-show="rows.length === 0" class="p-8 text-center text-gray-400 border-2 border-dashed border-gray-200 rounded-lg mt-4">
                            <i class="fas fa-barcode text-4xl mb-2 opacity-50"></i>
                            <p>Scan an item to begin adjustment</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="fixed bottom-0 left-0 w-full bg-white border-t p-4 shadow-[0_-5px_15px_rgba(0,0,0,0.1)] z-40">
                <div class="container mx-auto max-w-[1400px] flex justify-end items-center gap-4">
                    <div class="text-sm text-gray-500 mr-4">
                        Items Scanned: <span class="font-bold text-gray-900" x-text="rows.length"></span>
                    </div>
                    <button type="button" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="px-8 py-3 bg-cyan-600 text-white font-bold rounded shadow hover:bg-cyan-700 transition transform hover:-translate-y-1">
                        <i class="fas fa-save mr-2"></i> Update Stock
                    </button>
                </div>
            </div>

        </form>
    </div>

    <script>
        function adjustmentForm() {
            return {
                rows: [],

                fetchProduct(index) {
                    // Simulation: 
                    // In real app: fetch(`/api/products?barcode=${this.rows[index].barcode}`)
                    // The user asked to simulate for now or assume simple lookup 
                    // But for a robust test we might need a real endpoint later.
                    // For now, I'll mock a product to ensure the UI flow works.
                    const mock = {
                        id: 55,
                        name: 'Almara (Cabinet) A', // From legacy screenshot
                        stock: 12 // System says 12
                    };

                    this.rows[index].product_id = mock.id;
                    this.rows[index].name = mock.name;
                    this.rows[index].system_stock = mock.stock;
                    this.rows[index].physical_stock = mock.stock; // Default to matching system

                    // Auto add next row
                    if (index === this.rows.length - 1) {
                        this.addRow();
                    }
                },

                addRow() {
                    this.rows.push({
                        product_id: '',
                        barcode: '',
                        name: '',
                        system_stock: 0,
                        physical_stock: 0
                    });

                    // Focus logic would go here
                },

                removeRow(index) {
                    this.rows.splice(index, 1);
                },

                calculateDiff(row) {
                    return parseInt(row.physical_stock || 0) - parseInt(row.system_stock || 0);
                }
            }
        }
    </script>
</body>

</html>