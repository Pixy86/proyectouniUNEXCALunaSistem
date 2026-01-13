<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Inventory;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Administrador Principal (Acceso total al sistema)
        User::factory()->create([
            'name' => 'Josue',
            'email' => 'josue@sgiosci.com',
            'password' => bcrypt('admin123'),
            'role' => 'Administrador',
            'estado' => true,
        ]);

        // Perfiles de prueba para diferentes departamentos
        User::factory()->create([
            'name' => 'Juan Encargado',
            'email' => 'juan@sgiosci.com',
            'password' => bcrypt('password'),
            'role' => 'Encargado',
            'estado' => true,
        ]);

        User::factory()->create([
            'name' => 'Maria Recepcionista',
            'email' => 'maria@sgiosci.com',
            'password' => bcrypt('password'),
            'role' => 'Recepcionista',
            'estado' => true,
        ]);

        // Generamos datos maestros para el funcionamiento del taller
        \App\Models\PaymentMethod::factory(3)->create();
        Inventory::factory(10)->create();
        
        // Creamos clientes con sus vehículos y ventas históricas vinculadas
        Customer::factory(10)
            ->hasVehicles(2)
            ->hasSales(1)
            ->create();
    }
}
