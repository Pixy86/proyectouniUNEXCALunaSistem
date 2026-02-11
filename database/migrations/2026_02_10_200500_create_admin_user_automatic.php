<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Creamos o actualizamos el administrador principal
        User::updateOrCreate(
            ['email' => 'admin@sgiosci.com'], // Busca por este correo
            [
                'name' => 'Administrador SGIOSCI',
                'password' => Hash::make('admin123'), // Encriptación correcta
                'role' => 'Administrador',
                'estado' => true,
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        User::where('email', 'admin@sgiosci.com')->delete();
    }
};
