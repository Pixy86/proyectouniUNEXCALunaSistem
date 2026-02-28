<?php

namespace App\Livewire\ServiceOrders;

use App\Models\ServiceOrder;
use App\Models\ServiceOrderItem;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\Service;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ListServiceOrders extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => ServiceOrder::query()->with(['customer', 'vehicle', 'user', 'items.service']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('# Orden')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('customer.nombre')
                    ->label('Cliente')
                    ->formatStateUsing(fn (ServiceOrder $record): string => "{$record->customer->nombre} {$record->customer->apellido}")
                    ->searchable(['nombre', 'apellido', 'cedula_rif'])
                    ->sortable(),
                TextColumn::make('vehicle.placa')
                    ->label('Vehículo')
                    ->description(fn (ServiceOrder $record): string => $record->vehicle->modelo ?? '-')
                    ->searchable(),
                TextColumn::make('items_count')
                    ->label('Servicios')
                    ->state(fn (ServiceOrder $record): int => $record->items->count())
                    ->badge()
                    ->color('gray'),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Abierta' => 'warning',
                        'En Proceso' => 'info',
                        'Terminado' => 'success',
                        'Cancelada' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('user.name')
                    ->label('Responsable')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y h:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'Abierta' => 'Abierta',
                        'En Proceso' => 'En Proceso',
                        'Terminado' => 'Terminado',
                        'Cancelada' => 'Cancelada',
                    ]),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Nueva Orden')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->form($this->getOrderForm())
                    ->modalSubmitActionLabel('Guardar Orden')
                    ->modalCancelActionLabel('Cancelar')
                    ->action(function (array $data) {
                        // Validar que el vehículo pertenezca al cliente (Integridad CU-09.1.1)
                        $vehicle = \App\Models\Vehicle::find($data['vehicle_id']);
                        if ($vehicle && $vehicle->customer_id != $data['customer_id']) {
                            Notification::make()
                                ->title('Error de Integridad')
                                ->body('El vehículo seleccionado no pertenece al cliente.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Validar stock antes de crear nada
                        if (!$this->validateInventoryStock($data['items'])) {
                            return;
                        }

                        $order = ServiceOrder::create([
                            'customer_id' => $data['customer_id'],
                            'vehicle_id' => $data['vehicle_id'],
                            'user_id' => auth()->id(),
                            'status' => ServiceOrder::STATUS_ABIERTA,
                            'notes' => $data['notes'] ?? null,
                        ]);

                        foreach ($data['items'] as $item) {
                            $service = Service::with('inventories')->find($item['service_id']);
                            if ($service) {
                                ServiceOrderItem::create([
                                    'service_order_id' => $order->id,
                                    'service_id' => $item['service_id'],
                                    'quantity' => $item['quantity'] ?? 1,
                                    'price' => $service->precio,
                                ]);

                                // Decrementar inventario inmediatamente (Reserva)
                                foreach ($service->inventories as $inventory) {
                                    $decrementQty = $inventory->pivot->quantity * ($item['quantity'] ?? 1);
                                    $inventory->decrement('stockActual', $decrementQty);
                                }
                            }
                        }

                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_CREATE,
                            descripcion: "Orden de servicio #{$order->id} creada para el cliente {$order->customer->nombre} {$order->customer->apellido} (Stock reservado)",
                            modelo: 'ServiceOrder',
                            modeloId: $order->id
                        );

                        Notification::make()
                            ->title('Orden Creada')
                            ->body("Orden #{$order->id} creada exitosamente.")
                            ->success()
                            ->send();
                    })
                    ->modalWidth('2xl')
                    ->closeModalByClickingAway(false),
            ])
            ->actions([
                Action::make('view')
                    ->label('')
                    ->tooltip('Ver Detalles')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->iconButton()
                    ->modalHeading(fn (ServiceOrder $record): string => "Orden #{$record->id}")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->infolist([
                        Section::make('Información General')
                            ->columns(2)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('customer.nombre')
                                    ->label('Cliente')
                                    ->formatStateUsing(fn (ServiceOrder $record): string => "{$record->customer->nombre} {$record->customer->apellido}"),
                                \Filament\Infolists\Components\TextEntry::make('vehicle.placa')
                                    ->label('Vehículo'),
                                \Filament\Infolists\Components\TextEntry::make('user.name')
                                    ->label('Responsable'),
                                \Filament\Infolists\Components\TextEntry::make('status')
                                    ->label('Estado')
                                    ->badge(),
                            ]),
                        Section::make('Servicios')
                            ->schema([
                                \Filament\Infolists\Components\RepeatableEntry::make('items')
                                    ->label('')
                                    ->schema([
                                        \Filament\Infolists\Components\TextEntry::make('service.nombre')
                                            ->label('Servicio'),
                                        \Filament\Infolists\Components\TextEntry::make('quantity')
                                            ->label('Cant.'),
                                        \Filament\Infolists\Components\TextEntry::make('price')
                                            ->label('Precio')
                                            ->money('USD'),
                                    ])
                                    ->columns(3),
                            ]),
                        Section::make('Notas')
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('notes')
                                    ->label('')
                                    ->placeholder('Sin notas'),
                            ]),
                    ]),
                Action::make('changeStatus')
                    ->label('')
                    ->tooltip('Cambiar Estado')
                    ->icon('heroicon-m-arrow-path')
                    ->color('warning')
                    ->iconButton()
                    ->visible(fn (ServiceOrder $record): bool => 
                        $record->status === ServiceOrder::STATUS_ABIERTA && 
                        in_array(auth()->user()?->role, ['Administrador', 'Encargado'])
                    )
                    ->form([
                        Select::make('status')
                            ->label('Nuevo Estado')
                            ->options(fn (ServiceOrder $record): array => $this->getNextStatuses($record->status))
                            ->required(),
                    ])
                    ->fillForm(fn (ServiceOrder $record): array => ['status' => $record->status])
                    ->action(function (ServiceOrder $record, array $data) {
                        $oldData = $record->toArray();
                        $record->update([
                            'status' => $data['status'],
                            'completed_at' => $data['status'] === ServiceOrder::STATUS_TERMINADO ? now() : null,
                            'started_at' => ($data['status'] === ServiceOrder::STATUS_EN_PROCESO && !$record->started_at) ? now() : $record->started_at,
                        ]);
                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_UPDATE,
                            descripcion: "Estado de orden #{$record->id} actualizado a: {$data['status']}" . ($data['status'] === ServiceOrder::STATUS_EN_PROCESO ? " (Iniciada)" : ""),
                            modelo: 'ServiceOrder',
                            modeloId: $record->id,
                            datosAnteriores: $oldData,
                            datosNuevos: $record->fresh()->toArray()
                        );
                        Notification::make()
                            ->title('Estado Actualizado')
                            ->success()
                            ->send();
                    }),
                Action::make('edit')
                    ->label('')
                    ->tooltip('Editar')
                    ->icon('heroicon-m-pencil')
                    ->color('info')
                    ->iconButton()
                    ->visible(fn (ServiceOrder $record): bool => $record->status === ServiceOrder::STATUS_ABIERTA)
                    ->form($this->getOrderForm(true))
                    ->modalSubmitActionLabel('Actualizar Orden')
                    ->modalCancelActionLabel('Cancelar')
                    ->fillForm(fn (ServiceOrder $record): array => [
                        'customer_id' => $record->customer_id,
                        'vehicle_id' => $record->vehicle_id,
                        'notes' => $record->notes,
                        'items' => $record->items->map(fn ($item) => [
                            'service_id' => $item->service_id,
                            'quantity' => $item->quantity,
                        ])->toArray(),
                    ])
                    ->action(function (ServiceOrder $record, array $data) {
                        // Validar que el vehículo pertenezca al cliente (Integridad CU-09.1.1)
                        $vehicle = \App\Models\Vehicle::find($data['vehicle_id']);
                        if ($vehicle && $vehicle->customer_id != $data['customer_id']) {
                            Notification::make()
                                ->title('Error de Integridad')
                                ->body('El vehículo seleccionado no pertenece al cliente.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Validar stock antes de actualizar
                        if (!$this->validateInventoryStock($data['items'])) {
                            return;
                        }

                        $oldData = $record->toArray();
                        $record->update([
                            'customer_id' => $data['customer_id'],
                            'vehicle_id' => $data['vehicle_id'],
                            'notes' => $data['notes'] ?? null,
                        ]);

                        // Revertir stock antiguo antes de actualizar
                        foreach ($record->items as $item) {
                            $service = $item->service;
                            if ($service) {
                                foreach ($service->inventories as $inventory) {
                                    $revertQty = $inventory->pivot->quantity * $item->quantity;
                                    $inventory->increment('stockActual', $revertQty);
                                }
                            }
                        }

                        // Reemplazar items y quitar nuevo stock
                        $record->items()->delete();
                        foreach ($data['items'] as $item) {
                            $service = Service::with('inventories')->find($item['service_id']);
                            if ($service) {
                                ServiceOrderItem::create([
                                    'service_order_id' => $record->id,
                                    'service_id' => $item['service_id'],
                                    'quantity' => $item['quantity'] ?? 1,
                                    'price' => $service->precio,
                                ]);

                                // Decrementar nuevo stock
                                foreach ($service->inventories as $inventory) {
                                    $decrementQty = $inventory->pivot->quantity * ($item['quantity'] ?? 1);
                                    $inventory->decrement('stockActual', $decrementQty);
                                }
                            }
                        }

                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_UPDATE,
                            descripcion: "Orden de servicio #{$record->id} editada (Items actualizados)",
                            modelo: 'ServiceOrder',
                            modeloId: $record->id,
                            datosAnteriores: $oldData,
                            datosNuevos: $record->fresh()->toArray()
                        );

                        Notification::make()
                            ->title('Orden Actualizada')
                            ->success()
                            ->send();
                    })
                    ->closeModalByClickingAway(false),
                Action::make('cancel')
                    ->label('')
                    ->tooltip('Cancelar Orden')
                    ->icon('heroicon-m-x-circle')
                    ->color('danger')
                    ->iconButton()
                    ->visible(fn (ServiceOrder $record): bool => $record->status !== ServiceOrder::STATUS_TERMINADO && $record->status !== ServiceOrder::STATUS_CANCELADA)
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar Orden')
                    ->modalDescription('¿Estás seguro de que deseas cancelar esta orden? Esta acción no se puede deshacer.')
                    ->form([
                        Textarea::make('cancel_reason')
                            ->label('Motivo de Cancelación')
                            ->placeholder('Ingrese el motivo por el cual se cancela la orden...')
                            ->required(),
                    ])
                    ->action(function (ServiceOrder $record, array $data) {
                        $oldData = $record->toArray();

                        $record->update([
                            'status' => ServiceOrder::STATUS_CANCELADA,
                            'notes' => $record->notes . "\n\nMOTIVO CANCELACIÓN: " . $data['cancel_reason'],
                        ]);

                        // Revertir stock de inventario por cada servicio en la orden
                        foreach ($record->items as $item) {
                            $service = $item->service;
                            if (!$service) continue;

                            foreach ($service->inventories as $inventory) {
                                $requiredQty = $inventory->pivot->quantity * $item->quantity;
                                $inventory->increment('stockActual', $requiredQty);
                            }
                        }

                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_UPDATE,
                            descripcion: "Orden #{$record->id} cancelada. Motivo: {$data['cancel_reason']}",
                            modelo: 'ServiceOrder',
                            modeloId: $record->id,
                            datosAnteriores: $oldData,
                            datosNuevos: $record->fresh()->toArray()
                        );
                        Notification::make()
                            ->title('Orden Cancelada')
                            ->body('El stock del inventario ha sido restaurado.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    protected function getOrderForm(bool $isEdit = false): array
    {
        return [
            Section::make('Cliente y Vehículo')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('customer_id')
                                ->label('Cliente')
                                ->placeholder('Seleccionar cliente...')
                                ->options(Customer::query()->get()->mapWithKeys(fn ($c) => [$c->id => "{$c->nombre} {$c->apellido} ({$c->cedula_rif})"]))
                                ->searchable()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn (callable $set) => $set('vehicle_id', null)),
                            Select::make('vehicle_id')
                                ->label('Vehículo')
                                ->placeholder('Seleccionar vehículo...')
                                ->options(fn (callable $get) => Vehicle::where('customer_id', $get('customer_id'))->get()->mapWithKeys(fn ($v) => [$v->id => "{$v->marca} {$v->modelo} ({$v->placa})"]))
                                ->searchable()
                                ->required(),
                        ]),
                ]),
            Section::make('Servicios')
                ->schema([
                    Repeater::make('items')
                        ->label('')
                        ->schema([
                            Select::make('service_id')
                                ->label('Servicio')
                                ->placeholder('Seleccionar servicio...')
                                ->options(Service::where('estado', true)
                                    ->get()
                                    ->filter(fn ($s) => $s->cantidad > 0 || $s->cantidad === -1)
                                    ->mapWithKeys(fn ($s) => [
                                        $s->id => "{$s->nombre} - \${$s->precio}"
                                    ]))
                                ->searchable()
                                ->required(),
                            TextInput::make('quantity')
                                ->label('Cantidad')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required(),
                        ])
                        ->columns(2)
                        ->minItems(1)
                        ->defaultItems(1)
                        ->addActionLabel('Agregar Servicio'),
                ]),
            Section::make('Notas')
                ->schema([
                    Textarea::make('notes')
                        ->label('Observaciones')
                        ->rows(3)
                        ->placeholder('Notas adicionales sobre la orden...'),
                ]),
        ];
    }

    protected function getNextStatuses(string $currentStatus): array
    {
        return match ($currentStatus) {
            ServiceOrder::STATUS_ABIERTA => [
                ServiceOrder::STATUS_EN_PROCESO => 'En Proceso',
            ],
            default => [],
        };
    }

    protected function validateInventoryStock(array $items): bool
    {
        $inventoryRequirements = [];
        foreach ($items as $item) {
            $service = Service::with('inventories')->find($item['service_id']);
            if (!$service) continue;

            // Primero verificamos que el servicio esté activo y tenga capacidad general
            if (!$service->estado || $service->cantidad === 0) {
                Notification::make()
                    ->title('Servicio No Disponible')
                    ->body("El servicio '{$service->nombre}' está inactivo o no tiene stock.")
                    ->danger()
                    ->send();
                return false;
            }

            foreach ($service->inventories as $inventory) {
                // quantity viene en el pivot
                $requiredTotal = $inventory->pivot->quantity * ($item['quantity'] ?? 1);
                if (!isset($inventoryRequirements[$inventory->id])) {
                    $inventoryRequirements[$inventory->id] = [
                        'name' => $inventory->nombreProducto,
                        'required' => 0,
                        'available' => (int) $inventory->stockActual,
                    ];
                }
                $inventoryRequirements[$inventory->id]['required'] += $requiredTotal;
            }
        }

        foreach ($inventoryRequirements as $req) {
            if ($req['required'] > $req['available']) {
                \Filament\Notifications\Notification::make()
                    ->title('Error de Stock')
                    ->body("Stock insuficiente para el producto ({$req['name']})")
                    ->danger()
                    ->send();
                return false;
            }
        }

        return true;
    }

    public function render(): View
    {
        return view('livewire.service-orders.list-service-orders');
    }
}
