<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Explorer | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 99px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>

<body class="bg-slate-100 font-sans text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- TOP BAR (Dark Slate Header) -->
    <header class="bg-slate-900 border-b border-slate-800 px-6 py-3 shadow-md sticky top-0 z-50 text-white">
        <div class="container mx-auto max-w-[1600px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 flex items-center justify-center text-white shadow-md border border-indigo-500">
                    <i class="fas fa-chart-pie text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-white leading-none tracking-tight">
                        {{ tenancy()->initialized ? (\App\Models\Store::first()?->name ?? 'Mart POS') : 'Mart POS' }} <span class="text-indigo-400">Reports</span>
                    </h1>
                    <span class="text-xs text-slate-400 font-medium mt-0.5">Real-time Financial & Operational Analytics</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="/dashboard" class="inline-flex items-center gap-2 px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold rounded-lg shadow-sm transition border border-slate-700">
                    <i class="fas fa-home text-slate-400"></i> Dashboard
                </a>
            </div>
        </div>
    </header>

    <!-- MAIN TWO-COLUMN CONTAINER -->
    <div class="container mx-auto max-w-[1600px] px-4 py-6 flex-1 flex flex-col md:flex-row gap-6">

        <!-- LEFT SIDEBAR: Reports Categories & Links -->
        <aside class="w-full md:w-72 bg-slate-900 text-slate-300 rounded-2xl p-4 shadow-xl border border-slate-800 flex-shrink-0 space-y-6">
            <div class="px-2 pt-1 pb-2 border-b border-slate-800 flex justify-between items-center">
                <span class="text-xs font-extrabold uppercase tracking-widest text-slate-400">Reports Explorer</span>
                <i class="fas fa-list-ul text-slate-500 text-xs"></i>
            </div>

            <nav class="space-y-6 overflow-y-auto max-h-[calc(100vh-180px)] pr-1">
                @foreach($categories as $cat)
                <div class="space-y-1.5">
                    <!-- SECTION HEADER -->
                    <div class="px-3 text-[11px] font-extrabold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                        <span>{{ $cat['title'] }}</span>
                    </div>

                    <!-- REPORT LINKS -->
                    <div class="space-y-0.5 pl-2">
                        @foreach($cat['reports'] as $rep)
                        <button id="btn-{{ $rep['slug'] }}"
                                onclick="switchReport('{{ $rep['slug'] }}')"
                                class="report-nav-btn w-full text-left px-3 py-2 text-xs font-semibold rounded-lg transition-all flex justify-between items-center text-slate-300 hover:bg-slate-800 hover:text-white group">
                            <span>{{ $rep['name'] }}</span>
                            <i class="fas fa-chevron-right text-[10px] text-slate-600 group-hover:text-indigo-400 transition"></i>
                        </button>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </nav>
        </aside>

        <!-- MAIN AREA: Active Report View Content -->
        <main class="flex-1 bg-slate-100 rounded-2xl flex flex-col min-w-0">
            <!-- Loading Indicator Overlay -->
            <div id="report-loader" class="hidden py-24 text-center space-y-3">
                <i class="fas fa-circle-notch fa-spin text-4xl text-indigo-600"></i>
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Fetching live report data...</p>
            </div>

            <!-- Report HTML Content Slot -->
            <div id="report-content" class="flex-1">
                <!-- Dynamically loaded via AJAX -->
            </div>
        </main>

    </div>

    <!-- AJAX SCRIPT -->
    <script>
        let currentReport = '{{ $activeReport }}';

        document.addEventListener('DOMContentLoaded', function () {
            loadReport(currentReport);
        });

        function switchReport(reportSlug) {
            currentReport = reportSlug;
            // Update browser URL query param quietly
            const newUrl = window.location.pathname + '?report=' + reportSlug;
            window.history.pushState({ path: newUrl }, '', newUrl);

            loadReport(reportSlug);
        }

        function loadReport(reportSlug, params = {}) {
            const loader = document.getElementById('report-loader');
            const content = document.getElementById('report-content');

            loader.classList.remove('hidden');
            content.classList.add('opacity-40');

            // Highlight Active Sidebar Button
            document.querySelectorAll('.report-nav-btn').forEach(btn => {
                btn.classList.remove('bg-indigo-600', 'text-white', 'shadow-md', 'font-bold');
                btn.classList.add('text-slate-300');
            });

            const activeBtn = document.getElementById('btn-' + reportSlug);
            if (activeBtn) {
                activeBtn.classList.remove('text-slate-300');
                activeBtn.classList.add('bg-indigo-600', 'text-white', 'shadow-md', 'font-bold');
            }

            // Build query params string
            let queryParams = new URLSearchParams({ report: reportSlug, ...params }).toString();

            fetch('/reports/data?' + queryParams, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Network error loading report');
                return response.text();
            })
            .then(html => {
                content.innerHTML = html;
            })
            .catch(error => {
                content.innerHTML = '<div class="p-8 text-center text-rose-600 font-bold bg-white rounded-xl shadow-sm border border-rose-200">Error loading report: ' + error.message + '</div>';
            })
            .finally(() => {
                loader.classList.add('hidden');
                content.classList.remove('opacity-40');
            });
        }

        function applyDateFilter(reportSlug, fromId, toId) {
            const dateFrom = document.getElementById(fromId)?.value || '';
            const dateTo   = document.getElementById(toId)?.value || '';

            loadReport(reportSlug, { date_from: dateFrom, date_to: dateTo });
        }
    </script>
</body>
</html>