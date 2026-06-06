<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="OwnStore Reports Center – browse and launch all business reports organized by category.">
    <title>Reports Center | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: { DEFAULT: '#4F46E5', dark: '#3730A3', light: '#6366F1' }
                    }
                }
            }
        }
    </script>

    <style>
        /* ── Custom scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #4F46E5; border-radius: 99px; }

        /* ── Sidebar tree animations ── */
        .folder-content {
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        opacity 0.3s ease;
        }
        .folder-content.collapsed { max-height: 0; opacity: 0; }
        .folder-content.expanded  { max-height: 2000px; opacity: 1; }

        /* ── Chevron rotation ── */
        .chevron-icon { transition: transform 0.3s ease; }
        .chevron-open  { transform: rotate(90deg); }

        /* ── Report row hover glow ── */
        .report-row {
            transition: background 0.18s, transform 0.18s, box-shadow 0.18s;
        }
        .report-row:hover {
            background: rgba(99,102,241,0.12);
            transform: translateX(3px);
        }

        /* ── Category header hover ── */
        .category-btn {
            transition: background 0.2s, color 0.2s;
        }
        .category-btn:hover { background: rgba(99,102,241,0.1); }
        .category-btn.active { background: rgba(99,102,241,0.18); }

        /* ── Main panel fade-in ── */
        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .panel-animate { animation: fadeSlide 0.3s ease forwards; }

        /* ── Search highlight ── */
        .search-match { background: rgba(234,179,8,0.25); border-radius: 3px; }

        /* ── Glass card ── */
        .glass {
            background: rgba(255,255,255,0.07);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* ── Badge pill ── */
        .count-badge {
            font-size: 10px;
            padding: 1px 7px;
            border-radius: 99px;
            font-weight: 700;
            letter-spacing: .02em;
        }
    </style>
</head>

<body class="bg-slate-950 font-sans text-slate-100 min-h-screen"
      x-data="reportApp()" x-init="init()">

    {{-- ═══════════════════════════════════════════════════════
         TOP NAVBAR
    ═══════════════════════════════════════════════════════ --}}
    <nav class="bg-slate-900 border-b border-slate-800 px-5 py-3 sticky top-0 z-50 flex items-center justify-between shadow-lg">
        <div class="flex items-center gap-4">
            <div class="w-9 h-9 rounded-xl bg-brand flex items-center justify-center shadow-lg shadow-indigo-900/40">
                <i class="fas fa-chart-pie text-white text-sm"></i>
            </div>
            <div>
                <h1 class="text-base font-extrabold text-white leading-tight tracking-tight">
                    OwnStore <span class="text-indigo-400">PRO</span>
                </h1>
                <p class="text-[10px] text-slate-400 font-medium leading-none mt-0.5">Reports Center</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            {{-- Search bar (global) --}}
            <div class="relative hidden sm:block">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                <input id="global-search"
                       type="text"
                       x-model="globalSearch"
                       placeholder="Search all reports…"
                       class="bg-slate-800 border border-slate-700 text-sm text-slate-200 placeholder-slate-500
                              pl-8 pr-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500
                              w-56 transition">
                <button x-show="globalSearch" @click="globalSearch=''" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-white">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>

            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white
                      text-sm font-semibold rounded-lg border border-slate-700 transition">
                <i class="fas fa-home text-xs"></i> Dashboard
            </a>
            <a href="{{ route('general-ledger.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white
                      text-sm font-semibold rounded-lg transition shadow shadow-indigo-900/40">
                <i class="fas fa-sitemap text-xs"></i> Chart of Accounts
            </a>
        </div>
    </nav>

    {{-- ═══════════════════════════════════════════════════════
         MAIN LAYOUT  (sidebar + content)
    ═══════════════════════════════════════════════════════ --}}
    <div class="flex h-[calc(100vh-57px)] overflow-hidden">

        {{-- ──────────────────────────────────────────────────
             LEFT SIDEBAR – collapsible report tree
        ────────────────────────────────────────────────── --}}
        <aside id="report-sidebar"
               class="w-72 min-w-[260px] bg-slate-900 border-r border-slate-800
                      flex flex-col overflow-hidden transition-all duration-300">

            {{-- sidebar header --}}
            <div class="px-4 py-3 border-b border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-folder-open text-indigo-400 text-sm"></i>
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-widest">Report Tree</span>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="expandAll()"
                            title="Expand All"
                            class="text-slate-500 hover:text-indigo-400 text-xs transition">
                        <i class="fas fa-expand"></i>
                    </button>
                    <button @click="collapseAll()"
                            title="Collapse All"
                            class="text-slate-500 hover:text-indigo-400 text-xs transition">
                        <i class="fas fa-compress"></i>
                    </button>
                </div>
            </div>

            {{-- sidebar search --}}
            <div class="px-3 py-2.5 border-b border-slate-800">
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    <input id="sidebar-search"
                           type="text"
                           x-model="sidebarSearch"
                           @input="onSidebarSearch()"
                           placeholder="Filter reports…"
                           class="w-full bg-slate-800/70 border border-slate-700 text-xs text-slate-200
                                  placeholder-slate-500 pl-8 pr-3 py-2 rounded-lg focus:outline-none
                                  focus:ring-1 focus:ring-indigo-500 transition">
                </div>
            </div>

            {{-- tree scroll area --}}
            <div class="flex-1 overflow-y-auto py-2 space-y-0.5" id="tree-scroll">

                @foreach($reportTree as $catIndex => $category)
                @php
                    $catSlug = 'cat-' . $catIndex;
                    $count   = count($category['reports']);
                @endphp
                <div class="px-2" x-data="{ open: {{ $catIndex === 0 ? 'true' : 'false' }} }">

                    {{-- Category row --}}
                    <button @click="open = !open; setActiveCategory({{ $catIndex }})"
                            :id="'{{ $catSlug }}'"
                            class="category-btn w-full flex items-center gap-2.5 px-3 py-2.5 rounded-lg
                                   text-left group cursor-pointer select-none"
                            :class="activeCategoryIndex === {{ $catIndex }} ? 'active' : ''">

                        {{-- folder icon --}}
                        <span class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0
                                     bg-slate-800 group-hover:bg-indigo-900/60 transition"
                              :class="open ? 'bg-indigo-900/60' : ''">
                            <i class="{{ $category['icon'] }} text-[11px]"
                               :class="open ? 'text-indigo-400' : 'text-slate-400'"></i>
                        </span>

                        <span class="flex-1 text-xs font-semibold leading-tight"
                              :class="open ? 'text-indigo-300' : 'text-slate-300'">
                            {{ $category['name'] }}
                        </span>

                        <span class="count-badge bg-slate-800 text-slate-400">{{ $count }}</span>

                        <i class="fas fa-chevron-right chevron-icon text-[10px] text-slate-500"
                           :class="open ? 'chevron-open text-indigo-400' : ''"></i>
                    </button>

                    {{-- Reports list --}}
                    <div class="folder-content pl-2 space-y-px"
                         :class="open ? 'expanded' : 'collapsed'">

                        @foreach($category['reports'] as $rIndex => $reportName)
                        <button
                            id="report-{{ $catIndex }}-{{ $rIndex }}"
                            @click="selectReport('{{ addslashes($reportName) }}', '{{ addslashes($category['name']) }}', {{ $catIndex }})"
                            class="report-row w-full flex items-center gap-2.5 px-3 py-2 rounded-lg
                                   text-left cursor-pointer group"
                            :class="activeReport === '{{ addslashes($reportName) }}'
                                     ? 'bg-indigo-600/20 ring-1 ring-indigo-500/40'
                                     : ''"
                            x-show="reportVisible('{{ addslashes($reportName) }}')">

                            <i class="fas fa-file-alt text-[10px] flex-shrink-0"
                               :class="activeReport === '{{ addslashes($reportName) }}'
                                        ? 'text-indigo-400'
                                        : 'text-slate-600 group-hover:text-slate-400'"></i>

                            <span class="text-[11.5px] leading-tight font-medium truncate"
                                  :class="activeReport === '{{ addslashes($reportName) }}'
                                           ? 'text-indigo-300'
                                           : 'text-slate-400 group-hover:text-slate-200'">
                                {{ $reportName }}
                            </span>
                        </button>
                        @endforeach

                    </div>
                </div>
                @endforeach

            </div>

            {{-- sidebar footer --}}
            <div class="px-4 py-3 border-t border-slate-800">
                <p class="text-[10px] text-slate-600 font-medium">
                    <i class="fas fa-info-circle mr-1"></i>
                    <span x-text="totalReports + ' reports across ' + totalCategories + ' categories'"></span>
                </p>
            </div>
        </aside>

        {{-- ──────────────────────────────────────────────────
             RIGHT CONTENT AREA
        ────────────────────────────────────────────────── --}}
        <main class="flex-1 overflow-y-auto bg-slate-950">

            {{-- ── WELCOME / no-selection state ── --}}
            <div x-show="!activeReport" class="flex flex-col items-center justify-center h-full text-center px-8 panel-animate">
                <div class="w-24 h-24 rounded-3xl bg-slate-800/60 flex items-center justify-center mb-6 border border-slate-700/50">
                    <i class="fas fa-chart-bar text-4xl text-indigo-500/60"></i>
                </div>
                <h2 class="text-2xl font-bold text-slate-200 mb-2">Reports Center</h2>
                <p class="text-slate-500 text-sm max-w-sm leading-relaxed mb-8">
                    Select any report from the sidebar tree to configure and launch it.
                    Use the search box to quickly find reports by name.
                </p>

                {{-- Quick-stats strip --}}
                <div class="grid grid-cols-3 gap-4 w-full max-w-md">
                    <div class="bg-slate-800/60 rounded-xl p-4 border border-slate-700/50">
                        <p class="text-2xl font-bold text-indigo-400" x-text="totalCategories"></p>
                        <p class="text-xs text-slate-500 mt-1">Categories</p>
                    </div>
                    <div class="bg-slate-800/60 rounded-xl p-4 border border-slate-700/50">
                        <p class="text-2xl font-bold text-emerald-400" x-text="totalReports"></p>
                        <p class="text-xs text-slate-500 mt-1">Reports</p>
                    </div>
                    <div class="bg-slate-800/60 rounded-xl p-4 border border-slate-700/50">
                        <p class="text-2xl font-bold text-amber-400">PDF</p>
                        <p class="text-xs text-slate-500 mt-1">+ Excel</p>
                    </div>
                </div>
            </div>

            {{-- ── GLOBAL SEARCH RESULTS ── --}}
            <div x-show="globalSearch.length > 1 && !activeReport" class="p-6 panel-animate" x-cloak>
                <div class="flex items-center gap-3 mb-4">
                    <i class="fas fa-search text-indigo-400"></i>
                    <h2 class="text-lg font-bold text-slate-200">
                        Search results for "<span class="text-indigo-400" x-text="globalSearch"></span>"
                    </h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                    <template x-for="result in searchResults" :key="result.category + result.name">
                        <button @click="selectReport(result.name, result.category, result.catIndex)"
                                class="flex items-center gap-3 p-4 bg-slate-800/70 hover:bg-slate-800
                                       border border-slate-700 hover:border-indigo-500/50 rounded-xl
                                       text-left transition group">
                            <div class="w-8 h-8 rounded-lg bg-indigo-900/50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-file-alt text-indigo-400 text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-slate-200 truncate group-hover:text-indigo-300" x-text="result.name"></p>
                                <p class="text-xs text-slate-500 truncate" x-text="result.category"></p>
                            </div>
                        </button>
                    </template>
                    <div x-show="searchResults.length === 0" class="col-span-3 text-center py-12 text-slate-600">
                        <i class="fas fa-search text-3xl mb-3"></i>
                        <p class="text-sm">No reports matched your search.</p>
                    </div>
                </div>
            </div>

            {{-- ── REPORT DETAIL PANEL ── --}}
            <div x-show="activeReport" x-cloak class="p-6 panel-animate">

                {{-- Breadcrumb --}}
                <nav class="flex items-center gap-2 text-xs text-slate-500 mb-6">
                    <button @click="activeReport = null" class="hover:text-indigo-400 transition">Reports</button>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                    <span x-text="activeCategoryName" class="hover:text-slate-300 cursor-pointer"
                          @click="activeReport = null"></span>
                    <i class="fas fa-chevron-right text-[9px]"></i>
                    <span class="text-slate-300" x-text="activeReport"></span>
                </nav>

                {{-- Report card --}}
                <div class="max-w-2xl">
                    <div class="bg-slate-900 border border-slate-700/60 rounded-2xl overflow-hidden shadow-2xl shadow-black/40">

                        {{-- card header --}}
                        <div class="bg-gradient-to-r from-indigo-600/30 via-indigo-700/20 to-slate-900/0
                                    border-b border-slate-700/60 px-6 py-5 flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-indigo-600/20 border border-indigo-500/30
                                        flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-file-lines text-indigo-400 text-xl"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h2 class="text-xl font-bold text-white leading-tight" x-text="activeReport"></h2>
                                <div class="flex items-center gap-2 mt-1.5">
                                    <span class="text-xs bg-indigo-900/60 text-indigo-300 px-2 py-0.5 rounded-md font-medium border border-indigo-700/40"
                                          x-text="activeCategoryName"></span>
                                    <span class="text-xs text-slate-500">Business Report</span>
                                </div>
                            </div>
                            <button @click="activeReport = null"
                                    class="text-slate-500 hover:text-white transition p-1">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        {{-- date range inputs --}}
                        <div class="px-6 py-5 border-b border-slate-700/60">
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">
                                <i class="fas fa-calendar-alt mr-1.5"></i>Report Parameters
                            </p>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1.5">From Date</label>
                                    <input type="date" x-model="dateFrom" id="report-date-from"
                                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm
                                                  rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2
                                                  focus:ring-indigo-500 focus:border-transparent transition">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-500 uppercase mb-1.5">To Date</label>
                                    <input type="date" x-model="dateTo" id="report-date-to"
                                           class="w-full bg-slate-800 border border-slate-700 text-slate-200 text-sm
                                                  rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2
                                                  focus:ring-indigo-500 focus:border-transparent transition">
                                </div>
                            </div>

                            {{-- Quick ranges --}}
                            <div class="flex flex-wrap gap-2 mt-3">
                                <template x-for="range in quickRanges" :key="range.label">
                                    <button @click="applyRange(range)"
                                            class="text-[10px] font-semibold px-2.5 py-1 rounded-md border
                                                   border-slate-700 text-slate-400 hover:text-indigo-300
                                                   hover:border-indigo-600 transition"
                                            x-text="range.label"></button>
                                </template>
                            </div>
                        </div>

                        {{-- Action buttons --}}
                        <div class="px-6 py-5 flex flex-col sm:flex-row gap-3">
                            <a :href="buildUrl('pdf')"
                               target="_blank"
                               class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3
                                      bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold
                                      rounded-xl shadow-lg shadow-indigo-900/40 transition">
                                <i class="fas fa-file-pdf"></i> Open / Print Report
                            </a>
                            <a :href="buildUrl('excel')"
                               target="_blank"
                               class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3
                                      bg-emerald-700 hover:bg-emerald-600 text-white text-sm font-bold
                                      rounded-xl shadow-lg shadow-emerald-900/40 transition">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </a>
                            <button @click="copyLink()"
                                    title="Copy report link"
                                    class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300
                                           rounded-xl border border-slate-700 transition text-sm font-semibold">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>

                        {{-- Toast: link copied --}}
                        <div x-show="linkCopied" x-transition
                             class="mx-6 mb-5 px-4 py-2 bg-emerald-900/60 border border-emerald-700/60
                                    rounded-lg text-xs text-emerald-300 text-center">
                            <i class="fas fa-check-circle mr-1"></i> Link copied to clipboard!
                        </div>
                    </div>

                    {{-- Related reports from same category --}}
                    <div class="mt-6" x-show="relatedReports.length > 0">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-3">
                            Other reports in <span x-text="activeCategoryName" class="text-slate-400"></span>
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <template x-for="r in relatedReports.slice(0, 8)" :key="r">
                                <button @click="selectReport(r, activeCategoryName, activeCategoryIndex)"
                                        class="flex items-center gap-2.5 px-3 py-2.5 bg-slate-900/80
                                               hover:bg-slate-800 border border-slate-700/50
                                               hover:border-indigo-500/50 rounded-xl text-left transition group">
                                    <i class="fas fa-file-alt text-[10px] text-slate-600 group-hover:text-indigo-400 transition"></i>
                                    <span class="text-xs text-slate-400 group-hover:text-slate-200 transition truncate" x-text="r"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div><!-- /flex -->

    <script>
    function reportApp() {
        // Full report tree from PHP
        const tree = @json($reportTree);

        // Pre-compute a flat list for search
        const flat = [];
        tree.forEach((cat, ci) => {
            cat.reports.forEach(r => {
                flat.push({ name: r, category: cat.name, catIndex: ci });
            });
        });

        return {
            // ── state ──────────────────────────────────────
            activeReport:        null,
            activeCategoryName:  '',
            activeCategoryIndex: null,
            sidebarSearch:       '',
            globalSearch:        '',
            linkCopied:          false,
            openFolders:         [0],          // first folder open by default

            dateFrom: new Date().toISOString().slice(0, 8) + '01',
            dateTo:   new Date().toISOString().slice(0, 10),

            quickRanges: [
                { label: 'Today',      days: 0  },
                { label: 'This Week',  days: 6  },
                { label: 'This Month', days: 29 },
                { label: 'Last 3 Mo.', days: 89 },
                { label: 'This Year',  days: 364},
            ],

            // ── computed ────────────────────────────────────
            get totalCategories() { return tree.length; },
            get totalReports()    { return flat.length;  },

            get searchResults() {
                if (this.globalSearch.length < 2) return [];
                const q = this.globalSearch.toLowerCase();
                return flat.filter(r =>
                    r.name.toLowerCase().includes(q) ||
                    r.category.toLowerCase().includes(q)
                ).slice(0, 30);
            },

            get relatedReports() {
                if (this.activeCategoryIndex === null) return [];
                return tree[this.activeCategoryIndex].reports
                    .filter(r => r !== this.activeReport);
            },

            // ── methods ─────────────────────────────────────
            init() {
                // restore today's date range
                const now = new Date();
                const y   = now.getFullYear();
                const m   = String(now.getMonth() + 1).padStart(2, '0');
                const d   = String(now.getDate()).padStart(2, '0');
                this.dateFrom = `${y}-${m}-01`;
                this.dateTo   = `${y}-${m}-${d}`;
            },

            selectReport(name, category, catIndex) {
                this.activeReport        = name;
                this.activeCategoryName  = category;
                this.activeCategoryIndex = catIndex;
                this.globalSearch        = '';
                // scroll right panel to top
                document.querySelector('main').scrollTop = 0;
            },

            setActiveCategory(idx) {
                this.activeCategoryIndex = idx;
            },

            reportVisible(name) {
                if (!this.sidebarSearch) return true;
                return name.toLowerCase().includes(this.sidebarSearch.toLowerCase());
            },

            onSidebarSearch() {
                // If user is searching, expand all folders automatically
                if (this.sidebarSearch.length > 0) {
                    document.querySelectorAll('.folder-content').forEach(el => {
                        el.classList.add('expanded');
                        el.classList.remove('collapsed');
                    });
                }
            },

            expandAll() {
                document.querySelectorAll('.folder-content').forEach(el => {
                    el.classList.add('expanded');
                    el.classList.remove('collapsed');
                });
                // Trigger Alpine open states via DOM (brute force since each has its own x-data)
                document.querySelectorAll('[x-data*="open"]').forEach(el => {
                    if (el._x_dataStack) {
                        el._x_dataStack[0].open = true;
                    }
                });
            },

            collapseAll() {
                document.querySelectorAll('.folder-content').forEach(el => {
                    el.classList.add('collapsed');
                    el.classList.remove('expanded');
                });
            },

            applyRange(range) {
                const now   = new Date();
                const to    = new Date(now);
                const from  = new Date(now);
                from.setDate(now.getDate() - range.days);
                const fmt = d => d.toISOString().slice(0, 10);
                this.dateFrom = fmt(from);
                this.dateTo   = fmt(to);
            },

            buildUrl(format) {
                const base = '/account/reports/open';
                const params = new URLSearchParams({
                    name:      this.activeReport,
                    category:  this.activeCategoryName,
                    date_from: this.dateFrom,
                    date_to:   this.dateTo,
                    format:    format,
                });
                return `${base}?${params.toString()}`;
            },

            async copyLink() {
                const url = window.location.origin + this.buildUrl('pdf');
                try {
                    await navigator.clipboard.writeText(url);
                } catch {
                    // fallback
                }
                this.linkCopied = true;
                setTimeout(() => { this.linkCopied = false; }, 2500);
            }
        };
    }
    </script>

</body>
</html>
