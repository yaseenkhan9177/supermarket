<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
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
                        <p class="text-xs text-gray-500">Task Management</p>
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

        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Task Board</h1>
                <p class="text-gray-500">Manage daily store operations and staff tasks.</p>
            </div>
            <button onclick="document.getElementById('newTaskModal').classList.remove('hidden'); document.getElementById('newTaskModal').classList.add('flex')"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow font-medium transition">
                <i class="fas fa-plus mr-2"></i> New Task
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <!-- To Do Column -->
            <div class="flex flex-col">
                <div class="flex items-center mb-4">
                    <span class="w-2 h-2 rounded-full bg-yellow-400 mr-2"></span>
                    <h3 class="font-bold text-gray-700">To Do</h3>
                    <span class="ml-auto bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full">{{ $tasks['todo']->count() }}</span>
                </div>

                <div id="todo" class="kanban-col space-y-4 min-h-[200px]" data-status="todo">
                    @foreach($tasks['todo'] as $task)
                    @include('todo.partials.card', ['task' => $task])
                    @endforeach

                    <button onclick="document.getElementById('newTaskModal').classList.remove('hidden'); document.getElementById('newTaskModal').classList.add('flex')" class="w-full py-3 border-2 border-dashed border-gray-300 text-gray-400 rounded-xl hover:border-indigo-400 hover:text-indigo-500 transition text-sm font-medium">
                        + Add Card
                    </button>
                </div>
            </div>

            <!-- In Progress Column -->
            <div class="flex flex-col">
                <div class="flex items-center mb-4">
                    <span class="w-2 h-2 rounded-full bg-blue-500 mr-2"></span>
                    <h3 class="font-bold text-gray-700">In Progress</h3>
                    <span class="ml-auto bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full">{{ $tasks['in_progress']->count() }}</span>
                </div>

                <div id="in_progress" class="kanban-col space-y-4 min-h-[200px]" data-status="in_progress">
                    @foreach($tasks['in_progress'] as $task)
                    @include('todo.partials.card', ['task' => $task])
                    @endforeach
                </div>
            </div>

            <!-- Completed Column -->
            <div class="flex flex-col">
                <div class="flex items-center mb-4">
                    <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                    <h3 class="font-bold text-gray-700">Completed</h3>
                    <span class="ml-auto bg-gray-200 text-gray-600 text-xs px-2 py-1 rounded-full">{{ $tasks['completed']->count() }}</span>
                </div>

                <div id="completed" class="kanban-col space-y-4 min-h-[200px]" data-status="completed">
                    @foreach($tasks['completed'] as $task)
                    @include('todo.partials.card', ['task' => $task])
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <!-- New Task Modal -->
    <div id="newTaskModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
            <h2 class="text-xl font-bold mb-4 text-gray-800">Create New Task</h2>

            <form id="newTaskForm" method="POST" action="{{ route('tasks.store') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Task Title *</label>
                    <input type="text" name="title" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Enter task title">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Task details (optional)"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                        <select name="priority"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                        <input type="date" name="due_date"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeModal()"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                        <i class="fas fa-plus mr-2"></i>Create Task
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Close modal function
        function closeModal() {
            document.getElementById('newTaskModal').classList.add('hidden');
            document.getElementById('newTaskModal').classList.remove('flex');
            document.getElementById('newTaskForm').reset();
        }

        // Initialize Sortable for drag and drop
        const columns = document.querySelectorAll('.kanban-col');
        columns.forEach(col => {
            new Sortable(col, {
                group: 'shared', // Allow dragging between lists
                animation: 150,
                ghostClass: 'bg-indigo-50',
                onEnd: function(evt) {
                    let itemEl = evt.item;
                    let newStatus = evt.to.getAttribute('data-status');
                    let taskId = itemEl.getAttribute('data-id');

                    // AJAX call to update backend
                    console.log(`Moved task ${taskId} to ${newStatus}`);
                    fetch('{{ route("tasks.update-status") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            id: taskId,
                            status: newStatus
                        })
                    });
                }
            });
        });
    </script>

</body>

</html>