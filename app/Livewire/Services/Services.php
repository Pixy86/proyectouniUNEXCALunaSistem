<?php

namespace App\Livewire\Services;

use App\Models\Service;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use App\Models\Inventory;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Services extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    /**
     * Formulario compartido para crear y editar servicios.
     */
    protected function getServiceForm(): array
    {
        return [
            \Filament\Schemas\Components\Section::make('Información del Servicio')
                ->schema([
                    \Filament\Schemas\Components\Grid::make(2)
                        ->schema([
                            TextInput::make('nombre')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('precio')
                                ->numeric()
                                ->prefix('$')
                                ->required(),
                            Toggle::make('estado')
                                ->label('Activo')
                                ->default(true)
                                ->onColor('success'),
                        ]),
                    Textarea::make('descripcion')
                        ->label('Descripción del Servicio')
                        ->rows(3),
                ]),
            \Filament\Schemas\Components\Section::make('Productos del Inventario')
                ->description('Selecciona los productos que este servicio utiliza y la cantidad requerida de cada uno.')
                ->schema([
                    Repeater::make('products')
                        ->label('')
                        ->schema([
                            Select::make('inventory_id')
                                ->label('Producto')
                                ->options(Inventory::where('estado', true)->pluck('nombreProducto', 'id'))
                                ->searchable()
                                ->preload()
                                ->required(),
                            TextInput::make('quantity')
                                ->label('Cantidad requerida')
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->required(),
                        ])
                        ->columns(2)
                        ->minItems(1)
                        ->defaultItems(1)
                        ->addActionLabel('Agregar Producto'),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Service::query()->with('inventories'))
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre del Servicio')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('precio')
                    ->label('Precio')
                    ->money('USD')
                    ->sortable(),
                TextColumn::make('inventories')
                    ->label('Productos')
                    ->formatStateUsing(fn (Service $record): string =>
                        $record->inventories->map(fn ($inv) =>
                            $inv->nombreProducto . ' (x' . $inv->pivot->quantity . ')'
                        )->join(', ') ?: 'Sin productos'
                    )
                    ->wrap(),
                TextColumn::make('cantidad_disponible')
                    ->label('Cantidad Disponible')
                    ->state(fn (Service $record): int => $record->cantidad)
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger')
                    ->sortable(false),
                IconColumn::make('estado')
                    ->label('Estado')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Nuevo Servicio')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->visible(fn () => in_array(auth()->user()?->role, ['Administrador', 'Encargado']))
                    ->form($this->getServiceForm())
                    ->action(function (array $data) {
                        $service = Service::create([
                            'nombre' => $data['nombre'],
                            'precio' => $data['precio'],
                            'estado' => $data['estado'] ?? true,
                            'descripcion' => $data['descripcion'] ?? null,
                        ]);

                        // Sincronizar productos del inventario
                        $pivotData = [];
                        foreach ($data['products'] as $product) {
                            $pivotData[$product['inventory_id']] = [
                                'quantity' => $product['quantity'],
                            ];
                        }
                        $service->inventories()->sync($pivotData);

                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_CREATE,
                            descripcion: "Nuevo servicio creado: {$service->nombre}",
                            modelo: 'Service',
                            modeloId: $service->id
                        );

                        Notification::make()
                            ->title('Servicio Creado')
                            ->success()
                            ->send();
                    })
                    ->modalWidth('2xl')
                    ->closeModalByClickingAway(false),
            ])
            ->actions([
                Action::make('toggleEstado')
                    ->label('')
                    ->tooltip(fn (Service $record): string => $record->estado ? 'Desactivar' : 'Activar')
                    ->icon(fn (Service $record): string => $record->estado ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                    ->color(fn (Service $record): string => $record->estado ? 'danger' : 'success')
                    ->size('lg')
                    ->visible(fn () => in_array(auth()->user()?->role, ['Administrador', 'Encargado']))
                    ->iconButton()
                    ->action(function (Service $record) {
                        $oldData = $record->toArray();
                        $record->update(['estado' => !$record->estado]);
                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_UPDATE,
                            descripcion: "Estado de servicio '{$record->nombre}' actualizado a: " . ($record->estado ? 'Activo' : 'Inactivo'),
                            modelo: 'Service',
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
                    ->size('lg')
                    ->visible(fn () => in_array(auth()->user()?->role, ['Administrador', 'Encargado']))
                    ->iconButton()
                    ->modalHeading('Editar Servicio')
                    ->form($this->getServiceForm())
                    ->fillForm(fn (Service $record): array => [
                        'nombre' => $record->nombre,
                        'precio' => $record->precio,
                        'estado' => $record->estado,
                        'descripcion' => $record->descripcion,
                        'products' => $record->inventories->map(fn ($inv) => [
                            'inventory_id' => $inv->id,
                            'quantity' => $inv->pivot->quantity,
                        ])->toArray(),
                    ])
                    ->action(function (Service $record, array $data) {
                        $oldData = $record->toArray();
                        $record->update([
                            'nombre' => $data['nombre'],
                            'precio' => $data['precio'],
                            'estado' => $data['estado'] ?? true,
                            'descripcion' => $data['descripcion'] ?? null,
                        ]);

                        // Sincronizar productos
                        $pivotData = [];
                        foreach ($data['products'] as $product) {
                            $pivotData[$product['inventory_id']] = [
                                'quantity' => $product['quantity'],
                            ];
                        }
                        $record->inventories()->sync($pivotData);

                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_UPDATE,
                            descripcion: "Servicio '{$record->nombre}' actualizado",
                            modelo: 'Service',
                            modeloId: $record->id,
                            datosAnteriores: $oldData,
                            datosNuevos: $record->fresh()->toArray()
                        );

                        Notification::make()
                            ->title('Servicio Actualizado')
                            ->success()
                            ->send();
                    })
                    ->modalWidth('2xl')
                    ->closeModalByClickingAway(false),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Eliminar')
                    ->modalHeading('Borrar servicio')
                    ->size('lg')
                    ->visible(fn () => auth()->user()?->role === 'Administrador')
                    ->iconButton()
                    ->action(function (Service $record) {
                        if ($record->hasLinkedRecords()) {
                            Notification::make()
                                ->title('No se puede eliminar')
                                ->body('no se puede eliminar un recurso con registros vinculados')
                                ->danger()
                                ->send();
                            return;
                        }

                        $nombre = $record->nombre;
                        $record->delete();
                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_DELETE,
                            descripcion: "Servicio eliminado: {$nombre}",
                            modelo: 'Service',
                            modeloId: $record->id
                        );
                        Notification::make()
                            ->title('Servicio Eliminado')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.services.services');
    }
}

