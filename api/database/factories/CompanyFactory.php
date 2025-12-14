<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
final class CompanyFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Company>
     */
    protected $model = Company::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $industries = ['technology', 'finance', 'healthcare', 'retail', 'manufacturing', 'education'];
        $statuses = ['active', 'inactive', 'pending'];
        $employeeSizes = ['1-10', '11-50', '51-200', '201-500', '500+'];

        return [
            'name' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'industry' => fake()->randomElement($industries),
            'status' => fake()->randomElement($statuses),
            'employees' => fake()->randomElement($employeeSizes),
            'team_id' => null,
        ];
    }

    /**
     * Indicate that the company should be assigned to a specific team.
     */
    public function forTeam(int $teamId): static
    {
        return $this->state(fn (array $attributes): array => [
            'team_id' => $teamId,
        ]);
    }
}
