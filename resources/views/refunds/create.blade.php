<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <title>Process Return | OwnStore</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: { DEFAULT: '#DC2626', dark: '#991B1B', light: '#FEE2E2' }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }

        /* Smooth transitions */
        .bill-card { transition: all 0.2s ease; }
        .bill-card:hover { transform: translateY(-1px); }

        /* Condition toggle */
        .condition-btn { transition: all 0.15s ease; }
        .condition-btn.active-restock { background: #D1FAE5; color: #065F46; border-color: #059669; }
        .condition-btn.active-damaged { background: #FEE2E2; color: #991B1B; border-color: #DC2626; }

        /* Pulse on add to cart */
        @keyframes cartPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .cart-pulse { animation: cartPulse 0.3s ease; }

        /* Search spinner */
        .spinner { border: 2px solid #f3f4f6; border-top: 2px solid #DC2626; border-radius: 50%; width: 16px; height: 16px; animation: spin 0.7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Scrollbar */
        .thin-scroll::-webkit-scrollbar { width: 4px; }
        .thin-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
        .thin-scroll::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 2px; }
    </style>
</head>

<body class="bg-slate-100 font-sans text-gray-800" x-data="returnsApp()" x-init="init()">

    <!-- ====================================================================
         TOP NAV BAR
    ================================================================== -->
    <nav class="bg-white border-b border-gray-200 px-6 py-3 shadow-sm sticky top-0 z-50">
        <div class="max-w-[1500px] mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-600 flex items-center justify-center text-white shadow">
                    <i class="fas fa-undo-alt text-base"></i>
                </div>
                <div>
                    <h1 class="text-lg font-extrabold text-gray-900 leading-none">Process Return</h1>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Search bills · select items · confirm refund</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('refunds.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                    <i class="fas fa-list text-xs"></i> All Returns
                </a>
                <a href="{{ url()->previous() }}"
                   class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold bg-gray-800 text-white rounded-lg hover:bg-black transition">
                    <i class="fas fa-arrow-left text-xs"></i> Back
                </a>
            </div>
        </div>
    </nav>

    <!-- Flash messages -->
    @if(session('error'))
    <div class="max-w-[1500px] mx-auto px-6 pt-4">
        <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-lg flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-red-500"></i>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- ====================================================================
         MAIN LAYOUT — Left panel (search) + Right panel (cart)
    ================================================================== -->
    <div class="max-w-[1500px] mx-auto px-4 py-5 flex gap-5 h-[calc(100vh-72px)]">

        <!-- ================================================================
             LEFT PANEL — Search & Bill Selection
        ================================================================ -->
        <div class="flex-1 flex flex-col gap-4 min-w-0">

            <!-- SEARCH BAR -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                    <i class="fas fa-search mr-1 text-red-500"></i> Search Bills
                </label>
                <div class="relative">
                    <input
                        type="text"
                        x-model="searchQuery"
                        @input.debounce.300ms="searchBills()"
                        @keydown.escape="searchQuery = ''; billResults = []"
                        placeholder="Customer name, phone number, or invoice number…"
                        class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm placeholder-gray-400 bg-gray-50"
                        id="bill-search-input"
                        autocomplete="off"
                    />
                    <div class="absolute right-3 top-1/2 -translate-y-1/2">
                        <div x-show="isSearching" class="spinner"></div>
                        <i x-show="!isSearching && searchQuery.length > 0" @click="searchQuery=''; billResults=[]"
                           class="fas fa-times text-gray-400 cursor-pointer hover:text-gray-600 transition"></i>
                        <i x-show="!isSearching && searchQuery.length === 0" class="fas fa-search text-gray-400"></i>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">Searches across Cash Sales, Debit Sales, and POS Sales simultaneously.</p>
            </div>

            <!-- BILL RESULTS -->
            <div class="flex-1 overflow-y-auto thin-scroll space-y-3" x-cloak>

                <!-- Placeholder state -->
                <div x-show="billResults.length === 0 && !isSearching && searchQuery.length < 2"
                     class="bg-white rounded-2xl border border-dashed border-gray-200 p-12 text-center">
                    <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-receipt text-red-400 text-2xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Type a customer name, phone, or bill number above</p>
                    <p class="text-xs text-gray-400 mt-1">Minimum 2 characters to search</p>
                </div>

                <!-- No results -->
                <div x-show="billResults.length === 0 && !isSearching && searchQuery.length >= 2"
                     class="bg-white rounded-2xl border border-gray-100 p-10 text-center">
                    <i class="fas fa-search text-gray-300 text-3xl mb-3"></i>
                    <p class="text-gray-500 font-medium">No bills found for "<span x-text="searchQuery"></span>"</p>
                </div>

                <!-- Bill Cards -->
                <template x-for="bill in billResults" :key="bill.source + '-' + bill.id">
                    <div class="bill-card bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <!-- Bill header row -->
                        <div class="flex items-center justify-between px-5 py-4 cursor-pointer hover:bg-gray-50 transition"
                             @click="toggleBill(bill)">
                            <div class="flex items-center gap-4">
                                <!-- Type badge -->
                                <span class="text-xs font-bold px-2.5 py-1 rounded-full"
                                      :class="{
                                        'bg-green-100 text-green-700': bill.type_color === 'green',
                                        'bg-orange-100 text-orange-700': bill.type_color === 'orange',
                                        'bg-blue-100 text-blue-700': bill.type_color === 'blue',
                                      }"
                                      x-text="bill.type_label">
                                </span>
                                <div>
                                    <p class="font-bold text-gray-900 text-sm" x-text="bill.invoice_no"></p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        <i class="fas fa-user mr-1"></i><span x-text="bill.customer_name"></span>
                                        &nbsp;·&nbsp;
                                        <i class="fas fa-calendar mr-1"></i><span x-text="bill.date"></span>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">Total</p>
                                    <p class="font-extrabold text-gray-900 text-sm">Rs. <span x-text="bill.grand_total"></span></p>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center transition-transform duration-200"
                                     :class="expandedBills[bill.source + '-' + bill.id] ? 'rotate-180' : ''">
                                    <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Expanded line items -->
                        <div x-show="expandedBills[bill.source + '-' + bill.id]"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="border-t border-gray-100">

                            <!-- Loading items -->
                            <div x-show="loadingItems[bill.source + '-' + bill.id]"
                                 class="px-5 py-4 text-center text-gray-400 text-sm">
                                <div class="spinner mx-auto mb-2"></div> Loading items…
                            </div>

                            <!-- Item rows -->
                            <template x-for="item in (billItems[bill.source + '-' + bill.id] || [])" :key="item.line_item_id">
                                <div class="flex items-center gap-3 px-5 py-3 border-b border-gray-50 hover:bg-slate-50 transition">
                                    <!-- Checkbox -->
                                    <input type="checkbox"
                                           :id="'item-' + bill.source + '-' + item.line_item_id"
                                           class="w-4 h-4 text-red-600 rounded border-gray-300 cursor-pointer"
                                           :checked="isInCart(item, bill)"
                                           @change="toggleCartItem($event, item, bill)" />

                                    <!-- Item details -->
                                    <label :for="'item-' + bill.source + '-' + item.line_item_id"
                                           class="flex-1 cursor-pointer">
                                        <p class="text-sm font-semibold text-gray-800" x-text="item.item_name"></p>
                                        <p class="text-xs text-gray-500 mt-0.5">
                                            Qty bought: <span class="font-bold text-gray-700" x-text="item.quantity"></span>
                                            &nbsp;·&nbsp;
                                            Rate: Rs. <span x-text="parseFloat(item.rate).toFixed(2)"></span>
                                        </p>
                                    </label>

                                    <!-- Return qty input (only shown if in cart) -->
                                    <div x-show="isInCart(item, bill)" class="flex items-center gap-2" x-cloak>
                                        <label class="text-xs text-gray-500 font-medium">Return qty:</label>
                                        <input type="number"
                                               :value="getCartItem(item, bill)?.return_qty || 1"
                                               @change="updateCartQty($event, item, bill)"
                                               min="0.01"
                                               :max="item.quantity"
                                               step="1"
                                               class="w-20 text-center text-sm border border-gray-300 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-red-500 focus:outline-none" />
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ================================================================
             RIGHT PANEL — Return Cart + Refund Method + Submit
        ================================================================ -->
        <div class="w-96 flex-shrink-0 flex flex-col gap-4">

            <!-- CART HEADER -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center">
                        <i class="fas fa-shopping-basket text-red-600 text-sm"></i>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 text-sm">Return Cart</p>
                        <p class="text-xs text-gray-500" x-text="cart.length + ' item(s) selected'"></p>
                    </div>
                </div>
                <button x-show="cart.length > 0" @click="clearCart()"
                        class="text-xs text-red-500 hover:text-red-700 font-semibold transition" x-cloak>
                    <i class="fas fa-trash-alt mr-1"></i> Clear
                </button>
            </div>

            <!-- CART ITEMS -->
            <div class="flex-1 overflow-y-auto thin-scroll space-y-3">

                <!-- Empty cart -->
                <div x-show="cart.length === 0"
                     class="bg-white rounded-2xl border border-dashed border-gray-200 p-10 text-center">
                    <div class="w-14 h-14 bg-gray-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-box-open text-gray-400 text-xl"></i>
                    </div>
                    <p class="text-sm text-gray-500 font-medium">Cart is empty</p>
                    <p class="text-xs text-gray-400 mt-1">Check items from bills on the left</p>
                </div>

                <!-- Cart items -->
                <template x-for="(cartItem, index) in cart" :key="cartItem.key">
                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1 min-w-0 pr-2">
                                <p class="text-sm font-bold text-gray-900 truncate" x-text="cartItem.item_name"></p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    <span class="text-xs font-semibold px-1.5 py-0.5 rounded"
                                          :class="{
                                            'bg-green-100 text-green-700': cartItem.bill_source === 'cash_sale',
                                            'bg-orange-100 text-orange-700': cartItem.bill_source === 'debit_sale',
                                            'bg-blue-100 text-blue-700': cartItem.bill_source === 'pos_sale',
                                          }"
                                          x-text="cartItem.bill_invoice"></span>
                                </p>
                            </div>
                            <button @click="removeFromCart(index)"
                                    class="text-gray-400 hover:text-red-500 transition flex-shrink-0">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>

                        <!-- Qty and total -->
                        <div class="flex items-center justify-between text-sm mb-3">
                            <span class="text-gray-500">
                                <span x-text="cartItem.return_qty"></span>
                                × Rs. <span x-text="parseFloat(cartItem.rate).toFixed(2)"></span>
                            </span>
                            <span class="font-bold text-gray-900">
                                Rs. <span x-text="(cartItem.return_qty * cartItem.rate).toFixed(2)"></span>
                            </span>
                        </div>

                        <!-- Condition toggle -->
                        <div class="flex gap-2">
                            <button type="button"
                                    @click="setCondition(index, 'restock')"
                                    class="condition-btn flex-1 text-xs font-bold py-2 px-3 rounded-lg border-2 transition flex items-center justify-center gap-1.5"
                                    :class="cartItem.condition === 'restock' ? 'active-restock border-green-500' : 'bg-gray-50 border-gray-200 text-gray-600'">
                                <i class="fas fa-redo-alt text-xs"></i> Restock
                            </button>
                            <button type="button"
                                    @click="setCondition(index, 'damaged')"
                                    class="condition-btn flex-1 text-xs font-bold py-2 px-3 rounded-lg border-2 transition flex items-center justify-center gap-1.5"
                                    :class="cartItem.condition === 'damaged' ? 'active-damaged border-red-500' : 'bg-gray-50 border-gray-200 text-gray-600'">
                                <i class="fas fa-exclamation-triangle text-xs"></i> Damaged
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- TOTALS + REFUND METHOD + SUBMIT -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 space-y-4">

                <!-- Running total -->
                <div class="flex justify-between items-center pb-3 border-b border-gray-100">
                    <span class="text-sm font-semibold text-gray-600">Total Refund</span>
                    <span class="text-xl font-extrabold text-red-600">
                        Rs. <span x-text="cartTotal.toFixed(2)"></span>
                    </span>
                </div>

                <!-- Memo / Notes -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">
                        Note (optional)
                    </label>
                    <textarea x-model="memo" rows="2"
                              placeholder="Reason for return…"
                              class="w-full text-sm border border-gray-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-400 resize-none bg-gray-50"></textarea>
                </div>

                <!-- REFUND METHOD -->
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                        Refund Method <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2">
                        <!-- Cash Refund -->
                        <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition"
                               :class="refundMethod === 'CASH' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="refund_method_ui" value="CASH" x-model="refundMethod"
                                   class="text-green-600 focus:ring-green-500" />
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-green-100 flex items-center justify-center">
                                    <i class="fas fa-money-bill-wave text-green-600 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">Cash Refund</p>
                                    <p class="text-xs text-gray-500">Give cash back to customer</p>
                                </div>
                            </div>
                        </label>

                        <!-- Store Credit -->
                        <label class="flex flex-col p-3 rounded-xl border-2 transition"
                               :class="hasWalkInItem 
                                    ? 'border-gray-100 bg-gray-50 opacity-60 cursor-not-allowed text-gray-400' 
                                    : 'cursor-pointer ' + (refundMethod === 'STORE_CREDIT' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300')">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="refund_method_ui" value="STORE_CREDIT" x-model="refundMethod" :disabled="hasWalkInItem"
                                       class="text-blue-600 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed" />
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-wallet text-blue-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">Store Credit</p>
                                        <p class="text-xs text-gray-500">Add credit to customer's account</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Note when disabled -->
                            <p x-show="hasWalkInItem" class="text-[11px] text-amber-600 mt-2 font-medium" x-cloak>
                                <i class="fas fa-exclamation-triangle mr-1"></i> Store credit requires a registered customer.
                            </p>
                        </label>

                        <!-- Reduce Debit Balance — only shown if a debit_sale item is in cart -->
                        <label x-show="hasDebitSaleItem"
                               class="flex flex-col p-3 rounded-xl border-2 transition"
                               :class="hasWalkInItem 
                                    ? 'border-gray-100 bg-gray-50 opacity-60 cursor-not-allowed text-gray-400' 
                                    : 'cursor-pointer ' + (refundMethod === 'REDUCE_DEBIT' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-gray-300')">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="refund_method_ui" value="REDUCE_DEBIT" x-model="refundMethod" :disabled="hasWalkInItem"
                                       class="text-orange-600 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed" />
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-orange-100 flex items-center justify-center">
                                        <i class="fas fa-minus-circle text-orange-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-800">Reduce Debit Balance</p>
                                        <p class="text-xs text-gray-500">Deduct from customer's outstanding debt</p>
                                    </div>
                                </div>
                            </div>
                            <!-- Note when disabled -->
                            <p x-show="hasWalkInItem" class="text-[11px] text-amber-600 mt-2 font-medium" x-cloak>
                                <i class="fas fa-exclamation-triangle mr-1"></i> Reducing debit balance requires a registered customer.
                            </p>
                        </label>
                    </div>
                </div>

                <!-- PROCESS RETURN BUTTON -->
                <form id="return-form" action="{{ route('refunds.store') }}" method="POST" @submit.prevent="submitReturn">
                    @csrf
                    <!-- Hidden fields injected by JS on submit -->
                    <div id="hidden-fields"></div>

                    <button type="submit"
                            :disabled="cart.length === 0 || !refundMethod || isSubmitting"
                            class="w-full py-3.5 rounded-xl font-extrabold text-sm transition flex items-center justify-center gap-2"
                            :class="cart.length === 0 || !refundMethod
                                ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                                : 'bg-red-600 hover:bg-red-700 text-white shadow-lg hover:shadow-red-200 hover:scale-[1.01]'">
                        <template x-if="isSubmitting">
                            <div class="spinner" style="border-top-color: white;"></div>
                        </template>
                        <template x-if="!isSubmitting">
                            <i class="fas fa-check-circle"></i>
                        </template>
                        <span x-text="isSubmitting ? 'Processing…' : 'Process Return'"></span>
                    </button>

                    <p x-show="cart.length === 0" class="text-center text-xs text-gray-400 mt-2">
                        Add items to cart to enable
                    </p>
                    <p x-show="cart.length > 0 && !refundMethod" class="text-center text-xs text-red-400 mt-2">
                        Select a refund method above
                    </p>
                </form>
            </div>
        </div>
    </div>

    <!-- ====================================================================
         ALPINE.JS APP LOGIC
    ================================================================== -->
    <script>
        function returnsApp() {
            return {
                // Search state
                searchQuery: '',
                isSearching: false,
                billResults: [],
                expandedBills: {},
                loadingItems: {},
                billItems: {},      // key = source-id, value = array of items

                // Cart state
                cart: [],
                memo: '',
                refundMethod: 'CASH',
                isSubmitting: false,

                init() {
                    // Focus the search input on load
                    this.$nextTick(() => {
                        document.getElementById('bill-search-input')?.focus();
                    });
                },

                // ── SEARCH ──────────────────────────────────────────────────
                async searchBills() {
                    if (this.searchQuery.length < 1) {
                        this.billResults = [];
                        return;
                    }
                    this.isSearching = true;
                    try {
                        const res = await fetch(
                            `{{ route('refunds.search-bills') }}?q=${encodeURIComponent(this.searchQuery)}`,
                            { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
                        );
                        this.billResults = await res.json();
                    } catch (e) {
                        console.error('Search failed:', e);
                    } finally {
                        this.isSearching = false;
                    }
                },

                // ── BILL EXPAND / COLLAPSE ───────────────────────────────────
                async toggleBill(bill) {
                    const key = bill.source + '-' + bill.id;
                    this.expandedBills[key] = !this.expandedBills[key];

                    if (this.expandedBills[key] && !this.billItems[key]) {
                        await this.loadBillItems(bill);
                    }
                },

                async loadBillItems(bill) {
                    const key = bill.source + '-' + bill.id;
                    this.loadingItems[key] = true;
                    try {
                        const res = await fetch(
                            `{{ route('refunds.bill-items') }}?source=${bill.source}&id=${bill.id}`,
                            { headers: { 'X-Requested-With': 'XMLHttpRequest' } }
                        );
                        this.billItems[key] = await res.json();
                    } catch (e) {
                        console.error('Failed to load bill items:', e);
                        this.billItems[key] = [];
                    } finally {
                        this.loadingItems[key] = false;
                    }
                },

                // ── CART MANAGEMENT ──────────────────────────────────────────
                cartKey(item, bill) {
                    // Unique key for a cart item = bill source + line item id
                    return bill.source + '-' + item.line_item_id;
                },

                isInCart(item, bill) {
                    return this.cart.some(c => c.key === this.cartKey(item, bill));
                },

                getCartItem(item, bill) {
                    return this.cart.find(c => c.key === this.cartKey(item, bill));
                },

                toggleCartItem(event, item, bill) {
                    if (event.target.checked) {
                        this.addToCart(item, bill);
                    } else {
                        const idx = this.cart.findIndex(c => c.key === this.cartKey(item, bill));
                        if (idx !== -1) this.cart.splice(idx, 1);
                    }
                },

                addToCart(item, bill) {
                    if (this.isInCart(item, bill)) return;
                    this.cart.push({
                        key:          this.cartKey(item, bill),
                        product_id:   item.product_id,
                        line_item_id: item.line_item_id,
                        item_name:    item.item_name,
                        return_qty:   item.quantity,  // default to full qty, cashier can reduce
                        rate:         item.rate,
                        condition:    'restock',       // default to restock
                        bill_id:      bill.id,
                        bill_source:  bill.source,
                        bill_invoice: bill.invoice_no,
                        customer_id:  bill.customer_id,
                    });
                    this.checkRefundMethod();
                },

                removeFromCart(index) {
                    this.cart.splice(index, 1);
                    this.checkRefundMethod();
                },

                clearCart() {
                    this.cart = [];
                    this.refundMethod = 'CASH';
                },

                updateCartQty(event, item, bill) {
                    const key = this.cartKey(item, bill);
                    const entry = this.cart.find(c => c.key === key);
                    if (entry) {
                        const val = parseFloat(event.target.value) || 1;
                        entry.return_qty = Math.min(val, item.quantity);
                    }
                },

                setCondition(index, condition) {
                    this.cart[index].condition = condition;
                },

                checkRefundMethod() {
                    if (this.hasWalkInItem) {
                        this.refundMethod = 'CASH';
                    } else if (this.refundMethod === 'REDUCE_DEBIT' && !this.hasDebitSaleItem) {
                        this.refundMethod = 'CASH';
                    }
                },

                // ── COMPUTED ─────────────────────────────────────────────────
                get cartTotal() {
                    return this.cart.reduce((sum, item) => {
                        return sum + (parseFloat(item.return_qty) * parseFloat(item.rate));
                    }, 0);
                },

                get hasDebitSaleItem() {
                    return this.cart.some(c => c.bill_source === 'debit_sale');
                },

                get hasWalkInItem() {
                    return this.cart.some(c => !c.customer_id);
                },

                // Determine a single customer_id for the refund header
                // (prefer the first non-null customer_id found across selected bills)
                get derivedCustomerId() {
                    for (const c of this.cart) {
                        if (c.customer_id) return c.customer_id;
                    }
                    return null;
                },

                // ── SUBMIT ────────────────────────────────────────────────────
                submitReturn() {
                    if (this.cart.length === 0) {
                        alert('Please add at least one item to the return cart.');
                        return;
                    }
                    if (!this.refundMethod) {
                        alert('Please select a refund method.');
                        return;
                    }
                    if (this.refundMethod === 'REDUCE_DEBIT' && !this.hasDebitSaleItem) {
                        alert('Reduce Debit Balance is only available when returning Debit Sale items.');
                        return;
                    }
                    if (this.hasWalkInItem && this.refundMethod !== 'CASH') {
                        alert('Only Cash Refund is allowed for walk-in transactions.');
                        return;
                    }

                    this.isSubmitting = true;

                    const container = document.getElementById('hidden-fields');
                    container.innerHTML = '';

                    const addHidden = (name, value) => {
                        const el = document.createElement('input');
                        el.type = 'hidden';
                        el.name = name;
                        el.value = value;
                        container.appendChild(el);
                    };

                    addHidden('refund_method', this.refundMethod);
                    addHidden('memo', this.memo);
                    addHidden('customer_id', this.derivedCustomerId || '');

                    this.cart.forEach((item, idx) => {
                        addHidden(`items[${idx}][product_id]`,   item.product_id);
                        addHidden(`items[${idx}][line_item_id]`, item.line_item_id);
                        addHidden(`items[${idx}][item_name]`,    item.item_name);
                        addHidden(`items[${idx}][return_qty]`,   item.return_qty);
                        addHidden(`items[${idx}][rate]`,         item.rate);
                        addHidden(`items[${idx}][condition]`,    item.condition);
                        addHidden(`items[${idx}][sale_source]`,  item.bill_source);
                        addHidden(`items[${idx}][bill_id]`,      item.bill_id);
                    });

                    document.getElementById('return-form').submit();
                },
            };
        }
    </script>

</body>
</html>