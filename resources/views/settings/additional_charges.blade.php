@extends('layouts.admin')

@section('title', 'Additional Invoice Charges')

@section('content')
<div class="container mx-auto p-4 lg:p-6 max-w-7xl">

    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Saved!',
                    text: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true,
                    toast: true,
                    position: 'top-end'
                });
            }
        });
    </script>
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-white flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-500/20 shadow-sm">
                        <i class="fas fa-receipt text-lg"></i>
                    </div>
                    Additional Invoice Charges
                </h1>
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Configure database-driven additional charges (Delivery, Packaging, Service Charge, etc.) for sales invoices.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('settings.general') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl font-bold text-xs transition flex items-center gap-2 border border-slate-200 dark:border-slate-700">
                <i class="fas fa-arrow-left"></i> Settings
            </a>
        </div>
    </div>

    @if ($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-red-500/10 border border-red-500/30 text-red-500 text-sm">
        <strong class="font-bold block mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> Please fix the following errors:</strong>
        <ul class="list-disc list-inside space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <!-- Left: Create Charge Category Form (4 Cols) -->
        <div class="lg:col-span-4 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <i class="fas fa-plus-circle text-indigo-500"></i> Add Charge Category
            </h2>

            <form action="{{ route('settings.additional-charges.store') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                        Charge Name
                    </label>
                    <input type="text" name="name" required placeholder="e.g. Delivery, Service Charge, Packaging" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                        Charge Type
                    </label>
                    <select name="type" required class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        <option value="fixed">Fixed Amount (e.g. Rs. 200)</option>
                        <option value="percentage">Percentage % (e.g. 2%)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">
                        Value / Amount
                    </label>
                    <input type="number" step="0.01" min="0" name="value" required placeholder="e.g. 200 or 2.5" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <input type="checkbox" name="is_enabled" value="1" id="is_enabled" checked class="w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500 border-slate-300">
                    <label for="is_enabled" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                        Enable this charge by default
                    </label>
                </div>

                <button type="submit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-lg shadow-indigo-500/25 transition text-sm flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Save Charge Category
                </button>
            </form>
        </div>

        <!-- Right: Configured Charge Categories List (8 Cols) -->
        <div class="lg:col-span-8 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-4 flex items-center gap-2">
                <i class="fas fa-list text-indigo-500"></i> Configured Additional Charges
            </h2>

            @if($charges->isEmpty())
                <div class="text-center py-12 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-2xl">
                    <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-folder-open text-xl"></i>
                    </div>
                    <p class="text-sm font-bold text-slate-600 dark:text-slate-400">No additional charge categories defined.</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Use the form on the left to add your first charge category.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                        <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-800/60 text-slate-500 dark:text-slate-400 font-bold">
                            <tr>
                                <th class="px-4 py-3 rounded-l-xl">Name</th>
                                <th class="px-4 py-3">Type</th>
                                <th class="px-4 py-3">Value</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3 text-right rounded-r-xl">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach($charges as $charge)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition">
                                <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">
                                    {{ $charge->name }}
                                </td>
                                <td class="px-4 py-3 font-semibold">
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $charge->type === 'percentage' ? 'bg-purple-500/10 text-purple-600 border border-purple-500/20' : 'bg-blue-500/10 text-blue-600 border border-blue-500/20' }}">
                                        {{ ucfirst($charge->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-bold text-slate-800 dark:text-slate-200">
                                    {{ $charge->type === 'percentage' ? $charge->value . '%' : 'Rs. ' . number_format($charge->value, 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($charge->is_enabled)
                                        <span class="px-2.5 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-full text-xs font-bold border border-emerald-500/20">
                                            Enabled
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-slate-500/10 text-slate-500 rounded-full text-xs font-bold border border-slate-500/20">
                                            Disabled
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('settings.additional-charges.update', $charge->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="name" value="{{ $charge->name }}">
                                            <input type="hidden" name="type" value="{{ $charge->type }}">
                                            <input type="hidden" name="value" value="{{ $charge->value }}">
                                            <input type="hidden" name="is_enabled" value="{{ $charge->is_enabled ? 0 : 1 }}">
                                            <button type="submit" class="p-2 text-xs font-bold rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800 transition" title="Toggle status">
                                                <i class="fas {{ $charge->is_enabled ? 'fa-eye-slash text-amber-500' : 'fa-eye text-emerald-500' }}"></i>
                                            </button>
                                        </form>

                                        <form action="{{ route('settings.additional-charges.delete', $charge->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete charge category {{ $charge->name }}?')">
                                            @csrf
                                            <button type="submit" class="p-2 text-xs font-bold text-red-500 rounded-lg border border-red-200 dark:border-red-900/50 hover:bg-red-50 dark:hover:bg-red-950/30 transition" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
