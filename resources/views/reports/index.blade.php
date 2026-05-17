<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Center | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800" x-data="reportHub()">

    <nav class="bg-teal-900 border-b border-teal-800 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8 text-white">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-teal-700 flex items-center justify-center text-white shadow-md border border-teal-600">
                    <i class="fas fa-chart-pie text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-white leading-none tracking-tight">
                        OwnStore <span class="text-teal-400">PRO</span>
                    </h1>
                    <span class="text-xs text-teal-300 font-medium mt-0.5">Report Explorer & Analytics</span>
                </div>
            </div>
            <div>
                <a href="/dashboard" class="inline-flex items-center gap-2 px-5 py-2.5 bg-teal-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105 border border-teal-700">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-[1400px] pb-32">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-3 space-y-4">
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
                    <div class="bg-teal-50 px-4 py-3 border-b border-teal-100 flex justify-between items-center">
                        <h3 class="font-bold text-teal-900 uppercase text-xs tracking-wider">Report Categories</h3>
                        <a href="{{ route('child.create') }}" class="text-teal-600 hover:text-teal-800" title="Add New Root/Folder">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                    <nav class="p-2 space-y-1">
                        @foreach($categories as $category)
                        <button @click="activeCategory = {{ $category->id }}"
                            class="w-full flex items-center justify-between px-4 py-3 text-sm font-bold rounded-lg transition"
                            :class="activeCategory === {{ $category->id }} ? 'bg-teal-600 text-white shadow-md' : 'text-gray-600 hover:bg-teal-50 hover:text-teal-700'">
                            <div class="flex items-center gap-3">
                                <i class="{{ $category->icon ?? 'fas fa-folder' }}"></i>
                                <span>{{ $category->name }}</span>
                            </div>
                            <i class="fas fa-chevron-right text-xs" :class="activeCategory === {{ $category->id }} ? 'text-teal-200' : 'text-gray-300'"></i>
                        </button>
                        @endforeach
                    </nav>
                </div>

                <div class="bg-teal-800 rounded-xl shadow-lg p-6 text-white text-center">
                    <i class="fas fa-print text-4xl mb-3 text-teal-300 opacity-50"></i>
                    <p class="text-sm font-medium mb-3">Need a custom report?</p>
                    <button class="w-full bg-white text-teal-900 font-bold py-2 rounded shadow hover:bg-teal-50 transition text-xs">Request Layout</button>
                </div>
            </div>

            <div class="lg:col-span-9">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800" x-text="categoryName"></h2>
                    <div class="flex items-center gap-3">
                        <!-- Add Child Button -->
                        <a :href="'/child/create?parent_id=' + activeCategory" class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow transition">
                            <i class="fas fa-plus mr-1"></i> Add Item
                        </a>

                        <!-- Delete Button (New) -->
                        <a :href="'/delete/confirm?id=' + activeCategory" class="bg-red-100 hover:bg-red-200 text-red-600 px-4 py-2 rounded-lg text-sm font-bold shadow-sm transition border border-red-200" title="Delete this Folder/Report">
                            <i class="fas fa-times"></i>
                        </a>

                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            <input type="text" x-model="search" placeholder="Find report..." class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 outline-none w-64">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <template x-for="report in filteredReports" :key="report.id">
                        <div class="bg-white rounded-xl shadow border border-gray-200 hover:border-teal-500 hover:shadow-lg transition group relative overflow-hidden flex flex-col h-full">
                            <div class="h-1 w-full bg-gradient-to-r from-teal-400 to-blue-500"></div>

                            <div class="p-6 flex-1">
                                <div class="w-12 h-12 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition">
                                    <i :class="report.icon || 'fas fa-file-alt'"></i>
                                </div>
                                <h3 class="font-bold text-lg text-gray-900 mb-2" x-text="report.name"></h3>
                                <p class="text-sm text-gray-500" x-text="report.description || 'No description available.'"></p>
                            </div>

                            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-between items-center">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">PDF / Excel</span>
                                <!-- If it's a folder, View opens it (maybe in future?). For now assume reports are leaves -->
                                <button @click="runReport(report)" class="text-teal-600 font-bold text-sm hover:text-teal-800 flex items-center gap-1">
                                    View Report <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <div x-show="filteredReports.length === 0" class="text-center py-20 bg-white rounded-xl border border-dashed border-gray-300">
                    <i class="fas fa-folder-open text-4xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 font-medium">No reports found in this category.</p>
                </div>
            </div>

        </div>

    </div>

    <script>
        function reportHub() {
            return {
                activeCategory: {
                    {
                        $categories - > first() - > id ?? 'null'
                    }
                },
                search: '',
                categories: @json($categories),

                get filteredReports() {
                    if (!this.activeCategory) return [];

                    const category = this.categories.find(c => c.id === this.activeCategory);
                    if (!category || !category.children) return [];

                    return category.children.filter(r =>
                        r.name.toLowerCase().includes(this.search.toLowerCase())
                    );
                },

                get categoryName() {
                    const cat = this.categories.find(c => c.id === this.activeCategory);
                    return cat ? cat.name : 'Reports';
                },

                runReport(report) {
                    if (report.type === 'folder') {
                        // If we support nested folders, we'd switch activeCategory here.
                        // For now, assuming reports are items.
                        alert('Nested folders not fully supported in this view yet.');
                    } else {
                        window.location.href = '/reports/view/' + report.id;
                    }
                }
            }
        }
    </script>
</body>

</html>