@extends('layouts.admin')

@section('title', 'Product List')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Product Catalog</h1>
        <a href="/items/create" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-bold">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>

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
                        <a href="{{ route('items.edit', $item->id) }}" class="text-blue-600 hover:text-blue-800 font-bold text-xs uppercase">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-8 text-center text-gray-500">No items found.</td>
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