<div>
{{-- wire:poll.30s desactivado temporalmente para resolver error 504 --}}
    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Resumen del sistema en tiempo real</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Ventas Hoy --}}
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-5 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-100 text-sm font-medium">Ventas Hoy</p>
                    <p class="text-3xl font-bold">${{ number_format($this->todaySales['total'], 2) }}</p>
                    <p class="text-emerald-200 text-xs mt-1">{{ $this->todaySales['count'] }} transacciones</p>
                </div>
                <div class="bg-white/20 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Ventas Semanales --}}
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-5 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Ventas Semanales</p>
                    <p class="text-3xl font-bold">${{ number_format($this->weeklySales['total'], 2) }}</p>
                    <p class="text-blue-200 text-xs mt-1">{{ $this->weeklySales['count'] }} transacciones</p>
                </div>
                <div class="bg-white/20 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Clientes Activos --}}
        <div class="bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl p-5 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-violet-100 text-sm font-medium">Clientes Activos</p>
                    <p class="text-3xl font-bold">{{ $this->activeCustomers }}</p>
                    <p class="text-violet-200 text-xs mt-1">de {{ $this->totalCustomers }} registrados</p>
                </div>
                <div class="bg-white/20 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Órdenes Pendientes --}}
        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-5 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-amber-100 text-sm font-medium">Órdenes Pendientes</p>
                    <p class="text-3xl font-bold">{{ $this->pendingOrders }}</p>
                    <p class="text-amber-200 text-xs mt-1">{{ $this->completedOrdersToday }} completadas hoy</p>
                </div>
                <div class="bg-white/20 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Monthly and Yearly Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        {{-- Ventas Mensuales --}}
        <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-700 shadow">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ventas del Mes</h3>
                <span class="text-sm text-gray-500">{{ now()->isoFormat('MMMM YYYY') }}</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-bold text-gray-900 dark:text-white">${{ number_format($this->monthlySales['total'], 2) }}</span>
                <span class="text-gray-500 dark:text-gray-400">{{ $this->monthlySales['count'] }} ventas</span>
            </div>
        </div>

        {{-- Ventas Anuales --}}
        <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-700 shadow">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Ventas del Año</h3>
                <span class="text-sm text-gray-500">{{ now()->year }}</span>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-4xl font-bold text-gray-900 dark:text-white">${{ number_format($this->yearlySales['total'], 2) }}</span>
                <span class="text-gray-500 dark:text-gray-400">{{ $this->yearlySales['count'] }} ventas</span>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Line Chart: Daily Sales --}}
        <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-700 shadow">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ventas Últimos 7 Días</h3>
            <div class="h-64" wire:ignore>
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>

        {{-- Pie Chart: Top Services --}}
        <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-700 shadow">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Servicios Más Vendidos (Mes)</h3>
            <div class="h-64" wire:ignore>
                <canvas id="topServicesChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Payment Methods Chart --}}
    <div class="bg-white dark:bg-zinc-800 rounded-xl p-5 border border-gray-200 dark:border-zinc-700 shadow">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Ventas por Método de Pago (Mes)</h3>
        <div class="h-64" wire:ignore>
            <canvas id="paymentMethodsChart"></canvas>
        </div>
    </div>

    {{-- Chart.js Script --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('livewire:navigated', initCharts);
        document.addEventListener('DOMContentLoaded', initCharts);

        function initCharts() {
            // Destroy existing charts if they exist
            if (window.dailySalesChart) window.dailySalesChart.destroy();
            if (window.topServicesChart) window.topServicesChart.destroy();
            if (window.paymentMethodsChart) window.paymentMethodsChart.destroy();

            // Daily Sales Chart
            const dailyData = @json($this->dailySalesChart);
            const dailyCtx = document.getElementById('dailySalesChart');
            if (dailyCtx) {
                window.dailySalesChart = new Chart(dailyCtx, {
                    type: 'line',
                    data: {
                        labels: dailyData.map(d => d.label),
                        datasets: [{
                            label: 'Ventas ($)',
                            data: dailyData.map(d => d.total),
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }

            // Top Services Pie Chart
            const servicesData = @json($this->topServices);
            const servicesCtx = document.getElementById('topServicesChart');
            if (servicesCtx && servicesData.length > 0) {
                window.topServicesChart = new Chart(servicesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: servicesData.map(s => s.nombre),
                        datasets: [{
                            data: servicesData.map(s => s.total_quantity),
                            backgroundColor: [
                                'rgba(16, 185, 129, 0.8)',
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(139, 92, 246, 0.8)',
                                'rgba(251, 191, 36, 0.8)',
                                'rgba(239, 68, 68, 0.8)',
                            ],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                            }
                        }
                    }
                });
            }

            // Payment Methods Bar Chart
            const paymentData = @json($this->salesByPaymentMethod);
            const paymentCtx = document.getElementById('paymentMethodsChart');
            if (paymentCtx && paymentData.length > 0) {
                window.paymentMethodsChart = new Chart(paymentCtx, {
                    type: 'bar',
                    data: {
                        labels: paymentData.map(p => p.nombre),
                        datasets: [{
                            label: 'Total ($)',
                            data: paymentData.map(p => p.total),
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderRadius: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { beginAtZero: true }
                        }
                    }
                });
            }
        }
    </script>
    @endpush
</div>
