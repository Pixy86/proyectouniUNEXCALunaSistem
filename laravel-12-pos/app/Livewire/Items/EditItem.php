<?php

namespace App\Livewire\Items;

use App\Models\Item;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Livewire\Component;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Filament\Schemas\Components\Section;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Forms\Components\ToggleButtons;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class EditItem extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public Item $record;

    public ?array $data = [];

    public function mount(): void
    {
        //it populate the default values from db
        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Edit the Item')
                ->description('update the item details as you wish!!')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                    ->label('Item Name'),
                    TextInput::make('sku')
                    ->unique(),
                    TextInput::make('price')
                    ->prefix('$')
                    ->numeric(),
                    ToggleButtons::make('status')
                    ->label('Is this Item Active?')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'In Active',
                    ])
                    ->grouped()
                ])
            ])
            ->statePath('data')
            ->model($this->record);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->record->update($data);

        Notification::make()
        ->title('Item Updated!')
        ->success()
        ->body("Item {$this->record->name} has been updated successfully!")
        ->send();
    }

    public function render(): View
    {
        return view('livewire.items.edit-item');
    }
}
