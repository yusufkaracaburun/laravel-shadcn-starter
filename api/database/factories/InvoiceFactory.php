<?php

declare(strict_types=1);

namespace Database\Factories;

use Cknow\Money\Money;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Customer;
use App\Models\InvoiceItem;
use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
final class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id'    => Customer::factory(),
            'invoice_number' => 'INV-' . fake()->unique()->randomNumber(5),
            'date'           => fake()->dateTimeBetween('-1 month', 'now'),
            'due_days'       => 30,
            'date_due'       => now()->addDays(30),
            'status'         => InvoiceStatus::DRAFT->value,
            'subtotal'       => 0,
            'total_vat_0'    => 0,
            'total_vat_9'    => 0,
            'total_vat_21'   => 0,
            'total'          => 0,
            'notes'          => fake()->optional()->text(),
        ];
    }

    /**
     * Configure the factory.
     */
    public function configure(): static
    {
        return $this
            ->afterCreating(function (Invoice $invoice): void {
                // Pak willekeurige items (1 tot 3 regels)
                $items = Product::query()->inRandomOrder()->take(random_int(1, 3))->get();

                $inv_items = [];

                foreach ($items as $item) {
                    $quantity = fake()->randomFloat(2, 1, 5);
                    $unitPrice = $item->unit_price;
                    $quantity = (string) $quantity;

                    $subtotal = $unitPrice->multiply($quantity);

                    $vatRate = (string) $item->vat_rate;
                    $vatAmount = $subtotal->multiply(bcdiv($vatRate, '100', 10));

                    $totalIncl = $subtotal->add($vatAmount);

                    $inv_items[] = InvoiceItem::factory()->create([
                        'invoice_id'     => $invoice->id,
                        'product_id'     => $item->id,
                        'name'           => $item->name,
                        'description'    => $item->description,
                        'quantity'       => fake()->randomFloat(2, 1, 5),
                        'unit_price'     => $item->unit_price,
                        'vat_rate'       => $item->vat_rate,
                        'unit'           => $item->unit,
                        'total_excl_vat' => $subtotal,
                        'total_vat'      => $vatAmount,
                        'total_incl_vat' => $totalIncl,
                    ]);
                }

                if (fake()->boolean(40)) {
                    $quantity = fake()->randomFloat(2, 1, 3);
                    $unit_price = fake()->randomFloat(2, 50, 200);
                    $vat_rate = fake()->randomElement([0, 9, 21]);
                    $total_excl_vat = $quantity * $unit_price;
                    $total_vat = $total_excl_vat * ($vat_rate / 100);
                    $total_incl_vat = $total_excl_vat + $total_vat;

                    $inv_items[] = InvoiceItem::factory()->create([
                        'invoice_id'     => $invoice->id,
                        'product_id'     => null,
                        'name'           => $item->name,
                        'description'    => fake()->sentence(3),
                        'quantity'       => $quantity,
                        'unit_price'     => $unit_price,
                        'vat_rate'       => $vat_rate,
                        'unit'           => 'stuk',
                        'total_excl_vat' => $total_excl_vat,
                        'total_vat'      => $total_vat,
                        'total_incl_vat' => $total_incl_vat,
                    ]);
                }

                $subtotal = Money::EUR(0);
                $vat0 = Money::EUR(0);
                $vat9 = Money::EUR(0);
                $vat21 = Money::EUR(0);
                $total = Money::EUR(0);

                foreach ($inv_items as $item) {
                    $subtotal = $subtotal->add($item->total_excl_vat);

                    // BTW verdelen per tarief
                    $rate = (float) $item->vat_rate;

                    if ($rate === 0) {
                        $vat0 = $vat0->add($item->total_vat);
                    } elseif ($rate === 9) {
                        $vat9 = $vat9->add($item->total_vat);
                    } elseif ($rate === 21) {
                        $vat21 = $vat21->add($item->total_vat);
                    }

                    $total = $total->add($item->total_incl_vat);
                }

                $invoice->subtotal = $subtotal;
                $invoice->total_vat_0 = $vat0;
                $invoice->total_vat_9 = $vat9;
                $invoice->total_vat_21 = $vat21;
                $invoice->total = $total;
                $invoice->save();
            });
    }

    /**
     * State: sent invoice.
     */
    public function sent(): static
    {
        return $this->state(fn (): array => ['status' => InvoiceStatus::SENT->value]);
    }

    /**
     * State: paid invoice.
     */
    public function paid(): static
    {
        return $this->state(fn (): array => ['status' => InvoiceStatus::PAID->value]);
    }

    /**
     * State: unpaid invoice.
     */
    public function unpaid(): static
    {
        return $this->state(fn (): array => ['status' => InvoiceStatus::UNPAID->value]);
    }

    /**
     * State: overdue invoice.
     */
    public function overdue(): static
    {
        return $this->state(fn (): array => ['status' => InvoiceStatus::OVERDUE->value]);
    }
}
