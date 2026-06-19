<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployeeDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('employee')->user();

        // Placeholder Data for Employee
        $stats = [
            'my_sales_today' => 5400,
            'my_invoices_count' => 12,
            'pending_tasks' => 3
        ];

        return view('employee.dashboard', compact('user', 'stats'));
    }
}
