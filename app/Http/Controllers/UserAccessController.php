<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserPermission;

class UserAccessController extends Controller
{
    public function index(Request $request)
    {
        // 1. Fetch Users and Employees
        $users = User::with('userPermissions')->get();
        $employees = \App\Models\Employee::with('userPermissions')->get();

        // 2. Merge them into a single collection for the roster
        // We add a 'type' attribute to distinguish them in the view/URL
        $allStaff = $users->map(function ($u) {
            $u->type = 'user';
            return $u;
        })
            ->concat($employees->map(function ($e) {
                $e->type = 'employee';
                return $e;
            }));

        // 3. Determine selected user
        $selectedUser = null;
        if ($request->has('type') && $request->has('id')) {
            if ($request->type === 'user') {
                $selectedUser = $users->find($request->id);
                if ($selectedUser) $selectedUser->type = 'user';
            } elseif ($request->type === 'employee') {
                $selectedUser = $employees->find($request->id);
                if ($selectedUser) $selectedUser->type = 'employee';
            }
        } else {
            $selectedUser = $allStaff->first();
        }

        return view('settings.users', [
            'users' => $allStaff, // We pass the merged list as 'users' for the view loop
            'selectedUser' => $selectedUser
        ]);
    }

    public function update(Request $request, $id)
    {
        // We need to know if updating a User or Employee.
        // We can pass this as a query param or hidden input.
        $type = $request->input('model_type', 'user');

        $model = null;
        if ($type === 'user') {
            $model = User::findOrFail($id);
        } else {
            $model = \App\Models\Employee::findOrFail($id);
        }

        // 1. Update Profile (if supported/needed here)
        // ... (Skipping simple profile update for now unless requested)

        // 2. Update Permissions

        // Helper to build module permissions
        $buildModulePerms = function ($module) use ($request) {
            return [
                'view' => $request->has("{$module}_view"),
                'add'  => $request->has("{$module}_add"),
                'edit' => $request->has("{$module}_edit"),
                'del'  => $request->has("{$module}_del"),
            ];
        };

        $modules = [
            'sales_cash',
            'sales_debt',
            'sales_return_cash',
            'sales_return_crdt',
            'inventory_transfer',
            'accounts_receipts',
            'accounts_payments',
            'items_stock'
        ];

        $permissionData = [];
        foreach ($modules as $module) {
            $permissionData[$module] = $buildModulePerms($module);
        }

        UserPermission::updateOrCreate(
            [
                'model_id' => $model->id,
                'model_type' => get_class($model)
            ],
            [
                'sales_cash' => $permissionData['sales_cash'],
                'sales_debt' => $permissionData['sales_debt'],
                'sales_return_cash' => $permissionData['sales_return_cash'],
                'sales_return_crdt' => $permissionData['sales_return_crdt'],
                'inventory_transfer' => $permissionData['inventory_transfer'],
                'accounts_receipts' => $permissionData['accounts_receipts'],
                'accounts_payments' => $permissionData['accounts_payments'],
                'items_stock' => $permissionData['items_stock'],

                'can_change_discount' => $request->has('can_change_discount'),
                'can_close_session' => $request->has('can_close_session'),
                'allow_credit_override' => $request->has('allow_credit_override'),
                'view_all_counters' => $request->has('view_all_counters'),

                'min_qty_limit' => $request->input('min_qty_limit', 0),

                'sys_add_users' => $request->has('sys_add_users'),
                'sys_restore_data' => $request->has('sys_restore_data'),
                'sys_view_reports' => $request->has('sys_view_reports'),
                'sys_reconcile_banks' => $request->has('sys_reconcile_banks'),
            ]
        );

        return redirect()->route('settings.users', ['type' => $type, 'id' => $id])
            ->with('success', 'Access rights updated successfully.');
    }
}
