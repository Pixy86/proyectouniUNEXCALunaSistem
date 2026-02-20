<x-layouts.app :title="__('Tablero')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        {{-- Widgets de Estadísticas --}}
        <div class="w-full">
            @livewire(\App\Livewire\StatsOverview::class)
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-white dark:bg-zinc-900 rounded-xl border border-neutral-200 dark:border-neutral-700">
                @livewire(\App\Livewire\SalesChart::class)
            </div>
            <div class="p-4 bg-white dark:bg-zinc-900 rounded-xl border border-neutral-200 dark:border-neutral-700">
                @livewire(\App\Livewire\OrdersChart::class)
            </div>
        </div>
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts.app>
