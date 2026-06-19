<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Display the employee management page (Web UI)
     */
    public function webIndex()
    {
        $employees = Employee::orderBy('full_name', 'asc')->get();
        return view('employees.index', compact('employees'));
    }

    /**
     * Display a listing of the resource (API).
     */
    public function index()
    {
        // Handled by GeneralSettingsController@employees for the view, 
        // but can be used for fetching the list via API
        return response()->json(Employee::all());
    }

    public function getMeta()
    {
        $roles = \App\Models\Role::all();
        $permissions = $this->getPermissionMatrix();
        return response()->json(['roles' => $roles, 'matrix' => $permissions]);
    }

    private function getPermissionMatrix()
    {
        return [
            'Sales' => [
                'view' => 'View Sales Dashboard',
                'create' => 'Counter Sales',
                'debit' => 'Debit Sales',
                'refund' => 'Return / Refund',
                'edit' => 'Edit Sale',
                'delete' => 'Delete Sale',
                'print' => 'Print Invoice',
                'discount' => 'Apply Discount',
                'price_override' => 'Change Price',
                'hold' => 'Hold Sale'
            ],
            'Products' => [
                'view' => 'View Products',
                'create' => 'Add Product',
                'edit' => 'Edit Product',
                'delete' => 'Delete Product',
                'stock' => 'View Stock',
                'adjustment' => 'Stock Adjustment',
                'alerts' => 'Low Stock Alerts'
            ],
            'Customers' => [
                'view' => 'View Customers',
                'create' => 'Add Customer',
                'edit' => 'Edit Customer',
                'delete' => 'Delete Customer',
                'balance' => 'View Balance',
                'payment' => 'Receive Payment',
                'credit_override' => 'Credit Limit Override'
            ],
            'Payments' => [
                'view' => 'View Payments',
                'cash' => 'Receive Cash',
                'bank' => 'Bank Entry',
                'expense' => 'Expense Entry',
                'ledger' => 'View Ledger',
                'delete' => 'Delete Payment'
            ],
            'Reports' => [
                'sales' => 'Sales Report',
                'debit' => 'Debit Report',
                'profit' => 'Profit/Loss',
                'stock' => 'Stock Report',
                'ledger' => 'Customer Ledger',
                'export' => 'Export Report'
            ],
            'Employees' => [
                'view' => 'View Employees',
                'create' => 'Add Employee',
                'edit' => 'Edit Employee',
                'delete' => 'Delete Employee',
                'assign_roles' => 'Assign Roles'
            ],
            'Settings' => [
                'store' => 'Store Settings',
                'tax' => 'Tax Settings',
                'invoice' => 'Invoice Settings',
                'backup' => 'Backup',
                'restore' => 'Restore'
            ]
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'designation' => 'required|string',
            'employee_code' => 'nullable|string',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['password'] = Hash::make($request->input('password', '12345678')); // Default password if empty
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $employee = Employee::create($validated);

        return redirect()->route('employees.web.index')->with('success', 'New team member added successfully!');
    }

    public function webEdit($id)
    {
        $employee = Employee::findOrFail($id);
        $employees = Employee::orderBy('full_name', 'asc')->get();
        return view('employees.index', compact('employees', 'employee'));
    }

    public function webUpdate(Request $request, $id)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'designation' => 'required|string',
            'employee_code' => 'nullable|string',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $employee = Employee::findOrFail($id);
        $employee->update($validated);

        return redirect()->route('employees.web.index')->with('success', 'Employee updated successfully!');
    }

    public function show(string $id)
    {
        return response()->json(Employee::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $employee = Employee::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string',
            'permissions' => 'nullable|array',
        ]);

        $employee->update($validated);

        return response()->json([
            'message' => 'Employee updated successfully',
            'data' => $employee
        ]);
    }

    public function destroy(string $id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
    }
}
