@extends('super_admin.layout')

@section('title', 'Subscription Plans')
@section('header', 'Subscription Plans')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Free Plan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col">
        <h3 class="text-xl font-bold text-gray-800">Free</h3>
        <p class="text-gray-500 mt-2">Perfect for starters.</p>
        <div class="mt-4 mb-6">
            <span class="text-4xl font-bold text-gray-900">$0</span>
            <span class="text-gray-500">/month</span>
        </div>
        <ul class="space-y-3 mb-6 flex-1 text-sm text-gray-600">
            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i> 50 Products</li>
            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i> Basic Support</li>
            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i> 1 User</li>
        </ul>
        <button class="w-full py-2 px-4 border border-indigo-600 text-indigo-600 rounded-lg hover:bg-indigo-50 font-medium transition-colors">Edit Plan</button>
    </div>

    <!-- Basic Plan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col relative overflow-hidden">
        <div class="absolute top-0 right-0 bg-indigo-600 text-white text-xs px-2 py-1 rounded-bl-lg">Popular</div>
        <h3 class="text-xl font-bold text-gray-800">Basic</h3>
        <p class="text-gray-500 mt-2">For growing businesses.</p>
        <div class="mt-4 mb-6">
            <span class="text-4xl font-bold text-gray-900">$29</span>
            <span class="text-gray-500">/month</span>
        </div>
        <ul class="space-y-3 mb-6 flex-1 text-sm text-gray-600">
            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i> 500 Products</li>
            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i> Priority Support</li>
            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i> 5 Users</li>
        </ul>
        <button class="w-full py-2 px-4 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-medium transition-colors">Edit Plan</button>
    </div>

    <!-- Pro Plan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col">
        <h3 class="text-xl font-bold text-gray-800">Pro</h3>
        <p class="text-gray-500 mt-2">Unlimited power.</p>
        <div class="mt-4 mb-6">
            <span class="text-4xl font-bold text-gray-900">$99</span>
            <span class="text-gray-500">/month</span>
        </div>
        <ul class="space-y-3 mb-6 flex-1 text-sm text-gray-600">
            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i> Unlimited Products</li>
            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i> 24/7 Dedicated Support</li>
            <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i> Unlimited Users</li>
        </ul>
        <button class="w-full py-2 px-4 border border-indigo-600 text-indigo-600 rounded-lg hover:bg-indigo-50 font-medium transition-colors">Edit Plan</button>
    </div>
</div>
@endsection