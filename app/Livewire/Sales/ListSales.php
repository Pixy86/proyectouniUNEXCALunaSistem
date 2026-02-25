<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ListSales extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;
    
    public function mount(): void
    {
        if (!in_array(auth()->user()?->role, ['Administrador', 'Encargado'])) {
            abort(403, 'No tiene permisos para acceder a este módulo.');
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Sale::query()->with(['customer', 'user', 'paymentMethod', 'serviceOrder']))
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('customer.nombre')
                    ->label('Cliente')
                    ->formatStateUsing(fn ($record) => $record->customer ? "{$record->customer->nombre} {$record->customer->apellido}" : 'N/A')
                    ->searchable(['nombre', 'apellido'])
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Vendedor')
                    ->placeholder('Sistema')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('service_order_id')
                    ->label('Orden #')
                    ->placeholder('Venta Directa')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('paymentMethod.nombre')
                    ->label('Método de Pago')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y h:i A')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50])
            ->paginationPageOptions([10, 25, 50])
            ->filters([
                //
            ])
            ->headerActions([
                \Filament\Actions\Action::make('printReport')
                    ->label('Imprimir Reporte')
                    ->icon('heroicon-o-printer')
                    ->url(route('sales.report.pdf'))
                    ->openUrlInNewTab(),
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }

    public function render(): View
    {
        return view('livewire.sales.list-sales');
    }
}
