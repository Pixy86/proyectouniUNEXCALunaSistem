<?php
use Illuminate\Contracts\Console\Kernel;
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "Users: " . \App\Models\User::count() . "\n";
echo "Inventories: " . \App\Models\Inventory::count() . "\n";
echo "Services: " . \App\Models\Service::count() . "\n";
echo "Customers: " . \App\Models\Customer::count() . "\n";
echo "Vehicles: " . \App\Models\Vehicle::count() . "\n";
echo "Sales: " . \App\Models\Sale::count() . "\n";
echo "Service Orders: " . \App\Models\ServiceOrder::count() . "\n";
