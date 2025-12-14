<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Project;
use Illuminate\Database\Seeder;

final class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first team (or create one if none exists)
        $team = Team::query()->first();

        if (! $team) {
            $this->command->warn('No team found. Projects will be created without team_id.');
        }

        // Create projects for the team
        if ($team) {
            // Create 30 projects for the team
            Project::factory()
                ->count(30)
                ->forTeam($team->id)
                ->create();
        }

        // Create 10 additional projects without team assignment
        Project::factory()
            ->count(10)
            ->create();
    }
}
