<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Optimize query by grouping counts in a single query
        $orderCounts = Order::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Define statuses and their counts, defaulting to 0 if not present
        $statuses = [
            'pending' => $orderCounts['pending'] ?? 0,
            'processing' => $orderCounts['processing'] ?? 0,
            'shipped' => $orderCounts['shipped'] ?? 0,
            'completed' => $orderCounts['completed'] ?? 0,
            'canceled' => $orderCounts['canceled'] ?? 0,
        ];

        return [
            Stat::make('Pending Orders', $statuses['pending'])
                ->description('All Time')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
            Stat::make('Processing Orders', $statuses['processing'])
                ->description('All Time')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
            Stat::make('Shipped Orders', $statuses['shipped'])
                ->description('All Time')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
            Stat::make('Completed Orders', $statuses['completed'])
                ->description('All Time')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            // Optionally remove the 'Canceled Orders' stat to limit to 4
            Stat::make('Canceled Orders', $statuses['canceled'])
                ->description('All Time')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('danger'),
        ];
    }

    // Set the number of columns for the stats grid
    protected function getColumns(): int
    {
        return 4;
    }
}