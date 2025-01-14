<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\Product;
use App\Models\ClientProduct;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //User::factory(10)->create();
        //Task::factory(10)->create();
        //Device::factory(10)->create();
        //Customer::factory(10)->create();
        //Task::factory(10)->create(['user_id' => User::factory()]);
        //Client::factory(10)->create(['user_id'=> User::factory()]);

        User::factory(1)->create()->each(function ($user) {
            Client::factory(10)->create()->each(function ($client)use ($user)  {
                $products = Product::factory(3)->create([
                    'user_id' => $user->id,
                ]);
                foreach ($products as $product) {
                    $clientProduct = ClientProduct::factory()->create([
                        'client_id' => $client->id,
                        'product_id' => $product->id,
                    ]);
                    Ticket::factory(3)->create([
                        'client_product_id' => $clientProduct->id,
                    ]);
                    //sleep(1);
                }
            });
        });
    }
}
