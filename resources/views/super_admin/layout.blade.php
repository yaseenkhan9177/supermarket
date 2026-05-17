<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Super Dashboard') - OwnStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-50 text-gray-800">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white flex flex-col">
            <div class="p-6 border-b border-gray-800">
                <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-cyan-400">OwnStore Super</h1>
                <p class="text-xs text-gray-500 mt-1">Command Center</p>
            </div>
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="{{ route('super.dashboard') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('super.dashboard') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-tachometer-alt w-5 h-5 mr-3 text-center"></i>
                    Dashboard
                </a>

                <a href="{{ route('super.requests.index') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('super.requests.*') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-inbox w-5 h-5 mr-3 text-center"></i>
                    Store Requests
                </a>

                <a href="{{ route('super.tenants') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('super.tenants*') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-store w-5 h-5 mr-3 text-center"></i>
                    Active Stores
                </a>

                <a href="{{ route('super.plans') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('super.plans') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-credit-card w-5 h-5 mr-3 text-center"></i>
                    Subscriptions & Plans
                </a>

                <a href="{{ route('super.users') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('super.users') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-users-cog w-5 h-5 mr-3 text-center"></i>
                    Users (Admin)
                </a>

                <a href="{{ route('super.logs') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('super.logs') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-terminal w-5 h-5 mr-3 text-center"></i>
                    System Logs
                </a>

                <a href="{{ route('super.settings') }}" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-800 rounded-lg transition-colors {{ request()->routeIs('super.settings') ? 'bg-gray-800 text-white' : '' }}">
                    <i class="fas fa-cogs w-5 h-5 mr-3 text-center"></i>
                    Settings
                </a>
            </nav>
            <div class="p-4 border-t border-gray-800">
                <form method="POST" action="{{ route('super.logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center w-full px-4 py-2 text-red-400 hover:bg-gray-800 rounded-lg transition-colors text-sm">
                        <i class="fas fa-sign-out-alt w-4 h-4 mr-2"></i>
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-gray-50 p-8">
            <header class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">@yield('header')</h2>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">Logged in as <strong>{{ Auth::guard('super_admin')->user()->name }}</strong></span>
                    <div class="h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold">
                        {{ substr(Auth::guard('super_admin')->user()->name, 0, 1) }}
                    </div>
                </div>
            </header>

            @yield('content')
        </main>
    </div>

</body>

</html>