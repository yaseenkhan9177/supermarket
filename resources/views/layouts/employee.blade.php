<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>Employee Dashboard | OwnStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    },
                    colors: {
                        primary: '#4F46E5', // Indigo 600
                        dark: '#111827', // Gray 900
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-50 font-sans" x-data="{ sidebarOpen: false, sidebarCollapsed: window.innerWidth >= 768 && window.innerWidth < 1024 }">

    <div class="flex h-screen overflow-hidden">

        <!-- SIDEBAR -->
        <aside class="bg-dark text-white flex-shrink-0 transition-all duration-300 z-50 fixed md:relative h-full flex flex-col"
            :class="{
                '-translate-x-full md:translate-x-0': !sidebarOpen, 
                'translate-x-0': sidebarOpen,
                'w-64': !sidebarCollapsed,
                'w-20': sidebarCollapsed
            }">

            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-16 px-4 bg-gray-900">
                <span class="font-bold text-xl tracking-wide transition-opacity duration-200" :class="sidebarCollapsed ? 'opacity-0 hidden' : 'opacity-100'">OwnStore</span>
                <span class="font-bold text-xl tracking-wide text-center w-full" :class="!sidebarCollapsed ? 'hidden' : 'block'">OS</span>
                <button @click="sidebarOpen = false" class="md:hidden text-gray-400 hover:text-white"><i class="fas fa-times"></i></button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 space-y-1 px-2">

                <a href="{{ route('employee.dashboard') }}" class="group flex items-center px-3 py-3 rounded-lg text-gray-300 hover:bg-gray-800 hover:text-white transition-colors"
                    :class="request()->routeIs('employee.dashboard') ? 'bg-primary text-white' : ''">
                    <i class="fas fa-home w-6 text-center text-lg group-hover:text-white"></i>
                    <span class="ml-3 font-medium transition-all" :class="sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100'">Dashboard</span>
                </a>

                <!-- Sales -->
                <a href="{{ route('sales.pos') }}" class="group flex items-center px-3 py-3 rounded-lg text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                    <i class="fas fa-cash-register w-6 text-center text-lg group-hover:text-white"></i>
                    <span class="ml-3 font-medium transition-all" :class="sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100'">POS</span>
                </a>

                @if(auth('employee')->user()->hasPermission('customers.view'))
                <a href="{{ route('customers.index') }}" class="group flex items-center px-3 py-3 rounded-lg text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                    <i class="fas fa-users w-6 text-center text-lg group-hover:text-white"></i>
                    <span class="ml-3 font-medium transition-all" :class="sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100'">Customers</span>
                </a>
                @endif

                @if(auth('employee')->user()->hasPermission('reports.sales'))
                <a href="#" class="group flex items-center px-3 py-3 rounded-lg text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                    <i class="fas fa-chart-bar w-6 text-center text-lg group-hover:text-white"></i>
                    <span class="ml-3 font-medium transition-all" :class="sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100'">Reports</span>
                </a>
                @endif

                <a href="#" class="group flex items-center px-3 py-3 rounded-lg text-gray-300 hover:bg-gray-800 hover:text-white transition-colors">
                    <i class="fas fa-user w-6 text-center text-lg group-hover:text-white"></i>
                    <span class="ml-3 font-medium transition-all" :class="sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100'">Profile</span>
                </a>

            </nav>

            <!-- Logout -->
            <div class="p-4 border-t border-gray-800">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full text-gray-400 hover:text-white transition-colors">
                        <i class="fas fa-sign-out-alt w-6 text-center text-lg"></i>
                        <span class="ml-3 font-medium" :class="sidebarCollapsed ? 'opacity-0 w-0 hidden' : 'opacity-100'">Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN LAYOUT -->
        <div class="flex-1 flex flex-col h-screen overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 z-40">
                <div class="flex items-center">
                    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-gray-500 hover:text-gray-700 mr-4">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <button @click="sidebarCollapsed = !sidebarCollapsed" class="hidden md:block text-gray-400 hover:text-gray-600 transition-transform duration-200" :class="{'rotate-180': sidebarCollapsed}">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <img src="{{ asset('images/logo.png') }}" class="w-8 h-8 rounded-full mr-2" alt="OwnStore">
                    <h1 class="ml-2 text-xl font-bold text-gray-800 hidden sm:block">Employee Portal</h1>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="text-right hidden sm:block">
                        <div class="text-sm font-bold text-gray-900">{{ auth('employee')->user()->name }}</div>
                        <div class="text-xs text-green-600 font-semibold">{{ auth('employee')->user()->role }}</div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                @yield('content')
            </main>
        </div>

        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen" class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden" @click="sidebarOpen = false" x-transition.opacity></div>
    </div>

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>