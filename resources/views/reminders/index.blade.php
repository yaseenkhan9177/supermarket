x
<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800">

    <!-- Header with Logo and Home Icon -->
    <div class="bg-white shadow-md border-b border-gray-200 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <!-- Logo Section -->
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-10 rounded-lg shadow-sm">
                    <div>
                        <h1 class="text-xl font-black text-gray-900 tracking-tight">
                            OwnStore <span class="text-xs uppercase tracking-widest text-indigo-500 ml-1">PRO</span>
                        </h1>
                        <p class="text-xs text-gray-500">Reminder Management</p>
                    </div>
                </div>

                <!-- Home Icon -->
                <a href="/admin" class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow transition group">
                    <i class="fas fa-home"></i>
                    <span class="hidden sm:inline">Dashboard</span>
                </a>
            </div>
        </div>
    </div>

    <div class="container mx-auto p-6 max-w-7xl">

        @if(session('success'))
        <div id="successMessage" class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button onclick="document.getElementById('successMessage').remove()" class="text-green-700 hover:text-green-900">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <script>
            setTimeout(() => {
                const msg = document.getElementById('successMessage');
                if (msg) msg.remove();
            }, 3000);
        </script>
        @endif

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Set Reminder</h1>
            <p class="text-gray-500">Create specific alerts for important business events.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <div class="lg:col-span-5">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                    <h2 class="text-lg font-bold text-gray-800 mb-4">
                        {{ isset($reminder) ? 'Edit Reminder' : 'Create New Reminder' }}
                    </h2>

                    <form action="{{ isset($reminder) ? route('reminders.update', $reminder->id) : route('reminders.store') }}" method="POST" x-data="{ priority: '{{ isset($reminder) ? $reminder->priority : 'normal' }}' }">
                        @csrf

                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Reminder Title</label>
                            <input type="text" name="title" value="{{ isset($reminder) ? $reminder->title : '' }}" placeholder="e.g. Pay Electricity Bill" required
                                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-3 outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Date</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <i class="far fa-calendar text-gray-400"></i>
                                    </div>
                                    <input type="date" name="due_date" value="{{ isset($reminder) ? $reminder->due_date->format('Y-m-d') : '' }}" required
                                        class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5 outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Time</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                        <i class="far fa-clock text-gray-400"></i>
                                    </div>
                                    <input type="time" name="due_time" value="{{ isset($reminder) ? \Carbon\Carbon::parse($reminder->due_time)->format('H:i') : '' }}" required
                                        class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5 outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="mb-8">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Priority Level</label>
                            <input type="hidden" name="priority" x-model="priority">

                            <div class="grid grid-cols-2 gap-4">
                                <div @click="priority = 'high'"
                                    :class="priority === 'high' ? 'border-red-500 bg-red-50 text-red-600' : 'border-gray-200 bg-white text-gray-500 hover:bg-gray-50'"
                                    class="cursor-pointer border rounded-lg p-3 flex flex-col items-center justify-center transition">
                                    <i class="fas fa-exclamation-circle text-lg mb-1"></i>
                                    <span class="text-sm font-bold">High</span>
                                </div>

                                <div @click="priority = 'normal'"
                                    :class="priority === 'normal' ? 'border-indigo-500 bg-indigo-50 text-indigo-600' : 'border-gray-200 bg-white text-gray-500 hover:bg-gray-50'"
                                    class="cursor-pointer border rounded-lg p-3 flex flex-col items-center justify-center transition">
                                    <i class="fas fa-bell text-lg mb-1"></i>
                                    <span class="text-sm font-bold">Normal</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            @if(isset($reminder))
                            <a href="{{ route('reminders.index') }}" class="flex-1 text-center text-gray-700 bg-gray-200 hover:bg-gray-300 font-medium rounded-lg text-sm px-5 py-3 transition shadow-lg">
                                Cancel
                            </a>
                            <button type="submit" class="flex-1 text-white bg-indigo-600 hover:bg-indigo-700 font-medium rounded-lg text-sm px-5 py-3 text-center transition shadow-lg">
                                <i class="fas fa-save mr-2"></i> Update Reminder
                            </button>
                            @else
                            <button type="submit" class="w-full text-white bg-black hover:bg-gray-800 font-medium rounded-lg text-sm px-5 py-3 text-center transition shadow-lg">
                                <i class="fas fa-plus-circle mr-2"></i> Create Reminder
                            </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="lg:col-span-7">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Upcoming Alerts</h3>

                <div class="space-y-0 relative">
                    <div class="absolute left-6 top-4 bottom-4 w-0.5 bg-gray-200 z-0"></div>

                    @forelse($reminders as $reminder)
                    <div class="relative z-10 pl-0 pb-6 group">
                        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex items-start gap-4 transition hover:shadow-md">

                            <div class="flex-shrink-0">
                                @if($reminder->priority == 'high')
                                <div class="w-12 h-12 rounded-full bg-red-50 border border-red-100 flex items-center justify-center text-red-500 shadow-sm">
                                    <i class="fas fa-exclamation text-lg"></i>
                                </div>
                                @else
                                <div class="w-12 h-12 rounded-full bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-500 shadow-sm">
                                    <i class="fas fa-bell text-lg"></i>
                                </div>
                                @endif
                            </div>

                            <div class="flex-1 pt-1">
                                <h4 class="font-bold text-gray-900 text-lg">{{ $reminder->title }}</h4>
                                <div class="text-sm text-gray-500 mt-1 flex items-center gap-3">
                                    <span><i class="far fa-clock mr-1"></i>
                                        {{ \Carbon\Carbon::parse($reminder->due_date)->isToday() ? 'Today' : \Carbon\Carbon::parse($reminder->due_date)->format('M d') }},
                                        {{ \Carbon\Carbon::parse($reminder->due_time)->format('h:i A') }}
                                    </span>
                                    @if($reminder->priority == 'high')
                                    <span class="bg-red-100 text-red-700 text-[10px] px-2 py-0.5 rounded font-bold uppercase">Urgent</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('reminders.edit', $reminder->id) }}" class="text-gray-400 hover:text-indigo-500 transition p-2">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('reminders.destroy', $reminder->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-gray-300 hover:text-red-500 transition p-2">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="pl-14 text-gray-400 italic">No upcoming reminders.</div>
                    @endforelse

                </div>

                <h3 class="text-xs font-bold text-gray-300 uppercase tracking-widest mt-4 mb-4">Past Reminders</h3>
            </div>

        </div>
    </div>

</body>

</html>