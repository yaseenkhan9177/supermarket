<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank & Cash Accounts | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800" x-data="bankManager()">

    <nav class="bg-emerald-800 border-b border-emerald-700 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8 text-white">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-emerald-600 flex items-center justify-center text-white shadow-md border border-emerald-500">
                    <i class="fas fa-university text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-white leading-none tracking-tight">
                        OwnStore <span class="text-emerald-300">PRO</span>
                    </h1>
                    <span class="text-xs text-emerald-200 font-medium mt-0.5">Bank & Cash Management</span>
                </div>
            </div>
            <div>
                <a href="/dashboard" class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-700 hover:bg-emerald-900 text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105 border border-emerald-600">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1400px] pb-32">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-emerald-500">
                <div class="text-gray-500 text-sm font-bold uppercase">Total Liquid Funds</div>
                <div class="text-3xl font-bold text-gray-800 mt-1">Rs. 450,000</div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-blue-500">
                <div class="text-gray-500 text-sm font-bold uppercase">Cash in Hand</div>
                <div class="text-3xl font-bold text-gray-800 mt-1">Rs. 50,000</div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow border-l-4 border-purple-500">
                <div class="text-gray-500 text-sm font-bold uppercase">Bank Balance</div>
                <div class="text-3xl font-bold text-gray-800 mt-1">Rs. 400,000</div>
            </div>
        </div>

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-700"><i class="fas fa-wallet mr-2"></i>Accounts List</h2>
            <button @click="openModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-lg font-bold shadow transition flex items-center">
                <i class="fas fa-plus-circle mr-2"></i> Add Account
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Using PHP loop efficiently here for now rather than Alpine x-for for Server-Side Render -->
            @php
            $banks = \App\Models\BankAccount::all();
            @endphp

            @forelse($banks as $bank)
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition group relative">
                <div class="h-2 w-full {{ $bank->bank_name === 'Internal' ? 'bg-blue-500' : 'bg-emerald-500' }}"></div>

                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-xl shadow-inner {{ $bank->bank_name === 'Internal' ? 'bg-blue-100 text-blue-600' : 'bg-emerald-100 text-emerald-600' }}">
                            <i class="{{ $bank->bank_name === 'Internal' ? 'fas fa-cash-register' : 'fas fa-landmark' }}"></i>
                        </div>
                        <span class="bg-gray-100 text-gray-500 text-xs font-mono px-2 py-1 rounded border border-gray-300">{{ $bank->gl_code }}</span>
                    </div>

                    <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $bank->account_title }}</h3>
                    <p class="text-xs text-gray-500 uppercase font-bold tracking-wide">{{ $bank->bank_name }}</p>

                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Account No</p>
                                <p class="text-sm font-mono text-gray-600">{{ $bank->account_number ?? 'N/A' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-gray-400 font-bold uppercase">Current Balance</p>
                                <p class="text-xl font-bold {{ $bank->current_balance < 0 ? 'text-red-500' : 'text-emerald-700' }}">
                                    Rs. {{ number_format($bank->current_balance, 2) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-3 flex justify-between items-center border-t border-gray-100">
                    <button @click="openModal({{ $bank }})" class="text-sm text-gray-600 hover:text-emerald-600 font-bold">Edit Info</button>
                    <a href="{{ route('banks.show', $bank->id) }}" class="text-sm text-emerald-600 hover:text-emerald-800 font-bold flex items-center">
                        View Statement <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-12 text-gray-400">
                <i class="fas fa-university text-4xl mb-4"></i>
                <p>No Bank Accounts Found. Click "Add Account" to start.</p>
            </div>
            @endforelse
        </div>
    </div>

    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="isModalOpen" class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div x-show="isModalOpen" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">

                <form action="/banks/store" method="POST">
                    @csrf
                    <input type="hidden" name="id" x-model="form.id">

                    <div class="bg-emerald-800 px-4 py-3 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-bold text-white" x-text="form.id ? 'Edit Account' : 'New Account'"></h3>
                        <button type="button" @click="closeModal()" class="text-white hover:text-gray-300"><i class="fas fa-times"></i></button>
                    </div>

                    <div class="px-6 py-6 space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Account Type</label>
                            <select name="bank_name" x-model="form.bank_name" class="w-full border border-gray-300 rounded p-2 focus:border-emerald-500 outline-none">
                                <option value="Internal">Internal Cash (Safe/Drawer)</option>
                                <option value="Meezan Bank">Meezan Bank</option>
                                <option value="HBL">HBL</option>
                                <option value="Other">Other Bank</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Account Title (Name)</label>
                            <input type="text" name="account_title" x-model="form.account_title" placeholder="e.g. Main Cash Drawer" required class="w-full border border-gray-300 rounded p-2 focus:ring-2 focus:ring-emerald-500 outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">GL Code (Auto)</label>
                                <input type="text" name="gl_code" x-model="form.gl_code" class="w-full bg-gray-100 border border-gray-300 rounded p-2 font-mono text-gray-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Opening Balance</label>
                                <input type="number" step="0.01" name="opening_balance" x-model="form.opening_balance" class="w-full border border-gray-300 rounded p-2">
                            </div>
                        </div>

                        <div x-show="form.bank_name !== 'Internal'" class="bg-gray-50 p-3 rounded border border-gray-200">
                            <h4 class="text-xs font-bold text-emerald-600 mb-2 border-b border-gray-200 pb-1">Bank Details</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Account No</label>
                                    <input type="text" name="account_number" x-model="form.account_number" class="w-full border border-gray-300 rounded p-1.5 text-sm">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-1">Branch Code</label>
                                    <input type="text" name="branch_code" x-model="form.branch_code" class="w-full border border-gray-300 rounded p-1.5 text-sm">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-3 flex justify-end gap-3">
                        <button type="button" @click="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-700 font-bold rounded hover:bg-gray-300">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-emerald-700 text-white font-bold rounded hover:bg-emerald-800 shadow">
                            <i class="fas fa-save mr-2"></i> Save Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function bankManager() {
            return {
                isModalOpen: false,
                form: {
                    id: '',
                    account_title: '',
                    bank_name: 'Internal',
                    gl_code: '',
                    opening_balance: 0,
                    account_number: '',
                    branch_code: ''
                },

                openModal(bank = null) {
                    if (bank) {
                        this.form = {
                            ...bank
                        };
                    } else {
                        // Estimate next GL code (simple heuristic from existing count, imperfect but functional for UI)
                        let count = Number('{{ \App\Models\BankAccount::count() }}');
                        this.form = {
                            id: '',
                            account_title: '',
                            bank_name: 'Internal',
                            gl_code: '01-00' + (count + 1),
                            opening_balance: 0,
                            account_number: '',
                            branch_code: ''
                        };
                    }
                    this.isModalOpen = true;
                },

                closeModal() {
                    this.isModalOpen = false;
                }
            }
        }
    </script>
</body>

</html>