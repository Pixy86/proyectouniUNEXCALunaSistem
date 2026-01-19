<?php

use App\Models\Service;
use App\Models\Inventory;

$service = Service::with('inventory')->latest()->first();

if ($service) {
    echo "Service: " . $service->nombre . "\n";
    echo "ID: " . $service->id . "\n";
    echo "Inventory ID: " . $service->inventory_id . "\n";
    if ($service->inventory) {
        echo "Inventory Item: " . $service->inventory->nombreProducto . "\n";
        echo "Stock Actual: " . $service->inventory->stockActual . "\n";
        echo "Stock Type: " . gettype($service->inventory->stockActual) . "\n";
    } else {
        echo "No linked inventory found.\n";
    }
} else {
    echo "No services found.\n";
}
