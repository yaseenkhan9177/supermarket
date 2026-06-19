<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Child Item | OwnStore PRO</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800" x-data="childForm()">

    <nav class="bg-teal-900 border-b border-teal-800 px-6 py-3 shadow-sm sticky top-0 z-50 mb-8 text-white">
        <div class="container mx-auto max-w-[1400px] flex justify-between items-center">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-teal-700 flex items-center justify-center text-white shadow-md border border-teal-600">
                    <i class="fas fa-level-down-alt text-lg"></i>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-xl font-extrabold text-white leading-none tracking-tight">
                        OwnStore <span class="text-teal-400">PRO</span>
                    </h1>
                    <span class="text-xs text-teal-300 font-medium mt-0.5">Hierarchy Manager (Add Child)</span>
                </div>
            </div>
            <div>
                <a href="/reports" class="inline-flex items-center gap-2 px-5 py-2.5 bg-teal-800 hover:bg-black text-white text-sm font-bold rounded-lg shadow-sm transition transform hover:scale-105 border border-teal-700">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 max-w-2xl pb-32">

        <form action="/child/store" method="POST" class="bg-white rounded-xl shadow-2xl border border-teal-100 overflow-hidden relative">
            @csrf

            <div class="absolute top-0 left-8 h-12 w-1 bg-teal-200 z-0"></div>

            <div class="bg-teal-50 px-8 py-6 border-b border-teal-100 relative z-10">
                <div class="flex items-start gap-4">
                    <div class="flex flex-col items-center">
                        <div class="w-12 h-12 rounded bg-white border border-teal-200 flex items-center justify-center text-teal-600 shadow-sm mb-1">
                            <i class="fas fa-folder-open text-xl"></i>
                        </div>
                        <div class="h-6 w-0.5 bg-teal-300"></div>
                        <div class="w-10 h-10 rounded bg-teal-600 text-white flex items-center justify-center shadow-lg ring-4 ring-teal-50">
                            <i class="fas fa-plus"></i>
                        </div>
                    </div>

                    <div class="flex-1">
                        <h2 class="text-xl font-bold text-teal-900">Add Child Node</h2>
                        <p class="text-sm text-teal-600 mt-1">You are adding a sub-item to:</p>

                        <div class="mt-2 bg-white border border-teal-200 rounded-lg p-3 flex items-center gap-3 shadow-sm">
                            <i class="fas fa-folder text-yellow-500"></i>
                            <div>
                                <span class="block text-xs text-gray-400 font-bold uppercase">Parent Item</span>
                                <span class="font-bold text-gray-800">{{ $parent->name ?? 'Root Directory' }}</span>
                            </div>
                            <input type="hidden" name="parent_id" value="{{ $parent->id ?? '' }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-8 space-y-6">

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Item Type</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="folder" x-model="type" class="peer sr-only">
                            <div class="p-4 rounded-lg border-2 border-gray-200 peer-checked:border-teal-500 peer-checked:bg-teal-50 transition text-center hover:bg-gray-50">
                                <i class="fas fa-folder text-2xl mb-2 text-yellow-500"></i>
                                <span class="block text-sm font-bold text-gray-700">Sub-Folder</span>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="report" x-model="type" class="peer sr-only">
                            <div class="p-4 rounded-lg border-2 border-gray-200 peer-checked:border-teal-500 peer-checked:bg-teal-50 transition text-center hover:bg-gray-50">
                                <i class="fas fa-file-alt text-2xl mb-2 text-teal-500"></i>
                                <span class="block text-sm font-bold text-gray-700">Report / Item</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Name / Title</label>
                        <input type="text" name="name" placeholder="e.g. Monthly Sales Summary" required class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-teal-500 outline-none text-gray-800">
                    </div>

                    <div x-show="type === 'report'" x-transition>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Description (Optional)</label>
                        <textarea name="description" rows="2" placeholder="What does this report do?" class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-teal-500 outline-none text-sm"></textarea>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Select Icon</label>
                    <div class="flex gap-3 flex-wrap">
                        <template x-for="icon in icons" :key="icon">
                            <label class="cursor-pointer">
                                <input type="radio" name="icon" :value="icon" class="peer sr-only">
                                <div class="w-10 h-10 rounded border border-gray-200 flex items-center justify-center text-gray-400 peer-checked:bg-teal-600 peer-checked:text-white peer-checked:border-teal-600 hover:bg-gray-50 transition">
                                    <i :class="icon"></i>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

            </div>

            <div class="bg-gray-50 px-8 py-5 border-t border-gray-100 flex justify-end gap-3">
                <button type="button" onclick="history.back()" class="px-6 py-2.5 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition">Cancel</button>
                <button type="submit" class="px-8 py-2.5 bg-teal-600 text-white font-bold rounded-lg shadow-lg hover:bg-teal-700 transition transform hover:-translate-y-0.5">
                    <i class="fas fa-save mr-2"></i> Create Child
                </button>
            </div>

        </form>

    </div>

    <script>
        function childForm() {
            return {
                type: 'report',
                icons: [
                    'fas fa-file-invoice',
                    'fas fa-chart-line',
                    'fas fa-users',
                    'fas fa-box-open',
                    'fas fa-calculator',
                    'fas fa-calendar-alt',
                    'fas fa-folder',
                    'fas fa-folder-open'
                ]
            }
        }
    </script>
</body>

</html>