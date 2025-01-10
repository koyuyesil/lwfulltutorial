<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use GeminiAPI\Laravel\Facades\Gemini;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        // Kullanıcı tarafından yazılmış mesajlar
        $history = [
            [
                'message' => 'data|first_name|last_name|company|phone|email|address',
                'role' => 'user',
            ],
            [
                'message' => 'data|John|Doe|Acme Corp|123-456-7890|john.doe@example.com|123 Elm Street',
                'role' => 'model',
            ],
            [
                'message' => 'another',
                'role' => 'user',
            ],
            [
                'message' => 'data|Jane|Smith|XYZ Corp|098-765-4321|jane.smith@example.com |456 Oak Street',
                'role' => 'model',
            ],
        ];

        // Sohbet başlatılıyor
        //$chat = Gemini::startChat($history);
        // Yeni mesaj gönderiliyor (örnek veri talebi)
        //$clientText = $chat->sendMessage('another');

        $clientText ='data|John|Doe|Acme Corp|123-456-7890|john.doe@example.com|123 Elm Street';
        // Metni virgül ile bölerek dizi haline getiriyoruz
        $clientText = explode('|', $clientText);

        //dd($clientText);

        // Dizi elemanlarını ilgili alanlara atıyoruz
        return [
            'user_id' => 1, // Assuming user_id is fixed as 1
            'first_name' => $clientText[1] ?? $this->faker->firstName, // Rastgele bir ilk isim
            'last_name' => $clientText[2] ?? $this->faker->lastName,  // Rastgele bir soyisim
            'company' => rand(0, 1) ? $clientText[3] ?? $this->faker->company : null, // Rastgele bir şirket adı
            'phone' => $clientText[4] ?? $this->faker->phoneNumber, // Rastgele bir telefon numarası
            'email' => $clientText[5] ?? $this->faker->email, // Rastgele bir güvenli e-posta adresi
            'address' => $clientText[6] ?? $this->faker->address, // Rastgele bir adres
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
