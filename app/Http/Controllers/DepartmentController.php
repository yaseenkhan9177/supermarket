<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Search departments by name.
     */
    public function search(Request $request)
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) > 0) {
            $departments = Department::where('name', 'like', "%{$query}%")
                ->orderBy('name', 'asc')
                ->limit(30)
                ->get(['id', 'name']);
        } else {
            $departments = Department::orderBy('name', 'asc')
                ->limit(30)
                ->get(['id', 'name']);
        }

        return response()->json($departments);
    }

    /**
     * Store a new department.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $name = trim($request->name);

        $department = Department::firstOrCreate(
            ['name' => $name]
        );

        return response()->json($department, 201);
    }
}
