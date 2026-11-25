<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Örnek cihaz verisi (eski Gemini API çağrısının yerine, çevrimdışı ve hızlı).
     */
    private const DEVICES = [
        ['Xiaomi', 'Redmi', 'Note 12 Pro', '22101316G'],
        ['Xiaomi', 'Poco', 'X5 Pro', '22101320G'],
        ['Apple', 'iPhone', '13 Pro Max', 'A2643'],
        ['Apple', 'iPhone', '15', 'A3090'],
        ['Apple', 'iPad', 'Air 5', 'A2588'],
        ['Samsung', 'Galaxy', 'S21 Ultra', 'SM-G998B'],
        ['Samsung', 'Galaxy', 'A54', 'SM-A546B'],
        ['Huawei', 'P', 'P30 Pro', 'VOG-L29'],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        [$manufacturer, $brand, $modelName, $modelNumber] = fake()->randomElement(self::DEVICES);

        return [
            'user_id' => User::factory(),
            'manufacturer' => $manufacturer,
            'brand' => $brand,
            'model_name' => $modelName,
            'model_number' => $modelNumber,
            'description' => fake()->randomElement(['6GB RAM, 128GB', '8GB RAM, 256GB', '12GB RAM, 512GB']).', '.fake()->colorName(),
        ];
    }
}
