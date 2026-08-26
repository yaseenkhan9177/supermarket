<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        ExpenseCategory::seedDefaultsIfEmpty();

        $categories = ExpenseCategory::withCount('expenses')
            ->orderBy('name')
            ->paginate(15);

        return view('expenses.categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:expense_categories,name',
            'code'        => 'nullable|string|max:50|unique:expense_categories,code',
            'description' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);

        $category = ExpenseCategory::create([
            'name'        => trim($validated['name']),
            'code'        => !empty($validated['code']) ? strtoupper(trim($validated['code'])) : null,
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        AuditLog::record(
            'expense_category_created',
            "Created expense category: {$category->name}",
            'ExpenseCategory',
            $category->id
        );

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Expense category created successfully.',
                'category' => $category,
            ]);
        }

        return redirect()->back()->with('success', "Category '{$category->name}' created successfully.");
    }

    public function update(Request $request, $id)
    {
        $category = ExpenseCategory::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:expense_categories,name,' . $category->id,
            'code'        => 'nullable|string|max:50|unique:expense_categories,code,' . $category->id,
            'description' => 'nullable|string|max:255',
            'is_active'   => 'nullable|boolean',
        ]);

        $category->update([
            'name'        => trim($validated['name']),
            'code'        => !empty($validated['code']) ? strtoupper(trim($validated['code'])) : null,
            'description' => $validated['description'] ?? null,
            'is_active'   => $request->has('is_active') ? (bool) $request->is_active : true,
        ]);

        AuditLog::record(
            'expense_category_updated',
            "Updated expense category: {$category->name}",
            'ExpenseCategory',
            $category->id
        );

        return redirect()->back()->with('success', "Category '{$category->name}' updated successfully.");
    }

    public function destroy($id)
    {
        $category = ExpenseCategory::withCount('expenses')->findOrFail($id);

        if ($category->expenses_count > 0) {
            return redirect()->back()->with('error', "Cannot delete category '{$category->name}' because {$category->expenses_count} expense(s) are linked to it. You can deactivate it instead.");
        }

        $categoryName = $category->name;
        $category->delete();

        AuditLog::record(
            'expense_category_deleted',
            "Deleted expense category: {$categoryName}",
            'ExpenseCategory',
            $id
        );

        return redirect()->back()->with('success', "Category '{$categoryName}' deleted successfully.");
    }
}
