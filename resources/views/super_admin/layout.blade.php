<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Dashboard') — OwnStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }

        /* Animated sidebar gradient */
        .sidebar-gradient {
            background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
        }

        /* Glassmorphism card */
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Sidebar link active glow */
        .nav-active {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.3) 0%, rgba(99, 102, 241, 0.1) 100%);
            border-left: 3px solid #6366f1;
            color: #fff !important;
        }

        .nav-link {
            border-left: 3px solid transparent;
            transition: all 0.2s ease;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.07);
            border-left-color: rgba(99, 102, 241, 0.5);
            color: #fff !important;
        }

        /* Flash alerts slide-in */
        .flash-alert {
            animation: slideDown 0.4s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(99,102,241,0.4); border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(99,102,241,0.7); }

        /* Logo shimmer */
        .logo-text {
            background: linear-gradient(135deg, #818cf8 0%, #38bdf8 50%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>

<body class="bg-slate-50">

    <div class="flex h-screen overflow-hidden">

        <!-- ══════════════════════════════════════ -->
        <!-- SIDEBAR                                -->
        <!-- ══════════════════════════════════════ -->
        <aside class="w-64 sidebar-gradient text-white flex flex-col flex-shrink-0 shadow-2xl z-20">

            <!-- Logo -->
            <div class="px-6 py-5 border-b border-white/10">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg">
                        <i class="fas fa-crown text-white text-sm"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold logo-text leading-tight">OwnStore</h1>
                        <p class="text-[10px] text-slate-400 uppercase tracking-widest">Super Admin</p>
                    </div>
                </div>
            </div>

            <!-- Admin Info -->
            <div class="px-4 py-3 border-b border-white/5 bg-white/5">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr(Auth::guard('super_admin')->user()->name ?? 'S', 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-white truncate">{{ Auth::guard('super_admin')->user()->name ?? 'Super Admin' }}</p>
                        <p class="text-[10px] text-slate-400 truncate">{{ Auth::guard('super_admin')->user()->role ?? 'super_owner' }}</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <p class="text-[10px] text-slate-500 uppercase tracking-widest px-3 mb-2 font-semibold">Main</p>

                <a href="{{ route('super.dashboard') }}"
                   class="nav-link flex items-center px-3 py-2.5 text-slate-300 rounded-lg text-sm {{ request()->routeIs('super.dashboard') ? 'nav-active' : '' }}">
                    <i class="fas fa-tachometer-alt w-4 h-4 mr-3 text-center text-indigo-400 flex-shrink-0"></i>
                    Dashboard
                </a>

                <a href="{{ route('super.requests.index') }}"
                   class="nav-link flex items-center px-3 py-2.5 text-slate-300 rounded-lg text-sm {{ request()->routeIs('super.requests.*') ? 'nav-active' : '' }}">
                    <i class="fas fa-inbox w-4 h-4 mr-3 text-center text-amber-400 flex-shrink-0"></i>
                    Store Requests
                    @php $pendingCount = \App\Models\Tenant::where('status','pending')->count(); @endphp
                    @if($pendingCount > 0)
                        <span class="ml-auto bg-amber-500 text-white text-[10px] font-bold rounded-full w-5 h-5 flex items-center justify-center">
                            {{ $pendingCount > 9 ? '9+' : $pendingCount }}
                        </span>
                    @endif
                </a>

                <a href="{{ route('super.tenants') }}"
                   class="nav-link flex items-center px-3 py-2.5 text-slate-300 rounded-lg text-sm {{ request()->routeIs('super.tenants*') ? 'nav-active' : '' }}">
                    <i class="fas fa-store w-4 h-4 mr-3 text-center text-emerald-400 flex-shrink-0"></i>
                    Active Stores
                </a>

                <p class="text-[10px] text-slate-500 uppercase tracking-widest px-3 mb-2 mt-4 font-semibold">Management</p>

                <a href="{{ route('super.plans') }}"
                   class="nav-link flex items-center px-3 py-2.5 text-slate-300 rounded-lg text-sm {{ request()->routeIs('super.plans') ? 'nav-active' : '' }}">
                    <i class="fas fa-credit-card w-4 h-4 mr-3 text-center text-sky-400 flex-shrink-0"></i>
                    Plans &amp; Pricing
                </a>

                <a href="{{ route('super.users') }}"
                   class="nav-link flex items-center px-3 py-2.5 text-slate-300 rounded-lg text-sm {{ request()->routeIs('super.users*') ? 'nav-active' : '' }}">
                    <i class="fas fa-user-shield w-4 h-4 mr-3 text-center text-purple-400 flex-shrink-0"></i>
                    Admin Users
                </a>

                <p class="text-[10px] text-slate-500 uppercase tracking-widest px-3 mb-2 mt-4 font-semibold">System</p>

                <a href="{{ route('super.logs') }}"
                   class="nav-link flex items-center px-3 py-2.5 text-slate-300 rounded-lg text-sm {{ request()->routeIs('super.logs') ? 'nav-active' : '' }}">
                    <i class="fas fa-terminal w-4 h-4 mr-3 text-center text-rose-400 flex-shrink-0"></i>
                    System Logs
                </a>

                <a href="{{ route('super.settings') }}"
                   class="nav-link flex items-center px-3 py-2.5 text-slate-300 rounded-lg text-sm {{ request()->routeIs('super.settings') ? 'nav-active' : '' }}">
                    <i class="fas fa-cogs w-4 h-4 mr-3 text-center text-slate-400 flex-shrink-0"></i>
                    Settings
                </a>
            </nav>

            <!-- Logout -->
            <div class="p-3 border-t border-white/10">
                <form method="POST" action="{{ route('super.logout') }}">
                    @csrf
                    <button type="submit"
                        class="flex items-center w-full px-3 py-2.5 text-rose-400 hover:bg-rose-500/10 rounded-lg transition-colors text-sm group">
                        <i class="fas fa-sign-out-alt w-4 h-4 mr-3 text-center group-hover:translate-x-0.5 transition-transform flex-shrink-0"></i>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- ══════════════════════════════════════ -->
        <!-- MAIN CONTENT                          -->
        <!-- ══════════════════════════════════════ -->
        <main class="flex-1 overflow-y-auto bg-slate-50">

            <!-- Topbar -->
            <header class="sticky top-0 z-10 bg-white border-b border-slate-200 shadow-sm">
                <div class="flex justify-between items-center px-8 py-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800">@yield('header', 'Dashboard')</h2>
                        <p class="text-xs text-slate-400 mt-0.5">@yield('subheader', 'OwnStore Command Center')</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-xs text-slate-500 hidden sm:block">{{ now()->format('D, d M Y') }}</span>
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-bold shadow-md">
                            {{ strtoupper(substr(Auth::guard('super_admin')->user()->name ?? 'S', 0, 1)) }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <div class="px-8 pt-4 space-y-3">
                @if(session('success'))
                    <div class="flash-alert flex items-center p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm">
                        <i class="fas fa-check-circle mr-3 text-emerald-500 flex-shrink-0"></i>
                        <span>{{ session('success') }}</span>
                        <button onclick="this.parentElement.remove()" class="ml-auto text-emerald-400 hover:text-emerald-600"><i class="fas fa-times"></i></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="flash-alert flex items-center p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
                        <i class="fas fa-exclamation-circle mr-3 text-rose-500 flex-shrink-0"></i>
                        <span>{{ session('error') }}</span>
                        <button onclick="this.parentElement.remove()" class="ml-auto text-rose-400 hover:text-rose-600"><i class="fas fa-times"></i></button>
                    </div>
                @endif
                @if(session('info'))
                    <div class="flash-alert flex items-center p-4 bg-sky-50 border border-sky-200 text-sky-800 rounded-xl text-sm">
                        <i class="fas fa-info-circle mr-3 text-sky-500 flex-shrink-0"></i>
                        <span>{{ session('info') }}</span>
                        <button onclick="this.parentElement.remove()" class="ml-auto text-sky-400 hover:text-sky-600"><i class="fas fa-times"></i></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="flash-alert p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
                        <div class="flex items-center mb-2">
                            <i class="fas fa-exclamation-triangle mr-3 text-rose-500"></i>
                            <strong>Please fix the following errors:</strong>
                        </div>
                        <ul class="list-disc list-inside space-y-1 ml-6">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <div class="px-8 py-6">
                @yield('content')
            </div>
        </main>
    </div>

</body>

</html>