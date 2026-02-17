<?php

namespace App\Livewire;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\ServiceOrder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class StatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $todaySales = Sale::whereDate('created_at', now())->sum('total');
        $weeklySales = Sale::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total');
        $activeCustomers = Customer::where('estado', true)->count();
        $pendingOrders = ServiceOrder::whereIn('status', [ServiceOrder::STATUS_ABIERTA, ServiceOrder::STATUS_EN_PROCESO])->count();

        // Data for sparklines (last 7 entries)
        $salesTrend = Sale::orderBy('created_at', 'desc')->limit(7)->pluck('total')->reverse()->toArray();
        $ordersTrend = ServiceOrder::orderBy('created_at', 'desc')->limit(7)->get()->countBy(fn ($o) => $o->created_at->format('Y-m-d'))->values()->toArray();

        return [
            Stat::make('Ventas Hoy', '$' . number_format($todaySales, 2))
                ->description('Monto recaudado hoy')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($salesTrend)
                ->color('success'),

            Stat::make('Ventas Semanales', '$' . number_format($weeklySales, 2))
                ->description('Monto total esta semana')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make('Clientes Activos', $activeCustomers)
                ->description('Clientes habilitados')
                ->descriptionIcon('heroicon-m-users')
                ->chart([$activeCustomers, Customer::count()])
                ->color('primary'),

            Stat::make('Órdenes Pendientes', $pendingOrders)
                ->description('Abiertas o en proceso')
                ->descriptionIcon('heroicon-m-clock')
                ->chart($ordersTrend)
                ->color('warning'),
        ];
    }
}
