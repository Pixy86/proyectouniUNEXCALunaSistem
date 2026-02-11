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
        // Esta migración revisa si la columna NO existe y la crea
        // Es la forma más segura de arreglar errores en Railway
        if (!Schema::hasColumn('customers', 'cedula_rif')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('cedula_rif')->nullable()->after('id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario
    }
};
