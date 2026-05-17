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
                @yield('navbar_actions')

                <!-- Sales Dropdown (assuming this is where the links should go) -->
                <div x-data="{ open: false }" @click.away="open = false" class="relative">
                    <button @click="open = !open" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                        <i class="fas fa-cash-register"></i>
                        Sales <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                        <div class="py-1" role="menu" aria-orientation="vertical" aria-labelledby="options-menu">
                            <a href="{{ route('sales.pos') }}" class="block text-base text-gray-500 dark:text-indigo-200 hover:text-indigo-600 dark:hover:text-white hover:bg-indigo-50 dark:hover:bg-[#4338CA]/50 px-3 py-2 rounded-lg transition-colors" role="menuitem">
                                Counter Sales (POS)
                            </a>
                            <a href="{{ route('cash-sales.create') }}" class="block text-base text-gray-500 dark:text-indigo-200 hover:text-indigo-600 dark:hover:text-white hover:bg-indigo-50 dark:hover:bg-[#4338CA]/50 px-3 py-2 rounded-lg transition-colors" role="menuitem">
                                Cash Sales
                            </a>
                            <a href="{{ route('debit-sales.create') }}" class="block text-base text-gray-500 dark:text-indigo-200 hover:text-indigo-600 dark:hover:text-white hover:bg-indigo-50 dark:hover:bg-[#4338CA]/50 px-3 py-2 rounded-lg transition-colors" role="menuitem">
                                Debit Sales
                            </a>
                        </div>
                    </div>
                </div>

                <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105">
                    <i class="fas fa-home"></i>
                    Dashboard
                </a>

                <!-- Theme Toggle -->
                <button @click="toggleTheme()" class="w-10 h-10 rounded-full flex items-center justify-center transition bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-yellow-400 hover:bg-slate-200 dark:hover:bg-slate-700">
                    <i x-show="isDark" class="fas fa-sun text-lg"></i>
                    <i x-show="!isDark" class="fas fa-moon text-lg"></i>
                </button>

                <!-- User Profile (Keeping simplified version) -->
                <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
                    <div class="text-right hidden sm:block leading-tight">
                        <div class="text-xs font-bold text-gray-900">{{ (Auth::user() ?? Auth::guard('employee')->user())->name ?? 'User' }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-red-600 transition" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
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

            document.getElementById('current-date').innerText = now.toLocaleDateString('en-US', dateParams);
            document.getElementById('current-time').innerText = now.toLocaleTimeString('en-US', timeParams);
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
</body>

</html>