{{--
    Smart Product Search Component
    Usage: <x-smart-product-search @product-selected="handleProductSelection" />
    Props:
    - allow-out-of-stock: Enable selection of out-of-stock items (for stock adjustment)
--}}
<div x-data="smartProductSearch({{ isset($allowOutOfStock) && $allowOutOfStock ? 'true' : 'false' }})" x-init="init()">
    <div class="relative w-full">
        <div class="relative group">
            <!-- Search Icon -->
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="w-6 h-6 text-slate-400 group-focus-within:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Search Input -->
            <input
                x-ref="searchInput"
                type="text"
                x-model="searchQuery"
                @input.debounce.300ms="handleSearchInput()"
                @keydown.down.prevent="highlightNext()"
                @keydown.up.prevent="highlightPrev()"
                @keydown.enter.prevent="handleSearchEnter()"
                @keydown.escape="clearSearch()"
                class="w-full bg-white dark:bg-slate-800 text-slate-900 dark:text-white rounded-xl md:rounded-2xl pl-10 md:pl-12 pr-4 py-3 md:py-4 text-base md:text-xl shadow-lg border-2 transition-all outline-none placeholder-slate-400 dark:placeholder-slate-500"
                :class="searchQuery ? 'border-blue-500 ring-4 ring-blue-500/20' : 'border-slate-200 dark:border-slate-700'"
                autocomplete="off"
                placeholder="{{ $placeholder ?? 'Scan Barcode or Search Product...' }}">

            <!-- Clear Button -->
            <button
                x-show="searchQuery.length > 0"
                @click="clearSearch()"
                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 hover:text-slate-900 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Dropdown Results -->
        <div
            x-show="searchResults.length > 0"
            class="absolute top-full left-0 w-full mt-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl z-50 overflow-hidden max-h-[500px] overflow-y-auto"
            style="display: none;"
            @click.away="searchResults = []">

            <template x-for="(item, index) in searchResults" :key="item.id">
                <div
                    @click="selectResult(item)"
                    class="p-4 flex justify-between items-center cursor-pointer transition-colors border-b border-slate-200 dark:border-slate-700/50 last:border-0"
                    :class="{
                        'bg-blue-600 text-white': activeSearchIndex === index,
                        'bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-700': activeSearchIndex !== index && item.on_hand > 0,
                        'bg-red-50 dark:bg-red-900/20 opacity-60 cursor-not-allowed': item.on_hand <= 0 && activeSearchIndex !== index,
                        'bg-red-600 dark:bg-red-900/50 text-white': item.on_hand <= 0 && activeSearchIndex === index
                    }">
                    <div>
                        <div class="font-bold flex items-center gap-2">
                            <span x-text="item.name || item.description"></span>
                            <span x-show="item.low_stock && item.on_hand > 0" class="px-2 py-0.5 rounded text-[10px] font-bold bg-yellow-500/20 text-yellow-500 border border-yellow-500/30">LOW STOCK</span>
                            <span x-show="item.on_hand <= 0" class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-500/20 text-red-500 border border-red-500/30">OUT OF STOCK</span>
                        </div>
                        <div class="text-xs opacity-70 mt-1">
                            Code: <span class="font-mono" x-html="highlight(item.barcode || item.code)"></span> |
                            Stock: <span :class="{'text-red-400 font-bold': item.on_hand <= 0, 'text-emerald-400 font-bold': item.on_hand > 0}" x-text="item.on_hand"></span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="font-mono font-bold text-lg" x-text="formatPrice(item.rate || item.sale_rate)"></div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
    function smartProductSearch(allowOutOfStock = false) {
        return {
            searchQuery: '',
            searchResults: [],
            activeSearchIndex: -1,
            allowOutOfStock: allowOutOfStock,

            init() {
                this.$nextTick(() => {
                    if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                });
            },

            // Search with debounce (triggered by Alpine's debounce modifier)
            handleSearchInput() {
                let q = this.searchQuery.trim();
                this.activeSearchIndex = -1;

                // Empty search = close dropdown
                if (q.length < 1) {
                    this.searchResults = [];
                    return;
                }

                console.log('[Search] Query:', q);

                // Fetch products
                fetch(`/api/products/search?q=${encodeURIComponent(q)}`)
                    .then(response => response.json())
                    .then(data => {
                        console.log('[Search] Results count:', data.length);
                        this.searchResults = data;

                        // If no results found and query is meaningful, dispatch not-found event
                        if (data.length === 0 && q.length >= 2) {
                            console.log('[Search] No results found, dispatching product-not-found event with query:', this.searchQuery);
                            this.$dispatch('product-not-found', {
                                query: this.searchQuery
                            });
                            return;
                        }

                        // Auto-select if exact barcode match (single result)
                        if (data.length === 1 && this.searchQuery === data[0].barcode) {
                            this.selectResult(data[0]);
                            return;
                        }

                        // Auto-highlight first in-stock item
                        this.activeSearchIndex = this.searchResults.findIndex(item => item.on_hand > 0);

                        // Scroll to active
                        if (this.activeSearchIndex >= 0) {
                            this.$nextTick(() => this.scrollToActive());
                        }
                    })
                    .catch(error => {
                        console.error('[Search] Error:', error);
                        this.searchResults = [];
                    });
            },

            // Enter key handler
            handleSearchEnter() {
                console.log('[Search] Enter pressed. Search query:', this.searchQuery, 'Results count:', this.searchResults.length);

                // Select highlighted item
                if (this.activeSearchIndex >= 0 && this.searchResults[this.activeSearchIndex]) {
                    console.log('[Search] Selecting highlighted item at index:', this.activeSearchIndex);
                    this.selectResult(this.searchResults[this.activeSearchIndex]);
                    return;
                }

                // Auto-select first available item
                if (this.searchResults.length > 0) {
                    const firstAvail = this.searchResults.find(i => i.on_hand > 0);
                    if (firstAvail) {
                        console.log('[Search] Auto-selecting first available item:', firstAvail.name);
                        this.selectResult(firstAvail);
                    } else {
                        console.log('[Search] All results out of stock, dispatching product-not-found');
                        this.$dispatch('product-not-found', {
                            query: this.searchQuery
                        });
                    }
                } else if (this.searchQuery.trim()) {
                    console.log('[Search] No results found for query, dispatching product-not-found');
                    this.$dispatch('product-not-found', {
                        query: this.searchQuery
                    });
                }
            },

            // Select product
            selectResult(item) {
                // Only block out-of-stock selection if allowOutOfStock is false
                if (item.on_hand <= 0 && !this.allowOutOfStock) {
                    this.$dispatch('out-of-stock', item);
                    return;
                }

                // Dispatch event to parent
                this.$dispatch('product-selected', item);

                // Clear search
                this.clearSearch();

                // Auto-focus input again
                this.$nextTick(() => {
                    if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                });
            },

            // Navigate to next selectable item (skip out-of-stock unless allowOutOfStock is true)
            highlightNext() {
                if (this.searchResults.length === 0) return;

                let nextIndex = this.activeSearchIndex + 1;

                // If allowOutOfStock, just move to next item
                if (this.allowOutOfStock && nextIndex < this.searchResults.length) {
                    this.activeSearchIndex = nextIndex;
                    this.scrollToActive();
                    return;
                }

                // Find next in-stock item
                while (nextIndex < this.searchResults.length) {
                    if (this.searchResults[nextIndex].on_hand > 0) {
                        this.activeSearchIndex = nextIndex;
                        this.scrollToActive();
                        return;
                    }
                    nextIndex++;
                }
            },

            // Navigate to previous selectable item (skip out-of-stock unless allowOutOfStock is true)
            highlightPrev() {
                if (this.searchResults.length === 0) return;

                let prevIndex = this.activeSearchIndex - 1;

                // If allowOutOfStock, just move to previous item
                if (this.allowOutOfStock && prevIndex >= 0) {
                    this.activeSearchIndex = prevIndex;
                    this.scrollToActive();
                    return;
                }

                // Find previous in-stock item
                while (prevIndex >= 0) {
                    if (this.searchResults[prevIndex].on_hand > 0) {
                        this.activeSearchIndex = prevIndex;
                        this.scrollToActive();
                        return;
                    }
                    prevIndex--;
                }
            },

            // Scroll to active item in dropdown
            scrollToActive() {
                this.$nextTick(() => {
                    const activeEl = this.$el.querySelector('.bg-blue-600');
                    if (activeEl) {
                        activeEl.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                    }
                });
            },

            // Clear search
            clearSearch() {
                this.searchQuery = '';
                this.searchResults = [];
                this.activeSearchIndex = -1;
            },

            // Highlight matching text
            highlight(text) {
                if (!this.searchQuery || !text) return text;
                return text.replace(new RegExp(this.searchQuery, 'gi'), match => `<span class="bg-blue-500/30 text-blue-200 font-bold">${match}</span>`);
            },

            // Format price
            formatPrice(price) {
                return parseFloat(price || 0).toFixed(2);
            }
        };
    }
</script>