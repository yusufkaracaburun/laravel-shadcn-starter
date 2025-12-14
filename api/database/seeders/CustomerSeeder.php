<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use App\Models\Contact;
use App\Models\Customer;
use Illuminate\Database\Seeder;

final class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1️⃣ Particuliere klanten zonder user (geen login)
        Customer::factory()
            ->count(3)
            ->private()
            ->create();

        // 2️⃣ Particuliere klanten mét user (login)
        Customer::factory()
            ->count(3)
            ->private()
            ->create()
            ->each(function (Customer $customer): void {
                $user = User::factory()->contactUser($customer)->create();
                $user->assignRole(UserRole::CUSTOMER->value);

                // Get the contact associated with this user
                $contact = Contact::query()->where('email', $user->email)->first();

                if ($contact) {
                    $customer->contacts()->attach($contact->id, [
                        'role' => 'contactpersoon',
                        'is_primary' => true,
                    ]);
                }
            });

        // 3️⃣ Zakelijke klanten zonder user (meerdere contacten zonder login)
        Customer::factory()
            ->count(3)
            ->business()
            ->create()
            ->each(function (Customer $customer): void {
                $contacts = Contact::factory()->count(random_int(2, 4))->create();

                foreach ($contacts as $index => $contact) {
                    $customer->contacts()->attach($contact->id, [
                        'role' => $index === 0 ? 'directeur' : 'medewerker',
                        'is_primary' => $index === 0,
                    ]);
                }
            });

        // 4️⃣ Zakelijke klanten mét user (directeur met login + extra contacten)
        Customer::factory()
            ->count(3)
            ->business()
            ->create()
            ->each(function (Customer $customer): void {
                // primaire contactpersoon met login
                $user = User::factory()->contactUser($customer)->create();
                $user->assignRole(UserRole::CUSTOMER->value);

                // Get the contact associated with this user
                $contact = Contact::query()->where('email', $user->email)->first();

                if ($contact) {
                    $customer->contacts()->attach($contact->id, [
                        'role' => 'directeur',
                        'is_primary' => true,
                    ]);
                }

                // extra medewerkers zonder login
                Contact::factory()
                    ->count(random_int(1, 3))
                    ->create()
                    ->each(
                        fn (Contact $contact) => $customer->contacts()->attach($contact->id, [
                            'role' => 'medewerker',
                            'is_primary' => false,
                        ])
                    );
            });
    }
}
