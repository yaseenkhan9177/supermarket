@extends('layouts.app')

@section('title', 'Stock Adjustments')

@section('content')
<div class="min-h-screen p-6 bg-slate-100 dark:bg-slate-950">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Stock Adjustments</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">View and manage all stock adjustments</p>
            </div>
            <a href="{{ route('adjustments.create') }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                New Adjustment
            </a>
        </div>

        <!-- Adjustments Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-slate-50 dark:bg-slate-950">
                        <tr class="text-left text-xs font-bold text-slate-500 uppercase tracking-wider">
                            <th class="p-4">Adjustment #</th>
                            <th class="p-4">Date</th>
                            <th class="p-4">Type</th>
                            <th class="p-4">Reference</th>
                            <th class="p-4 text-right">Items</th>
                            <th class="p-4">Created By</th>
                            <th class="p-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($adjustments as $adjustment)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors">
                            <td class="p-4">
                                <span class="font-mono font-bold text-slate-900 dark:text-white">{{ $adjustment->adjustment_number }}</span>
                            </td>
                            <td class="p-4 text-slate-700 dark:text-slate-300">
                                {{ $adjustment->date ? \Carbon\Carbon::parse($adjustment->date)->format('d M Y') : 'N/A' }}
                            </td>
                            <td class="p-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    @if($adjustment->type == 'correction') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300
                                    @elseif($adjustment->type == 'damage') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300
                                    @elseif($adjustment->type == 'loss') bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300
                                    @elseif($adjustment->type == 'transfer') bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300
                                    @else bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $adjustment->type)) }}
                                </span>
                            </td>
                            <td class="p-4 text-slate-700 dark:text-slate-300">
                                {{ $adjustment->reference ?? '-' }}
                            </td>
                            <td class="p-4 text-right font-mono text-slate-700 dark:text-slate-300">
                                {{ $adjustment->items->count() ?? 0 }}
                            </td>
                            <td class="p-4 text-slate-700 dark:text-slate-300">
                                {{ $adjustment->user->name ?? 'System' }}
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('adjustments.show', $adjustment->id) }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300" title="View">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 dark:text-slate-400">
                                <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="font-medium">No stock adjustments found</p>
                                <p class="text-sm mt-1">Create your first adjustment to get started</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($adjustments->hasPages())
            <div class="p-4 border-t border-slate-200 dark:border-slate-800">
                {{ $adjustments->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection