@extends('super_admin.layout')

@section('title', 'Store Requests')
@section('header', 'Pending Store Requests')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full whitespace-nowrap">
            <thead>
                <tr class="text-left font-bold text-gray-500 border-b border-gray-100 bg-gray-50 uppercase text-xs tracking-wider">
                    <th class="px-6 py-4">Store Name</th>
                    <th class="px-6 py-4">Owner</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Plan</th>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($requests as $request)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-800">{{ $request->store_name }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-800">{{ $request->owner_name }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-500">{{ $request->owner_email }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-50 text-purple-600 border border-purple-100 uppercase">
                            {{ $request->subscription_plan }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-500">{{ $request->created_at->format('M d, Y') }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-amber-50 text-amber-600 border border-amber-100 uppercase">
                            Pending
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('super.requests.show', $request->id) }}" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">Review</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        No pending requests found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $requests->links() }}
    </div>
</div>
@endsection