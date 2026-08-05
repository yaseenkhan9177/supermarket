@extends('super_admin.layout')

@section('title', 'Store Requests')
@section('header', 'Store Requests')
@section('subheader', 'Review and approve pending store registration requests')

@section('content')
<div class="space-y-5">

    {{-- Info Banner --}}
    @if($requests->total() > 0)
    <div class="flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-2xl">
        <div class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-clock text-amber-600 text-sm"></i>
        </div>
        <div>
            <p class="text-sm font-semibold text-amber-800">{{ $requests->total() }} request{{ $requests->total() > 1 ? 's' : '' }} awaiting your review</p>
            <p class="text-xs text-amber-600">Review each request carefully before approving. Approving will provision a full database and user account.</p>
        </div>
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full whitespace-nowrap text-sm">
                <thead>
                    <tr class="bg-slate-50 text-left text-xs font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100">
                        <th class="px-6 py-3.5">Store</th>
                        <th class="px-6 py-3.5">Owner</th>
                        <th class="px-6 py-3.5">Contact</th>
                        <th class="px-6 py-3.5">Plan</th>
                        <th class="px-6 py-3.5">Submitted</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($requests as $request)
                    <tr class="hover:bg-amber-50/40 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center text-amber-700 font-bold text-sm flex-shrink-0">
                                    {{ strtoupper(substr($request->store_name, 0, 1)) }}
                                </div>
                                <p class="font-semibold text-slate-800">{{ $request->store_name }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-700">{{ $request->owner_name }}</td>
                        <td class="px-6 py-4">
                            <p class="text-slate-600">{{ $request->owner_email }}</p>
                            @if($request->owner_phone)
                                <p class="text-xs text-slate-400 mt-0.5">{{ $request->owner_phone }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-purple-50 text-purple-700 border border-purple-100 uppercase">
                                {{ $request->subscription_plan ?? 'Free' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-xs text-slate-400">
                            <p>{{ $request->created_at->format('d M Y') }}</p>
                            <p class="text-slate-300">{{ $request->created_at->diffForHumans() }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col gap-1">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-100 uppercase">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>Pending
                                </span>
                                @if($request->provisioning_error)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-full bg-rose-50 text-rose-700 border border-rose-200 uppercase"
                                          title="{{ $request->provisioning_error }}">
                                        <i class="fas fa-exclamation-triangle text-xs"></i> Prov. Failed
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                {{-- View Details --}}
                                <a href="{{ route('super.requests.show', $request->id) }}"
                                   class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">
                                    <i class="fas fa-eye mr-1"></i>Review
                                </a>

                                {{-- Quick Approve --}}
                                <form action="{{ route('super.requests.approve', $request->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Approve store \"{{ addslashes($request->store_name) }}\"? This will create a database and user account.')">
                                    @csrf
                                    <button type="submit"
                                        class="px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-sm shadow-emerald-500/20">
                                        <i class="fas fa-check mr-1"></i>Approve
                                    </button>
                                </form>

                                {{-- Quick Reject — opens shared modal to collect reason before posting --}}
                                <button type="button"
                                    onclick="openRejectModal(
                                        '{{ route('super.requests.reject', $request->id) }}',
                                        '{{ addslashes($request->store_name) }}',
                                        '{{ addslashes($request->owner_name) }}'
                                    )"
                                    class="px-3 py-1.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors shadow-sm shadow-rose-500/20">
                                    <i class="fas fa-times mr-1"></i>Reject
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-20 text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-50 mb-4">
                                <i class="fas fa-check-circle text-3xl text-emerald-400"></i>
                            </div>
                            <p class="text-slate-600 font-semibold">All caught up!</p>
                            <p class="text-slate-400 text-sm mt-1">No pending store requests at this time.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
        <div class="px-6 py-4 border-t border-slate-100">
            {{ $requests->links() }}
        </div>
        @endif
    </div>
</div>

{{-- ===== Quick Reject Modal (shared across all rows) ===== --}}
<div id="quickRejectModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     role="dialog" aria-modal="true" aria-labelledby="qrModalTitle">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closeRejectModal()"></div>

    {{-- Panel --}}
    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">

        {{-- Header --}}
        <div class="px-6 pt-6 pb-4 border-b border-slate-100">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-times-circle text-rose-500"></i>
                    </div>
                    <div>
                        <h3 id="qrModalTitle" class="text-base font-bold text-slate-800">Reject Store Request</h3>
                        <p id="qrModalSubtitle" class="text-xs text-slate-400 mt-0.5"></p>
                    </div>
                </div>
                <button onclick="closeRejectModal()" class="text-slate-400 hover:text-slate-600 transition-colors mt-0.5">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <form id="quickRejectForm" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <div>
                <label for="qr_rejection_reason" class="block text-xs font-semibold text-slate-600 mb-1.5">
                    Rejection Reason <span class="text-rose-500">*</span>
                </label>
                <textarea
                    id="qr_rejection_reason"
                    name="rejection_reason"
                    rows="4"
                    required
                    maxlength="1000"
                    placeholder="Provide a clear reason for rejecting this store request…"
                    class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 focus:border-transparent resize-none"></textarea>
                <p class="text-xs text-slate-400 mt-1">This reason is logged and kept on record for audit purposes.</p>
            </div>

            <div class="flex gap-3 justify-end pt-2">
                <button type="button" onclick="closeRejectModal()"
                    class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors shadow-sm">
                    <i class="fas fa-times mr-1.5"></i>Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRejectModal(actionUrl, storeName, ownerName) {
    const modal   = document.getElementById('quickRejectModal');
    const form    = document.getElementById('quickRejectForm');
    const reason  = document.getElementById('qr_rejection_reason');
    const subtitle = document.getElementById('qrModalSubtitle');

    form.action = actionUrl;
    subtitle.textContent = storeName + ' — ' + ownerName;
    reason.value = '';

    modal.classList.remove('hidden');
    reason.focus();
}

function closeRejectModal() {
    document.getElementById('quickRejectModal').classList.add('hidden');
}

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeRejectModal();
});
</script>

@endsection