@extends('layouts.app')

@section('title', 'Product Search Test')

@section('content')
<div class="min-h-screen p-6 bg-slate-100 dark:bg-slate-950">
    <div class="max-w-6xl mx-auto">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Smart Product Search - Test Page</h1>
            <p class="text-slate-600 dark:text-slate-400 mt-2">Test the reusable product search component with auto-add/increment logic</p>
        </div>

        <div
            x-data="{
                cart: [],
                
                // Handle product selection from search component
                handleProductSelected(event) {
                    const product = event.detail;
                    console.log('Product selected:', product);
                    
                    // Check if product already exists in cart
                    const existingIndex = this.cart.findIndex(item => item.id === product.id);
                    
                    if (existingIndex >= 0) {
                        // Product EXISTS: Increment quantity
                        this.cart[existingIndex].qty += 1;
                        this.cart[existingIndex].total = this.cart[existingIndex].qty * this.cart[existingIndex].rate;
                        
                        // Flash highlight
                        this.flashRow(existingIndex);
                        
                        // Beep
                        this.playBeep();
                        
                        console.log('Quantity incremented:', this.cart[existingIndex]);
                    } else {
                        // Product NOT EXISTS: Add new row
                        this.cart.push({
                            id: product.id,
                            name: product.name || product.description,
                            code: product.barcode || product.code,
                            rate: product.sale_rate || product.rate,
                            qty: 1,
                            total: product.sale_rate || product.rate
                        });
                        
                        // Beep
                        this.playBeep();
                        
                        console.log('Product added:', this.cart[this.cart.length - 1]);
                    }
                },
                
                // Handle product not found
                handleProductNotFound(event) {
                    alert('Product not found: ' + event.detail.query);
                    console.log('Product not found:', event.detail);
                },
                
                // Handle out of stock
                handleOutOfStock(event) {
                    alert('Product out of stock: ' + event.detail.name);
                    console.log('Out of stock:', event.detail);
                },
                
                // Flash row on update
                flashRow(index) {
                    const row = document.querySelector(`#cart-row-${index}`);
                    if (row) {
                        row.classList.add('bg-green-200', 'dark:bg-green-900/50');
                        setTimeout(() => {
                            row.classList.remove('bg-green-200', 'dark:bg-green-900/50');
                        }, 500);
                    }
                },
                
                // Remove from cart
                removeItem(index) {
                    this.cart.splice(index, 1);
                },
                
                // Play beep sound
                playBeep() {
                    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                    const oscillator = audioContext.createOscillator();
                    const gainNode = audioContext.createGain();
                    
                    oscillator.connect(gainNode);
                    gainNode.connect(audioContext.destination);
                    
                    oscillator.frequency.value = 880;
                    oscillator.type = 'sine';
                    
                    gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                    gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
                    
                    oscillator.start(audioContext.currentTime);
                    oscillator.stop(audioContext.currentTime + 0.1);
                },
                
                // Get cart total
                get cartTotal() {
                    return this.cart.reduce((sum, item) => sum + item.total, 0).toFixed(2);
                }
            }"
            @product-selected.window="handleProductSelected($event)"
            @product-not-found.window="handleProductNotFound($event)"
            @out-of-stock.window="handleOutOfStock($event)"
            class="space-y-6">

            <!-- Search Component -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl p-6 border border-slate-200 dark:border-slate-800">
                <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-4">Search Products</h2>
                <x-smart-product-search placeholder="Scan Barcode or Type Product Name..." />

                <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <h3 class="font-bold text-blue-900 dark:text-blue-300 mb-2">How it works:</h3>
                    <ul class="text-sm text-blue-800 dark:text-blue-400 space-y-1">
                        <li>✅ Scan barcode → Auto-adds to cart (or increments qty if exists)</li>
                        <li>✅ Type product name → Dropdown appears → Arrow keys to select</li>
                        <li>✅ Press Enter to select highlighted item</li>
                        <li>✅ Duplicate prevention: Same product increments quantity</li>
                        <li>✅ Out-of-stock items are skipped in navigation</li>
                    </ul>
                </div>
            </div>

            <!-- Cart Table -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="p-6 border-b border-slate-200 dark:border-slate-800">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Cart (Auto-Add/Increment Demo)</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-slate-50 dark:bg-slate-950">
                            <tr class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                                <th class="p-4">Product</th>
                                <th class="p-4 text-right">Rate</th>
                                <th class="p-4 text-center">Qty</th>
                                <th class="p-4 text-right">Total</th>
                                <th class="p-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            <template x-for="(item, index) in cart" :key="item.id">
                                <tr
                                    :id="'cart-row-' + index"
                                    class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                    <td class="p-4">
                                        <div class="font-bold text-slate-900 dark:text-white" x-text="item.name"></div>
                                        <div class="text-xs text-slate-500 font-mono" x-text="item.code"></div>
                                    </td>
                                    <td class="p-4 text-right font-mono text-slate-700 dark:text-slate-300" x-text="item.rate.toFixed(2)"></td>
                                    <td class="p-4 text-center">
                                        <span class="inline-flex items-center justify-center w-12 h-8 bg-blue-100 dark:bg-blue-900/30 text-blue-900 dark:text-blue-300 font-bold rounded" x-text="item.qty"></span>
                                    </td>
                                    <td class="p-4 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400" x-text="item.total.toFixed(2)"></td>
                                    <td class="p-4 text-center">
                                        <button @click="removeItem(index)" class="text-red-500 hover:text-red-700 dark:hover:text-red-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </td>
                                </tr>
                            </template>

                            <tr x-show="cart.length === 0">
                                <td colspan="5" class="p-8 text-center text-slate-500 dark:text-slate-400">
                                    <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="font-medium">Cart is empty</p>
                                    <p class="text-sm">Search for products above to add them</p>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800">
                            <tr>
                                <td colspan="3" class="p-4 text-right font-bold text-slate-900 dark:text-white">Total:</td>
                                <td class="p-4 text-right font-mono font-bold text-2xl text-emerald-600 dark:text-emerald-400" x-text="cartTotal"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection