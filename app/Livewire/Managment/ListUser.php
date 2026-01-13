<?php

namespace App\Livewire\Managment;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

use Filament\Tables\Columns\SelectColumn;

class ListUser extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public function table(Table $table): Table
    {
        return $table
            // Solo cargamos los usuarios registrados en el sistema
            ->query(fn (): Builder => User::query())
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre Completo')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable()
                    ->sortable(),

                // Columna editable directamente desde la tabla (solo para Administradores)
                SelectColumn::make('role')
                    ->label('Cargo / Rol')
                    ->options([
                        'Administrador' => 'Administrador',
                        'Encargado' => 'Encargado',
                        'Recepcionista' => 'Recepcionista',
                    ])
                    ->selectablePlaceholder(false)
                    ->disabled(fn () => auth()->user()?->role !== 'Administrador')
                    ->sortable(),

                // Representación visual del estado del usuario (Activo/Inactivo)
                IconColumn::make('estado')
                    ->label('Estado')
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Acción de edición completa en modal (Básico: Nombre, Email, Password)
                EditAction::make()
                    ->label('')
                    ->tooltip('Editar Usuario')
                    ->iconButton()
                    ->size('lg')
                    ->visible(fn () => auth()->user()?->role === 'Administrador')
                    ->form([
                        \Filament\Schemas\Components\Section::make('Información Personal')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->unique('users', 'email', ignoreRecord: true),
                                Select::make('role')
                                    ->options([
                                        'Administrador' => 'Administrador',
                                        'Encargado' => 'Encargado',
                                        'Recepcionista' => 'Recepcionista',
                                    ])
                                    ->required(),
                            ]),
                    ]),
                Action::make('toggleEstado')
                    ->label('')
                    ->tooltip(fn (User $record): string => $record->estado ? 'Desactivar Usuario' : 'Activar Usuario')
                    ->icon(fn (User $record): string => $record->estado ? 'heroicon-m-user-minus' : 'heroicon-m-user-plus')
                    ->color(fn (User $record): string => $record->estado ? 'danger' : 'success')
                    ->size('lg')
                    ->iconButton()
                    ->visible(fn () => auth()->user()?->role === 'Administrador')
                    ->action(function (User $record) {
                        $record->update(['estado' => !$record->estado]);
                        Notification::make()
                            ->title('Estado del Usuario Actualizado')
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

    public function render(): View
    {
        return view('livewire.managment.list-user');
    }
}
