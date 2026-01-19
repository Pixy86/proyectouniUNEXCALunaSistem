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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('accion', 100); // LOGIN, LOGOUT, CREATE, UPDATE, DELETE, etc.
            $table->string('modelo')->nullable(); // Model name affected (Customer, Sale, etc.)
            $table->unsignedBigInteger('modelo_id')->nullable(); // ID of the affected record
            $table->text('descripcion')->nullable(); // Additional details
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('datos_anteriores')->nullable(); // Previous data (for updates)
            $table->json('datos_nuevos')->nullable(); // New data (for creates/updates)
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
