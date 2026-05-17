<div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 mt-4 relative z-40" x-cloak x-show="activeTab !== 'none' && activeTab !== 'dashboard'">
    <div class="bg-white rounded-[20px] shadow-lg py-3 px-6 overflow-x-auto no-scrollbar border border-gray-100">

        <!-- ================= GENERAL SECTION ================= -->
        <div class="flex items-center gap-4 sm:gap-6" x-show="activeTab === 'general'">
            <a href="{{ route('settings.general') }}" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-desktop text-2xl mb-1"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">General</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-palette text-2xl mb-1"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Styles</span>
            </a>
            <a href="{{ route('settings.users') }}" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-users text-2xl mb-1"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Access</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-user-plus text-2xl mb-1"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Add/Edit</span>
            </a>

            <div class="w-px h-8 bg-gray-200"></div>

            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-clipboard-list text-2xl mb-1"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">To Do</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-clock text-2xl mb-1"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Reminder</span>
            </a>
            <a href="{{ route('settings.employees') }}" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-user-tie text-2xl mb-1"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Employees</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-save text-2xl mb-1"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Backup</span>
            </a>

            <div class="w-px h-8 bg-gray-200"></div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex flex-col items-center group min-w-[50px] text-red-500 hover:text-red-700 transition">
                    <i class="fas fa-power-off text-2xl mb-1"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wide">Exit</span>
                </button>
            </form>
        </div>


        <!-- ================= SALES SECTION ================= -->
        <div class="flex items-center gap-4 sm:gap-6" x-show="activeTab === 'sales'">
            <a href="{{ route('debit-sales.create') }}" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-cash-register text-2xl mb-1 text-red-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Counter</span>
            </a>
            <a href="{{ route('debit-sales.create') }}" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-cart-plus text-2xl mb-1 text-blue-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">New</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-globe text-2xl mb-1 text-blue-400"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Online</span>
            </a>

            <div class="w-px h-8 bg-gray-200"></div>

            <a href="{{ route('cash-sales.create') }}" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-money-bill-wave text-2xl mb-1 text-green-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Cash Sales</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-credit-card text-2xl mb-1 text-orange-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">CRDT Sales</span>
            </a>
            <a href="{{ route('refunds.create') }}" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-undo text-2xl mb-1 text-blue-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Refunds</span>
            </a>

            <div class="w-px h-8 bg-gray-200"></div>

            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-receipt text-2xl mb-1 text-gray-600"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Receipts</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-file-invoice-dollar text-2xl mb-1 text-yellow-600"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Payments</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-exchange-alt text-2xl mb-1 text-gray-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Transfers</span>
            </a>

            <div class="w-px h-8 bg-gray-200"></div>

            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-box text-2xl mb-1 text-gray-700"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Items</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-barcode text-2xl mb-1 text-gray-800"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Barcodes</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-tools text-2xl mb-1 text-blue-400"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Adjust</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-user-cog text-2xl mb-1 text-teal-600"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Manage</span>
            </a>

            <div class="w-px h-8 bg-gray-200"></div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex flex-col items-center group min-w-[50px] text-red-500 hover:text-red-700 transition">
                    <i class="fas fa-power-off text-2xl mb-1"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wide">Exit</span>
                </button>
            </form>
        </div>


        <!-- ================= PURCHASE SECTION ================= -->
        <div class="flex items-center gap-4 sm:gap-6" x-show="activeTab === 'purchase'">
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-file-invoice-dollar text-2xl mb-1 text-blue-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Cash Bill</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-file-contract text-2xl mb-1 text-blue-400"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">CRDT Bill</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-clipboard-check text-2xl mb-1 text-teal-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Pur. Order</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-undo-alt text-2xl mb-1 text-blue-600"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Returns</span>
            </a>
            <div class="w-px h-8 bg-gray-200"></div>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-coins text-2xl mb-1 text-yellow-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Payments</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-money-check-alt text-2xl mb-1 text-gray-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Receipts</span>
            </a>
            <div class="w-px h-8 bg-gray-200"></div>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-box-open text-2xl mb-1 text-gray-600"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Items</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-barcode text-2xl mb-1 text-gray-800"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Barcodes</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-wrench text-2xl mb-1 text-blue-400"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Adjust</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-truck text-2xl mb-1 text-pink-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Suppliers</span>
            </a>
            <div class="w-px h-8 bg-gray-200"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex flex-col items-center group min-w-[50px] text-red-500 hover:text-red-700 transition">
                    <i class="fas fa-power-off text-2xl mb-1"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wide">Exit</span>
                </button>
            </form>
        </div>


        <!-- ================= ACCOUNTS SECTION ================= -->
        <div class="flex items-center gap-4 sm:gap-6" x-show="activeTab === 'accounts'">
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-book-open text-2xl mb-1 text-orange-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Journal</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-book text-2xl mb-1 text-blue-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">GLedgers</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-briefcase text-2xl mb-1 text-yellow-600"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Accounts</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-university text-2xl mb-1 text-green-600"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Banks</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-search text-2xl mb-1 text-blue-400"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Names</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-search-minus text-2xl mb-1 text-teal-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Missing</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-dollar-sign text-2xl mb-1 text-purple-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Values</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-globe text-2xl mb-1 text-blue-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Online</span>
            </a>
            <div class="w-px h-8 bg-gray-200"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex flex-col items-center group min-w-[50px] text-red-500 hover:text-red-700 transition">
                    <i class="fas fa-power-off text-2xl mb-1"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wide">Exit</span>
                </button>
            </form>
        </div>


        <!-- ================= REPORTS SECTION ================= -->
        <div class="flex items-center gap-4 sm:gap-6" x-show="activeTab === 'reports'">
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-folder-open text-2xl mb-1 text-yellow-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Selected</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-window-restore text-2xl mb-1 text-gray-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Child</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-times text-2xl mb-1 text-red-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Delete</span>
            </a>

            <div class="w-px h-8 bg-gray-200"></div>

            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-file-alt text-2xl mb-1 text-yellow-600"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">View</span>
            </a>

            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-print text-2xl mb-1 text-blue-600"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Print</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-envelope text-2xl mb-1 text-orange-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Email</span>
            </a>

            <div class="w-px h-8 bg-gray-200"></div>

            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-code text-2xl mb-1 text-gray-600"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Code</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-columns text-2xl mb-1 text-gray-500"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Layout</span>
            </a>
            <a href="#" class="flex flex-col items-center group min-w-[50px] text-gray-500 hover:text-indigo-600 transition">
                <i class="fas fa-user-lock text-2xl mb-1 text-gray-800"></i>
                <span class="text-[10px] font-bold uppercase tracking-wide">Restrict</span>
            </a>

            <div class="w-px h-8 bg-gray-200"></div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex flex-col items-center group min-w-[50px] text-red-500 hover:text-red-700 transition">
                    <i class="fas fa-power-off text-2xl mb-1"></i>
                    <span class="text-[10px] font-bold uppercase tracking-wide">Exit</span>
                </button>
            </form>
        </div>


    </div>
</div>