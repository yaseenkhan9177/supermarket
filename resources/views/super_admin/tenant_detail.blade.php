@extends('super_admin.layout')

@section('title', 'Manage Store — ' . $tenant->store_name)
@section('header', 'Manage Store')
@section('subheader', $tenant->store_name . ' (' . $tenant->id . ')')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm font-medium">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-sm font-medium">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Provisioning Error Banner --}}
    @if($tenant->provisioning_error)
    <div class="bg-amber-50 border border-amber-300 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center mt-0.5">
                <i class="fas fa-exclamation-triangle text-amber-600 text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-amber-800">Partial Provisioning Failure</p>
                <p class="text-xs text-amber-700 mt-1">
                    A previous approval attempt failed. The physical database may already exist.
                    Use <strong>Approve &amp; Resume</strong> to retry from the failed step — DB creation will be skipped if the database already exists.
                </p>
                <details class="mt-2">
                    <summary class="text-xs font-semibold text-amber-700 cursor-pointer hover:text-amber-900">Show error details</summary>
                    <pre class="mt-2 text-xs bg-amber-100 border border-amber-200 rounded p-2 whitespace-pre-wrap break-all text-amber-900 font-mono">{{ $tenant->provisioning_error }}</pre>
                </details>
            </div>
        </div>
    </div>
    @endif

    {{-- Overview Card --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <h2 class="text-xl font-bold text-slate-800">{{ $tenant->store_name }}</h2>
                <p class="text-xs text-slate-400 font-mono mt-0.5">Tenant ID: {{ $tenant->id }}</p>
                <p class="text-xs text-slate-400 font-mono mt-0.5">Database: {{ $tenant->database_name }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('super.balance-conversion.preview', $tenant->id) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-xs font-bold rounded-lg transition shadow-sm">
                    <i class="fas fa-exchange-alt text-amber-600"></i> Convert Balances
                </a>
                @if($tenant->status === 'pending')
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 uppercase tracking-wider">
                        <i class="fas fa-clock mr-1"></i> Pending Approval
                    </span>
                @elseif($tenant->status === 'active')
                    @if($tenant->paid_until && $tenant->paid_until->lt(now()->startOfDay()))
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Subscription Expired
                        </span>
                    @else
                        <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider">
                            <i class="fas fa-check-circle mr-1"></i> Active
                        </span>
                    @endif
                @elseif($tenant->status === 'suspended')
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200 uppercase tracking-wider">
                        <i class="fas fa-ban mr-1"></i> Suspended
                    </span>
                @elseif($tenant->status === 'rejected')
                    <span class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200 uppercase tracking-wider">
                        <i class="fas fa-times-circle mr-1"></i> Rejected
                    </span>
                @endif
            </div>
        </div>

        {{-- Detail Fields --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Owner Details</h3>
                <div class="space-y-2 text-sm">
                    <p class="text-slate-700"><strong class="text-slate-900">Name:</strong> {{ $tenant->owner_name }}</p>
                    <p class="text-slate-700"><strong class="text-slate-900">Email:</strong> {{ $tenant->owner_email }}</p>
                    <p class="text-slate-700"><strong class="text-slate-900">Phone:</strong> {{ $tenant->owner_phone }}</p>
                </div>
            </div>

            <div>
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Subscription & Metadata</h3>
                <div class="space-y-2 text-sm">
                    <p class="text-slate-700">
                        <strong class="text-slate-900">Paid Until:</strong> 
                        @if($tenant->paid_until)
                            <span class="{{ $tenant->paid_until->lt(now()->startOfDay()) ? 'text-amber-600 font-bold' : 'text-slate-900' }}">
                                {{ $tenant->paid_until->format('Y-m-d') }}
                            </span>
                        @else
                            <span class="text-slate-400 italic">Not set</span>
                        @endif
                    </p>
                    @if($tenant->approved_at)
                        <p class="text-slate-700"><strong class="text-slate-900">Approved At:</strong> {{ $tenant->approved_at->format('Y-m-d H:i') }}</p>
                    @endif
                    @if($tenant->rejected_at)
                        <p class="text-slate-700"><strong class="text-slate-900">Rejected At:</strong> {{ $tenant->rejected_at->format('Y-m-d H:i') }}</p>
                        <p class="text-rose-600"><strong class="text-slate-900">Reason:</strong> {{ $tenant->rejection_reason }}</p>
                    @endif
                    <p class="text-slate-700"><strong class="text-slate-900">Created:</strong> {{ $tenant->created_at ? $tenant->created_at->format('Y-m-d H:i') : 'N/A' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions Section --}}
    @if($tenant->status === 'pending')
        {{-- Approval & Rejection Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-6">
            <h3 class="text-base font-bold text-slate-800">Pending Approval Actions</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Approve / Resume Form --}}
                <div class="{{ $tenant->provisioning_error ? 'bg-amber-50/60 border-amber-300' : 'bg-emerald-50/50 border-emerald-200/60' }} border rounded-xl p-5 space-y-4">
                    @if($tenant->provisioning_error)
                        <h4 class="font-bold text-amber-800 text-sm"><i class="fas fa-redo mr-1"></i>Resume Provisioning</h4>
                        <p class="text-xs text-slate-600">
                            A prior attempt partially failed. Resuming will skip DB creation (database already provisioned on the server) and retry from migrations onward.
                        </p>
                    @else
                        <h4 class="font-bold text-emerald-800 text-sm">Approve Store Request</h4>
                        <p class="text-xs text-slate-600">
                            Approving will provision the tenant database using the cPanel engine, run tenant migrations, set status to Active, and set initial payment period.
                        </p>
                    @endif
                    <form action="{{ route('super.requests.approve', $tenant->id) }}" method="POST" class="space-y-3" onsubmit="return confirmTdApproval(event)">
                        @csrf

                        {{-- Hidden fields — JS toggles which one is sent --}}
                        <input type="hidden" id="td_paid_days"  name="paid_days"  value="30">
                        <input type="hidden" id="td_paid_until" name="paid_until" value="" disabled>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Subscription Duration</label>
                            <select id="tdDuration" onchange="updateTdPreview(this)"
                                    class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 {{ $tenant->provisioning_error ? 'focus:ring-amber-400' : 'focus:ring-emerald-500' }}">
                                <option value="30">1 Month</option>
                                <option value="90">3 Months</option>
                                <option value="180">6 Months</option>
                                <option value="365">1 Year</option>
                                <option value="custom">Custom date…</option>
                            </select>
                        </div>

                        <div id="tdCustomDate" class="hidden">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Custom Paid Until Date</label>
                            <input type="date" id="tdCustomDateInput"
                                   onchange="updateTdPreviewCustom(this)"
                                   min="{{ now()->addDay()->format('Y-m-d') }}"
                                   class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <p class="text-xs text-emerald-700 font-semibold">
                            Valid until: <span id="tdPreviewDate"></span>
                        </p>

                        <button type="submit"
                            class="w-full {{ $tenant->provisioning_error ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition shadow-sm">
                            @if($tenant->provisioning_error)
                                <i class="fas fa-redo mr-1"></i> Approve &amp; Resume
                            @else
                                <i class="fas fa-check mr-1"></i> Approve &amp; Provision Store
                            @endif
                        </button>
                    </form>
                </div>

                {{-- Reject Form --}}
                <div class="bg-rose-50/50 border border-rose-200/60 rounded-xl p-5 space-y-4">
                    <h4 class="font-bold text-rose-800 text-sm">Reject Store Request</h4>
                    <p class="text-xs text-slate-600">
                        Rejecting will mark the application as rejected. No database will be provisioned.
                    </p>
                    <form action="{{ route('super.requests.reject', $tenant->id) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Rejection Reason (Required)</label>
                            <textarea name="rejection_reason" required rows="2" 
                                class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500"
                                placeholder="Enter reason for rejection..."></textarea>
                        </div>
                        <button type="submit" 
                            class="w-full bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition shadow-sm">
                            <i class="fas fa-times mr-1"></i> Reject Application
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @else
        {{-- Active / Suspended Payment & Status Management Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-6">
            <h3 class="text-base font-bold text-slate-800">Manual Payment & Status Controls</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Update Payment Date --}}
                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-5 space-y-3">
                    <h4 class="font-bold text-slate-800 text-sm">Update Manual Payment Date</h4>
                    <p class="text-xs text-slate-500">
                        Extend or set the subscription paid_until date when you collect payment manually.
                    </p>
                    <form action="{{ route('super.tenants.updatePaidUntil', $tenant->id) }}" method="POST" class="space-y-3" onsubmit="return confirmPaidUntil()">
                        @csrf
                        {{-- updatePaidUntil() only accepts paid_until (explicit date), so Custom sets it directly and dropdown options compute it --}}
                        <input type="hidden" id="mu_paid_until" name="paid_until" value="{{ $tenant->paid_until ? $tenant->paid_until->format('Y-m-d') : now()->addDays(30)->format('Y-m-d') }}">

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Extend By</label>
                            <select id="muDuration" onchange="updateMuPreview(this)"
                                    class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="30">+ 1 Month from today</option>
                                <option value="90">+ 3 Months from today</option>
                                <option value="180">+ 6 Months from today</option>
                                <option value="365">+ 1 Year from today</option>
                                <option value="custom">Custom date…</option>
                            </select>
                        </div>

                        <div id="muCustomDate" class="hidden">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Custom Date</label>
                            <input type="date" id="muCustomDateInput"
                                   onchange="updateMuPreviewCustom(this)"
                                   min="{{ now()->addDay()->format('Y-m-d') }}"
                                   value="{{ $tenant->paid_until ? $tenant->paid_until->format('Y-m-d') : now()->addDays(30)->format('Y-m-d') }}"
                                   class="w-full bg-white border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <p class="text-xs text-indigo-700 font-semibold">
                            Will be paid until: <span id="muPreviewDate"></span>
                        </p>

                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition">
                            Save Payment Date
                        </button>
                    </form>
                </div>

                {{-- Suspend / Activate Toggle --}}
                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-5 space-y-3">
                    <h4 class="font-bold text-slate-800 text-sm">Access Suspension Control</h4>
                    <p class="text-xs text-slate-500">
                        Suspension immediately blocks access regardless of paid_until date (e.g. for support issues or policy violations).
                    </p>

                    @if($tenant->status === 'suspended')
                        <form action="{{ route('super.tenants.unsuspend', $tenant->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition">
                                <i class="fas fa-play mr-1"></i> Lift Suspension (Re-activate)
                            </button>
                        </form>
                    @else
                        <form action="{{ route('super.tenants.suspend', $tenant->id) }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Are you sure you want to suspend this store?')" 
                                class="w-full bg-rose-600 hover:bg-rose-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition">
                                <i class="fas fa-ban mr-1"></i> Suspend Store Immediately
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>

<script>
(function () {
    function daysFromNow(days) {
        const d = new Date();
        d.setDate(d.getDate() + parseInt(days));
        return d;
    }
    function fmtHuman(dateObj) {
        return dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }
    function fmtInput(dateObj) { return dateObj.toISOString().split('T')[0]; }

    // ── Pending approval form (td*) ──────────────────────────────────────────
    window.updateTdPreview = function (select) {
        const isCustom    = select.value === 'custom';
        const customBlock = document.getElementById('tdCustomDate');
        const paidDaysEl  = document.getElementById('td_paid_days');
        const paidUntilEl = document.getElementById('td_paid_until');
        const previewEl   = document.getElementById('tdPreviewDate');
        if (isCustom) {
            customBlock.classList.remove('hidden');
            paidDaysEl.disabled = true; paidDaysEl.name = '';
            paidUntilEl.disabled = false; paidUntilEl.name = 'paid_until';
            const ci = document.getElementById('tdCustomDateInput');
            if (!ci.value) { ci.value = fmtInput(daysFromNow(30)); paidUntilEl.value = ci.value; }
            previewEl.textContent = fmtHuman(new Date(paidUntilEl.value + 'T00:00:00'));
        } else {
            customBlock.classList.add('hidden');
            paidDaysEl.disabled = false; paidDaysEl.name = 'paid_days'; paidDaysEl.value = select.value;
            paidUntilEl.disabled = true; paidUntilEl.name = '';
            previewEl.textContent = fmtHuman(daysFromNow(select.value));
        }
    };
    window.updateTdPreviewCustom = function (input) {
        document.getElementById('td_paid_until').value = input.value;
        document.getElementById('tdPreviewDate').textContent = input.value
            ? fmtHuman(new Date(input.value + 'T00:00:00')) : '';
    };
    window.confirmTdApproval = function () {
        const preview = document.getElementById('tdPreviewDate').textContent.trim();
        return confirm('Approve this store? Active until: ' + preview + '.');
    };

    // ── Update paid until form (mu*) ─────────────────────────────────────────
    window.updateMuPreview = function (select) {
        const isCustom    = select.value === 'custom';
        const customBlock = document.getElementById('muCustomDate');
        const paidUntilEl = document.getElementById('mu_paid_until');
        const previewEl   = document.getElementById('muPreviewDate');
        if (isCustom) {
            customBlock.classList.remove('hidden');
            const ci = document.getElementById('muCustomDateInput');
            paidUntilEl.value = ci.value;
            previewEl.textContent = ci.value ? fmtHuman(new Date(ci.value + 'T00:00:00')) : '';
        } else {
            customBlock.classList.add('hidden');
            const d = daysFromNow(select.value);
            paidUntilEl.value = fmtInput(d);
            previewEl.textContent = fmtHuman(d);
        }
    };
    window.updateMuPreviewCustom = function (input) {
        document.getElementById('mu_paid_until').value = input.value;
        document.getElementById('muPreviewDate').textContent = input.value
            ? fmtHuman(new Date(input.value + 'T00:00:00')) : '';
    };
    window.confirmPaidUntil = function () {
        const preview = document.getElementById('muPreviewDate').textContent.trim();
        return confirm('Set paid until ' + preview + '?');
    };

    // Init previews on load
    document.addEventListener('DOMContentLoaded', function () {
        const td = document.getElementById('tdDuration');
        if (td) window.updateTdPreview(td);
        const mu = document.getElementById('muDuration');
        if (mu) window.updateMuPreview(mu);
    });
}());
</script>

@endsection
