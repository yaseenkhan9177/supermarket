@extends('layouts.admin')

@section('title', 'Add New Product')

@section('content')

<div class="max-w-7xl mx-auto space-y-6" x-data="itemManager()">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900/90 border border-slate-800 p-6 rounded-2xl shadow-2xl backdrop-blur-md relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center text-blue-400 text-xl font-bold shadow-lg shadow-blue-900/20">
                <i class="fas fa-box-open"></i>
            </div>
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                    Create Product
                    <span class="text-xs px-2.5 py-0.5 rounded-full bg-blue-500/20 text-blue-400 border border-blue-500/30 font-medium">New Catalog Item</span>
                </h1>
                <p class="text-slate-400 text-sm mt-0.5">Define product details, set up pricing, and configure stock controls.</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button type="button" @click="showImportModal = true" class="px-4 py-2.5 rounded-xl bg-emerald-600/20 border border-emerald-500/40 text-emerald-300 hover:bg-emerald-600 hover:text-white font-bold text-sm shadow-lg shadow-emerald-950/40 transition duration-200 flex items-center gap-2">
                <i class="fas fa-file-excel"></i> Import from Excel
            </button>
            <a href="/items" class="px-4 py-2.5 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 hover:text-white transition font-bold text-sm flex items-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Cancel
            </a>
            <button form="itemForm" type="submit" class="px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow-lg shadow-blue-900/50 transition duration-200 transform hover:-translate-y-0.5 flex items-center gap-2">
                <i class="fas fa-save"></i> Save Product
            </button>
        </div>
    </div>

    <!-- Quick Navigation / Progress Indicator -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <a href="#section-identity" class="bg-slate-900/60 border border-slate-800/80 hover:border-blue-500/50 p-3 rounded-xl flex items-center gap-3 transition group">
            <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 group-hover:bg-blue-600 group-hover:text-white flex items-center justify-center font-bold text-xs transition">1</div>
            <div class="text-left">
                <p class="text-xs font-bold text-slate-300 group-hover:text-white transition">Product Identity</p>
                <p class="text-[10px] text-slate-500">Name, Type & Dept</p>
            </div>
        </a>
        <a href="#section-pricing" class="bg-slate-900/60 border border-slate-800/80 hover:border-emerald-500/50 p-3 rounded-xl flex items-center gap-3 transition group">
            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 group-hover:bg-emerald-600 group-hover:text-white flex items-center justify-center font-bold text-xs transition">2</div>
            <div class="text-left">
                <p class="text-xs font-bold text-slate-300 group-hover:text-white transition">Pricing Engine</p>
                <p class="text-[10px] text-slate-500">CP, SP & Margins</p>
            </div>
        </a>
        <a href="#section-stock" class="bg-slate-900/60 border border-slate-800/80 hover:border-orange-500/50 p-3 rounded-xl flex items-center gap-3 transition group">
            <div class="w-8 h-8 rounded-lg bg-orange-500/10 text-orange-400 group-hover:bg-orange-600 group-hover:text-white flex items-center justify-center font-bold text-xs transition">3</div>
            <div class="text-left">
                <p class="text-xs font-bold text-slate-300 group-hover:text-white transition">Stock Control</p>
                <p class="text-[10px] text-slate-500">Alerts & Opening Qty</p>
            </div>
        </a>
        <a href="#section-image" class="bg-slate-900/60 border border-slate-800/80 hover:border-purple-500/50 p-3 rounded-xl flex items-center gap-3 transition group">
            <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 group-hover:bg-purple-600 group-hover:text-white flex items-center justify-center font-bold text-xs transition">4</div>
            <div class="text-left">
                <p class="text-xs font-bold text-slate-300 group-hover:text-white transition">Media & Image</p>
                <p class="text-[10px] text-slate-500">Upload Photo</p>
            </div>
        </a>
    </div>

    <form id="itemForm" action="/items/store" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Left Column: Identity & Media -->
            <div class="lg:col-span-7 space-y-6">

                <!-- Product Identity Card -->
                <div id="section-identity" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1.5 h-full bg-blue-500"></div>

                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800/80">
                        <h3 class="text-white font-bold text-lg flex items-center gap-2.5">
                            <i class="fas fa-cube text-blue-500"></i> Product Identity
                        </h3>
                        <span class="text-[11px] font-semibold text-blue-400 bg-blue-500/10 px-2.5 py-1 rounded-lg border border-blue-500/20">Essential</span>
                    </div>

                    <div class="space-y-5">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 md:col-span-8">
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Product Name <span class="text-red-500">*</span></label>
                                <input type="text" name="description" x-model="name" @input="generateBarcode()" placeholder="e.g. Nestle Milkpak 1L" required class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition font-medium">
                            </div>
                            <div class="col-span-12 md:col-span-4">
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Barcode / Code</label>
                                <div class="relative">
                                    <input type="text" name="code" x-model="code" placeholder="Scan or Auto..." class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-3 pr-10 py-3 text-white font-mono focus:border-blue-500 outline-none">
                                    <button type="button" @click="forceGenerate()" class="absolute right-2 top-2.5 text-slate-500 hover:text-white p-1 transition" title="Regenerate Barcode">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Searchable Item Type & Department Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-searchable-select 
                                    name="item_type" 
                                    id="item_type" 
                                    label="Item Type" 
                                    placeholder="Search or add type..." 
                                    search-url="/item-types/search" 
                                    create-url="/item-types" 
                                    value-key="name" 
                                    display-key="name" 
                                    selected-id="Inventory"
                                    selected-name="Inventory"
                                    entity-label="Item Type" 
                                    icon="fa-layer-group" />
                            </div>
                            <div>
                                <x-searchable-select 
                                    name="department_id" 
                                    id="department_id" 
                                    label="Department" 
                                    placeholder="Search or add dept..." 
                                    search-url="/departments/search" 
                                    create-url="/departments" 
                                    value-key="id" 
                                    display-key="name" 
                                    entity-label="Department" 
                                    icon="fa-sitemap" />
                            </div>
                        </div>

                        <!-- Checkboxes & Settings -->
                        <div class="flex flex-wrap gap-4 pt-2">
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-950 px-3.5 py-2.5 rounded-xl border border-slate-800 hover:border-slate-700 transition">
                                <input type="checkbox" name="hide_sale_price" class="rounded text-blue-500 bg-slate-900 border-slate-700 focus:ring-0">
                                <span class="text-xs font-bold text-slate-300 uppercase">Hide Price</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-950 px-3.5 py-2.5 rounded-xl border border-slate-800 hover:border-slate-700 transition">
                                <input type="checkbox" name="open_price" class="rounded text-blue-500 bg-slate-900 border-slate-700 focus:ring-0">
                                <span class="text-xs font-bold text-slate-300 uppercase">Open Price</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Product Image Upload Card -->
                <div id="section-image" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative group">
                    <div class="absolute top-0 left-0 w-1.5 h-full bg-purple-500"></div>
                    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800/80">
                        <h3 class="text-white font-bold text-lg flex items-center gap-2.5">
                            <i class="fas fa-image text-purple-500"></i> Product Image
                        </h3>
                        <span class="text-[11px] font-semibold text-purple-400 bg-purple-500/10 px-2.5 py-1 rounded-lg border border-purple-500/20">Optional</span>
                    </div>

                    <div class="border-2 border-dashed border-slate-700 rounded-xl p-8 text-center hover:border-purple-500 hover:bg-slate-800/40 transition cursor-pointer relative" @click="$refs.fileInput.click()">
                        <input type="file" name="photo" x-ref="fileInput" class="hidden" accept="image/*" @change="previewImage">

                        <template x-if="!imageUrl">
                            <div>
                                <div class="w-16 h-16 bg-slate-800/80 border border-slate-700/60 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400 group-hover:scale-110 transition">
                                    <i class="fas fa-cloud-upload-alt text-2xl"></i>
                                </div>
                                <p class="text-slate-300 font-medium text-sm">Click to upload or drag & drop</p>
                                <p class="text-slate-500 text-xs mt-1">PNG, JPG, WEBP (Max 800x800px)</p>
                            </div>
                        </template>

                        <template x-if="imageUrl">
                            <div class="relative inline-block">
                                <img :src="imageUrl" class="h-48 mx-auto rounded-xl object-contain shadow-lg border border-slate-700">
                                <button type="button" @click.stop="imageUrl = null" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1.5 shadow-lg hover:bg-red-600 transition" title="Remove image">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Right Column: Pricing, Stock & GL Accounts -->
            <div class="lg:col-span-5 space-y-6">

                <!-- Pricing Engine Card -->
                <div id="section-pricing" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1.5 h-full bg-emerald-500"></div>
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800/80">
                        <h3 class="text-white font-bold text-lg flex items-center gap-2.5">
                            <i class="fas fa-tag text-emerald-500"></i> Pricing Engine
                        </h3>
                        <span class="text-[11px] font-semibold text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-lg border border-emerald-500/20">Financials</span>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Cost Price (CP)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-3 text-slate-500 text-xs font-bold">Rs.</span>
                                    <input type="number" step="0.01" name="cost_price" x-model="cost" class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-10 pr-3 py-2.5 text-white font-mono text-sm focus:border-emerald-500 outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-emerald-400 uppercase mb-1">Sale Price (SP)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-3 text-emerald-500 text-xs font-bold">Rs.</span>
                                    <input type="number" step="0.01" name="sale_price" x-model="sale" class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-10 pr-3 py-2.5 text-white font-bold font-mono text-sm focus:border-emerald-500 outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-950 rounded-xl p-3.5 border border-slate-800/80 flex justify-between items-center">
                            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Estimated Profit Margin</span>
                            <span class="text-sm font-mono font-bold px-2.5 py-1 rounded-lg" :class="margin >= 0 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'" x-text="margin + '%'"></span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Wholesale Price</label>
                                <input type="number" step="0.01" name="wholesale_price" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-slate-300 text-sm focus:border-emerald-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Trade Rate</label>
                                <input type="number" step="0.01" name="trade_rate" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-slate-300 text-sm focus:border-emerald-500 outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Control Card -->
                <div id="section-stock" class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1.5 h-full bg-orange-500"></div>
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-800/80">
                        <h3 class="text-white font-bold text-lg flex items-center gap-2.5">
                            <i class="fas fa-boxes text-orange-500"></i> Stock Control
                        </h3>
                        <span class="text-[11px] font-semibold text-orange-400 bg-orange-500/10 px-2.5 py-1 rounded-lg border border-orange-500/20">Inventory</span>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Min Stock (Alert)</label>
                            <input type="number" name="min_stock" placeholder="0" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-orange-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Max Stock</label>
                            <input type="number" name="max_stock" placeholder="0" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white text-sm focus:border-orange-500 outline-none">
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-800">
                        <label class="block text-[10px] font-bold text-amber-400 uppercase mb-1">Opening Stock (On Hand)</label>
                        <input type="number" name="on_hand" placeholder="0" class="w-full bg-amber-950/20 border border-amber-500/40 rounded-xl px-4 py-3 text-amber-400 font-bold focus:border-amber-500 outline-none transition">
                    </div>
                </div>

                <!-- GL Accounts Integration Card -->
                <div x-data="{ open: false }" class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl overflow-hidden">
                    <button type="button" @click="open = !open" class="w-full p-4 flex justify-between items-center text-left hover:bg-slate-800/40 transition">
                        <span class="text-sm font-bold text-slate-300 flex items-center gap-2.5">
                            <i class="fas fa-book text-slate-500"></i> GL Accounts Integration
                        </span>
                        <i class="fas fa-chevron-down text-slate-500 transition duration-200" :class="open ? 'rotate-180 text-blue-400' : ''"></i>
                    </button>

                    <div x-show="open" x-collapse class="p-6 pt-0 border-t border-slate-800 space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Sales Income Account</label>
                            <select name="sales_account_id" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-300 focus:border-blue-500 outline-none">
                                <option value="1">40100 - Sales Revenue</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">COGS Account</label>
                            <select name="cogs_account_id" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-300 focus:border-blue-500 outline-none">
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
                <h3 class="text-white font-extrabold text-xl flex items-center gap-2.5">
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

            generateBarcode() {
                if (this.name.length > 2 && this.code === '') {
                    this.code = this.getRandomCode();
                }
            },

            forceGenerate() {
                this.code = this.getRandomCode();
            },

            getRandomCode() {
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
                
                const csrfToken = document.querySelector('input[name="_token"]')?.value || '';

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
                            throw new Error('Server returned an error (status ' + response.status + ').');
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
                    alert(error.message || 'Import failed. Please check your file.');
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