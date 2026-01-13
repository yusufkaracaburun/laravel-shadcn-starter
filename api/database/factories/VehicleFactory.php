<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Vehicle;
use App\Enums\VehicleStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
final class VehicleFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vehicle::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'make'          => $this->faker->randomElement(['Toyota', 'Ford', 'Honda', 'BMW', 'Mercedes']),
            'model'         => $this->faker->word(),
            'year'          => $this->faker->year(),
            'license_plate' => mb_strtoupper($this->faker->bothify('??-###-??')),
            'vin'           => mb_strtoupper($this->faker->bothify('VIN#########')),
            'status'        => $this->faker->randomElement(VehicleStatus::cases()),
        ];
    }
}
