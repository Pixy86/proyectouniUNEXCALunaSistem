<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-8rem)]">
    <!-- Left Column: Products/Services -->
    <div class="lg:col-span-2 flex flex-col gap-4 h-full overflow-hidden">
        <!-- Search Bar -->
        <div class="bg-white dark:bg-zinc-800 p-4 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar por Orden #, Cliente o Placa..." />
        </div>

        <div class="flex-1 overflow-y-auto pr-2">
            @if($this->filteredItems->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                @foreach($this->filteredItems as $order)
                        <div class="bg-white dark:bg-zinc-800 p-4 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 transition-all group relative">
                            <div wire:click="addToCart({{ $order->id }})" 
                                 class="cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-400 transition-all">
                                <div class="flex flex-col h-full justify-between gap-2">
                                    <div>
                                        <div class="flex justify-between items-start mb-1">
                                            <span class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider">Orden #{{ $order->id }}</span>
                                            @if($order->status === 'Abierta')
                                                <flux:badge size="sm" color="warning" variant="solid">Abierta</flux:badge>
                                            @else
                                                <flux:badge size="sm" color="info" variant="solid">Proceso</flux:badge>
                                            @endif
                                        </div>
                                        <h3 class="font-semibold text-gray-800 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-1">
                                            {{ $order->customer?->nombre }} {{ $order->customer?->apellido }}
                                        </h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $order->vehicle?->placa ?? 'Sin Placa' }} - {{ $order->vehicle?->modelo ?? 'S/V' }}
                                        </p>
                                        <div class="mt-2 space-y-1">
                                            @foreach($order->items->take(2) as $item)
                                                <div class="text-[10px] text-gray-400 flex justify-between">
                                                    <span class="truncate pr-2">{{ $item->service->nombre }}</span>
                                                    <span class="shrink-0">x{{ $item->quantity }}</span>
                                                </div>
                                            @endforeach
                                            @if($order->items->count() > 2)
                                                <div class="text-[10px] text-gray-400 italic tracking-tight">+{{ $order->items->count() - 2 }} más...</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mt-2 text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                        ${{ number_format($order->total_amount, 2) }}
                                    </div>
                                </div>
                            </div>
                            
                            @if($order->status === 'Abierta' && in_array(auth()->user()?->role, ['Administrador', 'Encargado']))
                                <div class="mt-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                                    <flux:button wire:click.stop="moveToProcess({{ $order->id }})" variant="outline" size="sm" class="w-full">
                                        <flux:icon.arrow-right class="w-3 h-3" />
                                        Mover a Proceso
                                    </flux:button>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-64 text-gray-500">
                    <flux:icon.magnifying-glass class="w-12 h-12 mb-2 opacity-50" />
                    <p>No se encontraron órdenes abiertas o en proceso</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Right Column: Cart & Checkout -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 h-full overflow-y-auto custom-scrollbar">
        <div class="sticky top-0 z-10 p-4 border-b border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
            <h2 class="font-bold text-lg text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <flux:icon.shopping-cart class="w-5 h-5" />
                Ventas
            </h2>
        </div>

        <!-- Cart Items -->
        <div class="p-4 space-y-4">
            @forelse($cart as $key => $item)
                <div class="flex justify-between items-center p-3 bg-zinc-50 dark:bg-zinc-900/50 rounded-lg border border-zinc-100 dark:border-zinc-700">
                    <div class="flex-1 min-w-0 pr-3">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" title="{{ $item['name'] }}">
                            {{ $item['name'] }}
                        </div>
                        <div class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">
                            ${{ number_format($item['price'], 2) }}
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="flex items-center gap-1 bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700">
                            <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})" 
                                    class="p-1 hover:text-indigo-600 dark:hover:text-indigo-400 disabled:opacity-50 transition-colors">
                                <flux:icon.minus class="w-3 h-3" />
                            </button>
                            <span class="w-4 text-center text-xs font-medium">{{ $item['quantity'] }}</span>
                            <button wire:click="updateQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})"
                                    class="p-1 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <flux:icon.plus class="w-3 h-3" />
                            </button>
                        </div>
                        <button wire:click="removeFromCart('{{ $key }}')" 
                                class="text-gray-400 hover:text-red-500 transition-colors p-1">
                            <flux:icon.trash class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-gray-500 dark:text-gray-400 text-sm">
                    <flux:icon.shopping-bag class="w-8 h-8 mx-auto mb-2 opacity-50" />
                    <p>Carrito vacío</p>
                </div>
            @endforelse
        </div>

        <!-- Checkout Actions -->
        <div class="p-4 bg-zinc-50 dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700 space-y-4">
            
            <div class="space-y-3">
                @if($order_id)
                    @php
                        $activeOrder = \App\Models\ServiceOrder::find($order_id);
                    @endphp
                    @if($activeOrder)
                        <div class="p-3 bg-indigo-50 dark:bg-indigo-900/30 border border-indigo-100 dark:border-indigo-800 rounded-lg space-y-2">
                            <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-300">
                                <flux:icon.user class="w-4 h-4" />
                                <span class="text-sm font-semibold">Cliente:</span>
                                <span class="text-sm">{{ $activeOrder->customer?->nombre }} {{ $activeOrder->customer?->apellido }}</span>
                            </div>
                            @if($activeOrder->vehicle)
                                <div class="flex items-center gap-2 text-indigo-700 dark:text-indigo-300 border-t border-indigo-100 dark:border-indigo-800 pt-2">
                                    <flux:icon.truck class="w-4 h-4" />
                                    <span class="text-sm font-semibold">Vehículo:</span>
                                    <span class="text-sm">{{ $activeOrder->vehicle->placa }} ({{ $activeOrder->vehicle->modelo }})</span>
                                </div>
                            @endif
                        </div>
                    @endif
                @else
                    <div class="p-3 bg-amber-50 dark:bg-amber-900/30 border border-amber-100 dark:border-amber-800 rounded-lg">
                        <p class="text-sm text-amber-700 dark:text-amber-300 text-center">
                            <flux:icon.information-circle class="w-4 h-4 inline mr-1" />
                            Selecciona una orden de la izquierda
                        </p>
                    </div>
                @endif
                
                <flux:select wire:model.live="payment_method_id" placeholder="Seleccionar método de pago">
                    <flux:select.option value="">Seleccionar método de pago</flux:select.option>
                    @foreach($paymentMethods as $method)
                        <flux:select.option value="{{ $method->id }}">{{ $method->nombre }}</flux:select.option>
                    @endforeach
                </flux:select>
                @error('payment_method_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                <div class="grid grid-cols-2 gap-3">
                    <flux:input wire:model.live="discount_percentage" type="number" min="0" max="100" label="Descuento (%)" placeholder="0%" onfocus="if(this.value=='0'){this.value=''}" />
                    <flux:input wire:model.live="paid_amount" type="number" min="0" step="0.01" label="Monto Pagado" placeholder="0.00" onfocus="if(this.value=='0'){this.value=''}" />
                </div>

            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700 space-y-2">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                    <span class="font-medium text-gray-900 dark:text-gray-200">${{ number_format($this->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500 dark:text-gray-400">IVA (16%)</span>
                    <span class="font-medium text-gray-900 dark:text-gray-200">${{ number_format($this->tax, 2) }}</span>
                </div>
                @if($this->igtf > 0)
                    <div class="flex justify-between items-center text-sm text-orange-600 dark:text-orange-400">
                        <span>IGTF (3%)</span>
                        <span>${{ number_format($this->igtf, 2) }}</span>
                    </div>
                @endif
                @if($this->discountVal > 0)
                    <div class="flex justify-between items-center text-sm text-green-600 dark:text-green-400">
                        <span>Descuento ({{ $this->discount_percentage }}%)</span>
                        <span>-${{ number_format($this->discountVal, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between items-center pt-2 border-t border-zinc-200 dark:border-zinc-700">
                    <span class="text-lg text-gray-500 dark:text-gray-400">Total a Pagar</span>
                    <span class="text-2xl font-bold text-gray-900 dark:text-white">${{ number_format($this->total, 2) }}</span>
                </div>

                @if($this->change > 0)
                    <div class="flex justify-between items-center pt-2 border-t border-zinc-200 dark:border-zinc-700">
                        <span class="text-lg text-blue-600 dark:text-blue-400 font-medium">Vuelto / Cambio</span>
                        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">${{ number_format($this->change, 2) }}</span>
                    </div>
                @endif
                
                <flux:button wire:click="checkout" variant="primary" class="w-full h-12 text-lg shadow-lg">
                    Confirmar Venta
                </flux:button>
            </div>
        </div>
    </div>
</div>
