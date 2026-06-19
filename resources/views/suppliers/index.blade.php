@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto" x-data="{
    showModal: false,
    editMode: false,
    form: { id: null, name: '', code: '', company: '', phone: '', address: '', opening_balance: '', category_id: '' },
    showImportModal: false,
    excelFile: null,
    importing: false,
    importResult: null,

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

        const csrfToken = document.querySelector('input[name=\'_token\']').value;

        fetch('{{ route('suppliers.import') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
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
                throw new Error('Expected JSON response but received non-JSON.');
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
            alert(error.message || 'Import failed.');
            console.error(error);
        });
    },

    closeImportModal() {
        const hasUpdates = this.importResult && this.importResult.inserted > 0;
        this.showImportModal = false;
        this.excelFile = null;
        this.importResult = null;
        if (this.$refs.excelInput) {
            this.$refs.excelInput.value = '';
        }
        if (hasUpdates) {
            window.location.reload();
        }
    }
}">

    {{-- Header Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <div class="flex flex-col justify-center">
            <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white">Suppliers</h1>
            <p class="text-slate-500 text-sm mb-4">Manage vendors and track payables.</p>
            <div class="flex gap-3 flex-wrap">
                <button @click="editMode = false; form = {id:null,name:'',code:'',company:'',phone:'',address:'',opening_balance:'',category_id:''}; showModal = true"
                    id="btn-add-supplier"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg flex items-center gap-2 text-sm transition">
                    <i class="fas fa-plus"></i> Add Supplier
                </button>
                <button @click="showImportModal = true; importResult = null; excelFile = null; if ($refs.excelInput) $refs.excelInput.value = ''"
                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg flex items-center gap-2 text-sm transition">
                    <i class="fas fa-file-excel"></i> Import Suppliers
                </button>
                <a href="{{ route('suppliers.sample-excel') }}"
                   class="px-5 py-2.5 bg-slate-600 hover:bg-slate-700 text-white font-bold rounded-xl shadow-lg flex items-center gap-2 text-sm transition">
                    <i class="fas fa-download"></i> Sample Excel
                </a>
                <a href="{{ route('supplier-returns.index') }}"
                   class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl shadow-lg flex items-center gap-2 text-sm transition">
                    <i class="fas fa-rotate-left"></i> Expiry Returns
                </a>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-red-700 rounded-2xl p-6 text-white shadow-xl">
            <p class="text-red-200 font-bold uppercase text-xs tracking-widest">Total Payable (Debt)</p>
            <h2 class="text-3xl font-extrabold mt-2">Rs. {{ number_format($totalPayable, 2) }}</h2>
            <p class="text-xs mt-2 opacity-80">Money we owe to suppliers</p>
        </div>

        <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-6 text-white shadow-xl">
            <p class="text-emerald-200 font-bold uppercase text-xs tracking-widest">Total Return Credit</p>
            <h2 class="text-3xl font-extrabold mt-2">Rs. {{ number_format(abs($totalAdvance), 2) }}</h2>
            <p class="text-xs mt-2 opacity-80">Credit we hold from returns</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Supplier Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                <tr>
                    <th class="p-4">Code</th>
                    <th class="p-4">Supplier Details</th>
                    <th class="p-4">Category</th>
                    <th class="p-4">Contact</th>
                    <th class="p-4 text-right">Balance</th>
                    <th class="p-4 text-right">Return Credit</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @foreach($suppliers as $supplier)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    <td class="p-4">
                        <span class="inline-block bg-indigo-100 text-indigo-700 text-xs font-mono font-bold px-2 py-1 rounded-lg">
                            {{ $supplier->code ?? '—' }}
                        </span>
                    </td>
                    <td class="p-4">
                        <span class="block font-bold text-slate-800 dark:text-white text-base">{{ $supplier->name }}</span>
                        <span class="text-xs text-slate-500">{{ $supplier->company_name ?? 'Individual' }}</span>
                    </td>
                    <td class="p-4">
                        @if($supplier->category)
                            <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                                {{ $supplier->category->name }}
                            </span>
                        @else
                            <span class="text-slate-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="p-4">
                        <div class="flex flex-col gap-1">
                            <span class="text-slate-600 dark:text-slate-300"><i class="fas fa-phone text-xs w-4"></i> {{ $supplier->phone ?? '—' }}</span>
                            @if($supplier->address)
                                <span class="text-slate-500 text-xs truncate max-w-[150px]"><i class="fas fa-map-marker-alt text-xs w-4"></i> {{ $supplier->address }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="p-4 text-right">
                        @if($supplier->current_balance > 0)
                            <span class="font-bold text-xs text-red-600 bg-red-100 px-2 py-0.5 rounded-full">Payable</span>
                            <div class="font-bold text-red-600 mt-1">Rs. {{ number_format($supplier->current_balance, 2) }}</div>
                        @elseif($supplier->current_balance < 0)
                            <span class="font-bold text-xs text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">Credit</span>
                            <div class="font-bold text-emerald-600 mt-1">Rs. {{ number_format(abs($supplier->current_balance), 2) }}</div>
                        @else
                            <span class="font-bold text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">Settled</span>
                            <div class="font-bold text-slate-400 mt-1">—</div>
                        @endif
                    </td>
                    <td class="p-4 text-right">
                        @if($supplier->return_credit > 0)
                            <span class="inline-flex items-center gap-1 text-xs font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-full">
                                <i class="fas fa-tag text-[10px]"></i>
                                Rs. {{ number_format($supplier->return_credit, 2) }}
                            </span>
                            <div class="text-[10px] text-purple-400 mt-0.5">Auto-applies on next bill</div>
                        @else
                            <span class="text-slate-300 text-xs">—</span>
                        @endif
                    </td>
                    <td class="p-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <a href="{{ route('suppliers.show', $supplier->id) }}"
                               title="View Profile"
                               class="text-teal-600 hover:text-teal-800 bg-teal-50 hover:bg-teal-100 px-2.5 py-1.5 rounded-lg text-xs font-bold transition">
                                <i class="fas fa-user mr-1"></i>Profile
                            </a>
                            <a href="{{ route('suppliers.ledger', $supplier->id) }}"
                               title="View Ledger"
                               class="text-indigo-500 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg text-xs font-bold transition">
                                <i class="fas fa-book mr-1"></i>Ledger
                            </a>
                            <button
                                @click="editMode = true; form = {
                                    id: {{ $supplier->id }},
                                    name: '{{ addslashes($supplier->name) }}',
                                    code: '{{ addslashes($supplier->code ?? '') }}',
                                    company: '{{ addslashes($supplier->company_name ?? '') }}',
                                    phone: '{{ $supplier->phone }}',
                                    address: '{{ addslashes($supplier->address ?? '') }}',
                                    category_id: '{{ $supplier->category_id }}'
                                }; showModal = true"
                                title="Edit Supplier"
                                class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-2.5 py-1.5 rounded-lg text-xs font-bold transition">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $suppliers->links() }}
        </div>
    </div>

    {{-- Add / Edit Supplier Modal --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white dark:bg-slate-900 w-full max-w-lg rounded-2xl p-6 shadow-2xl border border-slate-200 dark:border-slate-700 overflow-y-auto max-h-[90vh]">
            <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-5" x-text="editMode ? 'Edit Supplier' : 'Add New Supplier'"></h2>

            <form method="POST" :action="editMode ? '/suppliers/' + form.id : '/suppliers'">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-4">

                    {{-- Row: Name & Code --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide block mb-1">Supplier Name *</label>
                            <input type="text" name="name" x-model="form.name" required
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg p-2.5 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide block mb-1">Unique Code *</label>
                            <input type="text" name="code" x-model="form.code" required placeholder="e.g. SUP-001"
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg p-2.5 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 font-mono uppercase">
                        </div>
                    </div>

                    {{-- Category --}}
                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wide block mb-1">Category</label>
                        <select name="category_id" x-model="form.category_id"
                                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg p-2.5 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">— No Category —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">
                            Can't find the category?
                            <a href="{{ route('supplier-categories.index') }}" class="text-indigo-500 underline" target="_blank">Manage categories</a>
                        </p>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wide block mb-1">Company Name</label>
                        <input type="text" name="company_name" x-model="form.company" placeholder="Optional"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg p-2.5 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide block mb-1">Phone</label>
                            <input type="text" name="phone" x-model="form.phone"
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg p-2.5 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide block mb-1">Address</label>
                            <input type="text" name="address" x-model="form.address"
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg p-2.5 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                    </div>

                    <template x-if="!editMode">
                        <div>
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide block mb-1">Opening Balance (Debt)</label>
                            <input type="number" name="opening_balance" x-model="form.opening_balance" placeholder="0.00" step="0.01"
                                   class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg p-2.5 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <p class="text-[10px] text-slate-400 mt-1">Enter a positive amount if you already owe them money.</p>
                        </div>
                    </template>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" @click="showModal = false"
                            class="flex-1 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-slate-500 font-bold text-sm hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition shadow-md">
                        Save Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Supplier Bulk Import Modal -->
    <div x-show="showImportModal"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl w-full max-w-2xl p-6 shadow-2xl relative overflow-hidden" @click.away="closeImportModal()">
            <div class="absolute top-0 left-0 w-full h-1 bg-emerald-500"></div>
            
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-slate-800 dark:text-white font-extrabold text-xl flex items-center gap-2">
                    <i class="fas fa-file-excel text-emerald-500"></i> Bulk Import Suppliers
                </h3>
                <button type="button" @click="closeImportModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form @submit.prevent="submitImport">
                <div class="space-y-5">
                    <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">
                        Upload an `.xls`, `.xlsx`, or `.csv` spreadsheet containing supplier profiles. Columns will be matched by header names automatically.
                    </p>

                    <!-- File drop area -->
                    <div class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl p-8 text-center hover:border-emerald-500 dark:hover:border-emerald-500 hover:bg-slate-50 dark:hover:bg-slate-800/30 transition cursor-pointer relative"
                         @click="$refs.excelInput.click()">
                        <input type="file" name="excel_file" x-ref="excelInput" class="hidden" accept=".xls,.xlsx,.csv" @change="excelFileSelected">
                        
                        <template x-if="!excelFile">
                            <div>
                                <div class="w-16 h-16 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3 text-slate-500">
                                    <i class="fas fa-file-upload text-2xl"></i>
                                </div>
                                <p class="text-slate-700 dark:text-slate-300 font-medium">Select Excel or CSV Spreadsheet</p>
                                <p class="text-slate-400 text-xs mt-1">Accepted formats: .xlsx, .xls, .csv</p>
                            </div>
                        </template>

                        <template x-if="excelFile">
                            <div class="flex items-center justify-center gap-3 bg-slate-50 dark:bg-slate-950 p-4 rounded-lg border border-slate-200 dark:border-slate-800" @click.stop>
                                <i class="fas fa-file-excel text-3xl text-emerald-500"></i>
                                <div class="text-left">
                                    <p class="text-slate-800 dark:text-slate-200 font-semibold" x-text="excelFile.name"></p>
                                    <p class="text-slate-400 text-xs" x-text="(excelFile.size / 1024).toFixed(1) + ' KB'"></p>
                                </div>
                                <button type="button" @click.stop="excelFile = null" class="ml-auto text-slate-400 hover:text-red-500 transition p-1">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    <!-- Progress state -->
                    <div x-show="importing" class="flex flex-col items-center justify-center py-6 space-y-3">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-emerald-500"></div>
                        <p class="text-slate-600 dark:text-slate-300 font-medium text-sm">Processing sheet and importing suppliers...</p>
                    </div>

                    <!-- Result summary card -->
                    <div x-show="importResult" class="space-y-4">
                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/30 rounded-xl p-4 text-center">
                                <div class="text-2xl mb-1">✅</div>
                                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400" x-text="importResult.inserted">0</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Imported</div>
                            </div>
                            <div class="bg-yellow-50 dark:bg-yellow-950/30 border border-yellow-200 dark:border-yellow-800/30 rounded-xl p-4 text-center">
                                <div class="text-2xl mb-1">⚠️</div>
                                <div class="text-2xl font-black text-yellow-600 dark:text-yellow-400" x-text="importResult.skipped_count">0</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Skipped</div>
                            </div>
                            <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/30 rounded-xl p-4 text-center">
                                <div class="text-2xl mb-1">❌</div>
                                <div class="text-2xl font-black text-red-600 dark:text-red-400" x-text="importResult.failed_count">0</div>
                                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Failed</div>
                            </div>
                        </div>

                        <!-- Skipped/Failed details list -->
                        <div x-show="importResult && importResult.skipped && importResult.skipped.length > 0" class="bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl p-4 max-h-48 overflow-y-auto space-y-1">
                            <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1">
                                <i class="fas fa-exclamation-triangle text-amber-500"></i> Import Log / Warnings & Errors:
                            </div>
                            <template x-for="log in importResult.skipped">
                                <div class="text-xs font-mono text-slate-600 dark:text-slate-400 border-b border-slate-100 dark:border-slate-900 pb-1 flex items-start gap-2">
                                    <span class="text-amber-500 font-bold">•</span>
                                    <span x-text="log"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Footer buttons -->
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" @click="closeImportModal()" class="px-5 py-2 rounded-lg border border-slate-300 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition font-bold text-sm">
                        Close
                    </button>
                    <button type="submit" x-show="!importResult" :disabled="!excelFile || importing" 
                            class="px-6 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold transition shadow-lg shadow-emerald-950/20">
                        <i class="fas fa-upload mr-2"></i> Start Import
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection