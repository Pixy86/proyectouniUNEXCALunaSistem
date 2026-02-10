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

        User::factory()->create([
            'name' => 'Usuario de Prueba',
            'email' => 'usuariodeprueba@gmail.com',
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

        /*
        // Generamos datos maestros para el funcionamiento del autolavado

        \App\Models\PaymentMethod::factory(5)->create();
        $inventories = Inventory::factory(20)->create();
        
        // Creamos Servicios vinculados a items de inventario
        $inventories->take(10)->each(function ($inventory) {
            \App\Models\Service::factory()->create([
                'inventory_id' => $inventory->id,
            ]);
        });
        
        // Generamos clientes con sus vehículos
        $customers = Customer::factory(10)
            ->hasVehicles(2) // Cada cliente tendrá 2 vehículos
            ->create();

        // Creamos Órdenes de Servicio para algunos clientes/vehículos
        $customers->each(function ($customer) {
            $vehicle = $customer->vehicles->random();
            \App\Models\ServiceOrder::factory(2) // 2 órdenes por cliente
                ->state([
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                    'user_id' => 1, // Asignado al Admin
                ])
                ->has(\App\Models\ServiceOrderItem::factory()->count(2), 'items') // 2 items por orden
                ->create();
        });

        // Creamos Ventas Históricas
        $customers->each(function ($customer) {
            $vehicle = $customer->vehicles->random();
            \App\Models\Sale::factory(2)
                ->state([
                    'customer_id' => $customer->id,
                    'vehicle_id' => $vehicle->id,
                ])
                ->has(\App\Models\SalesItem::factory()->count(3), 'items')
                ->create();
        });
        */
    }
}
