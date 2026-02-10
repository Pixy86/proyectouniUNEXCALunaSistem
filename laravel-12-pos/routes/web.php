<?php

use App\Livewire\Customer\CreateCustomer;
use App\Livewire\Customer\EditCustomers;
use App\Livewire\Customer\ListCustomers;
use App\Livewire\Items\CreateInventory;
use App\Livewire\Items\CreateItem;
use App\Livewire\Items\EditInventory;
use App\Livewire\Items\EditItem;
use App\Livewire\Items\ListInventories;
use App\Livewire\Items\ListItems;
use App\Livewire\Management\CreatePaymentMethod;
use App\Livewire\Management\CreateUser;
use App\Livewire\Management\EditPaymentMethod;
use App\Livewire\Management\EditUser;
use App\Livewire\Management\ListPaymentMethods;
use App\Livewire\Management\ListUsers;
use App\Livewire\POS;
use App\Livewire\Sales\ListSales;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

Route::get('/sales/{sale}/receipt', function (\App\Models\Sale $sale) {
    $sale->load(['saleItems.item', 'customer', 'paymentMethod']);
    return view('pdf', ['records' => collect([$sale])]);
})->name('sales.receipt');

Route::middleware(['auth'])->group(function () {
    //users
    Route::get('/manage-users',ListUsers::class)->name('users.index');
    Route::get('/create-user',CreateUser::class)->name('users.create');
    Route::get('/edit-user/{record}',EditUser::class)->name('user.update');
    //inventory
    Route::get('/manage-items',ListItems::class)->name('items.index');
    Route::get('/create-item',CreateItem::class)->name('items.create');
    Route::get('/edit-item/{record}',EditItem::class)->name('item.update');
    Route::get('/manage-inventories',ListInventories::class)->name('inventories.index');
    Route::get('/create-inventory',CreateInventory::class)->name('inventories.create');
    Route::get('/edit-inventory/{record}',EditInventory::class)->name('inventory.update');
    //sales
    Route::get('/manage-sales',ListSales::class)->name('sales.index');
    //customers
    Route::get('/manage-customers',ListCustomers::class)->name('customers.index');
    Route::get('/create-customer',CreateCustomer::class)->name('customers.create');
    Route::get('/edit-customer/{record}',EditCustomers::class)->name('customer.update');
    //payment method
    Route::get('/create-payment-method',CreatePaymentMethod::class)->name('payment-method.create');
    Route::get('/manage-payment-methods',ListPaymentMethods::class)->name('payment.method.index');
    Route::get('/edit-payment-method/{record}',EditPaymentMethod::class)->name('payment-method.update');

    Route::get('/pos',POS::class)->name('pos');
}); 

require __DIR__.'/auth.php';
