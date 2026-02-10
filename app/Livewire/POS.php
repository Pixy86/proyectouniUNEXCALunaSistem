<?php

namespace App\Livewire;

use App\Models\ServiceOrder;
use App\Models\Service;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Sale;
use App\Models\SalesItem;
use Livewire\Component;
use Filament\Notifications\Notification;
use App\Models\AuditLog;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

use Livewire\Attributes\Computed;

class POS extends Component
{
    // Colecciones de datos para el sistema POS
    public $serviceOrders;
    public $customers;
    public $paymentMethods;
    
    // Búsqueda y carrito de compras
    public $search = '';
    public $cart = [];
    
    // Properties for checkout
    public $customer_id = null;
    public $vehicle_id = null;
    public $order_id = null; // Associated order ID
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

    // Carga órdenes de servicio en proceso, clientes y métodos de pago
    public function loadData()
    {
        // Cargamos las órdenes de servicio que estén "En Proceso"
        $this->serviceOrders = ServiceOrder::with(['customer', 'vehicle', 'items.service'])
            ->where('status', ServiceOrder::STATUS_EN_PROCESO)
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
            return $this->serviceOrders;
        }

        return $this->serviceOrders->filter(function ($order) {
            return str_contains(strtolower($order->customer?->nombre ?? ''), strtolower($this->search))
                || str_contains(strtolower($order->vehicle?->placa ?? ''), strtolower($this->search))
                || str_contains(strtolower((string)$order->id), strtolower($this->search));
        });
    }

    public function updatedSearch()
    {
        // No necesitamos recargar data, el computed property se encarga
    }

    // Agrega una orden de servicio al carrito de compras
    public function addToCart($orderId)
    {
        $order = ServiceOrder::with(['items.service', 'customer', 'vehicle'])->find($orderId);
        
        if (!$order) {
            return;
        }

        // Al seleccionar una orden, sugerimos automáticamente el cliente y vehículo
        $this->customer_id = $order->customer_id;
        $this->updatedCustomerId($this->customer_id);
        $this->vehicle_id = $order->vehicle_id;
        $this->order_id = $order->id;

        foreach ($order->items as $item) {
            $serviceId = $item->service_id;
            // Usamos una llave única que combine servicio y orden para permitir múltiples órdenes si fuera necesario
            $cartKey = "{$order->id}-{$serviceId}";
            
            $this->cart[$cartKey] = [
                'id' => $serviceId,
                'name' => "({$order->id}) " . $item->service->nombre,
                'price' => (float) $item->price,
                'quantity' => $item->quantity,
                'order_id' => $order->id,
            ];
        }
        
        Notification::make()->title('Orden agregada al carrito')->success()->send();
    }

    // Elimina un item del carrito
    public function removeFromCart($cartKey)
    {
        unset($this->cart[$cartKey]);
        if (empty($this->cart)) {
            $this->reset(['customer_id', 'vehicle_id', 'order_id', 'customerVehicles', 'paid_amount', 'discount_percentage']);
        }
    }

    // Actualiza la cantidad de un item en el carrito
    public function updateQuantity($cartKey, $quantity)
    {
        if ($quantity > 0) {
            $this->cart[$cartKey]['quantity'] = $quantity;
        } else {
            $this->removeFromCart($cartKey);
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
            $transactionResult = \Illuminate\Support\Facades\DB::transaction(function () {
                // Verificación de stock antes de procesar
                foreach ($this->cart as $item) {
                    $service = Service::with('inventory')->find($item['id']);
                    if (!$service || ($service->inventory && $service->inventory->stockActual < $item['quantity'])) {
                        throw new \Exception("El servicio '{$item['name']}' no tiene suficiente inventario disponible.");
                    }
                }

                $sale = Sale::create([
                    'customer_id' => $this->customer_id,
                    'user_id' => Auth::id(), // Registra el usuario que realiza la venta
                    'vehicle_id' => $this->vehicle_id,
                    'service_order_id' => $this->order_id,
                    'payment_method_id' => $this->payment_method_id,
                    'total' => $this->total,
                    'paid_amount' => $this->paid_amount ?: $this->total,
                    'discount' => $this->discountVal,
                ]);

                $orderIds = [];

                foreach ($this->cart as $item) {
                    SalesItem::create([
                        'sale_id' => $sale->id,
                        'service_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);

                    if (isset($item['order_id'])) {
                        $orderIds[] = $item['order_id'];
                    }

                    // Decrementar inventario
                    $service = Service::find($item['id']);
                    if ($service) {
                        if ($service->inventory) {
                            $service->inventory->decrement('stockActual', $item['quantity']);
                        }
                        if ($service->cantidad > 0) {
                            $service->decrement('cantidad', $item['quantity']);
                        }
                    }
                }

                // Finalizar las órdenes de servicio involucradas
                $uniqueOrderIds = array_unique($orderIds);
                foreach ($uniqueOrderIds as $oid) {
                    $order = ServiceOrder::find($oid);
                    if ($order) {
                        $order->update([
                            'status' => ServiceOrder::STATUS_TERMINADO,
                            'completed_at' => now(),
                        ]);
                    }
                }

                return $sale;
            });
            
            $saleId = $transactionResult->id;
            $saleTotal = $transactionResult->total;

            $this->reset(['cart', 'customer_id', 'payment_method_id', 'search', 'paid_amount', 'discount_percentage', 'vehicle_id', 'order_id', 'customerVehicles']);
            $this->loadData(); // Recarga para actualizar lista basada en nuevo stock y estado de órdenes
            
            // Log Auditoría
            $customerId = $transactionResult->customer_id;
            $customerName = \App\Models\Customer::find($customerId)?->nombre ?? 'Cliente';
            
            AuditLog::registrar(
                accion: AuditLog::ACCION_CREATE,
                descripcion: "Venta de servicio realizada por el usuario [ID: " . Auth::id() . "] a cliente: {$customerName} (ID: {$customerId}). Orden: " . ($transactionResult->service_order_id ?? 'N/A') . ". Total: {$saleTotal}",
                modelo: 'Sale',
                modeloId: $saleId
            );

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
