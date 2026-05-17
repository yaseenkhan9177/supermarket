<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>User Access Management | OwnStore</title>

    <!-- Scripts & Styles from Admin Layout -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#312E81',
                        secondary: '#4338CA',
                    },
                    fontFamily: {
                        sans: ['Roboto', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .toggle-checkbox:checked {
            right: 0;
            border-color: #4f46e5;
        }

        .toggle-checkbox:checked+.toggle-label {
            background-color: #4f46e5;
        }

        /* Glass Panel & Gradients */
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

<body class="bg-gray-50 dark:bg-gray-950 font-sans leading-normal tracking-normal flex flex-col min-h-screen transition-colors duration-300"
    x-data="{ darkMode: localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches) }"
    x-init="if(darkMode) document.documentElement.classList.add('dark');"
    :class="{ 'dark': darkMode }">

    <!-- Centered Header with Logo -->
    <div class="w-full flex justify-center py-6 relative">
        <a href="{{ route('dashboard') }}" class="absolute left-6 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition transform hover:scale-110" title="Back to Dashboard">
            <i class="fas fa-home text-2xl"></i>
        </a>
        <div class="flex items-center gap-3">
            <div class="bg-indigo-600/10 p-2 rounded-full shadow-lg shadow-indigo-500/30">
                <img src="{{ asset('images/logo.png') }}" class="w-12 h-12 rounded-full" alt="OwnStore">
            </div>
            <span class="text-3xl font-black tracking-tighter text-gray-900 dark:text-white">
                OwnStore <span class="text-xs uppercase tracking-widest text-indigo-500 ml-1">PRO</span>
            </span>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container mx-auto px-4 max-w-7xl flex-grow flex gap-6 pb-6" x-data="{ currentTab: 'permissions' }">

        <!-- Sidebar: Staff Roster -->
        <div class="w-1/4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col h-[calc(100vh-140px)]">
            <div class="p-4 border-b dark:border-gray-700">
                <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Staff Roster</h2>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
                    <input type="text" placeholder="Find employee..." class="w-full pl-9 pr-3 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:outline-none focus:border-indigo-500">
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-2 space-y-2">
                @foreach($users as $user)
                <a href="{{ route('settings.users', ['type' => $user->type, 'id' => $user->id]) }}" class="block">
                    <div class="p-3 {{ isset($selectedUser) && $selectedUser->id === $user->id && $selectedUser->type === $user->type ? 'bg-indigo-50 dark:bg-indigo-900/20 border-indigo-200 dark:border-indigo-700' : 'hover:bg-gray-50 dark:hover:bg-gray-700 border-transparent' }} border rounded-lg flex items-center cursor-pointer transition">
                        <div class="w-10 h-10 rounded-full {{ isset($selectedUser) && $selectedUser->id === $user->id && $selectedUser->type === $user->type ? 'bg-indigo-600 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-600 dark:text-gray-200' }} flex items-center justify-center font-bold text-sm">
                            {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-bold text-gray-900 dark:text-white">{{ $user->name }}</p>
                            <span class="text-xs {{ isset($selectedUser) && $selectedUser->id === $user->id && $selectedUser->type === $user->type ? 'bg-indigo-200 text-indigo-800 dark:bg-indigo-700 dark:text-indigo-100' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' }} px-1.5 py-0.5 rounded">{{ ucfirst($user->role ?? $user->type) }}</span>
                        </div>
                    </div>
                </a>
                @endforeach

                <a href="{{ route('settings.employees') }}" class="block w-full mt-4 py-2 border-2 border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 rounded-lg text-sm text-center hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
                    + Add New User
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-3/4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 flex flex-col h-[calc(100vh-140px)]">
            @if($selectedUser)
            <form action="{{ route('settings.users.update', $selectedUser->id) }}" method="POST" class="h-full flex flex-col">
                @csrf
                <input type="hidden" name="model_type" value="{{ $selectedUser->type }}">

                <div class="p-6 border-b dark:border-gray-700 pb-0">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Access Management</h1>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Manage login credentials and granular system permissions for <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $selectedUser->name }}</span>.</p>
                        </div>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow transition text-sm font-medium">
                            <i class="fas fa-save mr-2"></i> Save Changes
                        </button>
                    </div>

                    <div class="flex space-x-6">
                        <button type="button" @click="currentTab = 'permissions'" :class="currentTab === 'permissions' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'" class="pb-3 border-b-2 font-medium text-sm transition">
                            Access Rights Matrix
                        </button>
                        <button type="button" @click="currentTab = 'profile'" :class="currentTab === 'profile' ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'" class="pb-3 border-b-2 font-medium text-sm transition">
                            Profile & Login
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-6 bg-gray-50/50 dark:bg-gray-900/50">

                    <!-- Tab: Permissions -->
                    <div x-show="currentTab === 'permissions'" class="space-y-8">

                        @if(session('success'))
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: "{{ session('success') }}",
                                    timer: 3000,
                                    showConfirmButton: false,
                                    background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#fff',
                                    color: document.documentElement.classList.contains('dark') ? '#fff' : '#000'
                                });
                            });
                        </script>
                        @endif

                        <div class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg shadow-sm">
                            <div class="px-6 py-4 border-b dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 flex justify-between items-center">
                                <h3 class="font-bold text-gray-800 dark:text-white">Transaction Modules</h3>
                                <div class="flex gap-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wide pr-4">
                                    <span class="w-12 text-center">View</span>
                                    <span class="w-12 text-center">Add</span>
                                    <span class="w-12 text-center">Edit</span>
                                    <span class="w-12 text-center">Del</span>
                                </div>
                            </div>

                            <div class="divide-y dark:divide-gray-700">
                                @php
                                $modules = [
                                'sales_cash' => 'Cash Sales',
                                'sales_debt' => 'Debt / Credit Sales',
                                'sales_return_cash' => 'Sales Returns (Cash)',
                                'sales_return_crdt' => 'Sales Returns (Credit)',
                                'items_stock' => 'Items & Stock Management',
                                'inventory_transfer' => 'Inventory Transfers',
                                'accounts_receipts' => 'Accounts Receipts',
                                'accounts_payments' => 'Accounts Payments',
                                ];
                                @endphp

                                @foreach($modules as $key => $label)
                                @php
                                // Safe access to permissions
                                $perm = $selectedUser->userPermissions ? $selectedUser->userPermissions->$key : null;
                                if(is_array($perm)) { $perm = (object)$perm; }
                                @endphp
                                <div class="px-6 py-3 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <span class="font-medium text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                    <div class="flex gap-4 pr-2">
                                        <input type="checkbox" name="{{ $key }}_view" {{ isset($perm->view) && $perm->view ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                                        <input type="checkbox" name="{{ $key }}_add" {{ isset($perm->add) && $perm->add ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                                        <input type="checkbox" name="{{ $key }}_edit" {{ isset($perm->edit) && $perm->edit ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                                        <input type="checkbox" name="{{ $key }}_del" {{ isset($perm->del) && $perm->del ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

                            <div class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg shadow-sm p-5">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 border-b dark:border-gray-700 pb-2">Operational Constraints</h4>

                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">Can Change Discounts</span>
                                        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                            <input type="checkbox" name="can_change_discount" id="toggle1" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer" {{ $selectedUser->userPermissions->can_change_discount ?? false ? 'checked' : '' }} />
                                            <label for="toggle1" class="toggle-label block overflow-hidden h-5 rounded-full bg-gray-300 dark:bg-gray-600 cursor-pointer"></label>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">Can Close Session</span>
                                        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                            <input type="checkbox" name="can_close_session" id="toggle_close" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer" {{ $selectedUser->userPermissions->can_close_session ?? false ? 'checked' : '' }} />
                                            <label for="toggle_close" class="toggle-label block overflow-hidden h-5 rounded-full bg-gray-300 dark:bg-gray-600 cursor-pointer"></label>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">Allow Credit Above Limit</span>
                                        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                            <input type="checkbox" name="allow_credit_override" id="toggle2" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer" {{ $selectedUser->userPermissions->allow_credit_override ?? false ? 'checked' : '' }} />
                                            <label for="toggle2" class="toggle-label block overflow-hidden h-5 rounded-full bg-gray-300 dark:bg-gray-600 cursor-pointer"></label>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600 dark:text-gray-300">View All Counters</span>
                                        <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                            <input type="checkbox" name="view_all_counters" id="toggle_vac" class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer" {{ $selectedUser->userPermissions->view_all_counters ?? false ? 'checked' : '' }} />
                                            <label for="toggle_vac" class="toggle-label block overflow-hidden h-5 rounded-full bg-gray-300 dark:bg-gray-600 cursor-pointer"></label>
                                        </div>
                                    </div>

                                    <div class="pt-2">
                                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Minimum QTY Limit</label>
                                        <input type="number" name="min_qty_limit" value="{{ $selectedUser->userPermissions->min_qty_limit ?? 0 }}" class="w-full p-2 border rounded-lg text-sm bg-gray-50 dark:bg-gray-700 border-gray-200 dark:border-gray-600 focus:bg-white dark:focus:bg-gray-600 focus:ring-2 focus:ring-indigo-500 outline-none text-gray-900 dark:text-gray-100">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-lg shadow-sm p-5">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 border-b dark:border-gray-700 pb-2">Administrative & System</h4>

                                <div class="space-y-3">
                                    <label class="flex items-center space-x-3">
                                        <input type="checkbox" name="sys_add_users" {{ $selectedUser->userPermissions->sys_add_users ?? false ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Add / Delete Users</span>
                                    </label>
                                    <label class="flex items-center space-x-3">
                                        <input type="checkbox" name="sys_restore_data" {{ $selectedUser->userPermissions->sys_restore_data ?? false ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Restore Data / Backups</span>
                                    </label>
                                    <label class="flex items-center space-x-3">
                                        <input type="checkbox" name="sys_view_reports" {{ $selectedUser->userPermissions->sys_view_reports ?? false ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">View Financial Reports</span>
                                    </label>
                                    <label class="flex items-center space-x-3">
                                        <input type="checkbox" name="sys_reconcile_banks" {{ $selectedUser->userPermissions->sys_reconcile_banks ?? false ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-indigo-600 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">Reconcile Banks</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Tab: Profile -->
                    <div x-show="currentTab === 'profile'" style="display: none;">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg border dark:border-gray-700 shadow-sm max-w-lg">
                            <h3 class="font-bold text-lg mb-4 text-gray-900 dark:text-white">User Details</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Display Name</label>
                                    <input type="text" name="name" value="{{ $selectedUser->name }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email Address</label>
                                    <input type="email" name="email" value="{{ $selectedUser->email }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Role</label>
                                    <select name="role" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                                        <option value="owner" {{ $selectedUser->role === 'owner' ? 'selected' : '' }}>Owner</option>
                                        <option value="admin" {{ $selectedUser->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        <option value="sales" {{ $selectedUser->role === 'sales' ? 'selected' : '' }}>Sales Staff</option>
                                        <option value="inventory" {{ $selectedUser->role === 'inventory' ? 'selected' : '' }}>Inventory Manager</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            @else
            <div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">
                Please select a user from the left to manage permissions.
            </div>
            @endif
        </div>
    </div>

</body>

</html>