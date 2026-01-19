<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Sale;
use App\Models\Service;
use App\Models\Inventory;
use Illuminate\Support\Facades\Log;

echo "Checking latest sales...\n";

$latestSale = Sale::with(['salesItems.service', 'customer'])->latest()->first();

if ($latestSale) {
    echo "Latest Sale ID: " . $latestSale->id . "\n";
    echo "Customer: " . ($latestSale->customer?->name ?? 'None') . "\n";
    echo "Total: " . $latestSale->total . "\n";
    echo "Created At: " . $latestSale->created_at . "\n";
    echo "Items:\n";
    foreach ($latestSale->salesItems as $item) {
        echo " - " . ($item->service?->nombre ?? 'Unknown') . " (Qty: " . $item->quantity . ")\n";
    }
} else {
    echo "No sales found in database.\n";
}

echo "\nChecking 'cantidad' (limit) update logic...\n";
// Check if any service has cantidad decremented recently? Difficult to know history.
// Just checking current state of a service.
$service = Service::with('inventory')->latest()->first();
if ($service) {
    echo "Service: " . $service->nombre . " | Cantidad (Cupo): " . $service->cantidad . " | Inventory Stock: " . ($service->inventory?->stockActual ?? 'N/A') . "\n";
}
