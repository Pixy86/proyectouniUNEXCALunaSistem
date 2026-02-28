<?php

namespace App\Livewire\Inventories;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\Inventory;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ListInventories extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public function table(Table $table): Table
    {
        return $table
            // Gestión de productos e insumos requeridos para los servicios
            ->query(fn (): Builder => Inventory::query())
            ->columns([
                TextColumn::make('nombreProducto')
                    ->label('Nombre del Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stockActual')
                    ->label('Stock Actual')
                    ->numeric()
                    ->badge()
                    ->color(fn (int $state): string => $state === 0 ? 'danger' : ($state < 10 ? 'warning' : 'success'))
                    ->sortable(),
                IconColumn::make('estado')
                    ->label('Estado')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Registrado')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nuevo Producto')
                    ->modalHeading('Crear Inventario')
                    ->icon('heroicon-m-plus')
                    ->modalWidth('2xl')
                    ->after(function (Inventory $record) {
                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_CREATE,
                            descripcion: "Nuevo producto de inventario creado: {$record->nombreProducto}",
                            modelo: 'Inventory',
                            modeloId: $record->id
                        );
                        Notification::make()
                            ->title('Producto Creado')
                            ->success()
                            ->send();
                    })
                    ->form([
                        \Filament\Schemas\Components\Section::make('Información del Producto')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        TextInput::make('nombreProducto')
                                            ->label('Nombre del Producto')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique('inventories', 'nombreProducto')
                                            ->validationMessages([
                                                'unique' => 'Este producto ya se encuentra registrado.',
                                            ]),
                                        TextInput::make('stockActual')
                                            ->label('Stock Inicial')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->live()
                                            ->required()
                                            ->validationMessages([
                                                'min' => 'El stock no puede ser negativo.',
                                            ]),
                                        Toggle::make('estado')
                                            ->label('Activo')
                                            ->default(true)
                                            ->onColor('success'),
                                    ]),
                                Textarea::make('descripcion')
                                    ->label('Descripción')
                                    ->rows(3),
                            ]),
                    ])
                    ->closeModalByClickingAway(false),
            ])
            ->actions([
                ViewAction::make()
                    ->label('')
                    ->tooltip('Consultar Detalles')
                    ->iconButton()
                    ->size('lg')
                    ->form([
                        \Filament\Schemas\Components\Section::make('Detalles del Producto')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        Placeholder::make('nombreProducto')
                                            ->label('Nombre')
                                            ->content(fn ($record) => $record->nombreProducto),
                                        Placeholder::make('stockActual')
                                            ->label('Stock')
                                            ->content(fn ($record) => $record->stockActual),
                                        Placeholder::make('estado')
                                            ->label('Estado')
                                            ->content(fn ($record) => $record->estado ? 'Activo' : 'Inactivo'),
                                        Placeholder::make('created_at')
                                            ->label('Fecha de Registro')
                                            ->content(fn ($record) => $record->created_at?->format('d/m/Y H:i') ?? 'N/A'),
                                    ]),
                                Placeholder::make('descripcion')
                                    ->label('Descripción')
                                    ->content(fn ($record) => $record->descripcion ?? 'Sin descripción'),
                            ]),
                    ]),
                EditAction::make()
                    ->label('')
                    ->tooltip('Editar Producto')
                    ->iconButton()
                    ->size('lg')
                    ->before(function (Inventory $record, \Closure $set) use (&$oldInventoryData) {
                        $oldInventoryData = $record->toArray();
                    })
                    ->after(function (Inventory $record) use (&$oldInventoryData) {
                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_UPDATE,
                            descripcion: "Producto de inventario '{$record->nombreProducto}' actualizado",
                            modelo: 'Inventory',
                            modeloId: $record->id,
                            datosAnteriores: $oldInventoryData ?? [],
                            datosNuevos: $record->fresh()->toArray()
                        );
                        Notification::make()
                            ->title('Producto Actualizado')
                            ->success()
                            ->send();
                    })
                    ->form([
                        \Filament\Schemas\Components\Section::make('Actualizar Producto')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        TextInput::make('nombreProducto')
                                            ->label('Nombre del Producto')
                                            ->required()
                                            ->unique('inventories', 'nombreProducto', ignoreRecord: true)
                                            ->validationMessages([
                                                'unique' => 'Ya existe un producto con ese nombre.',
                                            ]),
                                        TextInput::make('stockActual')
                                            ->label('Stock Actual')
                                            ->numeric()
                                            ->minValue(0)
                                            ->live()
                                            ->required()
                                            ->validationMessages([
                                                'min' => 'El stock no puede ser negativo.',
                                            ]),
                                        Toggle::make('estado')
                                            ->label('Activo')
                                            ->onColor('success'),
                                        DateTimePicker::make('created_at')
                                            ->label('Fecha de Registro')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->visible(fn ($record) => $record !== null),
                                    ]),
                                Textarea::make('descripcion')
                                    ->label('Descripción')
                                    ->rows(3),
                            ]),
                    ])
                    ->closeModalByClickingAway(false),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Eliminar')
                    ->modalHeading('Borrar producto del inventario')
                    ->iconButton()
                    ->size('lg')
                    ->visible(fn () => auth()->user()?->role === 'Administrador')
                    ->action(function (Inventory $record) {
                        if ($record->hasLinkedRecords()) {
                            Notification::make()
                                ->title('No se puede eliminar')
                                ->body('No se puede eliminar un producto con existencias o vinculado a un servicio.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $nombre = $record->nombreProducto;
                        $record->delete();
                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_DELETE,
                            descripcion: "Producto de inventario eliminado: {$nombre}",
                            modelo: 'Inventory',
                            modeloId: $record->id
                        );
                        Notification::make()
                            ->title('Producto Eliminado')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function mount(): void
    {
        /*
        if (Inventory::count() === 0) {
            Inventory::factory(10)->create();
        }
        */
    }

    public function render(): View
    {
        return view('livewire.inventories.list-inventories');
    }
}
