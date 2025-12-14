<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\Contact;
use App\Models\Customer;
use App\Enums\UserStatus;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    private static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'status' => fake()->randomElement(UserStatus::values()),
            'email_verified_at' => now(),
            'password' => self::$password ??= Hash::make('password'),
            'profile_photo_path' => 'https://i.pravatar.cc/300',
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * State: system user (not a contact user).
     */
    public function systemUser(): static
    {
        return $this->state(fn (): array => []);
    }

    /**
     * State: contact user (customer contact person with login).
     *
     * Creates a Contact first, then creates a User with matching information.
     * Sets the contact_id foreign key on the User to link it to the Contact.
     */
    public function contactUser(?Customer $customer = null): static
    {
        return $this->afterCreating(function (User $user) use ($customer): void {
            $contact = Contact::factory()->customer($customer ?? Customer::factory()->create())->create();

            // Update user with contact information and link to contact
            $user->forceFill([
                'email' => $contact->email,
                'name' => mb_trim($contact->first_name.' '.$contact->last_name),
                'contact_id' => $contact->id,
            ])->save();
        });
    }
}
