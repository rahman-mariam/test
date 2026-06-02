<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        if (User::where('email', 'test@example.com')->doesntExist()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        // Create 10 dummy products if none exist
        if (Product::count() === 0) {
            for ($i = 1; $i <= 10; $i++) {
                Product::create([
                    'title' => "Product $i",
                    'description' => "Sample description for product $i",
                    'price' => rand(100, 1000) / 100,
                ]);
            }
        }
    }
}
