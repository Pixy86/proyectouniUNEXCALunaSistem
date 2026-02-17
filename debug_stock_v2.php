<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Service;

$service = Service::with('inventory')->latest()->first();

if ($service) {
    echo "Service: " . $service->nombre . "\n";
    echo "ID: " . $service->id . "\n";
    echo "Inventory ID: " . $service->inventory_id . "\n";
    if ($service->inventory) {
        echo "Inventory Item: " . $service->inventory->nombreProducto . "\n";
        echo "Stock Actual: " . $service->inventory->stockActual . "\n";
        echo "Stock Type: " . gettype($service->inventory->stockActual) . "\n";
        
        // Check if filtered query would find it
        $found = Service::whereHas('inventory', function ($q) {
            $q->where('stockActual', '>', 0);
        })->find($service->id);
        
        echo "Found in Filtered Query: " . ($found ? 'YES' : 'NO') . "\n";
        
    } else {
        echo "No linked inventory found.\n";
    }
} else {
    echo "No services found.\n";
}
