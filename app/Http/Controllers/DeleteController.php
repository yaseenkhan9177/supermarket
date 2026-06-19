<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Support\Facades\DB;

class DeleteController extends Controller
{
    // Show Confirmation Screen
    public function confirm(Request $request)
    {
        $id = $request->query('id');

        if (!$id) {
            return redirect('/reports')->with('error', 'No item selected to delete.');
        }

        $item = Report::findOrFail($id);

        // Count children if it's a folder
        $children_count = 0;
        if ($item->type === 'folder') {
            $children_count = Report::where('parent_id', $id)->count();
        }

        return view('delete.confirm', compact('item', 'children_count'));
    }

    // Actual Logic
    public function destroy(Request $request)
    {
        $item = Report::findOrFail($request->id);

        // Optional: Prevent deleting System Folders
        // if ($item->is_system) { return back()->with('error', 'Cannot delete system folders.'); }

        $name = $item->name;
        $item->delete(); // This triggers Cascade Delete in DB if set up, or Model events

        return redirect('/reports')->with('success', "Item '$name' has been deleted.");
    }
}
