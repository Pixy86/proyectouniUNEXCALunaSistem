<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Customers\Customers;
use App\Livewire\Inventories\ListInventories;
use App\Livewire\Items\ListItems;
use App\Livewire\Managment\ListPaymentMethods;
use App\Livewire\Managment\ListUser;
use App\Livewire\Sales\ListSales;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

Route::middleware(['auth'])->group(function (): void {
    Route::get('/manage-sales', ListSales::class)->name('sales.index');
    Route::get('/manage-items', ListItems::class)->name('items.index');    
    Route::get('/manage-customers', Customers::class)->name('customers.index');
    Route::get('/manage-inventories', ListInventories::class)->name('inventories.index');
    Route::get('/manage-users', ListUser::class)->name('users.index');  
    Route::get('/manage-payment-methods', ListPaymentMethods::class)->name('payment-methods.index');
});