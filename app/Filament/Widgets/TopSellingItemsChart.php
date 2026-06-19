<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use App\Models\SaleItem;

class TopSellingItemsChart extends ChartWidget
{
    protected static ?string $heading = 'Top 5 Best Selling Items';

    protected static ?int $sort = 3; // Position on the dashboard

    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // 1. Fetch Top 5 Items by Quantity Sold
        $data = SaleItem::select('item_name', DB::raw('sum(qty) as total'))
            ->groupBy('item_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // 2. Separate Names and Totals for the Chart
        return [
            'datasets' => [
                [
                    'label' => 'Quantity Sold',
                    'data' => $data->pluck('total'),
                    'backgroundColor' => [
                        '#10b981', // Emerald 500
                        '#3b82f6', // Blue 500
                        '#f59e0b', // Amber 500
                        '#ef4444', // Red 500
                        '#8b5cf6', // Violet 500
                    ],
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => $data->pluck('item_name'),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; // Options: 'line', 'bar', 'bubble', 'doughnut', 'pie'
    }
}
