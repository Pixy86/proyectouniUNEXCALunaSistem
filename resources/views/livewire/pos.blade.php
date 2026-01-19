<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-[calc(100vh-8rem)]">
    <!-- Left Column: Products/Services -->
    <div class="lg:col-span-2 flex flex-col gap-4 h-full overflow-hidden">
        <!-- Search Bar -->
        <div class="bg-white dark:bg-zinc-800 p-4 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="Buscar servicios..." />
        </div>

        <!-- Services Grid -->
        <div class="flex-1 overflow-y-auto pr-2">
            @if($this->filteredItems->count() > 0)
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @foreach($this->filteredItems as $service)
                        <div wire:click="addToCart({{ $service->id }})" 
                             class="bg-white dark:bg-zinc-800 p-4 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-400 transition-all group">
                            <div class="flex flex-col h-full justify-between gap-2">
                                <div>
                                    <h3 class="font-semibold text-gray-800 dark:text-gray-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                        {{ $service->nombre }}
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 line-clamp-2">{{ $service->descripcion }}</p>
                                </div>
                                <div class="mt-2 text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                    ${{ number_format($service->precio, 2) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-64 text-gray-500">
                    <flux:icon.magnifying-glass class="w-12 h-12 mb-2 opacity-50" />
                    <p>No se encontraron servicios</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Right Column: Cart & Checkout -->
    <div class="bg-white dark:bg-zinc-800 rounded-xl shadow-sm border border-zinc-200 dark:border-zinc-700 flex flex-col h-full overflow-hidden">
        <div class="p-4 border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50">
            <h2 class="font-bold text-lg text-gray-800 dark:text-gray-100 flex items-center gap-2">
                <flux:icon.shopping-cart class="w-5 h-5" />
                Carrito de Ventas
            </h2>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            @forelse($cart as $item)
                <div class="flex justify-between items-center bg-zinc-50 dark:bg-zinc-900 rounded-lg p-3 group hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-800 dark:text-gray-200">{{ $item['name'] }}</h4>
                        <p class="text-sm text-gray-500">${{ number_format($item['price'], 2) }} x {{ $item['quantity'] }}</p>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-1 bg-white dark:bg-zinc-700 rounded-md border border-zinc-200 dark:border-zinc-600">
                            <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})" 
                                    class="p-1 hover:bg-gray-100 dark:hover:bg-zinc-600 rounded-l-md text-gray-600 dark:text-gray-300">
                                <flux:icon.minus class="w-3 h-3" />
                            </button>
                            <span class="w-8 text-center text-sm font-medium">{{ $item['quantity'] }}</span>
                            <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})" 
                                    class="p-1 hover:bg-gray-100 dark:hover:bg-zinc-600 rounded-r-md text-gray-600 dark:text-gray-300">
                                <flux:icon.plus class="w-3 h-3" />
                            </button>
                        </div>
                        <button wire:click="removeFromCart({{ $item['id'] }})" class="text-red-400 hover:text-red-600 p-1">
                            <flux:icon.trash class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center h-full text-gray-400">
                    <flux:icon.shopping-bag class="w-12 h-12 mb-2 opacity-20" />
                    <p>El carrito está vacío</p>
                </div>
            @endforelse
        </div>

        <!-- Checkout Actions -->
        <div class="p-4 bg-zinc-50 dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700 space-y-4">
            
            <div class="space-y-3">
                <flux:select wire:model.live="customer_id" placeholder="Seleccionar Cliente" searchable>
                    @foreach($customers as $customer)
                        <flux:select.option value="{{ $customer->id }}">{{ $customer->nombre }} {{ $customer->apellido }} ({{ $customer->cedula_rif }})</flux:select.option>
                    @endforeach
                </flux:select>
                @error('customer_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                @if(!empty($customerVehicles) && count($customerVehicles) > 0)
                    <flux:select wire:model="vehicle_id" placeholder="Seleccionar Vehículo (Opcional)">
                        <flux:select.option value="">Ninguno / No Aplica</flux:select.option>
                        @foreach($customerVehicles as $vehicle)
                            <flux:select.option value="{{ $vehicle->id }}">{{ $vehicle->marca }} {{ $vehicle->modelo }} ({{ $vehicle->placa }})</flux:select.option>
                        @endforeach
                    </flux:select>
                @endif
                
                <flux:select wire:model="payment_method_id" placeholder="Método de Pago">
                    @foreach($paymentMethods as $method)
                        <flux:select.option value="{{ $method->id }}">{{ $method->nombre }}</flux:select.option>
                    @endforeach
                </flux:select>
                @error('payment_method_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                <div class="grid grid-cols-2 gap-3">
                    <flux:input wire:model.live="discount_percentage" type="number" min="0" max="100" label="Descuento (%)" placeholder="0%" />
                    <flux:input wire:model.live="paid_amount" type="number" step="0.01" label="Monto Pagado" placeholder="0.00" />
                </div>

            <div class="pt-4 border-t border-zinc-200 dark:border-zinc-700 space-y-2">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Subtotal</span>
                    <span class="font-medium text-gray-900 dark:text-gray-200">${{ number_format($this->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-gray-500 dark:text-gray-400">IVA (15%)</span>
                    <span class="font-medium text-gray-900 dark:text-gray-200">${{ number_format($this->tax, 2) }}</span>
                </div>
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
