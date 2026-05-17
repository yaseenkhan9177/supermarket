<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reminder;
use Carbon\Carbon;

class ReminderController extends Controller
{
    public function index()
    {
        // Get reminders sorted by nearest date first
        $reminders = Reminder::where('is_completed', false)
            ->whereDate('due_date', '>=', Carbon::today())
            ->orderBy('due_date', 'asc')
            ->orderBy('due_time', 'asc')
            ->get();

        return view('reminders.index', compact('reminders'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'due_time' => 'required',
            'priority' => 'required|in:high,normal',
        ]);

        Reminder::create($request->all());

        return back()->with('success', 'Reminder created successfully!');
    }

    public function edit($id)
    {
        $reminder = Reminder::findOrFail($id);

        // Get all reminders for the list
        $reminders = Reminder::where('is_completed', false)
            ->whereDate('due_date', '>=', Carbon::today())
            ->orderBy('due_date', 'asc')
            ->orderBy('due_time', 'asc')
            ->get();

        return view('reminders.index', compact('reminders', 'reminder'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'due_date' => 'required|date',
            'due_time' => 'required',
            'priority' => 'required|in:high,normal',
        ]);

        $reminder = Reminder::findOrFail($id);
        $reminder->update($request->all());

        return redirect()->route('reminders.index')->with('success', 'Reminder updated successfully!');
    }

    public function destroy($id)
    {
        // In the legacy app, this was "Erase" or "Done"
        // We can either soft delete it or mark it as complete
        $reminder = Reminder::findOrFail($id);
        $reminder->delete();

        return back()->with('success', 'Reminder removed.');
    }

    public function checkDueReminders()
    {
        $now = Carbon::now();

        // Find reminders due NOW or in the past that haven't been shown yet
        $dueReminders = Reminder::where('is_shown_popup', false)
            ->where('due_date', '<=', $now->format('Y-m-d'))
            ->where('due_time', '<=', $now->format('H:i:s'))
            ->get();

        if ($dueReminders->count() > 0) {
            // Mark them as shown so they don't pop up again next minute
            foreach ($dueReminders as $reminder) {
                $reminder->update(['is_shown_popup' => true]);
            }

            return response()->json([
                'status' => 'found',
                'reminders' => $dueReminders
            ]);
        }

        return response()->json(['status' => 'none']);
    }
}
