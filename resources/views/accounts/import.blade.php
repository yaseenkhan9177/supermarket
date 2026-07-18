@extends('layouts.app')

@section('title', 'Chart of Accounts — Bulk CSV Import')

@section('content')

{{-- ===================================================================
     CHART OF ACCOUNTS IMPORT
     Upload → Preview (grouped by target) → Results
     =================================================================== --}}

<div class="max-w-7xl mx-auto">

    {{-- ── Page Header ──────────────────────────────────────────────── --}}
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                <i class="fas fa-file-invoice text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Chart of Accounts Import</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Import from a legacy CSV — routes rows to GL Accounts, Customers, or Suppliers by account prefix
                </p>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('import.show') }}"
               class="inline-flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400
                      hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                <i class="fas fa-arrow-left"></i> Back to Unified Import
            </a>
        </div>
    </div>

    {{-- ── Stage indicator ──────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 mb-6 text-sm font-medium" id="stage-indicator">
        <span id="ind-1"
              class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-600 text-white transition-all duration-300">
            <i class="fas fa-upload text-xs"></i> 1. Upload
        </span>
        <i class="fas fa-chevron-right text-slate-400"></i>
        <span id="ind-2"
              class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400 transition-all duration-300">
            <i class="fas fa-table text-xs"></i> 2. Preview
        </span>
        <i class="fas fa-chevron-right text-slate-400"></i>
        <span id="ind-3"
              class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400 transition-all duration-300">
            <i class="fas fa-check-circle text-xs"></i> 3. Results
        </span>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         STAGE 1 — UPLOAD
         ═══════════════════════════════════════════════════════════════ --}}
    <div id="stage-upload">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">

            {{-- Prefix legend --}}
            <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                <i class="fas fa-sitemap mr-1 text-emerald-500"></i>
                Account Prefix → Destination Mapping
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-8 text-xs">
                @foreach([
                    ['01','Banks',        'gl',       'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300'],
                    ['02','Inventory',    'gl',       'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300'],
                    ['03','Other Assets', 'gl',       'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300'],
                    ['04','Fixed Assets', 'gl',       'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'],
                    ['05','Customers',    'customer', 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'],
                    ['06','Suppliers',    'supplier', 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'],
                    ['07','Equity',       'gl',       'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
                    ['08','Liabilities',  'gl',       'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300'],
                ] as [$pfx, $cat, $tgt, $cls])
                <div class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/40">
                    <span class="font-mono font-bold px-1.5 py-0.5 rounded {{ $cls }}">{{ $pfx }}</span>
                    <div class="leading-tight">
                        <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $cat }}</p>
                        <p class="text-slate-400">→
                            @if($tgt === 'gl') GL Accounts
                            @elseif($tgt === 'customer') Customers table
                            @else Suppliers table
                            @endif
                        </p>
                    </div>
                </div>
                @endforeach
                <div class="flex items-center gap-2 p-2 rounded-lg border border-dashed border-slate-300 dark:border-slate-600">
                    <span class="font-mono font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-400">09+</span>
                    <div class="leading-tight">
                        <p class="font-semibold text-slate-500 dark:text-slate-400">Unmapped</p>
                        <p class="text-slate-400">→ excluded</p>
                    </div>
                </div>
            </div>

            {{-- Expected CSV columns note --}}
            <div class="mb-6 p-3 bg-slate-50 dark:bg-slate-700/40 rounded-xl border border-slate-200 dark:border-slate-600 text-xs text-slate-600 dark:text-slate-400">
                <i class="fas fa-info-circle text-slate-400 mr-1"></i>
                <strong>Expected CSV columns:</strong>
                <code class="mx-1 px-1.5 py-0.5 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600">accountid</code>
                <code class="mx-1 px-1.5 py-0.5 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600">ac</code>
                <code class="mx-1 px-1.5 py-0.5 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600">name</code>
                — any additional legacy columns are ignored.
            </div>

            {{-- Drop zone --}}
            <div id="drop-zone"
                 class="relative border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-2xl p-12 text-center cursor-pointer
                        hover:border-emerald-400 hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10 transition-all duration-200 group">
                <input type="file" id="csv-file" accept=".xls,.xlsx,.csv,.txt"
                       class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                <div class="pointer-events-none">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-emerald-100 dark:bg-emerald-900/40
                                flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-cloud-upload-alt text-3xl text-emerald-500"></i>
                    </div>
                    <p class="text-lg font-semibold text-slate-700 dark:text-slate-200 mb-1">Drop your accounts CSV here</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">or click to browse</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">.xlsx, .xls, .csv supported — max 20 MB</p>
                </div>
                <div id="file-chosen" class="hidden mt-4 pointer-events-none">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-100 dark:bg-emerald-900/50
                                text-emerald-700 dark:text-emerald-300 rounded-full text-sm font-medium">
                        <i class="fas fa-file-csv"></i>
                        <span id="file-name-label">file.csv</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end mt-6">
                <button id="btn-parse"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700
                               text-white text-sm font-semibold rounded-xl shadow-md shadow-emerald-500/30
                               transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    <i class="fas fa-search-plus" id="parse-icon"></i>
                    <span id="parse-label">Parse &amp; Preview</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         STAGE 2 — PREVIEW
         ═══════════════════════════════════════════════════════════════ --}}
    <div id="stage-preview" class="hidden">

        {{-- Summary banner --}}
        <div id="preview-banner"
             class="flex flex-wrap items-center gap-4 mb-4 p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">

            <div class="flex items-center gap-2 text-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                <span class="text-slate-600 dark:text-slate-300" id="banner-total">0 rows</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-slate-600 dark:text-slate-300"><span id="banner-gl">0</span> GL Accounts</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                <span class="text-slate-600 dark:text-slate-300"><span id="banner-customers">0</span> Customers</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                <span class="text-slate-600 dark:text-slate-300"><span id="banner-suppliers">0</span> Suppliers</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
                <span class="text-slate-600 dark:text-slate-300"><span id="banner-unmapped">0</span> Unmapped</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                <span class="text-slate-600 dark:text-slate-300"><span id="banner-duplicates">0</span> Duplicates</span>
            </div>

            <div class="ml-auto flex items-center gap-2">
                <button id="btn-back"
                        class="px-4 py-1.5 text-sm text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </button>
                <button id="btn-commit"
                        class="inline-flex items-center gap-2 px-5 py-1.5 bg-emerald-600 hover:bg-emerald-700
                               text-white text-sm font-semibold rounded-lg shadow shadow-emerald-500/30 transition-all duration-200">
                    <i class="fas fa-check-circle"></i>
                    <span id="commit-label">Confirm &amp; Import</span>
                </button>
            </div>
        </div>

        {{-- Sections container (filled by JS) --}}
        <div id="sections-container" class="space-y-4"></div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         STAGE 3 — RESULTS
         ═══════════════════════════════════════════════════════════════ --}}
    <div id="stage-results" class="hidden">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-8">

            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-xl bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center">
                    <i class="fas fa-check-double text-2xl text-emerald-600 dark:text-emerald-400"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white">Import Complete</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Records have been written to their target tables</p>
                </div>
            </div>

            <div id="result-cards" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6"></div>

            <div id="result-errors" class="hidden mb-6">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    <i class="fas fa-exclamation-triangle text-amber-500 mr-1"></i> Skipped / Failed rows
                </h3>
                <ul id="result-errors-list" class="text-xs text-slate-600 dark:text-slate-400 space-y-1 max-h-48 overflow-y-auto
                                                   bg-slate-50 dark:bg-slate-700/40 rounded-xl p-3 border border-slate-200 dark:border-slate-600"></ul>
            </div>

            <div class="flex gap-3">
                <button id="btn-reset"
                        class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition">
                    <i class="fas fa-plus mr-1"></i> Import Another File
                </button>
                <a href="{{ route('general-ledger.index') }}"
                   class="px-5 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    View GL Accounts
                </a>
                <a href="{{ route('customers.index') }}"
                   class="px-5 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    View Customers
                </a>
                <a href="{{ route('suppliers.index') }}"
                   class="px-5 py-2 text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    View Suppliers
                </a>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
