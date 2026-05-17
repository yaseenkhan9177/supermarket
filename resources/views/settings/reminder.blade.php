@extends('layouts.admin')

@section('content')
<div class="h-[calc(100vh-6rem)] bg-gray-50 flex overflow-hidden font-sans" x-data="{ 
    reminderTitle: '',
    reminderDate: '',
    reminders: [
        { id: 1, title: 'Tax Filing Deadline', time: 'Today, 5:00 PM', type: 'urgent', past: false },
        { id: 2, title: 'Meeting with Distributor', time: 'Tomorrow, 10:00 AM', type: 'normal', past: false },
        { id: 3, title: 'Renew Software License', time: 'Feb 15, 2026', type: 'low', past: false },
        { id: 4, title: 'Staff Training', time: 'Yesterday', type: 'normal', past: true }
    ]
}">

    <!-- Left Panel: Create Reminder (35%) -->
    <div class="w-[35%] bg-white border-r border-gray-200 p-8 flex flex-col z-10 shadow-[4px_0_24px_rgba(0,0,0,0.02)]">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Set Reminder</h1>
        <p class="text-sm text-gray-500 mb-8">Create specific alerts for important business events.</p>

        <form class="space-y-6">
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Reminder Title</label>
                <input type="text" x-model="reminderTitle" placeholder="e.g. Pay Electricity Bill" class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 font-bold focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Date</label>
                    <input type="date" class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all outline-none cursor-pointer">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Time</label>
                    <input type="time" class="block w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500 transition-all outline-none cursor-pointer">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Priority Level</label>
                <div class="flex space-x-4">
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="priority" value="high" class="sr-only peer">
                        <div class="py-3 px-4 rounded-xl border border-gray-200 text-center text-sm font-bold text-gray-500 peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-600 transition-all">
                            <i class="fas fa-exclamation-circle mb-1 block"></i> High
                        </div>
                    </label>
                    <label class="flex-1 cursor-pointer">
                        <input type="radio" name="priority" value="normal" class="sr-only peer" checked>
                        <div class="py-3 px-4 rounded-xl border border-gray-200 text-center text-sm font-bold text-gray-500 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:text-indigo-600 transition-all">
                            <i class="fas fa-bell mb-1 block"></i> Normal
                        </div>
                    </label>
                </div>
            </div>

            <button class="w-full bg-black hover:bg-gray-800 text-white py-4 rounded-xl font-bold shadow-lg mt-4 transition-transform transform active:scale-95 flex items-center justify-center">
                <i class="fas fa-plus-circle mr-2"></i> Create Reminder
            </button>
        </form>

        <div class="mt-auto pt-8 border-t border-gray-100">
            <div class="flex items-center p-4 bg-indigo-50 rounded-xl border border-indigo-100">
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-600 mr-4">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div>
                    <h4 class="font-bold text-indigo-900 text-sm">Pro Tip</h4>
                    <p class="text-xs text-indigo-700">Set recurring reminders for monthly bills to never miss a payment.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Panel: Alert Timeline (65%) -->
    <div class="flex-1 bg-gray-50 p-8 overflow-y-auto custom-scrollbar">
        <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-6 sticky top-0 bg-gray-50 z-20 py-2">Upcoming Alerts</h2>

        <div class="relative max-w-2xl mx-auto space-y-8">
            <!-- Vertical Line -->
            <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200"></div>

            <!-- Timeline Item -->
            <template x-for="reminder in reminders.filter(r => !r.past)" :key="reminder.id">
                <div class="relative pl-16 group">
                    <!-- Dot/Icon -->
                    <div class="absolute left-0 top-1 w-12 h-12 rounded-full border-4 border-gray-50 flex items-center justify-center z-10 shadow-sm"
                        :class="{
                            'bg-red-100 text-red-600': reminder.type === 'urgent',
                            'bg-indigo-100 text-indigo-600': reminder.type === 'normal',
                            'bg-gray-100 text-gray-500': reminder.type === 'low'
                         }">
                        <i class="fas" :class="reminder.type === 'urgent' ? 'fa-exclamation' : 'fa-bell'"></i>
                    </div>

                    <!-- Card -->
                    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-200 hover:shadow-md transition-all cursor-pointer transform hover:-translate-y-0.5">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-gray-900 text-lg mb-1" x-text="reminder.title"></h3>
                                <p class="text-sm text-gray-500 font-medium flex items-center">
                                    <i class="far fa-clock mr-2"></i> <span x-text="reminder.time"></span>
                                </p>
                            </div>
                            <button class="text-gray-300 hover:text-red-500 transition-colors bg-gray-50 hover:bg-red-50 w-8 h-8 rounded-full flex items-center justify-center">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Past Section -->
            <h2 class="text-xs font-bold text-gray-400 uppercase tracking-widest pt-8 pb-2">Past Reminders</h2>
            <template x-for="reminder in reminders.filter(r => r.past)" :key="reminder.id">
                <div class="relative pl-16 group opacity-60 hover:opacity-100 transition-opacity">
                    <!-- Dot/Icon -->
                    <div class="absolute left-0 top-1 w-12 h-12 rounded-full bg-gray-200 border-4 border-gray-50 flex items-center justify-center z-10 text-gray-400">
                        <i class="fas fa-history"></i>
                    </div>

                    <!-- Card -->
                    <div class="bg-gray-100 p-5 rounded-2xl border border-gray-200">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-bold text-gray-600 text-lg mb-1 line-through" x-text="reminder.title"></h3>
                                <p class="text-sm text-gray-500 font-medium">
                                    <span x-text="reminder.time"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

        </div>
    </div>

</div>

<!-- Alpine JS -->
<script src="//unpkg.com/alpinejs" defer></script>
@endsection