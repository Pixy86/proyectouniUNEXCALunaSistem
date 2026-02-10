<?php

namespace App\Livewire\Managment;

use App\Models\AuditLog;
use App\Models\PaymentMethod;
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

class ListPaymentMethods extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public function table(Table $table): Table
    {
        return $table
            // Listado de métodos de pago configurados (Efectivo, Zelle, etc.)
            ->query(fn (): Builder => PaymentMethod::query())
            ->columns([
                TextColumn::make('nombre')
                    ->label('Método de Pago')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50),
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
                    ->label('Nuevo Método')
                    ->icon('heroicon-m-plus')
                    ->color('primary')
                    ->form([
                        \Filament\Schemas\Components\Section::make('Detalles del Método')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        TextInput::make('nombre')
                                            ->required()
                                            ->unique('payment_methods', 'nombre')
                                            ->maxLength(255),
                                        Toggle::make('estado')
                                            ->label('Activo')
                                            ->default(true)
                                            ->onColor('success'),
                                    ]),
                                Textarea::make('descripcion')
                                    ->rows(3),
                            ]),
                    ])
                    ->action(function (array $data) {
                        $method = PaymentMethod::create($data);
                        AuditLog::registrar(
                            accion: AuditLog::ACCION_CREATE,
                            descripcion: "Nuevo método de pago registrado: {$method->nombre}",
                            modelo: 'PaymentMethod',
                            modeloId: $method->id
                        );
                        Notification::make()
                            ->title('Método Creado')
                            ->success()
                            ->send();
                    })
                    ->modalWidth('2xl'),
            ])
            ->actions([
                // Cambiar disponibilidad del método de pago sin eliminarlo
                Action::make('toggleEstado')
                    ->label('')
                    ->tooltip(fn (PaymentMethod $record): string => $record->estado ? 'Desactivar' : 'Activar')
                    ->icon(fn (PaymentMethod $record): string => $record->estado ? 'heroicon-m-x-circle' : 'heroicon-m-check-circle')
                    ->color(fn (PaymentMethod $record): string => $record->estado ? 'danger' : 'success')
                    ->size('lg')
                    ->iconButton()
                    ->action(function (PaymentMethod $record) {
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
                        \Filament\Schemas\Components\Section::make('Actualizar Método')
                            ->schema([
                                \Filament\Schemas\Components\Grid::make(2)
                                    ->schema([
                                        TextInput::make('nombre')
                                            ->required()
                                            ->unique('payment_methods', 'nombre', ignoreRecord: true),
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
        return view('livewire.managment.list-payment-methods');
    }
}
