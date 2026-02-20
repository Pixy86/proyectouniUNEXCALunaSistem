<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Vehicle;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Customers extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public function mount(): void
    {
        //
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Customer::query())
            ->columns([
                TextColumn::make('cedula_rif')
                    ->label('Cédula / RIF')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('full_name')
                    ->label('Nombre y Apellido')
                    ->state(fn (Customer $record): string => "{$record->nombre} {$record->apellido}")
                    ->searchable(['nombre', 'apellido'])
                    ->sortable(),
                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable(),
                IconColumn::make('estado')
                    ->label('Estado')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters(filters: [
                //
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Nuevo Cliente')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->form([
                        \Filament\Schemas\Components\Section::make('Información Personal')
                            ->description('Datos de identificación del cliente')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        TextInput::make('cedula_rif')
                                            ->label('Cédula / RIF')
                                            ->required()
                                            ->numeric()
                                            ->unique('customers', 'cedula_rif'),
                                        TextInput::make('nombre')
                                            ->required()
                                            ->regex('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u')
                                            ->validationMessages([
                                                'regex' => 'El nombre solo debe contener letras.',
                                            ]),
                                        TextInput::make('apellido')
                                            ->required()
                                            ->regex('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u')
                                            ->validationMessages([
                                                'regex' => 'El apellido solo debe contener letras.',
                                            ]),
                                        Toggle::make('estado')
                                            ->label('Cliente Activo')
                                            ->default(true)
                                            ->onColor('success')
                                            ->inline(false),
                                    ]),
                            ]),
                        \Filament\Schemas\Components\Section::make('Contacto y Dirección')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        TextInput::make('telefono')
                                            ->label('Teléfono Principal')
                                            ->tel()
                                            ->required()
                                            ->regex('/^[0-9]+$/')
                                            ->validationMessages([
                                                'regex' => 'El teléfono solo debe contener números.',
                                            ]),
                                        TextInput::make('telefono_secundario')
                                            ->label('Teléfono Secundario')
                                            ->tel()
                                            ->regex('/^[0-9]+$/')
                                            ->validationMessages([
                                                'regex' => 'El teléfono solo debe contener números.',
                                            ]),
                                        TextInput::make('email')
                                            ->email()
                                            ->placeholder('correo@ejemplo.com'),
                                    ]),
                                Textarea::make('direccion')
                                    ->label('Dirección de Habitación')
                                    ->rows(3),
                            ]),
                    ])
                    ->action(function (array $data) {
                        $customer = Customer::create($data);
                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_CREATE,
                            descripcion: "Nuevo cliente registrado: {$customer->nombre} {$customer->apellido}",
                            modelo: 'Customer',
                            modeloId: $customer->id
                        );
                        Notification::make()
                            ->title('Cliente Creado')
                            ->body('El cliente ha sido registrado exitosamente.')
                            ->success()
                            ->send();
                    })
                    ->modalWidth('2xl')
                    ->closeModalByClickingAway(false),
            ])
            ->actions([
                Action::make('vehicles')
                    ->label('')
                    ->tooltip('Vehículos')
                    ->icon('heroicon-m-truck')
                    ->color('info')
                    ->size('lg')
                    ->iconButton()
                    ->modalHeading(fn (Customer $record): string => "Gestionar Vehículos de: {$record->nombre} {$record->apellido}")
                    ->form([
                        \Filament\Schemas\Components\Section::make('Vehículos del Cliente')
                            ->description(fn (Customer $record): string => "A continuación puede agregar o editar los vehículos pertenecientes a {$record->nombre} {$record->apellido}")
                            ->schema([
                                Repeater::make('vehicles')
                                    ->schema([
                                        \Filament\Schemas\Components\Grid::make(2)
                                            ->schema([
                                                TextInput::make('placa')
                                                    ->label('Placa')
                                                    ->required()
                                                    ->placeholder('Ej: ABC123')
                                                    ->distinct()
                                                    ->validationMessages([
                                                        'required' => 'La placa es obligatoria.',
                                                        'distinct' => 'No puedes repetir la misma placa en la lista.',
                                                    ]),
                                                TextInput::make('marca')
                                                    ->label('Marca')
                                                    ->required()
                                                    ->placeholder('Ej: Toyota')
                                                    ->validationMessages([
                                                        'required' => 'La marca es obligatoria.',
                                                    ]),
                                                TextInput::make('modelo')
                                                    ->label('Modelo')
                                                    ->required()
                                                    ->placeholder('Ej: Corolla')
                                                    ->validationMessages([
                                                        'required' => 'El modelo es obligatorio.',
                                                    ]),
                                                TextInput::make('color')
                                                    ->label('Color')
                                                    ->required()
                                                    ->placeholder('Ej: Blanco')
                                                    ->validationMessages([
                                                        'required' => 'El color es obligatorio.',
                                                    ]),
                                                Select::make('tipo_vehiculo')
                                                    ->label('Tipo de Vehículo')
                                                    ->options([
                                                        'Moto' => 'Moto',
                                                        'Carro' => 'Carro',
                                                        'Camioneta' => 'Camioneta',
                                                        'Camioneta extra grande' => 'Camioneta extra grande',
                                                        'Otros' => 'Otros',
                                                    ])
                                                    ->required()
                                                    ->placeholder('Seleccione...')
                                                    ->validationMessages([
                                                        'required' => 'El tipo de vehículo es obligatorio.',
                                                    ]),
                                                Toggle::make('estado')
                                                    ->label('Activo')
                                                    ->default(true)
                                                    ->onColor('success'),
                                            ]),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => $state['placa'] ?? 'Nuevo Vehículo')
                                    ->addActionLabel('Agregar Vehículo')
                                    ->deletable(fn () => auth()->user()?->role === 'Administrador')
                                    ->collapsible()
                                    ->defaultItems(0),
                            ]),
                    ])
                    ->fillForm(fn (Customer $record): array => [
                        'vehicles' => $record->vehicles->toArray(),
                    ])
                    ->action(function (Customer $record, array $data): void {
                        try {
                            $processedIds = [];

                            foreach ($data['vehicles'] as $vehicleData) {
                                // Usamos updateOrCreate para mantener los registros existentes
                                // Buscamos por ID si existe, o por placa si es de este cliente
                                $vehicle = $record->vehicles()->updateOrCreate(
                                    ['id' => $vehicleData['id'] ?? null],
                                    $vehicleData
                                );
                                $processedIds[] = $vehicle->id;
                            }

                            // Eliminamos permanentemente los que el usuario quitó en la interfaz (Solo si es Admin)
                            if (auth()->user()?->role === 'Administrador') {
                                $vehiclesToDelete = $record->vehicles()->whereNotIn('id', $processedIds)->get();
                                
                                foreach ($vehiclesToDelete as $v) {
                                    if ($v->hasServiceOrders()) {
                                        Notification::make()
                                            ->title("No se puede eliminar vehículo: {$v->placa}")
                                            ->body("No se puede eliminar un recurso con registros vinculados.")
                                            ->danger()
                                            ->persistent()
                                            ->send();
                                        continue; 
                                    }
                                    $v->forceDelete();
                                }
                            }

                            \App\Models\AuditLog::registrar(
                                accion: \App\Models\AuditLog::ACCION_UPDATE,
                                descripcion: "Vehículos del cliente {$record->nombre} {$record->apellido} actualizados",
                                modelo: 'Vehicle',
                                modeloId: $record->id
                            );

                            Notification::make()
                                ->title('Vehículos Actualizados')
                                ->body("Se han guardado los cambios en los vehículos de {$record->nombre} {$record->apellido}")
                                ->success()
                                ->send();
                        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                            Notification::make()
                                ->title('Placa Duplicada')
                                ->body('No se pudo guardar: Una de las placas ingresadas ya pertenece a otro vehículo en el sistema.')
                                ->danger()
                                ->persistent()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Error al actualizar vehículos')
                                ->body('Ocurrió un error inesperado: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->modalSubmitActionLabel('Guardar Cambios')
                    ->modalCancelActionLabel('Cancelar')
                    ->modalWidth('4xl')
                    ->closeModalByClickingAway(false),
                Action::make('toggleEstado')
                    ->label('')
                    ->tooltip(fn (Customer $record): string => $record->estado ? 'Desactivar' : 'Activar')
                    ->icon(fn (Customer $record): string => $record->estado ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                    ->color(fn (Customer $record): string => $record->estado ? 'danger' : 'success')
                    ->size('lg')
                    ->iconButton()
                    ->action(function (Customer $record) {
                        $oldData = $record->toArray();
                        $record->update(['estado' => !$record->estado]);
                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_UPDATE,
                            descripcion: "Estado de cliente '{$record->nombre} {$record->apellido}' actualizado a: " . ($record->estado ? 'Activo' : 'Inactivo'),
                            modelo: 'Customer',
                            modeloId: $record->id,
                            datosAnteriores: $oldData,
                            datosNuevos: $record->fresh()->toArray()
                        );
                        Notification::make()
                            ->title('Estado Actualizado')
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->label('')
                    ->tooltip('Editar')
                    ->size('lg')
                    ->iconButton()
                    ->form([
                        \Filament\Schemas\Components\Section::make('Actualizar Datos')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        TextInput::make('cedula_rif')
                                            ->numeric()
                                            ->required(),
                                        TextInput::make('nombre')
                                            ->required()
                                            ->regex('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u')
                                            ->validationMessages([
                                                'regex' => 'El nombre solo debe contener letras.',
                                            ]),
                                        TextInput::make('apellido')
                                            ->required()
                                            ->regex('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u')
                                            ->validationMessages([
                                                'regex' => 'El apellido solo debe contener letras.',
                                            ]),
                                        TextInput::make('telefono')
                                            ->tel()
                                            ->required()
                                            ->regex('/^[0-9]+$/')
                                            ->validationMessages([
                                                'regex' => 'El teléfono solo debe contener números.',
                                            ]),
                                        TextInput::make('email')
                                            ->email(),
                                        Toggle::make('estado')
                                            ->label('Activo')
                                            ->onColor('success'),
                                    ]),
                            ]),

                    ])
                    ->closeModalByClickingAway(false)
                    ->action(function (Customer $record, array $data) {
                        $oldData = $record->toArray();
                        $record->update($data);
                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_UPDATE,
                            descripcion: "Datos del cliente '{$record->nombre} {$record->apellido}' actualizados",
                            modelo: 'Customer',
                            modeloId: $record->id,
                            datosAnteriores: $oldData,
                            datosNuevos: $record->fresh()->toArray()
                        );
                        Notification::make()
                            ->title('Cliente Actualizado')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Eliminar')
                    ->modalHeading('Borrar cliente')
                    ->size('lg')
                    ->visible(fn () => auth()->user()?->role === 'Administrador')
                    ->iconButton()
                    ->action(function (Customer $record) {
                        if ($record->hasLinkedRecords()) {
                            Notification::make()
                                ->title('No se puede eliminar')
                                ->body('no se puede eliminar un recurso con registros vinculados')
                                ->danger()
                                ->send();
                            return;
                        }

                        $nombre = "{$record->nombre} {$record->apellido}";
                        $record->delete();
                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_DELETE,
                            descripcion: "Cliente eliminado: {$nombre}",
                            modelo: 'Customer',
                            modeloId: $record->id
                        );
                        Notification::make()
                            ->title('Cliente Eliminado')
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

    public function render(): View
    {
        return view('livewire.customers.customers');
    }
}
