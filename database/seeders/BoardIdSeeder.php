<?php

namespace Database\Seeders;

use App\Models\BoardId;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BoardIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // //tüm kullanıcıya rastgele user ve board
        // BoardId::factory()
        // ->count(10) // 10 BoardId oluşturur
        // ->has(User::factory()) // Her BoardId için 1 kullanıcı
        // ->has(Product::factory()) // Her BoardId için 3 cihaz
        // ->create();

        //user 1 e boardid
        BoardId::factory()
            ->count(10) // 10 BoardId oluşturur
            ->has(Product::factory()) // Her BoardId için 3 cihaz
            ->create([ 'user_id' => 1, ]);



        //badway
        // Örnek kullanıcılar oluştur
        // $users = User::factory(10)->create();

        // $users->each(function ($user) {
        //     // Her kullanıcıya cihazlar atayın
        //     $product = Product::factory(rand(2, 5))->create([
        //         'user_id' => $user->id,
        //     ]);

        //     $product->each(function ($product) {
        //         // Her cihaz için birkaç BoardId oluşturun
        //         BoardId::factory(rand(3, 7))->create([
        //             'product_id' => $product->id,
        //         ]);
        //     });
        // });
    }
}
