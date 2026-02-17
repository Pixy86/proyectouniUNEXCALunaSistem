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
                    ->searchable(['customer.nombre', 'customer.apellido'])
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
                        $order = ServiceOrder::create([
                            'customer_id' => $data['customer_id'],
                            'vehicle_id' => $data['vehicle_id'],
                            'user_id' => auth()->id(),
                            'status' => ServiceOrder::STATUS_ABIERTA,
                            'notes' => $data['notes'] ?? null,
                        ]);

                        foreach ($data['items'] as $item) {
                            $service = Service::find($item['service_id']);
                            ServiceOrderItem::create([
                                'service_order_id' => $order->id,
                                'service_id' => $item['service_id'],
                                'quantity' => $item['quantity'] ?? 1,
                                'price' => $service->precio,
                            ]);
                        }

                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_CREATE,
                            descripcion: "Orden de servicio #{$order->id} creada para el cliente {$order->customer->nombre} {$order->customer->apellido}",
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
                    ->visible(fn (ServiceOrder $record): bool => $record->status !== ServiceOrder::STATUS_TERMINADO && $record->status !== ServiceOrder::STATUS_CANCELADA)
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
                        ]);
                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_UPDATE,
                            descripcion: "Estado de orden #{$record->id} actualizado a: {$data['status']}",
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
                        $oldData = $record->toArray();
                        $record->update([
                            'customer_id' => $data['customer_id'],
                            'vehicle_id' => $data['vehicle_id'],
                            'notes' => $data['notes'] ?? null,
                        ]);

                        // Replace items
                        $record->items()->delete();
                        foreach ($data['items'] as $item) {
                            $service = Service::find($item['service_id']);
                            ServiceOrderItem::create([
                                'service_order_id' => $record->id,
                                'service_id' => $item['service_id'],
                                'quantity' => $item['quantity'] ?? 1,
                                'price' => $service->precio,
                            ]);
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
                    ->action(function (ServiceOrder $record) {
                        $record->update(['status' => ServiceOrder::STATUS_CANCELADA]);
                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_UPDATE,
                            descripcion: "Orden de servicio #{$record->id} cancelada",
                            modelo: 'ServiceOrder',
                            modeloId: $record->id
                        );
                        Notification::make()
                            ->title('Orden Cancelada')
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
                                ->options(Service::where('estado', true)->with('inventories')->get()->mapWithKeys(fn ($s) => [
                                    $s->id => "{$s->nombre} - \${$s->precio}" . ($s->cantidad > 0 ? ' ✓' : ' ✗ Sin Stock')
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
                ServiceOrder::STATUS_TERMINADO => 'Terminado',
            ],
            ServiceOrder::STATUS_EN_PROCESO => [
                ServiceOrder::STATUS_TERMINADO => 'Terminado',
            ],
            default => [],
        };
    }

    public function render(): View
    {
        return view('livewire.service-orders.list-service-orders');
    }
}
