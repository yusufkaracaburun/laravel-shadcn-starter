<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Company;
use Illuminate\Database\Seeder;

final class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first team (or create one if none exists)
        $team = Team::query()->first();

        if (!$team) {
            $this->command->warn('No team found. Companies will be created without team_id.');
        }

        // Create companies for the team
        if ($team) {
            // Create 30 companies for the team
            Company::factory()
                ->count(30)
                ->forTeam($team->id)
                ->create();
        }

        // Create 10 additional companies without team assignment
        Company::factory()
            ->count(10)
            ->create();
    }
}
