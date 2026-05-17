<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Support\Facades\DB;

class ChildController extends Controller
{
    // Show the form to add a child to a specific parent
    public function create(Request $request)
    {
        $parent_id = $request->query('parent_id');
        $parent = null;

        if ($parent_id) {
            $parent = Report::find($parent_id);
        }

        return view('child.create', compact('parent'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:folder,report',
        ]);

        Report::create([
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'type' => $request->type,
            'icon' => $request->icon ?? 'fas fa-file',
            'description' => $request->description,
            'route_name' => $request->type === 'report' ? 'reports.dynamic' : null,
        ]);

        return redirect('/reports')->with('success', 'Child Item Created Successfully!');
    }
}
