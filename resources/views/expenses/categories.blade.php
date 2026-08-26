@extends('layouts.admin')

@section('title', 'Expense Categories')

@section('content')
<div class="max-w-5xl mx-auto pb-16" x-data="categoryManager()">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('expenses.index') }}" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 transition-colors">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight flex items-center gap-2">
                    <i class="fas fa-tags text-amber-500"></i> Expense Categories
                </h1>
                <p class="text-slate-500 dark:text-slate-400 text-sm mt-0.5">Manage categories for expense classification</p>
            </div>
        </div>
        <button @click="showAdd = !showAdd"
                class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl flex items-center gap-2 transition-colors shadow-sm">
            <i class="fas fa-plus"></i> Add Category
        </button>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="mb-5 p-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl flex items-start gap-3">
        <i class="fas fa-check-circle text-emerald-500 mt-0.5"></i>
        <p class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-5 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800/60 rounded-2xl flex items-start gap-3">
        <i class="fas fa-exclamation-circle text-red-500 mt-0.5"></i>
        <p class="text-sm font-semibold text-red-800 dark:text-red-300">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Add Category Panel --}}
    <div x-show="showAdd" x-transition class="mb-6">
        <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700/60 bg-amber-50/50 dark:bg-amber-900/10">
                <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200 flex items-center gap-2">
                    <i class="fas fa-plus text-amber-500"></i> New Category
                </h3>
            </div>
            <form method="POST" action="{{ route('expense-categories.store') }}" class="p-6">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">Category Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" placeholder="e.g. Electricity" required
                               class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">Short Code</label>
                        <input type="text" name="code" placeholder="e.g. ELEC"
                               class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">Description</label>
                        <input type="text" name="description" placeholder="Optional description"
                               class="w-full text-sm px-3 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 mt-4">
                    <button type="button" @click="showAdd = false"
                            class="px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-600 dark:text-slate-400 text-xs font-semibold rounded-xl transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition-colors shadow-sm">
                        <i class="fas fa-save mr-1"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Categories Table --}}
    <div class="bg-white dark:bg-slate-800/90 rounded-2xl border border-slate-200 dark:border-slate-700/60 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700/60">
            <h3 class="text-sm font-bold text-slate-700 dark:text-slate-200">
                All Categories <span class="text-slate-400 font-normal">({{ $categories->total() }} total)</span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="text-left px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Name</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Code</th>
                        <th class="text-left px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Description</th>
                        <th class="text-center px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Expenses</th>
                        <th class="text-center px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="text-right px-5 py-3 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/40">
                    @foreach($categories as $category)
                    <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-700/20 transition-colors"
                        x-show="!(editingId === {{ $category->id }})">
                        <td class="px-5 py-3.5 text-xs font-bold text-slate-700 dark:text-slate-300">{{ $category->name }}</td>
                        <td class="px-5 py-3.5">
                            @if($category->code)
                            <span class="inline-flex px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 font-mono">{{ $category->code }}</span>
                            @else
                            <span class="text-slate-300 dark:text-slate-600">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5 text-xs text-slate-500 dark:text-slate-400 max-w-48 truncate">{{ $category->description ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-center text-xs font-bold text-slate-700 dark:text-slate-300">{{ $category->expenses_count }}</td>
                        <td class="px-5 py-3.5 text-center">
                            @if($category->is_active)
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400">Active</span>
                            @else
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">Inactive</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center justify-end gap-1.5">
                                <button @click="editingId = {{ $category->id }}"
                                        class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-900/30 hover:bg-amber-100 text-amber-600 dark:text-amber-400 transition-colors"
                                        title="Edit">
                                    <i class="fas fa-pencil text-xs"></i>
                                </button>
                                @if($category->expenses_count === 0)
                                <form method="POST" action="{{ route('expense-categories.destroy', $category->id) }}"
                                      onsubmit="return confirm('Delete category {{ $category->name }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="p-1.5 rounded-lg bg-red-50 dark:bg-red-900/30 hover:bg-red-100 text-red-600 dark:text-red-400 transition-colors"
                                            title="Delete">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </form>
                                @else
                                <span class="p-1.5 text-slate-300 dark:text-slate-600 cursor-not-allowed" title="Cannot delete — has {{ $category->expenses_count }} expense(s)">
                                    <i class="fas fa-trash text-xs"></i>
                                </span>
                                @endif
                            </div>
                        </td>
                    </tr>

                    {{-- Inline Edit Row --}}
                    <tr x-show="editingId === {{ $category->id }}" x-transition
                        class="bg-amber-50/50 dark:bg-amber-900/10">
                        <td colspan="6" class="px-5 py-4">
                            <form method="POST" action="{{ route('expense-categories.update', $category->id) }}">
                                @csrf @method('PUT')
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                    <div>
                                        <input type="text" name="name" value="{{ $category->name }}" required placeholder="Name"
                                               class="w-full text-xs px-3 py-2 bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                    </div>
                                    <div>
                                        <input type="text" name="code" value="{{ $category->code }}" placeholder="Code"
                                               class="w-full text-xs px-3 py-2 bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                    </div>
                                    <div>
                                        <input type="text" name="description" value="{{ $category->description }}" placeholder="Description"
                                               class="w-full text-xs px-3 py-2 bg-white dark:bg-slate-900 border border-amber-300 dark:border-amber-700 rounded-xl text-slate-800 dark:text-slate-200 focus:ring-2 focus:ring-amber-500 focus:outline-none">
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <label class="flex items-center gap-1.5 text-xs font-semibold text-slate-600 dark:text-slate-400">
                                            <input type="checkbox" name="is_active" value="1" {{ $category->is_active ? 'checked' : '' }} class="rounded">
                                            Active
                                        </label>
                                        <button type="submit"
                                                class="px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition-colors">
                                            Save
                                        </button>
                                        <button type="button" @click="editingId = null"
                                                class="px-3 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 text-slate-600 dark:text-slate-400 text-xs font-semibold rounded-xl transition-colors">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 dark:border-slate-700/60">
            {{ $categories->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function categoryManager() {
    return { showAdd: false, editingId: null };
}
</script>
@endsection
