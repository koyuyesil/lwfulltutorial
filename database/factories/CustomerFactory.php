<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fname' => fake()->firstName(), // Rastgele bir ilk isim
            'lname' => fake()->lastName(),  // Rastgele bir soyisim
            'company' => rand(0, 1) ? fake()->company() : null, // Rastgele bir şirket adı
            'phone' => fake()->phoneNumber(), // Rastgele bir telefon numarası
            'email' => fake()->safeEmail(), // Rastgele bir güvenli e-posta adresi
            'address' => fake()->address(), // Rastgele bir adres
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
