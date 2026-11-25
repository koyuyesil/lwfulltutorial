<?php

namespace Database\Seeders;

use App\Models\BoardId;
use App\Models\Client;
use App\Models\ClientProduct;
use App\Models\Product;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Not: Testler artık :memory: veritabanı kullanır; bu seeder sadece
     * geliştirme veritabanını örnek verilerle doldurmak içindir.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'Default User',
            'email' => 'defaultuser@example.com',
            'password' => 'default',
        ]);

        $this->seedWorkspaceFor($admin);
    }

    /**
     * Bir kullanıcıya ait müşteri, cihaz, ticket, görev ve board kayıtları.
     */
    private function seedWorkspaceFor(User $user): void
    {
        $products = Product::factory(6)->for($user)->create();

        Client::factory(8)->for($user)->create()->each(function (Client $client) use ($user, $products) {
            $clientProduct = ClientProduct::factory()->for($user)->create([
                'client_id' => $client->id,
                'product_id' => $products->random()->id,
            ]);

            Ticket::factory(rand(1, 3))->for($user)->create([
                'client_product_id' => $clientProduct->id,
            ]);
        });

        Task::factory(6)->for($user)->create();

        BoardId::factory(5)->for($user)->sequence(fn () => [
            'product_id' => $products->random()->id,
        ])->create();
    }
}
