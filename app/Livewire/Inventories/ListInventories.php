<?php

namespace App\Livewire\Inventories;

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
use Filament\Actions\Action;
use App\Models\Inventory;
use Filament\Tables\Columns\TextColumn;
use Livewire\Component;

class ListInventories extends Component implements HasActions, HasSchemas, HasTable
{
    use InteractsWithActions;
    use InteractsWithTable;
    use InteractsWithSchemas;

    public function table(Table $table): Table
    {
        return $table
            ->query(query: fn (): Builder => Inventory::query())
            ->columns(components: [
                TextColumn::make(name: 'item.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make(name: 'quantity')
                    ->sortable()
                    ->badge(),
                TextColumn::make(name: 'description')
                    ->searchable()
                    ->sortable(),
                TextColumn::make(name: 'created_at')
                    ->sortable()
            ])
            ->filters(filters: [
                //
            ])
            ->headerActions([
                //
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

    public function mount(): void
    {
        if (Inventory::count() === 0) {
            Inventory::factory(10)->create();
        }
    }

    public function render(): View
    {
        return view('livewire.inventories.list-inventories');
    }
}
