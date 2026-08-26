@php $defaultTab = 'general'; @endphp
@extends('layouts.admin')

@section('title', 'Tax Settings')

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

    {{-- First-Time Setup Welcome Banner if not yet configured --}}
    @if(is_null($settings->tax_configured_at))
    <div class="mb-8 p-5 rounded-2xl bg-amber-500/10 border-2 border-amber-500/40 text-amber-900 dark:text-amber-200 shadow-lg shadow-amber-500/5">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center flex-shrink-0 border border-amber-500/30 text-xl font-bold">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h3 class="text-base sm:text-lg font-bold text-slate-900 dark:text-amber-100">
                    First-Time Tax Setup Required
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 dark:text-amber-200/90 mt-1 leading-relaxed">
                    Your store's tax policy has not been configured yet. Please make an explicit decision below. You can either <strong>enable a tax rate</strong> to be automatically calculated on new invoices, or <strong>explicitly choose not to charge tax</strong>.
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl lg:text-3xl font-extrabold text-slate-800 dark:text-white flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center border border-blue-500/20 shadow-sm">
                        <i class="fas fa-percent text-lg"></i>
                    </div>
                    Store Tax Settings
                </h1>
                @if($settings->tax_configured_at)
                    <span class="px-3 py-1 bg-emerald-500/15 border border-emerald-500/30 text-emerald-500 rounded-full text-xs font-bold flex items-center gap-1.5">
                        <i class="fas fa-check-circle text-[11px]"></i> Configured
                    </span>
                @else
                    <span class="px-3 py-1 bg-amber-500/15 border border-amber-500/30 text-amber-500 rounded-full text-xs font-bold flex items-center gap-1.5">
                        <i class="fas fa-clock text-[11px]"></i> Action Needed
                    </span>
                @endif
            </div>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                Configure store-level sales tax. The backend automatically calculates and locks this tax onto all new invoices.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('settings.general') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-xl font-bold text-xs transition flex items-center gap-2 border border-slate-200 dark:border-slate-700">
                <i class="fas fa-arrow-left"></i> General Settings
            </a>
            <button type="submit" form="taxSettingsForm" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl shadow-lg shadow-blue-500/25 transition font-bold text-sm flex items-center gap-2">
                <i class="fas fa-save"></i> Save Settings
            </button>
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

    {{-- Main Tax Settings Configuration Form --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10"
         x-data="{
             isConfigured: {{ $settings->tax_configured_at ? 'true' : 'false' }},
             taxChoice: '{{ old('tax_choice', $settings->tax_configured_at ? ($settings->tax_enabled ? 'enabled' : 'disabled') : '') }}',
             taxEnabled: {{ old('tax_enabled', $settings->tax_enabled ? 'true' : 'false') }},
             taxRate: '{{ old('tax_rate', number_format($settings->tax_rate ?? 0, 2, '.', '')) }}',
             sampleSubtotal: 10000,

             selectOption(choice) {
                 this.taxChoice = choice;
                 if (choice === 'enabled') {
                     this.taxEnabled = true;
                     if (!this.taxRate || parseFloat(this.taxRate) === 0) {
                         this.taxRate = '2.00';
                     }
                 } else if (choice === 'disabled') {
                     this.taxEnabled = false;
                     this.taxRate = '0.00';
                 }
             },

             get calculatedTax() {
                 if (!this.taxEnabled) return (0).toFixed(2);
                 let rate = parseFloat(this.taxRate) || 0;
                 return ((this.sampleSubtotal * rate) / 100).toFixed(2);
             },
             get calculatedGrandTotal() {
                 return (parseFloat(this.sampleSubtotal) + parseFloat(this.calculatedTax)).toFixed(2);
             }
         }">

        {{-- Configuration Card (7 Cols) --}}
        <div class="lg:col-span-7 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 lg:p-8 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500"></div>

            <form id="taxSettingsForm" action="{{ route('settings.tax.update') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="tax_enabled" :value="taxEnabled ? '1' : '0'">

                {{-- Policy Options Selection Grid (Explicit first-time or regular selection) --}}
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-3">
                        Tax Policy Decision <span class="text-red-500">*</span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Option 1: Enable Tax --}}
                        <div @click="selectOption('enabled')"
                             class="p-4 rounded-xl border-2 cursor-pointer transition flex flex-col justify-between relative"
                             :class="taxEnabled ? 'border-blue-500 bg-blue-500/10 dark:bg-blue-500/10 shadow-sm' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-slate-50 dark:bg-slate-950/40'">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-blue-500/20 text-blue-500 flex items-center justify-center font-bold">
                                        <i class="fas fa-receipt text-sm"></i>
                                    </div>
                                    <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                          :class="taxEnabled ? 'border-blue-500 bg-blue-500 text-white text-[10px]' : 'border-slate-400'">
                                        <i x-show="taxEnabled" class="fas fa-check"></i>
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-800 dark:text-white">Enable Sales Tax</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Automatically calculate and append tax on every new bill.</p>
                            </div>
                        </div>

                        {{-- Option 2: Explicitly Opt Out --}}
                        <div @click="selectOption('disabled')"
                             class="p-4 rounded-xl border-2 cursor-pointer transition flex flex-col justify-between relative"
                             :class="(!taxEnabled && (taxChoice === 'disabled' || isConfigured)) ? 'border-slate-500 bg-slate-500/10 dark:bg-slate-800/40 shadow-sm' : 'border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 bg-slate-50 dark:bg-slate-950/40'">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-8 h-8 rounded-lg bg-slate-500/20 text-slate-400 flex items-center justify-center font-bold">
                                        <i class="fas fa-ban text-sm"></i>
                                    </div>
                                    <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center"
                                          :class="(!taxEnabled && (taxChoice === 'disabled' || isConfigured)) ? 'border-slate-500 bg-slate-500 text-white text-[10px]' : 'border-slate-400'">
                                        <i x-show="!taxEnabled && (taxChoice === 'disabled' || isConfigured)" class="fas fa-check"></i>
                                    </span>
                                </div>
                                <h4 class="text-sm font-bold text-slate-800 dark:text-white">Do Not Charge Tax</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Explicitly opt out. No tax is added to customer invoices.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tax Rate Field (shown and enabled when tax is active) --}}
                <div x-show="taxEnabled" x-transition>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">
                        Tax Rate (%) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-xl shadow-sm">
                        <input type="number"
                               step="0.01"
                               min="0"
                               max="100"
                               name="tax_rate"
                               x-model="taxRate"
                               :required="taxEnabled"
                               placeholder="e.g. 2.00"
                               class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl py-3 pl-4 pr-12 text-slate-800 dark:text-white font-mono text-lg font-bold focus:ring-2 focus:ring-blue-500 outline-none transition">
                        <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none text-slate-400 font-bold text-lg">
                            %
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                        Enter percentage rate (e.g., <code class="text-blue-500 font-mono">2.00</code>, <code class="text-blue-500 font-mono">5.00</code>, or <code class="text-blue-500 font-mono">10.00</code>).
                    </p>
                </div>

                {{-- Informational Callout --}}
                <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800/40 text-blue-900 dark:text-blue-300 text-xs space-y-2">
                    <div class="font-bold flex items-center gap-2 text-blue-700 dark:text-blue-400">
                        <i class="fas fa-shield-alt"></i> Store Admin Protection & Governance
                    </div>
                    <ul class="list-disc list-inside space-y-1 text-slate-600 dark:text-slate-400">
                        <li><strong>Backend Authority:</strong> Once saved, the backend automatically enforces this setting on every invoice.</li>
                        <li><strong>Staff Restrictions:</strong> Cashiers cannot edit, disable, or override this rate on POS or sales screens.</li>
                        <li><strong>Historical Preservation:</strong> Future rate changes will not mutate previously finalized sales records.</li>
                    </ul>
                </div>
            </form>
        </div>

        {{-- Live Calculation Preview Card (5 Cols) --}}
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 p-6 lg:p-8 flex flex-col justify-between relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-1 bg-emerald-500"></div>

            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 pb-3">
                    <i class="fas fa-calculator text-emerald-500"></i> Live Invoice Calculation Preview
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 mb-4">
                    Sample calculation preview based on your selection:
                </p>

                <div class="space-y-3 p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 font-mono text-sm">
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Sample Subtotal:</span>
                        <span class="font-bold text-slate-800 dark:text-white">Rs. 10,000.00</span>
                    </div>
                    <div class="flex justify-between text-slate-600 dark:text-slate-400">
                        <span>Discount:</span>
                        <span class="font-bold text-slate-800 dark:text-white">Rs. 0.00</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-y border-dashed border-slate-300 dark:border-slate-700"
                         :class="taxEnabled ? 'text-blue-600 dark:text-blue-400 font-bold' : 'text-slate-400'">
                        <span>Tax (<span x-text="taxEnabled ? (parseFloat(taxRate) || 0) : '0'"></span>%):</span>
                        <span x-text="'Rs. ' + calculatedTax"></span>
                    </div>
                    <div class="flex justify-between items-center pt-1 font-sans">
                        <span class="font-extrabold text-slate-800 dark:text-white uppercase text-xs tracking-wider">Grand Total:</span>
                        <span class="font-black text-xl text-emerald-600 dark:text-emerald-400 font-mono" x-text="'Rs. ' + calculatedGrandTotal"></span>
                    </div>
                </div>

                @if($settings->tax_configured_at)
                <div class="mt-4 p-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 text-xs text-slate-500 space-y-1">
                    <div class="font-bold text-slate-700 dark:text-slate-300">Configuration Details:</div>
                    <div>Last configured on: <strong class="text-slate-800 dark:text-slate-200">{{ $settings->tax_configured_at->format('d M Y, h:i A') }}</strong></div>
                    @if($settings->taxConfiguredUser)
                        <div>By: <strong class="text-slate-800 dark:text-slate-200">{{ $settings->taxConfiguredUser->name }}</strong></div>
                    @endif
                </div>
                @endif
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-400 text-center">
                <i class="fas fa-lock text-slate-500 mr-1"></i> Calculations are performed strictly on the backend.
            </div>
        </div>
    </div>

    {{-- Tax Settings Audit History Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50 dark:bg-slate-950/60">
            <div>
                <h3 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                    <i class="fas fa-history text-indigo-500"></i> Tax Settings Change History
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Immutable audit log of all tax configuration adjustments made by Store Admin.
                </p>
            </div>
            <span class="text-xs font-bold px-3 py-1 bg-slate-200 dark:bg-slate-800 rounded-lg text-slate-700 dark:text-slate-300">
                {{ $history->total() }} recorded changes
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                <thead class="bg-slate-100 dark:bg-slate-950 text-xs uppercase font-bold text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Date & Time</th>
                        <th class="py-3 px-4">Performed By</th>
                        <th class="py-3 px-4 text-center">Status Change</th>
                        <th class="py-3 px-4 text-center">Rate Change</th>
                        <th class="py-3 px-4 text-right">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($history as $item)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition">
                        <td class="py-3.5 px-4 font-mono text-xs">
                            {{ $item->created_at?->format('d M Y, h:i A') }}
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-slate-800 dark:text-slate-200">
                            {{ $item->user_name ?: ($item->user?->name ?? 'Store Admin') }}
                        </td>
                        <td class="py-3.5 px-4 text-center text-xs">
                            <span class="px-2 py-0.5 rounded font-bold {{ $item->previous_tax_enabled ? 'bg-emerald-500/10 text-emerald-500' : 'bg-slate-500/10 text-slate-400' }}">
                                {{ $item->previous_tax_enabled ? 'ON' : 'OFF' }}
                            </span>
                            <i class="fas fa-arrow-right text-[10px] text-slate-400 mx-1.5"></i>
                            <span class="px-2 py-0.5 rounded font-bold {{ $item->new_tax_enabled ? 'bg-emerald-500/20 text-emerald-500' : 'bg-slate-500/20 text-slate-400' }}">
                                {{ $item->new_tax_enabled ? 'ON' : 'OFF' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center font-mono font-bold text-xs">
                            <span class="text-slate-400">{{ number_format($item->previous_tax_rate, 2) }}%</span>
                            <i class="fas fa-arrow-right text-[10px] text-slate-400 mx-1.5"></i>
                            <span class="text-blue-500">{{ number_format($item->new_tax_rate, 2) }}%</span>
                        </td>
                        <td class="py-3.5 px-4 text-right font-mono text-xs text-slate-400">
                            {{ $item->ip_address ?: '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400 italic">
                            <i class="fas fa-info-circle mr-1"></i> No tax settings modifications recorded yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($history->hasPages())
        <div class="p-4 border-t border-slate-200 dark:border-slate-800">
            {{ $history->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
