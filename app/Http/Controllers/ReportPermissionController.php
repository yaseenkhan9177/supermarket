<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Support\Facades\DB;

class ReportPermissionController extends Controller
{
    public function index()
    {
        // Fetch all reports to display in the manager
        // We can sort them or group them if needed. 
        // For now, simple list. Use view binding in Blade for better UX.
        $reports = Report::orderBy('name')->get();
        return view('reports.restrict', compact('reports'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'reports' => 'required|array',
        ]);

        try {
            DB::transaction(function () use ($request) {
                foreach ($request->reports as $reportData) {
                    // Update only if ID exists
                    Report::where('id', $reportData['id'])->update([
                        'is_hidden_global' => $reportData['is_hidden_global'],
                        'is_owner_only' => $reportData['is_owner_only'],
                        'requires_permission' => $reportData['requires_permission'],
                    ]);
                }
            });

            return response()->json(['message' => 'Permissions updated successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error updating permissions'], 500);
        }
    }
}
