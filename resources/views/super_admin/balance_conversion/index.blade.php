@extends('super_admin.layout')

@section('title', 'Convert Customer Balances — Super Admin')
@section('header', 'Convert Customer Balances')
@section('subheader', 'One-time conversion of customer balances to the Mart convention')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-sm font-semibold flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-600 text-base"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-sm font-semibold flex items-center gap-2">
            <i class="fas fa-exclamation-circle text-rose-600 text-base"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Explanatory Info Card --}}
    <div class="bg-gradient-to-r from-indigo-900 to-slate-900 rounded-2xl p-6 text-white shadow-md border border-indigo-700/40">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-500/20 border border-indigo-400/30 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-balance-scale text-indigo-300 text-xl"></i>
            </div>
            <div class="space-y-2">
                <h3 class="text-lg font-bold text-white">Customer Balance Convention Conversion Tool</h3>
                <p class="text-sm text-slate-300 leading-relaxed">
                    This tool allows Super Admins to safely convert customer balances from the legacy import convention to the standard Mart POS convention.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-2 text-xs">
                    <div class="bg-white/5 border border-white/10 rounded-lg p-3">
                        <span class="text-rose-400 font-bold uppercase tracking-wider block mb-1">Standard Mart Rule:</span>
                        <p class="text-slate-200"><strong class="text-rose-400">Positive (+) Balance</strong> = Customer owes store (<span class="text-rose-300 font-semibold">Pay to Store</span>)</p>
                        <p class="text-slate-200"><strong class="text-emerald-400">Negative (-) Balance</strong> = Store owes customer (<span class="text-emerald-300 font-semibold">Pay to Customer</span>)</p>
                    </div>
                    <div class="bg-white/5 border border-white/10 rounded-lg p-3">
                        <span class="text-amber-400 font-bold uppercase tracking-wider block mb-1">Conversion Formula:</span>
                        <p class="text-slate-200"><code class="text-amber-300 font-mono">new_balance = old_balance * -1</code></p>
                        <p class="text-slate-400 mt-1">Runs in an atomic transaction with full ledger audit logging. One-time only per store.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Store List Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Select Store / Tenant</h3>
                <p class="text-xs text-slate-500 mt-0.5">Select a store to view balance summary, inspect preview, and execute conversion.</p>
            </div>
            <form method="GET" action="{{ route('super.balance-conversion.index') }}" class="flex gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search store..."
                       class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 w-48 sm:w-64">
                <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg transition">
                    Search
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs border-b border-slate-100">
                    <tr>
                        <th class="p-4">Store Name</th>
                        <th class="p-4">Owner</th>
                        <th class="p-4">Database</th>
                        <th class="p-4 text-center">Conversion Status</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tenants as $t)
                    @php $conv = $conversions->get($t->id); @endphp
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="p-4 font-semibold text-slate-800">
                            {{ $t->store_name }}
                            <div class="text-[11px] text-slate-400 font-mono">{{ $t->id }}</div>
                        </td>
                        <td class="p-4 text-slate-600 text-xs">
                            <div>{{ $t->owner_name }}</div>
                            <div class="text-slate-400">{{ $t->owner_email }}</div>
                        </td>
                        <td class="p-4 text-slate-500 font-mono text-xs">
                            {{ $t->database_name }}
                        </td>
                        <td class="p-4 text-center">
                            @if($conv)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <i class="fas fa-check-circle text-emerald-500"></i> Converted ({{ $conv->converted_at->format('d M Y') }})
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                    <i class="fas fa-clock text-amber-500"></i> Not Converted
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('super.balance-conversion.preview', $t->id) }}"
                               class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $conv ? 'bg-slate-100 hover:bg-slate-200 text-slate-700' : 'bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm' }}">
                                <i class="fas {{ $conv ? 'fa-eye' : 'fa-exchange-alt' }}"></i>
                                {{ $conv ? 'View Status' : 'Inspect & Convert' }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400 text-sm">No stores found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
