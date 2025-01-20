<?php

namespace Database\Factories;

use GeminiAPI\Laravel\Facades\Gemini;
use Illuminate\Database\Eloquent\Factories\Factory;
use Exception;
use RuntimeException;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
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
        try {
            // Gemini API'ye veri oluşturma isteği gönderiyoruz
            $deviceText = Gemini::generateText($examplePrompt);
        } catch (RuntimeException $e) {
            // Herhangi bir hata durumunda (özellikle quota hatası), boş veri döndürüyoruz
            $deviceText = '';
        }
        // Metni virgül ile bölerek dizi haline getiriyoruz
        $device = explode('|', $deviceText);
        $man = ['Xiaomi', 'Apple', 'Samsung'];
        $brd = ['Mi', 'Redmi', 'Poco', 'iPhone', 'iPad'];
        $num = ['3', '4', '5', '12', '6S', '6SP', '7', '8', 'X'];


        // Dizi elemanlarını ilgili alanlara atıyoruz
        return [
            'manufacturer' => $device[0] ?? $man[array_rand($man)],         // Örnek: "Xiaomi,Apple,Samsung"
            'brand' => $device[1] ?? $brd[array_rand($brd)],                // Örnek: "Mi,Redmi,Poco,iPhone,iPad"
            'model_name' => $device[2] ?? $num[array_rand($num)],           // Örnek: "3,4,5,12,6S,6SP,7,8,X"
            'model_number' => $device[3] ?? fake()->bothify('A-#####G'),   // Örnek: "A1586"
            'description' => $device[4] ?? fake()->sentence(),                     // Örnek: "Kamera sayısı boyutlar pixel ram hafıza vs"
            'created_at' => now(),
            'updated_at' => now(),
            'user_id' => 1, // Kullanıcı ID'si sabit
        ];
    }
}
