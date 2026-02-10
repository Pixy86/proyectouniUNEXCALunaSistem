<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Vehicle;
use Filament\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;

class CustomerVehicles extends Component implements HasForms, HasTable, HasActions
{
    use InteractsWithForms;
    use InteractsWithTable;
    use InteractsWithActions;

    public Customer $customer;

    public function mount(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Vehicle::query()->where('customer_id', $this->customer->id))
            ->headerActions([
                Action::make('create')
                    ->label('Nuevo Vehículo')
                    ->icon('heroicon-m-plus')
                    ->button()
                    ->modalHeading('Registrar Vehículo')
                    ->form([
                        TextInput::make('placa')
                            ->required()
                            ->unique('vehicles', 'placa'),
                        TextInput::make('marca')
                            ->required(),
                        TextInput::make('modelo')
                            ->required(),
                        TextInput::make('color')
                            ->required(),
                        Select::make('tipo_vehiculo')
                            ->label('Tipo de Vehículo')
                            ->options([
                                'Moto' => 'Moto',
                                'Carro' => 'Carro',
                                'Camioneta' => 'Camioneta',
                                'Camioneta extra grande' => 'Camioneta extra grande',
                                'Otros' => 'Otros',
                            ])
                            ->required(),
                        Toggle::make('estado')
                            ->label('Activo')
                            ->default(true)
                            ->onColor('success'),
                    ])
                    ->action(function (array $data) {
                        $data['customer_id'] = $this->customer->id;
                        Vehicle::create($data);
                        Notification::make()
                            ->title('Vehículo registrado exitosamente')
                            ->success()
                            ->send();
                    }),
            ])
            ->columns([
                TextColumn::make('placa')
                    ->label('Placa')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('marca')
                    ->label('Marca')
                    ->searchable(),
                TextColumn::make('modelo')
                    ->label('Modelo')
                    ->searchable(),
                TextColumn::make('color')
                    ->label('Color'),
                TextColumn::make('tipo_vehiculo')
                    ->label('Tipo'),
                IconColumn::make('estado')
                    ->label('Estado')
                    ->boolean(),
            ])
            ->actions([
                Action::make('edit')
                    ->label('')
                    ->tooltip('Editar')
                    ->icon('heroicon-m-pencil')
                    ->button()
                    ->color('warning')
                    ->form([
                        TextInput::make('placa')
                            ->required()
                            ->unique('vehicles', 'placa', ignoreRecord: true),
                        TextInput::make('marca')
                            ->required(),
                        TextInput::make('modelo')
                            ->required(),
                        TextInput::make('color')
                            ->required(),
                        Select::make('tipo_vehiculo')
                            ->label('Tipo de Vehículo')
                            ->options([
                                'Moto' => 'Moto',
                                'Carro' => 'Carro',
                                'Camioneta' => 'Camioneta',
                                'Camioneta extra grande' => 'Camioneta extra grande',
                                'Otros' => 'Otros',
                            ])
                            ->required(),
                        Toggle::make('estado')
                            ->label('Activo')
                            ->onColor('success'),
                    ])
                    ->fillForm(fn (Vehicle $record): array => $record->attributesToArray())
                    ->action(function (Vehicle $record, array $data) {
                        $record->update($data);
                        Notification::make()
                            ->title('Vehículo actualizado exitosamente')
                            ->success()
                            ->send();
                    }),
                Action::make('delete')
                    ->label('')
                    ->tooltip('Eliminar')
                    ->icon('heroicon-m-trash')
                    ->button()
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Eliminar Vehículo')
                    ->modalDescription('¿Estás seguro de que deseas eliminar este vehículo? Esta acción no se puede deshacer.')
                    ->modalSubmitActionLabel('Sí, eliminar')
                    ->action(function (Vehicle $record) {
                        $record->delete();
                        Notification::make()
                            ->title('Vehículo eliminado exitosamente')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public function render()
    {
        return view('livewire.customers.customer-vehicles');
    }
}
