<?php

namespace App\Livewire;

use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Órdenes de Servicio (Mensual)';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => __('Órdenes'),
                    'data' => [15, 22, 18, 30, 25, 40, 35, 28, 45, 50, 42, 60],
                    'backgroundColor' => '#3b82f6',
                ],
                [
                    'label' => __('Ventas ($)'),
                    'data' => [1200, 1500, 1100, 2400, 3200, 4500, 3800, 5200, 6100, 4800, 7200, 8500],
                    'borderColor' => '#10b981',
                ],
            ],
            'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
