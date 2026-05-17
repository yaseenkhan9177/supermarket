<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        // Group tasks so we can easily pass them to the 3 columns
        $tasks = [
            'todo' => Task::where('status', 'todo')->orderBy('order')->get(),
            'in_progress' => Task::where('status', 'in_progress')->orderBy('order')->get(),
            'completed' => Task::where('status', 'completed')->orderBy('updated_at', 'desc')->get(),
        ];

        return view('todo.index', compact('tasks'));
    }

    // Store new task
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
        ]);

        Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'status' => 'todo',
            'progress_percent' => 0,
            'assigned_to' => auth()->id(), // Assign to current user
        ]);

        return redirect()->route('todo')->with('success', 'Task created successfully!');
    }

    // Handle Drag & Drop AJAX Update
    public function updateStatus(Request $request)
    {
        $task = Task::find($request->id);

        if ($task) {
            $task->status = $request->status;

            // Auto-update progress if moved to completed or todo
            if ($request->status == 'completed') {
                $task->progress_percent = 100;
            } elseif ($request->status == 'todo') {
                $task->progress_percent = 0;
            }

            $task->save();
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 404);
    }
}
