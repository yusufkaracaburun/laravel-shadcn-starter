<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Customer;
use Illuminate\Database\Seeder;

final class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = Customer::all();

        $customers->each(function (Customer $customer): void {
            Invoice::factory()
                ->count(random_int(2, 4))
                ->for($customer)
                ->create();
        });

        $randomCustomer = $customers->random();

        Invoice::factory()->count(5)->for($randomCustomer)->state(['status' => 'draft'])->create();
        Invoice::factory()->count(5)->for($randomCustomer)->state(['status' => 'paid'])->create();
    }
}
