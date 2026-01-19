<?php

namespace App\Livewire\Audit;

use App\Models\AuditLog;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ListAuditLogs extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public function mount(): void
    {
        // Solo administradores pueden acceder a esta página
        if (auth()->user()?->role !== 'Administrador') {
            abort(403, 'Acceso no autorizado');
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => AuditLog::query()->with('user'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha y Hora')
                    ->dateTime('d/m/Y h:i:s A')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable()
                    ->default('Sistema'),
                TextColumn::make('accion')
                    ->label('Acción')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'LOGIN' => 'success',
                        'LOGOUT' => 'gray',
                        'CREAR' => 'info',
                        'ACTUALIZAR' => 'warning',
                        'ELIMINAR' => 'danger',
                        'VER' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('modelo')
                    ->label('Módulo')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('modelo_id')
                    ->label('ID Registro')
                    ->placeholder('-'),
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50)
                    ->tooltip(fn (AuditLog $record): ?string => $record->descripcion)
                    ->searchable()
                    ->wrap(),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('accion')
                    ->label('Acción')
                    ->options([
                        'LOGIN' => 'Login',
                        'LOGOUT' => 'Logout',
                        'CREAR' => 'Crear',
                        'ACTUALIZAR' => 'Actualizar',
                        'ELIMINAR' => 'Eliminar',
                    ]),
                SelectFilter::make('user_id')
                    ->label('Usuario')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('desde')
                            ->label('Desde'),
                        DatePicker::make('hasta')
                            ->label('Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                //
            ])
            ->bulkActions([
                //
            ])
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public function render(): View
    {
        return view('livewire.audit.list-audit-logs');
    }
}
