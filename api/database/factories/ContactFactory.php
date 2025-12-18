<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Customer;
use Brick\PhoneNumber\PhoneNumber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
final class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name'  => fake()->lastName(),
            'email'      => fake()->unique()->safeEmail(),
            'phone'      => PhoneNumber::getExampleNumber('NL'),
            'address'    => fake()->streetAddress(),
            'zipcode'    => mb_strtoupper(fake()->postcode()),
            'city'       => fake()->city(),
            'country'    => 'NL',
        ];
    }

    /**
     * State: contact for a specific customer.
     */
    public function customer(Customer $customer): static
    {
        $split = explode(' ', $customer->name);

        return $this->state(fn (): array => [
            'email'      => $customer->email,
            'first_name' => $split[0],
            'last_name'  => isset($split[1]) ? implode(' ', array_slice($split, 1)) : fake()->lastName(),
            'phone'      => $customer->phone,
            'address'    => $customer->address,
            'zipcode'    => $customer->zipcode,
            'city'       => $customer->city,
            'country'    => $customer->country,
        ]);
    }
}
