<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use App\Models\Client;
use App\Models\Ticket;
use App\Models\BoardId;
use App\Models\Product;
use App\Models\ClientProduct;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Factories\UserFactory;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Constraint\Count;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Model kullanarak varsayılan yönetici oluşturma
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin'),
            'email_verified_at' => now(),
            'remember_token' => null,
        ]);

        // Model kullanarak varsayılan kullanıcı oluşturma
        User::create([
            'name' => 'Default User', // Elle girilen ad
            'email' => 'defaultuser@example.com', // Elle girilen e-posta
            'password' => Hash::make('default'), // Elle girilen şifre
            'email_verified_at' => now(), // E-posta doğrulama tarihi
            'remember_token' => null,
        ]);

        $this->call([
            BoardIdSeeder::class,
            TicketSeeder::class,

        ]);


        //Product::factory(10)->create();
        //User::factory(10)->create();
        //Task::factory(10)->create();

        //Customer::factory(10)->create();
        //Task::factory(10)->create(['user_id' => User::factory()]);
        //Client::factory(10)->create(['user_id'=> User::factory()]);





        User::factory(1)->create()->each(function ($user) {
            Client::factory(3)->create()->each(function ($client) use ($user) {
                $products = Product::factory(1)->create([
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
