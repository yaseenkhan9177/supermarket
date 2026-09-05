@extends('layouts.admin')

@section('title', 'Chart of Accounts — Bulk CSV Import')

@section('content')

{{-- ===================================================================
     CHART OF ACCOUNTS / CUSTOMER IMPORT
     Upload → Preview (grouped by target + Duplicate Resolution) → Results
     =================================================================== --}}

<div class="max-w-7xl mx-auto">

    {{-- ── Page Header ──────────────────────────────────────────────── --}}
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-1">
            <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                <i class="fas fa-file-invoice text-white text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Account &amp; Customer Import</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Import accounts and customers from legacy CSV or Excel files with balance interpretation and duplicate safety
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
            <i class="fas fa-upload text-xs"></i> 1. Upload &amp; Balance Meaning
        </span>
        <i class="fas fa-chevron-right text-slate-400"></i>
        <span id="ind-2"
              class="flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400 transition-all duration-300">
            <i class="fas fa-table text-xs"></i> 2. Preview &amp; Duplicates
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
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 p-8 space-y-6">

            {{-- Prefix legend --}}
            <div>
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                    <i class="fas fa-sitemap mr-1 text-emerald-500"></i>
                    Account Prefix &rarr; Destination Mapping
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                    @foreach([
                        ['01','Banks',        'gl',       'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300'],
                        ['02','Inventory',    'gl',       'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300'],
                        ['03','Other Assets', 'gl',       'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300'],
                        ['04','Fixed Assets', 'gl',       'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'],
                        ['05','Customers',    'customer', 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'],
                        ['06','Suppliers',    'supplier', 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300'],
                        ['07','Equity',       'gl',       'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
                        ['08','Liabilities',  'gl',       'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300'],
                        ['09','Sales Income', 'gl',       'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'],
                        ['10','Services',     'gl',       'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300'],
                        ['11','Other Income', 'gl',       'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300'],
                        ['12','Cost of Sales','gl',       'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300'],
                        ['13','Expenses',     'gl',       'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900/40 dark:text-fuchsia-300'],
                        ['14','Employees',    'gl',       'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300'],
                    ] as [$pfx, $cat, $tgt, $cls])
                    <div class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/40">
                        <span class="font-mono font-bold px-1.5 py-0.5 rounded {{ $cls }}">{{ $pfx }}</span>
                        <div class="leading-tight">
                            <p class="font-semibold text-slate-700 dark:text-slate-200">{{ $cat }}</p>
                            <p class="text-slate-400">&rarr;
                                @if($tgt === 'gl') GL Accounts
                                @elseif($tgt === 'customer') Customers table
                                @else Suppliers table
                                @endif
                            </p>
                        </div>
                    </div>
                    @endforeach
                    <div class="flex items-center gap-2 p-2 rounded-lg border border-dashed border-slate-300 dark:border-slate-600">
                        <span class="font-mono font-bold px-1.5 py-0.5 rounded bg-slate-200 text-slate-500 dark:bg-slate-700 dark:text-slate-400">15+</span>
                        <div class="leading-tight">
                            <p class="font-semibold text-slate-500 dark:text-slate-400">Unmapped</p>
                            <p class="text-slate-400">&rarr; excluded</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── REQUIREMENT 1: Ask user about meaning of imported balance ── --}}
            <div class="p-6 rounded-2xl border-2 border-indigo-200 dark:border-indigo-900/50 bg-indigo-50/30 dark:bg-indigo-950/20">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/30">
                        <i class="fas fa-balance-scale text-base"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-white">
                            How should the imported balance be treated?
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Specify whether positive numbers in your file represent customer debt or advance payments / store credit.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Option A: Customer Owes Store --}}
                    <label id="card-owes"
                           class="relative flex flex-col p-5 rounded-2xl border-2 cursor-pointer transition-all duration-200
                                  border-indigo-600 bg-white dark:bg-slate-800 shadow-md ring-2 ring-indigo-500/20">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2.5">
                                <input type="radio" name="balance_treatment" value="customer_owes" checked
                                       class="w-4 h-4 text-indigo-600 focus:ring-indigo-500 accent-indigo-600">
                                <span class="font-bold text-slate-800 dark:text-white text-sm">Customer Owes Store</span>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300">
                                <i class="fas fa-arrow-down text-[9px]"></i> Debt / Receivable (+)
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mb-3">
                            The customer bought goods and will pay later.
                        </p>
                        <div class="mt-auto p-2.5 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-700 text-xs font-mono space-y-1">
                            <div class="flex justify-between items-center text-slate-500 dark:text-slate-400">
                                <span>File: <strong>500</strong> or <strong>-500</strong></span>
                                <span>&rarr;</span>
                                <span>DB: <strong class="text-red-600 dark:text-red-400 font-bold">+500</strong></span>
                            </div>
                            <p class="text-[11px] text-slate-400 dark:text-slate-400 font-sans">
                                abs(balance) saved as positive. Customer must pay the store.
                            </p>
                        </div>
                    </label>

                    {{-- Option B: Customer Has Paid / Store Owes Customer --}}
                    <label id="card-paid"
                           class="relative flex flex-col p-5 rounded-2xl border-2 cursor-pointer transition-all duration-200
                                  border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="flex items-center gap-2.5">
                                <input type="radio" name="balance_treatment" value="store_owes"
                                       class="w-4 h-4 text-emerald-600 focus:ring-emerald-500 accent-emerald-600">
                                <span class="font-bold text-slate-800 dark:text-white text-sm">Customer Has Paid / Store Owes Customer</span>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-extrabold uppercase rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                                <i class="fas fa-arrow-up text-[9px]"></i> Credit (-)
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 dark:text-slate-300 mb-3">
                            The customer has already paid more / has credit with the store.
                        </p>
                        <div class="mt-auto p-2.5 rounded-xl bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-700 text-xs font-mono space-y-1">
                            <div class="flex justify-between items-center text-slate-500 dark:text-slate-400">
                                <span>File: <strong>500</strong> or <strong>-500</strong></span>
                                <span>&rarr;</span>
                                <span>DB: <strong class="text-emerald-600 dark:text-emerald-400 font-bold">-500</strong></span>
                            </div>
                            <p class="text-[11px] text-slate-400 dark:text-slate-400 font-sans">
                                -abs(balance) saved as negative. Store owes customer 500.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Expected CSV columns note --}}
            <div class="p-3 bg-slate-50 dark:bg-slate-700/40 rounded-xl border border-slate-200 dark:border-slate-600 text-xs text-slate-600 dark:text-slate-400">
                <i class="fas fa-info-circle text-slate-400 mr-1"></i>
                <strong>Supported columns:</strong>
                <code class="mx-1 px-1.5 py-0.5 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600">accountid</code>
                <code class="mx-1 px-1.5 py-0.5 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600">ac</code>
                <code class="mx-1 px-1.5 py-0.5 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600">name (or Customer Name)</code>
                <code class="mx-1 px-1.5 py-0.5 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600">phone</code>
                <code class="mx-1 px-1.5 py-0.5 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600">balance (or Opening Balance, stbalance)</code>
                <code class="mx-1 px-1.5 py-0.5 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600">credit_limit</code>
                <code class="mx-1 px-1.5 py-0.5 bg-white dark:bg-slate-800 rounded border border-slate-200 dark:border-slate-600">address</code>
            </div>

            {{-- Drop zone --}}
            <div id="drop-zone"
                 class="relative border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-2xl p-10 text-center cursor-pointer
                        hover:border-emerald-400 hover:bg-emerald-50/30 dark:hover:bg-emerald-900/10 transition-all duration-200 group">
                <input type="file" id="csv-file" accept=".xls,.xlsx,.csv,.txt"
                       class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                <div class="pointer-events-none">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-emerald-100 dark:bg-emerald-900/40
                                flex items-center justify-center group-hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-cloud-upload-alt text-3xl text-emerald-500"></i>
                    </div>
                    <p class="text-lg font-semibold text-slate-700 dark:text-slate-200 mb-1">Drop your account / customer file here</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-2">or click to browse</p>
                    <p class="text-xs text-slate-400 dark:text-slate-500">.xlsx, .xls, .csv, .txt supported — max 20 MB</p>
                </div>
                <div id="file-chosen" class="hidden mt-4 pointer-events-none">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-100 dark:bg-emerald-900/50
                                text-emerald-700 dark:text-emerald-300 rounded-full text-sm font-medium">
                        <i class="fas fa-file-excel"></i>
                        <span id="file-name-label">file.csv</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end">
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
         STAGE 2 — PREVIEW & DUPLICATE CUSTOMER RESOLUTION
         ═══════════════════════════════════════════════════════════════ --}}
    <div id="stage-preview" class="hidden space-y-6">

        {{-- Summary banner --}}
        <div id="preview-banner"
             class="flex flex-wrap items-center justify-between gap-4 p-4 bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm">

            <div class="flex flex-wrap items-center gap-4 text-xs font-semibold">
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700/50">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                    <span class="text-slate-700 dark:text-slate-200" id="banner-total">0 rows</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                    <span><span id="banner-customers">0</span> Customers</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span><span id="banner-duplicates">0</span> Duplicates Found</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <span><span id="banner-gl">0</span> GL Accounts</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                    <span><span id="banner-suppliers">0</span> Suppliers</span>
                </div>
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700/50 text-slate-600 dark:text-slate-300">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                    <span><span id="banner-unmapped">0</span> Excluded</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button id="btn-back"
                        class="px-4 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    <i class="fas fa-arrow-left mr-1"></i> Back
                </button>
                <button id="btn-commit"
                        class="inline-flex items-center gap-2 px-6 py-2 bg-emerald-600 hover:bg-emerald-700
                               text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-500/30 transition-all duration-200">
                    <i class="fas fa-check-circle"></i>
                    <span id="commit-label">Confirm &amp; Import</span>
                </button>
            </div>
        </div>

        {{-- ── REQUIREMENT 2 & 3: Duplicate Customer Confirmation Panel ─ --}}
        <div id="duplicate-resolution-panel" class="hidden bg-amber-50/80 dark:bg-amber-950/30 border-2 border-amber-300 dark:border-amber-700/60 rounded-2xl p-6 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shadow-md shadow-amber-500/30 flex-shrink-0">
                        <i class="fas fa-user-check text-base"></i>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-800 dark:text-white flex items-center gap-2">
                            <span>Existing Customers Detected</span>
                            <span id="dupe-count-badge" class="px-2 py-0.5 text-xs rounded-full bg-amber-200 dark:bg-amber-900/60 text-amber-800 dark:text-amber-200 font-extrabold">0</span>
                        </h2>
                        <p class="text-xs text-slate-600 dark:text-slate-300">
                            These customers already exist in your database. For each customer, choose whether to <strong>ADD</strong> the imported balance to their existing balance, or <strong>NOT ADD</strong> (keep existing balance unchanged). Existing balances are never overwritten without your confirmation.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-shrink-0">
                    <button type="button" id="btn-set-all-add"
                            class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        <i class="fas fa-check-double mr-1"></i> Set All to ADD
                    </button>
                    <button type="button" id="btn-set-all-not-add"
                            class="px-3 py-1.5 bg-slate-600 hover:bg-slate-700 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        <i class="fas fa-ban mr-1"></i> Set All to NOT ADD
                    </button>
                </div>
            </div>

            {{-- Duplicate cards container --}}
            <div id="duplicate-cards-list" class="space-y-3 max-h-[420px] overflow-y-auto pr-1"></div>
        </div>

        {{-- Sections container (filled by JS: Customers table, GL accounts, Suppliers) --}}
        <div id="sections-container" class="space-y-6"></div>
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
                    <p class="text-sm text-slate-500 dark:text-slate-400">All confirmed records and customer balances have been saved safely.</p>
                </div>
            </div>

            {{-- Result Cards Grid --}}
            <div id="result-cards" class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6"></div>

            {{-- Skipped / Failed rows list --}}
            <div id="result-errors" class="hidden mb-6">
                <h3 class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    <i class="fas fa-exclamation-triangle text-amber-500 mr-1"></i> Skipped / Failed Rows
                </h3>
                <ul id="result-errors-list"
                    class="text-xs text-slate-600 dark:text-slate-400 space-y-1.5 max-h-48 overflow-y-auto
                           bg-slate-50 dark:bg-slate-700/40 rounded-xl p-4 border border-slate-200 dark:border-slate-600 font-mono"></ul>
            </div>

            <div class="flex flex-wrap gap-3">
                <button id="btn-reset"
                        class="px-5 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition shadow-md shadow-emerald-500/20">
                    <i class="fas fa-plus mr-1"></i> Import Another File
                </button>
                <a href="{{ route('customers.index') }}"
                   class="px-5 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    <i class="fas fa-users mr-1 text-blue-500"></i> View Customers
                </a>
                <a href="{{ route('general-ledger.index') }}"
                   class="px-5 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    <i class="fas fa-book mr-1 text-emerald-500"></i> View GL Accounts
                </a>
                <a href="{{ route('suppliers.index') }}"
                   class="px-5 py-2.5 text-xs font-bold text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition">
                    <i class="fas fa-truck mr-1 text-purple-500"></i> View Suppliers
                </a>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
