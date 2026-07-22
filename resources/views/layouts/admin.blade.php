<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>{{ $pageTitle ?? 'Dashboard' }} | OwnStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#312E81', // Dark Purple
                        secondary: '#4338CA', // Lighter Purple
                        slate: {
                            850: '#151f32',
                            950: '#020617'
                        } // Custom dark shades
                    },
                    fontFamily: {
                        sans: ['Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .group:hover .group-hover\:block {
            display: block;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        [x-cloak] {
            display: none !important;
        }

        /* --- UI OVERHAUL STYLES --- */
        @keyframes borderRotate {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .gradient-border-wrapper {
            position: relative;
            z-index: 1;
        }

        .gradient-border-wrapper::before {
            content: '';
            position: absolute;
            inset: -2px;
            border-radius: inherit;
            background: linear-gradient(60deg, #6366f1, #ec4899, #8b5cf6, #3b82f6);
            background-size: 300% 300%;
            animation: borderRotate 3s ease infinite;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .gradient-border-wrapper:hover::before {
            opacity: 1;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .dark .glass-panel {
            background: rgba(17, 24, 39, 0.7);
        }
    </style>
</head>

<body class="bg-slate-100 dark:bg-slate-900 font-sans leading-normal tracking-normal flex flex-col min-h-screen transition-colors duration-300"
    x-data="adminState()"
    :class="{ 'dark': isDark }">

    <!-- TOP NAVBAR -->
    <nav class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">

            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-store text-lg"></i>
                </div>

                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-gray-900 leading-none tracking-tight">
                        OwnStore <span class="text-indigo-600">PRO</span>
                    </h1>
                    <span class="text-xs text-gray-500 font-medium mt-0.5">@yield('navbar_subtitle', 'Dashboard')</span>
                </div>
            </div>

            <div class="flex items-center gap-4">
                @include('partials.global_search')

                @yield('navbar_actions')

                {{-- ======================================================
                     SALES — visible to: owner, manager, cashier
                     ====================================================== --}}
                @hasanyrole('owner|manager|cashier')
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open" id="nav-sales-btn" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                        <i class="fas fa-cash-register"></i>
                        Sales <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                        <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                            <a href="{{ route('sales.pos') }}" id="nav-pos" class="block text-base text-gray-500 dark:text-indigo-200 hover:text-indigo-600 dark:hover:text-white hover:bg-indigo-50 dark:hover:bg-[#4338CA]/50 px-3 py-2 rounded-lg transition-colors" role="menuitem">
                                Counter Sales (POS)
                            </a>
                            <a href="{{ route('cash-sales.create') }}" id="nav-cash-sales" class="block text-base text-gray-500 dark:text-indigo-200 hover:text-indigo-600 dark:hover:text-white hover:bg-indigo-50 dark:hover:bg-[#4338CA]/50 px-3 py-2 rounded-lg transition-colors" role="menuitem">
                                Cash Sales
                            </a>
                            <a href="{{ route('debit-sales.create') }}" id="nav-debit-sales" class="block text-base text-gray-500 dark:text-indigo-200 hover:text-indigo-600 dark:hover:text-white hover:bg-indigo-50 dark:hover:bg-[#4338CA]/50 px-3 py-2 rounded-lg transition-colors" role="menuitem">
                                Debit Sales
                            </a>
                        </div>
                    </div>
                </div>
                @endhasanyrole

                {{-- ======================================================
                     REPORTS — visible to: owner, manager (dropdown)
                     ====================================================== --}}
                @hasanyrole('owner|manager')
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open" id="nav-reports-btn" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                        <i class="fas fa-chart-pie"></i>
                        Reports <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-52 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                        <div class="py-1" role="menu">
                            <a href="{{ route('reports.index') }}" id="nav-reports" class="flex items-center gap-2 text-base text-gray-500 dark:text-indigo-200 hover:text-indigo-600 dark:hover:text-white hover:bg-indigo-50 dark:hover:bg-[#4338CA]/50 px-3 py-2 rounded-lg transition-colors">
                                <i class="fas fa-chart-bar w-4 text-center"></i> All Reports
                            </a>
                            <a href="{{ route('reports.profit-loss') }}" id="nav-profit-loss" class="flex items-center gap-2 text-base text-gray-500 dark:text-indigo-200 hover:text-indigo-600 dark:hover:text-white hover:bg-indigo-50 dark:hover:bg-[#4338CA]/50 px-3 py-2 rounded-lg transition-colors">
                                <i class="fas fa-file-invoice-dollar w-4 text-center"></i> Profit & Loss
                            </a>
                            <a href="{{ route('reports.daily-closing') }}" id="nav-daily-closing" class="flex items-center gap-2 text-base text-gray-500 dark:text-indigo-200 hover:text-indigo-600 dark:hover:text-white hover:bg-indigo-50 dark:hover:bg-[#4338CA]/50 px-3 py-2 rounded-lg transition-colors">
                                <i class="fas fa-cash-register w-4 text-center"></i> Daily Closing
                            </a>
                            @role('owner')
                            <a href="{{ route('reports.audit-log') }}" id="nav-audit-log" class="flex items-center gap-2 text-base text-gray-500 dark:text-indigo-200 hover:text-indigo-600 dark:hover:text-white hover:bg-indigo-50 dark:hover:bg-[#4338CA]/50 px-3 py-2 rounded-lg transition-colors">
                                <i class="fas fa-shield-alt w-4 text-center"></i> Audit Log
                            </a>
                            @endrole
                        </div>
                    </div>
                </div>
                @endhasanyrole

                {{-- ======================================================
                     GODAMS — visible to: owner, manager, warehouse
                     ====================================================== --}}
                @hasanyrole('owner|manager|warehouse')
                <a href="{{ route('purchase-orders.index') }}" id="nav-po" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                    <i class="fas fa-file-invoice"></i>
                    Purchase Orders
                </a>

                <a href="{{ route('godams.index') }}" id="nav-godams" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                    <i class="fas fa-warehouse"></i>
                    Godams
                </a>

                <a href="{{ route('stock-transfers.index') }}" id="nav-transfers" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                    <i class="fas fa-exchange-alt"></i>
                    Transfers
                </a>
                @endhasanyrole

                {{-- ======================================================
                     SETTINGS — visible to: owner only
                     ====================================================== --}}
                @role('owner')
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open" id="nav-settings-btn" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-700 hover:bg-gray-900 text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                        <i class="fas fa-cog"></i>
                        Admin <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-52 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                        <div class="py-1" role="menu">
                            <a href="{{ route('staff.index') }}" id="nav-staff" class="flex items-center gap-2 text-base text-gray-500 dark:text-indigo-200 hover:text-indigo-600 dark:hover:text-white hover:bg-indigo-50 dark:hover:bg-[#4338CA]/50 px-3 py-2 rounded-lg transition-colors">
                                <i class="fas fa-users-cog w-4 text-center"></i> Staff Management
                            </a>
                            <a href="{{ route('settings.general') }}" id="nav-settings" class="flex items-center gap-2 text-base text-gray-500 dark:text-indigo-200 hover:text-indigo-600 dark:hover:text-white hover:bg-indigo-50 dark:hover:bg-[#4338CA]/50 px-3 py-2 rounded-lg transition-colors">
                                <i class="fas fa-sliders-h w-4 text-center"></i> Settings
                            </a>
                            <a href="{{ route('staff.create') }}" id="nav-employees" class="flex items-center gap-2 text-base text-gray-500 dark:text-indigo-200 hover:text-indigo-600 dark:hover:text-white hover:bg-indigo-50 dark:hover:bg-[#4338CA]/50 px-3 py-2 rounded-lg transition-colors">
                                <i class="fas fa-user-plus w-4 text-center"></i> Add Staff
                            </a>
                            <a href="{{ route('godams.index') }}" id="nav-admin-godams" class="flex items-center gap-2 text-base text-gray-500 dark:text-indigo-200 hover:text-indigo-600 dark:hover:text-white hover:bg-indigo-50 dark:hover:bg-[#4338CA]/50 px-3 py-2 rounded-lg transition-colors">
                                <i class="fas fa-warehouse w-4 text-center"></i> Godams
                            </a>
                            <hr class="my-1 border-slate-200 dark:border-slate-600">
                            <a href="{{ route('settings.backup.download') }}" id="nav-backup" class="flex items-center gap-2 text-base text-orange-500 hover:text-orange-700 hover:bg-orange-50 dark:hover:bg-orange-500/10 px-3 py-2 rounded-lg transition-colors font-semibold">
                                <i class="fas fa-database w-4 text-center"></i> Download Backup
                            </a>
                        </div>
                    </div>
                </div>
                @endrole

                <!-- Dashboard — visible to all -->
                <a href="{{ route('dashboard') }}" id="nav-dashboard" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>

                <!-- Theme Toggle -->
                <button @click="toggleTheme()" id="btn-theme-toggle" class="w-10 h-10 rounded-full flex items-center justify-center transition bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-yellow-400 hover:bg-slate-200 dark:hover:bg-slate-700">
                    <i x-show="isDark" class="fas fa-sun text-lg"></i>
                    <i x-show="!isDark" class="fas fa-moon text-lg"></i>
                </button>

                <!-- User Profile -->
                <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
                    <div x-data="{ open: false }" @click.away="open = false" class="relative">
                        <button @click="open = !open" id="btn-user-menu"
                                class="flex items-center gap-2 text-sm font-semibold text-gray-700 dark:text-slate-200 hover:text-indigo-600 transition">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold text-xs">
                                {{ strtoupper(substr((Auth::user() ?? Auth::guard('employee')->user())?->name ?? 'U', 0, 1)) }}
                            </div>
                            <span class="hidden sm:block leading-tight text-left">
                                <span class="block text-xs font-bold text-gray-900 dark:text-white">{{ (Auth::user() ?? Auth::guard('employee')->user())?->name ?? 'User' }}</span>
                                @if(Auth::check())
                                    <span class="block text-xs text-indigo-500 dark:text-indigo-400 capitalize">{{ Auth::user()->roles->first()?->name ?? Auth::user()->role ?? 'user' }}</span>
                                @endif
                            </span>
                            <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                        </button>

                        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-44 rounded-xl shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-50 py-1">
                            @if(Auth::check())
                            <a href="{{ route('profile.edit') }}" id="nav-profile"
                               class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-700 dark:text-slate-300 hover:bg-indigo-50 dark:hover:bg-slate-700 transition">
                                <i class="fas fa-user-circle w-4 text-center text-indigo-400"></i> My Profile
                            </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" id="btn-logout"
                                        class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                    <i class="fas fa-sign-out-alt w-4 text-center"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </nav>

    <!-- CONTENT WRAPPER -->
    <div class="flex-grow container mx-auto px-6 max-w-[1400px] py-4 sm:py-6">
        @yield('content')
    </div>

    <!-- Live Clock Script -->
    <script>
        function updateClock() {
            const now = new Date();
            const dateParams = {
                weekday: 'short',
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            };
            const timeParams = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };

            const dateEl = document.getElementById('current-date');
            const timeEl = document.getElementById('current-time');
            if (dateEl) dateEl.innerText = now.toLocaleDateString('en-US', dateParams);
            if (timeEl) timeEl.innerText = now.toLocaleTimeString('en-US', timeParams);
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>

    @yield('scripts')
    <script>
        function adminState() {
            return {
                isDark: document.documentElement.classList.contains('dark'),
                activeTab: 'general',
                mobileMenuOpen: false,
                scrolled: false,

                toggleTheme() {
                    this.isDark = !this.isDark;
                    if (this.isDark) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    }
                },
                init() {
                    window.addEventListener('scroll', () => {
                        this.scrolled = window.scrollY > 20
                    });
                }
            }
        }
    </script>

    {{-- =====================================================================
         FLOATING RADIAL SHORTCUT MENU — appears on every admin page
         Injects just before </body> via layouts/admin.blade.php
    ====================================================================== --}}
    @php
        // Resolve all routes safely — fall back to '#' if a route doesn't exist
        try { $rm_pos       = route('sales.pos');               } catch(\Exception $e) { $rm_pos       = '/sales'; }
        try { $rm_purchase  = route('purchase-orders.index');   } catch(\Exception $e) { $rm_purchase  = '/purchase-orders'; }
        try { $rm_transfer  = route('stock-transfers.create');  } catch(\Exception $e) { $rm_transfer  = '#'; }
        try { $rm_customer  = route('customers.create');        } catch(\Exception $e) { $rm_customer  = '#'; }
        try { $rm_supplier  = route('suppliers.create');        } catch(\Exception $e) { $rm_supplier  = '#'; }
        try { $rm_reports   = route('reports.sales');           } catch(\Exception $e) { $rm_reports   = '#'; }
        try { $rm_return    = route('supplier-returns.create'); } catch(\Exception $e) { $rm_return    = route('purchases.create'); }
        try { $rm_import    = route('items.import-preview');    } catch(\Exception $e) { $rm_import    = '#'; }
        try { $rm_godams    = route('godams.index');            } catch(\Exception $e) { $rm_godams    = '#'; }
        try { $rm_lowstock  = route('stock.low-stock');         } catch(\Exception $e) { $rm_lowstock  = '/items'; }

        // Alert badge count: low stock + expiring batches + supplier dues
        $rm_badge = 0;
        try {
            // Low stock (try min_stock_level, fall back to min_stock)
            try {
                $rm_badge += (int)\Illuminate\Support\Facades\DB::table('items')
                    ->whereNotNull('min_stock_level')->where('min_stock_level','>',0)
                    ->where('on_hand','>',0)->whereColumn('on_hand','<','min_stock_level')->count();
            } catch(\Throwable $e) {
                $rm_badge += (int)\Illuminate\Support\Facades\DB::table('items')
                    ->where('min_stock','>',0)->where('on_hand','>',0)
                    ->whereColumn('on_hand','<','min_stock')->count();
            }
            // Expiring within 7 days
            $rm_badge += (int)\Illuminate\Support\Facades\DB::table('batches')
                ->whereNotNull('expires_at')
                ->where('expires_at','>=',today()->toDateString())
                ->where('expires_at','<=',today()->addDays(7)->toDateString())
                ->where('quantity_available','>',0)->count();
            // Supplier dues
            $rm_badge += (int)\Illuminate\Support\Facades\DB::table('suppliers')
                ->where('current_balance','>',0)->count();
        } catch(\Throwable $e) { $rm_badge = 0; }
    @endphp

    <style>
        /* ── Radial Menu Root ── */
        #rm-root {
            position: fixed;
            right: 24px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 9999;
            width: 52px;
            height: 52px;
        }

        /* ── Backdrop overlay (click-outside to close) ── */
        #rm-backdrop {
            position: fixed;
            inset: 0;
            z-index: 9998;
            display: none;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(2px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        #rm-backdrop.rm-open {
            display: block;
            opacity: 1;
        }

        /* ── Dashed ring that appears when open ── */
        #rm-ring {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            border: 1.5px dashed rgba(108, 99, 255, 0.35);
            transform: translate(-50%, -50%) scale(0);
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1),
                        opacity 0.3s ease;
            opacity: 0;
            pointer-events: none;
        }
        #rm-ring.rm-open {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }

        /* ── Main trigger button ── */
        #rm-trigger {
            position: absolute;
            top: 0; left: 0;
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #6c63ff;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(108, 99, 255, 0.5);
            transition: box-shadow 0.25s ease, transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
            outline: none;
        }
        #rm-trigger:hover, #rm-trigger:focus-visible {
            box-shadow: 0 6px 28px rgba(108, 99, 255, 0.7);
            transform: scale(1.08);
        }
        #rm-trigger:focus-visible {
            outline: 3px solid #6c63ff;
            outline-offset: 2px;
        }
        #rm-trigger svg.rm-icon-plus {
            transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        #rm-trigger.rm-open svg.rm-icon-plus {
            transform: rotate(45deg);
        }

        /* ── Notification badge ── */
        #rm-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            border-radius: 9px;
            background: #ef4444;
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            font-family: 'Roboto', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
            pointer-events: none;
            line-height: 1;
            z-index: 10;
        }

        /* ── Individual radial item container ── */
        .rm-item {
            position: absolute;
            top: 26px;
            left: 26px;
            width: 44px;
            height: 44px;
            transform: translate(-50%, -50%) scale(0);
            opacity: 0;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1),
                        opacity 0.3s ease;
            pointer-events: none;
        }
        .rm-item.rm-open {
            opacity: 1;
            pointer-events: auto;
        }

        /* ── The anchor/circle inside ── */
        .rm-item a {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            text-decoration: none;
            box-shadow: 0 3px 12px rgba(0,0,0,0.35);
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease, outline 0.1s ease;
            position: relative;
            outline: none;
        }
        .rm-item a:hover, .rm-item a:focus-visible, .rm-item.rm-active a {
            transform: scale(1.15);
            box-shadow: 0 5px 18px rgba(108, 99, 255, 0.4);
            outline: 2px solid #6c63ff;
            outline-offset: 2px;
        }

        /* ── Tooltip ── */
        .rm-item a::after {
            content: attr(data-label);
            position: absolute;
            right: calc(100% + 8px);
            top: 50%;
            transform: translateY(-50%);
            background: rgba(15, 15, 15, 0.92);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            font-family: 'Roboto', sans-serif;
            white-space: nowrap;
            padding: 4px 8px;
            border-radius: 6px;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .rm-item a:hover::after, .rm-item a:focus-visible::after, .rm-item.rm-active a::after {
            opacity: 1;
        }

        /* ── Icon label below circle ── */
        .rm-item-label {
            position: absolute;
            bottom: -16px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 8px;
            font-weight: 700;
            font-family: 'Roboto', sans-serif;
            color: #fff;
            white-space: nowrap;
            text-shadow: 0 1px 3px rgba(0,0,0,0.6);
            pointer-events: none;
        }

        /* ── Pulse ring on the trigger ── */
        @keyframes rm-pulse {
            0%   { box-shadow: 0 0 0 0 rgba(108,99,255,0.5); }
            70%  { box-shadow: 0 0 0 12px rgba(108,99,255,0); }
            100% { box-shadow: 0 0 0 0 rgba(108,99,255,0); }
        }
        #rm-trigger { animation: rm-pulse 2.5s ease-out infinite; }
        #rm-trigger.rm-open { animation: none; }
    </style>

    {{-- ── Backdrop ── --}}
    <div id="rm-backdrop"></div>

    {{-- ── Root container ── --}}
    <div id="rm-root" role="navigation" aria-label="Quick shortcuts">

        {{-- Dashed ring --}}
        <div id="rm-ring"></div>

        {{-- 10 radial items — positions calculated in JS --}}

        {{-- 1. POS / New Sale --}}
        <div class="rm-item" data-idx="0">
            <a href="{{ $rm_pos }}" data-label="New Sale">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                <span class="rm-item-label">Sale</span>
            </a>
        </div>

        {{-- 2. Purchase Orders (PO) --}}
        <div class="rm-item" data-idx="1">
            <a href="{{ $rm_purchase }}" data-label="Purchase Orders (PO)">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                <span class="rm-item-label">PO Orders</span>
            </a>
        </div>

        {{-- 3. Stock Transfer --}}
        <div class="rm-item" data-idx="2">
            <a href="{{ $rm_transfer }}" data-label="Stock Transfer">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 014-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 01-4 4H3"/></svg>
                <span class="rm-item-label">Transfer</span>
            </a>
        </div>

        {{-- 4. Add Customer --}}
        <div class="rm-item" data-idx="3">
            <a href="{{ $rm_customer }}" data-label="Add Customer">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/><line x1="18" y1="8" x2="23" y2="8"/><line x1="20.5" y1="5.5" x2="20.5" y2="10.5"/></svg>
                <span class="rm-item-label">Customer</span>
            </a>
        </div>

        {{-- 5. Add Supplier --}}
        <div class="rm-item" data-idx="4">
            <a href="{{ $rm_supplier }}" data-label="Add Supplier">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                <span class="rm-item-label">Supplier</span>
            </a>
        </div>

        {{-- 6. Reports --}}
        <div class="rm-item" data-idx="5">
            <a href="{{ $rm_reports }}" data-label="View Reports">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <span class="rm-item-label">Reports</span>
            </a>
        </div>

        {{-- 7. Process Return --}}
        <div class="rm-item" data-idx="6">
            <a href="{{ $rm_return }}" data-label="Process Return">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/></svg>
                <span class="rm-item-label">Return</span>
            </a>
        </div>

        {{-- 8. Import Items --}}
        <div class="rm-item" data-idx="7">
            <a href="{{ $rm_import }}" data-label="Import Items">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="8 17 12 21 16 17"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.88 18.09A5 5 0 0018 9h-1.26A8 8 0 103 16.29"/></svg>
                <span class="rm-item-label">Import</span>
            </a>
        </div>

        {{-- 9. Godams --}}
        <div class="rm-item" data-idx="8">
            <a href="{{ $rm_godams }}" data-label="Godams">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                <span class="rm-item-label">Godams</span>
            </a>
        </div>

        {{-- 10. Stock Alerts --}}
        <div class="rm-item" data-idx="9">
            <a href="{{ $rm_lowstock }}" data-label="Stock Alerts">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span class="rm-item-label">Alerts</span>
            </a>
        </div>

        {{-- Main trigger button --}}
        <button id="rm-trigger" aria-expanded="false" aria-label="Quick shortcuts menu" title="Quick shortcuts">
            <svg class="rm-icon-plus" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.8" stroke-linecap="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
        </button>

        {{-- Notification badge --}}
        @if($rm_badge > 0)
        <div id="rm-badge">{{ $rm_badge > 99 ? '99+' : $rm_badge }}</div>
        @endif

    </div>{{-- /#rm-root --}}

    <script>
    (function () {
        'use strict';

        // ── Config ────────────────────────────────────────────────────────────
        var BG_COLORS = [
            '#1e3a5f', '#1a3a2a', '#3a1a2a', '#2a1a3a', '#3a2a1a',
            '#1a2a3a', '#1f3a33', '#3a1a1a', '#1a3a3a', '#2a2a1a'
        ];

        var ITEM_COUNT = 10;
        var RADIUS     = 110;
        var ARC_START  = -150; // degrees
        var ARC_END    = 150;  // degrees

        // ── Elements ──────────────────────────────────────────────────────────
        var root      = document.getElementById('rm-root');
        var trigger   = document.getElementById('rm-trigger');
        var ring      = document.getElementById('rm-ring');
        var backdrop  = document.getElementById('rm-backdrop');
        var items     = Array.prototype.slice.call(document.querySelectorAll('.rm-item'));

        if (!trigger || items.length === 0) return;

        // Apply colors and setup roles for accessibility
        items.forEach(function (item, i) {
            var a = item.querySelector('a');
            if (a) {
                a.style.backgroundColor = BG_COLORS[i] || '#333';
                a.setAttribute('role', 'menuitem');
                a.setAttribute('tabindex', '-1');
            }
        });

        // ── Animation & State Variables ───────────────────────────────────────
        var isOpen = false;
        var activeIndex = -1; // Index of the highlighted item (-1 means none)
        var staggerTimeouts = [];
        
        // Circular Scroll Wheel State
        var angleOffset = 0;
        var targetAngleOffset = 0;
        var isScrollLoopRunning = false;

        // ── Calculate Positions ───────────────────────────────────────────────
        function getPosition(i, offsetDegrees) {
            var step = ITEM_COUNT > 1 ? (ARC_END - ARC_START) / (ITEM_COUNT - 1) : 0;
            var angleDeg = ARC_START + step * i + (offsetDegrees || 0);
            var rad = angleDeg * Math.PI / 180;
            return {
                tx: Math.cos(rad) * RADIUS,
                ty: Math.sin(rad) * RADIUS
            };
        }

        // Apply visual coordinates to DOM items
        function updateItemPositions(instant) {
            items.forEach(function (item, i) {
                if (!isOpen && !instant) return;
                var p = getPosition(i, angleOffset);
                if (instant) {
                    item.style.transition = 'none';
                } else {
                    item.style.transition = 'transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease';
                }
                item.style.transform = 'translate(calc(-50% + ' + p.tx + 'px), calc(-50% + ' + p.ty + 'px)) scale(1)';
            });
        }

        // ── Smooth Inertia Wheel Loop ─────────────────────────────────────────
        function updateScrollPhysics() {
            if (!isScrollLoopRunning) return;
            
            // Friction and Ease-out physics
            var diff = targetAngleOffset - angleOffset;
            if (Math.abs(diff) > 0.05) {
                angleOffset += diff * 0.15; // Smooth interpolation
                updateItemPositions(true);
                requestAnimationFrame(updateScrollPhysics);
            } else {
                angleOffset = targetAngleOffset;
                updateItemPositions(true);
                isScrollLoopRunning = false;
            }
        }

        function clearAllTimeouts() {
            staggerTimeouts.forEach(clearTimeout);
            staggerTimeouts = [];
        }

        // ── Focus & Highlight Management ──────────────────────────────────────
        function highlightItem(index) {
            items.forEach(function (item) {
                item.classList.remove('rm-active');
                var a = item.querySelector('a');
                if (a) a.setAttribute('tabindex', '-1');
            });

            activeIndex = index;

            if (activeIndex >= 0 && activeIndex < items.length) {
                var activeItem = items[activeIndex];
                activeItem.classList.add('rm-active');
                var a = activeItem.querySelector('a');
                if (a) {
                    a.setAttribute('tabindex', '0');
                    a.focus();
                }
            } else {
                trigger.focus();
            }
        }

        // ── Open Menu ─────────────────────────────────────────────────────────
        function openMenu() {
            if (isOpen) return;
            isOpen = true;
            clearAllTimeouts();

            trigger.classList.add('rm-open');
            trigger.setAttribute('aria-expanded', 'true');
            ring.classList.add('rm-open');
            backdrop.classList.add('rm-open');

            // Stagger outwards
            items.forEach(function (item, i) {
                var p = getPosition(i, angleOffset);
                var t = setTimeout(function () {
                    item.style.transition = 'transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease';
                    item.style.transform = 'translate(calc(-50% + ' + p.tx + 'px), calc(-50% + ' + p.ty + 'px)) scale(1)';
                    item.classList.add('rm-open');
                }, i * 30);
                staggerTimeouts.push(t);
            });

            // Set focus initially on first element
            setTimeout(function () {
                if (isOpen) highlightItem(0);
            }, items.length * 30);
        }

        // ── Close Menu ────────────────────────────────────────────────────────
        function closeMenu() {
            if (!isOpen) return;
            isOpen = false;
            clearAllTimeouts();
            highlightItem(-1);

            trigger.classList.remove('rm-open');
            trigger.setAttribute('aria-expanded', 'false');
            ring.classList.remove('rm-open');
            backdrop.classList.remove('rm-open');

            // Reset wheel scroll offsets
            targetAngleOffset = 0;
            angleOffset = 0;
            isScrollLoopRunning = false;

            // Stagger reverse collapse
            var reversed = items.slice().reverse();
            reversed.forEach(function (item, i) {
                var t = setTimeout(function () {
                    item.style.transition = 'transform 0.3s ease, opacity 0.2s ease';
                    item.style.transform = 'translate(-50%, -50%) scale(0)';
                    item.classList.remove('rm-open');
                }, i * 20);
                staggerTimeouts.push(t);
            });
        }

        // ── Toggle ────────────────────────────────────────────────────────────
        function toggleMenu(e) {
            if (e) {
                e.stopPropagation();
                e.preventDefault();
            }
            if (isOpen) { closeMenu(); } else { openMenu(); }
        }

        // ── Event Handlers ────────────────────────────────────────────────────
        trigger.addEventListener('click', toggleMenu);
        backdrop.addEventListener('click', closeMenu);

        // Circular Mouse Wheel listener (rotates menu items recursively)
        root.addEventListener('wheel', function (e) {
            if (!isOpen) return;
            e.preventDefault();
            
            // Adjust step scroll
            var direction = e.deltaY > 0 ? 1 : -1;
            targetAngleOffset += direction * 24; // Degrees to rotate

            if (!isScrollLoopRunning) {
                isScrollLoopRunning = true;
                requestAnimationFrame(updateScrollPhysics);
            }
        });

        // Smart Keyboard Accessibility Engine
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isOpen) {
                closeMenu();
                trigger.focus();
                return;
            }

            // Keyboard trigger shortcuts
            if (!isOpen && document.activeElement === trigger && (e.key === ' ' || e.key === 'Enter')) {
                e.preventDefault();
                openMenu();
                return;
            }

            if (!isOpen) return;

            // Arrow cycles
            if (e.key === 'ArrowDown' || e.key === 'ArrowRight') {
                e.preventDefault();
                var next = (activeIndex + 1) % items.length;
                highlightItem(next);
            } else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') {
                e.preventDefault();
                var prev = (activeIndex - 1 + items.length) % items.length;
                highlightItem(prev);
            } else if (e.key === 'Enter' && activeIndex !== -1) {
                // Trigger action of the selected item
                var a = items[activeIndex].querySelector('a');
                if (a) a.click();
            }
        });

        // Clicking a menu item closes it after slight navigation delay
        items.forEach(function (item) {
            var a = item.querySelector('a');
            if (a) {
                a.addEventListener('click', function () {
                    setTimeout(closeMenu, 120);
                });
            }
        });

        // Initialize collapsed positions
        items.forEach(function (item) {
            item.style.transform = 'translate(-50%, -50%) scale(0)';
        });
    })();
    </script>
</body>

</html>