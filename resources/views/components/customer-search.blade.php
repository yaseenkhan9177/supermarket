@props([
    'walkIn' => false,
    'addNew' => false,
    'required' => false,
    'name' => 'customer_id',
    'id' => 'customer_id',
    'placeholder' => 'Search by name or phone...',
    'selectedId' => '',
    'selectedName' => '',
])

<div x-data="{
    query: '{{ $selectedName }}',
    selectedId: '{{ $selectedId }}',
    selectedCustomer: null,
    results: [],
    loading: false,
    isOpen: false,
    debounceTimer: null,

    onInput() {
        this.selectedId = '';
        this.selectedCustomer = null;
        this.dispatchChange();
        
        clearTimeout(this.debounceTimer);
        if (this.query.trim().length < 1) {
            this.results = [];
            this.isOpen = false;
            return;
        }

        this.loading = true;
        this.isOpen = true;
        this.debounceTimer = setTimeout(() => {
            fetch('/customers/search?q=' + encodeURIComponent(this.query.trim()))
                .then(res => res.json())
                .then(data => {
                    this.results = data || [];
                    this.loading = false;
                })
                .catch(err => {
                    console.error('Customer search error:', err);
                    this.loading = false;
                });
        }, 300);
    },

    getGroupedResults() {
        if (!this.results || this.results.length === 0) return [];
        const groups = {};
        this.results.forEach(cust => {
            const letter = cust.name ? cust.name.trim().charAt(0).toUpperCase() : '#';
            if (!groups[letter]) groups[letter] = [];
            groups[letter].push(cust);
        });
        return Object.keys(groups).sort().map(letter => ({
            letter: letter,
            items: groups[letter]
        }));
    },

    selectCustomer(customer) {
        if (!customer) {
            // Walk-in Customer
            this.selectedId = '';
            this.query = 'Walk-in Customer';
            this.selectedCustomer = null;
        } else {
            this.selectedId = customer.id;
            this.query = customer.name;
            this.selectedCustomer = customer;
        }
        this.isOpen = false;
        this.dispatchChange();
    },

    selectWalkIn() {
        this.selectCustomer(null);
    },

    triggerAddNew() {
        this.isOpen = false;
        this.$dispatch('open-add-customer-modal');
    },

    clearSelection() {
        this.selectedId = '';
        this.query = '';
        this.selectedCustomer = null;
        this.results = [];
        this.isOpen = false;
        this.dispatchChange();
    },

    dispatchChange() {
        $nextTick(() => {
            const hiddenInput = $refs.hiddenInput;
            if (hiddenInput) {
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
                hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            window.dispatchEvent(new CustomEvent('customer-selected', {
                detail: {
                    id: this.selectedId,
                    customer: this.selectedCustomer
                }
            }));
        });
    }
}" class="relative w-full" @click.outside="isOpen = false">

    <!-- Hidden Input for Form Submission -->
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" x-ref="hiddenInput" :value="selectedId" {{ $required ? 'required' : '' }}>

    <div class="relative flex items-center">
        <input 
            type="text" 
            x-model="query" 
            @input="onInput()" 
            @focus="if (query.trim().length >= 1 || {{ $walkIn ? 'true' : 'false' }} || {{ $addNew ? 'true' : 'false' }}) isOpen = true"
            placeholder="{{ $placeholder }}" 
            class="w-full bg-slate-950 border border-slate-700 rounded-lg py-2.5 pl-9 pr-8 text-white placeholder-slate-500 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
            autocomplete="off"
        >

        <!-- Search Icon -->
        <span class="absolute left-3 text-slate-400 pointer-events-none text-xs">
            <i class="fas fa-search"></i>
        </span>

        <!-- Clear / Loading Icon -->
        <div class="absolute right-2.5 flex items-center gap-1">
            <template x-if="loading">
                <i class="fas fa-spinner fa-spin text-slate-400 text-xs"></i>
            </template>
            <template x-if="!loading && (query || selectedId)">
                <button type="button" @click="clearSelection()" class="text-slate-400 hover:text-white text-xs p-1">
                    <i class="fas fa-times"></i>
                </button>
            </template>
        </div>
    </div>

    <!-- Dropdown Results List -->
    <div 
        x-show="isOpen" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute left-0 right-0 mt-1 bg-slate-900 border border-slate-700 rounded-lg shadow-2xl z-50 max-h-60 overflow-y-auto"
        style="display: none;"
    >
        @if($walkIn)
        <button 
            type="button" 
            @click="selectWalkIn()" 
            class="w-full px-3 py-2 text-left hover:bg-gray-700 flex justify-between items-center text-xs font-semibold text-indigo-400 transition border-b border-gray-700/50"
        >
            <span><i class="fas fa-user-tag mr-2"></i> Walk-in Customer (Default)</span>
            <span class="text-[10px] bg-indigo-900/60 text-indigo-300 px-1.5 py-0.5 rounded">Quick</span>
        </button>
        @endif

        @if($addNew)
        <button 
            type="button" 
            @click="triggerAddNew()" 
            class="w-full px-3 py-2 text-left hover:bg-gray-700 flex items-center text-xs font-bold text-green-400 transition border-b border-gray-700/50"
        >
            <i class="fas fa-plus-circle mr-2"></i> + Add New Customer
        </button>
        @endif

        <!-- Grouped Results -->
        <template x-for="group in getGroupedResults()" :key="group.letter">
            <div>
                <div class="px-3 py-1 bg-gray-900/90 text-[10px] font-black text-indigo-400 uppercase tracking-wider sticky top-0 border-y border-gray-700/60 backdrop-blur-sm flex justify-between items-center">
                    <span x-text="group.letter"></span>
                    <span class="text-[9px] text-gray-400 font-normal" x-text="group.items.length + (group.items.length === 1 ? ' match' : ' matches')"></span>
                </div>
                <template x-for="cust in group.items" :key="cust.id">
                    <button 
                        type="button" 
                        @click="selectCustomer(cust)" 
                        class="w-full px-3 py-2 text-left hover:bg-gray-700/80 transition flex flex-col justify-center text-xs group border-b border-gray-700/30"
                    >
                        <div class="flex justify-between items-center w-full">
                            <span class="font-bold text-gray-100 group-hover:text-indigo-300" x-text="cust.name"></span>
                            <span x-show="cust.phone" class="text-[11px] text-gray-400 font-mono" x-text="cust.phone"></span>
                        </div>
                        <div x-show="cust.address || cust.balance > 0" class="flex justify-between items-center text-[10px] text-gray-400 mt-0.5">
                            <span x-text="cust.address ? cust.address : ''" class="truncate max-w-[200px]"></span>
                            <span x-show="cust.balance > 0" class="text-amber-400 font-semibold" x-text="'Due: Rs.' + parseFloat(cust.balance).toFixed(2)"></span>
                        </div>
                    </button>
                </template>
            </div>
        </template>

        <div x-show="!loading && results.length === 0 && query.trim().length >= 1" class="p-3 text-center text-xs text-gray-400">
            No customers found for "<span x-text="query"></span>".
        </div>
    </div>
</div>
