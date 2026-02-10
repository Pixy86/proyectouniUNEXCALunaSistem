<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Customers\Customers;
use App\Livewire\Inventories\ListInventories;
use App\Livewire\Services\Services;
use App\Livewire\Managment\ListPaymentMethods;
use App\Livewire\Managment\ListUser;
use App\Livewire\Sales\ListSales;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', \App\Livewire\Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Configuración y Perfil de Usuario
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

// Gestión Principal del Sistema
Route::middleware(['auth'])->group(function (): void {
    // Ventas y Facturación
    Route::get('/manage-sales', ListSales::class)->name('sales.index');
    Route::get('/sales/report/pdf', [App\Http\Controllers\ReportController::class, 'generateSalesReport'])->name('sales.report.pdf');

    // Catálogo de Servicios
    Route::get('/manage-services', Services::class)->name('services.index');

    // Registro de Clientes
    Route::get('/manage-customers', Customers::class)->name('customers.index');

    // Control de Inventarios
    Route::get('/manage-inventories', ListInventories::class)->name('inventories.index');

    // Administración de Usuarios (Solo Admin)
    Route::get('/manage-users', ListUser::class)->name('users.index');

    // Configuración de Métodos de Pago (Solo Admin)
    Route::get('/manage-payment-methods', ListPaymentMethods::class)->name('payment-methods.index');

    // Auditoría (Solo Admin)
    Route::get('/audit-logs', \App\Livewire\Audit\ListAuditLogs::class)->name('audit.index');

    // Órdenes de Servicio
    Route::get('/service-orders', \App\Livewire\ServiceOrders\ListServiceOrders::class)->name('service-orders.index');

    // Venta
    Route::get('/venta', \App\Livewire\Venta::class)->name('venta.index');
});

// Ruta temporal para solucionar problemas de base de datos
Route::get('/migrate-fix', function() {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate:fresh', ['--seed' => true]);
        return "<h1>✅ Migración Completada Exitosamente!</h1><pre>" . \Illuminate\Support\Facades\Artisan::output() . "</pre><br><a href='/'>Ir al Inicio</a>";
    } catch (\Exception $e) {
        return "<h1>❌ Error:</h1><pre>" . $e->getMessage() . "</pre>";
    }
});