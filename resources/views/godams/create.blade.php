@extends('layouts.admin')

@section('title', 'Add New Godam')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">

    {{-- Breadcrumb --}}
    <div class="mb-6">
        <a href="{{ route('godams.index') }}" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-855 dark:text-slate-400 dark:hover:text-white font-semibold transition text-sm">
            <i class="fas fa-arrow-left"></i> Back to Godams
        </a>
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">Add New Godam</h1>
    </div>

    {{-- Form --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-gray-200 dark:border-slate-700 overflow-hidden">
        <form action="{{ route('godams.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            {{-- Name --}}
            <div>
                <label class="block text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wide mb-1" for="name">
                    Godam Name <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="name" 
                       id="name" 
                       required
                       placeholder="e.g. Main Godam, Cold Storage, Godam 2"
                       class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                       value="{{ old('name') }}">
                @error('name')
                    <p class="text-red-550 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Location --}}
            <div>
                <label class="block text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wide mb-1" for="location">
                    Location / Address
                </label>
                <input type="text" 
                       name="location" 
                       id="location" 
                       placeholder="e.g. Basement Block B, Near main exit"
                       class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                       value="{{ old('location') }}">
                @error('location')
                    <p class="text-red-550 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Active Status --}}
            <div class="flex items-center gap-3">
                <input type="checkbox" 
                       name="is_active" 
                       id="is_active" 
                       value="1" 
                       checked
                       class="w-4.5 h-4.5 rounded text-indigo-600 border-gray-300 dark:border-slate-700 focus:ring-indigo-500">
                <label for="is_active" class="text-sm font-bold text-gray-700 dark:text-slate-300 select-none cursor-pointer">
                    This Godam is Active (Accepts purchases & transfers)
                </label>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-xs font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wide mb-1" for="notes">
                    Notes / Description
                </label>
                <textarea name="notes" 
                          id="notes" 
                          rows="4" 
                          placeholder="Add any specific storage instructions or descriptions here..."
                          class="w-full bg-slate-50 dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg px-4 py-2.5 text-gray-900 dark:text-white font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-555 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Submit actions --}}
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-150 dark:border-slate-700">
                <a href="{{ route('godams.index') }}" 
                   class="px-5 py-2.5 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-700 dark:text-slate-350 hover:bg-gray-100 dark:hover:bg-slate-700 font-bold transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-755 text-white font-bold rounded-lg shadow-md transition">
                    <i class="fas fa-save mr-1"></i> Save Godam
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
