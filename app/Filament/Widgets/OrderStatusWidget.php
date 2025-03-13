<?php
namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\Widget;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters; 
use Illuminate\Database\Eloquent\Builder;



class OrderStatusWidget extends Widget
{
    protected static string $view = 'filament.widgets.order-status-widget';

    use InteractsWithPageFilters;
 
    public function getViewData(): array
    {
        return [
            'pendingCount' => Order::where('status', 'pending')->count(),
            'processingCount' => Order::where('status', 'processing')->count(),
            'shippedCount' => Order::where('status', 'shipped')->count(),
            'completedCount' => Order::where('status', 'completed')->count(),
            'canceledCount' => Order::where('status', 'canceled')->count(),
        ];
    }
}
