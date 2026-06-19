<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ReportLayout;
use Illuminate\Support\Facades\Auth;

class ReportLayoutController extends Controller
{
    public function index()
    {
        return view('reports.layout');
    }

    public function store(Request $request)
    {
        $request->validate([
            'layout_name' => 'required|string|max:50',
            'columns' => 'required|json', // The JS sends a JSON string
        ]);

        ReportLayout::create([
            'user_id' => Auth::id(), // Can be null if no auth, but migration allows nullable
            'report_type' => 'sales', // In real app, get from request or context
            'layout_name' => $request->layout_name,
            'visible_columns' => json_decode($request->columns),
            'is_default' => false,
        ]);

        return redirect('/reports')->with('success', 'Custom Layout Saved!');
    }
}
