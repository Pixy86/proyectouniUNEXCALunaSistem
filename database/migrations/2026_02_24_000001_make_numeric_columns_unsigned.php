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
        // Usar sentencias SQL directas para asegurar compatibilidad con UNSIGNED en MySQL/MariaDB
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE inventories MODIFY stockActual INT UNSIGNED NOT NULL DEFAULT 0');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE services MODIFY precio DECIMAL(10, 2) UNSIGNED NOT NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE sales MODIFY total DECIMAL(10, 2) UNSIGNED NOT NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE sales MODIFY paid_amount DECIMAL(10, 2) UNSIGNED NOT NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE sales MODIFY discount DECIMAL(10, 2) UNSIGNED NOT NULL DEFAULT 0');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE service_order_items MODIFY quantity INT UNSIGNED NOT NULL DEFAULT 1');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE service_order_items MODIFY price DECIMAL(10, 2) UNSIGNED NOT NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE sales_items MODIFY quantity INT UNSIGNED NOT NULL DEFAULT 1');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE sales_items MODIFY price DECIMAL(10, 2) UNSIGNED NOT NULL');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE inventory_service MODIFY quantity INT UNSIGNED NOT NULL DEFAULT 1');
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
