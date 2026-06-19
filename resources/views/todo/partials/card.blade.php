<div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 cursor-move hover:shadow-md transition group" data-id="{{ $task->id }}">

    <div class="flex justify-between items-start mb-2">
        @php
        $badgeColor = match($task->priority) {
        'high' => 'bg-red-50 text-red-600 border-red-100',
        'medium' => 'bg-orange-50 text-orange-600 border-orange-100',
        'low' => 'bg-gray-100 text-gray-600 border-gray-200',
        default => 'bg-gray-100 text-gray-600 border-gray-200',
        };
        @endphp
        <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded border {{ $badgeColor }}">
            {{ $task->priority }}
        </span>
        <button class="text-gray-300 hover:text-gray-600"><i class="fas fa-ellipsis-h"></i></button>
    </div>

    <h4 class="font-bold text-gray-800 text-sm mb-3">{{ $task->title }}</h4>

    @if($task->status === 'in_progress')
    <div class="mb-4">
        <div class="w-full bg-gray-100 rounded-full h-1.5">
            <div class="bg-blue-500 h-1.5 rounded-full" <?php echo 'style="width: ' . ($task->progress_percent ?? 0) . '%"'; ?>></div>
        </div>
        <div class="flex justify-end mt-1">
            <span class="text-xs text-gray-400 font-medium">{{ $task->progress_percent }}%</span>
        </div>
    </div>
    @elseif($task->status === 'completed')
    <div class="mb-3">
        <span class="text-xs text-green-600 font-bold"><i class="fas fa-check-circle mr-1"></i> DONE</span>
    </div>
    @endif

    <div class="flex justify-between items-center pt-2 border-t border-gray-50 mt-2">
        <div class="flex items-center text-gray-400 text-xs font-medium">
            <i class="far fa-clock mr-1.5"></i>
            {{ $task->due_date ? $task->due_date->format('M d') : 'No Date' }}
        </div>

        <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-600 border-2 border-white shadow-sm" title="Assigned to Admin">
            AD
        </div>
    </div>
</div>