<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\FavoriteProduct;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call(UserSeder::class);

        // User::factory()->count(10)->create();
        Product::factory()->count(30)->create();
        // Order::factory()->count(10)->create();
        // FavoriteProduct::factory()->count(20)->create();
        // OrderDetail::factory()->count(30)->create();



    }
}
