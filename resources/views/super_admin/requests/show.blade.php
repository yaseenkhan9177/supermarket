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

            <div class="mt-10 pt-6 border-t border-gray-100 flex flex-col md:flex-row gap-4 justify-end">
                <!-- Reject Button -->
                <button onclick="document.getElementById('rejectModal').classList.remove('hidden')" class="px-6 py-3 bg-white border border-red-300 text-red-700 font-medium rounded-lg hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                    <i class="fas fa-times mr-2"></i> Reject Request
                </button>

                <!-- Approve Button -->
                <form action="{{ route('super.requests.approve', $tenant->id) }}" method="POST" onsubmit="return confirm('Are you sure? This will create the database and send credentials.')">
                    @csrf
                    <button type="submit" class="w-full md:w-auto px-6 py-3 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all flex items-center justify-center">
                        <i class="fas fa-check mr-2"></i> Approve & Create Store
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

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
                    <textarea name="reason" id="reason" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm border p-2" placeholder="e.g. Invalid document information..."></textarea>
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