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

class Venta extends Component
{
    // Colecciones de datos para el sistema Venta
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
        // Cargamos las órdenes de servicio que estén "Abierta" o "En Proceso"
        $this->serviceOrders = ServiceOrder::with(['customer', 'vehicle', 'items.service'])
            ->whereIn('status', [ServiceOrder::STATUS_ABIERTA, ServiceOrder::STATUS_EN_PROCESO])
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

    // Calcula el impuesto aplicando 16% sobre el subtotal
    #[Computed]
    public function tax()
    {
        return $this->subtotal * 0.16; // 16% IVA
    }

    // Calcula el valor del descuento según el porcentaje aplicado
    #[Computed]
    public function discountVal()
    {
        return ($this->subtotal + $this->tax + $this->igtf) * ((float) $this->discount_percentage / 100);
    }

    // Calcula el total final: subtotal + impuesto + IGTF - descuento
    #[Computed]
    public function total()
    {
        return max(0, ($this->subtotal + $this->tax + $this->igtf) - (float) $this->discountVal);
    }

    // Calcula el cambio a devolver si el pago excede el total
    #[Computed]
    public function change()
    {
        if ((float) $this->paid_amount > $this->total) {
            return (float) $this->paid_amount - $this->total;
        }
        return 0;
    }

    // Calcula el IGTF (3%) si el método de pago contiene "Divisa"
    #[Computed]
    public function igtf()
    {
        if ($this->payment_method_id && $this->paymentMethods) {
            // Buscamos en la colección ya cargada en lugar de hacer query
            $paymentMethod = $this->paymentMethods->firstWhere('id', $this->payment_method_id);
            
            if ($paymentMethod && stripos($paymentMethod->nombre, 'Divisa') !== false) {
                return ($this->subtotal + $this->tax) * 0.03; // 3% IGTF
            }
        }
        return 0;
    }

    // Cambia el estado de una orden de "Abierta" a "En Proceso"
    public function moveToProcess($orderId)
    {
        if (!in_array(auth()->user()?->role, ['Administrador', 'Encargado'])) {
            Notification::make()->title('No tiene permisos para cambiar el estado de la orden')->danger()->send();
            return;
        }

        $order = ServiceOrder::find($orderId);
        if ($order && $order->status === ServiceOrder::STATUS_ABIERTA) {
            $order->update(['status' => ServiceOrder::STATUS_EN_PROCESO]);
            $this->loadData(); // Recargar datos para reflejar el cambio
            
            Notification::make()
                ->title('Orden movida a En Proceso')
                ->success()
                ->send();
        }
    }

    // Procesa la venta: valida datos, registra en BD, actualiza inventario y reinicia el carrito
    public function checkout()
    {
        $total = $this->total;

        $this->validate([
            'customer_id'          => 'required|exists:customers,id',
            'vehicle_id'           => 'nullable|exists:vehicles,id',
            'payment_method_id'    => 'required|exists:payment_methods,id',
            'cart'                 => 'required|array|min:1',
            'paid_amount'          => ['required', 'numeric', 'min:0', function ($attribute, $value, $fail) use ($total) {
                if ((float) $value < (float) $total) {
                    $fail('El monto pagado no cubre el total. Debe ingresar al menos $' . number_format($total, 2) . '.');
                }
            }],
            'discount_percentage'  => 'nullable|numeric|min:0|max:100',
        ], [
            'customer_id.required'       => 'Debe seleccionar un cliente.',
            'payment_method_id.required' => 'Debe seleccionar un método de pago.',
            'cart.required'              => 'El carrito no puede estar vacío.',
            'discount_percentage.max'    => 'El descuento no puede ser mayor al 100%.',
            'discount_percentage.min'    => 'El descuento no puede ser negativo.',
            'paid_amount.required'       => 'Debe ingresar el monto pagado.',
        ]);

        // Wrappear todo en una transacción de base de datos para asegurar integridad
        try {
            $transactionResult = \Illuminate\Support\Facades\DB::transaction(function () {
                // Verificación de stock antes de procesar
                foreach ($this->cart as $item) {
                    $service = Service::with('inventories')->find($item['id']);
                    if (!$service) {
                        throw new \Exception("El servicio '{$item['name']}' no existe.");
                    }
                    // Verificar stock de cada producto asociado al servicio
                    foreach ($service->inventories as $inventory) {
                        $requiredQty = $inventory->pivot->quantity * $item['quantity'];
                        if ($inventory->stockActual < $requiredQty) {
                            throw new \Exception("El producto '{$inventory->nombreProducto}' no tiene suficiente inventario para el servicio '{$item['name']}'. Disponible: {$inventory->stockActual}, Requerido: {$requiredQty}");
                        }
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

                    // Decrementar inventario de todos los productos del servicio
                    $service = Service::with('inventories')->find($item['id']);
                    if ($service) {
                        foreach ($service->inventories as $inventory) {
                            $decrementQty = $inventory->pivot->quantity * $item['quantity'];
                            $inventory->decrement('stockActual', $decrementQty);
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
            \Illuminate\Support\Facades\Log::error('Venta Checkout Error: ' . $e->getMessage());
            Notification::make()
                ->title('Error al procesar la venta')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.venta');
    }
}
