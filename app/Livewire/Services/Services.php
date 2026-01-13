<?php

namespace App\Livewire\Services;

use App\Models\Service;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\CreateAction;
use Filament\Actions\BulkActionGroup;
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
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Services extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public function table(Table $table): Table
    {
        return $table
            // Listado de servicios disponibles en el taller
            ->query(fn (): Builder => Service::query())
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
                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->badge()
                    ->sortable(),
                IconColumn::make('estado')
                    ->label('Estado')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Creación de servicios (No incluye duración, se enfoca en descripción)
                Action::make('create')
                    ->label('Nuevo Servicio')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->form([
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
                                        TextInput::make('cantidad')
                                            ->label('Cantidad')
                                            ->numeric()
                                            ->default(0)
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
                    ])
                    ->action(function (array $data) {
                        Service::create($data);
                        Notification::make()
                            ->title('Servicio Creado')
                            ->success()
                            ->send();
                    })
                    ->modalWidth('2xl'),
            ])
            ->actions([
                Action::make('toggleEstado')
                    ->label('')
                    ->tooltip(fn (Service $record): string => $record->estado ? 'Desactivar' : 'Activar')
                    ->icon(fn (Service $record): string => $record->estado ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                    ->color(fn (Service $record): string => $record->estado ? 'danger' : 'success')
                    ->size('lg')
                    ->iconButton()
                    ->action(function (Service $record) {
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
                        \Filament\Schemas\Components\Section::make('Actualizar Servicio')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        TextInput::make('nombre')
                                            ->required(),
                                        TextInput::make('precio')
                                            ->numeric()
                                            ->prefix('$')
                                            ->required(),
                                        TextInput::make('cantidad')
                                            ->label('Cantidad')
                                            ->numeric()
                                            ->required(),
                                        Toggle::make('estado')
                                            ->onColor('success'),
                                    ]),
                                Textarea::make('descripcion')
                                    ->rows(3),
                            ]),
                    ]),
                DeleteAction::make()
                    ->label('')
                    ->tooltip('Eliminar')
                    ->size('lg')
                    ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.services.services');
    }
}
