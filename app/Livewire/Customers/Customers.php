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
                                            ->required(),
                                        TextInput::make('apellido')
                                            ->required(),
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
                                            ->required(),
                                        TextInput::make('telefono_secundario')
                                            ->label('Teléfono Secundario')
                                            ->tel(),
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
                        Customer::create($data);
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
                                                    ->unique('vehicles', 'placa', ignoreRecord: true)
                                                    ->placeholder('Ej: ABC123'),
                                                TextInput::make('marca')
                                                    ->label('Marca')
                                                    ->required()
                                                    ->placeholder('Ej: Toyota'),
                                                TextInput::make('modelo')
                                                    ->label('Modelo')
                                                    ->required()
                                                    ->placeholder('Ej: Corolla'),
                                                TextInput::make('color')
                                                    ->label('Color')
                                                    ->required()
                                                    ->placeholder('Ej: Blanco'),
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
                                                    ->placeholder('Seleccione...'),
                                                Toggle::make('estado')
                                                    ->label('Activo')
                                                    ->default(true)
                                                    ->onColor('success'),
                                            ]),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => $state['placa'] ?? 'Nuevo Vehículo')
                                    ->addActionLabel('Agregar Vehículo')
                                    ->collapsible()
                                    ->defaultItems(0),
                            ]),
                    ])
                    ->fillForm(fn (Customer $record): array => [
                        'vehicles' => $record->vehicles->toArray(),
                    ])
                    ->action(function (Customer $record, array $data): void {
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

                        // Eliminamos permanentemente los que el usuario quitó en la interfaz
                        // Usamos forceDelete para que la placa quede libre inmediatamente
                        $record->vehicles()->whereNotIn('id', $processedIds)->forceDelete();

                        Notification::make()
                            ->title('Vehículos Actualizados')
                            ->body("Se han guardado los cambios en los vehículos de {$record->nombre} {$record->apellido}")
                            ->success()
                            ->send();
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
                        $record->update(['estado' => !$record->estado]);
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
                                            ->required(),
                                        TextInput::make('apellido')
                                            ->required(),
                                        TextInput::make('telefono')
                                            ->tel()
                                            ->required(),
                                        TextInput::make('email')
                                            ->email(),
                                        Toggle::make('estado')
                                            ->label('Activo')
                                            ->onColor('success'),
                                    ]),
                            ]),

                    ])
                    ->closeModalByClickingAway(false),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Eliminar')
                    ->size('lg')
                    ->iconButton(),
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
