@extends('layouts.admin')

@section('title', 'Add New Product')

@section('content')

<div class="max-w-7xl mx-auto" x-data="itemManager()">

    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Create Product</h1>
            <p class="text-slate-400 text-sm mt-1">Add a new inventory item to your catalog.</p>
        </div>
        <div class="flex gap-3">
            <a href="/items" class="px-5 py-2.5 rounded-lg border border-slate-600 text-slate-300 hover:bg-slate-800 hover:text-white transition font-bold text-sm">Cancel</a>
            <button form="itemForm" type="submit" class="px-6 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold shadow-lg shadow-blue-900/50 transition transform hover:-translate-y-0.5">
                <i class="fas fa-save mr-2"></i> Save Product
            </button>
        </div>
    </div>

    <form id="itemForm" action="/items/store" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-7 space-y-6">

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden group">
                    <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>

                    <h3 class="text-white font-bold text-lg mb-6 flex items-center gap-2">
                        <i class="fas fa-cube text-blue-500"></i> Product Identity
                    </h3>

                    <div class="space-y-5">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-8">
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Product Name <span class="text-red-500">*</span></label>
                                <input type="text" name="description" x-model="name" @input="generateBarcode()" placeholder="e.g. Nestle Milkpak 1L" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition font-medium">
                            </div>
                            <div class="col-span-4">
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Barcode / Code</label>
                                <div class="relative">
                                    <input type="text" name="code" x-model="code" placeholder="Scan or Auto..." class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-3 pr-10 py-3 text-white font-mono focus:border-blue-500 outline-none">
                                    <button type="button" @click="forceGenerate()" class="absolute right-2 top-2 text-slate-500 hover:text-white transition" title="Regenerate">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Item Type</label>
                                <select name="item_type" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-slate-300 focus:border-blue-500 outline-none">
                                    <option value="Inventory">Inventory Item</option>
                                    <option value="Service">Service (No Stock)</option>
                                    <option value="Package">Package / Deal</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Department</label>
                                <select name="department_id" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-3 text-slate-300 focus:border-blue-500 outline-none">
                                    <option value="">Select Dept...</option>
                                    <option value="1">Grocery</option>
                                    <option value="2">Dairy</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-4 pt-2">
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-800 px-3 py-2 rounded-lg border border-slate-700 hover:border-blue-500 transition">
                                <input type="checkbox" name="hide_sale_price" class="rounded text-blue-500 bg-slate-900 border-slate-600 focus:ring-0">
                                <span class="text-xs font-bold text-slate-300 uppercase">Hide Price</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer bg-slate-800 px-3 py-2 rounded-lg border border-slate-700 hover:border-blue-500 transition">
                                <input type="checkbox" name="open_price" class="rounded text-blue-500 bg-slate-900 border-slate-600 focus:ring-0">
                                <span class="text-xs font-bold text-slate-300 uppercase">Open Price</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative group">
                    <div class="absolute top-0 left-0 w-1 h-full bg-purple-500"></div>
                    <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                        <i class="fas fa-image text-purple-500"></i> Product Image
                    </h3>

                    <div class="border-2 border-dashed border-slate-700 rounded-xl p-8 text-center hover:border-purple-500 hover:bg-slate-800/50 transition cursor-pointer relative" @click="$refs.fileInput.click()">
                        <input type="file" name="photo" x-ref="fileInput" class="hidden" @change="previewImage">

                        <template x-if="!imageUrl">
                            <div>
                                <div class="w-16 h-16 bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-cloud-upload-alt text-2xl text-slate-500"></i>
                                </div>
                                <p class="text-slate-300 font-medium">Click to upload or drag and drop</p>
                                <p class="text-slate-500 text-xs mt-1">SVG, PNG, JPG (Max 800x800px)</p>
                            </div>
                        </template>

                        <template x-if="imageUrl">
                            <div class="relative">
                                <img :src="imageUrl" class="h-48 mx-auto rounded-lg object-contain">
                                <button type="button" @click.stop="imageUrl = null" class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1 shadow hover:bg-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <div class="lg:col-span-5 space-y-6">

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-green-500"></div>
                    <h3 class="text-white font-bold text-lg mb-6 flex items-center gap-2">
                        <i class="fas fa-tag text-green-500"></i> Pricing Engine
                    </h3>

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Cost Price (CP)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-3 text-slate-500 text-sm">Rs.</span>
                                    <input type="number" step="0.01" name="cost_price" x-model="cost" class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-10 pr-4 py-2.5 text-white font-mono focus:border-green-500 outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-green-400 uppercase mb-1">Sale Price (SP)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-3 text-green-500 text-sm">Rs.</span>
                                    <input type="number" step="0.01" name="sale_price" x-model="sale" class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-10 pr-4 py-2.5 text-white font-bold font-mono focus:border-green-500 outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="bg-slate-950 rounded-lg p-3 border border-slate-800 flex justify-between items-center">
                            <span class="text-xs text-slate-400 font-bold uppercase">Estimated Margin</span>
                            <span class="text-sm font-mono font-bold" :class="margin >= 0 ? 'text-green-400' : 'text-red-400'" x-text="margin + '%'"></span>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Whole Sale Price</label>
                                <input type="number" name="wholesale_price" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-slate-300 text-sm focus:border-green-500 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Trade Rate</label>
                                <input type="number" name="trade_rate" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-slate-300 text-sm focus:border-green-500 outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-1 h-full bg-orange-500"></div>
                    <h3 class="text-white font-bold text-lg mb-6 flex items-center gap-2">
                        <i class="fas fa-boxes text-orange-500"></i> Stock Control
                    </h3>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Min Stock (Alert)</label>
                            <input type="number" name="min_stock" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:border-orange-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Max Stock</label>
                            <input type="number" name="max_stock" class="w-full bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-white focus:border-orange-500 outline-none">
                        </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-800">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Opening Stock (On Hand)</label>
                        <input type="number" name="on_hand" placeholder="0" class="w-full bg-yellow-900/20 border border-yellow-700/50 rounded-lg px-4 py-3 text-yellow-400 font-bold focus:border-yellow-500 outline-none">
                    </div>
                </div>

                <div x-data="{ open: false }" class="bg-slate-900 border border-slate-800 rounded-2xl shadow-xl">
                    <button type="button" @click="open = !open" class="w-full p-4 flex justify-between items-center text-left">
                        <span class="text-sm font-bold text-slate-400 flex items-center gap-2">
                            <i class="fas fa-book text-slate-500"></i> GL Accounts Integration
                        </span>
                        <i class="fas fa-chevron-down text-slate-600 transition" :class="open ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="open" x-collapse class="p-6 pt-0 border-t border-slate-800 space-y-4">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Sales Income Account</label>
                            <select name="sales_account_id" class="w-full bg-slate-950 border border-slate-700 rounded px-2 py-2 text-xs text-slate-300">
                                <option value="1">40100 - Sales Revenue</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">COGS Account</label>
                            <select name="cogs_account_id" class="w-full bg-slate-950 border border-slate-700 rounded px-2 py-2 text-xs text-slate-300">
                                <option value="2">50100 - Cost of Goods Sold</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
    function itemManager() {
        return {
            imageUrl: null,
            cost: 0,
            sale: 0,
            name: '',
            code: '',

            get margin() {
                if (this.sale > 0 && this.cost > 0) {
                    return (((this.sale - this.cost) / this.sale) * 100).toFixed(1);
                }
                return 0;
            },

            previewImage(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.imageUrl = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            },

            // Auto-Generate Barcode Logic
            generateBarcode() {
                if (this.name.length > 2 && this.code === '') {
                    this.code = this.getRandomCode();
                }
            },

            forceGenerate() {
                this.code = this.getRandomCode();
            },

            getRandomCode() {
                // Generate random unique-ish 8 digit number
                return Math.floor(10000000 + Math.random() * 90000000).toString();
            }
        }
    }
</script>

@endsection