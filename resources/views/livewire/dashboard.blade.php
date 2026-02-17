<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
    {{-- Header --}}
    <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Panel de Control</h1>
            <p class="text-base text-gray-500 dark:text-gray-400">Resumen operativo y métricas en tiempo real</p>
        </div>
        <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-100 dark:border-emerald-800 rounded-full w-fit">
            <span class="flex h-2 w-2">
              <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
            </span>
            <span class="text-emerald-600 dark:text-emerald-400 font-semibold text-xs uppercase tracking-wider">
                Transmisión en vivo
            </span>
        </div>
    </div>

    {{-- Stats Cards Row --}}
    <div class="mb-8 overflow-x-auto pb-2">
        @livewire(\App\Livewire\StatsOverview::class)
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        {{-- Line Chart: Sales Trend --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden p-4">
            <div class="h-64 lg:h-80">
                @livewire(\App\Livewire\SalesChart::class)
            </div>
        </div>

        {{-- Bar Chart: Orders Volume --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden p-4">
            <div class="h-64 lg:h-80">
                @livewire(\App\Livewire\OrdersChart::class)
            </div>
        </div>

        {{-- Doughnut Chart: Customers Status --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden p-4">
            <div class="h-64 lg:h-80">
                @livewire(\App\Livewire\CustomersChart::class)
            </div>
        </div>
    </div>

    {{-- Maintenance and Controls --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-12">
        {{-- Ventas Mensuales (Static Card wrapper) --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-gray-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-50 dark:bg-emerald-900/30 rounded-lg">
                        <x-heroicon-o-calendar-days class="w-6 h-6 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Ventas del Mes</h3>
                </div>
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 uppercase">{{ now()->isoFormat('MMMM YYYY') }}</span>
            </div>
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">${{ number_format($this->monthlySales['total'], 2) }}</p>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 flex items-center gap-1">
                        <x-heroicon-m-arrow-trending-up class="w-4 h-4 text-emerald-500" />
                        {{ $this->monthlySales['count'] }} transacciones procesadas
                    </p>
                </div>
            </div>
        </div>

        {{-- Ventas Anuales (Static Card wrapper) --}}
        <div class="bg-white dark:bg-zinc-900 rounded-2xl p-6 border border-gray-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition-shadow duration-300">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-blue-50 dark:bg-blue-900/30 rounded-lg">
                        <x-heroicon-o-presentation-chart-line class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Ventas del Año</h3>
                </div>
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 uppercase">{{ now()->year }}</span>
            </div>
            <div class="flex items-end justify-between">
                <div>
                    <p class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">${{ number_format($this->yearlySales['total'], 2) }}</p>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 flex items-center gap-1">
                        <x-heroicon-m-check-badge class="w-4 h-4 text-blue-500" />
                        {{ $this->yearlySales['count'] }} transacciones anuales
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
