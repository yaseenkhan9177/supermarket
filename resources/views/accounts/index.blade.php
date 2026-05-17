<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chart of Accounts | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800" x-data="accountManager(@json($accounts))">

    <nav class="bg-slate-900 border-b border-slate-800 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8 text-white">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-white shadow-md border border-slate-600">
                    <i class="fas fa-sitemap text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-white leading-none tracking-tight">
                        OwnStore <span class="text-slate-400">PRO</span>
                    </h1>
                    <span class="text-xs text-slate-400 font-medium mt-0.5">General Ledger / Chart of Accounts</span>
                </div>
            </div>
            <div>
                <a href="/dashboard" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105 border border-slate-700">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1400px] pb-32">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-8 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800"><i class="fas fa-list-ul mr-2"></i>Accounts Structure</h3>
                    <button @click="openModal()" class="text-xs bg-slate-600 hover:bg-slate-700 text-white px-3 py-1.5 rounded font-bold transition">
                        <i class="fas fa-plus mr-1"></i> New Account
                    </button>
                </div>

                <div class="p-6 space-y-6">
                    <div class="border-l-4 border-green-500 pl-4">
                        <h4 class="font-bold text-lg text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-wallet text-green-500"></i> Assets
                        </h4>

                        <div class="mb-4 ml-4">
                            <h5 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-2">Current Assets</h5>
                            <div class="space-y-2">
                                <template x-for="acc in getAccounts('Asset')" :key="acc.code">
                                    <div @click="editAccount(acc)" class="flex justify-between items-center p-3 bg-gray-50 hover:bg-blue-50 rounded-lg cursor-pointer transition border border-transparent hover:border-blue-200 group">
                                        <div class="flex items-center gap-3">
                                            <span class="font-mono text-xs text-slate-500 bg-white border px-1.5 py-0.5 rounded" x-text="acc.code"></span>
                                            <span class="font-bold text-gray-700" x-text="acc.name"></span>
                                            <span class="text-[10px] text-gray-400" x-text="acc.category"></span>
                                        </div>
                                        <span class="font-mono font-bold text-gray-900 group-hover:text-blue-600" x-text="'Rs. ' + acc.current_balance"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="border-l-4 border-red-500 pl-4 pt-4">
                        <h4 class="font-bold text-lg text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-file-invoice-dollar text-red-500"></i> Liabilities & Expenses
                        </h4>

                        <div class="mb-4 ml-4">
                            <h5 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-2">Liabilities</h5>
                            <div class="space-y-2">
                                <template x-for="acc in getAccounts('Liability')" :key="acc.code">
                                    <div @click="editAccount(acc)" class="flex justify-between items-center p-3 bg-gray-50 hover:bg-red-50 rounded-lg cursor-pointer transition border border-transparent hover:border-red-200 group">
                                        <div class="flex items-center gap-3">
                                            <span class="font-mono text-xs text-slate-500 bg-white border px-1.5 py-0.5 rounded" x-text="acc.code"></span>
                                            <span class="font-bold text-gray-700" x-text="acc.name"></span>
                                            <span class="text-[10px] text-gray-400" x-text="acc.category"></span>
                                        </div>
                                        <span class="font-mono font-bold text-gray-900 group-hover:text-red-600" x-text="'Rs. ' + acc.current_balance"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="mb-4 ml-4">
                            <h5 class="text-sm font-bold text-gray-500 uppercase tracking-wide mb-2">Expenses</h5>
                            <div class="space-y-2">
                                <template x-for="acc in getAccounts('Expense')" :key="acc.code">
                                    <div @click="editAccount(acc)" class="flex justify-between items-center p-3 bg-gray-50 hover:bg-red-50 rounded-lg cursor-pointer transition border border-transparent hover:border-red-200 group">
                                        <div class="flex items-center gap-3">
                                            <span class="font-mono text-xs text-slate-500 bg-white border px-1.5 py-0.5 rounded" x-text="acc.code"></span>
                                            <span class="font-bold text-gray-700" x-text="acc.name"></span>
                                            <span class="text-[10px] text-gray-400" x-text="acc.category"></span>
                                        </div>
                                        <span class="font-mono font-bold text-gray-900 group-hover:text-red-600" x-text="'Rs. ' + acc.current_balance"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="border-l-4 border-blue-500 pl-4 pt-4">
                        <h4 class="font-bold text-lg text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-piggy-bank text-blue-500"></i> Equity & Income
                        </h4>
                        <div class="mb-4 ml-4">
                            <div class="space-y-2">
                                <template x-for="acc in getAccounts('Equity').concat(getAccounts('Income'))" :key="acc.code">
                                    <div @click="editAccount(acc)" class="flex justify-between items-center p-3 bg-gray-50 hover:bg-blue-50 rounded-lg cursor-pointer transition border border-transparent hover:border-blue-200 group">
                                        <div class="flex items-center gap-3">
                                            <span class="font-mono text-xs text-slate-500 bg-white border px-1.5 py-0.5 rounded" x-text="acc.code"></span>
                                            <span class="font-bold text-gray-700" x-text="acc.name"></span>
                                            <span class="text-[10px] text-gray-400" x-text="acc.category"></span>
                                            <span class="text-[9px] uppercase px-1 rounded bg-gray-200 text-gray-600" x-text="acc.type"></span>
                                        </div>
                                        <span class="font-mono font-bold text-gray-900 group-hover:text-blue-600" x-text="'Rs. ' + acc.current_balance"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                <div class="bg-slate-800 text-white rounded-xl shadow-lg p-6 border border-slate-700">
                    <h3 class="font-bold text-lg mb-4">Ledger Overview</h3>
                    <div class="space-y-4">
                        <div class="flex justify-between border-b border-slate-600 pb-2">
                            <span class="text-slate-400">Total Assets</span>
                            <span class="font-bold font-mono text-green-400">Rs. {{ number_format($totalAssets, 2) }}</span>
                        </div>
                        <div class="flex justify-between border-b border-slate-600 pb-2">
                            <span class="text-slate-400">Total Liabilities</span>
                            <span class="font-bold font-mono text-red-400">Rs. {{ number_format($totalLiabilities, 2) }}</span>
                        </div>
                        <div class="flex justify-between pt-2">
                            <span class="text-slate-400">Net Value</span>
                            <span class="font-bold font-mono text-blue-400">Rs. {{ number_format($netEquity, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-50 text-blue-800 rounded-xl p-6 border border-blue-100 text-sm">
                    <p class="mb-2"><strong><i class="fas fa-info-circle"></i> Note:</strong></p>
                    <p>System accounts (like Cash Drawer, Sales) cannot be deleted. You can rename them to fit your business terminology.</p>
                </div>
            </div>
        </div>

    </div>

    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isModalOpen" class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="isModalOpen" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">

                <form action="/accounts/store" method="POST">
                    @csrf
                    <input type="hidden" name="id" x-model="form.id">

                    <div class="bg-slate-800 px-4 py-3 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-bold text-white" x-text="form.id ? 'Edit GL Account' : 'New GL Account'"></h3>
                        <button type="button" @click="closeModal()" class="text-white hover:text-gray-300"><i class="fas fa-times"></i></button>
                    </div>

                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Account Type</label>
                            <select name="type" x-model="form.type" class="w-full border border-gray-300 rounded p-2 focus:border-slate-500 outline-none">
                                <option value="Asset">Asset</option>
                                <option value="Liability">Liability</option>
                                <option value="Equity">Equity</option>
                                <option value="Income">Income</option>
                                <option value="Expense">Expense</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Category (Group)</label>
                            <input type="text" name="category" x-model="form.category" placeholder="e.g. Operating Expenses" class="w-full border border-gray-300 rounded p-2">
                        </div>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-1">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">GL Code</label>
                                <input type="text" name="code" x-model="form.code" class="w-full bg-gray-50 border border-gray-300 rounded p-2 font-mono font-bold">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Account Name</label>
                                <input type="text" name="name" x-model="form.name" class="w-full border border-gray-300 rounded p-2">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Opening Balance</label>
                            <input type="number" step="0.01" name="opening_balance" x-model="form.opening_balance" class="w-full border border-gray-300 rounded p-2">
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-3 flex justify-end gap-3">
                        <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-700 font-bold rounded hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-slate-800 text-white font-bold rounded hover:bg-black shadow">
                            <i class="fas fa-save mr-2"></i> Save Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Standalone Script for Flash Messages -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sessionSuccess = "{{ session('success') }}";
            const sessionError = "{{ session('error') }}";

            if (sessionSuccess) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: sessionSuccess,
                    confirmButtonColor: '#1e293b'
                });
            }

            if (sessionError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: sessionError,
                    confirmButtonColor: '#1e293b'
                });
            }
        });
    </script>

    <script>
        function accountManager(initialAccounts) {
            return {
                isModalOpen: false,
                accounts: initialAccounts,
                form: {
                    id: '',
                    code: '',
                    name: '',
                    type: 'Asset',
                    category: '',
                    opening_balance: 0
                },

                getAccounts(type) {
                    return this.accounts.filter(a => a.type === type);
                },

                editAccount(acc) {
                    this.form = {
                        ...acc
                    };
                    this.isModalOpen = true;
                },

                openModal() {
                    const nextCode = this.calculateNextCode();
                    this.form = {
                        id: '',
                        code: nextCode,
                        name: '',
                        type: 'Asset',
                        category: '',
                        opening_balance: 0
                    };
                    this.isModalOpen = true;
                },

                calculateNextCode() {
                    // Simple helper to suggest a random code, ideally this is smarter
                    return Math.floor(100000 + Math.random() * 900000).toString();
                },

                closeModal() {
                    this.isModalOpen = false;
                }
            }
        }
    </script>
</body>

</html>