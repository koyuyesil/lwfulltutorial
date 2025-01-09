<?php

namespace Database\Factories;
use GeminiAPI\Laravel\Facades\Gemini;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        // 'manufacturer' => fake()->company(),    // Örnek: "ABC Teknoloji A.Ş."
        // 'brand' => fake()->company(),           // Örnek: "ABC Teknoloji A.Ş."
        // 'model_name' => fake()->bothify('IPhone-#####'), // Örnek: "XYZ-12345"
        // 'model_number' => fake()->bothify('A-#####'), // Örnek: "XYZ-12345"
        // 'description' => fake()->sentence(),   // Örnek: "Kullanıcı dostu bir cihazdır."
        // 'created_at' => now(),
        // 'updated_at' => now(),
        // 'user_id' => 1, // Assuming user_id is fixed as 1

        // Örnek markalar ve üretim talebi
        $examplePrompt = <<<EOT
Generate device data in the following format:
manufacturer|brand|model_name|model_number|description

Examples:
Xiaomi|Mi|Note 12 Pro|A2101|High resolution camera, 8GB RAM, 128GB storage
Apple|iPhone|13 Pro Max|A2483|Triple camera, 6.7-inch OLED display, 6GB RAM, 512GB storage
Samsung|Galaxy|S21 Ultra|SM-G998B|108MP camera, 5000mAh battery, Exynos 2100 chipset

Produce similar data for new devices. Each request will return data for a different product.
EOT;
        // Yapay zeka tarafından veri oluşturuluyor
        $deviceText = Gemini::generateText($examplePrompt);

        // Metni virgül ile bölerek dizi haline getiriyoruz
        $device = explode('|', $deviceText);

        // Dizi elemanlarını ilgili alanlara atıyoruz
        return [
            'manufacturer' => $device[0] ?? null,    // Örnek: "Xiaomi,Apple"
            'brand' => $device[1] ?? null,           // Örnek: "Mi,Redmi,Poco,iPhone,iPad"
            'model_name' => $device[2] ?? null,      // Örnek: "Note 12,6S,6SP,7,8,X"
            'model_number' => $device[3] ?? null,    // Örnek: "A1586"
            'description' => $device[4] ?? null,     // Örnek: "Kamera sayısı boyutlar pixel ram hafıza vs"
            'created_at' => now(),
            'updated_at' => now(),
            'user_id' => 1, // Kullanıcı ID'si sabit
        ];
    }
}
