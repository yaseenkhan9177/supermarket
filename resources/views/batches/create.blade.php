@extends('layouts.admin')

@section('title', 'Add New Stock')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('items.edit', $item->id) }}" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-slate-700 hover:text-white transition">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-white">Add Stock</h1>
            <p class="text-slate-400 text-sm">Adding new batch for <strong class="text-white">{{ $item->description }}</strong></p>
        </div>
    </div>

    <form action="{{ route('batches.store') }}" method="POST" class="bg-slate-900 rounded-2xl border border-slate-800 p-8 shadow-xl relative overflow-hidden">
        @csrf
        <input type="hidden" name="item_id" value="{{ $item->id }}">

        <div class="absolute top-0 left-0 w-1 h-full bg-green-500"></div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Batch Number <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" name="batch_no" value="{{ 'BATCH-' . date('Ymd') . '-' . rand(100, 999) }}" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-white font-mono tracking-wider focus:border-green-500 outline-none">
                    <i class="fas fa-barcode absolute right-4 top-3.5 text-slate-600"></i>
                </div>
                <p class="text-[10px] text-slate-500 mt-1">Auto-generated ID. You can change this to match supplier invoice #.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Quantity <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="quantity_available" placeholder="0" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-white font-bold text-lg focus:border-green-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Date Received <span class="text-red-500">*</span></label>
                <input type="date" name="received_at" value="{{ date('Y-m-d') }}" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-green-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Cost Price (Per Unit) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-3.5 text-slate-500 font-bold">Rs.</span>
                    <input type="number" step="0.01" name="cost_price" value="{{ $item->cost_rate ?? 0 }}" class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-10 pr-4 py-3 text-white font-mono focus:border-green-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-green-500 uppercase mb-1">Sale Price (Per Unit) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-3.5 text-green-600 font-bold">Rs.</span>
                    <input type="number" step="0.01" name="sale_price" value="{{ $item->sale_rate ?? 0 }}" class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-10 pr-4 py-3 text-green-400 font-bold font-mono focus:border-green-500 outline-none">
                </div>
            </div>

            <div class="md:col-span-2 pt-2 border-t border-slate-800 mt-2">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Expiration Date (Optional)</label>
                <input type="date" name="expires_at" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-slate-400 focus:border-green-500 outline-none">
            </div>
        </div>

        <button type="submit" class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-green-900/50 transition transform hover:-translate-y-0.5 flex justify-center items-center gap-2">
            <i class="fas fa-plus-circle"></i> Add to Inventory
        </button>

    </form>
</div>
@endsection