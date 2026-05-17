@extends('layouts.admin')

@section('title', 'Edit Batch')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('items.edit', $batch->item_id) }}" class="w-10 h-10 rounded-full bg-slate-800 flex items-center justify-center text-slate-400 hover:bg-slate-700 hover:text-white transition">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-extrabold text-white">Edit Batch</h1>
            <p class="text-slate-400 text-sm">Editing <span class="font-mono text-slate-300 bg-slate-800 px-1 rounded">{{ $batch->batch_no }}</span> for <strong class="text-white">{{ $batch->item->description }}</strong></p>
        </div>
    </div>

    <form action="{{ route('batches.update', $batch->id) }}" method="POST" class="bg-slate-900 rounded-2xl border border-slate-800 p-8 shadow-xl relative overflow-hidden">
        @csrf
        <input type="hidden" name="id" value="{{ $batch->id }}">

        <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Current Quantity</label>
                <input type="number" step="0.01" name="quantity_available" value="{{ $batch->quantity_available }}" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-white font-bold text-lg focus:border-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Date Received</label>
                <input type="date" name="received_at" value="{{ $batch->received_at->format('Y-m-d') }}" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-blue-500 outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Cost Price (Per Unit)</label>
                <div class="relative">
                    <span class="absolute left-3 top-3.5 text-slate-500 font-bold">Rs.</span>
                    <input type="number" step="0.01" name="cost_price" value="{{ $batch->cost_price }}" class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-10 pr-4 py-3 text-white font-mono focus:border-blue-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-green-500 uppercase mb-1">Sale Price (Per Unit)</label>
                <div class="relative">
                    <span class="absolute left-3 top-3.5 text-green-600 font-bold">Rs.</span>
                    <input type="number" step="0.01" name="sale_price" value="{{ $batch->sale_price }}" class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-10 pr-4 py-3 text-green-400 font-bold font-mono focus:border-green-500 outline-none">
                </div>
            </div>

            <div class="md:col-span-2 pt-2 border-t border-slate-800 mt-2">
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Expiration Date (Optional)</label>
                <input type="date" name="expires_at" value="{{ $batch->expires_at ? $batch->expires_at->format('Y-m-d') : '' }}" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-slate-400 focus:border-blue-500 outline-none">
            </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-900/50 transition transform hover:-translate-y-0.5 flex justify-center items-center gap-2">
            <i class="fas fa-save"></i> Update Batch Details
        </button>

    </form>
</div>
@endsection