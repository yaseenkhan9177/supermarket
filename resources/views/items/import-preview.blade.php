@extends('layouts.admin')

@section('title', 'Import Items — Preview & Upload')

@section('content')
<div class="max-w-7xl mx-auto" x-data="importPreviewManager()">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Import Items</h1>
            <p class="text-gray-500 dark:text-slate-400 text-sm mt-1">Preview and import inventory items in bulk with real-time feedback.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('items.download-sample') }}" class="px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold shadow-lg transition transform hover:-translate-y-0.5 flex items-center gap-2">
                <i class="fas fa-download"></i> Download Sample Excel
            </a>
            <a href="{{ route('items.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 dark:border-slate-700 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition font-bold text-sm">
                Back to Catalog
            </a>
        </div>
    </div>

    <!-- STEP 1: UPLOAD & PASTE AREA -->
    <div x-show="step === 1" class="space-y-6">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-slate-700 rounded-2xl p-8 shadow-xl">
            <!-- Tab Switcher -->
            <div class="flex gap-4 mb-6">
                <button type="button"
                        @click="activeTab = 'upload'"
                        :class="activeTab === 'upload' ? 'bg-blue-600 text-white font-bold shadow-md hover:bg-blue-500' : 'bg-transparent border border-gray-300 dark:border-slate-700 text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-800'"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm transition focus:outline-none">
                    📁 Upload File
                </button>
                <button type="button"
                        @click="activeTab = 'paste'"
                        :class="activeTab === 'paste' ? 'bg-blue-600 text-white font-bold shadow-md hover:bg-blue-500' : 'bg-transparent border border-gray-300 dark:border-slate-700 text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-800'"
                        class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm transition focus:outline-none">
                    📋 Paste from Excel
                </button>
            </div>

            <!-- Tab 1: Upload File -->
            <div x-show="activeTab === 'upload'" class="space-y-6">
                <h3 class="text-gray-900 dark:text-white font-bold text-lg mb-4 flex items-center gap-2">
                    <i class="fas fa-file-excel text-emerald-500"></i> Upload Spreadsheet
                </h3>
                
                <div class="border-2 border-dashed border-gray-300 dark:border-slate-700 rounded-xl p-12 text-center hover:border-emerald-500 hover:bg-gray-50 dark:hover:bg-slate-800/30 transition cursor-pointer relative"
                     @click="$refs.fileInput.click()"
                     @dragover.prevent="dragOver = true"
                     @dragleave.prevent="dragOver = false"
                     @drop.prevent="handleDrop($event)"
                     :class="dragOver ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/20' : ''">
                    
                    <input type="file" x-ref="fileInput" class="hidden" accept=".xlsx,.xls,.csv" @change="handleFileSelect">
                    
                    <div x-show="!selectedFile" class="space-y-4">
                        <div class="w-20 h-20 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center mx-auto text-gray-400 dark:text-slate-300">
                            <i class="fas fa-cloud-upload-alt text-3xl"></i>
                        </div>
                        <p class="text-gray-700 dark:text-slate-300 font-semibold text-lg">Click or drag & drop file to import</p>
                        <p class="text-gray-500 dark:text-slate-500 text-xs">Supported Formats: .xlsx, .xls, .csv (Max 64MB)</p>
                    </div>

                    <div x-show="selectedFile" class="flex items-center justify-center gap-4 bg-gray-50 dark:bg-slate-900 p-6 rounded-xl border border-gray-200 dark:border-slate-700 max-w-lg mx-auto" @click.stop>
                        <i class="fas fa-file-excel text-4xl text-emerald-500"></i>
                        <div class="text-left flex-grow">
                            <p class="text-gray-800 dark:text-slate-200 font-bold text-sm truncate" x-text="selectedFile ? selectedFile.name : ''"></p>
                            <p class="text-gray-500 dark:text-slate-500 text-xs" x-text="selectedFile ? (selectedFile.size / 1024 / 1024).toFixed(2) + ' MB' : ''"></p>
                        </div>
                        <button type="button" @click="selectedFile = null" class="text-gray-400 hover:text-red-500 transition p-2">
                            <i class="fas fa-trash-alt text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Upload Action & Spinner -->
                <div class="mt-8 flex justify-end">
                    <button type="button" 
                            @click="uploadAndPreview" 
                            :disabled="!selectedFile || loading"
                            class="px-6 py-3 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold transition shadow-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <template x-if="loading">
                            <span class="inline-block animate-spin border-2 border-white border-t-transparent rounded-full w-4 h-4 mr-2"></span>
                        </template>
                        <i class="fas fa-eye" x-show="!loading"></i>
                        <span x-text="loading ? 'Processing Spreadsheet...' : 'Upload & Preview'"></span>
                    </button>
                </div>
            </div>

            <!-- Tab 2: Paste from Excel -->
            <div x-show="activeTab === 'paste'" class="space-y-6" x-cloak>
                <h3 class="text-gray-900 dark:text-white font-bold text-lg mb-4 flex items-center gap-2">
                    <i class="fas fa-paste text-blue-500"></i> Paste Data from Excel / Google Sheets
                </h3>

                <div class="p-4 bg-blue-50 dark:bg-slate-900/50 border border-blue-200 dark:border-slate-800 rounded-xl text-sm text-blue-800 dark:text-blue-300">
                    <p class="font-bold flex items-center gap-2 mb-1">
                        <i class="fas fa-info-circle"></i> Instructions:
                    </p>
                    <p>Open your CSV or Excel file &rarr; Select All (Ctrl+A) &rarr; Copy (Ctrl+C) &rarr; Paste in the text area below.</p>
                </div>

                <div>
                    <textarea x-model="pastedData"
                              class="w-full min-h-[300px] p-4 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-slate-100 rounded-xl border border-gray-300 dark:border-slate-700 font-mono text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none transition shadow-inner placeholder-gray-400 dark:placeholder-slate-600"
                              placeholder="Paste your copied Excel/CSV data here..."></textarea>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mt-4">
                    <p class="text-xs text-gray-500 dark:text-slate-400 max-w-xl">
                        💡 <strong>Tip:</strong> If uploading isn't working, use this method instead. Open your file in Excel or Google Sheets, press Ctrl+A to select all, Ctrl+C to copy, then paste here.
                    </p>
                    <button type="button" 
                            @click="parsePastedData" 
                            class="px-6 py-3 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold transition shadow-lg flex items-center gap-2 whitespace-nowrap">
                        <i class="fas fa-eye"></i> Preview Data
                    </button>
                </div>
            </div>
        </div>

        <!-- Guide & Instructions -->
        <div class="bg-gray-50 dark:bg-slate-800/40 rounded-xl p-6 border border-gray-200 dark:border-slate-800">
            <h4 class="text-gray-800 dark:text-slate-300 font-bold text-sm uppercase tracking-wider mb-4">Column Guidelines & Validation Rules</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-slate-850">
                    <span class="font-bold text-gray-900 dark:text-white">Name*</span> (Required)
                    <p class="text-gray-500 dark:text-slate-400 text-xs mt-1">Item description or title. Cannot be empty.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-slate-850">
                    <span class="font-bold text-gray-900 dark:text-white">Type*</span> (Required)
                    <p class="text-gray-500 dark:text-slate-400 text-xs mt-1">Valid values: <code class="bg-gray-100 dark:bg-slate-900 px-1 py-0.5 rounded text-indigo-500">inventory</code>, <code class="bg-gray-100 dark:bg-slate-900 px-1 py-0.5 rounded text-indigo-500">service</code>, <code class="bg-gray-100 dark:bg-slate-900 px-1 py-0.5 rounded text-indigo-500">package</code>.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-slate-850">
                    <span class="font-bold text-gray-900 dark:text-white">SKU/Code</span>
                    <p class="text-gray-500 dark:text-slate-400 text-xs mt-1">Unique identifier. If duplicate exists, the item is updated.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-slate-850">
                    <span class="font-bold text-gray-900 dark:text-white">Category</span>
                    <p class="text-gray-500 dark:text-slate-400 text-xs mt-1">Product Category. Will be created if it does not exist.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-slate-850">
                    <span class="font-bold text-gray-900 dark:text-white">Pricing & Stock</span>
                    <p class="text-gray-500 dark:text-slate-400 text-xs mt-1">Sale Price, Cost Price, and Opening Stock must be numeric.</p>
                </div>
                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-100 dark:border-slate-850">
                    <span class="font-bold text-gray-900 dark:text-white">Barcodes</span>
                    <p class="text-gray-500 dark:text-slate-400 text-xs mt-1">Missing barcodes will be auto-generated during import.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 2: PREVIEW TABLE -->
    <div x-show="step === 2" class="space-y-6" x-cloak>
        <!-- Summary Stats Card -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 bg-white dark:bg-gray-800 p-6 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-md">
            <div class="text-center p-3 border-r border-gray-100 dark:border-slate-700/50">
                <span class="text-sm text-gray-500 dark:text-slate-400 font-semibold block uppercase">Total Rows</span>
                <span class="text-3xl font-black text-gray-900 dark:text-white" x-text="summary.total">0</span>
            </div>
            <div class="text-center p-3 border-r border-gray-100 dark:border-slate-700/50">
                <span class="text-sm text-emerald-600 font-semibold block uppercase">Ready ✓</span>
                <span class="text-3xl font-black text-emerald-600" x-text="summary.ready">0</span>
            </div>
            <div class="text-center p-3 border-r border-gray-100 dark:border-slate-700/50">
                <span class="text-sm text-yellow-500 font-semibold block uppercase">Warnings ⚠</span>
                <span class="text-3xl font-black text-yellow-500" x-text="summary.warnings">0</span>
            </div>
            <div class="text-center p-3">
                <span class="text-sm text-red-500 font-semibold block uppercase">Errors ✗</span>
                <span class="text-3xl font-black text-red-500" x-text="summary.errors">0</span>
            </div>
        </div>

        <!-- Preview Table -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-xl overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-slate-700/50 flex justify-between items-center">
                <h3 class="text-gray-900 dark:text-white font-bold text-lg">Sheet Preview</h3>
                <div class="text-xs text-gray-500 dark:text-slate-400">
                    Showing <span x-text="pageStart">1</span> to <span x-text="pageEnd">50</span> of <span x-text="rows.length"></span> rows
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 dark:bg-slate-750 text-gray-500 uppercase font-semibold text-[11px] tracking-wider border-b border-gray-100 dark:border-slate-700/50">
                        <tr>
                            <th class="p-4 w-12 text-center">#</th>
                            <th class="p-4">Name</th>
                            <th class="p-4 w-28">Type</th>
                            <th class="p-4 w-32">SKU/Code</th>
                            <th class="p-4 w-36">Category</th>
                            <th class="p-4 w-28 text-right">Price</th>
                            <th class="p-4 w-28 text-right">Cost</th>
                            <th class="p-4 w-24 text-center">Stock</th>
                            <th class="p-4 w-36 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700 text-sm">
                        <template x-for="(row, idx) in paginatedRows" :key="row.index">
                            <tr :class="{
                                     'bg-red-50/40 dark:bg-red-950/10 hover:bg-red-50/60': row.status === 'error',
                                     'bg-yellow-50/40 dark:bg-yellow-950/10 hover:bg-yellow-50/60': row.status === 'warning',
                                     'hover:bg-gray-50 dark:hover:bg-slate-750/30': row.status === 'ready'
                                 }" class="transition">
                                <td class="p-4 text-center text-gray-400 font-mono font-bold" x-text="row.index"></td>
                                <td class="p-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-800 dark:text-slate-200" x-text="row.name"></span>
                                        <template x-if="row.issues.length > 0">
                                            <div class="text-[11px] text-red-500 font-medium mt-1 flex flex-col gap-0.5">
                                                <template x-for="issue in row.issues">
                                                    <span class="flex items-center gap-1">
                                                        <i class="fas fa-info-circle text-[9px]"></i> <span x-text="issue"></span>
                                                    </span>
                                                </template>
                                            </div>
                                        </template>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="px-2.5 py-1 rounded text-xs font-bold uppercase tracking-wider block text-center"
                                          :class="{
                                              'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400': row.type === 'inventory',
                                              'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400': row.type === 'service',
                                              'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400': row.type === 'package'
                                          }" x-text="row.type"></span>
                                </td>
                                <td class="p-4 font-mono text-xs font-bold text-gray-600 dark:text-slate-400" x-text="row.sku || 'Auto-Gen'"></td>
                                <td class="p-4 text-gray-600 dark:text-slate-400" x-text="row.category || '-'"></td>
                                <td class="p-4 text-right font-mono font-bold text-gray-900 dark:text-white" x-text="'Rs.' + parseFloat(row.price).toFixed(2)"></td>
                                <td class="p-4 text-right font-mono text-gray-600 dark:text-slate-400" x-text="'Rs.' + parseFloat(row.cost).toFixed(2)"></td>
                                <td class="p-4 text-center font-bold text-gray-700 dark:text-slate-300" x-text="row.stock"></td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                                          :class="{
                                              'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400': row.status === 'ready',
                                              'bg-yellow-100 text-yellow-800 dark:bg-yellow-950/30 dark:text-yellow-400': row.status === 'warning',
                                              'bg-red-100 text-red-800 dark:bg-red-950/30 dark:text-red-400': row.status === 'error'
                                          }">
                                        <i class="fas" :class="{
                                            'fa-check-circle': row.status === 'ready',
                                            'fa-exclamation-triangle': row.status === 'warning',
                                            'fa-times-circle': row.status === 'error'
                                        }"></i>
                                        <span x-text="row.status"></span>
                                    </span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Table Pagination & Footer Actions -->
            <div class="p-6 border-t border-gray-100 dark:border-slate-700/50 bg-gray-50 dark:bg-slate-750/10 flex justify-between items-center">
                <div class="flex gap-2">
                    <button type="button" 
                            @click="prevPage" 
                            :disabled="currentPage === 1"
                            class="px-4 py-2 border border-gray-300 dark:border-slate-700 text-gray-600 dark:text-slate-400 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-chevron-left mr-1"></i> Prev
                    </button>
                    <button type="button" 
                            @click="nextPage" 
                            :disabled="currentPage === totalPages"
                            class="px-4 py-2 border border-gray-300 dark:border-slate-700 text-gray-600 dark:text-slate-400 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        Next <i class="fas fa-chevron-right ml-1"></i>
                    </button>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="resetToUpload" class="px-5 py-2.5 rounded-lg border border-gray-300 dark:border-slate-700 text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-slate-800 font-bold text-sm">
                        Cancel & Upload New
                    </button>
                    <button type="button" 
                            @click="startImport" 
                            :disabled="summary.ready + summary.warnings === 0"
                            class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold transition shadow-lg flex items-center gap-2">
                        <i class="fas fa-upload"></i> Import Valid Rows (<span x-text="summary.ready + summary.warnings"></span>)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- STEP 3: IMPORT PROGRESS & SUMMARY -->
    <div x-show="step === 3" class="space-y-6" x-cloak>
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-slate-700 rounded-2xl p-8 shadow-xl">
            
            <!-- Importing Animation State -->
            <div x-show="importing" class="space-y-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-gray-900 dark:text-white font-black text-xl flex items-center gap-2">
                        <span class="inline-block animate-spin border-3 border-blue-500 border-t-transparent rounded-full w-5 h-5"></span>
                        Importing Items...
                    </h3>
                    <span class="text-sm font-mono font-bold text-blue-500" x-text="progressPercent + '%'">0%</span>
                </div>

                <div class="w-full bg-gray-100 dark:bg-slate-700 rounded-full h-3.5 overflow-hidden">
                    <div class="bg-blue-600 h-3.5 rounded-full transition-all duration-350" :style="'width: ' + progressPercent + '%'"></div>
                </div>

                <div class="text-sm text-gray-500 dark:text-slate-400 text-center font-mono">
                    Processed <span x-text="processedRows">0</span> of <span x-text="validRowsToImport.length">0</span> rows.
                </div>
            </div>

            <!-- Complete Results Summary -->
            <div x-show="!importing" class="space-y-8 animate-fade-in">
                <div class="text-center space-y-2">
                    <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-950/40 rounded-full flex items-center justify-center mx-auto text-emerald-500 text-4xl shadow-inner">
                        ✓
                    </div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white">Import Complete!</h2>
                    <p class="text-gray-500 dark:text-slate-400 text-sm">Spreadsheet records successfully process-completed on the server.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 max-w-xl mx-auto">
                    <div class="bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-500/20 rounded-xl p-6 text-center">
                        <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest block mb-1">Imported</span>
                        <span class="text-3xl font-black text-emerald-600" x-text="results.imported">0</span>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-950/20 border border-yellow-500/20 rounded-xl p-6 text-center">
                        <span class="text-[10px] font-bold text-yellow-600 uppercase tracking-widest block mb-1">Skipped / Duplicates</span>
                        <span class="text-3xl font-black text-yellow-500" x-text="results.skipped">0</span>
                    </div>
                    <div class="bg-red-50 dark:bg-red-950/20 border border-red-500/20 rounded-xl p-6 text-center">
                        <span class="text-[10px] font-bold text-red-600 uppercase tracking-widest block mb-1">Failed</span>
                        <span class="text-3xl font-black text-red-500" x-text="results.failed">0</span>
                    </div>
                </div>

                <div class="flex justify-center gap-4 pt-4 border-t border-gray-100 dark:border-slate-700/50">
                    <button type="button" @click="resetToUpload" class="px-6 py-3 rounded-lg border border-gray-300 dark:border-slate-700 text-gray-700 dark:text-slate-300 hover:bg-gray-100 dark:hover:bg-slate-800 transition font-bold text-sm">
                        Import Another File
                    </button>
                    <a href="{{ route('items.index') }}" class="px-6 py-3 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold shadow-lg transition transform hover:-translate-y-0.5">
                        View Imported Items
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function importPreviewManager() {
        return {
            step: 1,
            selectedFile: null,
            pastedData: '',
            activeTab: 'upload',
            dragOver: false,
            loading: false,
            rows: [],
            summary: { ready: 0, warnings: 0, errors: 0, total: 0 },
            currentPage: 1,
            pageSize: 50,
            importing: false,
            processedRows: 0,
            results: { imported: 0, skipped: 0, failed: 0 },
           csrfToken: '{{ csrf_token() }}',

            get paginatedRows() {
                const start = (this.currentPage - 1) * this.pageSize;
                const end = start + this.pageSize;
                return this.rows.slice(start, end);
            },

            get totalPages() {
                return Math.ceil(this.rows.length / this.pageSize) || 1;
            },

            get pageStart() {
                return (this.currentPage - 1) * this.pageSize + 1;
            },

            get pageEnd() {
                return Math.min(this.currentPage * this.pageSize, this.rows.length);
            },

            get validRowsToImport() {
                return this.rows.filter(r => r.status !== 'error');
            },

            get progressPercent() {
                if (this.validRowsToImport.length === 0) return 0;
                return Math.round((this.processedRows / this.validRowsToImport.length) * 100);
            },

            handleFileSelect(event) {
                const file = event.target.files[0];
                if (file) {
                    this.selectedFile = file;
                }
            },

            handleDrop(event) {
                this.dragOver = false;
                const file = event.dataTransfer.files[0];
                if (file) {
                    this.selectedFile = file;
                }
            },

            uploadAndPreview() {
                if (!this.selectedFile) return;

                this.loading = true;
                const formData = new FormData();
                formData.append('excel_file', this.selectedFile);

               fetch("/items/upload-preview", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: formData
                })
                .then(async res => {
                    if (!res.ok) {
                        const err = await res.json();
                        throw new Error(err.message || 'Failed to read spreadsheet file');
                    }
                    return res.json();
                })
                .then(data => {
                    this.rows = data.rows;
                    this.summary = data.summary;
                    this.currentPage = 1;
                    this.step = 2;
                })
                .catch(err => {
                    alert(err.message || 'File upload preview failed.');
                    console.error(err);
                })
                .finally(() => {
                    this.loading = false;
                });
            },

            parseCSV(text) {
                const lines = text.trim().split(/\r?\n/);
                return lines.map(line => {
                    // Handle quoted fields with commas/tabs inside
                    const result = [];
                    let current = '';
                    let inQuotes = false;
                    for (let i = 0; i < line.length; i++) {
                        if (line[i] === '"') {
                            inQuotes = !inQuotes;
                        } else if (line[i] === ',' && !inQuotes) {
                            result.push(current.trim());
                            current = '';
                        } else if (line[i] === '\t' && !inQuotes) {
                            // Handle tab-separated (Excel default copy format)
                            result.push(current.trim());
                            current = '';
                        } else {
                            current += line[i];
                        }
                    }
                    result.push(current.trim());
                    return result;
                });
            },

            parsePastedData() {
                const text = this.pastedData;
                if (!text.trim()) {
                    alert('Please paste your data first.');
                    return;
                }

                const parsed = this.parseCSV(text);
                if (parsed.length < 2) {
                    alert('No data found. Make sure you copied at least the header row and one data row.');
                    return;
                }

                const headers = parsed[0].map(h => String(h).toLowerCase().trim());
                
                const findHeader = (options) => {
                    for (let opt of options) {
                        const idx = headers.indexOf(opt.toLowerCase().trim());
                        if (idx !== -1) {
                            return idx;
                        }
                    }
                    return false;
                };

                const map = {
                    name: findHeader(['name', 'description', 'item name', 'item_name', 'title']),
                    bar_code: findHeader(['bar_code', 'barcode', 'code', 'bar code', 'sku/code', 'sku', 'sku_code']),
                    type: findHeader(['type', 'item_type', 'item type']),
                    packing: findHeader(['packing', 'category', 'department', 'dept']),
                    cost: findHeader(['cost', 'cost_price', 'cost price', 'cost_rate', 'cost rate']),
                    sale: findHeader(['sale', 'sale_price', 'sale price', 'sale_rate', 'sale rate', 'price']),
                    trade: findHeader(['trade', 'trade_rate', 'trade rate', 'trade_price', 'trade price']),
                    h_price: findHeader(['h_price', 'wholesale_price', 'wholesale price', 'wholesale', 'h price']),
                    stock: findHeader(['stock', 'quantity', 'qty', 'opening stock', 'opening_stock', 'on_hand', 'on hand']),
                    min: findHeader(['min', 'min_stock', 'min stock', 'minimum', 'min stock level', 'min_stock_level']),
                    max: findHeader(['max', 'max_stock', 'max stock', 'maximum']),
                    disc: findHeader(['disc', 'discount', 'discount_percent', 'discount percent']),
                    openprice: findHeader(['openprice', 'open_price', 'open price']),
                    taxrate: findHeader(['taxrate', 'taxprate', 'tax_rate', 'tax rate', 'tax']),
                    itemid: findHeader(['itemid', 'item_id', 'imported_id', 'imported id', 'id']),
                    unit: findHeader(['unit', 'uom', 'measure']),
                    description: findHeader(['description', 'desc', 'details', 'item description']),
                };

                if (map.name === false) {
                    alert('Required column "Name" or "Description" not found in the pasted data.');
                    return;
                }

                const previewRows = [];
                const summary = { ready: 0, warnings: 0, errors: 0, total: 0 };

                for (let i = 1; i < parsed.length; i++) {
                    const row = parsed[i];
                    // Skip empty rows
                    if (row.length === 0 || (row.length === 1 && !row[0].trim())) {
                        continue;
                    }

                    const rowNumber = i + 1;

                    const name = (map.name !== false && row[map.name] !== undefined) ? String(row[map.name]).trim() : '';
                    const type = (map.type !== false && row[map.type] !== undefined) ? String(row[map.type]).toLowerCase().trim() : 'inventory';
                    const sku = (map.bar_code !== false && row[map.bar_code] !== undefined) ? String(row[map.bar_code]).trim() : '';
                    const category = (map.packing !== false && row[map.packing] !== undefined) ? String(row[map.packing]).trim() : '';
                    const unit = (map.unit !== false && row[map.unit] !== undefined) ? String(row[map.unit]).trim() : '';
                    
                    const priceRaw = (map.sale !== false && row[map.sale] !== undefined && row[map.sale] !== '') ? row[map.sale] : 0;
                    const costRaw = (map.cost !== false && row[map.cost] !== undefined && row[map.cost] !== '') ? row[map.cost] : 0;
                    const stockRaw = (map.stock !== false && row[map.stock] !== undefined && row[map.stock] !== '') ? row[map.stock] : 0;
                    const minStockRaw = (map.min !== false && row[map.min] !== undefined && row[map.min] !== '') ? row[map.min] : 0;

                    const tradeRaw = (map.trade !== false && row[map.trade] !== undefined && row[map.trade] !== '') ? row[map.trade] : 0;
                    const wholesaleRaw = (map.h_price !== false && row[map.h_price] !== undefined && row[map.h_price] !== '') ? row[map.h_price] : 0;
                    const maxStockRaw = (map.max !== false && row[map.max] !== undefined && row[map.max] !== '') ? row[map.max] : 0;
                    const discountRaw = (map.disc !== false && row[map.disc] !== undefined && row[map.disc] !== '') ? row[map.disc] : 0;
                    const openPriceRaw = (map.openprice !== false && row[map.openprice] !== undefined && row[map.openprice] !== '') ? row[map.openprice] : false;
                    const taxRateRaw = (map.taxrate !== false && row[map.taxrate] !== undefined && row[map.taxrate] !== '') ? row[map.taxrate] : 0;
                    const importedId = (map.itemid !== false && row[map.itemid] !== undefined && row[map.itemid] !== '') ? row[map.itemid] : null;
                    const description = (map.description !== false && row[map.description] !== undefined) ? String(row[map.description]).trim() : '';

                    let status = 'ready';
                    const issues = [];

                    // Required validations
                    if (!name) {
                        status = 'error';
                        issues.push('Item Name is required.');
                    }

                    const validTypes = ['inventory', 'service', 'package'];
                    if (!validTypes.includes(type)) {
                        status = 'error';
                        issues.push("Invalid type '" + type + "'. Must be one of: inventory, service, package.");
                    }

                    // Helper to check if a value is numeric
                    const isNumeric = (val) => {
                        if (val === undefined || val === null || val === '') return true;
                        const str = String(val).trim();
                        if (str === '') return true;
                        return !isNaN(str) && !isNaN(parseFloat(str));
                    };

                    // Numeric checks
                    if (!isNumeric(priceRaw)) {
                        status = 'error';
                        issues.push('Price must be numeric.');
                    }
                    if (!isNumeric(costRaw)) {
                        status = 'error';
                        issues.push('Cost must be numeric.');
                    }
                    if (!isNumeric(stockRaw)) {
                        status = 'error';
                        issues.push('Stock must be numeric.');
                    }

                    // Warnings
                    if (status !== 'error') {
                        if (!category) {
                            status = 'warning';
                            issues.push('Category is empty.');
                        }
                    }

                    // Update counts
                    if (status === 'error') {
                        summary.errors++;
                    } else if (status === 'warning') {
                        summary.warnings++;
                    } else {
                        summary.ready++;
                    }
                    summary.total++;

                    // Parse open price boolean
                    let openPrice = false;
                    if (openPriceRaw) {
                        const opStr = String(openPriceRaw).toLowerCase().trim();
                        openPrice = (opStr === 'true' || opStr === '1' || opStr === 'yes' || opStr === 'y');
                    }

                    previewRows.push({
                        index: rowNumber,
                        name: name,
                        type: type,
                        sku: sku,
                        category: category,
                        unit: unit,
                        price: isNumeric(priceRaw) ? (parseFloat(priceRaw) || 0) : 0,
                        cost: isNumeric(costRaw) ? (parseFloat(costRaw) || 0) : 0,
                        stock: isNumeric(stockRaw) ? (parseFloat(stockRaw) || 0) : 0,
                        min_stock: isNumeric(minStockRaw) ? (parseFloat(minStockRaw) || 0) : 0,
                        trade_rate: isNumeric(tradeRaw) ? (parseFloat(tradeRaw) || 0) : 0,
                        sale_whole: isNumeric(wholesaleRaw) ? (parseFloat(wholesaleRaw) || 0) : 0,
                        max_stock: isNumeric(maxStockRaw) ? (parseFloat(maxStockRaw) || 0) : 0,
                        discount_percent: isNumeric(discountRaw) ? (parseFloat(discountRaw) || 0) : 0,
                        open_price: openPrice,
                        tax_rate: isNumeric(taxRateRaw) ? (parseFloat(taxRateRaw) || 0) : 0,
                        imported_id: importedId,
                        description: description,
                        status: status,
                        issues: issues
                    });
                }

                this.rows = previewRows;
                this.summary = summary;
                this.currentPage = 1;
                this.step = 2;
            },

            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                }
            },

            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                }
            },

            resetToUpload() {
                this.selectedFile = null;
                this.pastedData = '';
                this.rows = [];
                this.summary = { ready: 0, warnings: 0, errors: 0, total: 0 };
                this.currentPage = 1;
                this.step = 1;
                this.importing = false;
                this.processedRows = 0;
                this.results = { imported: 0, skipped: 0, failed: 0 };
                if (this.$refs.fileInput) {
                    this.$refs.fileInput.value = '';
                }
            },

            async startImport() {
                const validRows = this.validRowsToImport;
                if (validRows.length === 0) return;

                this.step = 3;
                this.importing = true;
                this.processedRows = 0;
                this.results = { imported: 0, skipped: 0, failed: 0 };

                // Split into chunks of 100
                const chunkSize = 100;
                const chunks = [];
                for (let i = 0; i < validRows.length; i += chunkSize) {
                    chunks.push(validRows.slice(i, i + chunkSize));
                }

                // Process chunks sequentially
                for (let i = 0; i < chunks.length; i++) {
                    const chunk = chunks[i];
                    try {
                       const response = await fetch("/items/import-chunk", {
                         method: 'POST',
                         headers: {
                                 'Content-Type': 'application/json',
                                 'X-CSRF-TOKEN': this.csrfToken,
                                 'Accept': 'application/json'
    },
                            body: JSON.stringify({
                                rows: chunk,
                                chunk_index: i
                            })
                        });

                        if (!response.ok) {
                            throw new Error('Chunk import request failed');
                        }

                        const data = await response.json();
                        
                        if (data.success === false) {
                            this.results.failed += chunk.length;
                        } else {
                            this.results.imported += data.imported;
                            this.results.skipped += data.skipped;
                            this.results.failed += data.failed;
                        }
                    } catch (error) {
                        console.error('Import chunk failed:', error);
                        this.results.failed += chunk.length;
                    } finally {
                        this.processedRows += chunk.length;
                    }
                }

                this.importing = false;
            }
        };
    }
</script>
@endsection
