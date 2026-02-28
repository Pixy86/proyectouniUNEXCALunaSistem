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
    
    public function mount(): void
    {
        // Role check handled by middleware
    }

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
            ->headerActions([
                Action::make('create')
                    ->label('Nuevo Usuario')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->visible(fn () => auth()->user()?->role === 'Administrador')
                    ->form([
                        \Filament\Schemas\Components\Section::make('Información del Nuevo Usuario')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nombre Completo')
                                    ->required()
                                    ->maxLength(255)
                                    ->regex('/^[\pL\s\-\']+$/u')
                                    ->validationMessages([
                                        'regex' => 'El nombre solo debe contener letras.',
                                    ]),
                                TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->email()
                                    ->required()
                                    ->unique('users', 'email'),
                                TextInput::make('telefono')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->regex('/^[0-9]+$/')
                                    ->validationMessages([
                                        'regex' => 'El teléfono solo debe contener números.',
                                    ]),
                                Select::make('role')
                                    ->label('Cargo / Rol')
                                    ->options([
                                        'Administrador' => 'Administrador',
                                        'Encargado' => 'Encargado',
                                        'Recepcionista' => 'Recepcionista',
                                    ])
                                    ->required(),
                                TextInput::make('password')
                                    ->label('Contraseña')
                                    ->password()
                                    ->required()
                                    ->minLength(8),
                            ]),
                    ])
                    ->action(function (array $data) {
                        $user = User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'telefono' => $data['telefono'] ?? null,
                            'role' => $data['role'],
                            'password' => $data['password'], // Laravel hashes automatically if using casts or hooks
                            'estado' => true,
                        ]);

                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_CREATE,
                            descripcion: "Nuevo usuario creado: {$user->name} (Rol: {$user->role})",
                            modelo: 'User',
                            modeloId: $user->id
                        );

                        Notification::make()
                            ->title('Usuario Creado')
                            ->success()
                            ->send();
                    })
                    ->modalWidth('2xl')
                    ->closeModalByClickingAway(false),
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
                                    ->label('Nombre Completo')
                                    ->required()
                                    ->maxLength(255)
                                    ->regex('/^[\pL\s\-\']+$/u')
                                    ->validationMessages([
                                        'regex' => 'El nombre solo debe contener letras.',
                                    ]),
                                TextInput::make('email')
                                    ->label('Correo Electrónico')
                                    ->email()
                                    ->required()
                                    ->unique('users', 'email', ignoreRecord: true),
                                TextInput::make('telefono')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->regex('/^[0-9]+$/')
                                    ->validationMessages([
                                        'regex' => 'El teléfono solo debe contener números.',
                                    ]),
                                Select::make('role')
                                    ->label('Cargo / Rol')
                                    ->options([
                                        'Administrador' => 'Administrador',
                                        'Encargado' => 'Encargado',
                                        'Recepcionista' => 'Recepcionista',
                                    ])
                                    ->required(),
                            ]),
                    ])
                    ->closeModalByClickingAway(false)
                    ->before(function (User $record) use (&$oldUserData) {
                        $oldUserData = $record->toArray();
                    })
                    ->after(function (User $record) use (&$oldUserData) {
                        $newData = $record->fresh()->toArray();
                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_UPDATE,
                            descripcion: "Usuario '{$record->name}' actualizado",
                            modelo: 'User',
                            modeloId: $record->id,
                            datosAnteriores: $oldUserData ?? [],
                            datosNuevos: $newData
                        );
                        Notification::make()
                            ->title('Usuario Actualizado')
                            ->success()
                            ->send();
                    }),
                Action::make('toggleEstado')
                    ->label('')
                    ->tooltip(fn (User $record): string => $record->estado ? 'Desactivar Usuario' : 'Activar Usuario')
                    ->icon(fn (User $record): string => $record->estado ? 'heroicon-m-user-minus' : 'heroicon-m-user-plus')
                    ->color(fn (User $record): string => $record->estado ? 'danger' : 'success')
                    ->size('lg')
                    ->iconButton()
                    ->visible(fn () => auth()->user()?->role === 'Administrador')
                    ->action(function (User $record) {
                        $oldData = $record->toArray();
                        $record->update(['estado' => !$record->estado]);
                        \App\Models\AuditLog::registrar(
                            accion: \App\Models\AuditLog::ACCION_UPDATE,
                            descripcion: "Estado de usuario '{$record->name}' actualizado a: " . ($record->estado ? 'Activo' : 'Inactivo'),
                            modelo: 'User',
                            modeloId: $record->id,
                            datosAnteriores: $oldData,
                            datosNuevos: $record->fresh()->toArray()
                        );
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
