@extends('layouts.admin')

@section('title', 'Product List')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Product Catalog</h1>
        <div class="flex gap-2">
            <a href="/items/import-preview" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded font-bold flex items-center gap-2">
                <i class="fas fa-file-excel"></i> Import with Preview
            </a>
            <a href="/items/create" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-bold">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
    </div>

    {{-- Search Bar --}}
    <form method="GET" action="{{ route('items.index') }}" class="mb-4">
        <div class="flex items-center gap-2">
            <div class="relative flex-1 max-w-md">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 dark:text-gray-500 pointer-events-none">
                    <i class="fas fa-search text-sm"></i>
                </span>
                <input
                    type="text"
                    name="search"
                    id="items-search"
                    value="{{ $search ?? '' }}"
                    placeholder="Search by name or code…"
                    autocomplete="off"
                    class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-200 dark:border-gray-600
                           bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100
                           placeholder-gray-400 dark:placeholder-gray-500
                           focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                           text-sm transition"
                >
            </div>
            <button type="submit"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg transition">
                Search
            </button>
            @if($search)
            <a href="{{ route('items.index') }}"
               class="px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600
                      text-gray-600 dark:text-gray-300 text-sm font-bold rounded-lg transition flex items-center gap-1">
                <i class="fas fa-times text-xs"></i> Clear
            </a>
            @endif
        </div>
        @if($search)
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
            Showing results for <span class="font-semibold text-gray-700 dark:text-gray-200">"{{ $search }}"</span>
            — {{ $items->total() }} {{ Str::plural('item', $items->total()) }} found
        </p>
        @endif
    </form>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="p-4 text-xs font-bold text-gray-500 uppercase">Code</th>
                    <th class="p-4 text-xs font-bold text-gray-500 uppercase">Name</th>
                    <th class="p-4 text-xs font-bold text-gray-500 uppercase">Type</th>
                    <th class="p-4 text-xs font-bold text-gray-500 uppercase">Stock</th>
                    <th class="p-4 text-xs font-bold text-gray-500 uppercase">Price</th>
                    <th class="p-4 text-xs font-bold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($items as $item)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    <td class="p-4">
                        <div class="flex flex-col">
                            <span class="text-sm font-mono font-bold text-gray-800 dark:text-gray-200">{{ $item->code }}</span>
                            @if($item->barcode_image_path)
                            <img src="{{ asset('storage/' . $item->barcode_image_path) }}" alt="Barcode" class="h-6 w-auto mt-1 rounded bg-white p-0.5 border border-gray-100">
                            @endif
                        </div>
                    </td>
                    <td class="p-4 text-sm font-bold text-gray-800 dark:text-white">{{ $item->description }}</td>
                    <td class="p-4 text-sm">
                        <span class="px-2 py-1 rounded text-xs font-bold {{ $item->item_type === 'Service' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                            {{ $item->item_type }}
                        </span>
                    </td>
                    <td class="p-4 text-sm font-bold {{ $item->on_hand <= $item->min_stock ? 'text-red-500' : 'text-green-500' }}">
                        {{ $item->on_hand }}
                    </td>
                    <td class="p-4 text-sm font-bold text-gray-800 dark:text-white">Rs. {{ number_format($item->sale_rate, 2) }}</td>
                    <td class="p-4 text-sm">
                        <div class="flex items-center gap-3">
                            <a href="{{ route('items.show', $item->id) }}" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs uppercase flex items-center gap-1">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="{{ route('items.edit', $item->id) }}" class="text-blue-600 hover:text-blue-800 font-bold text-xs uppercase flex items-center gap-1">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-8 text-center text-gray-500">
                        @if($search)
                            No items found matching <span class="font-semibold">"{{ $search }}"</span>.
                        @else
                            No items found.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection