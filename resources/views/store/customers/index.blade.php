@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="{
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

        fetch('{{ route('customers.import') }}', {
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
            if (data.inserted > 0) {
                setTimeout(() => window.location.reload(), 1500);
            }
        })
        .catch(error => {
            this.importing = false;
            alert(error.message || 'Import failed.');
        });
    }
}">
    {{-- Breadcrumb --}}
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Customers</h1>
        <div class="flex items-center gap-2">
            <button @click="showImportModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold">
                <i class="fas fa-file-import"></i> Import
            </button>
            <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold">
                <i class="fas fa-plus"></i> New Customer
            </a>
        </div>
    </div>

    {{-- KPI Banner --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-8">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col">
            <span class="text-slate-400 text-xs font-bold uppercase">Total Customers</span>
            <span class="text-2xl font-extrabold text-slate-800 dark:text-white mt-1">{{ number_format($totalCustomers) }}</span>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col">
            <span class="text-slate-400 text-xs font-bold uppercase">Total Receivable</span>
            <span class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">Rs. {{ number_format($totalReceivable, 2) }}</span>
        </div>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-5 shadow-sm flex flex-col">
            <span class="text-slate-400 text-xs font-bold uppercase">Total Credit Limit</span>
            <span class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">Rs. {{ number_format($totalCreditLimit, 2) }}</span>
        </div>
    </div>

    {{-- Customers Table --}}
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
            <h3 class="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <i class="fas fa-users text-indigo-500"></i> Customer List
            </h3>
            <form method="GET" action="{{ route('customers.index') }}" class="flex items-center gap-2 flex-wrap">
                <input type="hidden" name="show_deactivated" value="{{ $showDeactivated ? '1' : '0' }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search name or phone..." class="px-3 py-1 border border-slate-300 dark:border-slate-600 rounded-md text-sm"/>
                <button type="submit" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-sm font-medium">
                    <i class="fas fa-search"></i>
                </button>
                <a href="{{ route('customers.index', array_merge(request()->except('show_deactivated', 'page'), ['show_deactivated' => $showDeactivated ? '0' : '1'])) }}"
                   class="px-3 py-1 {{ $showDeactivated ? 'bg-amber-600 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }} rounded-md text-sm font-semibold hover:opacity-80 transition flex items-center gap-1">
                    <i class="fas fa-user-slash text-xs"></i>
                    {{ $showDeactivated ? 'Hide Deactivated' : 'Show Deactivated' }}
                </a>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                    <tr>
                        <th class="p-4">Name</th>
                        <th class="p-4">Phone</th>
                        <th class="p-4">Address</th>
                        <th class="p-4">Balance</th>
                        <th class="p-4">Store Credit</th>
                        <th class="p-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($customers as $cust)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition {{ $cust->status === 'deactivated' ? 'opacity-60' : '' }}">
                        <td class="p-4 font-medium text-slate-800 dark:text-white">
                            {{ $cust->name }}
                            @if($cust->status === 'written_off')
                                <span class="ml-1.5 px-2 py-0.5 text-[10px] font-bold bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400 rounded-full uppercase">Written Off</span>
                            @elseif($cust->status === 'deactivated')
                                <span class="ml-1.5 px-2 py-0.5 text-[10px] font-bold bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-400 rounded-full uppercase">Deactivated</span>
                            @endif
                        </td>
                        <td class="p-4 text-slate-600 dark:text-slate-350">{{ $cust->phone ?? '—' }}</td>
                        <td class="p-4 text-slate-600 dark:text-slate-350">{{ $cust->address ?? '—' }}</td>
                        <td class="p-4 font-bold {{ $cust->balance > 0 ? 'text-red-600' : ($cust->balance < 0 ? 'text-emerald-600' : 'text-slate-500') }}">
                            Rs. {{ number_format($cust->balance, 2) }}
                        </td>
                        <td class="p-4 font-semibold {{ ($cust->store_credit ?? 0) > 0 ? 'text-violet-600 dark:text-violet-400' : 'text-slate-400' }}">
                            Rs. {{ number_format($cust->store_credit ?? 0, 2) }}
                        </td>
                        <td class="p-4 text-center">
                            <a href="{{ route('customers.show', $cust->id) }}" class="inline-flex items-center gap-1.5 text-indigo-500 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1.5 rounded-lg text-xs font-bold transition">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-450 dark:text-slate-500 font-medium">No customers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
        <div class="p-4 border-t border-slate-100 dark:border-slate-800">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

    {{-- Import Modal (Alpine.js) --}}
    <div x-show="showImportModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50" style="display:none;">
        <div class="bg-white dark:bg-slate-900 rounded-lg shadow-xl w-full max-w-lg p-6">
            <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-4">Import Customers</h2>
            <p class="text-sm text-slate-600 dark:text-slate-300 mb-4">Download the sample format <a href="{{ route('customers.sample_excel') }}" class="text-indigo-600 hover:underline">here</a> and upload your filled file.</p>
            <input type="file" x-ref="excelInput" @change="excelFileSelected" class="mb-4 w-full" accept=".xlsx,.xls,.csv,.txt" />
            <div class="flex justify-end gap-2">
                <button @click="showImportModal = false" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded">Cancel</button>
                <button @click="submitImport" :disabled="importing" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded">
                    <span x-show="!importing">Import</span>
                    <span x-show="importing">Importing...</span>
                </button>
            </div>
            <template x-if="importResult">
                <div class="mt-4 p-4 bg-gray-100 dark:bg-slate-800 rounded">
                    <p class="font-medium">Import Summary:</p>
                    <ul class="list-disc list-inside text-sm">
                        <li>Inserted: <span x-text="importResult.inserted"></span></li>
                        <li>Skipped: <span x-text="importResult.skipped_count"></span></li>
                        <li>Failed: <span x-text="importResult.failed_count"></span></li>
                        <template x-if="importResult.skipped && importResult.skipped.length > 0">
                            <li>Details: <ul class="list-disc ml-4" x-html="importResult.skipped.join('<br/>')"></ul></li>
                        </template>
                    </ul>
                </div>
            </template>
        </div>
    </div>
</div>
@endsection
