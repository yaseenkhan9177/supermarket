@extends('layouts.admin')

@section('title', 'Edit Product Master')

@section('content')
<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- LEFT: Master Data Form (Editable) -->
    <div class="lg:col-span-2">
        <form action="{{ route('items.update', $item->id) }}" method="POST" enctype="multipart/form-data"
            class="bg-slate-900 rounded-2xl shadow-xl border border-slate-800 p-6 relative overflow-hidden"
            x-data="imagePreview('{{ $item->image_path ? asset('storage/'.$item->image_path) : '' }}')">
            @csrf
            <!-- Use route logic to spoof PUT if needed, though Laravel supports POST for updates too if route is defined that way. 
                 Since route is defined as POST in web.php (implied by typical resource or manual def), sticking to POST or adding @method('PUT') based on web.php. 
                 User web.php defines: Route::post('/items/{id}/update', ...). So POST is correct. -->

            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-purple-500"></div>

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <i class="fas fa-edit text-blue-500"></i> Edit Master Details
                </h2>
                <div class="flex gap-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-400 border border-slate-700">
                        ID: {{ $item->id }}
                    </span>
                </div>
            </div>

            <div class="flex flex-col md:flex-row gap-6 mb-6">
                <!-- Image Upload -->
                <div class="w-32 h-32 bg-slate-800 rounded-xl flex-shrink-0 border-2 border-dashed border-slate-600 flex items-center justify-center relative overflow-hidden group cursor-pointer hover:border-blue-500 transition">

                    <template x-if="imageUrl">
                        <img :src="imageUrl" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!imageUrl">
                        <i class="fas fa-camera text-slate-500 text-2xl group-hover:text-blue-500 transition"></i>
                    </template>

                    <div class="absolute inset-0 bg-black/60 flex items-center justify-center opacity-0 group-hover:opacity-100 transition">
                        <span class="text-white text-xs font-bold">Change</span>
                    </div>
                    <input type="file" name="photo" @change="updateImage" class="absolute inset-0 opacity-0 cursor-pointer">
                </div>

                <div class="flex-1 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Product Name</label>
                        <input type="text" name="description" value="{{ $item->description }}" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Barcode (Scan)</label>
                        <div class="relative">
                            <input type="text" name="code" value="{{ $item->code }}" class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-10 pr-4 py-2.5 text-white font-mono tracking-wider focus:border-blue-500 outline-none">
                            <i class="fas fa-barcode absolute left-3 top-3 text-slate-500"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Department / Category</label>
                    <select name="department_id" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-300 focus:border-blue-500 outline-none">
                        <option value="">-- No Department --</option>
                        <!-- Assuming departments are passed or we hardcode for now based on create.blade.php -->
                        <option value="1" {{ $item->department_id == 1 ? 'selected' : '' }}>Grocery</option>
                        <option value="2" {{ $item->department_id == 2 ? 'selected' : '' }}>Dairy</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Item Type</label>
                    <select name="item_type" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-slate-300 focus:border-blue-500 outline-none">
                        <option value="Inventory" {{ $item->item_type == 'Inventory' ? 'selected' : '' }}>Inventory Item</option>
                        <option value="Service" {{ $item->item_type == 'Service' ? 'selected' : '' }}>Service</option>
                        <option value="Package" {{ $item->item_type == 'Package' ? 'selected' : '' }}>Package</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-4 mb-8">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="hide_sale_price" class="rounded bg-slate-800 border-slate-600 text-blue-500 focus:ring-0" {{ $item->hide_sale_price ? 'checked' : '' }}>
                    <span class="text-xs font-bold text-slate-400 uppercase">Hide Price</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="open_price" class="rounded bg-slate-800 border-slate-600 text-blue-500 focus:ring-0" {{ $item->open_price ? 'checked' : '' }}>
                    <span class="text-xs font-bold text-slate-400 uppercase">Open Price</span>
                </label>
            </div>

            <div class="pt-6 border-t border-slate-800 flex justify-end gap-3">
                <a href="/items" class="px-5 py-2.5 rounded-lg border border-slate-600 text-slate-400 font-bold hover:bg-slate-800 hover:text-white transition text-sm">Cancel</a>
                <button type="submit" class="px-6 py-2.5 rounded-lg bg-blue-600 text-white font-bold hover:bg-blue-500 shadow-lg shadow-blue-900/40 transition">
                    <i class="fas fa-check mr-2"></i> Update Master Data
                </button>
            </div>
        </form>
    </div>

    <!-- RIGHT: FIFO Stock Dashboard (Read Only) -->
    <div class="space-y-6">

        <!-- Total Stock Summary -->
        <div class="bg-gradient-to-br from-indigo-600 to-blue-700 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden">
            <div class="relative z-10 flex justify-between items-start">
                <div>
                    <p class="text-blue-200 text-xs font-bold uppercase tracking-wide mb-1">Total Available Stock</p>
                    <h1 class="text-4xl font-extrabold">{{ $item->activeBatches->sum('quantity_available') }} <span class="text-lg font-medium text-blue-200">units</span></h1>
                </div>
                <div class="bg-white/10 p-3 rounded-xl backdrop-blur-sm border border-white/10">
                    <i class="fas fa-cubes text-2xl text-blue-100"></i>
                </div>
            </div>

            <!-- Decorators -->
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-blue-500/30 rounded-full blur-2xl"></div>
            <div class="absolute -left-6 -top-6 w-32 h-32 bg-indigo-500/30 rounded-full blur-2xl"></div>

            <div class="mt-6 pt-4 border-t border-white/10 text-[10px] text-blue-100 flex items-start gap-2">
                <i class="fas fa-info-circle mt-0.5"></i>
                <span>This calculates the sum of all active batches currently in the warehouse.</span>
            </div>
        </div>

        <!-- Live Batches List -->
        <div class="bg-slate-900 rounded-2xl shadow-xl border border-slate-800 overflow-hidden flex flex-col h-[500px]">
            <div class="bg-slate-950 px-5 py-4 border-b border-slate-800 flex justify-between items-center shrink-0">
                <h3 class="font-bold text-sm text-slate-300 flex items-center gap-2">
                    <i class="fas fa-layer-group text-slate-500"></i> Active Batches (FIFO)
                </h3>
                <!-- Placeholder for Add Stock Link -->
                <a href="{{ route('batches.create', $item->id) }}" class="text-xs text-blue-500 font-bold hover:text-blue-400 flex items-center gap-1 transition">
                    <i class="fas fa-plus"></i> Add Stock
                </a>
            </div>

            <div class="overflow-y-auto flex-1 custom-scrollbar p-2">
                <table class="w-full text-sm text-left border-separate border-spacing-y-2">
                    <thead class="text-[10px] text-slate-500 uppercase sticky top-0 bg-slate-900 z-10">
                        <tr>
                            <th class="px-3 py-2">Batch Info</th>
                            <th class="px-3 py-2 text-right">Price</th>
                            <th class="px-3 py-2 text-right">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($item->activeBatches as $batch)
                        <tr class="group hover:bg-slate-800/50 transition duration-150 rounded-lg cursor-pointer" onclick="window.location='{{ route('batches.edit', $batch->id) }}'">
                            <td class="px-3 py-3 bg-slate-950/50 rounded-l-lg border-l-2 border-transparent group-hover:border-blue-500">
                                <span class="block font-mono text-xs text-slate-300 font-bold tracking-wide">{{ $batch->batch_no }}</span>
                                <span class="block text-[10px] text-slate-500 mt-0.5">
                                    <i class="far fa-clock mr-1"></i> {{ $batch->received_at->format('d M Y') }}
                                </span>
                            </td>
                            <td class="px-3 py-3 bg-slate-950/50 text-right">
                                <span class="block font-bold text-green-400 text-xs">Rs. {{ number_format($batch->sale_price, 2) }}</span>
                                <span class="block text-[10px] text-slate-600">CP: {{ number_format($batch->cost_price, 2) }}</span>
                            </td>
                            <td class="px-3 py-3 bg-slate-950/50 rounded-r-lg text-right">
                                <span class="inline-block px-2 py-1 rounded bg-slate-800 text-white font-bold text-xs border border-slate-700 min-w-[40px] text-center shadow-sm">
                                    {{ $batch->quantity_available }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center opacity-50">
                                    <i class="fas fa-box-open text-3xl text-slate-600 mb-3"></i>
                                    <span class="text-slate-500 text-sm font-medium">No live stock batches</span>
                                    <span class="text-slate-600 text-xs mt-1">Add stock to start selling</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Footer Stats -->
            <div class="p-3 bg-slate-950 border-t border-slate-800 text-[10px] text-slate-500 text-center shrink-0">
                System automatically picks oldest batch first.
            </div>
        </div>

    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('imagePreview', (initialUrl) => ({
            imageUrl: initialUrl,
            updateImage(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imageUrl = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            }
        }))
    });
</script>
@endsection