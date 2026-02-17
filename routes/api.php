<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Sale;
use App\Models\Service;
use App\Models\ServiceOrder;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/ping', function () {
    return response()->json(['status' => 'ok', 'message' => 'API is working']);
});

Route::middleware('auth:sanctum')->group(function () {
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/sales', function () {
        return Sale::with(['customer', 'items.service'])->latest()->limit(50)->get();
    });

    Route::get('/services', function () {
        return Service::where('estado', true)->get();
    });

    Route::get('/orders/active', function () {
        return ServiceOrder::whereIn('status', [ServiceOrder::STATUS_ABIERTA, ServiceOrder::STATUS_EN_PROCESO])
            ->with(['customer', 'vehicle'])
            ->get();
    });

    Route::post('/orders', function (Request $request) {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'notes' => 'nullable|string'
        ]);

        $order = ServiceOrder::create([
            'customer_id' => $data['customer_id'],
            'user_id' => $request->user()->id ?? 1,
            'status' => ServiceOrder::STATUS_ABIERTA,
            'notes' => $data['notes'] ?? 'Creada via API n8n',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Orden creada', 'order' => $order], 201);
    });
});
