@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto" x-data="{
    showModal: false,
    editMode: false,
    form: { id: null, name: '' }
}">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-800 dark:text-white">Supplier Categories</h1>
            <p class="text-slate-500 text-sm">Organize your suppliers into categories.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('suppliers.index') }}"
               class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-sm transition flex items-center gap-2">
                <i class="fas fa-arrow-left"></i> Back to Suppliers
            </a>
            <button @click="editMode = false; form = {id: null, name: ''}; showModal = true"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg flex items-center gap-2 text-sm transition">
                <i class="fas fa-plus"></i> Add Category
            </button>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-6 flex items-center gap-2">
            <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Categories Table --}}
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 dark:bg-slate-950 text-slate-500 uppercase font-bold text-xs">
                <tr>
                    <th class="p-4">#</th>
                    <th class="p-4">Category Name</th>
                    <th class="p-4 text-center">Suppliers</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($categories as $i => $cat)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                    <td class="p-4 text-slate-400 font-mono text-xs">{{ $i + 1 }}</td>
                    <td class="p-4">
                        <span class="font-bold text-slate-800 dark:text-white">{{ $cat->name }}</span>
                    </td>
                    <td class="p-4 text-center">
                        <span class="inline-block bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-0.5 rounded-full">
                            {{ $cat->suppliers_count }}
                        </span>
                    </td>
                    <td class="p-4 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <button
                                @click="editMode = true; form = { id: {{ $cat->id }}, name: '{{ addslashes($cat->name) }}' }; showModal = true"
                                class="text-blue-500 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-2.5 py-1.5 rounded-lg text-xs font-bold transition">
                                <i class="fas fa-edit"></i>
                            </button>
                            @if($cat->suppliers_count == 0)
                            <form method="POST" action="{{ route('supplier-categories.destroy', $cat->id) }}" class="inline"
                                  onsubmit="return confirm('Delete this category?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 px-2.5 py-1.5 rounded-lg text-xs font-bold transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-8 text-center text-slate-400">
                        <i class="fas fa-folder-open text-3xl mb-2 block"></i>
                        No categories yet. Click "Add Category" to create one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Add / Edit Modal --}}
    <div x-show="showModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white dark:bg-slate-900 w-full max-w-sm rounded-2xl p-6 shadow-2xl border border-slate-200 dark:border-slate-700">
            <h2 class="text-xl font-bold text-slate-800 dark:text-white mb-5"
                x-text="editMode ? 'Edit Category' : 'Add New Category'"></h2>

            <form method="POST"
                  :action="editMode ? '/supplier-categories/' + form.id : '{{ route('supplier-categories.store') }}'">
                @csrf
                <template x-if="editMode">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="mb-5">
                    <label class="text-xs font-bold text-slate-500 uppercase tracking-wide block mb-1">Category Name *</label>
                    <input type="text" name="name" x-model="form.name" required
                           class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg p-2.5 text-slate-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="e.g. Dairy, Beverages, FMCG">
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="showModal = false"
                            class="flex-1 py-2.5 rounded-xl border border-slate-300 dark:border-slate-600 text-slate-500 font-bold text-sm hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="submit"
                            class="flex-1 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition shadow-md">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
