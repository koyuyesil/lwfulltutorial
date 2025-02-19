<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientProduct>
 */
class ClientProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Assuming user_id is fixed as 1
            'client_id'=>Client::factory(),
            'product_id'=>Product::factory(),
            'serial' => $this->faker->unique()->numerify('SN-####XYZ###'),  // Benzersiz bir seri numarası (örneğin SN-1234)
            'imei' => $this->faker->numerify('35#############'), // Rastgele bir IMEI numarası
            'color' => $this->faker->colorName(), // Rastgele bir renk adı (örneğin "blue", "green")
            'created_at' => now(), // Şu anki tarih ve saat
            'updated_at' => now(), // Şu anki tarih ve saat
        ];
    }
}
