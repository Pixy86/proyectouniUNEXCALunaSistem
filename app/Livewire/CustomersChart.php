<?php

namespace App\Livewire;

use App\Models\Customer;
use Filament\Widgets\ChartWidget;

class CustomersChart extends ChartWidget
{
    protected ?string $heading = 'Estado de Clientes';
    protected static ?string $pollingInterval = '60s';

    protected function getData(): array
    {
        $activeCount = Customer::where('estado', true)->count();
        $inactiveCount = Customer::where('estado', false)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Clientes',
                    'data' => [$activeCount, $inactiveCount],
                    'backgroundColor' => ['#10b981', '#ef4444'],
                ],
            ],
            'labels' => ['Activos', 'Inactivos'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
