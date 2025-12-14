<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
final class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<Project>
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statuses = ['active', 'on-hold', 'completed', 'cancelled'];
        $categories = ['design', 'development', 'marketing', 'support', 'other'];

        $startDate = fake()->dateTimeBetween('-6 months', 'now');
        $endDate = fake()->dateTimeBetween($startDate, '+6 months');

        return [
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement($statuses),
            'category' => fake()->randomElement($categories),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'progress' => fake()->numberBetween(0, 100),
            'team_id' => null,
        ];
    }

    /**
     * Indicate that the project should be assigned to a specific team.
     */
    public function forTeam(int $teamId): static
    {
        return $this->state(fn (array $attributes): array => [
            'team_id' => $teamId,
        ]);
    }
}
