import re

def update_cash_sales():
    with open('resources/views/cash-sales/create.blade.php', 'r') as f:
        content = f.read()

    # Extract the old search row to remove it
    old_row = '''                    <tr class="bg-slate-800/30">
                        <td class="px-3 py-6 text-center align-top pt-8"><i class="fas fa-search text-blue-500 text-lg"></i></td>
                        <td class="px-3 py-4 relative" colspan="2">
                            <div class="text-[11px] text-slate-400 mb-2"><i class="fas fa-keyboard mr-1"></i> Press Enter to add item, Enter again when done to go to Payment</div>
                            <div class="relative">
                                <input type="text"
                                    id="item-search"
                                    x-model="searchQuery"
                                    @input.debounce.200ms="performSearch()"
                                    @keydown.enter.prevent="if ((searchQuery || '').trim() === '') { document.getElementById('received-amount').focus() } else { selectFirstResult() }"
                                    placeholder="Type 1 letter to search..."
                                    class="w-full bg-slate-950 border border-slate-700 rounded-xl py-3.5 px-4 pl-11 text-white focus:ring-2 focus:ring-blue-500 outline-none placeholder-slate-500 shadow-inner min-h-[48px] text-[15px] leading-tight">

                                <i class="fas fa-barcode absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-base"></i>

                                <div x-show="searchResults.length > 0"
                                    @click.outside="searchResults = []"
                                    class="absolute top-[calc(100%+6px)] left-0 w-full bg-slate-900 border border-slate-700 rounded-xl shadow-2xl z-50 max-h-80 overflow-y-auto custom-scrollbar"
                                    style="display: none;">
                                    <ul>
                                        <template x-for="item in searchResults" :key="item.id">
                                            <li @click="addItem(item)" class="px-4 py-3.5 hover:bg-blue-600 cursor-pointer flex justify-between items-center border-b border-slate-800 last:border-0 group transition">
                                                <div class="flex-1 min-w-0 pr-6">
                                                    <span class="font-bold text-white block truncate text-[15px]" x-text="item.name"></span>
                                                    <span class="text-xs text-slate-400 font-mono group-hover:text-blue-200 mt-0.5 block" x-text="item.code"></span>
                                                </div>
                                                <div class="text-right whitespace-nowrap">
                                                    <span class="block font-bold text-green-400 text-base" x-text="'Rs. ' + item.price"></span>
                                                    <span class="text-xs uppercase font-bold px-2 py-0.5 rounded-md bg-slate-800 group-hover:bg-blue-500 group-hover:text-white mt-1 inline-block"
                                                        :class="item.stock_qty > 0 ? 'text-yellow-400' : 'text-red-400'"
                                                        x-text="item.stock_qty > 0 ? 'Stock: ' + item.stock_qty : 'Out of Stock'"></span>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </td>
                        <td colspan="4" class="p-4 text-xs text-slate-500 italic hidden lg:table-cell">
                            Type to search instantly.
                        </td>
                    </tr>'''
    
    content = content.replace(old_row + '\n', '')

    new_search_bar = '''        <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
             <h3 class="font-bold text-sm text-slate-300 flex items-center gap-2">
                 <i class="fas fa-shopping-cart text-slate-500"></i> Cart Items
             </h3>
        </div>
        <div class="p-4 lg:px-6 bg-slate-800/30 border-b border-slate-800 flex items-start gap-4 z-20 relative">
            <div class="pt-3 hidden sm:block">
                <i class="fas fa-search text-blue-500 text-xl"></i>
            </div>
            <div class="flex-1 relative">
                <div class="flex justify-between items-end mb-2">
                    <span class="text-[11px] text-slate-400"><i class="fas fa-keyboard mr-1"></i> Press Enter to add item, Enter again when done to go to Payment</span>
                    <span class="text-[11px] text-slate-500 italic hidden lg:block">Type to search instantly.</span>
                </div>
                <div class="relative">
                    <input type="text"
                        id="item-search"
                        x-model="searchQuery"
                        @input.debounce.200ms="performSearch()"
                        @keydown.enter.prevent="if ((searchQuery || '').trim() === '') { document.getElementById('received-amount').focus() } else { selectFirstResult() }"
                        placeholder="Search by code or name..."
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl py-3.5 px-4 pl-11 text-white focus:ring-2 focus:ring-blue-500 outline-none placeholder-slate-500 shadow-inner min-h-[48px] text-[15px] leading-tight">
                    <i class="fas fa-barcode absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-base"></i>
                    
                    <div x-show="searchResults.length > 0"
                        @click.outside="searchResults = []"
                        class="absolute top-[calc(100%+6px)] left-0 w-full bg-slate-900 border border-slate-700 rounded-xl shadow-2xl z-50 max-h-80 overflow-y-auto custom-scrollbar"
                        style="display: none;">
                        <ul>
                            <template x-for="item in searchResults" :key="item.id">
                                <li @click="addItem(item)" class="px-4 py-3.5 hover:bg-blue-600 cursor-pointer flex justify-between items-center border-b border-slate-800 last:border-0 group transition">
                                    <div class="flex-1 min-w-0 pr-6">
                                        <span class="font-bold text-white block truncate text-[15px]" x-text="item.name"></span>
                                        <span class="text-xs text-slate-400 font-mono group-hover:text-blue-200 mt-0.5 block" x-text="item.code"></span>
                                    </div>
                                    <div class="text-right whitespace-nowrap">
                                        <span class="block font-bold text-green-400 text-base" x-text="'Rs. ' + item.price"></span>
                                        <span class="text-xs uppercase font-bold px-2 py-0.5 rounded-md bg-slate-800 group-hover:bg-blue-500 group-hover:text-white mt-1 inline-block"
                                            :class="item.stock_qty > 0 ? 'text-yellow-400' : 'text-red-400'"
                                            x-text="item.stock_qty > 0 ? 'Stock: ' + item.stock_qty : 'Out of Stock'"></span>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>'''
    
    old_header = '''        <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
             <h3 class="font-bold text-sm text-slate-300 flex items-center gap-2">
                 <i class="fas fa-shopping-cart text-slate-500"></i> Cart Items
             </h3>
        </div>'''

    content = content.replace(old_header, new_search_bar)

    with open('resources/views/cash-sales/create.blade.php', 'w') as f:
        f.write(content)

