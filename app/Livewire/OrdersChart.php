<?php

namespace App\Livewire;

use Filament\Widgets\ChartWidget;

class OrdersChart extends ChartWidget
{
    protected ?string $heading = 'Órdenes de Servicio (Mensual)';

    protected function getData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => __('Órdenes'),
                    'data' => [15, 22, 18, 30, 25, 40, 35, 28, 45, 50, 42, 60],
                    'backgroundColor' => '#f59e0b',
                ],
            ],
            'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
