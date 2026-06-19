<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize Layout | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="bg-gray-100 font-sans text-gray-800" x-data="layoutEditor()">

    <nav class="bg-teal-900 border-b border-teal-800 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8 text-white">
        <div class="container mx-auto max-w-[1200px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-teal-700 flex items-center justify-center text-white shadow-md border border-teal-600">
                    <i class="fas fa-columns text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-white leading-none tracking-tight">
                        OwnStore <span class="text-teal-400">PRO</span>
                    </h1>
                    <span class="text-xs text-teal-300 font-medium mt-0.5">Report Layout Designer</span>
                </div>
            </div>
            <div>
                <a href="/reports" class="inline-flex items-center gap-2 px-5 py-2.5 bg-teal-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105 border border-teal-700">
                    <i class="fas fa-arrow-left"></i> Back to Reports
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1200px] pb-32">

        <form action="/reports/layout/store" method="POST" class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">
            @csrf

            <div class="bg-gray-50 p-6 border-b border-gray-200 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">1. Select Report Base</label>
                    <select x-model="selectedReport" @change="loadColumns()" class="w-full border border-gray-300 rounded-lg p-2.5 bg-white focus:ring-2 focus:ring-teal-500 outline-none">
                        <option value="sales">Daily Sales Summary</option>
                        <option value="ledger">Customer/Supplier Ledger</option>
                        <option value="stock">Inventory Valuation</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">2. Layout Name</label>
                    <input type="text" name="layout_name" placeholder="e.g. My Custom View" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-teal-500 outline-none" required>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-bold py-2.5 rounded-lg shadow transition transform hover:-translate-y-0.5 flex justify-center items-center gap-2">
                        <i class="fas fa-save"></i> Save Layout
                    </button>
                </div>
            </div>

            <div class="p-8 grid grid-cols-1 md:grid-cols-9 gap-4 items-start h-[500px]">

                <div class="md:col-span-4 h-full flex flex-col">
                    <h3 class="font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <span class="bg-gray-200 text-gray-600 text-xs px-2 py-0.5 rounded">Source</span> Available Fields
                    </h3>
                    <div class="flex-1 border border-gray-300 rounded-xl bg-gray-50 p-2 overflow-y-auto custom-scrollbar shadow-inner">
                        <template x-for="col in available" :key="col.id">
                            <div @click="moveToVisible(col)" class="bg-white p-3 mb-2 rounded border border-gray-200 cursor-pointer hover:border-teal-500 hover:bg-teal-50 hover:shadow-md transition group flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700 group-hover:text-teal-700" x-text="col.label"></span>
                                <i class="fas fa-plus-circle text-gray-300 group-hover:text-teal-500"></i>
                            </div>
                        </template>
                        <div x-show="available.length === 0" class="text-center py-10 text-gray-400 text-sm italic">
                            All fields added.
                        </div>
                    </div>
                </div>

                <div class="md:col-span-1 h-full flex flex-col justify-center items-center gap-4">
                    <i class="fas fa-exchange-alt text-gray-300 text-2xl"></i>
                </div>

                <div class="md:col-span-4 h-full flex flex-col">
                    <h3 class="font-bold text-teal-800 mb-3 flex items-center gap-2">
                        <span class="bg-teal-100 text-teal-700 text-xs px-2 py-0.5 rounded">Output</span> Visible Columns (In Order)
                    </h3>

                    <div class="flex-1 border-2 border-dashed border-teal-200 rounded-xl bg-white p-2 overflow-y-auto custom-scrollbar relative">
                        <input type="hidden" name="columns" :value="JSON.stringify(visible)">

                        <template x-for="(col, index) in visible" :key="col.id">
                            <div class="bg-teal-50 p-3 mb-2 rounded border border-teal-200 flex justify-between items-center group transition shadow-sm hover:shadow-md">
                                <div class="flex items-center gap-3">
                                    <span class="bg-teal-200 text-teal-800 text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full" x-text="index + 1"></span>
                                    <span class="text-sm font-bold text-teal-900" x-text="col.label"></span>
                                </div>
                                <div class="flex gap-1">
                                    <button type="button" @click="moveUp(index)" class="text-gray-400 hover:text-teal-600 px-1" title="Move Up" :disabled="index === 0">
                                        <i class="fas fa-chevron-up"></i>
                                    </button>
                                    <button type="button" @click="moveDown(index)" class="text-gray-400 hover:text-teal-600 px-1" title="Move Down" :disabled="index === visible.length - 1">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                    <button type="button" @click="moveToAvailable(col)" class="text-red-300 hover:text-red-500 ml-2 px-1" title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <div x-show="visible.length === 0" class="absolute inset-0 flex flex-col items-center justify-center text-gray-400 opacity-50 pointer-events-none">
                            <i class="fas fa-columns text-4xl mb-2"></i>
                            <p class="text-sm">Click items on the left to add them here.</p>
                        </div>
                    </div>
                </div>

            </div>
        </form>

    </div>

    <script>
        function layoutEditor() {
            return {
                selectedReport: 'sales',
                // Mock Data Sources
                sources: {
                    sales: [{
                            id: 'date',
                            label: 'Date'
                        },
                        {
                            id: 'inv_no',
                            label: 'Invoice Number'
                        },
                        {
                            id: 'cust_name',
                            label: 'Customer Name'
                        },
                        {
                            id: 'tax_id',
                            label: 'Customer NTN/Tax ID'
                        },
                        {
                            id: 'gross',
                            label: 'Gross Amount'
                        },
                        {
                            id: 'discount',
                            label: 'Discount Given'
                        },
                        {
                            id: 'tax',
                            label: 'Tax / VAT'
                        },
                        {
                            id: 'net',
                            label: 'Net Total'
                        },
                        {
                            id: 'user',
                            label: 'Salesman'
                        },
                        {
                            id: 'region',
                            label: 'Region / Area'
                        },
                        {
                            id: 'pay_mode',
                            label: 'Payment Mode'
                        },
                    ],
                    ledger: [{
                            id: 'date',
                            label: 'Date'
                        },
                        {
                            id: 'desc',
                            label: 'Description'
                        },
                        {
                            id: 'debit',
                            label: 'Debit'
                        },
                        {
                            id: 'credit',
                            label: 'Credit'
                        },
                        {
                            id: 'balance',
                            label: 'Running Balance'
                        },
                        {
                            id: 'chq_no',
                            label: 'Cheque No'
                        },
                    ],
                    stock: [{
                            id: 'item_code',
                            label: 'Item Code'
                        },
                        {
                            id: 'item_name',
                            label: 'Product Name'
                        },
                        {
                            id: 'category',
                            label: 'Category'
                        },
                        {
                            id: 'qty',
                            label: 'Quantity'
                        },
                        {
                            id: 'cost',
                            label: 'Unit Cost'
                        },
                        {
                            id: 'total_val',
                            label: 'Total Value'
                        },
                    ]
                },

                available: [],
                visible: [],

                init() {
                    this.loadColumns();
                },

                loadColumns() {
                    // Reset lists based on selection
                    const all = [...this.sources[this.selectedReport] || []];
                    // Default visible: First 4 items
                    this.visible = all.slice(0, 4);
                    // Default available: The rest
                    this.available = all.slice(4);
                },

                moveToVisible(col) {
                    this.visible.push(col);
                    this.available = this.available.filter(c => c.id !== col.id);
                },

                moveToAvailable(col) {
                    this.available.push(col);
                    this.visible = this.visible.filter(c => c.id !== col.id);
                },

                moveUp(index) {
                    if (index > 0) {
                        const item = this.visible[index];
                        this.visible.splice(index, 1);
                        this.visible.splice(index - 1, 0, item);
                    }
                },

                moveDown(index) {
                    if (index < this.visible.length - 1) {
                        const item = this.visible[index];
                        this.visible.splice(index, 1);
                        this.visible.splice(index + 1, 0, item);
                    }
                }
            }
        }
    </script>
</body>

</html>