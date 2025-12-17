<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Customer;
use App\Enums\CustomerType;
use Brick\PhoneNumber\PhoneNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
final class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(CustomerType::values());

        return [
            'type' => $type,
            'name' => $type === CustomerType::BUSINESS->value
                ? fake()->company()
                : fake()->name(),
            'address'     => fake()->streetAddress(),
            'zipcode'     => mb_strtoupper(fake()->bothify('#### ??')),
            'city'        => fake()->city(),
            'country'     => fake()->countryCode(),
            'email'       => fake()->unique()->safeEmail(),
            'phone'       => PhoneNumber::getExampleNumber('NL'),
            'kvk_number'  => $type === CustomerType::BUSINESS->value ? fake()->numerify('########') : null,
            'vat_number'  => $type === CustomerType::BUSINESS->value ? fake()->bothify('NL#########B##') : null,
            'iban_number' => fake()->optional()->iban('NL'),
        ];
    }

    /**
     * State: business customer.
     */
    public function business(): static
    {
        return $this->state(fn (): array => [
            'type'       => CustomerType::BUSINESS,
            'name'       => fake()->company(),
            'kvk_number' => fake()->numerify('########'),
            'vat_number' => fake()->bothify('NL#########B##'),
            'email'      => fake()->companyEmail(),
        ]);
    }

    /**
     * State: private customer.
     */
    public function private(): static
    {
        return $this->state(fn (): array => [
            'type'       => CustomerType::PRIVATE,
            'name'       => fake()->name(),
            'kvk_number' => null,
            'vat_number' => null,
            'email'      => fake()->unique()->safeEmail(),
        ]);
    }
}
