<?php

namespace Database\Factories;

use App\Enums\RepairMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoardId>
 */
class BoardIdFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //'user_id' => User::factory(), // İlişkili kullanıcı oluşturulur
            'user_id' => 1, // İlişkili kullanıcı oluşturulur
            'product_id' => Product::factory(), // İlişkili ürün oluşturulur
            'build_name' => $this->faker->word(), // Örnek isim oluşturulur
            'resistances' => json_encode([
                'R1' => 100000, // Örnek R1 değeri
                'R2' => $this->faker->randomFloat(2, 1, 100000), // Örnek R2 değeri
            ]),
            // 'repair_methods' => json_encode($this->faker->randomElements([
            //     'meta',
            //     'flash',
            //     'patch',
            //     'unlocked',
            //     'locked',
            //     'resistor'
            // ], rand(2, 6))), // Rastgele tamir yöntemleri seçilir
            'repair_methods' => $this->faker->randomElements(
                RepairMethod::cases(),
                rand(2, 6)), // ["flash","patch","unlocked","locked"] Rastgele tamir yöntemleri dizisi
            'mass_production_hwid' => $this->faker->uuid(), // Seri üretim HWID
            'pre_production_hwid' => $this->faker->uuid(), // Ön üretim HWID
            'description' => $this->faker->sentence(), // Örnek açıklama
            'created_at' => now(),
            'updated_at' => now(),

        ];
    }
}
