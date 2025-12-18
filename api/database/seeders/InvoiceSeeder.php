<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\InvoiceStatus;
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

        $randomCustomer = $customers->random();

        Invoice::factory()->count(5)->for($randomCustomer)->sent()->create();
        Invoice::factory()->count(20)->for($randomCustomer)->paid()->create();
        Invoice::factory()->count(5)->for($randomCustomer)->unpaid()->create();
        Invoice::factory()->count(3)->for($randomCustomer)->overdue()->create();

        $customers->each(function (Customer $customer): void {
            Invoice::factory()
                ->count(random_int(2, 4))
                ->for($customer)
                ->create();
        });
    }
}
