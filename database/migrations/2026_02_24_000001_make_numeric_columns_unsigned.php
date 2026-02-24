<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Aseguramos que las columnas de inventario sean positivas
        Schema::table('inventories', function (Blueprint $table) {
            $table->unsignedInteger('stockActual')->default(0)->change();
        });

        // Aseguramos que los precios de servicios sean positivos
        Schema::table('services', function (Blueprint $table) {
            $table->unsignedDecimal('precio', 10, 2)->change();
        });

        // Aseguramos que los montos de ventas sean positivos
        Schema::table('sales', function (Blueprint $table) {
            $table->unsignedDecimal('total', 10, 2)->change();
            $table->unsignedDecimal('paid_amount', 10, 2)->change();
            $table->unsignedDecimal('discount', 10, 2)->default(0)->change();
        });

        // Aseguramos que las cantidades y precios en items de órdenes sean positivos
        Schema::table('service_order_items', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->change();
            $table->unsignedDecimal('price', 10, 2)->change();
        });

        // Aseguramos que las cantidades y precios en items de ventas sean positivos
        Schema::table('sales_items', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->change();
            $table->unsignedDecimal('price', 10, 2)->change();
        });

        // Aseguramos que las cantidades en la relación servicio-inventario sean positivas
        Schema::table('inventory_service', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es estrictamente necesario revertir a signed, pero se deja la estructura por si acaso
        Schema::table('inventories', function (Blueprint $table) { $table->integer('stockActual')->default(0)->change(); });
        Schema::table('services', function (Blueprint $table) { $table->decimal('precio', 10, 2)->change(); });
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('total', 10, 2)->change();
            $table->decimal('paid_amount', 10, 2)->change();
            $table->decimal('discount', 10, 2)->default(0)->change();
        });
    }
};
