@extends('layouts.admin')

@section('title', 'POS Terminal')

@section('content')

<div class="h-[calc(100vh-80px)] flex flex-col md:flex-row gap-6" x-data="posSystem()">

    <div class="flex-1 flex flex-col bg-white dark:bg-slate-900 rounded-2xl shadow-xl overflow-hidden border border-slate-200 dark:border-slate-800">

        <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex gap-4 bg-slate-50 dark:bg-slate-950">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-4 top-3.5 text-slate-400"></i>
                <input type="text" x-model="search" placeholder="Scan barcode or search product..."
                    class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl pl-12 pr-4 py-3 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm">
            </div>
            <select x-model="category" class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl px-4 py-3 outline-none focus:ring-2 focus:ring-blue-500 shadow-sm font-bold text-slate-600 dark:text-slate-300">
                <option value="all">All Categories</option>
                <option value="Inventory">Inventory</option>
                <option value="Service">Services</option>
                <option value="Package">Packages</option>
            </select>
        </div>

        <div class="flex-1 overflow-y-auto p-4 bg-slate-100 dark:bg-slate-950/50 custom-scrollbar">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">

                <template x-for="product in filteredProducts" :key="product.id">
                    <div @click="addToCart(product)"
                        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-lg hover:-translate-y-1 transition cursor-pointer overflow-hidden group relative">

                        <span class="absolute top-2 right-2 text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-900 text-slate-500 border border-slate-200 dark:border-slate-700 shadow-sm">
                            <span x-text="product.stock"></span> left
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
                            <h3 class="font-bold text-slate-700 dark:text-slate-200 text-sm truncate" x-text="product.name"></h3>
                            <div class="flex justify-between items-center mt-2">
                                <span class="font-mono font-bold text-blue-600 dark:text-blue-400" x-text="'Rs. ' + Number(product.price).toFixed(2)"></span>
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

    <div class="w-full md:w-96 bg-white dark:bg-slate-900 rounded-2xl shadow-xl flex flex-col border border-slate-200 dark:border-slate-800 h-full">

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
                <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-transparent hover:border-blue-200 dark:hover:border-blue-900 transition group">
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
                        <h4 class="text-sm font-bold text-slate-700 dark:text-slate-200 truncate" x-text="item.name"></h4>
                        <p class="text-xs text-slate-500" x-text="'@ ' + Number(item.price).toFixed(2)"></p>
                    </div>

                    <div class="text-right">
                        <div class="font-bold text-slate-800 dark:text-white" x-text="'Rs. ' + (item.price * item.qty).toFixed(2)"></div>
                        <button @click="removeItem(index)" class="text-xs text-red-400 hover:text-red-600 opacity-0 group-hover:opacity-100 transition">Remove</button>
                    </div>
                </div>
            </template>

            <div x-show="cart.length === 0" class="absolute inset-0 flex flex-col items-center justify-center text-slate-400">
                <i class="fas fa-shopping-basket text-5xl mb-3 opacity-20"></i>
                <p class="text-sm font-medium">Cart is empty</p>
                <p class="text-xs opacity-60">Click items to sell</p>
            </div>

        </div>

        <div class="bg-slate-50 dark:bg-slate-950 p-6 border-t border-slate-200 dark:border-slate-800 rounded-b-2xl">
            <div class="space-y-2 mb-4 text-sm">
                <div class="flex justify-between text-slate-500">
                    <span>Subtotal</span>
                    <span class="font-bold" x-text="'Rs. ' + subtotal"></span>
                </div>
                <div class="flex justify-between text-slate-500 items-center">
                    <span>Return/Replacement Adj.</span>
                    <input type="number" x-model="returnAdjustment" placeholder="0" class="w-24 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded px-2 py-1 text-right text-xs outline-none text-slate-800 dark:text-white">
                </div>
                <div class="flex justify-between text-slate-500">
                    <span>Tax (0%)</span>
                    <span class="font-bold">Rs. 0.00</span>
                </div>
                <div class="flex justify-between text-blue-600 font-bold text-xl pt-2 border-t border-slate-200 dark:border-slate-800 mt-2">
                    <span>Total</span>
                    <span x-text="'Rs. ' + grandTotal"></span>
                </div>
            </div>

            <button @click="openPaymentModal"
                :disabled="cart.length === 0"
                class="w-full bg-green-600 hover:bg-green-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold py-4 rounded-xl shadow-lg shadow-green-900/20 transition transform active:scale-95 flex items-center justify-center gap-2">
                <i class="fas fa-credit-card"></i>
                <span>SALES NOW</span>
            </button>
        </div>
    </div>

    <div x-show="isPaymentOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm" style="display: none;">
        <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden animate-zoomIn">

            <div class="bg-green-600 p-6 text-center">
                <h3 class="text-white text-lg font-bold opacity-80 uppercase tracking-wider">Amount Due</h3>
                <h1 class="text-4xl font-extrabold text-white mt-1" x-text="'Rs. ' + grandTotal"></h1>
            </div>

            <div class="p-8 space-y-6">
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Cash Received</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-slate-400 font-bold">Rs.</span>
                        <input type="number" x-model="amountReceived" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl pl-12 pr-4 py-3 text-xl font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-green-500 outline-none">
                    </div>
                </div>

                <div class="bg-slate-100 dark:bg-slate-800 p-4 rounded-xl flex justify-between items-center border border-slate-200 dark:border-slate-700">
                    <span class="text-sm font-bold text-slate-500 uppercase">Change Return</span>
                    <span class="text-2xl font-bold text-slate-800 dark:text-white" :class="change < 0 ? 'text-red-500' : 'text-green-500'" x-text="'Rs. ' + change"></span>
                </div>

                <div class="grid grid-cols-4 gap-2">
                    <button @click="amountReceived = 500" class="py-2 bg-slate-100 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-sm font-bold hover:bg-blue-50 hover:border-blue-200 transition">500</button>
                    <button @click="amountReceived = 1000" class="py-2 bg-slate-100 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-sm font-bold hover:bg-blue-50 hover:border-blue-200 transition">1000</button>
                    <button @click="amountReceived = 5000" class="py-2 bg-slate-100 dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-700 text-sm font-bold hover:bg-blue-50 hover:border-blue-200 transition">5000</button>
                    <button @click="amountReceived = Math.ceil(grandTotal)" class="py-2 bg-yellow-100 dark:bg-yellow-900/30 rounded border border-yellow-200 text-sm font-bold text-yellow-700 hover:bg-yellow-200 transition">Exact</button>
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
            cart: [],

            // Inject Laravel Products Here
            products: @json($items),

            get filteredProducts() {
                return this.products.filter(p => {
                    const matchesSearch = p.name.toLowerCase().includes(this.search.toLowerCase()) ||
                        (p.code && p.code.toString().includes(this.search));
                    const matchesCategory = this.category === 'all' || p.category === this.category;
                    return matchesSearch && matchesCategory;
                });
            },

            addToCart(product) {
                // 1. Play Sound
                const audio = document.getElementById('beepSound');
                audio.currentTime = 0;
                audio.play();

                // 2. Logic
                const existing = this.cart.find(item => item.id === product.id);
                if (existing) {
                    if (existing.qty < product.stock) {
                        existing.qty++;
                    } else {
                        alert('Out of stock!');
                    }
                } else {
                    if (product.stock > 0) {
                        this.cart.push({
                            ...product,
                            qty: 1
                        });
                    } else {
                        alert('Out of stock!');
                    }
                }
                this.search = ''; // Clear search after add (optional, good for scanners)
            },

            updateQty(index, amount) {
                const item = this.cart[index];
                const product = this.products.find(p => p.id === item.id);

                if (item.qty + amount <= 0) {
                    this.removeItem(index);
                } else if (item.qty + amount > product.stock) {
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
                return this.cart.reduce((sum, item) => sum + (Number(item.price) * item.qty), 0).toFixed(2);
            },

            get grandTotal() {
                const sub = parseFloat(this.subtotal) || 0;
                const ret = parseFloat(this.returnAdjustment) || 0;
                return Math.max(0, sub - ret).toFixed(2);
            },

            openPaymentModal() {
                this.amountReceived = '';
                this.isPaymentOpen = true;
            },

            get change() {
                const received = parseFloat(this.amountReceived) || 0;
                const total = parseFloat(this.grandTotal);
                return (received - total).toFixed(2);
            },

            async completeSale() {
                if (parseFloat(this.amountReceived) < parseFloat(this.grandTotal)) {
                    alert('Insufficient cash received!');
                    return;
                }

                // 1. Prepare Data
                const payload = {
                    cart: this.cart,
                    total: this.grandTotal,
                    amount_received: this.amountReceived,
                    return_adjustment: parseFloat(this.returnAdjustment) || 0
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
                            const PrintWindow = window.open(result.print_url, '_blank', 'width=400,height=600');
                        }

                        // 4. Reset POS for next customer
                        alert('Sale Recorded! Invoice #' + result.invoice_no);
                        this.cart = [];
                        this.isPaymentOpen = false;
                        this.amountReceived = '';

                        // Optional: Reload to update stock visuals if you don't use livewire/sockets
                        window.location.reload();

                    } else {
                        // Handle Laravel Validation Errors or Custom Error Messages
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

    /* Hide number input spinners */
    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
</style>

@endsection