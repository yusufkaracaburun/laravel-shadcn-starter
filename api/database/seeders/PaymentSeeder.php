<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Payment;
use App\Enums\PaymentStatus;
use Illuminate\Database\Seeder;

final class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $invoices = Invoice::all();

        $invoices->each(function (Invoice $invoice): void {
            $numPayments = fake()->numberBetween(0, 2);

            if ($numPayments === 0) {
                return;
            }

            $invoiceTotal = $invoice->total;

            // Verdeel in 1 of 2 betalingen (voor partial payments)
            $splitAmounts = $numPayments === 1
                ? [$invoiceTotal]
                : [
                    $invoiceTotal->divide(2),
                    $invoiceTotal->divide(2),
                ];

            foreach ($splitAmounts as $index => $amount) {
                $status = match ($index) {
                    0 => fake()->randomElement([PaymentStatus::PAID, PaymentStatus::PENDING]),
                    1 => fake()->randomElement([PaymentStatus::PAID, PaymentStatus::FAILED, PaymentStatus::REFUNDED]),
                    default => PaymentStatus::PENDING,
                };

                Payment::factory()->create([
                    'invoice_id' => $invoice->id,
                    'customer_id' => $invoice->customer_id,
                    'amount' => $amount,
                    'status' => $status,
                ]);
            }
        });

        Payment::factory()
            ->count(5)
            ->state(fn (): array => [
                'invoice_id' => null,
                'customer_id' => null,
                'status' => fake()->randomElement(PaymentStatus::cases()),
            ])
            ->create();
    }
}
