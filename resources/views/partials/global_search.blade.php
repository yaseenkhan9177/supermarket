<div x-data="globalSearch()" @click.away="open = false" @keydown.escape="open = false" class="relative w-48 sm:w-64 md:w-80">
    {{-- Search Input --}}
    <div class="relative">
        <input
            type="text"
            x-model="query"
            @input.debounce.300ms="performSearch()"
            @focus="if(query.length >= 2) open = true"
            placeholder="Search customers, items, invoices..."
            class="w-full pl-9 pr-8 py-2 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-xs sm:text-sm font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-inner"
        />
        <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
            <i class="fas fa-search text-xs" x-show="!loading"></i>
            <i class="fas fa-spinner fa-spin text-xs text-indigo-500" x-show="loading" x-cloak></i>
        </div>
        <button
            x-show="query.length > 0"
            @click="query = ''; results = {}; open = false"
            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs p-1"
            x-cloak
        >
            <i class="fas fa-times"></i>
        </button>
    </div>

    {{-- Dropdown Results --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-1"
        class="absolute left-0 right-0 mt-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-2xl overflow-hidden z-50 max-h-[480px] overflow-y-auto"
        x-cloak
    >
        {{-- No results message --}}
        <template x-if="!loading && totalResults === 0 && query.length >= 2">
            <div class="p-6 text-center text-slate-400 dark:text-slate-500 text-xs">
                <i class="fas fa-search-minus text-2xl mb-2 block opacity-40"></i>
                No records found matching "<span x-text="query" class="font-semibold text-slate-600 dark:text-slate-300"></span>"
            </div>
        </template>

        {{-- Group: Customers --}}
        <template x-if="results.customers && results.customers.length > 0">
            <div class="border-b border-slate-100 dark:border-slate-700/60">
                <div class="px-3.5 py-1.5 bg-slate-50 dark:bg-slate-900/50 text-[10px] font-bold tracking-wider text-slate-400 dark:text-slate-500 uppercase flex items-center gap-1.5">
                    <i class="fas fa-users text-blue-500"></i> Customers
                </div>
                <template x-for="item in results.customers" :key="'cust-' + item.id">
                    <a :href="item.url" class="flex items-center gap-3 px-3.5 py-2 hover:bg-indigo-50/70 dark:hover:bg-indigo-900/30 transition-colors group">
                        <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xs shrink-0">
                            <i :class="item.icon"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 truncate" x-text="item.title"></p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate" x-text="item.subtitle"></p>
                        </div>
                    </a>
                </template>
            </div>
        </template>

        {{-- Group: Suppliers --}}
        <template x-if="results.suppliers && results.suppliers.length > 0">
            <div class="border-b border-slate-100 dark:border-slate-700/60">
                <div class="px-3.5 py-1.5 bg-slate-50 dark:bg-slate-900/50 text-[10px] font-bold tracking-wider text-slate-400 dark:text-slate-500 uppercase flex items-center gap-1.5">
                    <i class="fas fa-truck text-purple-500"></i> Suppliers
                </div>
                <template x-for="item in results.suppliers" :key="'sup-' + item.id">
                    <a :href="item.url" class="flex items-center gap-3 px-3.5 py-2 hover:bg-indigo-50/70 dark:hover:bg-indigo-900/30 transition-colors group">
                        <div class="w-7 h-7 rounded-lg bg-purple-100 dark:bg-purple-900/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xs shrink-0">
                            <i :class="item.icon"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 truncate" x-text="item.title"></p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate" x-text="item.subtitle"></p>
                        </div>
                    </a>
                </template>
            </div>
        </template>

        {{-- Group: Items --}}
        <template x-if="results.items && results.items.length > 0">
            <div class="border-b border-slate-100 dark:border-slate-700/60">
                <div class="px-3.5 py-1.5 bg-slate-50 dark:bg-slate-900/50 text-[10px] font-bold tracking-wider text-slate-400 dark:text-slate-500 uppercase flex items-center gap-1.5">
                    <i class="fas fa-box text-emerald-500"></i> Products / Stock
                </div>
                <template x-for="item in results.items" :key="'item-' + item.id">
                    <a :href="item.url" class="flex items-center gap-3 px-3.5 py-2 hover:bg-indigo-50/70 dark:hover:bg-indigo-900/30 transition-colors group">
                        <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-xs shrink-0">
                            <i :class="item.icon"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 truncate" x-text="item.title"></p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate" x-text="item.subtitle"></p>
                        </div>
                    </a>
                </template>
            </div>
        </template>

        {{-- Group: Sales --}}
        <template x-if="results.sales && results.sales.length > 0">
            <div class="border-b border-slate-100 dark:border-slate-700/60">
                <div class="px-3.5 py-1.5 bg-slate-50 dark:bg-slate-900/50 text-[10px] font-bold tracking-wider text-slate-400 dark:text-slate-500 uppercase flex items-center gap-1.5">
                    <i class="fas fa-file-invoice-dollar text-amber-500"></i> Invoices & Sales
                </div>
                <template x-for="item in results.sales" :key="'sale-' + item.id">
                    <a :href="item.url" target="_blank" class="flex items-center gap-3 px-3.5 py-2 hover:bg-indigo-50/70 dark:hover:bg-indigo-900/30 transition-colors group">
                        <div class="w-7 h-7 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center text-xs shrink-0">
                            <i :class="item.icon"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 truncate" x-text="item.title"></p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate" x-text="item.subtitle"></p>
                        </div>
                    </a>
                </template>
            </div>
        </template>

        {{-- Group: Expenses --}}
        <template x-if="results.expenses && results.expenses.length > 0">
            <div>
                <div class="px-3.5 py-1.5 bg-slate-50 dark:bg-slate-900/50 text-[10px] font-bold tracking-wider text-slate-400 dark:text-slate-500 uppercase flex items-center gap-1.5">
                    <i class="fas fa-receipt text-red-500"></i> Expenses & Bills
                </div>
                <template x-for="item in results.expenses" :key="'exp-' + item.id">
                    <a :href="item.url" class="flex items-center gap-3 px-3.5 py-2 hover:bg-indigo-50/70 dark:hover:bg-indigo-900/30 transition-colors group">
                        <div class="w-7 h-7 rounded-lg bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400 flex items-center justify-center text-xs shrink-0">
                            <i :class="item.icon"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 truncate" x-text="item.title"></p>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 truncate" x-text="item.subtitle"></p>
                        </div>
                    </a>
                </template>
            </div>
        </template>
    </div>
</div>

<script>
function globalSearch() {
    return {
        query: '',
        open: false,
        loading: false,
        results: {},
        totalResults: 0,
        performSearch() {
            if (this.query.trim().length < 2) {
                this.results = {};
                this.totalResults = 0;
                this.open = false;
                return;
            }

            this.loading = true;
            fetch(`/search?q=${encodeURIComponent(this.query)}`)
                .then(res => res.json())
                .then(data => {
                    this.results = data;
                    this.totalResults = data.total || 0;
                    this.open = true;
                })
                .catch(() => {
                    this.results = {};
                    this.totalResults = 0;
                })
                .finally(() => {
                    this.loading = false;
                });
        }
    }
}
</script>
