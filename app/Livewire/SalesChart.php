<?php

namespace App\Livewire;

use App\Models\Sale;
use App\Models\ServiceOrder;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Rendimiento de Ventas';
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
                $startDate = $now->startOfDay()->copy();
                $labels = collect(range(0, 23))->map(fn ($h) => $h . ':00');
                
                $hourExpr = $isSqlite ? "strftime('%H', created_at)" : "HOUR(created_at)";
                
                $salesData = Sale::whereDate('created_at', $now->toDateString())
                    ->select(DB::raw("$hourExpr as hour"), DB::raw('SUM(total) as sum'))
                    ->groupBy('hour')
                    ->pluck('sum', 'hour');
                
                $data = $labels->map(fn ($label, $h) => $salesData->get(sprintf('%02d', $h), $salesData->get($h, 0)))->toArray();
                break;

            case 'week':
                $startDate = $now->subDays(7)->startOfDay()->copy();
                $labels = collect(range(0, 6))->map(fn ($i) => $startDate->copy()->addDays($i)->isoFormat('ddd'));
                
                $dateExpr = $isSqlite ? "date(created_at)" : "DATE(created_at)";
                
                $salesData = Sale::where('created_at', '>=', $startDate)
                    ->select(DB::raw("$dateExpr as date"), DB::raw('SUM(total) as sum'))
                    ->groupBy('date')
                    ->pluck('sum', 'date');
                $data = collect(range(0, 6))->map(function ($i) use ($startDate, $salesData) {
                    $date = $startDate->copy()->addDays($i)->toDateString();
                    return $salesData->get($date, 0);
                })->toArray();
                break;

            case 'year':
                $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
                
                $monthExpr = $isSqlite ? "strftime('%m', created_at)" : "MONTH(created_at)";
                
                $salesData = Sale::whereYear('created_at', $now->year)
                    ->select(DB::raw("$monthExpr as month"), DB::raw('SUM(total) as sum'))
                    ->groupBy('month')
                    ->pluck('sum', 'month');
                
                $data = collect(range(1, 12))->map(function ($m) use ($salesData, $isSqlite) {
                    $key = $isSqlite ? sprintf('%02d', $m) : $m;
                    return $salesData->get($key, 0);
                })->toArray();
                break;

            case 'month':
            default:
                $daysInMonth = $now->daysInMonth;
                $labels = collect(range(1, $daysInMonth))->map(fn ($d) => $d);
                
                $dayExpr = $isSqlite ? "strftime('%d', created_at)" : "DAY(created_at)";
                
                $salesData = Sale::whereMonth('created_at', $now->month)
                    ->whereYear('created_at', $now->year)
                    ->select(DB::raw("$dayExpr as day"), DB::raw('SUM(total) as sum'))
                    ->groupBy('day')
                    ->pluck('sum', 'day');
                
                $data = collect(range(1, $daysInMonth))->map(function ($d) use ($salesData, $isSqlite) {
                    $key = $isSqlite ? sprintf('%02d', $d) : $d;
                    return $salesData->get($key, 0);
                })->toArray();
                break;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ventas ($)',
                    'data' => $data,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => collect($labels)->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
