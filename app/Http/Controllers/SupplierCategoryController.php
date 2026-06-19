<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SupplierCategory;

class SupplierCategoryController extends Controller
{
    public function index()
    {
        $categories = SupplierCategory::withCount('suppliers')->latest()->get();
        return view('supplier_categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:supplier_categories,name',
        ]);

        SupplierCategory::create(['name' => $request->name]);

        return redirect()->back()->with('success', 'Category added.');
    }

    public function update(Request $request, $id)
    {
        $cat = SupplierCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100|unique:supplier_categories,name,' . $cat->id,
        ]);

        $cat->update(['name' => $request->name]);

        return redirect()->back()->with('success', 'Category updated.');
    }

    public function destroy($id)
    {
        SupplierCategory::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Category deleted.');
    }
}
