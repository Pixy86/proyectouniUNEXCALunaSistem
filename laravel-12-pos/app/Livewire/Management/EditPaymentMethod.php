<?php

namespace App\Livewire\Management;

use Filament\Forms\Components\Textarea;
use Livewire\Component;
use Filament\Schemas\Schema;
use App\Models\PaymentMethod;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;


class EditPaymentMethod extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public PaymentMethod $record;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill($this->record->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
               Section::make('Edit Payment Method')
                ->description('update the payment method details as you wish!!')
                ->columns(2)
                ->schema([
                    TextInput::make('name'),
                    Textarea::make('description')
                    ->unique(),
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
        ->title('Payment Method Updated!')
        ->success()
        ->body("Payment method {$this->record->name} has been updated successfully!")
        ->send();
    }

    public function render(): View
    {
        return view('livewire.management.edit-payment-method');
    }
}
