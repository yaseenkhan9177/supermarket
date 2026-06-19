@extends('super_admin.layout')

@section('title', 'Plans & Pricing')
@section('header', 'Plans & Pricing')
@section('subheader', 'Manage subscription plans available to tenants')

@section('content')
<div class="space-y-6">

    {{-- Stats Row --}}
    <div class="grid grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 text-center">
            <p class="text-3xl font-extrabold text-slate-800">3</p>
            <p class="text-xs text-slate-400 font-medium mt-1 uppercase tracking-wider">Active Plans</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 text-center">
            <p class="text-3xl font-extrabold text-indigo-600">$128</p>
            <p class="text-xs text-slate-400 font-medium mt-1 uppercase tracking-wider">Avg. MRR</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 text-center">
            <p class="text-3xl font-extrabold text-emerald-600">$99</p>
            <p class="text-xs text-slate-400 font-medium mt-1 uppercase tracking-wider">Top Plan</p>
        </div>
    </div>

    {{-- Plans Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- Free Plan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-7 flex flex-col hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center">
                    <i class="fas fa-seedling text-slate-500 text-lg"></i>
                </div>
                <span class="text-xs font-bold text-slate-400 bg-slate-100 px-2.5 py-1 rounded-full uppercase">Basic</span>
            </div>
            <h3 class="text-xl font-extrabold text-slate-800">Free</h3>
            <p class="text-slate-400 text-sm mt-1 mb-5">Perfect for small stores getting started.</p>
            <div class="mb-6">
                <span class="text-4xl font-extrabold text-slate-800">$0</span>
                <span class="text-slate-400 text-sm">/month</span>
            </div>
            <ul class="space-y-2.5 mb-7 flex-1 text-sm">
                <li class="flex items-center gap-2 text-slate-600"><i class="fas fa-check text-emerald-500 w-4 flex-shrink-0"></i>50 Products</li>
                <li class="flex items-center gap-2 text-slate-600"><i class="fas fa-check text-emerald-500 w-4 flex-shrink-0"></i>Email Support</li>
                <li class="flex items-center gap-2 text-slate-600"><i class="fas fa-check text-emerald-500 w-4 flex-shrink-0"></i>1 User Account</li>
                <li class="flex items-center gap-2 text-slate-300"><i class="fas fa-times text-slate-300 w-4 flex-shrink-0"></i>Custom Domain</li>
                <li class="flex items-center gap-2 text-slate-300"><i class="fas fa-times text-slate-300 w-4 flex-shrink-0"></i>Advanced Reports</li>
            </ul>
            <button class="w-full py-2.5 px-4 border-2 border-indigo-200 text-indigo-600 rounded-xl hover:bg-indigo-50 font-semibold text-sm transition-colors">
                Edit Plan
            </button>
        </div>

        {{-- Basic Plan (Popular) --}}
        <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl shadow-xl p-7 flex flex-col relative text-white transform scale-[1.02]">
            <div class="absolute top-4 right-4">
                <span class="bg-white/20 text-white text-[10px] font-bold px-2.5 py-1 rounded-full uppercase backdrop-blur-sm">⭐ Popular</span>
            </div>
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                    <i class="fas fa-rocket text-white text-lg"></i>
                </div>
            </div>
            <h3 class="text-xl font-extrabold">Basic</h3>
            <p class="text-indigo-200 text-sm mt-1 mb-5">For growing businesses that need more power.</p>
            <div class="mb-6">
                <span class="text-4xl font-extrabold">$29</span>
                <span class="text-indigo-300 text-sm">/month</span>
            </div>
            <ul class="space-y-2.5 mb-7 flex-1 text-sm">
                <li class="flex items-center gap-2 text-white"><i class="fas fa-check text-indigo-200 w-4 flex-shrink-0"></i>500 Products</li>
                <li class="flex items-center gap-2 text-white"><i class="fas fa-check text-indigo-200 w-4 flex-shrink-0"></i>Priority Support</li>
                <li class="flex items-center gap-2 text-white"><i class="fas fa-check text-indigo-200 w-4 flex-shrink-0"></i>5 User Accounts</li>
                <li class="flex items-center gap-2 text-white"><i class="fas fa-check text-indigo-200 w-4 flex-shrink-0"></i>Custom Domain</li>
                <li class="flex items-center gap-2 text-indigo-300"><i class="fas fa-times text-indigo-300 w-4 flex-shrink-0"></i>Advanced Reports</li>
            </ul>
            <button class="w-full py-2.5 px-4 bg-white text-indigo-700 rounded-xl hover:bg-indigo-50 font-bold text-sm transition-colors shadow-lg">
                Edit Plan
            </button>
        </div>

        {{-- Pro Plan --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-7 flex flex-col hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center">
                    <i class="fas fa-crown text-amber-500 text-lg"></i>
                </div>
                <span class="text-xs font-bold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full uppercase">Enterprise</span>
            </div>
            <h3 class="text-xl font-extrabold text-slate-800">Pro</h3>
            <p class="text-slate-400 text-sm mt-1 mb-5">Unlimited power for enterprise-level stores.</p>
            <div class="mb-6">
                <span class="text-4xl font-extrabold text-slate-800">$99</span>
                <span class="text-slate-400 text-sm">/month</span>
            </div>
            <ul class="space-y-2.5 mb-7 flex-1 text-sm">
                <li class="flex items-center gap-2 text-slate-600"><i class="fas fa-check text-emerald-500 w-4 flex-shrink-0"></i>Unlimited Products</li>
                <li class="flex items-center gap-2 text-slate-600"><i class="fas fa-check text-emerald-500 w-4 flex-shrink-0"></i>24/7 Dedicated Support</li>
                <li class="flex items-center gap-2 text-slate-600"><i class="fas fa-check text-emerald-500 w-4 flex-shrink-0"></i>Unlimited Users</li>
                <li class="flex items-center gap-2 text-slate-600"><i class="fas fa-check text-emerald-500 w-4 flex-shrink-0"></i>Custom Domain</li>
                <li class="flex items-center gap-2 text-slate-600"><i class="fas fa-check text-emerald-500 w-4 flex-shrink-0"></i>Advanced Reports</li>
            </ul>
            <button class="w-full py-2.5 px-4 border-2 border-amber-200 text-amber-600 rounded-xl hover:bg-amber-50 font-semibold text-sm transition-colors">
                Edit Plan
            </button>
        </div>
    </div>
</div>
@endsection