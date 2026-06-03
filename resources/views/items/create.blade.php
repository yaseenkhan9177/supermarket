@extends('layouts.admin')

@section('title', 'Add New Product')

@section('content')

<div class="max-w-7xl mx-auto" x-data="itemManager()">

    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Create Product</h1>
            <p class="text-slate-400 text-sm mt-1">Add a new inventory item to your catalog.</p>
        </div>
        <div class="flex gap-3">
            <button type="button" @click="showImportModal = true" class="px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold shadow-lg shadow-emerald-900/50 transition transform hover:-translate-y-0.5">
                <i class="fas fa-file-excel mr-2"></i> Import from Excel
            </button>
            <a href="/items" class="px-5 py-2.5 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-800 hover:text-white transition font-bold text-sm">Cancel</a>
            <button form="itemForm" type="submit" class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold shadow-lg shadow-blue-900/50 transition transform hover:-translate-y-0.5">
                <i class="fas fa-save mr-2"></i> Save Product
            </button>
        </div>
    </div>

    <form id="itemForm" action="/items/store" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-7 space-y-6">

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>

                    <h3 class="text-white font-bold text-lg mb-6 flex items-center gap-2">
                        <i class="fas fa-cube text-blue-500"></i> Product Identity
                    </h3>

                    <div class="space-y-5">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-8">
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Product Name <span class="text-red-500">*</span></label>
                                <input type="text" name="description" x-model="name" @input="generateBarcode()" placeholder="e.g. Nestle Milkpak 1L" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition font-medium">
                            </div>
                            <div class="col-span-4">
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Barcode / Code</label>
                                <div class="relative">
                                    <input type="text" name="code" x-model="code" placeholder="Scan or Auto..." class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-3 pr-10 py-3 text-white font-mono focus:border-blue-500 outline-none">
                                    <button type="button" @click="forceGenerate()" class="absolute right-2 top-2 text-slate-500 hover:text-white transition" title="Regenerate">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Item Type</label>
                                <select name="item_type" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-slate-300 focus:border-blue-500 outline-none">
                                    <option value="Inventory">Inventory Item</option>
                                    <option value="Service">Service (No Stock)</option>
                                    <option value="Package">Package / Deal</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Department</label>
                                <select name="department_id" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-slate-300 focus:border-blue-500 outline-none">
                                    <option value="">Select Dept...</option>
                                    <option value="1">Grocery</option>
                                    <option value="2">Dairy</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-4 pt-2">
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-800 px-3 py-2 rounded-lg border border-slate-700 hover:border-blue-500 transition">
                                <input type="checkbox" name="hide_sale_price" class="rounded text-blue-500 bg-slate-900 border-slate-600 focus:ring-0">
                                <span class="text-xs font-bold text-slate-300 uppercase">Hide Price</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-800 px-3 py-2 rounded-lg border border-slate-700 hover:border-blue-500 transition">
                                <input type="checkbox" name="open_price" class="rounded text-blue-500 bg-slate-900 border-slate-600 focus:ring-0">
                                <span class="text-xs font-bold text-slate-300 uppercase">Open Price</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative group">
                    <div class="absolute top-0 left-0 w-1 h-full bg-purple-500"></div>
                    <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                        <i class="fas fa-image text-purple-500"></i> Product Image
                    </h3>

                    <div class="border-2 border-dashed border-slate-700 rounded-xl p-8 text-center hover:border-purple-500 hover:bg-slate-800/50 transition cursor-pointer relative" @click="$refs.fileInput.click()">
                        <input type="file" name="photo" x-ref="fileInput" class="hidden" @change="previewImage">

                        <template x-if="!imageUrl">
                            <div>
                                <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-cloud-upload-alt text-2xl text-slate-500"></i>
                                </div>
                                <p class="text-slate-300 font-medium">Click to upload or drag and drop</p>
                                <p class="text-slate-500 text-xs mt-1">SVG, PNG, JPG (Max 800x800px)</p>
                            </div>
                        </template>

                        <template x-if="imageUrl">
                            <div class="relative">
                                <img :src="imageUrl" class="h-48 mx-auto rounded-lg object-contain">
                                <button type="button" @click.stop="imageUrl = null" class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1 shadow hover:bg-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <div class="lg:col-span-5 space-y-6">

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-green-500"></div>
                    <h3 class="text-white font-bold text-lg mb-6 flex items-center gap-2">
                        <i class="fas fa-tag text-green-500"></i> Pricing Engine
                    </h3>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Cost Price (CP)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-3 text-slate-500 text-sm">Rs.</span>
                                    <input type="number" step="0.01" name="cost_price" x-model="cost" class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-10 pr-4 py-2.5 text-white font-mono focus:border-green-500 outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-green-400 uppercase mb-1">Sale Price (SP)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-3 text-green-500 text-sm">Rs.</span>
                                    <input type="number" step="0.01" name="sale_price" x-model="sale" class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-10 pr-4 py-2.5 text-white font-bold font-mono focus:border-green-500 outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-950 rounded-lg p-3 border border-slate-800 flex justify-between items-center">
                            <span class="text-xs text-slate-400 font-bold uppercase">Estimated Margin</span>
                            <span class="text-sm font-mono font-bold" :class="margin >= 0 ? 'text-green-400' : 'text-red-400'" x-text="margin + '%'"></span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Whole Sale Price</label>
                                <input type="number" name="wholesale_price" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-slate-300 text-sm focus:border-green-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Trade Rate</label>
                                <input type="number" name="trade_rate" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-slate-300 text-sm focus:border-green-500 outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-orange-500"></div>
                    <h3 class="text-white font-bold text-lg mb-6 flex items-center gap-2">
                        <i class="fas fa-boxes text-orange-500"></i> Stock Control
                    </h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Min Stock (Alert)</label>
                            <input type="number" name="min_stock" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:border-orange-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Max Stock</label>
                            <input type="number" name="max_stock" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:border-orange-500 outline-none">
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-800">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Opening Stock (On Hand)</label>
                        <input type="number" name="on_hand" placeholder="0" class="w-full bg-yellow-900/20 border border-yellow-700/50 rounded-lg px-4 py-3 text-yellow-400 font-bold focus:border-yellow-500 outline-none">
                    </div>
                </div>

                <div x-data="{ open: false }" class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl">
                    <button type="button" @click="open = !open" class="w-full p-4 flex justify-between items-center text-left">
                        <span class="text-sm font-bold text-slate-400 flex items-center gap-2">
                            <i class="fas fa-book text-slate-500"></i> GL Accounts Integration
                        </span>
                        <i class="fas fa-chevron-down text-slate-600 transition" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open" x-collapse class="p-6 pt-0 border-t border-slate-800 space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Sales Income Account</label>
                            <select name="sales_account_id" class="w-full bg-slate-950 border border-slate-700 rounded px-2 py-2 text-xs text-slate-300">
                                <option value="1">40100 - Sales Revenue</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">COGS Account</label>
                            <select name="cogs_account_id" class="w-full bg-slate-950 border border-slate-700 rounded px-2 py-2 text-xs text-slate-300">
                                <option value="2">50100 - Cost of Goods Sold</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

    <!-- Excel Import Modal -->
    <div x-show="showImportModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         x-cloak>
        <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-2xl p-6 shadow-2xl relative overflow-hidden" @click.away="showImportModal = false">
            <div class="absolute top-0 left-0 w-full h-1 bg-emerald-500"></div>
            
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-white font-extrabold text-xl flex items-center gap-2">
                    <i class="fas fa-file-excel text-emerald-500"></i> Bulk Import Items
                </h3>
                <button type="button" @click="showImportModal = false" class="text-slate-400 hover:text-white transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form @submit.prevent="submitImport">
                <div class="space-y-5">
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Upload an `.xls` or `.xlsx` spreadsheet exported from your old POS system. Columns will be matched by name automatically.
                    </p>

                    <!-- File drop area -->
                    <div class="border-2 border-dashed border-slate-700 rounded-xl p-8 text-center hover:border-emerald-500 hover:bg-slate-800/30 transition cursor-pointer relative"
                         @click="$refs.excelInput.click()">
                        <input type="file" name="excel_file" x-ref="excelInput" class="hidden" accept=".xls,.xlsx" @change="excelFileSelected">
                        
                        <template x-if="!excelFile">
                            <div>
                                <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-500">
                                    <i class="fas fa-file-upload text-2xl"></i>
                                </div>
                                <p class="text-slate-300 font-medium">Select Excel spreadsheet</p>
                                <p class="text-slate-500 text-xs mt-1">Only .xls or .xlsx formats are accepted</p>
                            </div>
                        </template>

                        <template x-if="excelFile">
                            <div class="flex items-center justify-center gap-3 bg-slate-950 p-4 rounded-lg border border-slate-800" @click.stop>
                                <i class="fas fa-file-excel text-3xl text-emerald-500"></i>
                                <div class="text-left">
                                    <p class="text-slate-200 font-semibold" x-text="excelFile.name"></p>
                                    <p class="text-slate-500 text-xs" x-text="(excelFile.size / 1024).toFixed(1) + ' KB'"></p>
                                </div>
                                <button type="button" @click.stop="excelFile = null" class="ml-auto text-slate-500 hover:text-red-400 transition p-1">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Progress state -->
                    <div x-show="importing" class="flex flex-col items-center justify-center py-6 space-y-3">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-emerald-500"></div>
                        <p class="text-slate-300 font-medium text-sm">Processing items and importing to database...</p>
                    </div>

                    <!-- Result summary card -->
                    <div x-show="importResult" class="space-y-4">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-emerald-950/30 border border-emerald-500/30 rounded-xl p-4 text-center">
                                <div class="text-2xl mb-1">✅</div>
                                <div class="text-2xl font-black text-emerald-400" x-text="importResult.inserted">0</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Inserted</div>
                            </div>
                            <div class="bg-blue-950/30 border border-blue-500/30 rounded-xl p-4 text-center">
                                <div class="text-2xl mb-1">🔄</div>
                                <div class="text-2xl font-black text-blue-400" x-text="importResult.updated">0</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Updated</div>
                            </div>
                            <div class="bg-yellow-950/30 border border-yellow-500/30 rounded-xl p-4 text-center">
                                <div class="text-2xl mb-1">⚠️</div>
                                <div class="text-2xl font-black text-yellow-400" x-text="importResult.skipped_count">0</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Skipped</div>
                            </div>
                        </div>

                        <!-- Skipped logs list -->
                        <div x-show="importResult && importResult.skipped && importResult.skipped.length > 0" class="bg-slate-950 border border-slate-800 rounded-xl p-4 max-h-48 overflow-y-auto space-y-1">
                            <div class="text-[11px] font-bold text-yellow-500 uppercase tracking-wider mb-2 flex items-center gap-1">
                                <i class="fas fa-exclamation-triangle"></i> Skip Log / Warnings:
                            </div>
                            <template x-for="log in importResult.skipped">
                                <div class="text-xs font-mono text-slate-400 border-b border-slate-900/60 pb-1 flex items-start gap-2">
                                    <span class="text-yellow-500 font-bold">•</span>
                                    <span x-text="log"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Footer buttons -->
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-800">
                    <button type="button" @click="closeImportModal" class="px-5 py-2 rounded-lg border border-slate-700 text-slate-300 hover:bg-slate-800 transition font-bold text-sm">
                        Close
                    </button>
                    <button type="submit" x-show="!importResult" :disabled="!excelFile || importing" 
                            class="px-6 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold transition shadow-lg shadow-emerald-950/50">
                        <i class="fas fa-upload mr-2"></i> Start Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function itemManager() {
        return {
            imageUrl: null,
            cost: 0,
            sale: 0,
            name: '',
            code: '',
            showImportModal: false,
            excelFile: null,
            importing: false,
            importResult: null,

            get margin() {
                if (this.sale > 0 && this.cost > 0) {
                    return (((this.sale - this.cost) / this.sale) * 100).toFixed(1);
                }
                return 0;
            },

            previewImage(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imageUrl = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            },

            // Auto-Generate Barcode Logic
            generateBarcode() {
                if (this.name.length > 2 && this.code === '') {
                    this.code = this.getRandomCode();
                }
            },

            forceGenerate() {
                this.code = this.getRandomCode();
            },

            getRandomCode() {
                // Generate random unique-ish 8 digit number
                return Math.floor(10000000 + Math.random() * 90000000).toString();
            },

            excelFileSelected(event) {
                const file = event.target.files[0];
                if (file) {
                    this.excelFile = file;
                    this.importResult = null;
                }
            },

            submitImport() {
                if (!this.excelFile) return;
                this.importing = true;
                this.importResult = null;

                const formData = new FormData();
                formData.append('excel_file', this.excelFile);
                
                const csrfToken = document.querySelector('input[name="_token"]').value;

                fetch('/items/import', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(async response => {
                    const contentType = response.headers.get('content-type');
                    const isJson = contentType && contentType.includes('application/json');
                    
                    if (!response.ok) {
                        if (isJson) {
                            const err = await response.json();
                            throw new Error(err.message || 'Import failed with server error.');
                        } else {
                            const text = await response.text();
                            console.error('Non-JSON error response:', text);
                            throw new Error('Server returned an error (status ' + response.status + '). Please check your internet connection or the server logs.');
                        }
                    }
                    
                    if (isJson) {
                        return response.json();
                    } else {
                        throw new Error('Expected JSON response but received: ' + (contentType || 'none'));
                    }
                })
                .then(data => {
                    this.importing = false;
                    this.importResult = data;
                    this.excelFile = null;
                    if (this.$refs.excelInput) {
                        this.$refs.excelInput.value = '';
                    }
                })
                .catch(error => {
                    this.importing = false;
                    alert(error.message || 'Import failed. Please make sure the Excel file structure is correct and contains valid headers.');
                    console.error(error);
                });
            },

            closeImportModal() {
                const hasUpdates = this.importResult && (this.importResult.inserted > 0 || this.importResult.updated > 0);
                this.showImportModal = false;
                this.excelFile = null;
                this.importResult = null;
                if (this.$refs.excelInput) {
                    this.$refs.excelInput.value = '';
                }
                if (hasUpdates) {
                    window.location.href = '/items';
                }
            }
        }
    }
</script>

@endsection