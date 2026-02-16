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
        // Crear tabla pivote para relación muchos-a-muchos entre servicios e inventarios
        Schema::create('inventory_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('inventory_id')->constrained('inventories')->cascadeOnDelete();
            $table->integer('quantity')->default(1); // Cantidad del producto usado por el servicio
            $table->timestamps();
        });

        // Eliminar columna inventory_id de services (ya no es belongsTo)
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['inventory_id']);
            $table->dropColumn('inventory_id');
        });

        // Eliminar columna cantidad de services (ahora es calculada)
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('cantidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->integer('cantidad')->default(0);
            $table->foreignId('inventory_id')->nullable()->constrained('inventories')->nullOnDelete();
        });

        Schema::dropIfExists('inventory_service');
    }
};
