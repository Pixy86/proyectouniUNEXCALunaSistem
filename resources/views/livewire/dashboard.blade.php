<div>
    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Resumen del sistema en tiempo real con Filament</p>
        </div>
        <div class="text-emerald-500 font-medium text-sm animate-pulse">
            ● Transmisión en vivo
        </div>
    </div>

    {{-- Stats Cards Row --}}
    <div class="mb-6">
        @livewire(\App\Livewire\StatsOverview::class)
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Line Chart: Sales Trend --}}
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-200 dark:border-zinc-700 shadow-sm overflow-hidden p-4">
            <div class="h-64 lg:h-80">
                @livewire(\App\Livewire\SalesChart::class)
            </div>
        </div>

        {{-- Bar Chart: Orders Volume --}}
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-200 dark:border-zinc-700 shadow-sm overflow-hidden p-4">
            <div class="h-64 lg:h-80">
                @livewire(\App\Livewire\OrdersChart::class)
            </div>
        </div>

        {{-- Doughnut Chart: Customers Status --}}
        <div class="bg-white dark:bg-zinc-900 rounded-xl border border-gray-200 dark:border-zinc-700 shadow-sm overflow-hidden p-4">
            <div class="h-64 lg:h-80">
                @livewire(\App\Livewire\CustomersChart::class)
            </div>
        </div>
    </div>

    {{-- Maintenance and Controls --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        {{-- Ventas Mensuales (Static Card wrapper) --}}
        <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-700 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ventas del Mes</h3>
                <span class="text-xs font-medium px-2 py-0.5 rounded bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">{{ now()->isoFormat('MMMM YYYY') }}</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-bold text-gray-900 dark:text-white">${{ number_format($this->monthlySales['total'], 2) }}</span>
                <span class="text-gray-500 dark:text-gray-400 text-sm">{{ $this->monthlySales['count'] }} ventas</span>
            </div>
        </div>

        {{-- Ventas Anuales (Static Card wrapper) --}}
        <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-700 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ventas del Año</h3>
                <span class="text-xs font-medium px-2 py-0.5 rounded bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">{{ now()->year }}</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-bold text-gray-900 dark:text-white">${{ number_format($this->yearlySales['total'], 2) }}</span>
                <span class="text-gray-500 dark:text-gray-400 text-sm">{{ $this->yearlySales['count'] }} ventas</span>
            </div>
        </div>
    </div>

    {{-- Componentes de Filament manejados automáticamente --}}
</div>
