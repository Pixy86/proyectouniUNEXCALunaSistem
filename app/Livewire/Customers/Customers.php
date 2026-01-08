<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\Vehicle;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
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
            ->query(query: fn (): Builder => Customer::query())
            ->columns(components: [
                TextColumn::make(name: 'cedula_rif')
                    ->label(label: 'Cédula/RIF')
                    ->searchable()
                    ->sortable(),
                TextColumn::make(name: 'nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make(name: 'apellido')
                    ->searchable()
                    ->sortable(),
                TextColumn::make(name: 'telefono')
                    ->label(label: 'Teléfono')
                    ->searchable(),
                TextColumn::make(name: 'email')
                    ->searchable(),
                ToggleColumn::make(name: 'estado')
                    ->label(label: 'Estado')
                    ->sortable(),
            ])
            ->filters(filters: [
                //
            ])
            ->headerActions([
                Action::make(name: 'create')
                    ->label(label: 'Nuevo Cliente')
                    ->form(schema: [
                        TextInput::make(name: 'cedula_rif')
                            ->required()
                            ->unique(table: 'customers', column: 'cedula_rif'),
                        TextInput::make(name: 'nombre')
                            ->required(),
                        TextInput::make(name: 'apellido')
                            ->required(),
                        TextInput::make(name: 'telefono')
                            ->tel()
                            ->required(),
                        TextInput::make(name: 'telefono_secundario')
                            ->tel(),
                        TextInput::make(name: 'email')
                            ->email(),
                        Textarea::make(name: 'direccion')
                            ->rows(rows: 2),
                        Toggle::make(name: 'estado')
                            ->label(label: 'Activo')
                            ->default(true)
                            ->onColor(color: 'success')
                    ])
                    ->action(action: function (array $data) {
                        Customer::create($data);
                        Notification::make()
                            ->title(title: 'Cliente creado correctamente')
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Action::make(name: 'vehicles')
                    ->label(label: 'Vehículos')
                    ->icon(icon: 'heroicon-m-truck')
                    ->color(color: 'info')
                    ->modalHeading(heading: 'Gestionar Vehículos')
                    ->form(schema: [
                        Repeater::make(name: 'vehicles')
                            ->relationship(relationshipName: 'vehicles')
                            ->schema(components: [
                                TextInput::make(name: 'placa')
                                    ->required()
                                    ->unique(table: 'vehicles', column: 'placa', ignoreRecord: true),
                                TextInput::make(name: 'marca')
                                    ->required(),
                                TextInput::make(name: 'modelo')
                                    ->required(),
                                TextInput::make(name: 'color'),
                                TextInput::make(name: 'tipo_vehiculo')
                                    ->label(label: 'Tipo de Vehículo'),
                                Toggle::make(name: 'estado')
                                    ->label(label: 'Activo')
                                    ->default(true)
                                    ->onColor(color: 'success')
                                    ->offColor(color: 'danger'),
                            ])
                            ->columns(columns: 2)
                            ->itemLabel(fn (array $state): ?string => $state['placa'] ?? null),
                    ]),
                EditAction::make(name: 'edit')
                    ->form(schema: [
                        TextInput::make(name: 'cedula_rif')
                            ->required(),
                        TextInput::make(name: 'nombre')
                            ->required(),
                        TextInput::make(name: 'apellido')
                            ->required(),
                        TextInput::make(name: 'telefono')
                            ->tel()
                            ->required(),
                        TextInput::make(name: 'email')
                            ->email(),
                        Toggle::make(name: 'estado'),
                    ]),
                DeleteAction::make(name: 'delete'),
            ])
            ->toolbarActions([
                BulkActionGroup::make(actions: [
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.customers.customers');
    }
}
