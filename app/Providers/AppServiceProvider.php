<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Filament\Support\Facades\FilamentView::registerRenderHook(
            'panels::body.start',
            fn(): string => \Illuminate\Support\Facades\Blade::render(<<<'HTML'
            <style>
                /* --- GLOBAL FIXES --- */
                .fi-topbar, .fi-sidebar, .fi-header { display: none !important; }
                .fi-main { padding: 0 !important; max-width: 100% !important; margin: 0 !important; }
                .fi-body { background: transparent !important; }

                /* --- FIXED GRADIENT BORDER ANIMATION --- */
                @keyframes borderRotate {
                    0% { background-position: 0% 50%; }
                    50% { background-position: 100% 50%; }
                    100% { background-position: 0% 50%; }
                }

                /* Wrapper for INDIVIDUAL buttons only */
                .gradient-btn {
                    position: relative;
                    padding: 2px; /* Border width */
                    border-radius: 0.5rem; /* rounded-lg */
                    background: transparent;
                    transition: all 0.3s ease;
                    display: inline-flex; /* Fixes the width issue */
                }

                /* Hover effect: Only applies to this specific element */
                .gradient-btn:hover {
                    background: linear-gradient(60deg, #6366f1, #ec4899, #8b5cf6, #3b82f6);
                    background-size: 300% 300%;
                    animation: borderRotate 3s ease infinite;
                    box-shadow: 0 0 10px rgba(99, 102, 241, 0.5); /* Glowing effect */
                }

                /* Active State (Permanent Gradient) */
                .gradient-btn.active-tab {
                    background: linear-gradient(60deg, #6366f1, #ec4899, #8b5cf6, #3b82f6);
                    background-size: 300% 300%;
                    box-shadow: 0 0 5px rgba(99, 102, 241, 0.3);
                }

                /* Inner White/Dark Content Box */
                .gradient-inner {
                    background-color: #f9fafb; /* Light mode bg */
                    border-radius: 0.4rem; /* Slightly smaller than wrapper */
                    width: 100%;
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 0.5rem 1.25rem; /* px-5 py-2 */
                    z-index: 10;
                }
                .dark .gradient-inner {
                    background-color: #111827; /* Dark mode bg */
                }
            </style>
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

            <div x-data="{ 
                activeTab: 'sales', 
                mobileMenuOpen: false,
                darkMode: localStorage.getItem('theme') === 'dark',
                toggleTheme() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('theme', this.darkMode ? 'dark' : 'light');
                    if (this.darkMode) { document.documentElement.classList.add('dark'); } 
                    else { document.documentElement.classList.remove('dark'); }
                }
            }" 
            x-init="if(darkMode) document.documentElement.classList.add('dark');"
            class="font-sans antialiased min-h-screen bg-white dark:bg-gray-950 text-gray-900 dark:text-gray-100 pb-10">

                <nav class="bg-white/90 dark:bg-gray-900/90 shadow-lg backdrop-blur-md sticky top-0 z-50 border-b border-gray-200 dark:border-gray-800">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="flex items-center justify-between h-20">
                            
                            <div class="flex items-center gap-8">
                                <a href="#" class="flex items-center gap-2">
                                    <div class="bg-indigo-600 text-white p-2 rounded-lg shadow-md">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    </div>
                                    <span class="text-2xl font-black tracking-tighter text-gray-900 dark:text-white">
                                        OwnStore <span class="text-[10px] uppercase tracking-widest text-indigo-500 ml-1">PRO</span>
                                    </span>
                                </a>

                                <div class="hidden md:flex items-center space-x-3">
                                    <template x-for="tab in ['General', 'Sales', 'Purchase', 'Accounts', 'Reports']">
                                        
                                        <button 
                                            @click="activeTab = tab.toLowerCase()"
                                            class="gradient-btn"
                                            :class="activeTab === tab.toLowerCase() ? 'active-tab' : ''"
                                        >
                                            <div class="gradient-inner group transition-colors">
                                                <span class="text-sm font-bold transition-colors"
                                                      :class="activeTab === tab.toLowerCase() ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white'"
                                                      x-text="tab">
                                                </span>
                                            </div>
                                        </button>
                                        
                                    </template>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <button @click="toggleTheme()" class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 transition">
                                    <span x-show="!darkMode">🌙</span>
                                    <span x-show="darkMode">☀️</span>
                                </button>
                                 <div class="flex items-center gap-3 pl-4 border-l border-gray-200 dark:border-gray-800">
                                    <div class="text-right hidden sm:block">
                                        <div class="text-xs font-bold">Amos Stark</div>
                                        <div class="text-[10px] text-gray-500">Owner</div>
                                    </div>
                                    <div class="h-9 w-9 bg-indigo-600 rounded-full flex items-center justify-center text-white font-bold text-sm">AS</div>
                                </div>
                                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div x-show="mobileMenuOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="md:hidden bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-lg absolute w-full left-0 z-40">
                        <div class="px-2 pt-2 pb-3 space-y-1">
                             <template x-for="tab in ['General', 'Sales', 'Purchase', 'Accounts', 'Reports']">
                                <button 
                                    @click="activeTab = tab.toLowerCase(); mobileMenuOpen = false" 
                                    class="block w-full text-left px-3 py-3 rounded-lg text-base font-bold transition-colors"
                                    :class="activeTab === tab.toLowerCase() ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800'"
                                    x-text="tab">
                                </button>
                            </template>
                        </div>
                    </div>
                </nav>

                <div class="max-w-7xl mx-auto px-2 mb-2 mt-4">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-xl p-2">
                        <div class="flex items-center gap-2 overflow-x-auto p-2">
                            
                            <div x-show="activeTab === 'general'" class="flex gap-4 w-full animate-fadeIn">
                                <a href="{{ route('settings.general') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">🖥️</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">General</span>
                                </a>
                                <a href="#" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">🎨</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Styles</span>
                                </a>
                                <a href="{{ route('settings.users') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">👥</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Access</span>
                                </a>
                                <a href="{{ route('items.create') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">➕</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Add Items</span>
                                </a>
                                <div class="w-px h-10 bg-gray-200 dark:bg-gray-700 mx-2"></div>
                                <a href="{{ route('todo') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">📋</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">To Do</span>
                                </a>
                                <a href="{{ route('reminders.index') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">⏰</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Reminder</span>
                                </a>
                                <a href="{{ route('employees.web.index') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">👔</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Employees</span>
                                </a>
                                <a href="#" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">💾</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Backup</span>
                                </a>
                            </div>

                            <div x-show="activeTab === 'sales'" class="flex gap-4 w-full animate-fadeIn" style="display: none;">
                                <a href="{{ route('sales.pos') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">🏪</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Counter</span>
                                </a>
                                <a href="{{ route('sales.history') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">📜</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">History</span>
                                </a>
                            
                                <div class="w-px h-10 bg-gray-200 dark:bg-gray-700 mx-2"></div>
                                <a href="{{ route('cash-sales.create') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">💵</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Cash Sales</span>
                                </a>
                                <a href="{{ route('debit-sales.index') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">💳</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">CRDT Sales</span>
                                </a>
                                <a href="{{ route('refunds.index') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">↩️</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Refunds</span>
                                </a>
                                <div class="w-px h-10 bg-gray-200 dark:bg-gray-700 mx-2"></div>
                                <a href="{{ route('receipts.index') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">🧾</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Receipts</span>
                                </a>
                                <a href="{{ route('payments.create') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">💰</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Payments</span>
                                </a>
                                <a href="{{route('transfers.create') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">⇄</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Transfers</span>
                                </a>
                                <div class="w-px h-10 bg-gray-200 dark:bg-gray-700 mx-2"></div>
                                <a href="/items" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">📦</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Items</span>
                                </a>
                                <a href="{{route('barcodes.index')}}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">⏸️</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Barcodes</span>
                                </a>
                                <a href="{{route('adjustments.create')}}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">🔧</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Adjust</span>
                                </a>
                                <a href="{{route('hotel.index')}}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">⚙️</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Manage</span>
                                </a>
                            </div>

                            <div x-show="activeTab === 'purchase'" class="flex gap-4 w-full animate-fadeIn" style="display: none;">
                                <a href="{{route('purchases.create')}}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">📄</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Cash Bill</span>
                                </a>
                                <a href="{{route('purchases.create-credit')}}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">📝</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">CRDT Bill</span>
                                </a>
                                <a href="{{route('purchase-orders.create')}}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">📋</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Pur. Order</span>
                                </a>
                                <a href="{{route('purchase-returns.create')}}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">↩️</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Returns</span>
                                </a>
                                <div class="w-px h-10 bg-gray-200 dark:bg-gray-700 mx-2"></div>
                                <a href="{{ route('payments.create') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">💰</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Payments</span>
                                </a>
                                <a href="{{ route('receipts.index') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">🧾</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Receipts</span>
                                </a>
                                <div class="w-px h-10 bg-gray-200 dark:bg-gray-700 mx-2"></div>
                                <a href="/admin/items" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">📦</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Items</span>
                                </a>
                                <a href="{{route('barcodes.index')}}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">⏸️</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Barcodes</span>
                                </a>
                                <a href="{{route('adjustments.create')}}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">🔧</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Adjust</span>
                                </a>
                                <a href="{{ route('suppliers.index') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">🚛</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Suppliers</span>
                                </a>
                            </div>

                            <div x-show="activeTab === 'accounts'" class="flex gap-4 w-full animate-fadeIn" style="display: none;">
                                <a href="{{ route('journals.create') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">📖</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Journal</span>
                                </a>
                                <a href="{{ route('general-ledger.index') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">📚</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">GLedgers</span>
                                </a>
                                <a href="{{ route('reports.accounts') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">💼</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Accounts</span>
                                </a>
                                <a href="{{ route('banks.index') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">🏦</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Banks</span>
                                </a>
                                <a href="#" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">🔍</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Names</span>
                                </a>
                                <a href="#" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">➖</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Missing</span>
                                </a>
                                <a href="{{ route('values.index') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">  <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">💲</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Values</span>
                                </a>
                                <a href="{{ route('reports.index') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">📊</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-amber-600 dark:group-hover:text-amber-400">Reports</span>
                                </a>
                                <a href="#" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">🌐</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Online</span>
                                </a>
                            </div>

                            <div x-show="activeTab === 'reports'" class="flex gap-4 w-full animate-fadeIn" style="display: none;">
                                <a href="{{ route('reports.index') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">📂</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Selected</span>
                                </a>
                                <a href="{{ route('child.create') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">🗗</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Child</span>
                                </a>
                                <a href="{{ route('delete.confirm') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">❌</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Delete</span>
                                </a>
                                <div class="w-px h-10 bg-gray-200 dark:bg-gray-700 mx-2"></div>
                                <a href="{{ route('reports.sales') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">👁️</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">View</span>
                                </a>
                                <a href="#" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">🖨️</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Print</span>
                                </a>
                                <a href="#" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">📧</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Email</span>
                                </a>
                                <div class="w-px h-10 bg-gray-200 dark:bg-gray-700 mx-2"></div>
                                <a href="#" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">💻</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Code</span>
                                </a>
                                <a href="{{ route('reports.layout') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">📰</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Layout</span>
                                </a>
                                <a href="{{ route('reports.restrict') }}" class="flex flex-col items-center justify-center min-w-[70px] p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 transition group gradient-hover-card">
                                    <span class="text-3xl filter drop-shadow-sm group-hover:scale-110 transition-transform">🔒</span>
                                    <span class="text-[11px] font-bold text-gray-600 dark:text-gray-300 mt-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-400">Restrict</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                 
            <div class="max-w-7xl mx-auto px-4 mb-8">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                    <div class="lg:col-span-3 space-y-6">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 flex flex-col border border-gray-100 dark:border-gray-700">
                                <h3 class="text-gray-700 dark:text-gray-200 font-bold mb-4">Paid vs Unpaid Invoices</h3>
                                <div class="relative flex-grow flex items-center justify-center h-64">
                                    <canvas id="paidVsUnpaidChart"></canvas>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 flex flex-col border border-gray-100 dark:border-gray-700">
                                <h3 class="text-gray-700 dark:text-gray-200 font-bold mb-4">Top Customer Balances</h3>
                                <div class="relative flex-grow h-64">
                                    <canvas id="customerBalanceChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                                <h3 class="text-gray-700 dark:text-gray-200 font-bold mb-4">Daily Debit Trend</h3>
                                <div class="h-64">
                                    <canvas id="dailyDebitChart"></canvas>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                                <h3 class="text-gray-700 dark:text-gray-200 font-bold mb-4">Cash Flow (In/Out)</h3>
                                <div class="h-64">
                                    <canvas id="cashFlowChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                            <h3 class="text-gray-700 dark:text-gray-200 font-bold mb-4">Top 5 Best Selling Items</h3>
                            <div class="h-64">
                                <canvas id="topSellingChart"></canvas>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 border border-gray-100 dark:border-gray-700">
                            <h3 class="text-gray-700 dark:text-gray-200 font-bold mb-4">Monthly Sales Performance</h3>
                            <div class="h-80 w-full">
                                <canvas id="monthlySalesChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 h-full sticky top-24 border border-gray-100 dark:border-gray-700">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-gray-700 dark:text-gray-200 font-bold">Recent Activity</h3>
                                <a href="#" class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">View All</a>
                            </div>
                            
                            <div class="space-y-6 relative border-l border-gray-200 dark:border-gray-700 ml-3 pl-6">
                                @foreach($recentActivities as $activity)
                                <div class="relative">
                                    <div class="absolute -left-[31px] bg-white dark:bg-gray-800 border-2 border-white dark:border-gray-800 rounded-full">
                                         <div class="h-8 w-8 rounded-full {{ $activity['bg'] }} flex items-center justify-center ring-4 ring-white dark:ring-gray-800">
                                             <i class="fas {{ $activity['icon'] }} {{ $activity['color'] }} text-xs"></i>
                                         </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $activity['action'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $activity['description'] }}</p>
                                        <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $activity['time'] }}</span>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                             
                            <button class="w-full mt-6 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Load More
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <div id="filament-chart-data" class="hidden"
                data-paid="{{ json_encode($chartData['paid_vs_unpaid']) }}"
                data-debit="{{ json_encode($chartData['daily_debit']) }}"
                data-sales="{{ json_encode($chartData['monthly_sales']) }}"
                data-balance="{{ json_encode($chartData['customer_balance']) }}"
                data-cashflow="{{ json_encode($chartData['cash_flow']) }}"
                data-topselling="{{ json_encode($chartData['top_selling']) }}"
            ></div>

            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const dataElement = document.getElementById('filament-chart-data');
                    if(!dataElement) return;

                    const paidData = JSON.parse(dataElement.dataset.paid);
                    const debitData = JSON.parse(dataElement.dataset.debit);
                    const salesData = JSON.parse(dataElement.dataset.sales);
                    const balanceData = JSON.parse(dataElement.dataset.balance);
                    const cashFlowData = JSON.parse(dataElement.dataset.cashflow);
                    const topSellingData = JSON.parse(dataElement.dataset.topselling);

                    const isDarkMode = document.documentElement.classList.contains('dark');
                    const textColor = isDarkMode ? '#9ca3af' : '#6B7280';
                    const gridColor = isDarkMode ? '#374151' : '#f3f4f6';

                    Chart.defaults.font.family = "'Roboto', sans-serif";
                    Chart.defaults.color = textColor;
                    Chart.defaults.scale.grid.color = gridColor;

                    // 1. Paid vs Unpaid (Doughnut)
                    new Chart(document.getElementById('paidVsUnpaidChart'), {
                        type: 'doughnut',
                        data: {
                            labels: paidData.labels,
                            datasets: [{
                                data: paidData.data,
                                backgroundColor: ['#10B981', '#EF4444'], 
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, color: textColor } } }
                        }
                    });

                    // 2. Customer Balance (Horizontal Bar)
                    new Chart(document.getElementById('customerBalanceChart'), {
                        type: 'bar',
                        data: {
                            labels: balanceData.labels,
                            datasets: [{
                                label: 'Balance (Rs)',
                                data: balanceData.data,
                                backgroundColor: '#6366F1',
                                borderRadius: 4
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { 
                                x: { grid: { display: false }, ticks: { color: textColor } }, 
                                y: { grid: { display: false }, ticks: { color: textColor } } 
                            }
                        }
                    });

                    // 3. Daily Debit (Line)
                    new Chart(document.getElementById('dailyDebitChart'), {
                        type: 'line',
                        data: {
                            labels: debitData.labels,
                            datasets: [{
                                label: 'Debit',
                                data: debitData.data,
                                borderColor: '#F59E0B',
                                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { 
                                y: { beginAtZero: true, grid: { borderDash: [2, 4], color: gridColor }, ticks: { color: textColor } }, 
                                x: { grid: { display: false }, ticks: { color: textColor } } 
                            }
                        }
                    });

                    // 4. Cash Flow (Line)
                    new Chart(document.getElementById('cashFlowChart'), {
                        type: 'line',
                        data: {
                            labels: cashFlowData.labels,
                            datasets: [{
                                label: 'Inflow',
                                data: cashFlowData.inflow,
                                borderColor: '#10B981',
                                tension: 0.3,
                                pointRadius: 0
                            }, {
                                label: 'Outflow',
                                data: cashFlowData.outflow,
                                borderColor: '#EF4444',
                                tension: 0.3,
                                pointRadius: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'top', labels: { usePointStyle: true, boxWidth: 6, color: textColor } } },
                            scales: { 
                                y: { grid: { borderDash: [2, 4], color: gridColor }, ticks: { color: textColor } }, 
                                x: { grid: { display: false }, ticks: { color: textColor } } 
                            }
                        }
                    });

                    // 5. Top 5 Best Selling Items (NEW)
                    new Chart(document.getElementById('topSellingChart'), {
                        type: 'bar',
                        data: {
                            labels: topSellingData.labels,
                            datasets: [{
                                label: 'Units Sold',
                                data: topSellingData.data,
                                backgroundColor: ['#8B5CF6', '#EC4899', '#6366F1', '#3B82F6', '#10B981'],
                                borderRadius: 4
                            }]
                        },
                        options: {
                            indexAxis: 'y', // Horizontal Bar Chart
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { 
                                x: { grid: { display: false }, ticks: { color: textColor } }, 
                                y: { grid: { display: false }, ticks: { color: textColor } } 
                            }
                        }
                    });

                    // 6. Monthly Sales (Bar)
                    new Chart(document.getElementById('monthlySalesChart'), {
                        type: 'bar',
                        data: {
                            labels: salesData.labels,
                            datasets: [{
                                label: 'Sales',
                                data: salesData.data,
                                backgroundColor: '#3B82F6',
                                borderRadius: 4,
                                barPercentage: 0.5
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: { 
                                y: { beginAtZero: true, grid: { borderDash: [2, 4], color: gridColor }, ticks: { color: textColor } }, 
                                x: { grid: { display: false }, ticks: { color: textColor } } 
                            }
                        }
                    });
                });
            </script>

            <audio id="alertSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3" preload="auto"></audio>

            <div id="reminderModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm"></div>

                <div class="fixed inset-0 z-10 overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        
                        <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border-t-4 border-indigo-600">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10 animate-bounce">
                                        <i class="fas fa-bell text-indigo-600 text-lg"></i>
                                    </div>
                                    <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                        <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">Reminder Alert!</h3>
                                        
                                        <div class="mt-4 bg-gray-50 p-3 rounded-lg border border-gray-100">
                                            <p id="reminderTitle" class="text-xl font-bold text-gray-800">Title goes here...</p>
                                            <p class="text-sm text-gray-500 mt-1"><i class="far fa-clock mr-1"></i> Just Now</p>
                                        </div>

                                        <p class="text-sm text-gray-500 mt-4">
                                            This task is due now. Please take action.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="button" onclick="closeReminderModal()" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">
                                    Acknowledge & Close
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <script>
                function checkReminders() {
                    fetch('/reminders/check')
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'found') {
                                // 1. Play Sound
                                const sound = document.getElementById('alertSound');
                                sound.play().catch(error => console.log('Autoplay prevented by browser'));

                                // 2. Populate Data (Show the first one found)
                                const reminder = data.reminders[0];
                                document.getElementById('reminderTitle').innerText = reminder.title;

                                // 3. Show Modal
                                document.getElementById('reminderModal').classList.remove('hidden');
                            }
                        })
                        .catch(error => console.error('Error checking reminders:', error));
                }

                function closeReminderModal() {
                    document.getElementById('reminderModal').classList.add('hidden');
                    // Stop sound if still playing
                    const sound = document.getElementById('alertSound');
                    sound.pause();
                    sound.currentTime = 0;
                }

                // Run check every 30 seconds
                setInterval(checkReminders, 30000);
                
                // Run once immediately on load
                document.addEventListener('DOMContentLoaded', () => {
                    checkReminders();
                });
            </script>
            HTML, [
                'recentActivities' => [
                    ['action' => 'Sale', 'description' => 'New sale recorded #INV-001', 'time' => '10 mins ago', 'icon' => 'fa-shopping-cart', 'color' => 'text-green-600', 'bg' => 'bg-green-100'],
                    ['action' => 'Stock', 'description' => 'Low stock alert: Panadol', 'time' => '1 hour ago', 'icon' => 'fa-exclamation-triangle', 'color' => 'text-orange-600', 'bg' => 'bg-orange-100'],
                    ['action' => 'Payment', 'description' => 'Payment received from John', 'time' => '2 hours ago', 'icon' => 'fa-money-bill-wave', 'color' => 'text-blue-600', 'bg' => 'bg-blue-100'],
                    ['action' => 'User', 'description' => 'New user registered', 'time' => '5 hours ago', 'icon' => 'fa-user-plus', 'color' => 'text-purple-600', 'bg' => 'bg-purple-100'],
                    ['action' => 'Return', 'description' => 'Item returned #REC-009', 'time' => '1 day ago', 'icon' => 'fa-undo', 'color' => 'text-red-600', 'bg' => 'bg-red-100'],
                ],
                'chartData' => [
                    'paid_vs_unpaid' => ['labels' => ['Paid', 'Unpaid'], 'data' => [65, 35]],
                    'daily_debit' => ['labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], 'data' => [5000, 7000, 4000, 8000, 6000, 9000, 12000]],
                    'monthly_sales' => ['labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], 'data' => [45000, 52000, 48000, 61000, 58000, 75000, 82000, 79000, 86000, 91000, 95000, 108000]],
                    'customer_balance' => ['labels' => ['John Doe', 'Jane Smith', 'Ali Khan', 'Mike Ross', 'Sarah Lee'], 'data' => [15000, 12000, 9500, 8000, 5000]],
                    'cash_flow' => ['labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'], 'inflow' => [30000, 45000, 32000, 50000], 'outflow' => [20000, 25000, 22000, 28000]],
                    'top_selling' => ['labels' => ['Milkpak 1L', 'Basmati Rice', 'Sugar', 'Dawn Bread', 'Cooking Oil'], 'data' => [450, 320, 280, 250, 200]],
                ]
            ])
        );
    }
}
