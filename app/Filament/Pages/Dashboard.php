<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static string $view = 'filament.pages.dashboard';

    public function getViewData(): array
    {
        $dailySales = \App\Models\Sale::whereDate('sale_date', today())->sum('grand_total');

        return [
            'dailySales' => $dailySales,
            'chartData' => [
                'paid_vs_unpaid' => [
                    'labels' => ['Paid', 'Unpaid'],
                    'data' => [65, 35],
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
            ],
            'recentActivities' => [
                ['action' => 'Sale', 'description' => 'New sale recorded #INV-001', 'time' => '10 mins ago', 'icon' => 'fa-shopping-cart', 'color' => 'text-green-600', 'bg' => 'bg-green-100'],
                ['action' => 'Stock', 'description' => 'Low stock alert: Panadol', 'time' => '1 hour ago', 'icon' => 'fa-exclamation-triangle', 'color' => 'text-orange-600', 'bg' => 'bg-orange-100'],
                ['action' => 'Payment', 'description' => 'Payment received from John', 'time' => '2 hours ago', 'icon' => 'fa-money-bill-wave', 'color' => 'text-blue-600', 'bg' => 'bg-blue-100'],
                ['action' => 'User', 'description' => 'New user registered', 'time' => '5 hours ago', 'icon' => 'fa-user-plus', 'color' => 'text-purple-600', 'bg' => 'bg-purple-100'],
                ['action' => 'Return', 'description' => 'Item returned #REC-009', 'time' => '1 day ago', 'icon' => 'fa-undo', 'color' => 'text-red-600', 'bg' => 'bg-red-100'],
            ],
        ];
    }
}
