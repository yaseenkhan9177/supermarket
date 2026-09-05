@extends('super_admin.layout')

@section('title', 'Convert Customer Balances — ' . $tenant->store_name)
@section('header', 'Convert Customer Balances')
@section('subheader', $tenant->store_name . ' (' . $tenant->id . ')')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{ confirmed: false, showModal: false }">

    {{-- Breadcrumb / Back Navigation --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('super.balance-conversion.index') }}"
           class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-semibold transition">
            <i class="fas fa-arrow-left"></i> Back to Store Selection
        </a>
        <a href="{{ route('super.tenants.show', $tenant->id) }}"
           class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-lg transition">
            <i class="fas fa-store"></i> Store Detail
        </a>
    </div>

    {{-- Error Banner --}}
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl text-sm font-semibold flex items-center gap-3 shadow-sm">
            <i class="fas fa-exclamation-circle text-rose-600 text-lg flex-shrink-0"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- Success Summary Banner (Shown right after execution) --}}
    @if(session('conversion_success'))
        @php $res = session('conversion_success'); @endphp
        <div class="bg-emerald-50 border border-emerald-300 rounded-2xl p-6 shadow-sm space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 border border-emerald-300 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-check-circle text-emerald-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-emerald-900">{{ $res['message'] ?? 'Customer balance conversion completed successfully.' }}</h3>
                    <p class="text-xs text-emerald-700 mt-0.5">The customer balances for this store have been safely converted and recorded.</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 pt-2 text-center">
                <div class="bg-white/80 rounded-xl p-3 border border-emerald-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Processed</p>
                    <p class="text-xl font-extrabold text-slate-800 mt-1">{{ number_format($res['customers_processed'] ?? 0) }}</p>
                </div>
                <div class="bg-white/80 rounded-xl p-3 border border-emerald-200 shadow-sm">
                    <p class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Positive Converted</p>
                    <p class="text-xl font-extrabold text-rose-700 mt-1">{{ number_format($res['positive_converted'] ?? 0) }}</p>
                </div>
                <div class="bg-white/80 rounded-xl p-3 border border-emerald-200 shadow-sm">
                    <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Negative Converted</p>
                    <p class="text-xl font-extrabold text-emerald-700 mt-1">{{ number_format($res['negative_converted'] ?? 0) }}</p>
                </div>
                <div class="bg-white/80 rounded-xl p-3 border border-emerald-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Zero Unchanged</p>
                    <p class="text-xl font-extrabold text-slate-700 mt-1">{{ number_format($res['zero_unchanged'] ?? 0) }}</p>
                </div>
                <div class="bg-white/80 rounded-xl p-3 border border-emerald-200 shadow-sm">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Failed Records</p>
                    <p class="text-xl font-extrabold text-slate-800 mt-1">{{ number_format($res['failed_records'] ?? 0) }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- ALREADY CONVERTED BANNER --}}
    @if($existingConversion)
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-6 shadow-sm space-y-3">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 rounded-xl bg-amber-100 border border-amber-300 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas fa-lock text-amber-700 text-lg"></i>
                </div>
                <div class="space-y-1 flex-1">
                    <h3 class="text-base font-bold text-amber-900">Customer balance conversion has already been completed.</h3>
                    <p class="text-xs text-amber-800 leading-relaxed">
                        This one-time conversion was successfully performed on
                        <strong>{{ $existingConversion->converted_at->format('d M Y, h:i A') }}</strong>
                        by <strong>{{ $existingConversion->super_admin_name ?? 'Super Admin' }}</strong>.
                        To protect accounting and ledger integrity, this conversion cannot be run again.
                    </p>
                    <div class="flex flex-wrap gap-4 pt-2 text-xs font-semibold text-amber-900">
                        <span><i class="fas fa-users mr-1"></i> Customers: {{ number_format($existingConversion->customers_processed) }}</span>
                        <span><i class="fas fa-exchange-alt mr-1"></i> Positive Converted: {{ number_format($existingConversion->positive_converted) }}</span>
                        <span><i class="fas fa-exchange-alt mr-1"></i> Negative Converted: {{ number_format($existingConversion->negative_converted) }}</span>
                        <span><i class="fas fa-check mr-1"></i> Zero Unchanged: {{ number_format($existingConversion->zero_unchanged) }}</span>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- MANDATORY WARNING BANNER --}}
        <div class="bg-rose-50 border-2 border-rose-300 rounded-2xl p-6 shadow-sm">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-xl bg-rose-100 border border-rose-300 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas fa-exclamation-triangle text-rose-600 text-xl"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-base font-extrabold text-rose-900 uppercase tracking-wide">Warning: Irreversible One-Time Conversion</h3>
                    <p class="text-sm text-rose-800 font-medium leading-relaxed">
                        Your existing customer balances will be converted from the old balance convention to the new Mart balance convention. Negative balances will become positive and positive balances will become negative.
                    </p>
                    <p class="text-xs text-rose-700">
                        Formula: <code class="bg-rose-100 px-1.5 py-0.5 rounded font-mono font-bold text-rose-900">new_balance = old_balance * -1</code>.
                        This operation is wrapped in a database transaction with full ledger audit logging.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- Tenant Target Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-4 border-b border-slate-100">
            <div>
                <span class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider bg-indigo-50 px-2 py-0.5 rounded">Target Tenant</span>
                <h2 class="text-xl font-black text-slate-800 mt-1">{{ $tenant->store_name }}</h2>
                <div class="flex flex-wrap gap-3 text-xs text-slate-400 font-mono mt-1">
                    <span>Tenant ID: {{ $tenant->id }}</span>
                    <span>•</span>
                    <span>Database: {{ $tenant->database_name }}</span>
                    <span>•</span>
                    <span>Owner: {{ $tenant->owner_name }} ({{ $tenant->owner_email }})</span>
                </div>
            </div>
            <div>
                @if($existingConversion)
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 flex items-center gap-1.5">
                        <i class="fas fa-check-circle"></i> Completed
                    </span>
                @else
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 flex items-center gap-1.5">
                        <i class="fas fa-clock"></i> Ready for Conversion
                    </span>
                @endif
            </div>
        </div>

        {{-- Statistics Grid --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 text-center">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Customers</p>
                <p class="text-2xl font-black text-slate-800 mt-1">{{ number_format($customersCount) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">In store database</p>
            </div>
            <div class="bg-rose-50/50 border border-rose-200/60 rounded-xl p-4 text-center">
                <p class="text-xs font-bold uppercase tracking-wider text-rose-600">Positive Balances</p>
                <p class="text-2xl font-black text-rose-700 mt-1">{{ number_format($positiveCount) }}</p>
                <p class="text-[11px] text-rose-500 mt-1">Will become negative (-)</p>
            </div>
            <div class="bg-emerald-50/50 border border-emerald-200/60 rounded-xl p-4 text-center">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Negative Balances</p>
                <p class="text-2xl font-black text-emerald-700 mt-1">{{ number_format($negativeCount) }}</p>
                <p class="text-[11px] text-emerald-600 mt-1">Will become positive (+)</p>
            </div>
            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 text-center">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Zero Balances</p>
                <p class="text-2xl font-black text-slate-700 mt-1">{{ number_format($zeroCount) }}</p>
                <p class="text-[11px] text-slate-400 mt-1">Remains zero (0.00)</p>
            </div>
        </div>

        {{-- Expected Result Summary --}}
        <div class="mt-6 p-4 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1.5 text-slate-700">
            <p class="font-bold text-slate-800 uppercase tracking-wider mb-1">Expected Result Summary:</p>
            <p class="flex items-center gap-2">
                <i class="fas fa-arrow-right text-rose-500 text-[10px]"></i>
                <strong>Positive balances</strong> ({{ number_format($positiveCount) }}) will become negative &rarr; Display: <span class="text-emerald-600 font-bold">GREEN ("Pay to Customer")</span>
            </p>
            <p class="flex items-center gap-2">
                <i class="fas fa-arrow-right text-emerald-500 text-[10px]"></i>
                <strong>Negative balances</strong> ({{ number_format($negativeCount) }}) will become positive &rarr; Display: <span class="text-rose-600 font-bold">RED ("Pay to Store")</span>
            </p>
            <p class="flex items-center gap-2">
                <i class="fas fa-arrow-right text-slate-400 text-[10px]"></i>
                <strong>Zero balances</strong> ({{ number_format($zeroCount) }}) will remain zero &rarr; Display: <span class="text-slate-500 font-bold">"No Balance"</span>
            </p>
        </div>
    </div>

    {{-- Preview Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="text-base font-bold text-slate-800">Conversion Preview</h3>
                <p class="text-xs text-slate-500 mt-0.5">Showing customer balance transformation preview before execution.</p>
            </div>
            <span class="text-xs font-semibold text-slate-400">
                Sample {{ $previewRows->count() }} of {{ number_format($customersCount) }}
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs border-b border-slate-100">
                    <tr>
                        <th class="p-4">Customer</th>
                        <th class="p-4">Current Balance</th>
                        <th class="p-4">New Balance</th>
                        <th class="p-4">Meaning</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($previewRows as $row)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="p-4 text-slate-800">
                            <div class="font-bold">{{ $row['name'] }}</div>
                            @if(!empty($row['phone']))
                                <div class="text-[11px] text-slate-400">{{ $row['phone'] }}</div>
                            @endif
                        </td>
                        <td class="p-4 font-mono font-semibold {{ $row['current_balance'] > 0 ? 'text-slate-700' : ($row['current_balance'] < 0 ? 'text-slate-700' : 'text-slate-400') }}">
                            {{ $row['current_balance'] > 0 ? '+' : '' }}{{ number_format($row['current_balance'], 2) }}
                        </td>
                        <td class="p-4 font-mono font-bold {{ $row['class'] === 'red' ? 'text-rose-600' : ($row['class'] === 'green' ? 'text-emerald-600' : 'text-slate-400') }}">
                            {{ $row['new_balance'] > 0 ? '+' : '' }}{{ number_format($row['new_balance'], 2) }}
                        </td>
                        <td class="p-4">
                            @if($row['class'] === 'red')
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200">
                                    Pay to Store
                                </span>
                            @elseif($row['class'] === 'green')
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Pay to Customer
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                                    No Balance
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400 text-sm">No customer records in this store.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Execution & Confirmation Section --}}
    @if(!$existingConversion)
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-5">
            <h3 class="text-base font-bold text-slate-800">Explicit Super Admin Confirmation</h3>
            <p class="text-xs text-slate-600 leading-relaxed">
                To prevent accidental execution, please check the confirmation box below before proceeding. Once executed, this conversion cannot be undone or re-run.
            </p>

            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" x-model="confirmed" class="mt-1 w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                    <span class="text-xs font-medium text-slate-700">
                        I confirm that I am authorized as Super Admin to convert all customer balances for store <strong>{{ $tenant->store_name }}</strong> (Tenant ID: {{ $tenant->id }}). I understand that all positive balances will become negative and all negative balances will become positive.
                    </span>
                </label>
            </div>

            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 pt-2">
                <a href="{{ route('super.balance-conversion.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">
                    Cancel and Return
                </a>

                <button type="button"
                        :disabled="!confirmed"
                        @click="showModal = true"
                        :class="confirmed ? 'bg-rose-600 hover:bg-rose-700 text-white shadow-md' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                        class="px-6 py-3 rounded-xl text-sm font-bold transition flex items-center gap-2">
                    <i class="fas fa-exchange-alt"></i> Convert Balances
                </button>
            </div>
        </div>

        {{-- Final Safety Confirmation Modal --}}
        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" style="display: none;">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 space-y-5 shadow-2xl border border-slate-200">
                <div class="w-12 h-12 rounded-full bg-rose-100 border border-rose-200 flex items-center justify-center text-rose-600 text-xl mx-auto">
                    <i class="fas fa-shield-alt"></i>
                </div>

                <div class="text-center space-y-2">
                    <h4 class="text-lg font-black text-slate-800">Confirm Balance Conversion</h4>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Are you sure you want to convert customer balances for <strong>{{ $tenant->store_name }}</strong>?
                    </p>
                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-800 font-semibold">
                        This action will update {{ number_format($customersCount) }} customer records and write ledger audit entries. This operation happens ONLY ONCE.
                    </div>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" @click="showModal = false"
                            class="flex-1 py-2.5 px-4 rounded-xl text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                        Cancel
                    </button>

                    <form method="POST" action="{{ route('super.balance-conversion.convert', $tenant->id) }}" class="flex-1">
                        @csrf
                        <button type="submit"
                                class="w-full py-2.5 px-4 rounded-xl text-xs font-extrabold text-white bg-rose-600 hover:bg-rose-700 shadow transition flex items-center justify-center gap-1.5">
                            <i class="fas fa-check"></i> Yes, Execute Conversion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection
