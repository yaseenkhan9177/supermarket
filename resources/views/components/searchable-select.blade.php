@props([
    'name' => '',
    'id' => null,
    'label' => '',
    'placeholder' => 'Search or type...',
    'searchUrl' => '',
    'createUrl' => '',
    'valueKey' => 'id',
    'displayKey' => 'name',
    'selectedId' => '',
    'selectedName' => '',
    'required' => false,
    'entityLabel' => 'Option',
    'icon' => 'fa-search'
])

@php
    $elementId = $id ?? $name;
@endphp

<div x-data="{
    query: '{{ addslashes($selectedName) }}',
    selectedValue: '{{ addslashes($selectedId) }}',
    results: [],
    loading: false,
    isOpen: false,
    highlightedIndex: -1,
    showConfirmModal: false,
    pendingName: '',
    isCreating: false,
    debounceTimer: null,

    init() {
        this.fetchResults('');
    },

    onFocus() {
        this.isOpen = true;
        if (this.results.length === 0) {
            this.fetchResults(this.query);
        }
    },

    onInput() {
        clearTimeout(this.debounceTimer);
        this.highlightedIndex = -1;
        this.isOpen = true;
        this.debounceTimer = setTimeout(() => {
            this.fetchResults(this.query);
        }, 200);
    },

    fetchResults(q) {
        this.loading = true;
        fetch('{{ $searchUrl }}?q=' + encodeURIComponent((q || '').trim()))
            .then(res => res.json())
            .then(data => {
                this.results = data || [];
                this.loading = false;
            })
            .catch(err => {
                console.error('Search error:', err);
                this.loading = false;
            });
    },

    selectOption(opt) {
        if (!opt) return;
        this.selectedValue = opt['{{ $valueKey }}'];
        this.query = opt['{{ $displayKey }}'];
        this.isOpen = false;
        this.dispatchChange();
    },

    handleEnter() {
        const trimmed = this.query.trim();
        if (!trimmed) return;

        if (this.highlightedIndex >= 0 && this.results[this.highlightedIndex]) {
            this.selectOption(this.results[this.highlightedIndex]);
            return;
        }

        const exactMatch = this.results.find(r => 
            (r['{{ $displayKey }}'] || '').toString().toLowerCase() === trimmed.toLowerCase()
        );

        if (exactMatch) {
            this.selectOption(exactMatch);
            return;
        }

        this.pendingName = trimmed;
        this.isOpen = false;
        this.showConfirmModal = true;
    },

    triggerAddNew() {
        const trimmed = this.query.trim();
        if (!trimmed) return;
        this.pendingName = trimmed;
        this.isOpen = false;
        this.showConfirmModal = true;
    },

    confirmAdd() {
        if (!this.pendingName || this.isCreating) return;
        this.isCreating = true;
        
        const csrfToken = document.querySelector('meta[name=\'csrf-token\']')?.content 
            || document.querySelector('input[name=\'_token\']')?.value || '';

        fetch('{{ $createUrl }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ name: this.pendingName })
        })
        .then(async res => {
            if (!res.ok) {
                const errData = await res.json().catch(() => ({}));
                throw new Error(errData.message || 'Failed to create new {{ $entityLabel }}');
            }
            return res.json();
        })
        .then(newItem => {
            this.isCreating = false;
            this.showConfirmModal = false;
            this.results.push(newItem);
            this.selectOption(newItem);
            this.pendingName = '';
        })
        .catch(err => {
            this.isCreating = false;
            alert(err.message || 'Error saving new item');
        });
    },

    cancelAdd() {
        this.showConfirmModal = false;
        this.pendingName = '';
    },

    clearSelection() {
        this.selectedValue = '';
        this.query = '';
        this.results = [];
        this.fetchResults('');
        this.dispatchChange();
    },

    dispatchChange() {
        $nextTick(() => {
            const hidden = $refs.hiddenInput;
            if (hidden) {
                hidden.dispatchEvent(new Event('change', { bubbles: true }));
                hidden.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    },

    hasExactMatch() {
        if (!this.query.trim()) return true;
        return this.results.some(r => 
            (r['{{ $displayKey }}'] || '').toString().toLowerCase() === this.query.trim().toLowerCase()
        );
    }
}" 
class="relative"
@click.away="isOpen = false">

    <!-- Hidden Input for Form Submission -->
    <input type="hidden" name="{{ $name }}" id="{{ $elementId }}" x-model="selectedValue" x-ref="hiddenInput" @if($required) required @endif>

    @if($label)
        <label for="{{ $elementId }}_display" class="block text-xs font-bold text-slate-400 uppercase mb-1">
            {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <div class="relative">
        <input 
            type="text" 
            id="{{ $elementId }}_display"
            x-model="query" 
            @focus="onFocus()" 
            @input="onInput()" 
            @keydown.enter.prevent="handleEnter()"
            @keydown.arrow-down.prevent="highlightedIndex = Math.min(highlightedIndex + 1, results.length - 1)"
            @keydown.arrow-up.prevent="highlightedIndex = Math.max(highlightedIndex - 1, 0)"
            @keydown.escape="isOpen = false"
            placeholder="{{ $placeholder }}" 
            autocomplete="off"
            class="w-full bg-slate-950 border border-slate-700 rounded-lg pl-9 pr-8 py-2.5 text-white font-medium focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition text-sm">
        
        <i class="fas {{ $icon }} absolute left-3 top-3.5 text-slate-500 text-xs"></i>

        <template x-if="loading">
            <i class="fas fa-spinner fa-spin absolute right-3 top-3.5 text-blue-400 text-xs"></i>
        </template>

        <template x-if="!loading && query.length > 0">
            <button type="button" @click="clearSelection()" class="absolute right-3 top-3 text-slate-500 hover:text-slate-300 text-xs">
                <i class="fas fa-times"></i>
            </button>
        </template>
    </div>

    <!-- Dropdown List -->
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-100"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute z-40 mt-1 w-full bg-slate-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-60 overflow-y-auto divide-y divide-slate-800/60"
         style="display: none;">
        
        <template x-if="results.length > 0">
            <div>
                <template x-for="(item, index) in results" :key="item.id || index">
                    <div 
                        @click="selectOption(item)"
                        @mouseenter="highlightedIndex = index"
                        :class="{'bg-blue-600/30 text-blue-300 font-semibold': highlightedIndex === index || selectedValue == item['{{ $valueKey }}'], 'text-slate-200 hover:bg-slate-800/80': highlightedIndex !== index && selectedValue != item['{{ $valueKey }}']}"
                        class="px-4 py-2.5 cursor-pointer text-sm flex items-center justify-between transition">
                        <span x-text="item['{{ $displayKey }}']"></span>
                        <template x-if="selectedValue == item['{{ $valueKey }}']">
                            <i class="fas fa-check text-blue-400 text-xs"></i>
                        </template>
                    </div>
                </template>
            </div>
        </template>

        <!-- "Add New" option prompt inside dropdown if not exact match -->
        <template x-if="query.trim().length > 0 && !hasExactMatch()">
            <div 
                @click="triggerAddNew()"
                class="px-4 py-3 bg-blue-950/40 hover:bg-blue-900/60 text-blue-400 cursor-pointer text-sm font-semibold flex items-center gap-2 transition border-t border-slate-800">
                <i class="fas fa-plus-circle"></i>
                <span>Add "<strong x-text="query.trim()"></strong>" as new {{ $entityLabel }}</span>
            </div>
        </template>

        <template x-if="results.length === 0 && query.trim().length === 0 && !loading">
            <div class="px-4 py-3 text-slate-500 text-xs text-center italic">
                No existing {{ strtolower($entityLabel) }}s found. Type to add a new one.
            </div>
        </template>
    </div>

    <!-- Confirmation Modal Popup -->
    <template x-teleport="body">
        <div x-show="showConfirmModal" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             style="display: none;">
            
            <div class="bg-slate-900 border border-slate-800 rounded-2xl w-full max-w-md p-6 shadow-2xl relative overflow-hidden" @click.away="cancelAdd()">
                <div class="absolute top-0 left-0 w-full h-1 bg-blue-500"></div>

                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 text-xl flex-shrink-0">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div>
                        <h3 class="text-white font-bold text-lg">Add New {{ $entityLabel }}?</h3>
                        <p class="text-slate-400 text-sm mt-1">
                            "<strong class="text-slate-200" x-text="pendingName"></strong>" was not found in existing records. Would you like to add it as a new {{ strtolower($entityLabel) }}?
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-slate-800/80">
                    <button type="button" @click="cancelAdd()" class="px-4 py-2 rounded-lg border border-slate-700 text-slate-300 hover:bg-slate-800 font-bold text-sm transition">
                        Cancel
                    </button>
                    <button type="button" @click="confirmAdd()" :disabled="isCreating" class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow-lg shadow-blue-900/50 transition flex items-center gap-2">
                        <template x-if="isCreating">
                            <i class="fas fa-spinner fa-spin"></i>
                        </template>
                        <span>Yes, Add {{ $entityLabel }}</span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
