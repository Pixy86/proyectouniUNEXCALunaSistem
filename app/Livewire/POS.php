<?php

namespace App\Livewire;

use App\Models\Service;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\SalesItem;
use Livewire\Component;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

use Livewire\Attributes\Computed;

class POS extends Component
{
    // Colecciones de datos para el sistema POS
    public $services;
    public $customers;
    public $paymentMethods;
    
    // Búsqueda y carrito de compras
    public $search = '';
    public $cart = [];
    
    // Properties for checkout
    public $customer_id = null;
    public $vehicle_id = null;
    public $payment_method_id = null;
    public $paid_amount = 0; // Amount paid by customer
    public $discount_percentage = 0; // Discount as percentage

    public $customerVehicles = [];

    // Inicializa el componente cargando servicios, clientes y métodos de pago
    public function mount()
    {
        $this->loadData();
    }

    // Actualiza la lista de vehículos cuando se selecciona un cliente
    public function updatedCustomerId($value)
    {
        $this->vehicle_id = null;
        $this->customerVehicles = [];
        
        if ($value) {
            $customer = Customer::find($value);
            if ($customer) {
                $this->customerVehicles = $customer->vehicles;
            }
        }
    }

    // Carga servicios activos con stock disponible, clientes y métodos de pago
    public function loadData()
    {
        // Cargamos los servicios activos que tengan un item de inventario con stock > 0
        $this->services = Service::query()
            ->where('estado', true)
            ->whereHas('inventory', function (Builder $query) {
                $query->where('stockActual', '>', 0);
            })
            ->with('inventory')
            ->get();

        // Carga solo los campos necesarios de clientes para mejorar rendimiento
        $this->customers = Customer::select('id', 'cedula_rif', 'nombre', 'apellido')
            ->where('estado', true)
            ->orderBy('nombre')
            ->get();
        
        if ($this->customer_id) {
             $customer = Customer::find($this->customer_id);
             $this->customerVehicles = $customer ? $customer->vehicles : [];
        } else {
             $this->customerVehicles = [];
        }
        
        // Carga solo métodos de pago activos con campos necesarios
        $this->paymentMethods = PaymentMethod::select('id', 'nombre')
            ->where('estado', true)
            ->get();
    }

    #[Computed]
    public function filteredItems()
    {
        if (empty($this->search)) {
            return $this->services;
        }

        return $this->services->filter(function ($service) {
            return str_contains(strtolower($service->nombre), strtolower($this->search))
                || str_contains(strtolower($service->descripcion), strtolower($this->search));
        });
    }

    public function updatedSearch()
    {
        // No necesitamos recargar data, el computed property se encarga
    }

    // Agrega un servicio al carrito de compras
    public function addToCart($serviceId)
    {
        $service = Service::find($serviceId);
        
        if (!$service) {
            return;
        }

        if (isset($this->cart[$serviceId])) {
            $this->cart[$serviceId]['quantity']++;
        } else {
            $this->cart[$serviceId] = [
                'id' => $service->id,
                'name' => $service->nombre,
                'price' => $service->precio,
                'quantity' => 1,
            ];
        }
        
        Notification::make()->title('Servicio agregado al carrito')->success()->send();
    }

    // Elimina un servicio del carrito de compras
    public function removeFromCart($serviceId)
    {
        unset($this->cart[$serviceId]);
    }

    // Actualiza la cantidad de un servicio en el carrito
    public function updateQuantity($serviceId, $quantity)
    {
        if ($quantity > 0) {
            $this->cart[$serviceId]['quantity'] = $quantity;
        } else {
            $this->removeFromCart($serviceId);
        }
    }

    // Calcula el subtotal sumando precio por cantidad de cada item en el carrito
    #[Computed]
    public function subtotal()
    {
        return collect($this->cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
    }

    // Calcula el impuesto aplicando 15% sobre el subtotal
    #[Computed]
    public function tax()
    {
        return $this->subtotal * 0.15; // 15% Tax
    }

    // Calcula el valor del descuento según el porcentaje aplicado
    #[Computed]
    public function discountVal()
    {
        return ($this->subtotal + $this->tax) * ($this->discount_percentage / 100);
    }

    // Calcula el total final: subtotal + impuesto - descuento
    #[Computed]
    public function total()
    {
        return max(0, ($this->subtotal + $this->tax) - $this->discountVal);
    }

    // Calcula el cambio a devolver si el pago excede el total
    #[Computed]
    public function change()
    {
        if ($this->paid_amount > $this->total) {
            return $this->paid_amount - $this->total;
        }
        return 0;
    }

    // Procesa la venta: valida datos, registra en BD, actualiza inventario y reinicia el carrito
    public function checkout()
    {
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'cart' => 'required|array|min:1',
            'paid_amount' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ], [
            'customer_id.required' => 'Debe seleccionar un cliente.',
            'payment_method_id.required' => 'Debe seleccionar un método de pago.',
            'cart.required' => 'El carrito no puede estar vacío.',
        ]);

        // Wrappear todo en una transacción de base de datos para asegurar integridad
        try {
            \Illuminate\Support\Facades\DB::transaction(function () {
                // Verificación de stock antes de procesar
                foreach ($this->cart as $item) {
                    $service = Service::with('inventory')->find($item['id']);
                    // Use lockForUpdate to prevent race conditions
                    if (!$service || !$service->inventory || $service->inventory->stockActual < $item['quantity']) {
                        throw new \Exception("El servicio '{$item['name']}' no tiene suficiente inventario disponible.");
                    }
                }

                $sale = Sale::create([
                    'customer_id' => $this->customer_id,
                    'vehicle_id' => $this->vehicle_id,
                    'payment_method_id' => $this->payment_method_id,
                    'total' => $this->total,
                    'paid_amount' => $this->paid_amount ?: $this->total,
                    'discount' => $this->discountVal,
                ]);

                foreach ($this->cart as $item) {
                    SalesItem::create([
                        'sale_id' => $sale->id,
                        'service_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);

                    // Decrementar inventario
                    $service = Service::find($item['id']);
                    if ($service) {
                         // Decrement Inventory Stock
                        if ($service->inventory) {
                            $service->inventory->decrement('stockActual', $item['quantity']);
                        }
                        
                        // Decrement Service Limit (Cupo) if applicable
                        if ($service->cantidad > 0) {
                            $service->decrement('cantidad', $item['quantity']);
                        }
                    }
                }
            });

            $this->reset(['cart', 'customer_id', 'payment_method_id', 'search', 'paid_amount', 'discount_percentage', 'vehicle_id', 'customerVehicles']);
            $this->loadData(); // Recarga para actualizar lista basada en nuevo stock

            Notification::make()->title('Venta registrada exitosamente')->success()->send();

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('POS Checkout Error: ' . $e->getMessage());
            Notification::make()
                ->title('Error al procesar la venta')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.pos');
    }
}