/* ============================================================
   ACCOUNT & CUSTOMER IMPORT — CLIENT-SIDE CONTROLLER
   ============================================================ */
(function () {
    'use strict';

    // ── State ────────────────────────────────────────────────
    let parsedRows = [];
    let currentBalanceTreatment = 'customer_owes'; // 'customer_owes' | 'store_owes'

    // ── DOM refs ─────────────────────────────────────────────
    const fileInput   = document.getElementById('csv-file');
    const dropZone    = document.getElementById('drop-zone');
    const fileChosen  = document.getElementById('file-chosen');
    const fileLabel   = document.getElementById('file-name-label');
    const btnParse    = document.getElementById('btn-parse');
    const parseIcon   = document.getElementById('parse-icon');
    const parseLabel  = document.getElementById('parse-label');

    const cardOwes    = document.getElementById('card-owes');
    const cardPaid    = document.getElementById('card-paid');

    const stageUpload   = document.getElementById('stage-upload');
    const stagePreview  = document.getElementById('stage-preview');
    const stageResults  = document.getElementById('stage-results');

    const ind1 = document.getElementById('ind-1');
    const ind2 = document.getElementById('ind-2');
    const ind3 = document.getElementById('ind-3');

    const duplicatePanel      = document.getElementById('duplicate-resolution-panel');
    const duplicateCardsList  = document.getElementById('duplicate-cards-list');
    const dupeCountBadge      = document.getElementById('dupe-count-badge');
    const btnSetAllAdd        = document.getElementById('btn-set-all-add');
    const btnSetAllNotAdd     = document.getElementById('btn-set-all-not-add');

    const sectionsContainer = document.getElementById('sections-container');
    const btnBack           = document.getElementById('btn-back');
    const btnCommit         = document.getElementById('btn-commit');
    const commitLabel       = document.getElementById('commit-label');
    const btnReset          = document.getElementById('btn-reset');

    // ── Category config (mirrors PREFIX_CATEGORY in controller) ─
    const ALL_CATEGORIES = [
        'Banks', 'Inventory', 'Other Assets', 'Fixed Assets',
        'Customers', 'Suppliers', 'Equity', 'Liabilities',
        'Sales Income', 'Services', 'Other Income', 'Cost of Sales',
        'Expenses', 'Employees', 'Unmapped',
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
        'Sales Income': 'emerald',
        'Services':     'cyan',
        'Other Income': 'sky',
        'Cost of Sales': 'orange',
        'Expenses':     'fuchsia',
        'Employees':    'pink',
        'Unmapped':     'slate',
    };

    const SECTIONS = [
        {
            id:         'sec-customers',
            title:      'Customers',
            icon:       'fa-users',
            color:      'blue',
            targets:    ['customer'],
            categories: ['Customers'],
        },
        {
            id:         'sec-gl',
            title:      'General Ledger Accounts',
            icon:       'fa-book-open',
            color:      'emerald',
            targets:    ['gl'],
            categories: [
                'Banks', 'Inventory', 'Other Assets', 'Fixed Assets',
                'Equity', 'Liabilities', 'Sales Income', 'Services',
                'Other Income', 'Cost of Sales', 'Expenses', 'Employees'
            ],
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

    // ── Balance Meaning Card Selection ───────────────────────
    document.querySelectorAll('input[name="balance_treatment"]').forEach(radio => {
        radio.addEventListener('change', e => {
            currentBalanceTreatment = e.target.value;
            updateBalanceTreatmentCards();
        });
    });

    function updateBalanceTreatmentCards() {
        if (currentBalanceTreatment === 'customer_owes') {
            cardOwes.className = 'relative flex flex-col p-5 rounded-2xl border-2 cursor-pointer transition-all duration-200 border-indigo-600 bg-white dark:bg-slate-800 shadow-md ring-2 ring-indigo-500/20';
            cardPaid.className = 'relative flex flex-col p-5 rounded-2xl border-2 cursor-pointer transition-all duration-200 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600';
            cardOwes.querySelector('input').checked = true;
        } else {
            cardPaid.className = 'relative flex flex-col p-5 rounded-2xl border-2 cursor-pointer transition-all duration-200 border-emerald-600 bg-white dark:bg-slate-800 shadow-md ring-2 ring-emerald-500/20';
            cardOwes.className = 'relative flex flex-col p-5 rounded-2xl border-2 cursor-pointer transition-all duration-200 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 hover:border-slate-300 dark:hover:border-slate-600';
            cardPaid.querySelector('input').checked = true;
        }
    }

    // ── File Selection ───────────────────────────────────────
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
        fd.append('file', fileInput.files[0]);
        fd.append('balance_treatment', currentBalanceTreatment);
        fd.append('_token', getCsrfToken());

        let data;
        try {
            const res  = await fetch('{{ route("accounts.import.preview") }}', { method: 'POST', body: fd });
            data = await res.json();

            if (!res.ok) {
                showToast(data.message || 'Failed to parse file.', 'error');
                return;
            }
        } catch (err) {
            showToast('Network error: ' + (err.message || 'Failed to reach server.'), 'error');
            return;
        }

        try {
            parsedRows = data.rows || [];
            if (data.balance_treatment) {
                currentBalanceTreatment = data.balance_treatment;
            }

            renderPreview();
            goToStage(2);
        } catch (err) {
            console.error('Preview error:', err);
            showToast('Preview error: ' + err.message, 'error');
        } finally {
            btnParse.disabled      = false;
            parseIcon.className    = 'fas fa-search-plus';
            parseLabel.textContent = 'Parse & Preview';
        }
    });

    // ── Balance recalculation helper ─────────────────────────
    // Uses abs()-based rule: the sign in the uploaded file is ignored.
    // customer_owes  → balance = +abs(raw_balance)  (positive = red = owes store)
    // store_owes     → balance = -abs(raw_balance)  (negative = green = store owes customer)
    function recalculateBalances() {
        parsedRows.forEach(r => {
            if (r.target === 'customer') {
                const raw = r.raw_balance !== undefined ? r.raw_balance : r.balance;
                const absRaw = Math.abs(parseFloat(raw) || 0);
                r.balance = (currentBalanceTreatment === 'store_owes') ? -absRaw : absRaw;
                if (r.is_existing) {
                    r.final_balance_add    = round2((parseFloat(r.existing_balance) || 0) + r.balance);
                    r.final_balance_not_add = r.existing_balance;
                } else {
                    r.final_balance_add    = r.balance;
                    r.final_balance_not_add = 0;
                }
            }
        });
    }

    // ── Render Preview ───────────────────────────────────────
    function renderPreview() {
        sectionsContainer.innerHTML = '';
        renderDuplicateResolutionPanel();
        updateBanner();

        SECTIONS.forEach(sec => {
            const sectionRows = parsedRows.filter(r => sec.targets.includes(r.target));
            if (!sectionRows.length) return;

            const isUnmapped = sec.id === 'sec-unmapped';
            const col        = sec.color;

            const wrap = document.createElement('div');
            wrap.id        = sec.id;
            wrap.className = 'bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden';

            let headersHtml = '';
            if (sec.id === 'sec-customers') {
                headersHtml = `
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">Customer Name</th>
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">Phone / Email</th>
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">Address</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Existing Balance</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Imported Balance</th>
                    <th class="px-3 py-2.5 text-center text-xs font-semibold text-slate-500 dark:text-slate-400 w-44">Action / Status</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Final Balance</th>
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 w-32">Category</th>
                `;
            } else {
                headersHtml = `
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">Account ID</th>
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">AC Code</th>
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400">Name</th>
                    <th class="px-3 py-2.5 text-right text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Balance</th>
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 w-40">Category</th>
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-500 dark:text-slate-400 w-28">Status</th>
                `;
            }

            // Section Header
            wrap.innerHTML = `
                <div class="flex flex-wrap items-center justify-between px-5 py-3.5
                             bg-${col}-50/60 dark:bg-${col}-900/20 border-b border-slate-200 dark:border-slate-700 gap-2">
                    <div class="flex items-center gap-2">
                        <i class="fas ${sec.icon} text-${col}-600 dark:text-${col}-400"></i>
                        <span class="font-bold text-slate-800 dark:text-white text-sm">${sec.title}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-${col}-100 text-${col}-700 dark:bg-${col}-900/40 dark:text-${col}-300 font-bold">
                            ${sectionRows.length} row${sectionRows.length !== 1 ? 's' : ''}
                        </span>
                        ${sec.id === 'sec-customers' ? `
                            <span class="ml-2 inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold ${
                                currentBalanceTreatment === 'customer_owes'
                                    ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/50 dark:text-indigo-300'
                                    : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300'
                            }">
                                Treatment: ${currentBalanceTreatment === 'customer_owes' ? 'Customer Owes Store (+)' : 'Customer Has Paid (-)'}
                            </span>
                        ` : ''}
                    </div>
                    ${!isUnmapped ? `
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-300 cursor-pointer select-none font-medium">
                            <input type="checkbox" class="sec-toggle rounded accent-${col}-600" data-sec="${sec.id}" checked>
                            Select all
                        </label>
                    </div>` : ''}
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
                tr.dataset.idx = globalIdx;

                if (sec.id === 'sec-customers') {
                    tr.className = row.is_existing
                        ? 'bg-amber-50/50 dark:bg-amber-950/20 hover:bg-amber-100/50 dark:hover:bg-amber-950/40 transition'
                        : 'hover:bg-slate-50 dark:hover:bg-slate-700/30 transition';

                    const catOptions = ALL_CATEGORIES.map(c =>
                        `<option value="${c}" ${c === row.category ? 'selected' : ''}>${c}</option>`
                    ).join('');

                    const finalBal = (row.is_existing && row.duplicate_action === 'not_add')
                        ? row.existing_balance
                        : (row.is_existing ? row.final_balance_add : row.balance);

                    tr.innerHTML = `
                        <td class="px-3 py-2.5 text-center">
                            <input type="checkbox" class="row-check rounded accent-emerald-600"
                                   data-idx="${globalIdx}" ${row.included ? 'checked' : ''}>
                        </td>
                        <td class="px-3 py-2.5">
                            <div class="font-bold text-slate-800 dark:text-slate-100 text-xs">${escHtml(row.name || '—')}</div>
                            ${row.is_existing ? `<span class="inline-flex items-center gap-1 text-[10px] text-amber-600 dark:text-amber-400 font-bold"><i class="fas fa-user-check"></i> Existing Customer</span>` : `<span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold"><i class="fas fa-user-plus"></i> New Customer</span>`}
                        </td>
                        <td class="px-3 py-2.5 text-xs text-slate-600 dark:text-slate-300">
                            <div>${escHtml(row.phone || row.existing_phone || '—')}</div>
                            ${row.email || row.existing_email ? `<div class="text-[11px] text-slate-400">${escHtml(row.email || row.existing_email)}</div>` : ''}
                        </td>
                        <td class="px-3 py-2.5 text-xs text-slate-500 dark:text-slate-400 max-w-xs truncate" title="${escHtml(row.address || '')}">
                            ${escHtml(row.address || '—')}
                        </td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs">
                            ${row.is_existing ? formatBalance(row.existing_balance) : '<span class="text-slate-400">—</span>'}
                        </td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs font-bold">
                            ${formatBalance(row.balance)}
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            ${row.is_existing ? `
                                <div class="inline-flex items-center p-1 rounded-xl bg-slate-100 dark:bg-slate-700/60 border border-slate-200 dark:border-slate-600 gap-1">
                                    <button type="button" class="btn-dupe-action px-2.5 py-1 rounded-lg text-xs font-extrabold transition ${
                                        (row.duplicate_action !== 'not_add')
                                            ? 'bg-emerald-600 text-white shadow-sm'
                                            : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                                    }" data-idx="${globalIdx}" data-action="add">
                                        <i class="fas fa-plus text-[10px] mr-0.5"></i> ADD
                                    </button>
                                    <button type="button" class="btn-dupe-action px-2.5 py-1 rounded-lg text-xs font-extrabold transition ${
                                        (row.duplicate_action === 'not_add')
                                            ? 'bg-slate-800 dark:bg-slate-900 text-white shadow-sm'
                                            : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                                    }" data-idx="${globalIdx}" data-action="not_add">
                                        <i class="fas fa-ban text-[10px] mr-0.5"></i> NOT ADD
                                    </button>
                                </div>
                            ` : `
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                                    <i class="fas fa-plus-circle text-[10px]"></i> Create New
                                </span>
                            `}
                        </td>
                        <td class="px-3 py-2.5 text-right font-mono text-xs font-extrabold final-bal-cell" id="final-bal-${globalIdx}">
                            ${formatBalance(finalBal)}
                            ${row.is_existing && row.duplicate_action === 'not_add' ? '<span class="block text-[10px] text-slate-400 font-sans font-normal">(Unchanged)</span>' : ''}
                        </td>
                        <td class="px-3 py-2.5">
                            <select class="cat-select w-full text-xs rounded-lg border border-slate-300 dark:border-slate-600
                                           bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-2 py-1.5"
                                    data-idx="${globalIdx}">
                                ${catOptions}
                            </select>
                        </td>
                    `;
                } else {
                    // Standard GL & Supplier rows
                    const catOptions = ALL_CATEGORIES.map(c =>
                        `<option value="${c}" ${c === row.category ? 'selected' : ''}>${c}</option>`
                    ).join('');
                    const badgeCol = CATEGORY_BADGE[row.category] || 'slate';

                    tr.className = isDupe
                        ? 'bg-rose-50/50 dark:bg-rose-900/10 hover:bg-rose-50 dark:hover:bg-rose-900/20 transition'
                        : isUnmapRow
                            ? 'bg-slate-50/80 dark:bg-slate-800/80 opacity-60'
                            : 'hover:bg-slate-50 dark:hover:bg-slate-700/30 transition';

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
                        <td class="px-3 py-2.5 text-right font-mono text-xs font-bold text-slate-700 dark:text-slate-200">
                            ${row.balance !== undefined && row.balance !== null ? 'Rs. ' + parseFloat(row.balance).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '—'}
                        </td>
                        <td class="px-3 py-2.5">
                            <select class="cat-select w-full text-xs rounded-lg border border-slate-300 dark:border-slate-600
                                           bg-white dark:bg-slate-700 text-slate-700 dark:text-slate-200 px-2 py-1.5"
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
            });
        });

        wireEventListeners();
    }

    // ── Render Duplicate Resolution Panel ────────────────────
    function renderDuplicateResolutionPanel() {
        const duplicateCustomers = parsedRows.filter(r => r.target === 'customer' && r.is_existing);
        if (!duplicateCustomers.length) {
            duplicatePanel.classList.add('hidden');
            return;
        }

        duplicatePanel.classList.remove('hidden');
        dupeCountBadge.textContent = duplicateCustomers.length;
        duplicateCardsList.innerHTML = '';

        duplicateCustomers.forEach(row => {
            const globalIdx = parsedRows.indexOf(row);
            const isAdd = row.duplicate_action !== 'not_add';
            const finalBal = isAdd ? row.final_balance_add : row.existing_balance;

            const card = document.createElement('div');
            card.id = `dupe-card-${globalIdx}`;
            card.className = `p-4 rounded-xl border bg-white dark:bg-slate-800 shadow-sm transition flex flex-col md:flex-row md:items-center justify-between gap-4 ${
                isAdd ? 'border-emerald-300 dark:border-emerald-800' : 'border-slate-300 dark:border-slate-700'
            }`;

            card.innerHTML = `
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="font-bold text-slate-800 dark:text-white text-sm">${escHtml(row.name || '—')}</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">Existing Customer</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                        ${row.phone || row.existing_phone ? `<span><i class="fas fa-phone-alt text-[10px] mr-1 text-slate-400"></i>${escHtml(row.phone || row.existing_phone)}</span>` : ''}
                        ${row.email || row.existing_email ? `<span><i class="fas fa-envelope text-[10px] mr-1 text-slate-400"></i>${escHtml(row.email || row.existing_email)}</span>` : ''}
                        ${row.address ? `<span><i class="fas fa-map-marker-alt text-[10px] mr-1 text-slate-400"></i>${escHtml(row.address)}</span>` : ''}
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 text-xs font-mono">
                    <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 text-right">
                        <span class="text-[10px] text-slate-400 block font-sans">Existing Balance</span>
                        <strong>${formatBalance(row.existing_balance)}</strong>
                    </div>
                    <div class="text-slate-400 font-sans font-bold text-sm">+</div>
                    <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600 text-right">
                        <span class="text-[10px] text-slate-400 block font-sans">Imported Balance</span>
                        <strong>${formatBalance(row.balance)}</strong>
                    </div>
                    <div class="text-slate-400 font-sans font-bold text-sm">=</div>

                    {{-- Actions: ADD or NOT ADD --}}
                    <div class="flex items-center gap-1.5 p-1 rounded-xl bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600">
                        <button type="button" class="btn-dupe-action px-3 py-1.5 rounded-lg text-xs font-bold transition ${
                            isAdd ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                        }" data-idx="${globalIdx}" data-action="add">
                            <i class="fas fa-plus text-[10px] mr-1"></i> ADD
                        </button>
                        <button type="button" class="btn-dupe-action px-3 py-1.5 rounded-lg text-xs font-bold transition ${
                            !isAdd ? 'bg-slate-800 dark:bg-slate-900 text-white shadow-sm' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-600'
                        }" data-idx="${globalIdx}" data-action="not_add">
                            <i class="fas fa-ban text-[10px] mr-1"></i> NOT ADD
                        </button>
                    </div>

                    {{-- Resulting Final Balance preview --}}
                    <div class="p-2 rounded-lg ${isAdd ? 'bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-300 dark:border-emerald-800' : 'bg-slate-50 dark:bg-slate-700/50 border border-slate-200 dark:border-slate-600'} text-right min-w-[110px]">
                        <span class="text-[10px] text-slate-400 block font-sans">Final Balance</span>
                        <span class="font-extrabold" id="dupe-card-final-${globalIdx}">${formatBalance(finalBal)}</span>
                        ${!isAdd ? '<span class="block text-[9px] text-slate-400 font-sans font-normal">(Unchanged)</span>' : ''}
                    </div>
                </div>
            `;

            duplicateCardsList.appendChild(card);
        });
    }

    // ── Wire event listeners ─────────────────────────────────
    function wireEventListeners() {
        // Wire ADD / NOT ADD buttons
        document.querySelectorAll('.btn-dupe-action').forEach(btn => {
            btn.addEventListener('click', e => {
                const idx    = parseInt(btn.dataset.idx);
                const action = btn.dataset.action; // 'add' or 'not_add'
                setDuplicateAction(idx, action);
            });
        });

        // Wire section toggles
        document.querySelectorAll('.sec-toggle').forEach(chk => {
            chk.addEventListener('change', e => {
                const secId     = e.target.dataset.sec;
                const section   = document.getElementById(secId);
                const rowChecks = section.querySelectorAll('.row-check:not([disabled])');
                rowChecks.forEach(rc => {
                    rc.checked = e.target.checked;
                    const idx  = parseInt(rc.dataset.idx);
                    parsedRows[idx].included = e.target.checked;
                    if (!e.target.checked) {
                        parsedRows[idx].import_action = 'skip';
                    } else {
                        parsedRows[idx].import_action = parsedRows[idx].is_existing ? 'update' : 'create';
                    }
                });
                updateBanner();
            });
        });

        // Wire per-row checkboxes
        document.querySelectorAll('.row-check').forEach(chk => {
            chk.addEventListener('change', e => {
                const idx = parseInt(e.target.dataset.idx);
                parsedRows[idx].included = e.target.checked;
                parsedRows[idx].import_action = e.target.checked
                    ? (parsedRows[idx].is_existing ? 'update' : 'create')
                    : 'skip';
                updateBanner();
            });
        });

        // Wire category dropdowns
        document.querySelectorAll('.cat-select').forEach(sel => {
            sel.addEventListener('change', e => {
                const idx    = parseInt(e.target.dataset.idx);
                const newCat = e.target.value;
                const catToTarget = {
                    'Banks': 'gl', 'Inventory': 'gl', 'Other Assets': 'gl',
                    'Fixed Assets': 'gl', 'Equity': 'gl', 'Liabilities': 'gl',
                    'Sales Income': 'gl', 'Services': 'gl', 'Other Income': 'gl',
                    'Cost of Sales': 'gl', 'Expenses': 'gl', 'Employees': 'gl',
                    'Customers': 'customer',
                    'Suppliers': 'supplier',
                    'Unmapped':  'unmapped',
                };
                parsedRows[idx].category = newCat;
                parsedRows[idx].target   = catToTarget[newCat] || 'unmapped';

                if (parsedRows[idx].target === 'unmapped') {
                    parsedRows[idx].included = false;
                    parsedRows[idx].import_action = 'skip';
                }
                renderPreview();
            });
        });
    }

    // ── Bulk ADD / NOT ADD triggers ──────────────────────────
    btnSetAllAdd.addEventListener('click', () => {
        parsedRows.forEach((r, idx) => {
            if (r.target === 'customer' && r.is_existing) {
                setDuplicateAction(idx, 'add', false);
            }
        });
        renderPreview();
        showToast('All duplicate customer balances set to ADD.', 'info');
    });

    btnSetAllNotAdd.addEventListener('click', () => {
        parsedRows.forEach((r, idx) => {
            if (r.target === 'customer' && r.is_existing) {
                setDuplicateAction(idx, 'not_add', false);
            }
        });
        renderPreview();
        showToast('All duplicate customers set to NOT ADD (existing balances preserved).', 'info');
    });

    function setDuplicateAction(idx, action, reRender = true) {
        if (!parsedRows[idx]) return;
        parsedRows[idx].duplicate_action = action;
        parsedRows[idx].import_action    = 'update';

        if (reRender) {
            renderPreview();
        }
    }

    // ── Banner Counter ───────────────────────────────────────
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

        const setElText = (id, text) => {
            const el = document.getElementById(id);
            if (el) el.textContent = text;
        };

        setElText('banner-total', `${parsedRows.length} rows`);
        setElText('banner-gl', gl);
        setElText('banner-customers', cust);
        setElText('banner-suppliers', supp);
        setElText('banner-unmapped', unmap);
        setElText('banner-duplicates', dupes);
    }

    // ── Back Button ──────────────────────────────────────────
    btnBack.addEventListener('click', () => goToStage(1));

    // ── Commit & Import ───────────────────────────────────────
    btnCommit.addEventListener('click', async () => {
        const includedRows = parsedRows.filter(r => r.included && r.target !== 'unmapped' && r.import_action !== 'skip');
        if (!includedRows.length) {
            showToast('No rows selected for import — please check at least one row.', 'error');
            return;
        }

        commitLabel.textContent = 'Importing…';
        btnCommit.disabled      = true;

        const payload = parsedRows.map(r => ({
            row:              r.row,
            accountid:        r.accountid,
            ac:               r.ac,
            name:             r.name,
            phone:            r.phone,
            email:            r.email,
            address:          r.address,
            credit_limit:     r.credit_limit,
            balance:          r.balance,
            raw_balance:      r.raw_balance,
            store_credit:     r.store_credit,
            category:         r.category,
            target:           r.target,
            included:         r.included,
            is_existing:      Boolean(r.is_existing),
            customer_id:      r.customer_id || null,
            duplicate_action: r.duplicate_action || 'add',
            import_action:    r.import_action,
        }));

        let data;
        try {
            const res  = await fetch('{{ route("accounts.import.commit") }}', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
                body:    JSON.stringify({
                    rows:              payload,
                    balance_treatment: currentBalanceTreatment,
                }),
            });
            data = await res.json();

            if (!res.ok) {
                showToast(data.message || 'Import failed.', 'error');
                return;
            }
        } catch (err) {
            showToast('Network error: ' + (err.message || 'Failed to reach server.'), 'error');
            return;
        }

        try {
            renderResults(data.summary || {});
            goToStage(3);
        } catch (err) {
            console.error('Summary render error:', err);
            showToast('Display error: ' + err.message, 'error');
        } finally {
            commitLabel.textContent = 'Confirm & Import';
            btnCommit.disabled      = false;
        }
    });

    // ── REQUIREMENT 6: Render Results Summary ────────────────
    function renderResults(summary) {
        const cards = document.getElementById('result-cards');
        cards.innerHTML = '';

        const sCust = summary.customer || { inserted: 0, new_customers: 0, existing_customers: 0, added_to_existing: 0, not_added: 0, failed: 0 };
        const sGl   = summary.gl       || { inserted: 0, skipped: 0, failed: 0 };
        const sSupp = summary.supplier || { inserted: 0, skipped: 0, failed: 0 };

        // 1. Customer Detailed Summary Card (Requirement 6)
        cards.innerHTML += `
            <div class="p-6 rounded-2xl border-2 border-blue-200 dark:border-blue-800/80 bg-blue-50/40 dark:bg-blue-950/20 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-md shadow-blue-500/30">
                                <i class="fas fa-users text-sm"></i>
                            </div>
                            <span class="font-bold text-slate-800 dark:text-white text-base">Customers</span>
                        </div>
                        <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/60 dark:text-blue-200">
                            Processed: ${sCust.inserted + sCust.failed}
                        </span>
                    </div>

                    <div class="space-y-2 text-xs font-medium">
                        <div class="flex justify-between items-center py-1 border-b border-blue-100 dark:border-blue-900/40">
                            <span class="text-slate-600 dark:text-slate-300 font-bold">Imported successfully:</span>
                            <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400">${sCust.inserted}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-blue-100 dark:border-blue-900/40">
                            <span class="text-slate-600 dark:text-slate-300">New customers:</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400">${sCust.new_customers || 0}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-blue-100 dark:border-blue-900/40">
                            <span class="text-slate-600 dark:text-slate-300">Existing customers:</span>
                            <span class="font-bold text-amber-600 dark:text-amber-400">${sCust.existing_customers || 0}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-blue-100 dark:border-blue-900/40">
                            <span class="text-slate-600 dark:text-slate-300 pl-2">&bull; Added to existing balances:</span>
                            <span class="font-bold text-teal-600 dark:text-teal-400">${sCust.added_to_existing || 0}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-blue-100 dark:border-blue-900/40">
                            <span class="text-slate-600 dark:text-slate-300 pl-2">&bull; Not added (kept unchanged):</span>
                            <span class="font-bold text-slate-500 dark:text-slate-400">${sCust.not_added || 0}</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-slate-600 dark:text-slate-300 font-bold">Failed rows:</span>
                            <span class="font-bold text-rose-600 dark:text-rose-400">${sCust.failed || 0}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // 2. GL Accounts Summary Card
        cards.innerHTML += `
            <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center shadow-md shadow-emerald-500/30">
                            <i class="fas fa-book-open text-sm"></i>
                        </div>
                        <span class="font-bold text-slate-800 dark:text-white text-base">GL Accounts</span>
                    </div>
                    <div class="space-y-2 text-xs font-medium">
                        <div class="flex justify-between items-center py-1 border-b border-slate-200 dark:border-slate-700">
                            <span class="text-slate-500">Inserted:</span>
                            <span class="text-sm font-extrabold text-emerald-600 dark:text-emerald-400">${sGl.inserted}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-slate-200 dark:border-slate-700">
                            <span class="text-slate-500">Skipped (duplicates):</span>
                            <span class="font-bold text-amber-500">${sGl.skipped}</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-slate-500">Failed:</span>
                            <span class="font-bold text-rose-500">${sGl.failed}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // 3. Suppliers Summary Card
        cards.innerHTML += `
            <div class="p-6 rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/30 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-9 h-9 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-md shadow-purple-500/30">
                            <i class="fas fa-truck text-sm"></i>
                        </div>
                        <span class="font-bold text-slate-800 dark:text-white text-base">Suppliers</span>
                    </div>
                    <div class="space-y-2 text-xs font-medium">
                        <div class="flex justify-between items-center py-1 border-b border-slate-200 dark:border-slate-700">
                            <span class="text-slate-500">Inserted:</span>
                            <span class="text-sm font-extrabold text-purple-600 dark:text-purple-400">${sSupp.inserted}</span>
                        </div>
                        <div class="flex justify-between items-center py-1 border-b border-slate-200 dark:border-slate-700">
                            <span class="text-slate-500">Skipped (duplicates):</span>
                            <span class="font-bold text-amber-500">${sSupp.skipped}</span>
                        </div>
                        <div class="flex justify-between items-center py-1">
                            <span class="text-slate-500">Failed:</span>
                            <span class="font-bold text-rose-500">${sSupp.failed}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Render Error List
        const errorsList = document.getElementById('result-errors-list');
        const errorsDiv  = document.getElementById('result-errors');
        errorsList.innerHTML = '';
        let hasErrors = false;

        ['customer', 'gl', 'supplier'].forEach(key => {
            (summary[key]?.errors || []).forEach(msg => {
                hasErrors = true;
                const li = document.createElement('li');
                li.className = 'flex items-start gap-2 py-0.5';
                li.innerHTML = `<i class="fas fa-exclamation-circle text-amber-500 mt-0.5 flex-shrink-0"></i><span>${escHtml(msg)}</span>`;
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
        duplicatePanel.classList.add('hidden');
        goToStage(1);
    });

    // ── Stage Transitions ────────────────────────────────────
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

    function round2(val) {
        return Math.round((val + Number.EPSILON) * 100) / 100;
    }

    function formatBalance(amount) {
        if (amount === undefined || amount === null || isNaN(amount)) {
            return '<span class="text-slate-400 font-mono">—</span>';
        }
        const val = parseFloat(amount);
        const formatted = 'Rs. ' + Math.abs(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (val > 0) {
            // Customer owes store = positive debt = red
            return `<span class="text-red-600 dark:text-red-400 font-bold font-mono">+${formatted}</span>`;
        } else if (val < 0) {
            // Store owes customer = credit = green
            return `<span class="text-emerald-600 dark:text-emerald-400 font-bold font-mono">-${formatted}</span>`;
        } else {
            return `<span class="text-slate-500 dark:text-slate-400 font-mono font-medium">Rs. 0.00</span>`;
        }
    }

    function escHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function showToast(msg, type = 'info') {
        const bg    = type === 'error' ? 'bg-rose-600' : 'bg-emerald-600';
        const toast = document.createElement('div');
        toast.className = `fixed bottom-6 right-6 z-50 px-5 py-3 ${bg} text-white text-sm font-semibold rounded-xl shadow-xl
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
