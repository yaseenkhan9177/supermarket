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
                <span class="text-xs text-gray-500 font-medium mt-0.5">{{ $pageTitle ?? 'Dashboard' }}</span>
            </div>
        </div>

        <div class="flex items-center gap-4">
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
                 REPORTS — visible to: owner, manager
                 ====================================================== --}}
            @hasanyrole('owner|manager')
            <a href="{{ route('reports.index') }}" id="nav-reports" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                <i class="fas fa-chart-pie"></i>
                Reports
            </a>
            @endhasanyrole

            {{-- ======================================================
                 GODAMS — visible to: owner, manager, warehouse
                 ====================================================== --}}
            @hasanyrole('owner|manager|warehouse')
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
