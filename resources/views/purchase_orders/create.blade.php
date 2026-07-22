@extends('layouts.admin')

@section('title', 'Create Purchase Order')

@section('content')
<div class="max-w-5xl mx-auto pb-16" x-data="createPoForm()">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="flex items-center gap-2 text-sm text-slate-400 mb-1">
                <a href="{{ route('dashboard') }}" class="hover:text-slate-200 flex items-center gap-1">
                    <i class="fas fa-home text-xs"></i> Dashboard
                </a>
                <span>/</span>
                <a href="{{ route('purchase-orders.index') }}" class="hover:text-slate-200 flex items-center gap-1">
                    Purchase Orders
                </a>
            </div>
            <h1 class="text-3xl font-bold text-slate-800 dark:text-white">Create Purchase Order</h1>
        </div>
        <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-xl flex items-center gap-1.5 transition-transform hover:scale-105">
            <i class="fas fa-home"></i> Dashboard
        </a>
    </div>

    <form method="POST" action="{{ route('purchase-orders.store') }}" class="space-y-6">
        @csrf

        {{-- Supplier & Order Info Card --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-6 border border-slate-200 dark:border-slate-700/60 shadow-sm grid grid-cols-1 md:grid-cols-3 gap-5">
            {{-- Supplier --}}
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Supplier <span class="text-red-500">*</span></label>
                <select name="supplier_id" required class="w-full text-sm font-semibold p-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white">
                    <option value="">Select Supplier...</option>
                    @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}">{{ $sup->name }} ({{ $sup->code }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Expected Date --}}
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Expected Delivery Date</label>
                <input type="date" name="expected_date" class="w-full text-sm font-medium p-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white">
            </div>

            {{-- Save Status --}}
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Initial Status</label>
                <select name="status" class="w-full text-sm font-semibold p-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white">
                    <option value="draft">Draft (Editable)</option>
                    <option value="sent">Sent to Supplier</option>
                </select>
            </div>

            {{-- Note --}}
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Notes / Instructions</label>
                <textarea name="note" rows="2" placeholder="Delivery instructions, reference terms, payment terms..." class="w-full text-sm p-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white"></textarea>
            </div>
        </div>

        {{-- Line Items Card --}}
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl p-6 border border-slate-200 dark:border-slate-700/60 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-slate-800 dark:text-white text-base">Order Line Items</h3>
                <button type="button" @click="addRow()" class="px-3 py-1.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:text-indigo-400 rounded-xl text-xs font-bold transition-colors">
                    <i class="fas fa-plus mr-1"></i> Add Item Row
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 text-[11px] font-bold text-slate-400 uppercase">
                            <th class="py-3 px-3">Item / Product</th>
                            <th class="py-3 px-3 w-32">Qty</th>
                            <th class="py-3 px-3 w-36">Unit Cost (Rs.)</th>
                            <th class="py-3 px-3 w-36 text-right">Line Total (Rs.)</th>
                            <th class="py-3 px-3 w-12 text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                        <template x-for="(row, index) in rows" :key="index">
                            <tr>
                                <td class="py-3 px-3">
                                    <select :name="'items[' + index + '][item_id]'" x-model="row.item_id" @change="onItemSelect(row)" required class="w-full text-xs font-semibold p-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white">
                                        <option value="">Select Item...</option>
                                        @foreach($items as $item)
                                        <option value="{{ $item->id }}" data-cost="{{ $item->cost_rate }}">{{ $item->description ?? $item->name }} ({{ $item->code }}) — Cost: Rs.{{ $item->cost_rate }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="py-3 px-3">
                                    <input type="number" step="0.01" min="0.01" :name="'items[' + index + '][qty]'" x-model.number="row.qty" required class="w-full text-xs font-semibold p-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white">
                                </td>
                                <td class="py-3 px-3">
                                    <input type="number" step="0.01" min="0" :name="'items[' + index + '][unit_cost]'" x-model.number="row.unit_cost" required class="w-full text-xs font-semibold p-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-white">
                                </td>
                                <td class="py-3 px-3 text-right font-bold text-slate-800 dark:text-white">
                                    Rs. <span x-text="(row.qty * row.unit_cost).toFixed(2)"></span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <button type="button" @click="removeRow(index)" x-show="rows.length > 1" class="text-red-500 hover:text-red-700 p-1 text-xs">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Subtotal Summary --}}
            <div class="mt-6 pt-4 border-t border-slate-100 dark:border-slate-700 flex justify-end">
                <div class="text-right">
                    <span class="text-xs font-bold text-slate-400 uppercase">Estimated PO Subtotal</span>
                    <h3 class="text-2xl font-black text-indigo-600 dark:text-indigo-400">Rs. <span x-text="calculateSubtotal().toFixed(2)"></span></h3>
                </div>
            </div>
        </div>

        {{-- Submit Action Bar --}}
        <div class="flex justify-end gap-3">
            <a href="{{ route('purchase-orders.index') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-200 rounded-xl text-sm font-semibold">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold shadow-lg shadow-indigo-500/20">Save Purchase Order</button>
        </div>
    </form>
</div>

<script>
function createPoForm() {
    return {
        rows: [
            { item_id: '', qty: 1, unit_cost: 0 }
        ],
        addRow() {
            this.rows.push({ item_id: '', qty: 1, unit_cost: 0 });
        },
        removeRow(index) {
            if (this.rows.length > 1) {
                this.rows.splice(index, 1);
            }
        },
        onItemSelect(row) {
            const selectEl = event.target;
            const opt = selectEl.options[selectEl.selectedIndex];
            const cost = opt.getAttribute('data-cost');
            if (cost) {
                row.unit_cost = parseFloat(cost) || 0;
            }
        },
        calculateSubtotal() {
            return this.rows.reduce((sum, r) => sum + (parseFloat(r.qty || 0) * parseFloat(r.unit_cost || 0)), 0);
        }
    }
}
</script>
@endsection