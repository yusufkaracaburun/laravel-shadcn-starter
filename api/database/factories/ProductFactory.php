<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => fake()->unique()->jobTitle(),
            'description' => fake()->text(),
            'unit_price'  => fake()->randomFloat(2, 1, 1000),
            'vat_rate'    => 21,
            'unit'        => 'uur',
        ];
    }
}
