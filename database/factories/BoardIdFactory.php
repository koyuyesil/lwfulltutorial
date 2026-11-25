<?php

namespace Database\Factories;

use App\Enums\DividerType;
use App\Enums\RepairMethod;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BoardId>
 */
class BoardIdFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'product_id' => Product::factory(),
            'build_name' => strtoupper($this->faker->bothify('??##')).'_'.$this->faker->randomElement(['MP', 'PVT', 'DVT', 'EVT']),
            'divider_type' => DividerType::ADC,
            'chip' => \App\Enums\ChipType::PMIC,
            'vref' => 1.8,
            'resistances' => [
                'R1' => 100000,
                'R2' => $this->faker->randomElement([4700, 10000, 22000, 47000, 100000]),
            ],
            'repair_methods' => $this->faker->randomElements(array_map(fn ($m) => $m->value, RepairMethod::cases()), rand(1, 3)),
            'mass_production_hwid' => strtoupper($this->faker->bothify('HW-####-??')),
            'pre_production_hwid' => strtoupper($this->faker->bothify('PP-####-??')),
            'description' => $this->faker->sentence(),
        ];
    }

    public function type(DividerType $type, ?\App\Enums\ChipType $chip = null): static
    {
        return $this->state(array_filter(['divider_type' => $type, 'chip' => $chip]));
    }
}
