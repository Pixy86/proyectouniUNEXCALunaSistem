<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Sale;
use App\Models\Service;
use App\Models\ServiceOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Computed;

class Dashboard extends Component
{
    // Polling interval for real-time updates (in milliseconds)
    public int $pollingInterval = 30000; // 30 seconds

    // Date range filter
    public string $period = 'today';

    // Cuenta clientes activos en el sistema
    #[Computed]
    public function activeCustomers(): int
    {
        return Customer::where('estado', true)->count();
    }

    // Cuenta el total de clientes registrados
    #[Computed]
    public function totalCustomers(): int
    {
        return Customer::count();
    }

    // Obtiene ventas del día actual: cantidad y monto total (optimizado para rendimiento)
    #[Computed]
    public function todaySales(): array
    {
        $result = Sale::whereDate('created_at', Carbon::today())
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total')
            ->first();
        
        return [
            'count' => $result->count ?? 0,
            'total' => $result->total ?? 0,
        ];
    }

    // Obtiene ventas de la semana actual: cantidad y monto total (optimizado para rendimiento)
    #[Computed]
    public function weeklySales(): array
    {
        $result = Sale::whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek(),
        ])
        ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total')
        ->first();
        
        return [
            'count' => $result->count ?? 0,
            'total' => $result->total ?? 0,
        ];
    }

    // Obtiene ventas del mes actual: cantidad y monto total (optimizado para rendimiento)
    #[Computed]
    public function monthlySales(): array
    {
        $result = Sale::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total')
            ->first();
        
        return [
            'count' => $result->count ?? 0,
            'total' => $result->total ?? 0,
        ];
    }

    // Obtiene ventas del año actual: cantidad y monto total (optimizado para rendimiento)
    #[Computed]
    public function yearlySales(): array
    {
        $result = Sale::whereYear('created_at', Carbon::now()->year)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total')
            ->first();
        
        return [
            'count' => $result->count ?? 0,
            'total' => $result->total ?? 0,
        ];
    }

    // Cuenta órdenes de servicio pendientes (Abierta o En Proceso)
    #[Computed]
    public function pendingOrders(): int
    {
        return ServiceOrder::whereIn('status', ['Abierta', 'En Proceso'])->count();
    }

    // Cuenta órdenes de servicio completadas hoy
    #[Computed]
    public function completedOrdersToday(): int
    {
        return ServiceOrder::where('status', 'Terminado')
            ->whereDate('completed_at', Carbon::today())
            ->count();
    }

    // Genera datos para gráfico de ventas de los últimos 7 días
    #[Computed]
    public function dailySalesChart(): array
    {
        // Last 7 days sales
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $total = Sale::whereDate('created_at', $date)->sum('total');
            $data[] = [
                'date' => $date->format('d/m'),
                'label' => $date->isoFormat('ddd'),
                'total' => round($total, 2),
            ];
        }
        return $data;
    }

    // Obtiene los 5 servicios más vendidos del mes actual
    #[Computed]
    public function topServices(): array
    {
        return DB::table('sales_items')
            ->join('services', 'sales_items.service_id', '=', 'services.id')
            ->select('services.nombre', DB::raw('SUM(sales_items.quantity) as total_quantity'))
            ->whereMonth('sales_items.created_at', Carbon::now()->month)
            ->groupBy('services.id', 'services.nombre')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get()
            ->toArray();
    }

    // Obtiene ventas agrupadas por método de pago del mes actual
    #[Computed]
    public function salesByPaymentMethod(): array
    {
        return DB::table('sales')
            ->join('payment_methods', 'sales.payment_method_id', '=', 'payment_methods.id')
            ->select('payment_methods.nombre', DB::raw('COUNT(*) as count'), DB::raw('SUM(sales.total) as total'))
            ->whereMonth('sales.created_at', Carbon::now()->month)
            ->groupBy('payment_methods.id', 'payment_methods.nombre')
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.dashboard');
    }
}
