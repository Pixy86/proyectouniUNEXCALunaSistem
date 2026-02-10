<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Item;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Item::factory(10)->create();
        // Inventory::factory(30)->create();
        // Customer::factory(5)->create();
        PaymentMethod::factory(3)->create();

    }
}
