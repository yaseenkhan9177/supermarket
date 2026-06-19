<!DOCTYPE html>
<html lang="en" class="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'POS System')</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <script>
        tailwind.config = {
            darkMode: 'class', // This enables manual toggling
            theme: {
                extend: {
                    colors: {
                        slate: {
                            850: '#151f32',
                            950: '#020617'
                        } // Custom dark shades
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-slate-100 dark:bg-slate-900 transition-colors duration-300" x-data="themeSwitcher()">

    <nav class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 px-6 py-3 flex justify-between items-center sticky top-0 z-50">

        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-indigo-500/30">
                POS
            </div>
            <span class="text-xl font-bold text-slate-800 dark:text-white tracking-tight">OwnStore</span>
        </div>

        <div class="flex items-center gap-4">

            <button @click="toggleTheme()"
                class="w-10 h-10 rounded-full flex items-center justify-center transition bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-yellow-400 hover:bg-slate-200 dark:hover:bg-slate-700">
                <i x-show="isDark" class="fas fa-sun text-lg"></i>
                <i x-show="!isDark" class="fas fa-moon text-lg"></i>
            </button>

            <div class="flex items-center gap-3 pl-4 border-l border-slate-200 dark:border-slate-700">
                <div class="text-right hidden md:block">
                    <p class="text-sm font-bold text-slate-800 dark:text-white">{{ Auth::user()->name ?? 'Guest' }}</p>
                    <p class="text-xs text-slate-500">{{ Auth::user()->role ?? 'Admin' }}</p>
                </div>
                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">
                    {{ substr(Auth::user()->name ?? 'G', 0, 1) }}
                </div>
            </div>
        </div>
    </nav>

    <main class="p-6">
        @yield('content')
    </main>

    <script>
        function themeSwitcher() {
            return {
                isDark: document.documentElement.classList.contains('dark'),

                toggleTheme() {
                    this.isDark = !this.isDark;
                    if (this.isDark) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    }
                }
            }
        }
    </script>
</body>

</html>