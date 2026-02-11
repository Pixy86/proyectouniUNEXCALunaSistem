<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Buscamos cualquier usuario con el correo admin@sgiosci.com 
        // y le damos el rol de Administrador
        User::where('email', 'admin@sgiosci.com')
            ->update([
                'role' => 'Administrador',
                'estado' => true
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No es necesario deshacer
    }
};
