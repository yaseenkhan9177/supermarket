@php $defaultTab = 'sales'; @endphp
@extends('layouts.admin')

@section('title', 'POS Terminal')

@section('content')

<div class="min-h-[calc(100vh-100px)] md:h-[calc(100vh-80px)] flex flex-col md:flex-row gap-4 md:gap-6" x-data="posSystem()" @keydown.f2.window="handleF2($event)">

    <div class="flex-1 flex flex-col bg-white dark:bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-800 min-h-[420px]">

        <div class="p-3 sm:p-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row gap-3 sm:gap-4 bg-slate-50 dark:bg-slate-950">
            <div class="relative flex-1">
                <x-smart-product-search @product-selected="addToCart($event.detail)" />
                <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-1"><i class="fas fa-keyboard mr-1"></i> F2: Go to checkout</div>
            </div>
            <select x-model="category" class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-3 sm:px-4 py-2.5 sm:py-3 outline-none focus:ring-2 focus:ring-blue-500 shadow-sm font-bold text-slate-600 dark:text-slate-300 text-sm">
                <option value="all">All Categories</option>
                <option value="Inventory">Inventory</option>
                <option value="Service">Services</option>
                <option value="Package">Packages</option>
            </select>
        </div>

        <div class="flex-1 overflow-y-auto p-3 sm:p-4 bg-slate-100 dark:bg-slate-950/50 custom-scrollbar">
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

                <template x-for="product in filteredProducts" :key="product.id">
                    <div @click="addToCart(product)"
                        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-lg hover:-translate-y-1 transition cursor-pointer overflow-hidden group relative">

                        <span class="absolute top-2 right-2 text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 shadow-sm"
                            :class="isService(product) ? 'text-indigo-500 dark:text-indigo-400' : (productStock(product) > 0 ? 'text-slate-500' : 'text-red-500')">
                            <span x-text="isService(product) ? 'Service' : (productStock(product) + ' left')"></span>
                        </span>

                        <div class="h-32 w-full bg-slate-50 dark:bg-slate-900 flex items-center justify-center p-4 overflow-hidden">
                            <template x-if="product.image_path">
                                <img :src="'/storage/' + product.image_path" class="h-full w-full object-contain hover:scale-110 transition duration-300" alt="Product Image">
                            </template>
                            <template x-if="!product.image_path">
                                <i class="fas fa-box-open text-4xl text-slate-300 group-hover:text-blue-400 transition transform group-hover:scale-110"></i>
                            </template>
                        </div>

                        <div class="p-3">
                            <h3 class="font-bold text-slate-700 dark:text-slate-200 text-sm truncate" x-text="product.name || product.description"></h3>
                            <div class="flex justify-between items-center mt-2">
                                <span class="font-mono font-bold text-blue-600 dark:text-blue-400" x-text="'Rs. ' + Number(product.price || product.sale_rate || product.rate || 0).toFixed(2)"></span>
                                <div class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 flex items-center justify-center text-xs">
                                    <i class="fas fa-plus"></i>
                                </div>
                            </div>
                        </div>

                        <div class="absolute inset-0 bg-blue-500/10 opacity-0 group-active:opacity-100 transition duration-75"></div>
                    </div>
                </template>

            </div>
        </div>
    </div>

    <div class="w-full md:w-80 lg:w-96 bg-white dark:bg-slate-900 rounded-2xl shadow-xl flex flex-col border border-slate-200 dark:border-slate-800 md:h-full min-h-[340px]">

        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 flex justify-between items-center rounded-t-2xl">
            <div>
                <h2 class="font-bold text-lg text-slate-800 dark:text-white">Current Order</h2>
                <p class="text-xs text-slate-500">Walk-in Customer</p>
            </div>
            <button @click="clearCart" class="text-red-500 hover:text-red-700 text-xs font-bold uppercase tracking-wide bg-red-50 dark:bg-red-900/20 px-3 py-1.5 rounded-lg transition">
                <i class="fas fa-trash-alt mr-1"></i> Clear
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-2 space-y-2 custom-scrollbar relative">

            <template x-for="(item, index) in cart" :key="index">
                <div class="flex flex-col gap-1 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-transparent hover:border-blue-200 dark:hover:border-blue-900 transition group">
                    <div class="flex items-center gap-3">
                        <div class="flex flex-col items-center gap-1 bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-1">
                            <button @click="updateQty(index, 1)" class="w-6 h-6 rounded flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition text-xs">
                                <i class="fas fa-plus"></i>
                            </button>
                            <span class="font-bold text-sm text-slate-700 dark:text-white" x-text="item.qty"></span>
                            <button @click="updateQty(index, -1)" class="w-6 h-6 rounded flex items-center justify-center bg-slate-100 text-slate-500 hover:bg-red-500 hover:text-white transition text-xs">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>

                        <div class="flex-1 min-w-0">
                            <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200 truncate" x-text="item.name || item.description"></h4>
                            <p class="text-xs text-slate-500" x-text="'@ ' + Number(item.price || item.rate || 0).toFixed(2)"></p>
                        </div>

                        <div class="text-right">
                            <div class="font-bold text-slate-800 dark:text-white" x-text="'Rs. ' + ((Number(item.price || item.rate || 0)) * item.qty).toFixed(2)"></div>
                            <button @click="removeItem(index)" class="text-slate-300 hover:text-red-500 text-xs transition mt-1 opacity-0 group-hover:opacity-100">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Per-item Tax Rate Input -->
                    <div class="flex items-center gap-2 pl-9 mt-0.5">
                        <label class="text-[10px] text-slate-400 font-semibold uppercase whitespace-nowrap"><i class="fas fa-percent text-[9px] mr-0.5"></i> Tax %</label>
                        <input type="number"
                               x-model="item.tax_rate"
                               min="0" max="100" step="0.01"
                               placeholder="0"
                               class="w-20 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded px-2 py-0.5 text-xs text-right text-slate-700 dark:text-slate-300 outline-none focus:ring-1 focus:ring-blue-400">
                        <span class="text-[10px] text-slate-400">Tax: <span class="font-mono font-bold" x-text="'Rs.' + (((Number(item.price||item.rate||0)*item.qty) * (parseFloat(item.tax_rate)||0)) / 100).toFixed(2)"></span></span>
                    </div>
                </div>
            </template>

            <template x-if="cart.length === 0">
                <div class="h-full flex flex-col items-center justify-center text-slate-400 dark:text-slate-600 p-6 text-center">
                    <i class="fas fa-shopping-basket text-5xl mb-3 text-slate-200 dark:text-slate-800"></i>
                    <p class="font-bold text-sm">Cart is empty</p>
                    <p class="text-xs text-slate-400 mt-1">Scan items or click products to start sale</p>
                </div>
            </template>

        </div>

        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 rounded-b-2xl space-y-3">
            <div class="space-y-1 text-xs">
                <div class="flex justify-between text-slate-500">
                    <span>Subtotal</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300" x-text="'Rs. ' + subtotal"></span>
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Tax</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300" x-text="'Rs. ' + taxAmount"></span>
                </div>
                <div class="flex justify-between text-slate-500" x-show="parseFloat(additionalChargesTotal) > 0">
                    <span>Additional Charges</span>
                    <span class="font-bold text-slate-700 dark:text-slate-300" x-text="'Rs. ' + additionalChargesTotal"></span>
                </div>
                <div class="flex justify-between text-slate-500 items-center">
                    <span>Return/Replacement Adj.</span>
                    <input type="number" x-model="returnAdjustment" placeholder="0" class="w-24 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-right text-xs outline-none text-slate-800 dark:text-white">
                </div>
                <div class="flex justify-between text-blue-600 font-bold text-xl pt-2 border-t border-slate-200 dark:border-slate-800 mt-2">
                    <span>Total</span>
                    <span x-text="'Rs. ' + grandTotal"></span>
                </div>
            </div>

            <button id="checkout-btn" @click="openPaymentModal"
                :disabled="cart.length === 0"
                class="w-full bg-green-600 hover:bg-green-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold py-4 rounded-xl shadow-lg shadow-green-900/20 transition transform active:scale-95 flex items-center justify-center gap-2">
                <i class="fas fa-credit-card"></i>
                <span>SALES NOW</span>
            </button>
        </div>
    </div>

    <div x-show="isPaymentOpen" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="bg-white dark:bg-slate-900 w-full max-w-md max-h-[90vh] rounded-2xl shadow-2xl overflow-hidden flex flex-col animate-zoomIn">

            <div class="bg-green-600 p-4 sm:p-6 text-center flex-shrink-0">
                <h3 class="text-white text-base sm:text-lg font-bold opacity-80 uppercase tracking-wider">Amount Due</h3>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-white mt-1" x-text="'Rs. ' + grandTotal"></h1>
            </div>

            <div class="p-4 sm:p-6 space-y-4 overflow-y-auto custom-scrollbar flex-1">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Payment Method</label>
                    <select x-model="paymentMode" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2.5 font-bold text-slate-800 dark:text-white outline-none">
                        <option value="Cash">Cash</option>
                        <option value="Online">Online / Digital</option>
                        <option value="Bank">Bank Transfer</option>
                        <option value="Card">Card</option>
                        <option value="Credit">Credit / Debit</option>
                    </select>
                </div>

                <template x-if="wallets && wallets.length > 0">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Receiving Account / Wallet</label>
                        <select x-model="selectedWalletId" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-2.5 font-bold text-slate-800 dark:text-white outline-none">
                            <option value="">Default Active Wallet</option>
                            <template x-for="w in wallets" :key="w.id">
                                <option :value="w.id" x-text="w.name + ' (Bal: Rs. ' + Number(w.balance).toFixed(2) + ')'"></option>
                            </template>
                        </select>
                    </div>
                </template>

                <template x-if="availableCharges && availableCharges.length > 0">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Apply Additional Charges</label>
                        <div class="space-y-2 bg-slate-50 dark:bg-slate-950 p-3 rounded-xl border border-slate-300 dark:border-slate-700">
                            <template x-for="charge in availableCharges" :key="charge.id">
                                <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-200">
                                    <input type="checkbox" :value="charge" x-model="selectedCharges" class="rounded text-blue-600 focus:ring-blue-500">
                                    <span x-text="charge.name + ' (' + (charge.type === 'percentage' ? charge.value + '%' : 'Rs. ' + charge.value) + ')'"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Cash / Amount Received</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-slate-400 font-bold">Rs.</span>
                        <input type="number" id="cash-received" x-model="amountReceived" @keydown.enter.prevent="completeSale()" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl pl-12 pr-4 py-2.5 text-xl font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                </div>

                <div class="bg-slate-100 dark:bg-slate-800 p-3 rounded-xl flex justify-between items-center border border-slate-200 dark:border-slate-700">
                    <span class="text-xs font-bold text-slate-500 uppercase">Change Return</span>
                    <span class="text-xl font-bold text-slate-800 dark:text-white" :class="change < 0 ? 'text-red-500' : 'text-green-500'" x-text="'Rs. ' + change"></span>
                </div>

                <div class="grid grid-cols-4 gap-2">
                    <button @click="amountReceived = 500" class="py-2 bg-slate-100 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-xs font-bold hover:bg-blue-50 hover:border-blue-200 transition">500</button>
                    <button @click="amountReceived = 1000" class="py-2 bg-slate-100 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-xs font-bold hover:bg-blue-50 hover:border-blue-200 transition">1000</button>
                    <button @click="amountReceived = 5000" class="py-2 bg-slate-100 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-xs font-bold hover:bg-blue-50 hover:border-blue-200 transition">5000</button>
                    <button @click="amountReceived = Math.ceil(grandTotal)" class="py-2 bg-yellow-100 dark:bg-yellow-900/30 rounded border border-yellow-200 text-xs font-bold text-yellow-700 hover:bg-yellow-200 transition">Exact</button>
                </div>
            </div>

            <div class="p-6 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex gap-4">
                <button @click="isPaymentOpen = false" class="flex-1 py-3 rounded-xl border border-slate-300 dark:border-slate-700 font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 transition">Cancel</button>
                <button @click="completeSale" class="flex-1 py-3 rounded-xl bg-green-600 hover:bg-green-700 text-white font-bold shadow-lg transition flex justify-center items-center gap-2">
                    <i class="fas fa-check-circle"></i> Confirm Sale
                </button>
            </div>
        </div>
    </div>

    <audio id="beepSound" src="https://assets.mixkit.co/active_storage/sfx/2000/2000-preview.mp3"></audio>

