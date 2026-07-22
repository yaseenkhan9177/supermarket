@extends('layouts.app')

@section('title', 'Unified Import — Customers, Suppliers, Staff & GL Accounts')

@section('content')

{{-- ===================================================================
     UNIFIED IMPORT — Single file upload, per-row type override, confirm
     =================================================================== --}}

<div class="max-w-7xl mx-auto">

    {{-- ── Page Header ──────────────────────────────────────────────── --}}
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center shadow-lg shadow-indigo-500/30">
                <i class="fas fa-file-import text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Unified Import</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Import Customers, Suppliers, Staff&nbsp;(profile only) and Chart of Accounts from a single file
                </p>
            </div>
        </div>
    </div>

    {{-- ── Stage indicator ──────────────────────────────────────────── --}}
    <div class="flex items-center gap-2 mb-6 text-sm font-medium" id="stage-indicator">
        <span id="ind-1"
              class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-600 text-white transition-all duration-300">
            <i class="fas fa-upload text-xs"></i> 1. Upload
        </span>
        <i class="fas fa-chevron-right text-slate-400"></i>
        <span id="ind-2"
              class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400 transition-all duration-300">
            <i class="fas fa-table text-xs"></i> 2. Preview &amp; Assign
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

            {{-- Type legend --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-8">
                @foreach ([
                    ['icon'=>'fa-user', 'color'=>'blue',   'label'=>'Customer',        'hint'=>'Name, Phone, Balance'],
                    ['icon'=>'fa-truck','color'=>'purple',  'label'=>'Supplier',         'hint'=>'Name, Code, Balance'],
                    ['icon'=>'fa-id-badge','color'=>'green','label'=>'Staff (Profile)',  'hint'=>'Name, Phone — no login'],
                    ['icon'=>'fa-book', 'color'=>'amber',   'label'=>'GL Account',       'hint'=>'Code, Type, Balance'],
                ] as $t)
                <div class="flex items-start gap-3 p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600">
                    <div class="w-8 h-8 rounded-lg bg-{{ $t['color'] }}-100 dark:bg-{{ $t['color'] }}-900/40 flex items-center justify-center flex-shrink-0">
                        <i class="fas {{ $t['icon'] }} text-{{ $t['color'] }}-600 dark:text-{{ $t['color'] }}-400 text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ $t['label'] }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $t['hint'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Drop zone --}}
            <div id="drop-zone"
                 class="relative border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-2xl p-12 text-center cursor-pointer
                        hover:border-indigo-400 hover:bg-indigo-50/30 dark:hover:bg-indigo-900/10 transition-all duration-200 group">
                <input type="file" id="csv-file" accept=".xls,.xlsx,.csv,.txt"
                       class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                <div class="pointer-events-none">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-indigo-100 dark:bg-indigo-900/40
                                flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-cloud-upload-alt text-3xl text-indigo-500"></i>
                    </div>
                    <p class="text-lg font-semibold text-slate-700 dark:text-slate-200 mb-1">Drop your file here</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">or click to browse</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">.xlsx, .xls, .csv supported — max 20 MB</p>
                </div>
                <div id="file-chosen" class="hidden mt-4 pointer-events-none">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-100 dark:bg-indigo-900/50
                                text-indigo-700 dark:text-indigo-300 rounded-full text-sm font-medium">
                        <i class="fas fa-file-excel"></i>
                        <span id="file-name-label">file.xlsx</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between mt-6">
                <a href="{{ route('import.sample') }}"
                   class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400
                          hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                    <i class="fas fa-download"></i> Download sample template
                </a>

                <button id="btn-parse"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700
                               text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-500/30
                               transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        disabled>
                    <i class="fas fa-search-plus" id="parse-icon"></i>
                    <span id="parse-label">Parse &amp; Preview</span>
                </button>
            </div>

            {{-- ── Chart of Accounts import shortcut ─────────────────────────── --}}
            <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
                <div class="flex items-center justify-between p-4 rounded-xl
                            bg-emerald-50 dark:bg-emerald-900/20
                            border border-emerald-200 dark:border-emerald-700/60">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-emerald-100 dark:bg-emerald-900/40
                                    flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-file-invoice text-emerald-600 dark:text-emerald-400"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                                Chart of Accounts Import
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Import from a legacy CSV using account-prefix routing
                                (01–14 → GL&nbsp;Accounts / Customers / Suppliers)
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('accounts.import.show') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700
                              text-white text-sm font-semibold rounded-lg shadow shadow-emerald-500/30
                              transition-all duration-200 whitespace-nowrap">
                        <i class="fas fa-arrow-right"></i> Open
                    </a>
                </div>
            </div>
        </div>
    </div>


    {{-- ═══════════════════════════════════════════════════════════════
         STAGE 2 — PREVIEW TABLE
         ═══════════════════════════════════════════════════════════════ --}}
    <div id="stage-preview" class="hidden">

        {{-- Summary banner --}}
        <div id="preview-banner"
             class="flex flex-wrap gap-3 mb-4 p-4 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
            <div class="flex items-center gap-2 text-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                <span class="text-slate-600 dark:text-slate-300" id="banner-total">0 rows parsed</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                <span class="text-slate-600 dark:text-slate-300"><span id="banner-customer">0</span> customers</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                <span class="text-slate-600 dark:text-slate-300"><span id="banner-supplier">0</span> suppliers</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-slate-600 dark:text-slate-300"><span id="banner-staff">0</span> staff</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                <span class="text-slate-600 dark:text-slate-300"><span id="banner-gl">0</span> GL accounts</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-400"></span>
                <span class="text-slate-600 dark:text-slate-300"><span id="banner-duplicates">0</span> duplicates</span>
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

        {{-- Staff safety notice (hidden until ≥1 staff row exists) --}}
        <div id="staff-notice" class="hidden mb-4 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-xl flex items-start gap-3">
            <i class="fas fa-shield-alt text-amber-500 mt-0.5"></i>
            <p class="text-sm text-amber-700 dark:text-amber-300">
                <strong>Staff import safety:</strong> Staff rows create <em>profile-only</em> Employee records
                with <code>password = null</code> and <code>is_active = false</code>.
                No login account, no system permissions, and no role is assigned.
                Login setup must be done manually by the owner afterward.
            </p>
        </div>

        {{-- Preview table --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700">
                        <tr>
                            <th class="px-3 py-3 text-left font-semibold text-slate-600 dark:text-slate-300 w-12">#</th>
                            <th class="px-3 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Name</th>
                            <th class="px-3 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Phone / Code</th>
                            <th class="px-3 py-3 text-left font-semibold text-slate-600 dark:text-slate-300 max-w-xs">Other Fields</th>
                            <th class="px-3 py-3 text-left font-semibold text-slate-600 dark:text-slate-300 w-44">
                                Record Type
                                <span class="ml-1 text-xs font-normal text-slate-400">(editable)</span>
                            </th>
                            <th class="px-3 py-3 text-left font-semibold text-slate-600 dark:text-slate-300 w-28">Status</th>
                        </tr>
                    </thead>
                    <tbody id="preview-tbody" class="divide-y divide-slate-100 dark:divide-slate-700">
                        {{-- Filled by JS --}}
                    </tbody>
                </table>
            </div>
        </div>
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
                    <p class="text-sm text-slate-500 dark:text-slate-400">Records have been written to the database</p>
                </div>
            </div>

            <div id="result-cards" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6"></div>

            <div id="result-errors" class="hidden">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    <i class="fas fa-exclamation-triangle text-amber-500 mr-1"></i> Skipped / Failed rows
                </h3>
                <ul id="result-errors-list" class="text-xs text-slate-600 dark:text-slate-400 space-y-1 max-h-48 overflow-y-auto"></ul>
            </div>

            <div class="mt-6 flex gap-3">
                <button id="btn-reset"
                        class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl transition">
                    <i class="fas fa-plus mr-1"></i> Import Another File
                </button>
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
   UNIFIED IMPORT — client-side controller
   ============================================================ */
(function () {
    'use strict';

    // ── State ────────────────────────────────────────────────
    let parsedRows  = [];
    let colMap      = {};

    // ── DOM refs ─────────────────────────────────────────────
    const fileInput    = document.getElementById('csv-file');
    const dropZone     = document.getElementById('drop-zone');
    const fileChosen   = document.getElementById('file-chosen');
    const fileLabel    = document.getElementById('file-name-label');
    const btnParse     = document.getElementById('btn-parse');
    const parseIcon    = document.getElementById('parse-icon');
    const parseLabel   = document.getElementById('parse-label');

    const stageUpload  = document.getElementById('stage-upload');
    const stagePreview = document.getElementById('stage-preview');
    const stageResults = document.getElementById('stage-results');

    const ind1 = document.getElementById('ind-1');
    const ind2 = document.getElementById('ind-2');
    const ind3 = document.getElementById('ind-3');

    const tbody        = document.getElementById('preview-tbody');
    const btnBack      = document.getElementById('btn-back');
    const btnCommit    = document.getElementById('btn-commit');
    const commitLabel  = document.getElementById('commit-label');
    const staffNotice  = document.getElementById('staff-notice');
    const btnReset     = document.getElementById('btn-reset');

    // ── Type colour config ───────────────────────────────────
    const TYPE_CONFIG = {
        customer:   { label: 'Customer',   badge: 'blue',   icon: 'fa-user'     },
        supplier:   { label: 'Supplier',   badge: 'purple', icon: 'fa-truck'    },
        staff:      { label: 'Staff',      badge: 'green',  icon: 'fa-id-badge' },
        gl_account: { label: 'GL Account', badge: 'amber',  icon: 'fa-book'     },
        skip:       { label: 'Skip',       badge: 'slate',  icon: 'fa-ban'      },
    };

    // ── File selection ───────────────────────────────────────
    fileInput.addEventListener('change', onFileSelected);

    dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('border-indigo-500'); });
    dropZone.addEventListener('dragleave', ()  => dropZone.classList.remove('border-indigo-500'));
    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('border-indigo-500');
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            onFileSelected();
        }
    });

    function onFileSelected() {
        if (!fileInput.files.length) return;
        const f = fileInput.files[0];
        fileLabel.textContent = f.name;
        fileChosen.classList.remove('hidden');
        btnParse.disabled = false;
    }

    // ── Parse & Preview ──────────────────────────────────────
    btnParse.addEventListener('click', async () => {
        if (!fileInput.files.length) return;

        btnParse.disabled = true;
        parseIcon.className  = 'fas fa-spinner fa-spin';
        parseLabel.textContent = 'Parsing…';

        const fd = new FormData();
        fd.append('file', fileInput.files[0]);
        fd.append('_token', getCsrfToken());

        try {
            const res  = await fetch('{{ route("import.preview") }}', { method: 'POST', body: fd });
            const data = await res.json();

            if (!res.ok) {
                showToast(data.message || 'Failed to parse file.', 'error');
                return;
            }

            parsedRows = data.rows;
            colMap     = data.col_map;

            renderPreview();
            goToStage(2);
        } catch (err) {
            showToast('Network error: ' + err.message, 'error');
        } finally {
            btnParse.disabled = false;
            parseIcon.className  = 'fas fa-search-plus';
            parseLabel.textContent = 'Parse & Preview';
        }
    });

    // ── Render preview table ─────────────────────────────────
    function renderPreview() {
        tbody.innerHTML = '';
        let counts = { customer: 0, supplier: 0, staff: 0, gl_account: 0, duplicates: 0 };

        parsedRows.forEach((row, idx) => {
            const cfg      = TYPE_CONFIG[row.suggested_type] || TYPE_CONFIG.customer;
            const isDupe   = row.is_duplicate;
            if (isDupe) counts.duplicates++;
            counts[row.suggested_type] = (counts[row.suggested_type] || 0) + 1;

            const tr = document.createElement('tr');
            tr.className = isDupe
                ? 'bg-rose-50/50 dark:bg-rose-900/10 hover:bg-rose-50 dark:hover:bg-rose-900/20'
                : 'hover:bg-slate-50 dark:hover:bg-slate-700/30';
            tr.dataset.idx = idx;

            tr.innerHTML = `
                <td class="px-3 py-2.5 text-xs text-slate-400 dark:text-slate-500 font-mono">${row.row}</td>
                <td class="px-3 py-2.5">
                    <p class="text-sm font-medium text-slate-800 dark:text-slate-100">${escHtml(row.name || '—')}</p>
                </td>
                <td class="px-3 py-2.5 text-sm text-slate-500 dark:text-slate-400 font-mono">
                    ${escHtml(row.phone || row.code || '—')}
                </td>
                <td class="px-3 py-2.5 text-xs text-slate-500 dark:text-slate-400 max-w-xs truncate" title="${escHtml(row.extras || '')}">
                    ${escHtml(row.extras || '')}
                </td>
                <td class="px-3 py-2.5">
                    <select class="type-select w-full text-xs rounded-lg border border-slate-300 dark:border-slate-600
                                   bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-2 py-1.5
                                   focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                            data-idx="${idx}">
                        ${Object.entries(TYPE_CONFIG).map(([v, c]) =>
                            `<option value="${v}" ${v === row.suggested_type ? 'selected' : ''}>${c.label}</option>`
                        ).join('')}
                    </select>
                </td>
                <td class="px-3 py-2.5">
                    ${isDupe
                        ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-400"
                                  title="${escHtml(row.duplicate_label || '')}">
                              <i class="fas fa-exclamation-triangle text-[10px]"></i> Duplicate
                           </span>`
                        : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400">
                              <i class="fas fa-check text-[10px]"></i> New
                           </span>`
                    }
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Listen for type changes
        tbody.querySelectorAll('.type-select').forEach(sel => {
            sel.addEventListener('change', e => {
                const idx = parseInt(e.target.dataset.idx);
                parsedRows[idx].suggested_type = e.target.value;
                updateBanner();
                updateStaffNotice();
            });
        });

        updateBanner(counts);
        updateStaffNotice();
    }

    function updateBanner(counts) {
        // Recount from current selections
        let c = { customer: 0, supplier: 0, staff: 0, gl_account: 0, duplicates: 0 };
        parsedRows.forEach(r => {
            c[r.suggested_type] = (c[r.suggested_type] || 0) + 1;
            if (r.is_duplicate) c.duplicates++;
        });
        document.getElementById('banner-total').textContent      = `${parsedRows.length} rows parsed`;
        document.getElementById('banner-customer').textContent   = c.customer;
        document.getElementById('banner-supplier').textContent   = c.supplier;
        document.getElementById('banner-staff').textContent      = c.staff;
        document.getElementById('banner-gl').textContent         = c.gl_account;
        document.getElementById('banner-duplicates').textContent = c.duplicates;
    }

    function updateStaffNotice() {
        const hasStaff = parsedRows.some(r => r.suggested_type === 'staff');
        staffNotice.classList.toggle('hidden', !hasStaff);
    }

    // ── Back button ──────────────────────────────────────────
    btnBack.addEventListener('click', () => goToStage(1));

    // ── Commit ───────────────────────────────────────────────
    btnCommit.addEventListener('click', async () => {
        const nonSkip = parsedRows.filter(r => r.suggested_type !== 'skip');
        if (!nonSkip.length) {
            showToast('All rows are set to Skip — nothing to import.', 'error');
            return;
        }

        // Build payload
        const rows = parsedRows.map(r => ({
            row:    r.row,
            type:   r.suggested_type,
            name:   r.name,
            phone:  r.phone,
            code:   r.code,
            _raw:   r._raw,
        }));

        commitLabel.textContent = 'Importing…';
        btnCommit.disabled = true;

        try {
            const res  = await fetch('{{ route("import.commit") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                body: JSON.stringify({ rows, col_map: colMap }),
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
            btnCommit.disabled = false;
        }
    });

    // ── Render results ───────────────────────────────────────
    function renderResults(summary) {
        const cards = document.getElementById('result-cards');
        cards.innerHTML = '';

        const types = [
            { key: 'customer',   label: 'Customers',   icon: 'fa-user',     color: 'blue' },
            { key: 'supplier',   label: 'Suppliers',   icon: 'fa-truck',    color: 'purple' },
            { key: 'staff',      label: 'Staff',       icon: 'fa-id-badge', color: 'emerald' },
            { key: 'gl_account', label: 'GL Accounts', icon: 'fa-book',     color: 'amber' },
        ];

        types.forEach(t => {
            const s = summary[t.key] || { inserted: 0, skipped: 0, failed: 0 };
            cards.innerHTML += `
                <div class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-7 h-7 rounded-lg bg-${t.color}-100 dark:bg-${t.color}-900/40 flex items-center justify-center">
                            <i class="fas ${t.icon} text-${t.color}-600 dark:text-${t.color}-400 text-xs"></i>
                        </div>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-200">${t.label}</span>
                    </div>
                    <div class="space-y-1 text-xs">
                        <div class="flex justify-between"><span class="text-slate-500">Inserted</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">${s.inserted}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Skipped</span>
                            <span class="font-bold text-amber-500">${s.skipped}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Failed</span>
                            <span class="font-bold text-rose-500">${s.failed}</span></div>
                    </div>
                </div>
            `;
        });

        // Collect all errors
        const errorsList = document.getElementById('result-errors-list');
        const errorsDiv  = document.getElementById('result-errors');
        errorsList.innerHTML = '';
        let hasErrors = false;

        ['customer', 'supplier', 'staff', 'gl_account'].forEach(key => {
            (summary[key]?.errors || []).forEach(msg => {
                hasErrors = true;
                const li = document.createElement('li');
                li.className = 'flex items-start gap-1.5';
                li.innerHTML = `<i class="fas fa-circle text-[6px] text-amber-400 mt-1.5 flex-shrink-0"></i>${escHtml(msg)}`;
                errorsList.appendChild(li);
            });
        });

        errorsDiv.classList.toggle('hidden', !hasErrors);
    }

    // ── Reset ────────────────────────────────────────────────
    btnReset.addEventListener('click', () => {
        parsedRows  = [];
        colMap      = {};
        fileInput.value = '';
        fileChosen.classList.add('hidden');
        btnParse.disabled = true;
        tbody.innerHTML   = '';
        goToStage(1);
    });

    // ── Stage transitions ────────────────────────────────────
    function goToStage(n) {
        stageUpload.classList.toggle('hidden',  n !== 1);
        stagePreview.classList.toggle('hidden', n !== 2);
        stageResults.classList.toggle('hidden', n !== 3);

        const activeClass   = 'bg-indigo-600 text-white';
        const inactiveClass = 'bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400';

        ind1.className = `flex items-center gap-1.5 px-3 py-1 rounded-full transition-all duration-300 ${n === 1 ? activeClass : inactiveClass}`;
        ind2.className = `flex items-center gap-1.5 px-3 py-1 rounded-full transition-all duration-300 ${n === 2 ? activeClass : inactiveClass}`;
        ind3.className = `flex items-center gap-1.5 px-3 py-1 rounded-full transition-all duration-300 ${n === 3 ? activeClass : inactiveClass}`;

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // ── Utilities ────────────────────────────────────────────
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content
            || '{{ csrf_token() }}';
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function showToast(msg, type = 'info') {
        const toast = document.createElement('div');
        const bg    = type === 'error' ? 'bg-rose-600' : 'bg-indigo-600';
        toast.className = `fixed bottom-6 right-6 z-50 px-5 py-3 ${bg} text-white text-sm rounded-xl shadow-xl
                           transform translate-y-0 opacity-100 transition-all duration-300`;
        toast.textContent = msg;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => toast.remove(), 400);
        }, 4000);
    }
})();
</script>
@endpush
