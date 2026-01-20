<?php

namespace App\Livewire;

use App\Models\Sale;
use App\Models\Customer;
use App\Models\ServiceOrder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $todaySales = Sale::whereDate('created_at', now())->sum('total');
        $weeklySales = Sale::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->sum('total');
        $activeCustomers = Customer::where('estado', true)->count();
        $pendingOrders = ServiceOrder::whereIn('status', [ServiceOrder::STATUS_ABIERTA, ServiceOrder::STATUS_EN_PROCESO])->count();

        return [
            Stat::make(__('Ventas Hoy'), '$' . number_format($todaySales, 2))
                ->description(__('Ventas del día actual'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make(__('Ventas Semanales'), '$' . number_format($weeklySales, 2))
                ->description(__('Ventas de esta semana'))
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('info'),

            Stat::make(__('Clientes Activos'), $activeCustomers)
                ->description(__('Clientes en el sistema'))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make(__('Órdenes Pendientes'), $pendingOrders)
                ->description(__('Abiertas o en proceso'))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
