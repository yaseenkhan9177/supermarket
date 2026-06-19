<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restrict Reports | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800" x-data="restrictManager()">

    <nav class="bg-slate-900 border-b border-slate-800 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8 text-white">
        <div class="container mx-auto max-w-[1200px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-slate-700 flex items-center justify-center text-white shadow-md border border-slate-600">
                    <i class="fas fa-user-lock text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-white leading-none tracking-tight">
                        OwnStore <span class="text-slate-400">PRO</span>
                    </h1>
                    <span class="text-xs text-slate-400 font-medium mt-0.5">Report Access Control</span>
                </div>
            </div>
            <div>
                <a href="/reports" class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105 border border-slate-700">
                    <i class="fas fa-arrow-left"></i> Back to Reports
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1200px] pb-32">

        <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Manage Visibility</h2>
                <p class="text-sm text-gray-500">Control who can view specific reports.</p>
            </div>

            <div class="relative w-full md:w-96">
                <i class="fas fa-search absolute left-3 top-3.5 text-gray-400"></i>
                <input type="text" x-model="search" placeholder="Search report name..." class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-slate-500 outline-none">
            </div>

            <button @click="saveChanges()" class="bg-slate-800 hover:bg-slate-900 text-white px-6 py-3 rounded-lg font-bold shadow transition transform hover:-translate-y-0.5 flex items-center gap-2">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-200 text-xs font-bold text-gray-500 uppercase">
                        <th class="p-4 w-12 text-center">Icon</th>
                        <th class="p-4">Report Name</th>
                        <th class="p-4 w-40 text-center">
                            <span class="block">Hide to All</span>
                            <span class="text-[9px] text-gray-400 normal-case">(Disable Completely)</span>
                        </th>
                        <th class="p-4 w-40 text-center">
                            <span class="block">Owner Only</span>
                            <span class="text-[9px] text-gray-400 normal-case">(Hide to all except me)</span>
                        </th>
                        <th class="p-4 w-40 text-center">
                            <span class="block">Restricted</span>
                            <span class="text-[9px] text-gray-400 normal-case">(For not permitted)</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <template x-for="report in filteredReports" :key="report.id">
                        <tr class="hover:bg-slate-50 transition group">
                            <td class="p-4 text-center">
                                <div class="w-8 h-8 rounded bg-gray-100 flex items-center justify-center text-gray-500 group-hover:bg-white group-hover:text-slate-600 shadow-sm border border-gray-200">
                                    <i :class="report.icon"></i>
                                </div>
                            </td>

                            <td class="p-4">
                                <span class="font-bold text-gray-800 block" x-text="report.name"></span>
                                <span class="text-xs text-gray-400" x-text="report.type"></span>
                            </td>

                            <td class="p-4 text-center">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="report.is_hidden_global" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                                </label>
                            </td>

                            <td class="p-4 text-center">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="report.is_owner_only" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                                </label>
                            </td>

                            <td class="p-4 text-center">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" x-model="report.requires_permission" class="sr-only peer">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500"></div>
                                </label>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

    </div>

    <script>
        function restrictManager() {
            return {
                search: '',
                reports: {
                    {
                        Js::from($reports)
                    }
                },

                get filteredReports() {
                    if (this.search === '') return this.reports;
                    return this.reports.filter(r => r.name.toLowerCase().includes(this.search.toLowerCase()));
                },

                saveChanges() {
                    // Send this.reports to backend
                    fetch('/reports/restrict/update', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                reports: this.reports
                            })
                        })
                        .then(response => {
                            if (response.ok) {
                                Swal.fire('Saved!', 'Access permissions updated.', 'success');
                            } else {
                                Swal.fire('Error', 'Failed to save changes.', 'error');
                            }
                        })
                        .catch(error => {
                            Swal.fire('Error', 'An unexpected error occurred.', 'error');
                            console.error('Error:', error);
                        });
                }
            }
        }
    </script>
</body>

</html>