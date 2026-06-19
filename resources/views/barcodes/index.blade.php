<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Generator | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.0/dist/JsBarcode.all.min.js"></script>
</head>

<body class="bg-gray-900 font-sans text-gray-200" x-data="barcodeSystem()">

    <nav class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-teal-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-barcode text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-gray-900 leading-none tracking-tight">
                        OwnStore <span class="text-teal-600">PRO</span>
                    </h1>
                    <span class="text-xs text-gray-500 font-medium mt-0.5">Barcode & Label Generator</span>
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

        <form action="/barcodes/print" method="POST" target="_blank">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                <div class="lg:col-span-4 space-y-6">

                    <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 shadow-lg">
                        <div class="flex items-center gap-2 mb-4 border-b border-gray-700 pb-2">
                            <i class="fas fa-cog text-teal-400"></i>
                            <h3 class="text-white font-bold">Label Configuration</h3>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Barcode Standard</label>
                                <select name="barcode_type" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-gray-300 outline-none">
                                    <option value="C128">Code 128 (Standard)</option>
                                    <option value="C39">Code 39</option>
                                    <option value="QR">QR Code</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Row Capacity</label>
                                    <input type="number" name="labels_per_row" value="2" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-center">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Gap (mm)</label>
                                    <input type="number" name="gap" value="2" class="w-full bg-gray-900 border border-gray-600 rounded p-2 text-sm text-center">
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-2">
                                <input type="checkbox" name="show_price" checked class="w-4 h-4 text-teal-600 rounded bg-gray-700 border-gray-600">
                                <label class="text-sm text-gray-400">Print Price on Label</label>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="show_expiry" class="w-4 h-4 text-teal-600 rounded bg-gray-700 border-gray-600">
                                <label class="text-sm text-gray-400">Print Expiry Date</label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-lg text-gray-800">
                        <div class="flex items-center gap-2 mb-4 border-b pb-2">
                            <i class="fas fa-search text-teal-600"></i>
                            <h3 class="font-bold text-gray-900">Add Product to Queue</h3>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Scan / Search Product</label>
                                <div class="relative">
                                    <input type="text" x-model="searchQuery" @keydown.enter.prevent="searchProduct" placeholder="Barcode or Name..." class="w-full pl-8 p-2 border border-gray-300 rounded focus:border-teal-500 outline-none">
                                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                                </div>
                            </div>

                            <div class="border-2 border-dashed border-teal-200 bg-teal-50 rounded-lg p-4 flex flex-col items-center justify-center min-h-[120px]" x-show="previewItem.name">
                                <h4 class="font-bold text-xs text-gray-700" x-text="previewItem.name">Item Name</h4>
                                <div class="my-1">
                                    <canvas id="barcodePreview"></canvas>
                                </div>
                                <span class="font-bold text-sm text-gray-900" x-text="'Rs. ' + previewItem.price">100.00</span>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Print Qty</label>
                                    <input type="number" x-model="qty" class="w-full p-2 border border-gray-300 rounded text-center font-bold">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Expiry (Opt)</label>
                                    <input type="date" x-model="expiry" class="w-full p-2 border border-gray-300 rounded text-xs">
                                </div>
                            </div>

                            <button type="button" @click="addToQueue" class="w-full py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded shadow transition">
                                <i class="fas fa-plus-circle mr-2"></i> Add to Queue
                            </button>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-8 bg-white p-6 rounded-xl border border-gray-200 shadow-lg text-gray-800">
                    <div class="flex items-center justify-between mb-4 border-b pb-2">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-list-ol text-gray-400"></i>
                            <h3 class="font-bold text-gray-900">Printing Queue</h3>
                        </div>
                        <span class="bg-teal-100 text-teal-800 text-xs font-bold px-2 py-1 rounded" x-text="queue.length + ' Items'"></span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-xs font-bold text-gray-500 uppercase bg-gray-50 border-b">
                                    <th class="p-3">Product Name</th>
                                    <th class="p-3 w-32">Barcode</th>
                                    <th class="p-3 w-24">Price</th>
                                    <th class="p-3 w-24">Expiry</th>
                                    <th class="p-3 w-20 text-center">Copies</th>
                                    <th class="p-3 w-16 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(item, index) in queue" :key="index">
                                    <tr class="border-b hover:bg-teal-50 transition group">
                                        <td class="p-3 text-sm font-medium" x-text="item.name">
                                            <input type="hidden" :name="`items[${index}][id]`" x-model="item.id">
                                            <input type="hidden" :name="`items[${index}][name]`" x-model="item.name">
                                        </td>
                                        <td class="p-3 text-xs font-mono text-gray-500" x-text="item.barcode">
                                            <input type="hidden" :name="`items[${index}][barcode]`" x-model="item.barcode">
                                        </td>
                                        <td class="p-3 text-sm" x-text="item.price">
                                            <input type="hidden" :name="`items[${index}][price]`" x-model="item.price">
                                        </td>
                                        <td class="p-3 text-xs text-gray-500" x-text="item.expiry || '-'">
                                            <input type="hidden" :name="`items[${index}][expiry]`" x-model="item.expiry">
                                        </td>
                                        <td class="p-3">
                                            <input type="number" :name="`items[${index}][qty]`" x-model="item.qty" class="w-full p-1 border rounded text-center text-sm font-bold text-teal-600">
                                        </td>
                                        <td class="p-3 text-center">
                                            <button type="button" @click="remove(index)" class="text-gray-300 hover:text-red-500 transition">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="queue.length === 0">
                                    <td colspan="6" class="p-8 text-center text-gray-400 italic">
                                        Queue is empty. Scan items to add labels.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="fixed bottom-0 left-0 w-full bg-white border-t p-4 shadow-[0_-5px_15px_rgba(0,0,0,0.1)] z-40">
                <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Total Labels to Print: <span class="font-bold text-gray-900" x-text="totalLabels">0</span>
                    </div>

                    <div class="flex gap-4">
                        <button type="button" @click="queue = []" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded hover:bg-gray-300">
                            Clear All
                        </button>
                        <button type="submit" class="px-8 py-3 bg-teal-600 text-white font-bold rounded shadow hover:bg-teal-700 transition transform hover:-translate-y-1">
                            <i class="fas fa-print mr-2"></i> Print Labels
                        </button>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <script>
        function barcodeSystem() {
            return {
                searchQuery: '',
                qty: 1,
                expiry: '',
                previewItem: {
                    name: '',
                    barcode: '',
                    price: ''
                },
                queue: [],

                searchProduct() {
                    // Simulate AJAX fetch
                    // In real app: fetch(`/api/products?q=${this.searchQuery}`)
                    const mockProduct = {
                        id: 101,
                        name: 'Cool Cola 1.5L',
                        barcode: this.searchQuery || '8964000123',
                        price: '140.00'
                    };

                    this.previewItem = mockProduct;

                    // Generate Live Preview Barcode
                    this.$nextTick(() => {
                        JsBarcode("#barcodePreview", mockProduct.barcode, {
                            format: "CODE128",
                            lineColor: "#000",
                            width: 1.5,
                            height: 40,
                            displayValue: true
                        });
                    });
                },

                addToQueue() {
                    if (!this.previewItem.name) return;

                    this.queue.push({
                        ...this.previewItem,
                        qty: this.qty,
                        expiry: this.expiry
                    });

                    // Reset inputs but keep focus on search
                    this.searchQuery = '';
                    this.previewItem = {
                        name: '',
                        barcode: '',
                        price: ''
                    };
                    this.qty = 1;
                },

                remove(index) {
                    this.queue.splice(index, 1);
                },

                get totalLabels() {
                    return this.queue.reduce((acc, item) => acc + parseInt(item.qty), 0);
                }
            }
        }
    </script>
</body>

</html>