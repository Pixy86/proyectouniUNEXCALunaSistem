<?php

namespace App\Livewire;

use App\Models\ServiceOrder;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class OrdersChart extends ChartWidget
{
    protected ?string $heading = 'Flujo de Órdenes de Servicio';
    protected ?string $pollingInterval = '30s';
    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hoy',
            'week' => 'Última Semana',
            'month' => 'Último Mes',
            'year' => 'Este Año',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;
        $now = now();

        $driver = DB::getDriverName();
        $isSqlite = $driver === 'sqlite';

        switch ($activeFilter) {
            case 'today':
                $labels = collect(range(0, 23))->map(fn ($h) => $h . ':00');
                
                $hourExpr = $isSqlite ? "strftime('%H', created_at)" : "HOUR(created_at)";
                
                $orderData = ServiceOrder::whereDate('created_at', $now->toDateString())
                    ->select(DB::raw("$hourExpr as hour"), DB::raw('COUNT(*) as count'))
                    ->groupBy('hour')
                    ->pluck('count', 'hour');
                
                $data = $labels->map(fn ($label, $h) => $orderData->get(sprintf('%02d', $h), $orderData->get($h, 0)))->toArray();
                break;

            case 'week':
                $startDate = $now->subDays(7)->startOfDay()->copy();
                $labels = collect(range(0, 6))->map(fn ($i) => $startDate->copy()->addDays($i)->isoFormat('ddd'));
                
                $dateExpr = $isSqlite ? "date(created_at)" : "DATE(created_at)";
                
                $orderData = ServiceOrder::where('created_at', '>=', $startDate)
                    ->select(DB::raw("$dateExpr as date"), DB::raw('COUNT(*) as count'))
                    ->groupBy('date')
                    ->pluck('count', 'date');
                $data = collect(range(0, 6))->map(function ($i) use ($startDate, $orderData) {
                    $date = $startDate->copy()->addDays($i)->toDateString();
                    return $orderData->get($date, 0);
                })->toArray();
                break;

            case 'year':
                $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                
                $monthExpr = $isSqlite ? "strftime('%m', created_at)" : "MONTH(created_at)";
                
                $orderData = ServiceOrder::whereYear('created_at', $now->year)
                    ->select(DB::raw("$monthExpr as month"), DB::raw('COUNT(*) as count'))
                    ->groupBy('month')
                    ->pluck('count', 'month');
                
                $data = collect(range(1, 12))->map(function ($m) use ($orderData, $isSqlite) {
                    $key = $isSqlite ? sprintf('%02d', $m) : $m;
                    return $orderData->get($key, 0);
                })->toArray();
                break;

            case 'month':
            default:
                $daysInMonth = $now->daysInMonth;
                $labels = collect(range(1, $daysInMonth))->map(fn ($d) => $d);
                
                $dayExpr = $isSqlite ? "strftime('%d', created_at)" : "DAY(created_at)";
                
                $orderData = ServiceOrder::whereMonth('created_at', $now->month)
                    ->whereYear('created_at', $now->year)
                    ->select(DB::raw("$dayExpr as day"), DB::raw('COUNT(*) as count'))
                    ->groupBy('day')
                    ->pluck('count', 'day');
                
                $data = collect(range(1, $daysInMonth))->map(function ($d) use ($orderData, $isSqlite) {
                    $key = $isSqlite ? sprintf('%02d', $d) : $d;
                    return $orderData->get($key, 0);
                })->toArray();
                break;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Órdenes Creadas',
                    'data' => $data,
                    'backgroundColor' => '#f59e0b',
                    'fill' => true,
                ],
            ],
            'labels' => collect($labels)->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
