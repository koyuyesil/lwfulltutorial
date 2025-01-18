<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\BoardId;
use App\Models\Product;
use App\Models\ClientProduct;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TicketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::factory(1)->has(
        //     Client::factory(10)->has(
        //         Product::factory(3)->has(
        //             ClientProduct::factory()->has(
        //                 Ticket::factory(3)
        //             )
        //         )
        //     )
        // )->create();

        //Bad way
        // User::factory(1)->create()->each(function ($user) {
        //     Client::factory(10)->create()->each(function ($client) use ($user) {
        //         $products = Product::factory(3)->create([
        //             'user_id' => $user->id,
        //         ]);
        //         foreach ($products as $product) {
        //             $clientProduct = ClientProduct::factory()->create([
        //                 'client_id' => $client->id,
        //                 'product_id' => $product->id,
        //             ]);
        //             Ticket::factory(3)->create([
        //                 'client_product_id' => $clientProduct->id,
        //             ]);
        //             //sleep(1);
        //         }
        //     });
        // });
    }
}
