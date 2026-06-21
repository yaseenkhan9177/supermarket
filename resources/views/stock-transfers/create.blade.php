@extends('layouts.admin')

@section('title', 'New Stock Transfer')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Breadcrumb --}}
    <div class="mb-6">
        <a href="{{ route('stock-transfers.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-855 dark:text-slate-400 dark:hover:text-white font-semibold transition text-sm">
            <i class="fas fa-arrow-left"></i> Back to Transfers
        </a>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">Initiate Stock Transfer</h1>
    </div>

    {{-- Session alerts --}}
    @if(session('error'))
    <div class="bg-red-50 border border-red-250 text-red-800 rounded-xl p-4 mb-6 shadow-sm flex items-center gap-3">
        <i class="fas fa-exclamation-circle text-lg text-red-650"></i>
        <div class="font-semibold">{{ session('error') }}</div>
    </div>
    @endif

    {{-- Transfer Form Card --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-gray-200 dark:border-slate-700 overflow-hidden" x-data="transferForm()">
        <form action="{{ route('stock-transfers.store') }}" method="POST" class="p-6 space-y-6" @submit="onSubmit">
            @csrf

            {{-- 1. Select Item --}}
            <div>
                <label class="block text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wide mb-1" for="item_id">
                    Select Item <span class="text-red-500">*</span>
                </label>
                <select name="item_id" 
                        id="item_id" 
                        required
                        x-model="itemId"
                        @change="fetchStock()"
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg px-4 py-2.5 text-gray-950 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    <option value="">— Select Product —</option>
                    @foreach($items as $item)
                        <option value="{{ $item->id }}">{{ $item->code }} — {{ $item->description }} (Shop Floor Stock: {{ (float)$item->on_hand }})</option>
                    @endforeach
                </select>
                @error('item_id')
                    <p class="text-red-550 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- 2. From & To Locations --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Source --}}
                <div>
                    <label class="block text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wide mb-1" for="from_godam_id">
                        From Location (Source) <span class="text-red-500">*</span>
                    </label>
                    <select name="from_godam_id" 
                            id="from_godam_id"
                            x-model="fromGodamId"
                            @change="fetchStock()"
                            class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg px-4 py-2.5 text-gray-950 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        <option value="0">🏠 — Shop Floor —</option>
                        @foreach($godams as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                    @error('from_godam_id')
                        <p class="text-red-550 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    {{-- Live Stock feedback --}}
                    <div class="mt-2" x-show="itemId">
                        <div class="text-xs font-semibold py-1 px-3 rounded-lg inline-flex items-center gap-1.5"
                             :class="isCheckingStock ? 'bg-gray-150 text-gray-600 dark:bg-slate-750 dark:text-slate-400' : (availableStock > 0 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/20 dark:text-emerald-450' : 'bg-red-50 text-red-650 dark:bg-red-950/20 dark:text-red-400')">
                            <i class="fas" :class="isCheckingStock ? 'fa-spinner fa-spin' : (availableStock > 0 ? 'fa-check-circle' : 'fa-exclamation-triangle')"></i>
                            <span x-show="isCheckingStock">Checking source stock...</span>
                            <span x-show="!isCheckingStock">
                                Available Stock: <strong class="font-mono" x-text="availableStock"></strong> units
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Destination --}}
                <div>
                    <label class="block text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wide mb-1" for="to_godam_id">
                        To Location (Destination) <span class="text-red-500">*</span>
                    </label>
                    <select name="to_godam_id" 
                            id="to_godam_id"
                            x-model="toGodamId"
                            class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg px-4 py-2.5 text-gray-950 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        <option value="0">🏠 — Shop Floor —</option>
                        @foreach($godams as $g)
                            <option value="{{ $g->id }}">{{ $g->name }}</option>
                        @endforeach
                    </select>
                    @error('to_godam_id')
                        <p class="text-red-550 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 3. Quantity & Date --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                {{-- Transfer Qty --}}
                <div>
                    <label class="block text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wide mb-1" for="quantity">
                        Transfer Quantity <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           step="0.01" 
                           name="quantity" 
                           id="quantity" 
                           required
                           min="0.01"
                           x-model="qty"
                           placeholder="0.00"
                           class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg px-4 py-2.5 text-gray-950 dark:text-white font-bold focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    
                    {{-- Warning message --}}
                    <p x-show="qty > availableStock && !isCheckingStock" 
                       class="text-red-550 text-xs font-semibold mt-1 flex items-center gap-1">
                        <i class="fas fa-exclamation-triangle"></i> Warning: Quantity exceeds available stock!
                    </p>
                    
                    @error('quantity')
                        <p class="text-red-550 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Date --}}
                <div>
                    <label class="block text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wide mb-1" for="transfer_date">
                        Transfer Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           name="transfer_date" 
                           id="transfer_date" 
                           required
                           value="{{ date('Y-m-d') }}"
                           class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg px-4 py-2.5 text-gray-950 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    @error('transfer_date')
                        <p class="text-red-550 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wide mb-1" for="notes">
                    Memo / Notes
                </label>
                <textarea name="notes" 
                          id="notes" 
                          rows="3" 
                          placeholder="Explain why this transfer is being initiated (optional)..."
                          class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg px-4 py-2.5 text-gray-950 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"></textarea>
                @error('notes')
                    <p class="text-red-550 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Form Submit Actions --}}
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-150 dark:border-slate-700">
                <a href="{{ route('stock-transfers.index') }}" 
                   class="px-5 py-2.5 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-700 dark:text-slate-350 hover:bg-gray-100 dark:hover:bg-slate-700 font-bold transition">
                    Cancel
                </a>
                <button type="submit" 
                        :disabled="qty <= 0 || qty > availableStock || itemId == '' || fromGodamId == toGodamId"
                        :class="(qty > 0 && qty <= availableStock && itemId != '' && fromGodamId != toGodamId) ? 'bg-indigo-600 hover:bg-indigo-700 shadow-md' : 'bg-gray-400 cursor-not-allowed'"
                        class="px-6 py-2.5 text-white font-bold rounded-lg transition">
                    <i class="fas fa-exchange-alt mr-1"></i> Complete Transfer
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function transferForm() {
        return {
            itemId: '{{ $prefilledItemId ?: '' }}',
            fromGodamId: '{{ $prefilledFromGodamId ?: '0' }}',
            toGodamId: '0',
            availableStock: 0,
            qty: '',
            isCheckingStock: false,

            init() {
                if (this.itemId) {
                    this.fetchStock();
                }
            },

            async fetchStock() {
                if (!this.itemId) {
                    this.availableStock = 0;
                    return;
                }
                this.isCheckingStock = true;
                try {
                    const res = await fetch(`/api/stock-check?item_id=${this.itemId}&godam_id=${this.fromGodamId}`);
                    const data = await res.json();
                    this.availableStock = parseFloat(data.available_qty || 0);
                } catch (e) {
                    console.error('Failed to fetch stock', e);
                    this.availableStock = 0;
                } finally {
                    this.isCheckingStock = false;
                }
            },

            onSubmit(e) {
                if (this.fromGodamId === this.toGodamId) {
                    e.preventDefault();
                    alert('Source and destination locations must be different.');
                    return false;
                }
                const transferQty = parseFloat(this.qty || 0);
                if (transferQty <= 0 || transferQty > this.availableStock) {
                    e.preventDefault();
                    alert('Invalid transfer quantity.');
                    return false;
                }
            }
        };
    }
</script>
@endsection
