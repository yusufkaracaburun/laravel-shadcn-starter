<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Equipment;
use App\Enums\EquipmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
final class EquipmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Equipment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'          => $this->faker->words(3, true),
            'serial_number' => mb_strtoupper($this->faker->bothify('EQ-#####-??')),
            'type'          => $this->faker->randomElement(['Drill', 'Excavator', 'Saw', 'Generator']),
            'status'        => $this->faker->randomElement(EquipmentStatus::cases()),
            'image'         => null,
        ];
    }
}
