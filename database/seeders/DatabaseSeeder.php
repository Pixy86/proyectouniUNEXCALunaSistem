<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Inventory;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;

use Illuminate\Support\Facades\Crypt;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Administrador Principal (Acceso total al sistema)
        User::updateOrCreate(
            ['email' => 'admin@sgiosci.com'],
            [
                'name' => 'Administrador SGIOSCI',
                'password' => bcrypt('admin123'),
                'role' => 'Administrador',
                'estado' => true,
                'security_answer_1' => 'Mascota',
                'security_answer_2' => 'Ciudad',
                'security_answer_3' => 'Amigo',
                'plain_password_encrypted' => Crypt::encryptString('admin123'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'josue@sgiosci.com'],
            [
                'name' => 'Josue',
                'password' => bcrypt('admin123'),
                'role' => 'Administrador',
                'estado' => true,
                'security_answer_1' => 'Mascota',
                'security_answer_2' => 'Ciudad',
                'security_answer_3' => 'Amigo',
                'plain_password_encrypted' => Crypt::encryptString('admin123'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'usuariodeprueba@gmail.com'],
            [
                'name' => 'Usuario de Prueba',
                'password' => bcrypt('admin123'),
                'role' => 'Administrador',
                'estado' => true,
                'security_answer_1' => 'Mascota',
                'security_answer_2' => 'Ciudad',
                'security_answer_3' => 'Amigo',
                'plain_password_encrypted' => Crypt::encryptString('admin123'),
            ]
        );

        // Perfiles de prueba para diferentes departamentos
        User::updateOrCreate(
            ['email' => 'juan@sgiosci.com'],
            [
                'name' => 'Juan Encargado',
                'password' => bcrypt('password'),
                'role' => 'Encargado',
                'estado' => true,
                'security_answer_1' => 'Mascota',
                'security_answer_2' => 'Ciudad',
                'security_answer_3' => 'Amigo',
                'plain_password_encrypted' => Crypt::encryptString('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'maria@sgiosci.com'],
            [
                'name' => 'Maria Recepcionista',
                'password' => bcrypt('password'),
                'role' => 'Recepcionista',
                'estado' => true,
                'security_answer_1' => 'Mascota',
                'security_answer_2' => 'Ciudad',
                'security_answer_3' => 'Amigo',
                'plain_password_encrypted' => Crypt::encryptString('password'),
            ]
        );

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