/* ============================================================
   CHART OF ACCOUNTS IMPORT — client-side controller
   ============================================================ */
(function () {
    'use strict';

    // ── State ────────────────────────────────────────────────
    /** @type {Array<Object>} */
    let parsedRows = [];

    // ── DOM refs ─────────────────────────────────────────────
    const fileInput   = document.getElementById('csv-file');
    const dropZone    = document.getElementById('drop-zone');
    const fileChosen  = document.getElementById('file-chosen');
    const fileLabel   = document.getElementById('file-name-label');
    const btnParse    = document.getElementById('btn-parse');
    const parseIcon   = document.getElementById('parse-icon');
    const parseLabel  = document.getElementById('parse-label');

    const stageUpload   = document.getElementById('stage-upload');
    const stagePreview  = document.getElementById('stage-preview');
    const stageResults  = document.getElementById('stage-results');

    const ind1 = document.getElementById('ind-1');
    const ind2 = document.getElementById('ind-2');
    const ind3 = document.getElementById('ind-3');

    const sectionsContainer = document.getElementById('sections-container');
    const btnBack           = document.getElementById('btn-back');
    const btnCommit         = document.getElementById('btn-commit');
    const commitLabel       = document.getElementById('commit-label');
    const btnReset          = document.getElementById('btn-reset');

    // ── Category config (mirrors PREFIX_CATEGORY in the controller) ─
    const ALL_CATEGORIES = [
        'Banks', 'Inventory', 'Other Assets', 'Fixed Assets',
        'Customers', 'Suppliers', 'Equity', 'Liabilities', 'Unmapped',
    ];

    const CATEGORY_BADGE = {
        'Banks':        'sky',
        'Inventory':    'teal',
        'Other Assets': 'violet',
        'Fixed Assets': 'indigo',
        'Customers':    'blue',
        'Suppliers':    'purple',
        'Equity':       'amber',
        'Liabilities':  'rose',
        'Unmapped':     'slate',
    };

    const TARGET_LABEL = {
        'gl':       '→ GL Accounts',
        'customer': '→ Customers',
        'supplier': '→ Suppliers',
        'unmapped': '→ Excluded',
    };

    // Section meta — which categories are grouped into each visual section
    const SECTIONS = [
        {
            id:         'sec-gl',
            title:      'General Ledger Accounts',
            icon:       'fa-book-open',
            color:      'emerald',
            targets:    ['gl'],
            categories: ['Banks','Inventory','Other Assets','Fixed Assets','Equity','Liabilities'],
        },
        {
            id:         'sec-customers',
            title:      'Customers',
            icon:       'fa-users',
            color:      'blue',
            targets:    ['customer'],
            categories: ['Customers'],
        },
        {
            id:         'sec-suppliers',
            title:      'Suppliers',
            icon:       'fa-truck',
            color:      'purple',
            targets:    ['supplier'],
            categories: ['Suppliers'],
        },
        {
            id:         'sec-unmapped',
            title:      'Unmapped / Excluded',
            icon:       'fa-ban',
            color:      'slate',
            targets:    ['unmapped'],
            categories: ['Unmapped'],
        },
    ];

    // ── File selection ───────────────────────────────────────
    fileInput.addEventListener('change', onFileSelected);

    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('border-emerald-500');
    });
    dropZone.addEventListener('dragleave', () => dropZone.classList.remove('border-emerald-500'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('border-emerald-500');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            onFileSelected();
        }
    });

    function onFileSelected() {
        if (!fileInput.files.length) return;
        fileLabel.textContent = fileInput.files[0].name;
        fileChosen.classList.remove('hidden');
        btnParse.disabled = false;
    }

    // ── Parse & Preview ──────────────────────────────────────
    btnParse.addEventListener('click', async () => {
        if (!fileInput.files.length) return;

        btnParse.disabled      = true;
        parseIcon.className    = 'fas fa-spinner fa-spin';
        parseLabel.textContent = 'Parsing…';

        const fd = new FormData();
        fd.append('file',   fileInput.files[0]);
        fd.append('_token', getCsrfToken());

        try {
            const res  = await fetch('{{ route("accounts.import.preview") }}', { method: 'POST', body: fd });
            const data = await res.json();

            if (!res.ok) {
                showToast(data.message || 'Failed to parse file.', 'error');
                return;
            }

            parsedRows = data.rows;
            renderPreview();
            goToStage(2);
        } catch (err) {
            showToast('Network error: ' + err.message, 'error');
        } finally {
            btnParse.disabled      = false;
            parseIcon.className    = 'fas fa-search-plus';
            parseLabel.textContent = 'Parse & Preview';
        }
    });

    // ── Render preview ───────────────────────────────────────
    function renderPreview() {
        sectionsContainer.innerHTML = '';
        updateBanner();

        SECTIONS.forEach(sec => {
            const sectionRows = parsedRows.filter(r => sec.targets.includes(r.target));
            if (!sectionRows.length) return;

            const isUnmapped = sec.id === 'sec-unmapped';
            const col        = sec.color;

            // Section wrapper
            const wrap = document.createElement('div');
            wrap.id        = sec.id;
            wrap.className = `bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden`;

            // Dynamic header columns based on section target type
            let headersHtml = '';
            if (sec.id === 'sec-customers') {
                headersHtml = `
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 w-44">Name</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Phone</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 w-44">Address</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 w-24">Credit Limit</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 w-24">Balance</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 w-24">Store Credit</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Category</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 w-36">Status / Action</th>
                `;
            } else {
                headersHtml = `
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">Account ID</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">AC Code</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">Name</th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 w-40">
                        Category <span class="font-normal text-slate-400">(editable)</span>
                    </th>
                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Status</th>
                `;
            }

            // Section header
            wrap.innerHTML = `
                <div class="flex items-center justify-between px-5 py-3
                             bg-${col}-50 dark:bg-${col}-900/20 border-b border-slate-200 dark:border-slate-700">
                    <div class="flex items-center gap-2">
                        <i class="fas ${sec.icon} text-${col}-600 dark:text-${col}-400"></i>
                        <span class="font-semibold text-slate-800 dark:text-white text-sm">${sec.title}</span>
                        <span class="ml-1 text-xs px-2 py-0.5 rounded-full
                                     bg-${col}-100 text-${col}-700 dark:bg-${col}-900/40 dark:text-${col}-300 font-medium">
                            ${sectionRows.length} row${sectionRows.length !== 1 ? 's' : ''}
                        </span>
                        ${isUnmapped ? `<span class="text-xs text-slate-400 dark:text-slate-500 ml-1">(excluded from import)</span>` : ''}
                    </div>
                    ${!isUnmapped ? `
                    <label class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 cursor-pointer select-none">
                        <input type="checkbox" class="sec-toggle rounded accent-${col}-600" data-sec="${sec.id}" checked>
                        Select all
                    </label>` : ''}
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-100 dark:border-slate-700">
                            <tr>
                                ${!isUnmapped ? `<th class="px-3 py-2 w-10 text-center">
                                    <i class="fas fa-check-square text-slate-400 text-xs"></i>
                                </th>` : `<th class="px-3 py-2 w-10"></th>`}
                                ${headersHtml}
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700" id="${sec.id}-tbody"></tbody>
                    </table>
                </div>
            `;

            sectionsContainer.appendChild(wrap);

            // Fill rows
            const tbody = wrap.querySelector(`#${sec.id}-tbody`);
            sectionRows.forEach(row => {
                const globalIdx = parsedRows.indexOf(row);
                const isDupe    = row.is_duplicate;
                const isUnmapRow = row.is_unmapped;

                const tr = document.createElement('tr');
                if (sec.id === 'sec-customers') {
                    tr.className = row.is_existing
                        ? 'bg-amber-50/70 dark:bg-amber-950/20 hover:bg-amber-100/70 dark:hover:bg-amber-950/30'
                        : 'hover:bg-slate-50 dark:hover:bg-slate-700/30';
                } else {
                    tr.className = isDupe
                        ? 'bg-rose-50/50 dark:bg-rose-900/10 hover:bg-rose-50 dark:hover:bg-rose-900/20'
                        : isUnmapRow
                            ? 'bg-slate-50/80 dark:bg-slate-800/80 opacity-60'
                            : 'hover:bg-slate-50 dark:hover:bg-slate-700/30';
                }
                tr.dataset.idx = globalIdx;

                // Category dropdown options — allow re-routing to any valid category
                const catOptions = ALL_CATEGORIES.map(c =>
                    `<option value="${c}" ${c === row.category ? 'selected' : ''}>${c}</option>`
                ).join('');

                // Badge colour
                const badgeCol = CATEGORY_BADGE[row.category] || 'slate';

                if (sec.id === 'sec-customers') {
                    // Customer inline editable fields structure
                    tr.innerHTML = `
                        <td class="px-3 py-2.5 text-center">
                            <input type="checkbox" class="row-check rounded accent-emerald-600"
                                   data-idx="${globalIdx}"
                                   ${row.included ? 'checked' : ''}>
                        </td>
                        <td class="px-3 py-2.5">
                            <input type="text" class="cust-name-input px-2 py-1 border border-slate-300 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-slate-850 dark:text-slate-100 text-xs w-full focus:ring-1 focus:ring-emerald-500 focus:border-transparent transition" data-idx="${globalIdx}" value="${escHtml(row.name || '')}">
                        </td>
                        <td class="px-3 py-2.5">
                            <input type="text" class="cust-phone-input px-2 py-1 border border-slate-300 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-slate-850 dark:text-slate-100 text-xs w-full focus:ring-1 focus:ring-emerald-500 focus:border-transparent transition" data-idx="${globalIdx}" value="${escHtml(row.phone || '')}">
                        </td>
                        <td class="px-3 py-2.5">
                            <input type="text" class="cust-address-input px-2 py-1 border border-slate-300 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-slate-850 dark:text-slate-100 text-xs w-full focus:ring-1 focus:ring-emerald-500 focus:border-transparent transition" data-idx="${globalIdx}" value="${escHtml(row.address || '')}">
                        </td>
                        <td class="px-3 py-2.5">
                            <input type="number" step="0.01" class="cust-credit-limit-input px-2 py-1 border border-slate-300 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-slate-850 dark:text-slate-100 text-xs w-full focus:ring-1 focus:ring-emerald-500 focus:border-transparent transition" data-idx="${globalIdx}" value="${row.credit_limit || 0}">
                        </td>
                        <td class="px-3 py-2.5">
                            <input type="number" step="0.01" class="cust-balance-input px-2 py-1 border border-slate-300 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-slate-850 dark:text-slate-100 text-xs w-full focus:ring-1 focus:ring-emerald-500 focus:border-transparent transition" data-idx="${globalIdx}" value="${row.balance || 0}">
                        </td>
                        <td class="px-3 py-2.5">
                            <input type="number" step="0.01" class="cust-store-credit-input px-2 py-1 border border-slate-300 dark:border-slate-600 rounded bg-white dark:bg-slate-700 text-slate-850 dark:text-slate-100 text-xs w-full focus:ring-1 focus:ring-emerald-500 focus:border-transparent transition" data-idx="${globalIdx}" value="${row.store_credit || 0}">
                        </td>
                        <td class="px-3 py-2.5">
                            <select class="cat-select w-full text-xs rounded-lg border border-slate-300 dark:border-slate-600
                                           bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-2 py-1.5
                                           focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                                    data-idx="${globalIdx}">
                                ${catOptions}
                            </select>
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap">
                            <div class="flex flex-col gap-1">
                                ${row.is_existing
                                    ? `<span class="inline-flex items-center justify-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-400">
                                           <i class="fas fa-user-edit"></i> Existing
                                       </span>`
                                    : `<span class="inline-flex items-center justify-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                                           <i class="fas fa-user-plus"></i> New
                                       </span>`
                                }
                                <select class="action-select w-full text-[10px] rounded border border-slate-300 dark:border-slate-600
                                               bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-1.5 py-1
                                               focus:ring-1 focus:ring-emerald-500 focus:border-transparent transition"
                                        data-idx="${globalIdx}">
                                    ${row.is_existing
                                        ? `<option value="update" ${row.import_action === 'update' ? 'selected' : ''}>Update</option>`
                                        : `<option value="create" ${row.import_action === 'create' ? 'selected' : ''}>Create</option>`
                                    }
                                    <option value="skip" ${row.import_action === 'skip' ? 'selected' : ''}>Skip</option>
                                </select>
                            </div>
                        </td>
                    `;
                } else {
                    // Standard GL/Supplier rows
                    tr.innerHTML = `
                        <td class="px-3 py-2.5 text-center">
                            <input type="checkbox" class="row-check rounded accent-emerald-600"
                                   data-idx="${globalIdx}"
                                   ${row.included ? 'checked' : ''}
                                   ${isUnmapRow ? 'disabled' : ''}>
                        </td>
                        <td class="px-3 py-2.5 font-mono text-xs text-slate-600 dark:text-slate-300 whitespace-nowrap">
                            ${escHtml(row.accountid || '—')}
                            <span class="ml-1 inline-block px-1 rounded text-[10px] font-bold
                                         bg-${badgeCol}-100 text-${badgeCol}-700 dark:bg-${badgeCol}-900/40 dark:text-${badgeCol}-300">
                                ${escHtml(row.prefix || '??')}
                            </span>
                        </td>
                        <td class="px-3 py-2.5 font-mono text-xs text-slate-500 dark:text-slate-400">${escHtml(row.ac || '—')}</td>
                        <td class="px-3 py-2.5 font-medium text-slate-800 dark:text-slate-100">
                            ${escHtml(row.name || '—')}
                        </td>
                        <td class="px-3 py-2.5">
                            <select class="cat-select w-full text-xs rounded-lg border border-slate-300 dark:border-slate-600
                                           bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-2 py-1.5
                                           focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                                    data-idx="${globalIdx}"
                                    ${isUnmapRow ? 'disabled' : ''}>
                                ${catOptions}
                            </select>
                        </td>
                        <td class="px-3 py-2.5 whitespace-nowrap">
                            ${isDupe
                                ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                              bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400"
                                            title="${escHtml(row.duplicate_label || '')}">
                                       <i class="fas fa-exclamation-triangle text-[10px]"></i> Duplicate
                                   </span>`
                                : isUnmapRow
                                    ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                                  bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400">
                                           <i class="fas fa-ban text-[10px]"></i> Excluded
                                       </span>`
                                    : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                                  bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                                           <i class="fas fa-check text-[10px]"></i> New
                                       </span>`
                            }
                        </td>
                    `;
                }

                tbody.appendChild(tr);

                // Customer inline editing input event listeners
                if (sec.id === 'sec-customers') {
                    tr.querySelector('.cust-name-input').addEventListener('input', e => {
                        parsedRows[globalIdx].name = e.target.value;
                    });
                    tr.querySelector('.cust-phone-input').addEventListener('input', e => {
                        parsedRows[globalIdx].phone = e.target.value;
                    });
                    tr.querySelector('.cust-address-input').addEventListener('input', e => {
                        parsedRows[globalIdx].address = e.target.value;
                    });
                    tr.querySelector('.cust-credit-limit-input').addEventListener('input', e => {
                        parsedRows[globalIdx].credit_limit = parseFloat(e.target.value) || 0;
                    });
                    tr.querySelector('.cust-balance-input').addEventListener('input', e => {
                        parsedRows[globalIdx].balance = parseFloat(e.target.value) || 0;
                    });
                    tr.querySelector('.cust-store-credit-input').addEventListener('input', e => {
                        parsedRows[globalIdx].store_credit = parseFloat(e.target.value) || 0;
                    });

                    const actSel = tr.querySelector('.action-select');
                    actSel.addEventListener('change', e => {
                        const action = e.target.value;
                        parsedRows[globalIdx].import_action = action;
                        if (action === 'skip') {
                            parsedRows[globalIdx].included = false;
                            tr.querySelector('.row-check').checked = false;
                        } else {
                            parsedRows[globalIdx].included = true;
                            tr.querySelector('.row-check').checked = true;
                        }
                        updateBanner();
                    });
                }
            });
        });

        // ── Wire section-level "select all" toggles ──────────────
        document.querySelectorAll('.sec-toggle').forEach(chk => {
            chk.addEventListener('change', e => {
                const secId    = e.target.dataset.sec;
                const section  = document.getElementById(secId);
                const rowChecks = section.querySelectorAll('.row-check:not([disabled])');
                rowChecks.forEach(rc => {
                    rc.checked = e.target.checked;
                    const idx  = parseInt(rc.dataset.idx);
                    parsedRows[idx].included = e.target.checked;

                    // Sync action select if Customer row
                    const actSel = rc.closest('tr').querySelector('.action-select');
                    if (actSel) {
                        const defaultAct = parsedRows[idx].is_existing ? 'update' : 'create';
                        const action = e.target.checked ? defaultAct : 'skip';
                        parsedRows[idx].import_action = action;
                        actSel.value = action;
                    }
                });
                updateBanner();
            });
        });

        // ── Wire per-row checkboxes ──────────────────────────────
        document.querySelectorAll('.row-check').forEach(chk => {
            chk.addEventListener('change', e => {
                const idx = parseInt(e.target.dataset.idx);
                parsedRows[idx].included = e.target.checked;

                // Sync action select if Customer row
                const actSel = e.target.closest('tr').querySelector('.action-select');
                if (actSel) {
                    const defaultAct = parsedRows[idx].is_existing ? 'update' : 'create';
                    const action = e.target.checked ? defaultAct : 'skip';
                    parsedRows[idx].import_action = action;
                    actSel.value = action;
                }

                updateBanner();
            });
        });

        // ── Wire per-row category dropdowns ─────────────────────
        document.querySelectorAll('.cat-select').forEach(sel => {
            sel.addEventListener('change', e => {
                const idx      = parseInt(e.target.dataset.idx);
                const newCat   = e.target.value;
                // Derive new target from new category
                const catToTarget = {
                    'Banks': 'gl', 'Inventory': 'gl', 'Other Assets': 'gl',
                    'Fixed Assets': 'gl', 'Equity': 'gl', 'Liabilities': 'gl',
                    'Customers': 'customer',
                    'Suppliers': 'supplier',
                    'Unmapped':  'unmapped',
                };
                parsedRows[idx].category = newCat;
                parsedRows[idx].target   = catToTarget[newCat] || 'unmapped';
                // If re-routed to unmapped, force uncheck
                if (parsedRows[idx].target === 'unmapped') {
                    parsedRows[idx].included = false;
                    const chk = document.querySelector(`.row-check[data-idx="${idx}"]`);
                    if (chk) { chk.checked = false; chk.disabled = true; }
                } else {
                    const chk = document.querySelector(`.row-check[data-idx="${idx}"]`);
                    if (chk) { chk.disabled = false; }
                }
                updateBanner();
            });
        });
    }

    // ── Banner counter update ────────────────────────────────
    function updateBanner() {
        let gl = 0, cust = 0, supp = 0, unmap = 0, dupes = 0;
        parsedRows.forEach(r => {
            const included = r.included && r.import_action !== 'skip';
            if (r.target === 'gl')       gl++;
            if (r.target === 'customer') cust++;
            if (r.target === 'supplier') supp++;
            if (r.target === 'unmapped') unmap++;
            if (r.is_duplicate || r.is_existing) dupes++;
        });
        document.getElementById('banner-total').textContent      = `${parsedRows.length} rows`;
        document.getElementById('banner-gl').textContent         = gl;
        document.getElementById('banner-customers').textContent  = cust;
        document.getElementById('banner-suppliers').textContent  = supp;
        document.getElementById('banner-unmapped').textContent   = unmap;
        document.getElementById('banner-duplicates').textContent = dupes;
    }

    // ── Back button ──────────────────────────────────────────
    btnBack.addEventListener('click', () => goToStage(1));

    // ── Commit ───────────────────────────────────────────────
    btnCommit.addEventListener('click', async () => {
        const includedRows = parsedRows.filter(r => r.included && r.target !== 'unmapped');
        if (!includedRows.length) {
            showToast('No rows selected for import — check at least one row.', 'error');
            return;
        }

        commitLabel.textContent = 'Importing…';
        btnCommit.disabled      = true;

        // Build the payload — only send fields the controller needs
        const payload = parsedRows.map(r => ({
            row:           r.row,
            accountid:     r.accountid,
            ac:            r.ac,
            name:          r.name,
            phone:         r.phone,
            address:       r.address,
            credit_limit:  r.credit_limit,
            balance:       r.balance,
            store_credit:  r.store_credit,
            category:      r.category,
            target:        r.target,
            included:      r.included,
            is_existing:   r.is_existing,
            customer_id:   r.customer_id,
            import_action: r.import_action,
        }));

        try {
            const res  = await fetch('{{ route("accounts.import.commit") }}', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                body:    JSON.stringify({ rows: payload }),
            });
            const data = await res.json();

            if (!res.ok) {
                showToast(data.message || 'Import failed.', 'error');
                return;
            }

            renderResults(data.summary);
            goToStage(3);
        } catch (err) {
            showToast('Network error: ' + err.message, 'error');
        } finally {
            commitLabel.textContent = 'Confirm & Import';
            btnCommit.disabled      = false;
        }
    });

    // ── Render results ───────────────────────────────────────
    function renderResults(summary) {
        const cards = document.getElementById('result-cards');
        cards.innerHTML = '';

        const types = [
            { key: 'gl',       label: 'GL Accounts', icon: 'fa-book-open', color: 'emerald' },
            { key: 'customer', label: 'Customers',   icon: 'fa-users',     color: 'blue'    },
            { key: 'supplier', label: 'Suppliers',   icon: 'fa-truck',     color: 'purple'  },
        ];

        types.forEach(t => {
            const s = summary[t.key] || { inserted: 0, skipped: 0, failed: 0 };
            cards.innerHTML += `
                <div class="p-5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-${t.color}-100 dark:bg-${t.color}-900/40 flex items-center justify-center">
                            <i class="fas ${t.icon} text-${t.color}-600 dark:text-${t.color}-400 text-sm"></i>
                        </div>
                        <span class="font-semibold text-slate-700 dark:text-slate-200">${t.label}</span>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Inserted</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400 text-lg">${s.inserted}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Skipped</span>
                            <span class="font-bold text-amber-500">${s.skipped}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Failed</span>
                            <span class="font-bold text-rose-500">${s.failed}</span>
                        </div>
                    </div>
                </div>
            `;
        });

        // Error list
        const errorsList = document.getElementById('result-errors-list');
        const errorsDiv  = document.getElementById('result-errors');
        errorsList.innerHTML = '';
        let hasErrors = false;

        ['gl', 'customer', 'supplier'].forEach(key => {
            (summary[key]?.errors || []).forEach(msg => {
                hasErrors = true;
                const li  = document.createElement('li');
                li.className = 'flex items-start gap-1.5 py-0.5';
                li.innerHTML = `<i class="fas fa-circle text-[6px] text-amber-400 mt-1.5 flex-shrink-0"></i>${escHtml(msg)}`;
                errorsList.appendChild(li);
            });
        });
        errorsDiv.classList.toggle('hidden', !hasErrors);
    }

    // ── Reset ────────────────────────────────────────────────
    btnReset.addEventListener('click', () => {
        parsedRows = [];
        fileInput.value = '';
        fileChosen.classList.add('hidden');
        btnParse.disabled = true;
        sectionsContainer.innerHTML = '';
        goToStage(1);
    });

    // ── Stage transitions ────────────────────────────────────
    function goToStage(n) {
        stageUpload.classList.toggle('hidden',  n !== 1);
        stagePreview.classList.toggle('hidden', n !== 2);
        stageResults.classList.toggle('hidden', n !== 3);

        const active   = 'bg-emerald-600 text-white';
        const inactive = 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400';
        ind1.className = `flex items-center gap-1.5 px-3 py-1 rounded-full transition-all duration-300 ${n === 1 ? active : inactive}`;
        ind2.className = `flex items-center gap-1.5 px-3 py-1 rounded-full transition-all duration-300 ${n === 2 ? active : inactive}`;
        ind3.className = `flex items-center gap-1.5 px-3 py-1 rounded-full transition-all duration-300 ${n === 3 ? active : inactive}`;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ── Utilities ────────────────────────────────────────────
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function showToast(msg, type = 'info') {
        const bg    = type === 'error' ? 'bg-rose-600' : 'bg-emerald-600';
        const toast = document.createElement('div');
        toast.className = `fixed bottom-6 right-6 z-50 px-5 py-3 ${bg} text-white text-sm rounded-xl shadow-xl
                           transform translate-y-0 opacity-100 transition-all duration-300`;
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity   = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => toast.remove(), 400);
        }, 4000);
    }

})();
</script>
@endpush
