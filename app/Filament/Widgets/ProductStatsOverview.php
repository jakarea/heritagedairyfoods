<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ProductStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    { 
        $products = Product::get();
        $totalProducts = $products->count();
        $simpleProducts = $products->where('type','simple')->count();
        $variableProducts = $products->where('type','variable')->count();
        $totalUsers = User::count();

        return [
            Stat::make('Total Products', $totalProducts)
                ->description('All Time')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
            Stat::make('Simple Products', $simpleProducts)
                ->description('All Time')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
            Stat::make('Variable Products', $variableProducts)
                ->description('All Time')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
            Stat::make('Total Users', $totalUsers)
                ->description('All Time')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'), 
        ];
    }

    // Set the number of columns for the stats grid
    protected function getColumns(): int
    {
        return 4;
    }
}
