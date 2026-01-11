<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
final class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id'  => Invoice::factory(),
            'item_id'     => Product::factory(),
            'description' => fake()->words(3, true),
            'quantity'    => fake()->randomFloat(5, 1, 10), // 5 decimalen
            'unit_price'  => fake()->randomFloat(5, 50, 500),
            'vat_rate'    => fake()->randomElement([0, 9, 21]),
            'unit'        => fake()->randomElement(['stuk', 'uur', 'kg']),
            // totalen hoef je niet te vullen -> HasMoneyTrait berekent ze
        ];
    }

    /**
     * State: invoice item without linked item (custom description).
     */
    public function withoutItem(): static
    {
        // Factuurregel zonder gekoppeld artikel (losse beschrijving)
        return $this->state(fn (): array => [
            'item_id'     => null,
            'description' => fake()->sentence(3),
        ]);
    }
}
