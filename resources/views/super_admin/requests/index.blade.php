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
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-full bg-amber-50 text-amber-700 border border-amber-100 uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>Pending
                            </span>
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

                                {{-- Quick Reject --}}
                                <form action="{{ route('super.requests.reject', $request->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Reject store request from \"{{ addslashes($request->owner_name) }}\"?')">
                                    @csrf
                                    <button type="submit"
                                        class="px-3 py-1.5 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg transition-colors shadow-sm shadow-rose-500/20">
                                        <i class="fas fa-times mr-1"></i>Reject
                                    </button>
                                </form>
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
@endsection