@extends('super_admin.layout')

@section('title', 'Review Request')
@section('header', 'Review Store Request')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Back Button -->
    <a href="{{ route('super.requests.index') }}" class="inline-flex items-center text-gray-500 hover:text-gray-700 mb-6">
        <i class="fas fa-arrow-left mr-2"></i> Back to Requests
    </a>

    <!-- Notification Messages -->
    @if(session('error'))
    <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-circle text-red-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        </div>
    </div>
    @endif

    @if(session('success'))
    <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-check-circle text-green-500"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Provisioning Error Banner (shown when a prior Approve attempt partially failed) --}}
    @if($tenant->provisioning_error)
    <div class="mb-5 bg-amber-50 border border-amber-300 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center mt-0.5">
                <i class="fas fa-exclamation-triangle text-amber-600 text-sm"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-amber-800">Partial Provisioning Failure Detected</p>
                <p class="text-xs text-amber-700 mt-1">
                    A previous approval attempt failed partway through. The database may already exist on the server.
                    Clicking <strong>Approve &amp; Resume</strong> will skip database creation if it already exists and retry from the failed step.
                </p>
                <details class="mt-2">
                    <summary class="text-xs font-semibold text-amber-700 cursor-pointer hover:text-amber-900">Show error details</summary>
                    <pre class="mt-2 text-xs bg-amber-100 border border-amber-200 rounded p-2 whitespace-pre-wrap break-all text-amber-900 font-mono">{{ $tenant->provisioning_error }}</pre>
                </details>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="p-6 md:p-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $tenant->store_name }}</h3>
                    <p class="text-gray-500 mt-1">Request ID: #{{ $tenant->id }}</p>
                </div>
                <span class="px-3 py-1 text-sm font-semibold rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                    Pending Review
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Store Info -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Store Information</h4>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Store Name</dt>
                            <dd class="mt-1 text-base text-gray-900 font-medium">{{ $tenant->store_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Requested Plan</dt>
                            <dd class="mt-1 text-base text-gray-900">
                                <span class="uppercase font-bold text-indigo-600">{{ $tenant->subscription_plan }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Request Date</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ $tenant->created_at->format('F d, Y h:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Database Name</dt>
                            <dd class="mt-1 text-xs font-mono bg-slate-50 text-slate-600 px-2 py-1 rounded border border-slate-200 break-all">{{ $tenant->database_name }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Owner Info -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">Owner Information</h4>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Owner Name</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ $tenant->owner_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ $tenant->owner_email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone Number</dt>
                            <dd class="mt-1 text-base text-gray-900">{{ $tenant->owner_phone ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-gray-100">

                {{-- Duration Selector + Approve Form --}}
                <div class="mb-5 bg-slate-50 border border-slate-200 rounded-xl p-5" id="approveBlock">
                    <h4 class="text-sm font-bold text-slate-700 mb-3">
                        @if($tenant->provisioning_error)
                            <i class="fas fa-redo text-amber-600 mr-1"></i> Resume Provisioning — Set Subscription Duration
                        @else
                            <i class="fas fa-calendar-alt text-indigo-500 mr-1"></i> Set Subscription Duration
                        @endif
                    </h4>

                    <form id="approveForm"
                          action="{{ route('super.requests.approve', $tenant->id) }}"
                          method="POST"
                          onsubmit="return confirmApproval(event)">
                        @csrf

                        {{-- Hidden fields — JS toggles which one is sent --}}
                        <input type="hidden" id="approve_paid_days"  name="paid_days"  value="30">
                        <input type="hidden" id="approve_paid_until" name="paid_until" value="" disabled>

                        {{-- Duration Dropdown --}}
                        <div class="mb-3">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Duration</label>
                            <select id="approveDuration"
                                    onchange="updateApprovePreview(this)"
                                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 bg-white focus:outline-none focus:ring-2 {{ $tenant->provisioning_error ? 'focus:ring-amber-400' : 'focus:ring-indigo-400' }}">
                                <option value="30">1 Month</option>
                                <option value="90">3 Months</option>
                                <option value="180">6 Months</option>
                                <option value="365">1 Year</option>
                                <option value="custom">Custom date…</option>
                            </select>
                        </div>

                        {{-- Custom date picker (hidden until "Custom" selected) --}}
                        <div id="approveCustomDate" class="mb-3 hidden">
                            <label class="block text-xs font-semibold text-slate-600 mb-1.5">Custom Paid Until Date</label>
                            <input type="date"
                                   id="approveCustomDateInput"
                                   onchange="updateApprovePreviewCustom(this)"
                                   min="{{ now()->addDay()->format('Y-m-d') }}"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        </div>

                        {{-- Live preview --}}
                        <p id="approvePreview" class="text-xs text-indigo-600 font-semibold mb-4">
                            Subscription valid until: <span id="approvePreviewDate"></span>
                        </p>

                        <div class="flex flex-col sm:flex-row gap-3 justify-end">
                            <!-- Reject Button -->
                            <button type="button"
                                    onclick="document.getElementById('rejectModal').classList.remove('hidden')"
                                    class="px-5 py-2.5 bg-white border border-red-300 text-red-700 font-medium rounded-lg hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-400 transition-colors text-sm">
                                <i class="fas fa-times mr-2"></i> Reject Request
                            </button>

                            <!-- Approve Button -->
                            <button type="submit"
                                    class="px-6 py-2.5 {{ $tenant->provisioning_error ? 'bg-amber-600 hover:bg-amber-700 focus:ring-amber-500' : 'bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500' }} text-white font-semibold rounded-lg shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition-all text-sm flex items-center justify-center">
                                @if($tenant->provisioning_error)
                                    <i class="fas fa-redo mr-2"></i> Approve &amp; Resume
                                @else
                                    <i class="fas fa-check mr-2"></i> Approve &amp; Create Store
                                @endif
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Approve duration JS --}}
<script>
(function () {
    // Duration map: days -> Date string
    function daysFromNow(days) {
        const d = new Date();
        d.setDate(d.getDate() + parseInt(days));
        return d;
    }

    function formatDateHuman(dateObj) {
        return dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function formatDateInput(dateObj) {
        return dateObj.toISOString().split('T')[0]; // YYYY-MM-DD
    }

    window.updateApprovePreview = function (select) {
        const isCustom = select.value === 'custom';
        const customBlock = document.getElementById('approveCustomDate');
        const paidDaysEl  = document.getElementById('approve_paid_days');
        const paidUntilEl = document.getElementById('approve_paid_until');
        const previewEl   = document.getElementById('approvePreviewDate');

        if (isCustom) {
            customBlock.classList.remove('hidden');
            // Switch to paid_until mode
            paidDaysEl.disabled  = true;
            paidDaysEl.name      = ''; // don't submit
            paidUntilEl.disabled = false;
            paidUntilEl.name     = 'paid_until';
            // Seed custom date input with today+30 if empty
            const ci = document.getElementById('approveCustomDateInput');
            if (!ci.value) {
                ci.value = formatDateInput(daysFromNow(30));
                paidUntilEl.value = ci.value;
            }
            previewEl.textContent = formatDateHuman(new Date(paidUntilEl.value + 'T00:00:00'));
        } else {
            customBlock.classList.add('hidden');
            // Switch back to paid_days mode
            paidDaysEl.disabled  = false;
            paidDaysEl.name      = 'paid_days';
            paidDaysEl.value     = select.value;
            paidUntilEl.disabled = true;
            paidUntilEl.name     = '';
            previewEl.textContent = formatDateHuman(daysFromNow(select.value));
        }
    };

    window.updateApprovePreviewCustom = function (input) {
        const paidUntilEl = document.getElementById('approve_paid_until');
        paidUntilEl.value = input.value;
        const previewEl   = document.getElementById('approvePreviewDate');
        previewEl.textContent = input.value
            ? new Date(input.value + 'T00:00:00').toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })
            : '';
    };

    window.confirmApproval = function (e) {
        const preview = document.getElementById('approvePreviewDate').textContent.trim();
        const storeName = {{ Js::from($tenant->store_name) }};
        return confirm(
            '{{ $tenant->provisioning_error ? 'Resume provisioning for ' : 'Approve store: ' }}'
            + storeName
            + '? Active until: ' + preview + '.'
        );
    };

    // Init preview on page load (default = 30 days)
    document.addEventListener('DOMContentLoaded', function () {
        const sel = document.getElementById('approveDuration');
        if (sel) window.updateApprovePreview(sel);
    });
}());
</script>

<!-- Reject Modal -->
<div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <form action="{{ route('super.requests.reject', $tenant->id) }}" method="POST">
            @csrf
            <div class="p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Reject Store Request</h3>
                <p class="text-gray-500 mb-4">Are you sure you want to reject this request? The owner will be notified.</p>

                <div class="mb-4">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Rejection (Optional)</label>
                <textarea name="rejection_reason" id="reason" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2" placeholder="e.g. Invalid document information..." required></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">Confirm Reject</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection