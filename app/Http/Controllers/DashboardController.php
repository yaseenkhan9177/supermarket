<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user(); // Strictly Web Guard User
        $store = $user->store;

        // 1. Calculate Real Financials
        $salesToday = \App\Models\Sale::whereDate('sale_date', today())->sum('grand_total');
        // $salesMonth = \App\Models\Sale::whereMonth('created_at', now()->month)->sum('grand_total'); // Can be used for charts

        $todaySales = \App\Models\Sale::whereDate('sale_date', today())
            ->with('customer:id,name')
            ->withCount('items')
            ->latest('sale_date')
            ->get();

        // Real low-stock count: items below threshold OR completely out of stock
        $lowStockCount = \App\Models\Item::where(function ($q) {
            $q->where(function ($inner) {
                $inner->whereNotNull('min_stock_level')
                      ->where('min_stock_level', '>', 0)
                      ->whereColumn('on_hand', '<', 'min_stock_level');
            })->orWhere('on_hand', '<=', 0);
        })->count();

        // KPI Cards
        $kpis = [
            'daily_sales'        => $salesToday,          // Real Data
            'daily_transactions' => $todaySales->count(), // Real Data
            'cash_in_hand'       => 32000,                // Still static, can link to GL if needed later
            'receivables'        => 18500,                // Still static
            'low_stock_count'    => $lowStockCount,       // Real Data
            'expiring_count'     => 4,                    // items
        ];

        // Placeholder Data for Charts
        $salesData = [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'sales' => [12000, 19000, 3000, 5000, 20000, 30000, 45000],
            'purchases' => [8000, 12000, 20000, 5000, 10000, 15000, 10000],
        ];

        // Real low-stock items for the dashboard widget (top 5, worst first)
        $lowStockItems = \App\Models\Item::where(function ($q) {
            $q->where(function ($inner) {
                $inner->whereNotNull('min_stock_level')
                      ->where('min_stock_level', '>', 0)
                      ->whereColumn('on_hand', '<', 'min_stock_level');
            })->orWhere('on_hand', '<=', 0);
        })
        ->with('preferredSupplier:id,name')
        ->orderByRaw('on_hand ASC')
        ->limit(5)
        ->get()
        ->map(fn($item) => [
            'name'     => $item->description ?? $item->code,
            'qty'      => (float) $item->on_hand,
            'supplier' => optional($item->preferredSupplier)->name ?? '—',
            'status'   => $item->on_hand <= 0 ? 'out' : 'low',
        ])
        ->all();

        $expiringItems = [
            ['name' => 'Augmentin 625mg', 'batch' => 'B123', 'expiry' => '2026-02-01', 'days' => 12],
            ['name' => 'Flagyl 400mg', 'batch' => 'F456', 'expiry' => '2026-01-25', 'days' => 5],
        ];

        // NEW: Specific Chart Data (Mock)
        $chartData = [
            'paid_vs_unpaid' => [
                'labels' => ['Paid', 'Unpaid'],
                'data' => [65, 35], // Percentage or Value
            ],
            'daily_debit' => [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'data' => [5000, 7000, 4000, 8000, 6000, 9000, 12000],
            ],
            'monthly_sales' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'data' => [45000, 52000, 48000, 61000, 58000, 75000, 82000, 79000, 86000, 91000, 95000, 108000],
            ],
            'customer_balance' => [
                'labels' => ['John Doe', 'Jane Smith', 'Ali Khan', 'Mike Ross', 'Sarah Lee'],
                'data' => [15000, 12000, 9500, 8000, 5000],
            ],
            'cash_flow' => [
                'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                'inflow' => [30000, 45000, 32000, 50000],
                'outflow' => [20000, 25000, 22000, 28000],
            ],
        ];

        // NEW: Recent Activity (Mock)
        $recentActivities = [
            ['action' => 'Sale', 'description' => 'New sale recorded #INV-001', 'time' => '10 mins ago', 'icon' => 'fa-shopping-cart', 'color' => 'text-green-600', 'bg' => 'bg-green-100'],
            ['action' => 'Stock', 'description' => 'Low stock alert: Panadol', 'time' => '1 hour ago', 'icon' => 'fa-exclamation-triangle', 'color' => 'text-orange-600', 'bg' => 'bg-orange-100'],
            ['action' => 'Payment', 'description' => 'Payment received from John', 'time' => '2 hours ago', 'icon' => 'fa-money-bill-wave', 'color' => 'text-blue-600', 'bg' => 'bg-blue-100'],
            ['action' => 'User', 'description' => 'New user registered', 'time' => '5 hours ago', 'icon' => 'fa-user-plus', 'color' => 'text-purple-600', 'bg' => 'bg-purple-100'],
            ['action' => 'Return', 'description' => 'Item returned #REC-009', 'time' => '1 day ago', 'icon' => 'fa-undo', 'color' => 'text-red-600', 'bg' => 'bg-red-100'],
        ];

        return view('dashboard', compact('user', 'store', 'kpis', 'salesData', 'lowStockItems', 'expiringItems', 'chartData', 'recentActivities', 'todaySales'));
    }
}
