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
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public Routes (if any)
Route::get('/ping', function () {
    return response()->json(['status' => 'ok', 'message' => 'API is working']);
});

// Protected Routes (require Sanctum token)
Route::middleware('auth:sanctum')->group(function () {
    
    // User Profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Sales Data
    Route::get('/sales', function () {
        return Sale::with(['customer', 'items.service'])->latest()->limit(50)->get();
    });

    // Services Catalog
    Route::get('/services', function () {
        return Service::where('estado', true)->get();
    });

    // Active Service Orders
    Route::get('/orders/active', function () {
        return ServiceOrder::whereIn('status', ['Abierta', 'En Proceso'])
            ->with(['customer', 'vehicle'])
            ->get();
    });

    // Create a Service Order via API (Example for n8n)
    Route::post('/orders', function (Request $request) {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'notes' => 'nullable|string'
        ]);

        $order = ServiceOrder::create([
            'customer_id' => $data['customer_id'],
            'user_id' => $request->user()->id ?? 1, // Fallback if needed
            'status' => ServiceOrder::STATUS_ABIERTA,
            'notes' => $data['notes'] ?? 'Creada via API n8n',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Orden creada', 'order' => $order], 201);
    });
});
