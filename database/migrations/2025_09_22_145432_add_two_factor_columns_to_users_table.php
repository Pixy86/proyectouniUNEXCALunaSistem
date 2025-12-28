<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique()->nullable(); // Código de barras (SKU)
            $table->text('description')->nullable();
            $table->decimal('price', 8, 2); // Precio de venta
            $table->decimal('cost', 8, 2)->nullable(); // Costo (para cálculo de ganancias)
            $table->integer('stock_quantity')->default(0); // Cantidad en inventario
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    // ... (el método down permanece igual)
};