<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Customer;
use Illuminate\Support\Str;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
final class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(PaymentStatus::values());
        $enum = PaymentStatus::tryFrom($status);

        return [
            'invoice_id' => Invoice::factory(),
            'customer_id' => Customer::factory(),
            'payment_number' => 'PAY-'.now()->year.'-'.mb_strtoupper(Str::random(8)),
            'amount' => $this->faker->randomFloat(2, 10, 5000),
            'method' => $this->faker->randomElement(['ideal', 'credit_card', 'bank_transfer', 'paypal']),
            'provider' => $this->faker->randomElement(['mollie', 'stripe', 'adyen', 'manual']),
            'provider_reference' => mb_strtoupper(Str::random(12)),
            'status' => $status,
            'paid_at' => $enum && $enum->isPaid() ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'refunded_at' => $enum && $enum->isRefunded() ? $this->faker->dateTimeBetween('-1 week', 'now') : null,
        ];
    }

    /**
     * State: paid payment.
     */
    public function paid(): static
    {
        return $this->state(fn (): array => [
            'status' => PaymentStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    /**
     * State: refunded payment.
     */
    public function refunded(): static
    {
        return $this->state(fn (): array => [
            'status' => PaymentStatus::REFUNDED,
            'paid_at' => now()->subDays(3),
            'refunded_at' => now(),
        ]);
    }

    /**
     * State: pending payment.
     */
    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => PaymentStatus::PENDING,
            'paid_at' => null,
        ]);
    }

    /**
     * State: failed payment.
     */
    public function failed(): static
    {
        return $this->state(fn (): array => [
            'status' => PaymentStatus::FAILED,
            'paid_at' => null,
        ]);
    }

    /**
     * State: cancelled payment.
     */
    public function cancelled(): static
    {
        return $this->state(fn (): array => [
            'status' => PaymentStatus::CANCELLED,
            'paid_at' => null,
        ]);
    }
}