</div>

<script>
    function posSystem() {
        return {
            search: '',
            category: 'all',
            isPaymentOpen: false,
            amountReceived: '',
            returnAdjustment: '',
            paymentMode: 'Cash',
            selectedWalletId: '',
            selectedCharges: [],
            cart: [],

            // Inject Laravel Products & Tax Settings & Wallets
            products: @json($items),
            taxSettings: @json($taxSettings ?? ['tax_enabled' => false, 'tax_rate' => 0.00]),
            wallets: @json($wallets ?? []),
            availableCharges: @json($availableCharges ?? []),

            get filteredProducts() {
                const q = (this.search || '').toLowerCase();
                return this.products.filter(p => {
                    const name = (p.name || p.description || '').toLowerCase();
                    const code = (p.code || p.barcode || '').toString();
                    const matchesSearch = name.includes(q) || code.includes(this.search || '');
                    const matchesCategory = this.category === 'all' || p.category === this.category;
                    return matchesSearch && matchesCategory;
                });
            },

            isService(product) {
                return product && (product.item_type === 'Service' || product.category === 'Service');
            },

            productStock(product) {
                if (!product) return 0;
                return parseFloat(product.on_hand ?? product.stock ?? product.stock_qty ?? 0);
            },

            addToCart(product) {
                if (!product) return;

                // 1. Play Sound
                const audio = document.getElementById('beepSound');
                if (audio) {
                    audio.currentTime = 0;
                    audio.play().catch(() => {});
                }

                // 2. Logic (Service items bypass stock check)
                const existing = this.cart.find(item => item.id === product.id);
                const isServ = this.isService(product);
                const availStock = this.productStock(product);

                if (existing) {
                    if (isServ || existing.qty < availStock) {
                        existing.qty++;
                    } else {
                        alert('Out of stock!');
                    }
                } else {
                    if (isServ || availStock > 0) {
                        this.cart.push({
                            ...product,
                            price: product.price || product.sale_rate || product.rate || 0,
                            name: product.name || product.description,
                            qty: 1,
                            // Initialise per-item tax_rate from the product record;
                            // falls back to global tax settings if product has no rate.
                            tax_rate: (product.tax_rate !== null && product.tax_rate !== undefined)
                                ? product.tax_rate
                                : (this.taxSettings && this.taxSettings.tax_enabled ? parseFloat(this.taxSettings.tax_rate) || 0 : 0)
                        });
                    } else {
                        alert('Out of stock!');
                    }
                }
            },

            updateQty(index, amount) {
                const item = this.cart[index];
                const product = this.products.find(p => p.id === item.id);
                const isServ = product ? this.isService(product) : (item.item_type === 'Service' || item.category === 'Service');
                const availStock = product ? this.productStock(product) : parseFloat(item.on_hand ?? item.stock ?? item.stock_qty ?? 0);

                if (item.qty + amount <= 0) {
                    this.removeItem(index);
                } else if (!isServ && item.qty + amount > availStock) {
                    alert('Cannot exceed available stock!');
                } else {
                    item.qty += amount;
                }
            },

            removeItem(index) {
                this.cart.splice(index, 1);
            },

            clearCart() {
                if (confirm('Are you sure you want to clear the cart?')) {
                    this.cart = [];
                }
            },

            get subtotal() {
                return this.cart.reduce((sum, item) => sum + (Number(item.price || item.rate || 0) * item.qty), 0).toFixed(2);
            },

            get taxAmount() {
                return this.cart.reduce((sum, item) => {
                    let rate = (item.tax_rate !== null && item.tax_rate !== undefined)
                        ? parseFloat(item.tax_rate)
                        : (this.taxSettings && this.taxSettings.tax_enabled ? parseFloat(this.taxSettings.tax_rate) || 0 : 0);
                    let lineSub = (parseFloat(item.price || item.rate || 0)) * item.qty;
                    return sum + ((lineSub * rate) / 100);
                }, 0).toFixed(2);
            },

            get additionalChargesTotal() {
                const sub = parseFloat(this.subtotal) || 0;
                return this.selectedCharges.reduce((sum, charge) => {
                    let amt = charge.type === 'percentage' ? (sub * parseFloat(charge.value)) / 100 : parseFloat(charge.value);
                    return sum + (amt || 0);
                }, 0).toFixed(2);
            },

            get grandTotal() {
                const sub = parseFloat(this.subtotal) || 0;
                const tax = parseFloat(this.taxAmount) || 0;
                const charges = parseFloat(this.additionalChargesTotal) || 0;
                const ret = parseFloat(this.returnAdjustment) || 0;
                return Math.max(0, sub + tax + charges - ret).toFixed(2);
            },

            openPaymentModal() {
                 this.amountReceived = '';
                 this.isPaymentOpen = true;
                 this.$nextTick(() => {
                     const cashInput = document.getElementById('cash-received');
                     if (cashInput) cashInput.focus();
                 });
             },

            get change() {
                const received = parseFloat(this.amountReceived) || 0;
                const total = parseFloat(this.grandTotal);
                return (received - total).toFixed(2);
            },

            async completeSale() {
                if (parseFloat(this.amountReceived) < parseFloat(this.grandTotal)) {
                    alert('Insufficient cash/amount received!');
                    return;
                }

                // 1. Prepare Data — include per-item tax_rate so backend can save it accurately
                const cartWithTax = this.cart.map(item => ({
                    ...item,
                    tax_rate: (item.tax_rate !== null && item.tax_rate !== undefined) ? parseFloat(item.tax_rate) : null
                }));
                const payload = {
                    cart: cartWithTax,
                    total: this.grandTotal,
                    amount_received: this.amountReceived,
                    return_adjustment: parseFloat(this.returnAdjustment) || 0,
                    payment_mode: this.paymentMode,
                    wallet_id: this.selectedWalletId,
                    additional_charges: this.selectedCharges.map(c => c.id)
                };

                try {
                    // 2. Send to Laravel
                    const response = await fetch('/sales/store', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    });

                    const result = await response.json();

                    if (result.success) {
                        // 3. Open Receipt in New Popup Window
                        if (result.print_url) {
                            window.open(result.print_url, '_blank', 'width=400,height=600');
                        }

                        // 4. Reset POS for next customer
                        alert('Sale Recorded! Invoice #' + result.invoice_no);
                        this.cart = [];
                        this.isPaymentOpen = false;
                        this.amountReceived = '';
                        this.selectedCharges = [];

                        window.location.reload();

                    } else {
                        let errorMsg = result.message || result.error || 'Unknown error';
                        if (result.errors) {
                            errorMsg += '\n' + Object.values(result.errors).flat().join('\n');
                        }
                        alert('Error: ' + errorMsg);
                    }

                } catch (error) {
                    console.error('Error:', error);
                    alert('System Error: Could not save sale.');
                }
            },

            handleF2(e) {
                if (this.cart.length > 0) {
                    e.preventDefault();
                    const checkoutBtn = document.getElementById('checkout-btn');
                    if (checkoutBtn) checkoutBtn.focus();
                }
            }
        }
    }
</script>

<style>
    @keyframes zoomIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .animate-zoomIn {
        animation: zoomIn 0.2s ease-out;
    }

    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>

@endsection