def update_debit_sales():
    with open('resources/views/debit-sales/create.blade.php', 'r') as f:
        content = f.read()

    old_row = '''                    <tr class="bg-slate-800/30">
                        <td class="px-3 py-6 text-center align-top pt-8"><i class="fas fa-search text-red-500 text-lg"></i></td>
                        <td class="px-3 py-4 relative" colspan="2">
                            <div class="text-[11px] text-slate-400 mb-2"><i class="fas fa-keyboard mr-1"></i> Select customer first. Press Enter to add item, Enter again when done to go to Paid amount</div>
                            <div class="relative">
                                <input type="text"
                                    id="item-search"
                                    x-model="searchQuery"
                                    @input.debounce.200ms="performSearch()"
                                    @keydown.enter.prevent="if ((searchQuery || '').trim() === '') { document.getElementById('received-amount').focus() } else { selectFirstResult() }"
                                    placeholder="Type 1 letter to search..."
                                    class="w-full bg-slate-950 border border-slate-700 rounded-xl py-3.5 px-4 pl-11 text-white focus:ring-2 focus:ring-red-500 outline-none placeholder-slate-500 shadow-inner min-h-[48px] text-[15px] leading-tight">

                                <i class="fas fa-barcode absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-base"></i>

                                <div x-show="searchResults.length > 0"
                                    @click.outside="searchResults = []"
                                    class="absolute top-[calc(100%+6px)] left-0 w-full bg-slate-900 border border-slate-700 rounded-xl shadow-2xl z-50 max-h-80 overflow-y-auto custom-scrollbar"
                                    style="display: none;">
                                    <ul>
                                        <template x-for="item in searchResults" :key="item.id">
                                            <li @click="addItem(item)" class="px-4 py-3.5 hover:bg-red-900/50 cursor-pointer flex justify-between items-center border-b border-slate-800 last:border-0 group transition">
                                                <div class="flex-1 min-w-0 pr-6">
                                                    <span class="font-bold text-white block truncate text-[15px]" x-text="item.name"></span>
                                                    <span class="text-xs text-slate-400 font-mono group-hover:text-red-200 mt-0.5 block" x-text="item.code"></span>
                                                </div>
                                                <div class="text-right whitespace-nowrap">
                                                    <span class="block font-bold text-white text-base" x-text="'Rs. ' + item.price"></span>
                                                    <span class="text-xs uppercase font-bold px-2 py-0.5 rounded-md bg-slate-800 group-hover:bg-red-500 group-hover:text-white mt-1 inline-block"
                                                        :class="item.stock_qty > 0 ? 'text-yellow-400' : 'text-red-400'"
                                                        x-text="item.stock_qty > 0 ? 'Stock: ' + item.stock_qty : 'Out of Stock'"></span>
                                                </div>
                                            </li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </td>
                        <td colspan="4" class="p-4 text-xs text-slate-500 italic hidden lg:table-cell">
                            Adding to Debit Invoice.
                        </td>
                    </tr>'''
    
    content = content.replace(old_row + '\n', '')

    new_search_bar = '''        <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
             <h3 class="font-bold text-sm text-slate-300 flex items-center gap-2">
                 <i class="fas fa-shopping-cart text-slate-500"></i> Cart Items
             </h3>
        </div>
        <div class="p-4 lg:px-6 bg-slate-800/30 border-b border-slate-800 flex items-start gap-4 z-20 relative">
            <div class="pt-3 hidden sm:block">
                <i class="fas fa-search text-red-500 text-xl"></i>
            </div>
            <div class="flex-1 relative">
                <div class="flex justify-between items-end mb-2">
                    <span class="text-[11px] text-slate-400"><i class="fas fa-keyboard mr-1"></i> Select customer first. Press Enter to add item, Enter again when done to go to Paid amount</span>
                    <span class="text-[11px] text-slate-500 italic hidden lg:block">Adding to Debit Invoice.</span>
                </div>
                <div class="relative">
                    <input type="text"
                        id="item-search"
                        x-model="searchQuery"
                        @input.debounce.200ms="performSearch()"
                        @keydown.enter.prevent="if ((searchQuery || '').trim() === '') { document.getElementById('received-amount').focus() } else { selectFirstResult() }"
                        placeholder="Search by code or name..."
                        class="w-full bg-slate-950 border border-slate-700 rounded-xl py-3.5 px-4 pl-11 text-white focus:ring-2 focus:ring-red-500 outline-none placeholder-slate-500 shadow-inner min-h-[48px] text-[15px] leading-tight">
                    <i class="fas fa-barcode absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500 text-base"></i>
                    
                    <div x-show="searchResults.length > 0"
                        @click.outside="searchResults = []"
                        class="absolute top-[calc(100%+6px)] left-0 w-full bg-slate-900 border border-slate-700 rounded-xl shadow-2xl z-50 max-h-80 overflow-y-auto custom-scrollbar"
                        style="display: none;">
                        <ul>
                            <template x-for="item in searchResults" :key="item.id">
                                <li @click="addItem(item)" class="px-4 py-3.5 hover:bg-red-900/50 cursor-pointer flex justify-between items-center border-b border-slate-800 last:border-0 group transition">
                                    <div class="flex-1 min-w-0 pr-6">
                                        <span class="font-bold text-white block truncate text-[15px]" x-text="item.name"></span>
                                        <span class="text-xs text-slate-400 font-mono group-hover:text-red-200 mt-0.5 block" x-text="item.code"></span>
                                    </div>
                                    <div class="text-right whitespace-nowrap">
                                        <span class="block font-bold text-white text-base" x-text="'Rs. ' + item.price"></span>
                                        <span class="text-xs uppercase font-bold px-2 py-0.5 rounded-md bg-slate-800 group-hover:bg-red-500 group-hover:text-white mt-1 inline-block"
                                            :class="item.stock_qty > 0 ? 'text-yellow-400' : 'text-red-400'"
                                            x-text="item.stock_qty > 0 ? 'Stock: ' + item.stock_qty : 'Out of Stock'"></span>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>'''

    old_header = '''        <div class="bg-slate-950 px-6 py-4 border-b border-slate-800 flex justify-between items-center">
             <h3 class="font-bold text-sm text-slate-300 flex items-center gap-2">
                 <i class="fas fa-shopping-cart text-slate-500"></i> Cart Items
             </h3>
        </div>'''

    content = content.replace(old_header, new_search_bar)

    with open('resources/views/debit-sales/create.blade.php', 'w') as f:
        f.write(content)


update_cash_sales()
update_debit_sales()
print("Move search bar successful")
